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

namespace ShipperHQ\Shipper\Setup;

use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Customer\Setup\CustomerSetupFactory;

use Magento\Framework\Setup\UpgradeDataInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * Category setup factory
     *
     * @var CategorySetupFactory
     */
    protected $categorySetupFactory;

    /**
     * Quote setup factory
     *
     * @var QuoteSetupFactory
     */
    protected $quoteSetupFactory;

    /**
     * Sales setup factory
     *
     * @var SalesSetupFactory
     */
    protected $salesSetupFactory;
    /**
     * Customer setup factory
     *
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * @param SalesSetupFactory $salesSetupFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     */
    public function __construct(
        CategorySetupFactory $categorySetupFactory,
        QuoteSetupFactory $quoteSetupFactory,
        SalesSetupFactory $salesSetupFactory,
        CustomerSetupFactory $customerSetupFactory
    ) {
        $this->categorySetupFactory = $categorySetupFactory;
        $this->quoteSetupFactory = $quoteSetupFactory;
        $this->salesSetupFactory = $salesSetupFactory;
        $this->customerSetupFactory = $customerSetupFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $catalogSetup = $this->categorySetupFactory->create(['setup' => $setup]);

        /* ------ shipperhq_shipping_fee -------- */
        $catalogSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'shipperhq_shipping_fee', [
            'type'                     => 'decimal',
            'backend' => 'Magento\Catalog\Model\Product\Attribute\Backend\Price',
            'input'                    => 'price',
            'label'                    => 'Shipping Fee',
            'global' =>\Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            'visible'                  => true,
            'required'                 => false,
            'visible_on_front'         => false,
            'is_html_allowed_on_front' => false,
            'searchable'               => false,
            'filterable'               => false,
            'comparable'               => false,
            'is_configurable'          => false,
            'unique'                   => false,
            'user_defined'			   => true,
            'used_in_product_listing'  => false
        ]);



        /* ------ shipperhq_handling_fee -------- */
        $catalogSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'shipperhq_handling_fee', [
            'type'                     => 'decimal',
            'backend' => 'Magento\Catalog\Model\Product\Attribute\Backend\Price',
            'input'                    => 'price',
            'label'                    => 'Handling Fee',
            'global' =>\Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            'visible'                  => true,
            'required'                 => false,
            'visible_on_front'         => false,
            'is_html_allowed_on_front' => false,
            'searchable'               => false,
            'filterable'               => false,
            'comparable'               => false,
            'is_configurable'          => false,
            'unique'                   => false,
            'user_defined'			   => true,
            'used_in_product_listing'  => false
        ]);

        /* ------ shipperhq_volume_weight -------- */
        $catalogSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'shipperhq_volume_weight', [
            'type'                      => 'varchar',
            'input'                    => 'text',
            'label'                    => 'Volume Weight',
            'global' =>\Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            'visible'                  => true,
            'required'                 => false,
            'visible_on_front'         => false,
            'is_html_allowed_on_front' => false,
            'searchable'               => false,
            'filterable'               => false,
            'comparable'               => false,
            'is_configurable'          => false,
            'unique'                   => false,
            'user_defined'			   => true,
            'used_in_product_listing'  => false,
            'note'                     => 'This value is only used in conjunction with shipping filters'
        ]);

        /* ------ shipperhq_declared_value -------- */
        $catalogSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'shipperhq_declared_value', [
            'type'                     => 'decimal',
            'backend' => 'Magento\Catalog\Model\Product\Attribute\Backend\Price',
            'input'                    => 'price',
            'label'                    => 'Declared Value',
            'global' =>\Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            'visible'                  => true,
            'required'                 => false,
            'visible_on_front'         => false,
            'is_html_allowed_on_front' => false,
            'searchable'               => false,
            'filterable'               => false,
            'comparable'               => false,
            'is_configurable'          => false,
            'unique'                   => false,
            'user_defined'			   => true,
            'used_in_product_listing'  => false,
            'note'                     => 'The deemed cost of this product for customs & insurance purposes'
        ]);

        /* ------ ship_separately -------- */
        $catalogSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'ship_separately', [
            'type'                     => 'int',
            'input'                     => 'boolean',
            'label'                    => 'Ship Separately',
            'global' =>\Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            'visible'                  => true,
            'required'                 => false,
            'visible_on_front'         => false,
            'is_html_allowed_on_front' => false,
            'searchable'               => false,
            'filterable'               => false,
            'comparable'               => false,
            'is_configurable'          => false,
            'unique'                   => false,
            'user_defined'			   => true,
            'used_in_product_listing'  => false
        ]);

        /* ------ shipperhq_dim_group -------- */
        $catalogSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'shipperhq_dim_group', [
            'type'                     => 'int',
            'backend'                  => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
            'frontend'                 => '',
            'label'                    => 'ShipperHQ Dimensional Rule Group',
            'input'                    => 'select',
            'global' =>\Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            'visible'                  => true,
            'required'                 => false,
            'visible_on_front'         => false,
            'is_html_allowed_on_front' => false,
            'searchable'               => false,
            'filterable'               => false,
            'comparable'               => false,
            'is_configurable'          => false,
            'unique'                   => false,
            'user_defined'			   => true,
            'used_in_product_listing'  => false
            ]);
        /* ------ ship_length -------- */
        $catalogSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'ship_length', [
            'type'                      => 'decimal',
            'input'                    => 'text',
            'label'                    => 'Dimension Length',
            'global' =>\Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            'visible'                  => true,
            'required'                 => false,
            'visible_on_front'         => false,
            'is_html_allowed_on_front' => false,
            'searchable'               => false,
            'filterable'               => false,
            'comparable'               => false,
            'is_configurable'          => false,
            'unique'                   => false,
            'user_defined'			   => true,
            'used_in_product_listing'  => false
        ]);

        /* ------ ship_width -------- */
        $catalogSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'ship_width', [
            'type'                      => 'decimal',
            'input'                    => 'text',
            'label'                    => 'Dimension Width',
            'global' =>\Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            'visible'                  => true,
            'required'                 => false,
            'visible_on_front'         => false,
            'is_html_allowed_on_front' => false,
            'searchable'               => false,
            'filterable'               => false,
            'comparable'               => false,
            'is_configurable'          => false,
            'unique'                   => false,
            'user_defined'			   => true,
            'used_in_product_listing'  => false
        ]);

        /* ------ ship_height -------- */
        $catalogSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'ship_height', [
            'type'                      => 'decimal',
            'input'                    => 'text',
            'label'                    => 'Dimension Height',
            'global' =>\Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            'visible'                  => true,
            'required'                 => false,
            'visible_on_front'         => false,
            'is_html_allowed_on_front' => false,
            'searchable'               => false,
            'filterable'               => false,
            'comparable'               => false,
            'is_configurable'          => false,
            'unique'                   => false,
            'user_defined'			   => true,
            'used_in_product_listing'  => false
        ]);

        /* ------ shipperhq_poss_boxes -------- */
        $catalogSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'shipperhq_poss_boxes', [
            'type'                     => 'text',
            'backend'                  => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
            'input'                    => 'multiselect',
            'label'                    => 'Possible Packing Boxes',
            'global'                   => false,
            'visible'                  => true,
            'required'                 => false,
            'visible_on_front'         => false,
            'is_html_allowed_on_front' => false,
            'searchable'               => false,
            'filterable'               => false,
            'comparable'               => false,
            'is_configurable'          => false,
            'unique'                   => false,
            'user_defined'			   => true,
            'used_in_product_listing'  => false
        ]);

        /* ------ shipperhq_malleable_product -------- */
        $catalogSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'shipperhq_malleable_product', [
            'type'                     => 'int',
            'input'                     => 'boolean',
            'label'                    => 'Malleable Product',
            'global' =>\Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            'visible'                  => true,
            'required'                 => false,
            'visible_on_front'         => false,
            'is_html_allowed_on_front' => false,
            'searchable'               => false,
            'filterable'               => false,
            'comparable'               => false,
            'is_configurable'          => false,
            'unique'                   => false,
            'user_defined'			   => true,
            'used_in_product_listing'  => false,
            'note'                     => 'Ignore if unsure. Indicates the product dimensions can be adjusted to fit box',
        ]);

        /* ------ shipperhq_master_boxes -------- */
        $catalogSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'shipperhq_master_boxes', [
            'type'                     => 'text',
            'backend'                  => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
            'input'                    => 'multiselect',
            'label'                    => 'Master Packing Boxes',
            'global'                   => false,
            'visible'                  => true,
            'required'                 => false,
            'visible_on_front'         => false,
            'is_html_allowed_on_front' => false,
            'searchable'               => false,
            'filterable'               => false,
            'comparable'               => false,
            'is_configurable'          => false,
            'unique'                   => false,
            'user_defined'			   => true,
            'used_in_product_listing'  => false
        ]);

        $entityTypeId = $catalogSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);

        $attributeSetArr = $catalogSetup->getAllAttributeSetIds($entityTypeId);

        $dimAttributeCodes = ['ship_separately' => '2',
            'shipperhq_dim_group' => '1',
            'ship_length' => '10',
            'ship_width' => '11',
            'ship_height' => '12',
            'shipperhq_poss_boxes' => '20'];


        foreach ($attributeSetArr as $attributeSetId) {

            $catalogSetup->addAttributeGroup($entityTypeId, $attributeSetId, 'Dimensional Shipping', '100');

            $attributeGroupId = $catalogSetup->getAttributeGroupId($entityTypeId, $attributeSetId, 'Dimensional Shipping');

            foreach($dimAttributeCodes as $code => $sort) {
                $attributeId = $catalogSetup->getAttributeId($entityTypeId, $code);
                $catalogSetup->addAttributeToGroup($entityTypeId, $attributeSetId, $attributeGroupId, $attributeId, $sort);
            }

        };


        $catalogSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'shipperhq_availability_date', [
            'type'                     => 'datetime',
            'backend'                  => 'Magento\Eav\Model\Entity\Attribute\Backend\Datetime',
            'input'                    => 'date',
            'label'                    => 'Availability Date',
            'global'                   => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            'visible'                  => true,
            'required'                 => false,
            'visible_on_front'         => false,
            'is_html_allowed_on_front' => false,
            'searchable'               => false,
            'filterable'               => false,
            'comparable'               => false,
            'is_configurable'          => false,
            'unique'                   => false,
            'user_defined'			   => true,
            'used_in_product_listing'  => false
        ]);

        /** @var \Magento\Quote\Setup\QuoteSetup $quoteSetup */
        $quoteSetup = $this->quoteSetupFactory->create(['setup' => $setup]);
        $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

        $destinationTypeAttr = ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'visible' => false, 'required' => false, 'comment' => 'ShipperHQ Address Type'];
        $quoteSetup->addAttribute('quote_address' , 'destination_type', $destinationTypeAttr);
        $salesSetup->addAttribute('order', 'destination_type', $destinationTypeAttr);

        $destinationTypeAddressAttr = [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            'label' => 'Address Type',

            'system' => 0, // <-- important, otherwise values aren't saved.
                            // @see Magento\Customer\Model\Metadata\AddressMetadata::getCustomAttributesMetadata()
//            'visible' => false,
            'required' => false,
            'position' => 100,
            'comment' => 'ShipperHQ Address Type'
        ];
        $customerSetup->addAttribute('customer_address', 'destination_type', $destinationTypeAddressAttr);


        $addressValiationStatus = ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'visible' => false, 'required' => false, 'comment' => 'ShipperHQ Address Validation Status'];
        $quoteSetup->addAttribute('quote_address' , 'validation_status', $addressValiationStatus);
        $salesSetup->addAttribute('order', 'validation_status', $addressValiationStatus);

        $validationStatusAddressAttr = [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            'label' => 'Address Validation',

            'system' => 0, // <-- important, otherwise values aren't saved.
            // @see Magento\Customer\Model\Metadata\AddressMetadata::getCustomAttributesMetadata()
//            'visible' => false,
            'required' => false,
            'position' => 101,
            'comment' => 'ShipperHQ Address Validation Status'
        ];
        $customerSetup->addAttribute('customer_address', 'validation_status', $validationStatusAddressAttr);

        // add attribute to form
        /** @var  $attribute */
        $attribute = $customerSetup->getEavConfig()->getAttribute('customer_address', 'validation_status');
        $attribute->setData('used_in_forms', ['adminhtml_customer_address']);
        $attribute->save();

        $attribute = $customerSetup->getEavConfig()->getAttribute('customer_address', 'destination_type');
        $attribute->setData('used_in_forms', ['adminhtml_customer_address']);
        $attribute->save();

        //1.0.7
        $dispatchDateAttr = ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATE, 'visible' => false, 'required' => false, 'comment' => 'ShipperHQ Address Type'];
        $quoteSetup->addAttribute('quote_address_rate' , 'shq_dispatch_date', $dispatchDateAttr);
        $deliveryDateAttr = ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATE, 'visible' => false, 'required' => false, 'comment' => 'ShipperHQ Address Type'];
        $quoteSetup->addAttribute('quote_address_rate' , 'shq_delivery_date', $deliveryDateAttr);

        $installer->endSetup();

    }
}
