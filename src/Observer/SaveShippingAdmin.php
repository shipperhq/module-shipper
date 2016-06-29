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

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;


/**
 * ShipperHQ Shipper module observer
 */
class SaveShippingAdmin implements ObserverInterface
{
    /**
     * @var \ShipperHQ\Shipper\Helper\Data
     */
    protected $shipperDataHelper;
    /**
     * @var \ShipperHQ\Shipper\Model\CarrierGroupFactory
     */
    protected $carrierGroupFactory;
    /**
     * @var \ShipperHQ\Shipper\Helper\LogAssist
     */
    private $shipperLogger;

    /**
     * @param \ShipperHQ\Shipper\Helper\Data $shipperDataHelper
     * @param  \ShipperHQ\Shipper\Model\Carrier\Shipper $carrier
     * @param  \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger

     */
    public function __construct(
        \ShipperHQ\Shipper\Helper\Data $shipperDataHelper,
        \ShipperHQ\Shipper\Model\CarrierGroupFactory $carrierGroupFactory,
        \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger
)
    {
        $this->shipperDataHelper = $shipperDataHelper;
        $this->carrierGroupFactory = $carrierGroupFactory;
        $this->shipperLogger = $shipperLogger;
    }
    /**
     * Record order shipping information after order is placed
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        if ($this->shipperDataHelper->getConfigValue('carriers/shipper/active')) {
            $requestData = $observer->getRequestModel()->getPost();
            if (isset($requestData['order'])) {
                $orderData = $requestData['order'];
            }
            $quote = $observer->getSession()->getQuote();
            //if(!empty($orderData['shipping_method_flag']))
            if (!empty($orderData['shipping_method'])) {
                $shippingMethod = $orderData['shipping_method'];
                $this->saveCarrierGroupInformation($quote->getShippingAddress(), $shippingMethod);
            }
            //}
        }
    }

    /**
     * Save the carrier group shipping details for single carriergroup orders and then
     * return to standard Magento logic to save the method
     *
     * @param $shippingMethod
     * @return array
     */
    protected function saveCarrierGroupInformation($shippingAddress, $shippingMethod)
    {

        $foundRate = $shippingAddress->getShippingRateByCode($shippingMethod);
        if($foundRate && $foundRate->getCarriergroupShippingDetails() != '') {
            $shipDetails = $this->shipperDataHelper->decodeShippingDetails($foundRate->getCarriergroupShippingDetails());
            if(array_key_exists('carrierGroupId', $shipDetails)) {
                $arrayofShipDetails = array();
                $arrayofShipDetails[] = $shipDetails;

                $shipDetails = $arrayofShipDetails;
                $encodedShipDetails = $this->shipperDataHelper->encodeShippingDetails($arrayofShipDetails);
            }
            else {
                $encodedShipDetails = $this->shipperDataHelper->encodeShippingDetails($shipDetails);
            }

            $shippingAddress
                ->setCarrierId($foundRate->getCarrierId())
                ->setCarrierType($foundRate->getCarrierType())
                ->save();

            $carrierGroupDetail = $this->carrierGroupFactory->create();
            $update = ['quote_address_id' => $shippingAddress->getId(),
                'carrier_group_detail' => $encodedShipDetails,
                'carrier_group_html' => $this->shipperDataHelper->getCarriergroupShippingHtml(
                    $encodedShipDetails)];
            $carrierGroupDetail->setData($update);
            $carrierGroupDetail->save();
            //save selected shipping options to items
        }
        return array();
    }

    /**
     * Save shipping breakdown per carrier group
     * @param $observer
     */
    public function saveShippingMethodAdmin($observer)
    {
        if(!Mage::helper('shipperhq_shipper')->isModuleEnabled('Shipperhq_Shipper', 'carriers/shipper/active')) {
            return;
        }
        $requestData = $observer->getRequestModel()->getPost();
        $orderData = array();
        if (isset($requestData['order'])) {
            $orderData = $requestData['order'];
        }
        if(!empty($requestData['shipping_method_flag'])) {
            $orderData = $requestData;
        }
        $quote = $observer->getOrderCreateModel()->getQuote();
        Mage::helper('shipperhq_shipper')->setQuote($quote);

        if (!empty($orderData['shipping_method_flag'])) {
            if (!empty($orderData['shipping_method'])) {
                $shippingMethod = $orderData['shipping_method'];
                $helper = Mage::getSingleton('shipperhq_shipper/checkout_helper');
                $helper->saveSingleShippingMethod($quote->getShippingAddress(), $shippingMethod);

                $rate = $quote->getShippingAddress()->getShippingRateByCode($shippingMethod);
                if(!$rate) {
                    if (Mage::helper('shipperhq_shipper')->isDebug()) {
                        Mage::helper('wsalogger/log')->postDebug('Shipperhq_Shipper',
                            'save Shipping Method', "Can't find rate for selected shipping method of " .$shippingMethod);
                    }
                    return;
                }

                if(Mage::helper('shipperhq_shipper')->isModuleEnabled('Shipperhq_Pbint') &&
                    Mage::helper('shipperhq_shipper')->isModuleEnabled('Shipperhq_Shipper', 'carriers/shipper/active')) {
                    $address = $quote->getShippingAddress();
                    $pbHelper = Mage::getModel('shipperhq_pbint/helper');
                    $pbHelper->cleanDownSession();

                    if (Mage::helper('shipperhq_pbint')->isPbOrder($address->getCarrierType())) {

                        $result = $this->reserveOrder($pbHelper, $address, $rate->getCarrier(), $rate->getCarriergroupId());
                    }
                    else {
                        if (Mage::helper('shipperhq_shipper')->isDebug()) {
                            Mage::helper('wsalogger/log')->postDebug('Shipperhq_Shipper',
                                '', "Selected shipping method is NOT ShipperHQ Pitney");
                        }
                    }
                }
                $requestData['order']['shipping_method'] = $orderData['shipping_method'];
            }
            $observer->getRequestModel()->setPost($requestData);
        }
    }
}

