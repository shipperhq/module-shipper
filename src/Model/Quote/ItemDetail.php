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

namespace ShipperHQ\Shipper\Model\Quote;

class ItemDetail extends \Magento\Framework\Model\AbstractExtensibleModel
{

    const QUOTE_ITEM_ID = 'quote_item_id';
    const CARRIER_GROUP_ID = 'carrier_group_id';
    const CARRIER_GROUP = 'carrier_group';
    const CARRIER_GROUP_SHIPPING = 'carrier_group_shipping';

    /**
     * @var \ShipperHQ\Shipper\Model\ResourceModel\Quote\ItemDetail\Collection
     */
    private $quoteItemDetailCollection;

    /**
     * @param \ShipperHQ\Shipper\Model\ResourceModel\Quote\AddressDetail\CollectionFactory
     * $quoteAddressDetailCollectionFactory
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \ShipperHQ\Shipper\Model\ResourceModel\Quote\ItemDetail\CollectionFactory $quoteItemDetailCollectionFactory,
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
        $this->quoteItemDetailCollection = $quoteItemDetailCollectionFactory->create();
    }

    /**
     * Return model from carrier group id and address id
     *
     * @param string $carrierGroupId
     * @param string $addressId
     * @return mixed
     */
    public function loadDetailByItemId($itemId)
    {

        $collection = $this->quoteItemDetailCollection
            ->addItemToFilter($itemId);

        foreach ($collection as $object) {
            return $object;
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getQuoteItemId()
    {
        return $this->getData(self::QUOTE_ITEM_ID);
    }

    //@codeCoverageIgnoreStart

    /**
     * {@inheritdoc}
     */
    public function getCarrierGroupId()
    {
        return $this->getData(self::CARRIER_GROUP_ID);
    }

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
    public function getCarrierGroupShipping()
    {
        return $this->getData(self::CARRIER_GROUP_SHIPPING);
    }

    /**
     * {@inheritdoc}
     */
    public function setCarrierGroupShipping($carrierGroupShipping)
    {
        return $this->setData(self::CARRIER_GROUP_SHIPPING, $carrierGroupShipping);
    }

    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('ShipperHQ\Shipper\Model\ResourceModel\Quote\ItemDetail');
    }
}
