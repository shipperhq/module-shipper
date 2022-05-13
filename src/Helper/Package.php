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
     * @var Data
     */
    private $shipperDataHelper;
    /**
     * @var CarrierGroup
     */
    private $carrierGroupHelper;
    /**
     * @var \Magento\Sales\Api\OrderStatusHistoryRepositoryInterface
     */
    protected $orderStatusHistoryRepository;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * Package constructor.
     *
     * @param \Magento\Framework\App\Helper\Context                    $context
     * @param \ShipperHQ\Shipper\Model\Order\PackagesFactory           $orderPackageFactory
     * @param Data                                                     $shipperDataHelper
     * @param CarrierGroup                                             $carrierGroupHelper
     * @param \Magento\Sales\Api\OrderStatusHistoryRepositoryInterface $orderStatusHistoryRepository
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \ShipperHQ\Shipper\Model\Order\PackagesFactory $orderPackageFactory,
        Data $shipperDataHelper,
        CarrierGroup $carrierGroupHelper,
        \Magento\Sales\Api\OrderStatusHistoryRepositoryInterface $orderStatusHistoryRepository,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {

        parent::__construct($context);
        $this->orderPackageFactory = $orderPackageFactory;
        $this->shipperDataHelper = $shipperDataHelper;
        $this->carrierGroupHelper = $carrierGroupHelper;
        $this->orderStatusHistoryRepository = $orderStatusHistoryRepository;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Save the quote package information
     *
     * @param $shippingAddressId
     * @param $shipmentArray
     * @param $carrierCode
     * @param $carrierGroupId
     *
     * @return void
     */
    public function saveQuotePackages($shippingAddressId, $shipmentArray)
    {
        if ($shippingAddressId === null) {
            return;
        }

        $sessionPackages = json_decode($this->checkoutSession->getShipperHQPackages() ?? "", true);

        foreach ($shipmentArray as $shipment) {
            $carrierCode = $shipment['carrier_code'];
            $carrierGroupId = $shipment['carrier_group_id'];

            /*
             * MNB-604 Need to ensure remove any rates from requests with only 1 origin if we now have > 1 origin and
             * so now have merged rates. Merged rates are stored with carriercode_methodcode
             */
            $carrierCodeExplArr = explode("_", $carrierCode ?? "");
            $carrierCodeExpl = $carrierCodeExplArr[0];

            //Delete any existing packages for this set of rates
            unset($sessionPackages[$shippingAddressId][$carrierGroupId][$carrierCode]);
            unset($sessionPackages[$shippingAddressId][$carrierGroupId][$carrierCodeExpl]);
        }

        foreach ($shipmentArray as $shipment) {
            $shipment['quote_address_id'] = $shippingAddressId;
            $carrierCode = $shipment['carrier_code'];
            $carrierGroupId = $shipment['carrier_group_id'];

            //$carrierCode can be carrierCode_methodCode - see populateShipments()
            $sessionPackages[$shippingAddressId][$carrierGroupId][$carrierCode][] = $shipment;
        }
        $this->checkoutSession->setShipperHQPackages(json_encode($sessionPackages));
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

                        $sessionPackages = json_decode($this->checkoutSession->getShipperHQPackages() ?? "", true);

                        $packagesColl = $this->getPackagesFromSession(
                            $sessionPackages,
                            $shippingAddress->getAddressId(),
                            $shippingMethodCode,
                            $carrier_code,
                            $carrierGroupId
                        );

                        foreach ($packagesColl as $box) {
                            $package = $this->orderPackageFactory->create();
                            $package->setOrderId($orderId);
                            $package->setCarrierGroupId($carrierGroupId)
                                ->setCarrierCode($box['carrier_code'])
                                ->setPackageName($box['package_name'])
                                ->setLength($box['length'])
                                ->setWidth($box['width'])
                                ->setHeight($box['height'])
                                ->setWeight($box['weight'])
                                ->setDeclaredValue($box['declared_value'])
                                ->setSurchargePrice($box['surcharge_price'])
                                ->setItems($box['items']);
                            $package->save();
                        }

                        if (!empty($packagesColl) && $savePackagesAsOrderComment) {
                            $boxText = $this->shipperDataHelper->getPackageBreakdownText(
                                $packagesColl,
                                $carrier_group['name']
                            );
                            $boxText .= __('Transaction ID: ') . $carrier_group['transaction'];
                        } else {
                            $boxText = __('Transaction ID: ') . $carrier_group['transaction'];
                        }

                        /*
                         * SHQ18-1700 Thanks to @dewayneholden on Github for this suggested code change
                         * Using method addStatusHistoryComment() for Magento 2.1 compatibility
                         * Once 2.1 is EOL we will switch to using addCommentToStatusHistory()
                         */
                        $this->orderStatusHistoryRepository->save($order->addStatusHistoryComment($boxText, $order->getStatus()));

                        if (strpos($order->getShippingMethod(), 'multicarrier') === 0) {
                            $this->orderStatusHistoryRepository->save($order->addStatusHistoryComment(
                                "Shipping method for " . $carrier_group['name'] . ": " . $carrier_group['carrierTitle'] ." - ". $carrier_group['methodTitle'],
                                $order->getStatus()
                            ));
                        }

                        if ($carrier_group['carrierType'] == 'customerAccount') {
                            $this->orderStatusHistoryRepository->save($order->addStatusHistoryComment(
                                $this->shipperDataHelper->getCustomerCarrierBreakdownText($carrier_group),
                                $order->getStatus()
                            ));
                        }
                    }
                }
            } catch (\Exception $e) {
                //Log exception and move on.
                $this->_logger->critical('ShipperHQ save order package error: ' . $e->getMessage());
            }
        }
        //record without carrier group details?
    }

    /**
     * Finds the packages/boxes saved in the session
     *
     * MNB-1465 Switched order of if/else. Now will favour carrier_method code over just carrier
     *
     * @param $sessionData
     * @param $shippingAddressId
     * @param $methodCode
     * @param $carrierCode
     * @param $carrierGroupId
     *
     * @return array
     */
    private function getPackagesFromSession($sessionData, $shippingAddressId, $methodCode, $carrierCode, $carrierGroupId)
    {
        $packages = [];

        if (!empty($sessionData) && array_key_exists($shippingAddressId, $sessionData)) {
            if (array_key_exists($carrierGroupId, $sessionData[$shippingAddressId])) {
                if (array_key_exists($carrierCode . '_' . $methodCode, $sessionData[$shippingAddressId][$carrierGroupId])) {
                    $packages = $sessionData[$shippingAddressId][$carrierGroupId][$carrierCode . '_' . $methodCode];
                } elseif (array_key_exists($carrierCode, $sessionData[$shippingAddressId][$carrierGroupId])) {
                    $packages = $sessionData[$shippingAddressId][$carrierGroupId][$carrierCode];
                }
            }
        }

        return $packages;
    }
}
