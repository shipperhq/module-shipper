<?php
/**
 *
 * ShipperHQ Shipping Module
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * Shipper HQ Shipping
 *
 * @category ShipperHQ
 * @package ShipperHQ_Shipping_Carrier
 * @copyright Copyright (c) 2015 Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */

namespace ShipperHQ\Shipper\Model\Listing;

use ShipperHQ\GraphQL\Client\PostorderClient;
use ShipperHQ\GraphQL\Helpers\Serializer;
use ShipperHQ\GraphQL\Response\CreateListing;
use ShipperHQ\GraphQL\Response\Data\CreateListingData;
use ShipperHQ\Shipper\Model\Carrier\Processor\ListingMapper;
use ShipperHQ\Shipper\Helper\Authorization;
use ShipperHQ\Shipper\Helper\GraphQLHelper;

class ListingService extends \Magento\Framework\Model\AbstractModel
{
    const LISTING_CREATED = 'Listing Created';
    const LISTING_FAILED = 'Listing Failed';

    /**
     * @var \ShipperHQ\Shipper\Helper\LogAssist
     */
    private $shipperLogger;

    /**
     * @var ListingMapper
     */
    private $listingMapper;

    /**
     * @var GraphQLHelper
     */
    private $graphqlHelper;

    /**
     * @var Authorization
     */
    private $authorizationHelper;

    /**
     * @var PostorderClient
     */
    private $postorderClient;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var \ShipperHQ\Shipper\Helper\CarrierGroup
     */
    protected $carrierGroupHelper;

    /**
     * @var \ShipperHQ\Shipper\Helper\Data
     */
    protected $shipperDataHelper;

    /**
     * ListingService constructor.
     * @param \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger
     * @param ListingMapper $listingMapper
     * @param GraphQLHelper $graphqlHelper
     * @param Authorization $authorizationHelper
     * @param \ShipperHQ\Shipper\Helper\CarrierGroup $carrierGroupHelper
     * @param \ShipperHQ\Shipper\Helper\Data $shipperDataHelper
     */
    public function __construct(
        \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger,
        ListingMapper $listingMapper,
        GraphQLHelper $graphqlHelper,
        Authorization $authorizationHelper,
        PostorderClient $postorderClient,
        Serializer $serializer,
        \ShipperHQ\Shipper\Helper\CarrierGroup $carrierGroupHelper,
        \ShipperHQ\Shipper\Helper\Data $shipperDataHelper
    ) {
        $this->shipperLogger = $shipperLogger;
        $this->listingMapper = $listingMapper;
        $this->graphqlHelper = $graphqlHelper;
        $this->authorizationHelper = $authorizationHelper;
        $this->postorderClient = $postorderClient;
        $this->serializer = $serializer;
        $this->carrierGroupHelper = $carrierGroupHelper;
        $this->shipperDataHelper = $shipperDataHelper;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param \Magento\Quote\Model\Quote\Address $shippingAddress
     * @param string $carrierType
     * @param \ShipperHQ\Shipper\Model\Api\CreateListing\Item[] $withItems
     * @param false|\ShipperHQ\Shipper\Api\FetchUpdatedCarrierRate\RateInterface $rate
     * @return false|CreateListing
     */
    public function createListing($order, $shippingAddress, $carrierType, $withItems = [], $rate = false)
    {
        $endpoint = $this->graphqlHelper->getListingEndpoint();
        $timeout = $this->graphqlHelper->getTimeout();
        $headers = $this->graphqlHelper->buildRequestHeader();

        $orderDetailArray = $this->carrierGroupHelper->loadOrderDetailByOrderId($order->getId());

        $originName = $this->getOriginName($orderDetailArray);
        $shippingCost = $this->getShippingCost($orderDetailArray);
        try {
            $requestObj = $this->listingMapper->mapCreateListingRequest($order, $shippingAddress, $carrierType, $originName, $shippingCost, $withItems, $rate);
        } catch (\Exception $e) {
            $this->shipperLogger->postCritical('Shipperhq_Shipper', 'Failed to form request', $e->getMessage());
            return false;
        }

        try {
            $initVal = microtime(true);
            $response = $this->postorderClient->createListing($requestObj, $endpoint, $timeout, $headers);
            $elapsed = microtime(true) - $initVal;
            $this->shipperLogger->postDebug('Shipperhq_Shipper', 'Listing Request time elapsed', $elapsed);
            $this->shipperLogger->postInfo('Shipperhq_Shipper', 'Listing Request and Response', $response['debug']);
        } catch (\Exception $e) {
            $this->shipperLogger->postCritical('Shipperhq_Shipper', 'Rate Request failed with Exception', $e->getMessage());
            return false;
        }

        $result = $this->parseResponse($response, CreateListing::class, [$this, 'handleCreateListingResponse']);
        $this->recordResult($result, $response, $orderDetailArray);
        return $result ? $response['result'] : $result;
    }

    /**
     * @param array $response
     * @param string $expectClass
     * @param callable $callback
     * @param array $context
     * @return mixed|null
     */
    private function parseResponse($response, $expectClass, $callback, $context = [])
    {
        /** @var \ShipperHQ\GraphQL\Response\ResponseInterface $responseBody */
        $responseBody = $response['result'];
        if ($responseBody instanceof $expectClass && ($responseBody->getData() !== null)) {
            $responseData = $responseBody->getData();
            if (!$responseBody->getErrors() && $responseData && !$responseData->isEmpty()) {
                return call_user_func($callback, $responseData, $context);
            } else {
                $this->shipperLogger->postWarning('Shipperhq_Shipper', 'Server responded with errors', $responseBody->getErrors());
            }
        } else {
            $this->shipperLogger->postWarning('Shipperhq_Shipper', 'Response did not have expected format', '');
        }
        return null;
    }

    /**
     * @param CreateListingData $responseData
     * @param array $context
     * @return bool
     */
    private function handleCreateListingResponse($responseData, $context)
    {
        $createListing = $responseData->getCreateListing();
        $errors = $createListing->getErrors();
        if (!empty($errors)) {
            $this->shipperLogger->postWarning('Shipperhq_Shipper', 'Response has errors', $errors);
            return false;
        }

        $serializedResponse = $this->serializer::serialize($createListing, JSON_PRETTY_PRINT);

        if ($createListing->getResponseSummary()->getStatus() != 1) {
            $this->shipperLogger->postWarning('Shipperhq_Shipper', 'Response returned failing status', $serializedResponse);
            return false;
        }

        $this->shipperLogger->postInfo('Shipperhq_Shipper', 'Listing Parsed Response', $serializedResponse);
        return true;
    }

    /**
     * @param Order $order
     * @return string
     */
    private function getOriginName($orderDetailArray)
    {

        //Formatted as arrays but likely to be only 1 per order
        foreach ($orderDetailArray as $orderDetail) {

            $cgArray = $this->shipperDataHelper->decodeShippingDetails($orderDetail->getCarrierGroupDetail());

            foreach ($cgArray as $key => $cgDetail) {
                return $cgDetail['name'];
            }
        }
        return '';
    }

    /**
     * @param Array $orderDetailArray
     * @return string
     */
    private function getShippingCost($orderDetailArray)
    {

        //Formatted as arrays but likely to be only 1 per order
        foreach ($orderDetailArray as $orderDetail) {

            $cgArray = $this->shipperDataHelper->decodeShippingDetails($orderDetail->getCarrierGroupDetail());

            foreach ($cgArray as $key => $cgDetail) {
                return isset($cgDetail['rate_cost']) ? $cgDetail['rate_cost'] : $cgDetail['cost'];
            }
        }
        return '';
    }

    /**
     * @param boolean $success
     * @param array $response
     * @param array $orderDetailArray
     * @return mixed
     */
    private function recordResult($success, $response, $orderDetailArray)
    {
        /** @var CreateListing $response */
        $response = $response['result']; // Ditch the debugging data

        $listingResult = $success ? self::LISTING_CREATED : self::LISTING_FAILED;

        $listingId = '';
        if ($response->getData() && $response->getData()->getCreateListing()) {
            $listingId = $response->getData()->getCreateListing()->getListingId();
        }

        //Formatted as arrays but likely to be only 1 per order
        foreach ($orderDetailArray as $orderDetail) {
            $cgArray = $this->shipperDataHelper->decodeShippingDetails($orderDetail->getCarrierGroupDetail());
            foreach ($cgArray as $key => $cgDetail) {
                $cgDetail['listing_created'] = $listingResult;
                $cgDetail['listing_id'] = $listingId;
                $cgArray[$key] = $cgDetail;
            }
            $encoded = $this->shipperDataHelper->encode($cgArray);
            $orderDetail->setCarrierGroupDetail($encoded);
            $orderDetail->save();
        }

        return $success;
    }
}
