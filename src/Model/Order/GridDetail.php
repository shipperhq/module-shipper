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

namespace ShipperHQ\Shipper\Model\Order;

class GridDetail extends \Magento\Framework\Model\AbstractExtensibleModel
{

    const ORDER_ID = 'order_id';
    const CARRIER_GROUP = 'carrier_group';
    const DISPATCH_DATE = 'dispatch_date';
    const DELIVERY_DATE = 'delivery_date';
    const TIME_SLOT = 'time_slot';
    const PICKUP_LOCATION = 'pickup_location';
    const DELIVERY_COMMENTS = 'delivery_comments';
    const DESTINATION_TYPE = 'destination_type';
    const LIFTGATE_REQUIRED = 'liftgate_required';
    const NOTIFY_REQUIRED = 'notify_required';
    const INSIDE_DELIVERY = 'inside_delivery';
    const ADDRESS_VALID = 'address_valid';

    /**
     * @var /ShipperHQ\Shipper\Model\ResourceModel\Order\GridDetail\Collection
     */
    private $orderGridDetailCollection;

    /**
     * @param \ShipperHQ\Shipper\Model\ResourceModel\Order\GridDetail\CollectionFactory $orderDetailCollectionFactory
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \ShipperHQ\Shipper\Model\ResourceModel\Order\GridDetail\CollectionFactory $orderGridDetailCollectionFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
        $this->orderGridDetailCollection = $orderGridDetailCollectionFactory->create();
    }

    /**
     * Return model from carrier group id and address id
     *
     * @param string $carrierGroupId
     * @param string $addressId
     * @return mixed
     */
    public function loadByOrder($orderId)
    {

        $collection = $this->orderGridDetailCollection
            ->addOrderIdToFilter($orderId);

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    //@codeCoverageIgnoreStart

    /**
     * {@inheritdoc}
     */
    public function getCarrierGroup()
    {
        return $this->getData(self::CARRIER_GROUP);
    }

    /**
     * {@inheritdoc}
     */
    public function setCarrierGroup($carrierGroup)
    {
        return $this->setData(self::CARRIER_GROUP, $carrierGroup);
    }

    /**
     * {@inheritdoc}
     */
    public function getDispatchDate()
    {
        return $this->getData(self::DISPATCH_DATE);
    }

    /**
     * {@inheritdoc}
     */
    public function setDispatchDate($dispatchDate)
    {
        return $this->setData(self::DISPATCH_DATE, $dispatchDate);
    }

    /**
     * {@inheritdoc}
     */
    public function getDeliveryDate()
    {
        return $this->getData(self::DELIVERY_DATE);
    }

    /**
     * {@inheritdoc}
     */
    public function setDeliveryDate($deliveryDate)
    {
        return $this->setData(self::DELIVERY_DATE, $deliveryDate);
    }

    /**
     * {@inheritdoc}
     */
    public function getTimeSlot()
    {
        return $this->getData(self::TIME_SLOT);
    }

    /**
     * {@inheritdoc}
     */
    public function setTimeSlot($timeSlot)
    {
        return $this->setData(self::TIME_SLOT, $timeSlot);
    }

    /**
     * {@inheritdoc}
     */
    public function getPickupLocation()
    {
        return $this->getData(self::PICKUP_LOCATION);
    }

    /**
     * {@inheritdoc}
     */
    public function setPickupLocation($pickupLocation)
    {
        return $this->setData(self::PICKUP_LOCATION, $pickupLocation);
    }

    /**
     * {@inheritdoc}
     */
    public function getDeliveryComments()
    {
        return $this->getData(self::DELIVERY_COMMENTS);
    }

    /**
     * {@inheritdoc}
     */
    public function setDeliveryComments($deliveryComments)
    {
        return $this->setData(self::DELIVERY_COMMENTS, $deliveryComments);
    }

    /**
     * {@inheritdoc}
     */
    public function getDestinationType()
    {
        return $this->getData(self::DESTINATION_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function setDestinationType($destinationType)
    {
        return $this->setData(self::DESTINATION_TYPE, $destinationType);
    }

    /**
     * {@inheritdoc}
     */
    public function getLiftgateRequired()
    {
        return $this->getData(self::LIFTGATE_REQUIRED);
    }

    /**
     * {@inheritdoc}
     */
    public function setLiftgateRequired($liftgateRequired)
    {
        return $this->setData(self::LIFTGATE_REQUIRED, $liftgateRequired);
    }

    /**
     * {@inheritdoc}
     */
    public function getNotifyRequired()
    {
        return $this->getData(self::NOTIFY_REQUIRED);
    }

    /**
     * {@inheritdoc}
     */
    public function setNotifyRequired($notifyRequired)
    {
        return $this->setData(self::NOTIFY_REQUIRED, $notifyRequired);
    }

    /**
     * {@inheritdoc}
     */
    public function getInsideDelivery()
    {
        return $this->getData(self::INSIDE_DELIVERY);
    }

    /**
     * {@inheritdoc}
     */
    public function setInsideDelivery($insideDelivery)
    {
        return $this->setData(self::INSIDE_DELIVERY, $insideDelivery);
    }

    /**
     * {@inheritdoc}
     */
    public function getAddressValid()
    {
        return $this->getData(self::ADDRESS_VALID);
    }

    /**
     * {@inheritdoc}
     */
    public function setAddressValid($addressValid)
    {
        return $this->setData(self::ADDRESS_VALID, $addressValid);
    }

    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('ShipperHQ\Shipper\Model\ResourceModel\Order\GridDetail');
    }
}
