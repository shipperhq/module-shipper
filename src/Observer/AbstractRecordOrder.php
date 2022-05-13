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

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace ShipperHQ\Shipper\Observer;

use Magento\Framework\Event\ObserverInterface;
use ShipperHQ\GraphQL\Response\CreateListing;
use ShipperHQ\Shipper\Helper\Listing as ListingHelper;
use ShipperHQ\Shipper\Helper\PostOrder;
use ShipperHQ\Shipper\Model\Listing\ListingService;

/**
 * ShipperHQ Shipper module observer
 */
abstract class AbstractRecordOrder implements ObserverInterface
{
    /**
     * Constant for order view feature code
     *
     * @var string
     */
    const ORDER_VIEW_FEATURE = 'order_view';

    /**
     * @var \ShipperHQ\Shipper\Helper\Data
     */
    protected $shipperDataHelper;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \ShipperHQ\Shipper\Helper\CarrierGroup
     */
    protected $carrierGroupHelper;

    /**
     * @var \ShipperHQ\Shipper\Helper\LogAssist
     */
    private $shipperLogger;

    /**
     * @var \ShipperHQ\Shipper\Helper\Package
     */
    private $packageHelper;

    /**
     * @var ListingService
     */
    private $listingService;

    /**
     * @var ListingHelper
     */
    private $listingHelper;

    /**
     * @var PostOrder
     */
    private $postOrderHelper;

    const USHIP_CARRIER_TYPE = 'uShip';

    const AUTOMATIC_LISTING = 'AUTO';

    /**
     * AbstractRecordOrder constructor.
     *
     * @param \ShipperHQ\Shipper\Helper\Data             $shipperDataHelper
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \ShipperHQ\Shipper\Helper\LogAssist        $shipperLogger
     * @param \ShipperHQ\Shipper\Helper\Package          $packageHelper
     * @param \ShipperHQ\Shipper\Helper\CarrierGroup     $carrierGroupHelper
     * @param ListingService                             $listingService
     * @param ListingHelper                              $listingHelper
     * @param PostOrder                                  $postOrderHelper
     */
    public function __construct(
        \ShipperHQ\Shipper\Helper\Data $shipperDataHelper,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger,
        \ShipperHQ\Shipper\Helper\Package $packageHelper,
        \ShipperHQ\Shipper\Helper\CarrierGroup $carrierGroupHelper,
        ListingService $listingService,
        ListingHelper $listingHelper,
        PostOrder $postOrderHelper
    ) {
        $this->shipperDataHelper = $shipperDataHelper;
        $this->quoteRepository = $quoteRepository;
        $this->shipperLogger = $shipperLogger;
        $this->packageHelper = $packageHelper;
        $this->carrierGroupHelper = $carrierGroupHelper;
        $this->listingService = $listingService;
        $this->listingHelper = $listingHelper;
        $this->postOrderHelper = $postOrderHelper;
    }

    public function recordOrder($order)
    {
        // https://github.com/magento/magento2/issues/4233
        $quoteId = $order->getQuoteId();
        // Merged from pull request https://github.com/shipperhq/module-shipper/pull/20 - credit to vkalchenko
        $quote = $this->quoteRepository->get($quoteId, [$order->getStoreId()]);

        // SHQ18-1947 Need to find correct address to lookup carriergroup details and packed boxes when MAC
        if ($quote->getIsMultiShipping()) {
            $customerAddressId = $order->getShippingAddress()->getCustomerAddressId();

            foreach ($quote->getAllShippingAddresses() as $address) {
                if ($address->getCustomerAddressId() == $customerAddressId) {
                    $shippingAddress = $address;
                    break;
                }
            }
        } else {
            $shippingAddress = $quote->getShippingAddress();
        }

        $order->setDestinationType($shippingAddress->getDestinationType());
        $order->setValidationStatus($shippingAddress->getValidationStatus());

        $this->carrierGroupHelper->saveOrderDetail($order, $shippingAddress);
        $this->carrierGroupHelper->recordOrderItems($order);
        $this->packageHelper->saveOrderPackages($order, $shippingAddress);

        $shippingMethod = (string) $order->getShippingMethod();
        $shippingRate = $shippingAddress->getShippingRateByCode($shippingMethod);

        if ($shippingRate) {

            // RIV-443 Save order details to OMS / MNB-1464 Ensure only saving for SHQ methods
            if ($this->shipperDataHelper->getStoreQuoteOrder() &&
                (strstr((string) $shippingRate->getCarrier(), 'shq') || $shippingRate->getCarrier() == 'multicarrier')) {
                $this->postOrderHelper->handleOrder($order, $shippingAddress, $quoteId);
            }

            list($carrierCode, $method) = explode('_', $shippingMethod, 2);
            $carrierType = $shippingRate->getCarrierType();

            if ($carrierType == self::USHIP_CARRIER_TYPE &&
                $this->shipperDataHelper->getDefaultConfigValue('carriers/shipper/create_listing') &&
                $this->shipperDataHelper->getDefaultConfigValue('carriers/shipper/create_listing') == self::AUTOMATIC_LISTING) {

                /** @var false|CreateListing $listingCreated */
                $listingCreated = $this->listingService->createListing($order, $shippingAddress, $carrierType);
                if ($listingCreated !== false && $listingCreated != null) {
                    $this->listingHelper->saveListingDetailsToOrderComments($order, ListingService::LISTING_CREATED, $listingCreated->getData()->getCreateListing()->getListingId());
                }
            }
        }

        // Merged rates or display as single carrier
        if (strstr((string) $shippingMethod, 'shqshared_')) {
            $orderDetailArray = $this->carrierGroupHelper->loadOrderDetailByOrderId($order->getId());
            //SHQ16- Review for splits
            foreach ($orderDetailArray as $orderDetail) {
                $original = $orderDetail->getCarrierType();
                $carrierTypeArray = explode('_', (string) $orderDetail->getCarrierType());
                if (is_array($carrierTypeArray) && isset($carrierTypeArray[1])) {
                    $orderDetail->setCarrierType($carrierTypeArray[1]);
                    //SHQ16-1026
                    $currentShipDescription = (string) $order->getShippingDescription();
                    $shipDescriptionArray = explode('-', $currentShipDescription);
                    $cgArray = $this->shipperDataHelper->decodeShippingDetails($orderDetail->getCarrierGroupDetail());
                    foreach ($cgArray as $key => $cgDetail) {
                        if (isset($cgDetail['carrierType']) && $cgDetail['carrierType'] == $original) {
                            $cgDetail['carrierType'] = $carrierTypeArray[1];
                        }
                        if (is_array($shipDescriptionArray) && isset($cgDetail['carrierTitle'])) {
                            $shipDescriptionArray[0] = $cgDetail['carrierTitle'] . ' ';
                            $newShipDescription = implode('-', $shipDescriptionArray);
                            if (!$this->shipperDataHelper->getAlwaysShowSingleCarrierTitle()) {
                                $order->setShippingDescription($newShipDescription);

                                //SHQ18-2416 Save actual carrier and method code from rate shop
                                if (isset($cgDetail['carrier_code']) && isset($cgDetail['code'])) {
                                    $order->setShippingMethod($cgDetail['carrier_code'] . "_" . $cgDetail['code']);
                                }
                            }
                        }

                        $cgArray[$key] = $cgDetail;
                    }
                    $encoded = $this->shipperDataHelper->encode($cgArray);
                    $orderDetail->setCarrierGroupDetail($encoded);
                    $orderDetail->save();
                    $this->shipperLogger->postInfo(
                        'Shipperhq_Shipper',
                        'Rates displayed as single carrier',
                        'Resetting carrier type on order to be ' . $carrierTypeArray[1]
                    );
                }
            }
        }

        if ($this->shipperDataHelper->useDefaultCarrierCodes()) {
            $order->setShippingMethod($this->getDefaultCarrierShipMethod($order->getShippingMethod(), $shippingRate));
        }

        $order->save();
    }

    /**
     * Converts shipping_method saved on order from ShipperHQ carrier code to Magento carrier code
     * Will also convert to Magento UPS method codes if carrier type is UPS
     *
     * @param $shippingMethod String carriercode_methodcode
     * @param $shippingRate
     *
     * @return string
     */
    private function getDefaultCarrierShipMethod($shippingMethod, $shippingRate)
    {
        if ($shippingRate) {
            list($carrierCode, $method) = explode('_', (string) $shippingMethod, 2);
            $carrierType = $shippingRate->getCarrierType();
            $carrierType = strstr((string) $carrierType, "shqshared_") ?
                str_replace('shqshared_', '', $carrierType) : $carrierType;
            $magentoCarrierCode = $this->shipperDataHelper->mapToMagentoCarrierCode(
                $carrierType,
                $carrierCode
            );

            // SHQ18-1620 Change to numerical UPS code for Magento labelling support
            if ($carrierType == "ups") {
                $method = $this->shipperDataHelper->mapToMagentoUPSMethodCode($method);
            }
            $shippingMethod = ($magentoCarrierCode . '_' . $method);
        }

        return $shippingMethod;
    }
}
