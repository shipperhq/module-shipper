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
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Sales\Setup\SalesSetupFactory;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
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
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    private $configStorageWriter;
    /**
     * Customer setup factory
     *
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * Init
     *
     * @param CategorySetupFactory $categorySetupFactory
     * @param QuoteSetupFactory $quoteSetupFactory
     * @param SalesSetupFactory $salesSetupFactory
     * @param \Magento\Framework\App\Config\MutableScopeConfigInterface $mutableConfig
     * @param CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(
        CategorySetupFactory $categorySetupFactory,
        QuoteSetupFactory $quoteSetupFactory,
        SalesSetupFactory $salesSetupFactory,
        \Magento\Framework\App\Config\Storage\WriterInterface $configStorageWriter,
        CustomerSetupFactory $customerSetupFactory
    ) {
        $this->categorySetupFactory = $categorySetupFactory;
        $this->quoteSetupFactory = $quoteSetupFactory;
        $this->salesSetupFactory = $salesSetupFactory;
        $this->configStorageWriter = $configStorageWriter;
        $this->customerSetupFactory = $customerSetupFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {

        $installer = $setup;

        $installer->startSetup();

        $catalogSetup = $this->categorySetupFactory->create(['setup' => $setup]);

        /* ------ shipperhq_shipping_group -------- */
        $catalogSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'shipperhq_shipping_group', [
            'type' => 'text',
            'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
            'input' => 'multiselect',
            'label' => 'Shipping Group',
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            'visible' => true,
            'required' => false,
            'visible_on_front' => false,
            'is_html_allowed_on_front' => false,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'is_configurable' => false,
            'unique' => false,
            'user_defined' => true,
            'used_in_product_listing' => false
        ]);

        /* ------ shipperhq_warehouse -------- */
        $catalogSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'shipperhq_warehouse', [
            'type' => 'text',
            'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
            'input' => 'multiselect',
            'label' => 'Origin',
            'global' => false,
            'visible' => true,
            'required' => false,
            'visible_on_front' => false,
            'is_html_allowed_on_front' => false,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'is_configurable' => false,
            'unique' => false,
            'user_defined' => true,
            'used_in_product_listing' => false
        ]);

        $entityTypeId = $catalogSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);

        $attributeSetArr = $catalogSetup->getAllAttributeSetIds($entityTypeId);

        $stdAttributeCodes = ['shipperhq_shipping_group' => '1', 'shipperhq_warehouse' => '10'];

        foreach ($attributeSetArr as $attributeSetId) {
            //SHQ16-2123 handle migrated instances from M1 to M2
            $migrated = $catalogSetup->getAttributeGroup($entityTypeId, $attributeSetId, 'migration-shipping');
            if ($migrated !== false) {
                $catalogSetup->removeAttributeGroup($entityTypeId, $attributeSetId, 'migration-shipping');
            }

            $catalogSetup->addAttributeGroup($entityTypeId, $attributeSetId, 'Shipping', '99');

            $attributeGroupId = $catalogSetup->getAttributeGroupId($entityTypeId, $attributeSetId, 'Shipping');

            foreach ($stdAttributeCodes as $code => $sort) {
                $attributeId = $catalogSetup->getAttributeId($entityTypeId, $code);
                $catalogSetup->addAttributeToGroup(
                    $entityTypeId,
                    $attributeSetId,
                    $attributeGroupId,
                    $attributeId,
                    $sort
                );
            }
        };

        /** @var \Magento\Quote\Setup\QuoteSetup $quoteSetup */
        $quoteSetup = $this->quoteSetupFactory->create(['setup' => $setup]);
        $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);

        $carrier_type = [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            'visible' => false,
            'required' => false,
            'comment' => 'ShipperHQ Carrier Type'
        ];
        $entities = ['quote_address', 'quote_address_rate'];
        foreach ($entities as $entity) {
            $quoteSetup->addAttribute($entity, 'carrier_type', $carrier_type);
        }
        $salesSetup->addAttribute('order', 'carrier_type', $carrier_type);

        $carrier_id = [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            'visible' => false,
            'required' => false,
            'comment' => 'ShipperHQ Carrier ID'
        ];
        $entities = ['quote_address_rate', 'quote_address'];
        foreach ($entities as $entity) {
            $quoteSetup->addAttribute($entity, 'carrier_id', $carrier_id);
        }
        $salesSetup->addAttribute('order', 'carrier_id', $carrier_id);

        $carrier_group_id = [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            'visible' => false,
            'required' => false,
            'comment' => 'Carrier Group ID'
        ];
        $entities = ['quote_address_rate', 'quote_item', 'quote_address_item'];
        foreach ($entities as $entity) {
            $quoteSetup->addAttribute($entity, 'carriergroup_id', $carrier_group_id);
        }
        $salesSetup->addAttribute('order_item', 'carriergroup_id', $carrier_group_id);

        $carrier_group = [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            'visible' => false,
            'required' => false,
            'comment' => 'ShipperHQ Carrier Group'
        ];
        $entities = ['quote_address_rate', 'quote_item', 'quote_address_item'];
        foreach ($entities as $entity) {
            $quoteSetup->addAttribute($entity, 'carriergroup', $carrier_group);
        }
        $salesSetup->addAttribute('order_item', 'carriergroup', $carrier_group);

        $carrierGroupDetails = [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            'visible' => false,
            'required' => false,
            'comment' => 'ShipperHQ Carrier Group Details'
        ];
        $entities = ['quote_address_rate', 'quote_address'];
        foreach ($entities as $entity) {
            $quoteSetup->addAttribute($entity, 'carriergroup_shipping_details', $carrierGroupDetails);
        }
        $salesSetup->addAttribute('order', 'carriergroup_shipping_details', $carrierGroupDetails);

        $isCheckout = [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            'visible' => false,
            'required' => true,
            'default' => 0,
            'comment' => 'ShipperHQ Checkout Flag'
        ];
        $quoteSetup->addAttribute('quote_address', 'is_checkout', $isCheckout);

        $splitRates = [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            'visible' => false,
            'required' => true,
            'default' => 0,
            'comment' => 'ShipperHQ Split Rates Flag'
        ];
        $quoteSetup->addAttribute('quote_address', 'split_rates', $splitRates);

        $displayMerged = [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            'visible' => false,
            'required' => true,
            'default' => 1,
            'comment' => 'ShipperHQ Checkout Display Type'
        ];
        $quoteSetup->addAttribute('quote_address', 'checkout_display_merged', $displayMerged);

        $carriergroupHtml = [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            'visible' => false,
            'required' => false,
            'comment' => 'ShipperHQ Carrier Group HTML'
        ];
        $quoteSetup->addAttribute('quote_address', 'carriergroup_shipping_html', $carriergroupHtml);
        $salesSetup->addAttribute('order', 'carriergroup_shipping_html', $carriergroupHtml);

        $carriergroupShipping = [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            'visible' => false,
            'required' => false,
            'comment' => 'ShipperHQ Shipping Description'
        ];
        $entities = ['quote_item', 'quote_address_item'];
        foreach ($entities as $entity) {
            $quoteSetup->addAttribute($entity, 'carriergroup_shipping', $carriergroupShipping);
        }
        $salesSetup->addAttribute('order_item', 'carriergroup_shipping', $carriergroupShipping);

        $this->configStorageWriter->save('carriers/shipper/ignore_empty_zip', 1);

        $this->installAttributes($catalogSetup);

        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

        $destinationTypeAttr = [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            'visible' => false,
            'required' => false,
            'comment' => 'ShipperHQ Address Type'
        ];
        $quoteSetup->addAttribute('quote_address', 'destination_type', $destinationTypeAttr);
        $salesSetup->addAttribute('order', 'destination_type', $destinationTypeAttr);

        $destinationTypeAddressAttr = [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            'label' => 'Address Type',
            'input' => 'select',
            'source_model' => 'ShipperHQ\Shipper\Model\Customer\Attribute\Source\AddressType',
            'system' => 0, // <-- important, otherwise values aren't saved.
            // @see Magento\Customer\Model\Metadata\AddressMetadata::getCustomAttributesMetadata()
            //            'visible' => false,
            'source_model' => 'ShipperHQ\Shipper\Model\Customer\Attribute\Source\AddressType',
            'required' => false,
            'position' => 100,
            'comment' => 'ShipperHQ Address Type'
        ];
        $customerSetup->addAttribute('customer_address', 'destination_type', $destinationTypeAddressAttr);

        $addressValiationStatus = [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            'visible' => false,
            'required' => false,
            'comment' => 'ShipperHQ Address Validation Status'
        ];
        $quoteSetup->addAttribute('quote_address', 'validation_status', $addressValiationStatus);
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

        $dispatchDateAttr = [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
            'visible' => false,
            'required' => false,
            'comment' => 'ShipperHQ Address Type'
        ];
        $quoteSetup->addAttribute('quote_address_rate', 'shq_dispatch_date', $dispatchDateAttr);
        $deliveryDateAttr = [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
            'visible' => false,
            'required' => false,
            'comment' => 'ShipperHQ Address Type'
        ];
        $quoteSetup->addAttribute('quote_address_rate', 'shq_delivery_date', $deliveryDateAttr);

        $this->installFreightAttributes($catalogSetup);
    }

    private function installAttributes($catalogSetup)
    {
        /* ------ shipperhq_shipping_fee -------- */
        $catalogSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'shipperhq_shipping_fee', [
            'type' => 'decimal',
            'backend' => 'Magento\Catalog\Model\Product\Attribute\Backend\Price',
            'input' => 'price',
            'label' => 'Shipping Fee',
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            'visible' => true,
            'required' => false,
            'visible_on_front' => false,
            'is_html_allowed_on_front' => false,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'is_configurable' => false,
            'unique' => false,
            'user_defined' => true,
            'used_in_product_listing' => false
        ]);

        /* ------ shipperhq_handling_fee -------- */
        $catalogSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'shipperhq_handling_fee', [
            'type' => 'decimal',
            'backend' => 'Magento\Catalog\Model\Product\Attribute\Backend\Price',
            'input' => 'price',
            'label' => 'Handling Fee',
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            'visible' => true,
            'required' => false,
            'visible_on_front' => false,
            'is_html_allowed_on_front' => false,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'is_configurable' => false,
            'unique' => false,
            'user_defined' => true,
            'used_in_product_listing' => false
        ]);

        /* ------ shipperhq_volume_weight -------- */
        $catalogSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'shipperhq_volume_weight', [
            'type' => 'varchar',
            'input' => 'text',
            'label' => 'Volume Weight',
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            'visible' => true,
            'required' => false,
            'visible_on_front' => false,
            'is_html_allowed_on_front' => false,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'is_configurable' => false,
            'unique' => false,
            'user_defined' => true,
            'used_in_product_listing' => false,
            'note' => 'This value is only used in conjunction with shipping filters'
        ]);

        /* ------ shipperhq_declared_value -------- */
        $catalogSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'shipperhq_declared_value', [
            'type' => 'decimal',
            'backend' => 'Magento\Catalog\Model\Product\Attribute\Backend\Price',
            'input' => 'price',
            'label' => 'Declared Value',
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            'visible' => true,
            'required' => false,
            'visible_on_front' => false,
            'is_html_allowed_on_front' => false,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'is_configurable' => false,
            'unique' => false,
            'user_defined' => true,
            'used_in_product_listing' => false,
            'note' => 'The deemed cost of this product for customs & insurance purposes'
        ]);

        /* ------ ship_separately -------- */
        $catalogSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'ship_separately', [
            'type' => 'int',
            'input' => 'boolean',
            'label' => 'Ship Separately',
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            'visible' => true,
            'required' => false,
            'visible_on_front' => false,
            'is_html_allowed_on_front' => false,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'is_configurable' => false,
            'unique' => false,
            'user_defined' => true,
            'used_in_product_listing' => false
        ]);

        /* ------ shipperhq_dim_group -------- */
        $catalogSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'shipperhq_dim_group', [
            'type' => 'int',
            'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
            'frontend' => '',
            'label' => 'ShipperHQ Dimensional Rule Group',
            'input' => 'select',
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            'visible' => true,
            'required' => false,
            'visible_on_front' => false,
            'is_html_allowed_on_front' => false,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'is_configurable' => false,
            'unique' => false,
            'user_defined' => true,
            'used_in_product_listing' => false
        ]);
        /* ------ ship_length -------- */
        $catalogSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'ship_length', [
            'type' => 'decimal',
            'input' => 'text',
            'label' => 'Dimension Length',
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            'visible' => true,
            'required' => false,
            'visible_on_front' => false,
            'is_html_allowed_on_front' => false,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'is_configurable' => false,
            'unique' => false,
            'user_defined' => true,
            'used_in_product_listing' => false
        ]);

        /* ------ ship_width -------- */
        $catalogSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'ship_width', [
            'type' => 'decimal',
            'input' => 'text',
            'label' => 'Dimension Width',
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            'visible' => true,
            'required' => false,
            'visible_on_front' => false,
            'is_html_allowed_on_front' => false,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'is_configurable' => false,
            'unique' => false,
            'user_defined' => true,
            'used_in_product_listing' => false
        ]);

        /* ------ ship_height -------- */
        $catalogSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'ship_height', [
            'type' => 'decimal',
            'input' => 'text',
            'label' => 'Dimension Height',
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            'visible' => true,
            'required' => false,
            'visible_on_front' => false,
            'is_html_allowed_on_front' => false,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'is_configurable' => false,
            'unique' => false,
            'user_defined' => true,
            'used_in_product_listing' => false
        ]);

        /* ------ shipperhq_poss_boxes -------- */
        $catalogSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'shipperhq_poss_boxes', [
            'type' => 'text',
            'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
            'input' => 'multiselect',
            'label' => 'Possible Packing Boxes',
            'global' => false,
            'visible' => true,
            'required' => false,
            'visible_on_front' => false,
            'is_html_allowed_on_front' => false,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'is_configurable' => false,
            'unique' => false,
            'user_defined' => true,
            'used_in_product_listing' => false
        ]);

        /* ------ shipperhq_malleable_product -------- */
        $catalogSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'shipperhq_malleable_product', [
            'type' => 'int',
            'input' => 'boolean',
            'label' => 'Malleable Product',
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            'visible' => true,
            'required' => false,
            'visible_on_front' => false,
            'is_html_allowed_on_front' => false,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'is_configurable' => false,
            'unique' => false,
            'user_defined' => true,
            'used_in_product_listing' => false,
            'note' => 'Ignore if unsure. Indicates the product dimensions can be adjusted to fit box',
        ]);

        /* ------ shipperhq_master_boxes -------- */
        $catalogSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'shipperhq_master_boxes', [
            'type' => 'text',
            'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
            'input' => 'multiselect',
            'label' => 'Master Packing Boxes',
            'global' => false,
            'visible' => true,
            'required' => false,
            'visible_on_front' => false,
            'is_html_allowed_on_front' => false,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'is_configurable' => false,
            'unique' => false,
            'user_defined' => true,
            'used_in_product_listing' => false
        ]);

        $entityTypeId = $catalogSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);

        $attributeSetArr = $catalogSetup->getAllAttributeSetIds($entityTypeId);

        $dimAttributeCodes = [
            'ship_separately' => '2',
            'shipperhq_dim_group' => '1',
            'ship_length' => '10',
            'ship_width' => '11',
            'ship_height' => '12',
            'shipperhq_poss_boxes' => '20'
        ];

        foreach ($attributeSetArr as $attributeSetId) {
            //SHQ16-2123 handle migrated instances from M1 to M2
            $migrated = $catalogSetup->getAttributeGroup(
                $entityTypeId,
                $attributeSetId,
                'migration-dimensional-shipping'
            );
            if ($migrated !== false) {
                $catalogSetup->removeAttributeGroup($entityTypeId, $attributeSetId, 'migration-dimensional-shipping');
            }

            $attributeGroupId = $catalogSetup->getAttributeGroup(
                $entityTypeId,
                $attributeSetId,
                'Dimensional Shipping'
            );

            if (!$attributeGroupId) {
                $catalogSetup->addAttributeGroup($entityTypeId, $attributeSetId, 'Dimensional Shipping', '100');
            }

            $attributeGroupId = $catalogSetup->getAttributeGroupId(
                $entityTypeId,
                $attributeSetId,
                'Dimensional Shipping'
            );

            foreach ($dimAttributeCodes as $code => $sort) {
                $attributeId = $catalogSetup->getAttributeId($entityTypeId, $code);
                $catalogSetup->addAttributeToGroup(
                    $entityTypeId,
                    $attributeSetId,
                    $attributeGroupId,
                    $attributeId,
                    $sort
                );
            }
        };

        $catalogSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'shipperhq_availability_date', [
            'type' => 'datetime',
            'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\Datetime',
            'input' => 'date',
            'label' => 'Availability Date',
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            'visible' => true,
            'required' => false,
            'visible_on_front' => false,
            'is_html_allowed_on_front' => false,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'is_configurable' => false,
            'unique' => false,
            'user_defined' => true,
            'used_in_product_listing' => false
        ]);
    }

    private function installFreightAttributes($catalogSetup)
    {
        /* ------ freight_class -------- */
        $catalogSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'freight_class', [
            'type' => 'int',
            'source' => 'ShipperHQ\Shipper\Model\Product\Attribute\Source\FreightClass',
            'input' => 'select',
            'label' => 'Freight Class',
            'global' => false,
            'visible' => true,
            'required' => false,
            'visible_on_front' => false,
            'is_html_allowed_on_front' => false,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'is_configurable' => false,
            'unique' => false,
            'user_defined' => true,
            'used_in_product_listing' => false
        ]);
        /* ------ shipperhq_nmfc_class -------- */
        $catalogSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'shipperhq_nmfc_class', [
            'type' => 'text',
            'input' => 'text',
            'label' => 'NMFC',
            'global' => false,
            'visible' => true,
            'required' => false,
            'visible_on_front' => false,
            'is_html_allowed_on_front' => false,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'is_configurable' => false,
            'unique' => false,
            'user_defined' => true,
            'used_in_product_listing' => false
        ]);
        /* ------ must_ship_freight -------- */
        $catalogSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'must_ship_freight', [
            'type' => 'int',
            'input' => 'boolean',
            'label' => 'Must Ship Freight',
            'global' => false,
            'visible' => true,
            'required' => false,
            'visible_on_front' => false,
            'is_html_allowed_on_front' => false,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'is_configurable' => false,
            'unique' => false,
            'user_defined' => true,
            'used_in_product_listing' => false,
            'note' => 'Can be overridden at Carrier level within ShipperHQ'
        ]);
        /* ------ shipperhq_nmfc_sub -------- */
        $catalogSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'shipperhq_nmfc_sub', [
            'type' => 'text',
            'input' => 'text',
            'label' => 'NMFC Sub',
            'global' => false,
            'visible' => true,
            'required' => false,
            'visible_on_front' => false,
            'is_html_allowed_on_front' => false,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'is_configurable' => false,
            'unique' => false,
            'user_defined' => true,
            'used_in_product_listing' => false,
            'note' => 'Only required to support ABF Freight'
        ]);

        $entityTypeId = $catalogSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);

        $attributeSetArr = $catalogSetup->getAllAttributeSetIds($entityTypeId);

        $freightAttributeCodes = [
            'freight_class' => '1',
            'must_ship_freight' => '10'
        ];

        foreach ($attributeSetArr as $attributeSetId) {
            //SHQ16-2123 handle migrated instances from M1 to M2
            $migrated = $catalogSetup->getAttributeGroup($entityTypeId, $attributeSetId, 'migration-freight-shipping');
            if ($migrated !== false) {
                $catalogSetup->removeAttributeGroup($entityTypeId, $attributeSetId, 'migration-freight-shipping');
            }

            $attributeGroupId = $catalogSetup->getAttributeGroup(
                $entityTypeId,
                $attributeSetId,
                'Freight Shipping'
            );

            if (!$attributeGroupId) {
                $catalogSetup->addAttributeGroup($entityTypeId, $attributeSetId, 'Freight Shipping', '101');
            }

            $attributeGroupId = $catalogSetup->getAttributeGroupId(
                $entityTypeId,
                $attributeSetId,
                'Freight Shipping'
            );

            foreach ($freightAttributeCodes as $code => $sort) {
                $attributeId = $catalogSetup->getAttributeId($entityTypeId, $code);
                $catalogSetup->addAttributeToGroup(
                    $entityTypeId,
                    $attributeSetId,
                    $attributeGroupId,
                    $attributeId,
                    $sort
                );
            }
        };
    }
}
