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
class CarrierGroup extends Data
{
    /**
     * @var \ShipperHQ\Shipper\Model\Quote\AddressDetailFactory
     */
    private $addressDetailFactory;
    /**
     * @var \ShipperHQ\Shipper\Model\Quote\ItemDetailFactory
     */
    private $itemDetailFactory;
    /**
     * @var \ShipperHQ\Shipper\Model\Order\DetailFactory
     */
    private $orderDetailFactory;
    /**
     * @var \ShipperHQ\Shipper\Model\Order\ItemDetailFactory
     */
    private $orderItemDetailFactory;
    /*
    * @var Data
    */
    protected $shipperDataHelper;

    /**
     * @param \ShipperHQ\Lib\Helper\Rest $restHelper
     * @param Data $shipperHelperData
     */
    public function __construct(\ShipperHQ\Shipper\Model\Quote\AddressDetailFactory $addressDetailFactory,
                                \ShipperHQ\Shipper\Model\Quote\ItemDetailFactory $itemDetailFactory,
                                \ShipperHQ\Shipper\Model\Order\DetailFactory $orderDetailFactory,
                                \ShipperHQ\Shipper\Model\Order\ItemDetailFactory $orderItemDetailFactory,
                                Data $shipperDataHelper)
    {
        $this->addressDetailFactory = $addressDetailFactory;
        $this->itemDetailFactory = $itemDetailFactory;
        $this->orderDetailFactory = $orderDetailFactory;
        $this->orderItemDetailFactory = $orderItemDetailFactory;
        $this->shipperDataHelper = $shipperDataHelper;
    }

    /**
     * Save the carrier group shipping details for single carriergroup orders and
     * set carrier information on shipping address
     *
     * @param $shippingAddress
     * @param $shippingMethod
     * @return array
     */
    public function saveCarrierGroupInformation($shippingAddress, $shippingMethod, array $additionalDetail = [])
    {
        //admin and front end orders use method
        $foundRate = $shippingAddress->getShippingRateByCode($shippingMethod);
        if($foundRate && $foundRate->getCarriergroupShippingDetails() != '') {
            $shipDetails = $this->shipperDataHelper->decodeShippingDetails($foundRate->getCarriergroupShippingDetails());
            if(array_key_exists('carrierGroupId', $shipDetails)) {
                $arrayofShipDetails = [];
                $arrayofShipDetails[] = $shipDetails;
            }
            else {
                $arrayofShipDetails = $shipDetails;
            }

            $encodedShipDetails = $this->shipperDataHelper->encode($arrayofShipDetails);

            $shippingAddress
                ->setCarrierId($foundRate->getCarrierId())
                ->setCarrierType($foundRate->getCarrierType())
                ->save();

            $addressDetail = $this->addressDetailFactory->create();
            $thisAddressDetail = $addressDetail->loadByCarrierGroupIdAndAddress($foundRate->getCarriergroupId(),
                $shippingAddress->getId());
            if(!$thisAddressDetail) {
                $thisAddressDetail = $addressDetail;
            }
            $update = ['quote_address_id' => $shippingAddress->getId(),
                'carrier_group_id' => $foundRate->getCarriergroupId(),
                'carrier_type' => $foundRate->getCarrierType(),
                'carrier_group' => $foundRate->getCarriergroup(),
                'carrier_id' => $foundRate->getCarrierId(),
                'dispatch_date' => $foundRate->getShqDispatchDate(),
                'delivery_date' => $foundRate->getShqDeliveryDate(),
                'carrier_group_detail' => $encodedShipDetails,
                'carrier_group_html' => $this->getCarriergroupShippingHtml(
                    $encodedShipDetails)];
            foreach($additionalDetail as $key => $data){
                $update[$key] = $data;
            }
            foreach($arrayofShipDetails as $detail) {
                //records destination type returned on rate - not type from address validation or user selection
                if(isset($detail['destination_type'])) {
                    $update['destination_type'] = $detail['destination_type'];
                }
            }
            $existing = $thisAddressDetail->getData();
            $data = array_merge($existing, $update);
            $thisAddressDetail->setData($data);
            $thisAddressDetail->save();

            //save selected shipping options to items
            $this->setShippingOnItems($arrayofShipDetails,  $shippingAddress);
        }
        return true;
    }

    public function saveCarrierGroupItem($item, $carrierGroupId, $carrierGroup)
    {
        $itemDetail = $this->itemDetailFactory->create();
        $itemRecord = $itemDetail->loadDetailByItemId($item->getItemId());
        if(!$itemRecord) {
            $itemRecord = $itemDetail->setQuoteItemId($item->getItemId());
        }
        $itemRecord->setCarrierGroupId($carrierGroupId)
                    ->setCarrierGroup($carrierGroup);
        $itemRecord->save();
    }

    public function setShippingOnItems($shippingDetails, $shippingAddress)
    {
        $itemDetail = $this->itemDetailFactory->create();
        foreach($shippingAddress->getAllItems() as $item){
            $itemRecord = $itemDetail->loadDetailByItemId($item->getItemId());
            //TODO handle when no record exists
            if($itemRecord) {
                foreach($shippingDetails as $carrierGroupDetail) {
                    if($carrierGroupDetail['carrierGroupId'] == $itemRecord->getCarrierGroupId()) {
                        //updateRecord
                        $shippingText = $carrierGroupDetail['carrierTitle'] .' - ' .$carrierGroupDetail['methodTitle'];
                        $itemRecord->setCarriergroupShipping($shippingText);
                        $itemRecord->save();
                    }
                }
            }
        }
    }

    public function saveOrderDetail($order, $shippingAddress)
    {

        $quoteAddressCollection = $this->loadAddressDetailByShippingAddress($shippingAddress->getId());
        $orderId = $order->getId();
        foreach($quoteAddressCollection as $quoteDetail ) {
            $orderDetailModel = $this->orderDetailFactory->create();
            $data = $quoteDetail->getData();
            $existingOrderDetailCollection = $orderDetailModel->loadByOrder($orderId);
            if(count($existingOrderDetailCollection) > 0) {
                //TODO deal with this so we don't get duplicates
                foreach($existingOrderDetailCollection as $order) {
                    $data = array_merge($data, $order->getData());
                    break;
                }
            }

            unset($data['quote_address_id']);
            unset($data['id']);
            $data['order_id'] = $orderId;
            $orderDetailModel->setData($data);
            $orderDetailModel->save();
        }

    }

    public function recordOrderItems($order)
    {
        foreach($order->getAllItems() as $orderItem) {
            $quoteItemId =  $orderItem->getQuoteItemId();
            $quoteItemDetail = $this->itemDetailFactory->create()->loadDetailByItemId($quoteItemId);
            if($quoteItemDetail) {
                $orderItemDetail = $this->orderItemDetailFactory->create();

                $data = $quoteItemDetail->getData();
                $data['order_item_id'] = $orderItem->getId();
                unset($data['quote_item_id']);
                unset($data['id']);
                $orderItemDetail->setData($data)
                    ->save();
            }
        }
    }

    public function loadAddressDetailByShippingAddress($shippingAddressId)
    {
        $addressDetailModel = $this->addressDetailFactory->create();

        $addressDetailCollection = $addressDetailModel->loadByAddress($shippingAddressId);
        return $addressDetailCollection;
    }

    public function getOrderCarrierGroupInfo($orderId)
    {
        $orderDetailCollection = $this->loadOrderDetailByOrderId($orderId);
        $detail = [];
        foreach ($orderDetailCollection as $orderDetail)
        {
          //  $cginfo = $this->decode($orderDetail->getCarrierGroupDetail());
            $data = $orderDetail->getData();
            $detail[] = $data;
        }
        return $detail;
    }

    public function loadOrderDetailByOrderId($orderId)
    {
        $orderDetailModel = $this->orderDetailFactory->create();

        $orderDetailCollection = $orderDetailModel->loadByOrder($orderId);
        return $orderDetailCollection;
    }


}