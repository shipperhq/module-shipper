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

/**
 * ShipperHQ Shipper module observer
 */
abstract class AbstractRecordOrder implements ObserverInterface
{
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
     * @param \ShipperHQ\Shipper\Helper\Data $shipperDataHelper
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger
     * @param \ShipperHQ\Shipper\Helper\Package $packageHelper
     * @param \ShipperHQ\Shipper\Helper\CarrierGroup $carrierGroupHelper
     */
    public function __construct(
        \ShipperHQ\Shipper\Helper\Data $shipperDataHelper,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger,
        \ShipperHQ\Shipper\Helper\Package $packageHelper,
        \ShipperHQ\Shipper\Helper\CarrierGroup $carrierGroupHelper
    ) {

        $this->shipperDataHelper = $shipperDataHelper;
        $this->quoteRepository = $quoteRepository;
        $this->shipperLogger = $shipperLogger;
        $this->packageHelper = $packageHelper;
        $this->carrierGroupHelper = $carrierGroupHelper;
    }

    public function recordOrder($order)
    {
        //https://github.com/magento/magento2/issues/4233
        $quoteId = $order->getQuoteId();
        //Merged from pull request https://github.com/shipperhq/module-shipper/pull/20 - credit to vkalchenko
        $quote = $this->quoteRepository->get($quoteId, [$order->getStoreId()]);

        $shippingAddress = $quote->getShippingAddress();

        $order->setDestinationType($shippingAddress->getDestinationType());
        $order->setValidationStatus($shippingAddress->getValidationStatus());

        $this->carrierGroupHelper->saveOrderDetail($order, $shippingAddress);
        $this->carrierGroupHelper->recordOrderItems($order);
        $this->packageHelper->saveOrderPackages($order, $shippingAddress);

        if (strstr($order->getShippingMethod(), 'shqshared_')) {
            $orderDetailArray = $this->carrierGroupHelper->loadOrderDetailByOrderId($order->getId());
            //SHQ16- Review for splits
            foreach ($orderDetailArray as $orderDetail) {
                $original = $orderDetail->getCarrierType();
                $carrierTypeArray = explode('_', $orderDetail->getCarrierType());
                if (is_array($carrierTypeArray) && isset($carrierTypeArray[1])) {
                    $orderDetail->setCarrierType($carrierTypeArray[1]);
                    //SHQ16-1026
                    $currentShipDescription = $order->getShippingDescription();
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
            $order->setShippingMethod($this->getDefaultCarrierShipMethod($order, $shippingAddress));
        }

        $order->save();
    }

    private function getDefaultCarrierShipMethod($order, $shippingAddress)
    {
        $shipping_method = $order->getShippingMethod();
        $rate = $shippingAddress->getShippingRateByCode($shipping_method);
        if ($rate) {
            list($carrierCode, $method) = explode('_', $shipping_method, 2);
            $carrierType = $rate->getCarrierType();
            $carrierType = strstr($carrierType, "shqshared_") ?
                str_replace('shqshared_', '', $carrierType) : $carrierType;
            $magentoCarrierCode = $this->shipperDataHelper->mapToMagentoCarrierCode(
                $carrierType,
                $carrierCode
            );
            $shipping_method = ($magentoCarrierCode . '_' . $method);
        }
        return $shipping_method;
    }
}
