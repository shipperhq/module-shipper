<?php
/**
 * ShipperHQ Shipping Module
 * NOTICE OF LICENSE
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 * Shipper HQ Shipping
 * @category  ShipperHQ
 * @package   ShipperHQ_Shipping_Carrier
 * @copyright Copyright (c) 2021 Zowta LLC (http://www.ShipperHQ.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author    ShipperHQ Team sales@shipperhq.com
 */

/**
 * Copyright © 2021 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace ShipperHQ\Shipper\Helper;

use ShipperHQ\Shipper\Model\Carrier\Processor\ShipperMapper;
use ShipperHQ\WS\Client\WebServiceClientFactory;
use ShipperHQ\WS\PostOrder\Placeorder\Request\PlaceOrderRequestFactory;
use ShipperHQ\WS\Shared\BasicAddressFactory;
use ShipperHQ\Shipper\Helper\CarrierGroup;

/**
 * PostOrder Helper
 */
class PostOrder
{
    /**
     * @var LogAssist
     */
    private $shipperLogger;
    /**
     * @var Client\WebServiceClientFactory
     */
    private $shipperWSClientFactory;
    /**
     * @var ShipperHQ\WS\PostOrder\Placeorder\Request
     */
    private $placeOrderRequestFactory;
    /**
     * @var Data
     */
    private $shipperDataHelper;
    /**
     * @var \ShipperHQ\Shipper\Helper\CarrierGroup
     */
    private $carrierGroupHelper;
    /**
     * @var WS\Shared\BasicAddressFactory
     */
    private $basicAddressFactory;

    /**
     * PostOrder constructor.
     *
     * @param LogAssist                $shipperLogger
     * @param WebServiceClientFactory  $shipperWSClientFactory
     * @param Rest                     $restHelper
     * @param PlaceOrderRequestFactory $placeOrderRequestFactory
     * @param ShipperMapper            $shipperMapper
     * @param Data                     $shipperDataHelper
     * @param BasicAddressFactory      $basicAddressFactory
     */
    public function __construct(
        LogAssist                $shipperLogger,
        WebServiceClientFactory  $shipperWSClientFactory,
        Rest                     $restHelper,
        PlaceOrderRequestFactory $placeOrderRequestFactory,
        ShipperMapper            $shipperMapper,
        Data                     $shipperDataHelper,
        BasicAddressFactory      $basicAddressFactory,
        CarrierGroup             $carrierGroupHelper
    ) {
        $this->shipperLogger = $shipperLogger;
        $this->shipperWSClientFactory = $shipperWSClientFactory;
        $this->restHelper = $restHelper;
        $this->shipperMapper = $shipperMapper;
        $this->placeOrderRequestFactory = $placeOrderRequestFactory;
        $this->shipperDataHelper = $shipperDataHelper;
        $this->basicAddressFactory = $basicAddressFactory;
        $this->carrierGroupHelper = $carrierGroupHelper;
    }

    /**
     * @param $order
     * @param $shippingAddress
     * @param $quoteId
     */
    public function handleOrder($order, $shippingAddress, $quoteId)
    {
        $orderNumber = $order->getIncrementId();
        if ($shippingAddress) {
            if ($rate = $shippingAddress->getShippingRateByCode($shippingAddress->getShippingMethod())) {
                $initVal = microtime(true);
                $request = $this->getPlaceorderRequest($order, $rate);
                if ($request != null) {
                    $resultSet = $this->shipperWSClientFactory->create()->sendAndReceive(
                        $request,
                        $this->restHelper->getPlaceorderGatewayUrl(),
                        $this->restHelper->getWebserviceTimeout()
                    );
                    $elapsed = microtime(true) - $initVal;
                    $this->shipperLogger->postDebug(
                        'Shipperhq_Shipper',
                        'PostOrder::handleOrder: success',
                        [
                            'result'      => $resultSet,
                            'orderNumber' => $orderNumber,
                            'price'       => $request->totalCharges,
                            'method'      => $request->methodCode,
                            'carrier'     => $request->carrierCode,
                            'endPoint'    => $this->restHelper->getPlaceorderGatewayUrl(),
                            'timeout'     => $this->restHelper->getWebserviceTimeout(),
                            'Lapsed Time' => $elapsed
                        ]
                    );
                }
            } else {
                $this->shipperLogger->postDebug(
                    'Shipperhq_Shipper',
                    "PostOrder::handleOrder - can't get rate for shipping method",
                    ['shippingMethod' => $shippingAddress->getShippingMethod(), 'orderNumber' => $orderNumber]
                );
            }
        } else {
            $this->shipperLogger->postDebug(
                'Shipperhq_Shipper',
                'PostOrder::handleOrder - shipping address is null',
                ['orderNumber' => $orderNumber]
            );
        }
    }

    /**
     * @param $order
     * @param $rate
     *
     * @return mixed
     */
    private function getPlaceorderRequest($order, $rate)
    {
        $request = null;
        $orderDetails = $this->carrierGroupHelper->loadOrderDetailByOrderId($order->getId());
        $transactionId = "";
        $methodCode = $rate->getMethod();

        if ($orderDetails->getFirstItem() != null
            && !empty($orderDetails->getFirstItem()->getData('carrier_group_detail'))) {

            $carrierGroupDetails = $this->shipperDataHelper->decodeShippingDetails(
                $orderDetails->getFirstItem()->getData('carrier_group_detail')
            );

            $carrierGroupDetail = !empty($carrierGroupDetails) ? $carrierGroupDetails[0] : [];

            if (array_key_exists('transaction', $carrierGroupDetail)) {
                $transactionId = $carrierGroupDetail['transaction'];
            }

            $ignoreCarriers = ['shqshared', 'multicarrier'];
            // MNB-1429 Ensure sending method code that's not been altered by Magento
            if (!in_array($rate->getCarrier(), $ignoreCarriers) && array_key_exists('code', $carrierGroupDetail)) {
                $methodCode = $carrierGroupDetail['code'];
            }

            $request = $this->placeOrderRequestFactory->create([
                'orderNumber'  => $order->getIncrementId(),
                'totalCharges' => $rate->getPrice(),
                'carrierCode'  => $rate->getCarrier(),
                'methodCode'   => $methodCode,
                'transId'      => $transactionId,
                'recipient'    => $this->getRecipient($order->getShippingAddress())
            ]);

            $request->setCredentials($this->shipperMapper->getCredentials());
        } else {
            // MNB-2430 Can fall in here for admin order. Not supporting admin orders at this time
            $this->shipperLogger->postDebug(
                'Shipperhq_Shipper',
                'PostOrder::getPlaceorderRequest - failed to find order details',
                ['orderNumber' => $order->getIncrementId()]
            );
        }

        return $request;
    }

    /**
     * Get values for recipient
     *
     * @param $request
     *
     * @return array
     */
    private function getRecipient($shippingAddress)
    {
        $region = $shippingAddress->getRegionCode();
        if ($region === null) {
            $region = "";
        }
        $street = $shippingAddress->getStreetLine(1);
        $street2 = $shippingAddress->getStreetLine(2);
        $recipient = $this->basicAddressFactory->create([
            'city'    => $shippingAddress->getCity() === null ? '' : $shippingAddress->getCity(),
            'country' => $shippingAddress->getCountryId() === null ? '' : $shippingAddress->getCountryId(),
            'region'  => $region,
            'street'  => $street === null || !is_string($street) ? '' : str_replace("\n", ' ', $street),
            'street2' => $street2 == null || !is_string($street2) ? '' : str_replace("\n", ' ', $street2),
            'zipcode' => $shippingAddress->getPostcode() === null ? '' : $shippingAddress->getPostcode()
        ]);

        return $recipient;
    }
}
