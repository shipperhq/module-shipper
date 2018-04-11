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

namespace ShipperHQ\Shipper\Helper;

/**
 * Carrier Group Processing helper
 */
class Package extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \ShipperHQ\Shipper\Model\Order\PackagesFactory
     */
    protected $orderPackageFactory;
    /**
     * @var \ShipperHQ\Shipper\Model\Quote\PackagesFactory
     */
    private $quotePackageFactory;
    /**
     * @var Data
     */
    private $shipperDataHelper;
    /**
     * @var CarrierGroup
     */
    private $carrierGroupHelper;

    /**
     * Package constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \ShipperHQ\Shipper\Model\Quote\PackagesFactory $quotePackageFactory
     * @param \ShipperHQ\Shipper\Model\Order\PackagesFactory $orderPackageFactory
     * @param Data $shipperDataHelper
     * @param CarrierGroup $carrierGroupHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \ShipperHQ\Shipper\Model\Quote\PackagesFactory $quotePackageFactory,
        \ShipperHQ\Shipper\Model\Order\PackagesFactory $orderPackageFactory,
        Data $shipperDataHelper,
        CarrierGroup $carrierGroupHelper
    ) {

        parent::__construct($context);
        $this->quotePackageFactory = $quotePackageFactory;
        $this->orderPackageFactory = $orderPackageFactory;
        $this->shipperDataHelper = $shipperDataHelper;
        $this->carrierGroupHelper = $carrierGroupHelper;
    }

    /**
     * Save the quote package information
     *
     * @param $shippingAddressId
     * @param $shipmentArray
     * @return void
     */
    public function saveQuotePackages($shippingAddressId, $shipmentArray)
    {
        if ($shippingAddressId === null) {
            return;
        }
        try {
            $packageModel = $this->quotePackageFactory->create();
            foreach ($shipmentArray as $shipment) {
                //clean up packages saved
                //this should be in some kind of package manager - an interface or something as this could be replaced
                $packages = $packageModel->loadByCarrier(
                    $shippingAddressId,
                    $shipment['carrier_group_id'],
                    $shipment['carrier_code']
                );
                foreach ($packages as $package) {
                    $packageModel->deleteByPackageId($package->getPackageId());
                }
            }

            foreach ($shipmentArray as $shipment) {
                $shipment['quote_address_id'] = $shippingAddressId;
                $packageModel->setData($shipment);
                $packageModel->save();
            }
        } catch (\Exception $e) {
            //Log exception and move on.
            $this->_logger->critical('ShipperHQ save quote package error: ' . $e->getMessage());
        }
    }

    public function recoverOrderPackageDetail($order)
    {
        $packages = $this->loadOrderPackagesByOrderId($order->getId());
        if (empty($packages)) {
            $quoteShippingAddress = $this->carrierGroupHelper->getQuoteShippingAddressFromOrder($order);

            if ($quoteShippingAddress != null) {
                $this->saveOrderPackages($order, $quoteShippingAddress);
            }
        }
    }

    public function loadOrderPackagesByOrderId($orderId)
    {
        $packageModel = $this->orderPackageFactory->create();
        $orderPackageCollection = $packageModel->loadByOrderId($orderId);
        return $orderPackageCollection;
    }

    public function saveOrderPackages($order, $shippingAddress)
    {
        $orderId = $order->getId();
        $packagesColl = [];
        $addressDetail = $this->carrierGroupHelper
            ->loadAddressDetailByShippingAddress($shippingAddress->getAddressId());
        $savePackagesAsOrderComment = $this->shipperDataHelper->getStoreDimComments();
        foreach ($addressDetail as $detail) {
            try {
                $carrierGroupDetail = $this->shipperDataHelper->decodeShippingDetails(
                    $detail->getCarrierGroupDetail()
                );
                if (is_array($carrierGroupDetail)) {
                    foreach ($carrierGroupDetail as $carrier_group) {
                        if (!isset($carrier_group['carrierGroupId'])) {
                            continue;
                        }
                        $carrierGroupId = $carrier_group['carrierGroupId'];
                        $carrier_code = $carrier_group['carrier_code'];
                        $shippingMethodCode = $carrier_group['code'];
                        $quotePackageModel = $this->quotePackageFactory->create();
                        $packagesColl = $quotePackageModel->loadByCarrier(
                            $shippingAddress->getAddressId(),
                            $carrierGroupId,
                            $carrier_code . '_' . $shippingMethodCode
                        );
                        if ($packagesColl->getSize() < 1) {
                            $quotePackageModelToo = $this->quotePackageFactory->create();
                            $packagesColl = $quotePackageModelToo->loadByCarrier(
                                $shippingAddress->getAddressId(),
                                $carrierGroupId,
                                $carrier_code
                            );
                        }
                        foreach ($packagesColl as $box) {
                            $package = $this->orderPackageFactory->create();
                            $package->setOrderId($orderId);
                            $package->setCarrierGroupId($carrierGroupId)
                                ->setCarrierCode($box->getCarrierCode())
                                ->setPackageName($box->getPackageName())
                                ->setLength($box->getLength())
                                ->setWidth($box->getWidth())
                                ->setHeight($box->getHeight())
                                ->setWeight($box->getWeight())
                                ->setDeclaredValue($box->getDeclaredValue())
                                ->setSurchargePrice($box->getSurchargePrice())
                                ->setItems($box->getItems());
                            $package->save();
                        }

                        if (!empty($packagesColl) && $savePackagesAsOrderComment) {
                            $boxText = $this->shipperDataHelper->getPackageBreakdownText(
                                $packagesColl,
                                $carrier_group['name']
                            );
                            $boxText .= __('Transaction ID: ') . $carrier_group['transaction'];
                            $order->addStatusToHistory($order->getStatus(), $boxText, false);
                        } else {
                            $boxText = __('Transaction ID: ') . $carrier_group['transaction'];
                            $order->addStatusToHistory($order->getStatus(), $boxText, false);
                        }
                    }
                }
            } catch (\Exception $e) {
                //Log exception and move on.
                $this->_logger->critical('ShipperHQ save order package error: ' . $e->getMessage());
            }
        }
        $order->save();

        //record without carrier group details?
    }
}
