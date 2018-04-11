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

class Packages extends \Magento\Framework\Model\AbstractExtensibleModel
{
    /**
     * @var /ShipperHQ\Shipper\Model\ResourceModel\Quote\Packages\Collection
     */
    private $quotePackageCollection;

    /**
     * @param \ShipperHQ\Shipper\Model\ResourceModel\Quote\Packages\CollectionFactory $quotePackageCollectionFactory
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \ShipperHQ\Shipper\Model\ResourceModel\Quote\Packages\CollectionFactory $quotePackageCollectionFactory,
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
        $this->quotePackageCollection = $quotePackageCollectionFactory->create();
    }

    /**
     * Loads data by carrier specific parameters
     *
     * @param string $addressId
     * @param string $carrierGroupId
     * @param string $carrierCode
     * @return $this
     */
    public function loadByCarrier($addressId, $carrierGroupId, $carrierCode)
    {
        $collection = $this->quotePackageCollection
            ->addAddressToFilter($addressId)
            ->addCarrierCodeToFilter($carrierCode);
        if ($carrierGroupId !== null) {
            $collection->addCarrierGroupToFilter($carrierGroupId);
        }
        return $collection;
    }

    public function deleteByPackageId($packageId)
    {
        $this->_getResource()->deleteByPackageId($packageId);
    }

    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('ShipperHQ\Shipper\Model\ResourceModel\Quote\Packages');
    }
}
