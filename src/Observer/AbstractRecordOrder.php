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
Abstract class AbstractRecordOrder implements ObserverInterface
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
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;
    /**
     * @var \ShipperHQ\Shipper\Helper\LogAssist
     */
    private $shipperLogger;

    /**
     * @param \ShipperHQ\Shipper\Helper\Data $shipperDataHelper
     * @param \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        \ShipperHQ\Shipper\Helper\Data $shipperDataHelper,
        \ShipperHQ\Shipper\Model\CarrierGroupFactory $carrierGroupFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger
    )
    {
        $this->shipperDataHelper = $shipperDataHelper;
        $this->carrierGroupFactory = $carrierGroupFactory;
        $this->quoteRepository = $quoteRepository;
        $this->shipperLogger = $shipperLogger;

    }

    //TODO multiaddress multishipping_checkout_controller_success_action

//    public function checkoutMulitaddressSuccess($observer)
//    {
//        if(!Mage::helper('shipperhq_shipper')->isModuleEnabled('Shipperhq_Shipper', 'carriers/shipper/active')) {
//            return;
//        }
//        $orderIds = $observer->getEvent()->getOrderIds();
//        if (empty($orderIds) || !is_array($orderIds)) {
//            return;
//        }
//        foreach($orderIds as $orderId) {
//            $order = Mage::getModel('sales/order')->load($orderId);
//            if($order->getIncrementId()) {
//                $this->confirmOrder($order);
//            }
//        }
//    }

    protected function recordOrder($order)
    {
        $customOrderId = null;
        //https://github.com/magento/magento2/issues/4233
        $quoteId = $order->getQuoteId();
        $quote = $this->quoteRepository->get($quoteId);

        $shippingAddress = $quote->getShippingAddress();
        $carrierType = $shippingAddress->getCarrierType();
        $order->setCarrierType($carrierType);
        $order->setDestinationType($shippingAddress->getDestinationType());
        $order->setValidationStatus($shippingAddress->getValidationStatus());
        if($shippingAddress->getCustomerId()) {
            $customerAddresses = $quote->getCustomer()->getAddresses();
            foreach($customerAddresses as $address) {

              //  $currentValue = $address->getValidationStatus();

                if($address->getId() == $shippingAddress->getCustomerAddressId()) {
                    $address->setCustomAttribute('validation_status',$shippingAddress->getValidationStatus());
                    $address->setCustomAttribute('destination_type', $shippingAddress->getDestinationType());
                   // $address->save();
                }
            }
        }
        $this->recordOrderItems($order, $quote);
        if(strstr($order->getCarrierType(), 'shqshared_')) {
            $original  = $order->getCarrierType();
            $carrierTypeArray = explode('_', $order->getCarrierType());
            if(is_array($carrierTypeArray)) {
                $order->setCarrierType($carrierTypeArray[1]);
                //SHQ16-1026
                //    $carrierGroupDetail = $order->getCarriergroupShippingDetails();
                $shipId = $shippingAddress->getAddressId();
                $carrierGroupDetailObject = $this->carrierGroupFactory->create()->load($shipId, 'quote_address_id');
                $carrierGroupDetail = $carrierGroupDetailObject->getData('carrier_group_detail');
                $currentShipDescription = $order->getShippingDescription();
                $shipDescriptionArray = explode('-', $currentShipDescription);
                $cgArray = $this->shipperDataHelper->decodeShippingDetails($carrierGroupDetail);
                foreach($cgArray as $key => $cgDetail) {
                    if(isset($cgDetail['carrierType']) && $cgDetail['carrierType'] == $original) {
                        $cgDetail['carrierType'] = $carrierTypeArray[1];
                    }
                    if(is_array($shipDescriptionArray) && isset($cgDetail['carrierTitle'])) {
                        $shipDescriptionArray[0] = $cgDetail['carrierTitle'] .' ';
                        $newShipDescription = implode('-', $shipDescriptionArray);
                        $order->setShippingDescription($newShipDescription);
                    }
                    $cgArray[$key] = $cgDetail;
                }
                $carrierGroupDetailObject->setData('carrier_group_detail', $carrierGroupDetail);
                $carrierGroupDetailObject->save();
                $this->shipperLogger->postInfo('Shipperhq_Shipper',
                    'Rates displayed as single carrier',
                    'Resetting carrier type on order to be ' .$carrierTypeArray[1]);

            }
        }
        $order->save();

    }

    protected function recordOrderItems($order, $quote)
    {
        foreach($order->getAllItems() as $orderItem) {
            foreach($quote->getAllItems() as $quoteItem) {
                if($quoteItem->getId() == $orderItem->getQuoteItemId()) {
                    $orderItem->setCarriergroupId($quoteItem->getCarriergroupId());
                    $orderItem->setCarriergroup($quoteItem->getCarriergroup());
                    $orderItem->setCarriergroupShipping($quoteItem->getCarriergroupShipping());
                    $orderItem->save();
                }
            }

        }
    }
}

