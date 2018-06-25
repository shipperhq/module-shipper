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

use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Carrier Group Processing helper
 */
class CarrierGroup extends Data
{
    const NO_SHIPPERHQ_DETAIL_AVAILABLE = 'ShipperHQ Notice';
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
    /**
     * @var \ShipperHQ\Shipper\Model\Order\GridDetailFactory
     */
    private $orderGridDetailFactory;
    /**
     * @var Data
     */
    private $shipperDataHelper;
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    private $allNamedOptions = array(
        'liftgate_required' => 'Liftgate Required',
        'inside_delivery' => 'Inside Delivery',
        'destination_type' => 'Destination Type',
        'notify_required' => 'Notify Required',
        'customer_carrier' => 'Customer Carrier',
        'customer_carrier_ph' => 'Customer Carrier Phone',
        'customer_carrier_account' => 'Customer Carrier Account Number',
        'limited_delivery' => 'Limited Access for Delivery'
    );

    /**
     * @param \ShipperHQ\Shipper\Model\Quote\AddressDetailFactory $addressDetailFactory
     * @param \ShipperHQ\Shipper\Model\Quote\ItemDetailFactory $itemDetailFactory
     * @param \ShipperHQ\Shipper\Model\Order\DetailFactory $orderDetailFactory
     * @param \ShipperHQ\Shipper\Model\Order\ItemDetailFactory $orderItemDetailFactory
     * @param \ShipperHQ\Shipper\Model\Order\GridDetailFactory $orderGridDetailFactory
     * @param Data $shipperDataHelper
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        \ShipperHQ\Shipper\Model\Quote\AddressDetailFactory $addressDetailFactory,
        \ShipperHQ\Shipper\Model\Quote\ItemDetailFactory $itemDetailFactory,
        \ShipperHQ\Shipper\Model\Order\DetailFactory $orderDetailFactory,
        \ShipperHQ\Shipper\Model\Order\ItemDetailFactory $orderItemDetailFactory,
        \ShipperHQ\Shipper\Model\Order\GridDetailFactory $orderGridDetailFactory,
        Data $shipperDataHelper,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {

        $this->addressDetailFactory = $addressDetailFactory;
        $this->itemDetailFactory = $itemDetailFactory;
        $this->orderDetailFactory = $orderDetailFactory;
        $this->orderItemDetailFactory = $orderItemDetailFactory;
        $this->orderGridDetailFactory = $orderGridDetailFactory;
        $this->shipperDataHelper = $shipperDataHelper;
        $this->quoteRepository = $quoteRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
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
        if ($foundRate && $foundRate->getCarriergroupShippingDetails() != '') {
            $shipDetails = $this->shipperDataHelper->decodeShippingDetails(
                $foundRate->getCarriergroupShippingDetails()
            );
            if (array_key_exists('carrierGroupId', $shipDetails)) {
                $arrayofShipDetails = [];
                $arrayofShipDetails[] = $shipDetails;
            } else {
                $arrayofShipDetails = $shipDetails;
            }

            $shippingAddress
                ->setCarrierId($foundRate->getCarrierId())
                ->setCarrierType($foundRate->getCarrierType())
                ->save();

            $addressDetail = $this->addressDetailFactory->create();
            $thisAddressDetail = $addressDetail->loadByCarrierGroupIdAndAddress(
                $foundRate->getCarriergroupId(),
                $shippingAddress->getId()
            );
            if (!$thisAddressDetail) {
                $thisAddressDetail = $addressDetail;
            }

            $update = [
                'quote_address_id' => $shippingAddress->getId(),
                'carrier_group_id' => $foundRate->getCarriergroupId(),
                'carrier_type' => $foundRate->getCarrierType(),
                'carrier_group' => $foundRate->getCarriergroup(),
                'carrier_id' => $foundRate->getCarrierId(),
                'dispatch_date' => $foundRate->getShqDispatchDate() ?
                    date('Y-m-d', strtotime($foundRate->getShqDispatchDate())) :
                    '',
                'delivery_date' => $foundRate->getShqDeliveryDate() ?
                    date('Y-m-d', strtotime($foundRate->getShqDeliveryDate())) :
                    ''
            ];

            $update = array_merge($update, $additionalDetail);

            foreach ($arrayofShipDetails as $key => $detail) {
                //records destination type returned on rate - not type from address validation or user selection
                if (isset($detail['destination_type'])) {
                    $update['destination_type'] = $detail['destination_type'];
                }
                //SHQ18-69 include additional fields in carrier_group_detail
                $arrayofShipDetails[$key] = array_merge($detail, $additionalDetail);
            }

            $encodedShipDetails = $this->shipperDataHelper->encode($arrayofShipDetails);
            $update['carrier_group_detail'] = $encodedShipDetails;
            $update['carrier_group_html'] = $this->getCarriergroupShippingHtml($encodedShipDetails);

            $existing = $thisAddressDetail->getData();
            $data = array_merge($existing, $update);
            $thisAddressDetail->setData($data);
            $thisAddressDetail->save();

            //save selected shipping options to items
            $this->setShippingOnItems($arrayofShipDetails, $shippingAddress);
        }
        return true;
    }

    public function setShippingOnItems($shippingDetails, $shippingAddress)
    {
        foreach ($shippingAddress->getAllItems() as $item) {
            $itemDetail = $this->itemDetailFactory->create();
            $itemRecord = $itemDetail->loadDetailByItemId($item->getItemId());
            if ($itemRecord) {
                foreach ($shippingDetails as $carrierGroupDetail) {
                    if ($carrierGroupDetail['carrierGroupId'] == $itemRecord->getCarrierGroupId()) {
                        //updateRecord
                        $shippingText = $carrierGroupDetail['carrierTitle']
                            . ' - '
                            . $carrierGroupDetail['methodTitle'];
                        $itemRecord->setCarriergroupShipping($shippingText);
                        $itemRecord->save();
                    }
                }
            }
        }
    }

    public function saveCarrierGroupItem($item, $carrierGroupId, $carrierGroup)
    {
        $itemDetail = $this->itemDetailFactory->create();
        $itemRecord = $itemDetail->loadDetailByItemId($item->getItemId());
        if (!$itemRecord) {
            $itemRecord = $itemDetail->setQuoteItemId($item->getItemId());
        }
        $itemRecord->setCarrierGroupId($carrierGroupId)
            ->setCarrierGroup($carrierGroup);
        $itemRecord->save();
    }

    public function loadOrderGridDetailByOrderId($orderId)
    {
        $orderGridDetailModel = $this->orderGridDetailFactory->create();
        $orderGridDetailCollection = $orderGridDetailModel->loadByOrder($orderId);
        return $orderGridDetailCollection;
    }

    public function recoverOrderInfoFromQuote($order)
    {
        $shippingAddress = $this->getQuoteShippingAddressFromOrder($order);

        if ($shippingAddress != null) {
            $this->saveOrderDetail($order, $shippingAddress);
        }

        //attempt to recover item detail
        foreach ($order->getAllItems() as $item) {
            $itemDetail = $this->loadOrderItemDetailByOrderItemId($item->getId());
            if (!$itemDetail) {
                $this->recordOrderItems($order);
            }
            break;
        }
        $cgInfo = $this->getOrderCarrierGroupInfo($order->getId());
        if (empty($cgInfo)) {
            $order->addStatusToHistory(
                $order->getStatus(),
                self::NO_SHIPPERHQ_DETAIL_AVAILABLE . __(': No detailed shipping information recorded'),
                false
            );
            $order->save();
        }
        return $cgInfo;
    }

    public function getQuoteShippingAddressFromOrder($order)
    {
        $shippingAddress = null;

        $searchCriteria = $this->searchCriteriaBuilder->addFilter(
            'main_table.entity_id',
            $order->getQuoteId()
        )->create(); //SHQ18-56
        $quotes = $this->quoteRepository->getList($searchCriteria);
        if ($quotes->getTotalCount() > 0) {
            foreach ($quotes->getItems() as $quote) {
                $shippingAddress = $quote->getShippingAddress();
                break;
            }
        }

        return $shippingAddress;
    }

    public function saveOrderDetail($order, $shippingAddress)
    {
        $quoteAddressCollection = $this->loadAddressDetailByShippingAddress($shippingAddress->getId());
        $orderId = $order->getId();
        foreach ($quoteAddressCollection as $quoteDetail) {
            $orderDetailModel = $this->orderDetailFactory->create();
            $data = $quoteDetail->getData();
            $existingOrderDetailCollection = $orderDetailModel->loadByOrder($orderId);
            if (!empty($existingOrderDetailCollection)) {
                foreach ($existingOrderDetailCollection as $order) {
                    if ($order->getCarrierGroupId() == $quoteDetail->getCarrierGroupId()) {
                        $data = array_merge($data, $order->getData());
                        break;
                    }
                }
            }

            unset($data['quote_address_id']);
            unset($data['id']);
            $data['order_id'] = $orderId;
            $orderDetailModel->setData($data);
            $orderDetailModel->save();
        }
        $this->saveOrderGridDetail($quoteAddressCollection, $orderId);
    }

    public function loadAddressDetailByShippingAddress($shippingAddressId)
    {
        $addressDetailModel = $this->addressDetailFactory->create();

        $addressDetailCollection = $addressDetailModel->loadByAddress($shippingAddressId);
        return $addressDetailCollection;
    }

    private function saveOrderGridDetail($quoteDetailCollection, $orderId)
    {
        $orderGridDetailModel = $this->orderGridDetailFactory->create();
        $data = [];
        $isMultiple = count($quoteDetailCollection) > 1;
        $carrierGroup = '';
        foreach ($quoteDetailCollection as $quote) {
            $data = $quote->getData();
            if ($isMultiple) {
                $carrierGroup .= $quote->getData('carrier_group') . ' ';
            } else {
                $carrierGroup = $quote->getData('carrier_group');
            }
        }

        $data['carrier_group'] = $carrierGroup;

        $existingOrderGridDetailCollection = $orderGridDetailModel->loadByOrder($orderId);
        if ($existingOrderGridDetailCollection->getSize() > 0) {
            return;
        }

        unset($data['quote_address_id']);
        unset($data['id']);
        $data['order_id'] = $orderId;
        $orderGridDetailModel->setData($data)
            ->save();
    }

    public function loadOrderItemDetailByOrderItemId($orderItemId)
    {
        $orderItemDetailModel = $this->orderItemDetailFactory->create();
        $orderItemDetailCollection = $orderItemDetailModel->loadDetailByItemId($orderItemId);
        return $orderItemDetailCollection;
    }

    public function recordOrderItems($order)
    {
        foreach ($order->getAllItems() as $orderItem) {
            $quoteItemId = $orderItem->getQuoteItemId();
            $quoteItemDetail = $this->itemDetailFactory->create()->loadDetailByItemId($quoteItemId);
            if ($quoteItemDetail) {
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

    public function getOrderCarrierGroupInfo($orderId)
    {
        $orderDetailCollection = $this->loadOrderDetailByOrderId($orderId);
        $detail = [];
        foreach ($orderDetailCollection as $orderDetail) {
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

    public function canCheckForQuoteInformation($order)
    {
        foreach ($order->getAllStatusHistory() as $orderComment) {
            if (strstr($orderComment->getComment(), self::NO_SHIPPERHQ_DETAIL_AVAILABLE)) {
                return false;
            }
        }
        return true;
    }

    public function getCarrierGroupText($cginfo, $order)
    {
        $displayValues = ['destination_type', 'customer_carrier', 'customer_carrier_ph', 'customer_carrier_account'];

        $carriergroupText = '';
        foreach ($cginfo as $cgrp) {
            if (is_array($cgrp)) {
                $carriergroupText .= '<div class="shq-oinfo">';
                $carriergroupText .= '<div class="shq-oinfo-origin">' . $cgrp['name'] . '</div>';
                $carriergroupText .= '<div class="shq-oinfo-method">' . $cgrp['carrierTitle'];
                $carriergroupText .= ' - ' . $cgrp['methodTitle'];
                $price = $order->formatPrice($cgrp['price']);
                $carriergroupText .= ' ' . $price . '</div>';
                $carriergroupText .= '<div class="shq-oinfo-carrier">';
                if ((array_key_exists('carrierName', $cgrp) && $cgrp['carrierName'] != '')) {
                    $carriergroupText .= 'Carrier: ';
                    $carriergroupText .= '' . strtoupper($cgrp['carrierName']);
                }

                if (array_key_exists('pickup_location', $cgrp)) {
                    $carriergroupText .= '<br/> Pickup : ';
                    $carriergroupText .= '' . $cgrp['pickup_location'];
                }

                if ((array_key_exists('pickup_date', $cgrp) && $cgrp['pickup_date'] != '')) {
                    $carriergroupText .= ' ' . $cgrp['pickup_date'];
                    if (array_key_exists('pickup_slot', $cgrp)) {
                        $displayTimeSlot = str_replace('_', ' - ', $cgrp['pickup_slot']);
                        $carriergroupText .= ' ' . $displayTimeSlot . ' ';
                    }
                }

                if (array_key_exists('dispatch_date', $cgrp) && $cgrp['dispatch_date'] != '') {
                    $carriergroupText .= '<br/>' . __('Dispatch Date') . ' : ' . $cgrp['dispatch_date'];
                }

                if (array_key_exists('delivery_date', $cgrp) && $cgrp['delivery_date'] != '') {
                    $dateText = isset($cgrp['pickup_location']) ? __('Pickup Date') :  __('Delivery Date');
                    $carriergroupText .= '<br/>' .$dateText . ' : ' . $cgrp['delivery_date'];
                    if (array_key_exists('time_slot', $cgrp)) {
                        $displayTimeSlot = str_replace('_', ' - ', $cgrp['time_slot']);
                        $carriergroupText .= ' ' . $displayTimeSlot . ' ';
                    }
                }
                foreach ($this->allNamedOptions as $code => $name) {
                    $value = false;
                    if (array_key_exists($code, $cgrp) && $cgrp[$code] != '') {
                        $value = $cgrp[$code];
                    }
                    if ($value) {
                        $carriergroupText .= '<br/>' . $name;
                        if (in_array($code, $displayValues)) {
                            $carriergroupText .= ': ' . $value;
                        }
                    }
                }
                if (array_key_exists('freightQuoteId', $cgrp) && $cgrp['freightQuoteId'] != '') {
                    $carriergroupText .= ' Quote Id: ' . $cgrp['freightQuoteId'];
                }
                $carriergroupText .= '</div></div>';
            }
        }
        return $carriergroupText;
    }
}
