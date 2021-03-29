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
 * @copyright Copyright (c) 2021 Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */

/**
 * Copyright © 2021 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace ShipperHQ\Shipper\Helper;

use ShipperHQ\Shipper\Model\Carrier\Processor\ShipperMapper;
use ShipperHQ\WS\Client\WebServiceClientFactory;
use ShipperHQ\WS\PostOrder\Placeorder\Request\PlaceOrderRequestFactory;

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
     * PostOrder constructor.
     *
     * @param LogAssist                $shipperLogger
     * @param WebServiceClientFactory  $shipperWSClientFactory
     * @param Rest                     $restHelper
     * @param PlaceOrderRequestFactory $placeOrderRequestFactory
     * @param ShipperMapper            $shipperMapper
     * @param Data                     $shipperDataHelper
     */
    public function __construct(
        LogAssist $shipperLogger,
        WebServiceClientFactory $shipperWSClientFactory,
        Rest $restHelper,
        PlaceOrderRequestFactory $placeOrderRequestFactory,
        ShipperMapper $shipperMapper,
        Data $shipperDataHelper
    ) {
        $this->shipperLogger = $shipperLogger;
        $this->shipperWSClientFactory = $shipperWSClientFactory;
        $this->restHelper = $restHelper;
        $this->shipperMapper = $shipperMapper;
        $this->placeOrderRequestFactory = $placeOrderRequestFactory;
        $this->shipperDataHelper = $shipperDataHelper;
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
                $resultSet = $this->shipperWSClientFactory->create()->sendAndReceive(
                    $this->getPlaceorderRequest($order, $rate),
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
                            'price'       => $rate->getPrice(),
                            'method'      => $rate->getMethod(),
                            'carrier'     => $rate->getCarrier(),
                            'endPoint'    => $this->restHelper->getPlaceorderGatewayUrl(),
                            'timeout'     => $this->restHelper->getWebserviceTimeout(),
                            'Lapsed Time' => $elapsed
                    ]
                );
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
        $carrierGroupDetails = $this->shipperDataHelper->decodeShippingDetails($rate->getCarriergroupShippingDetails());

        $request = $this->placeOrderRequestFactory->create([
            'orderNumber'  => $order->getIncrementId(),
            'totalCharges' => $rate->getPrice(),
            'carrierCode'  => $rate->getCarrier(),
            'methodCode'   => $rate->getMethod(),
            'transId'      => $carrierGroupDetails['transaction']
        ]);

        $request->setCredentials($this->shipperMapper->getCredentials());

        return $request;
    }
}
