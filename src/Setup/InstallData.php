<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace ShipperHQ\Shipper\Setup;

//use Magento\Framework\Setup\InstallDataInterface;

//use Magento\Framework\Setup\InstallSchemaInterface;
//use Magento\Framework\Setup\ModuleContextInterface;
//use Magento\Framework\Setup\SchemaSetupInterface;

use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Sales\Setup\SalesSetupFactory;

/**
 * @codeCoverageIgnore
 */
//class InstallSchema implements InstallSchemaInterface
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
     * Init
     *
     * @param CategorySetupFactory $categorySetupFactory
     * @param QuoteSetupFactory $quoteSetupFactory
     * @param SalesSetupFactory $salesSetupFactory
     */
    public function __construct(
        CategorySetupFactory $categorySetupFactory,
        QuoteSetupFactory $quoteSetupFactory,
        SalesSetupFactory $salesSetupFactory
    ) {
        $this->categorySetupFactory = $categorySetupFactory;
        $this->quoteSetupFactory = $quoteSetupFactory;
        $this->salesSetupFactory = $salesSetupFactory;
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
            'type'                     => 'text',
          //  'backend'                  => 'eav/entity_attribute_backend_array',
            'input'                    => 'multiselect',
            'label'                    => 'Shipping Group',
            'global' => \Magento\Catalog\Model\Resource\Eav\Attribute::SCOPE_STORE,
            'visible'                  => true,
            'required'                 => false,
            'visible_on_front'         => false,
            'is_html_allowed_on_front' => false,
            'searchable'               => false,
            'filterable'               => false,
            'comparable'               => false,
            'is_configurable'          => false,
            'unique'                   => false,
            'user_defined'			   => false,
            'used_in_product_listing'  => false
        ]);

        /* ------ shipperhq_warehouse -------- */
        $catalogSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'shipperhq_warehouse', [
            'type'                     => 'text',
       //     'backend'                  => 'eav/entity_attribute_backend_array',
            'input'                    => 'multiselect',
            'label'                    => 'Origin',
            'global'                   => false,
            'visible'                  => 1,
            'required'                 => 0,
            'visible_on_front'         => 0,
            'is_html_allowed_on_front' => 0,
            'searchable'               => 0,
            'filterable'               => 0,
            'comparable'               => 0,
            'is_configurable'          => 0,
            'unique'                   => false,
            'user_defined'			   => false,
            'used_in_product_listing'  => false
        ]);


        $entityTypeId = $catalogSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);

        $attributeSetArr = $catalogSetup->getAllAttributeSetIds($entityTypeId);


        $stdAttributeCodes = ['shipperhq_shipping_group' => '1',  'shipperhq_warehouse' => '10'];


        foreach ($attributeSetArr as $attributeSetId) {

            $catalogSetup->addAttributeGroup($entityTypeId, $attributeSetId, 'Shipping', '99');

            $attributeGroupId = $catalogSetup->getAttributeGroupId($entityTypeId, $attributeSetId, 'Shipping');

            foreach($stdAttributeCodes as $code => $sort) {
                $attributeId = $catalogSetup->getAttributeId($entityTypeId, $code);
                $catalogSetup->addAttributeToGroup($entityTypeId, $attributeSetId, $attributeGroupId, $attributeId, $sort);
            }

        };

        $options = ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'visible' => false, 'required' => false, 'comment' => 'ShipperHQ Carrier Type'];
        $entities = ['quote_address'];
        foreach ($entities as $entity) {
            /** @var \Magento\Quote\Setup\QuoteSetup $quoteSetup */
            $quoteSetup = $this->quoteSetupFactory->create(['setup' => $setup]);
            $quoteSetup->addAttribute($entity, 'carrier_type', $options);
        }

        $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);
        $salesSetup->addAttribute('order', 'carrier_type', $options);

        $options = ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'visible' => false, 'required' => false, 'comment' => 'ShipperHQ Carrier ID'];
        $entities = ['quote_shipping_rate', 'quote_address'];
        foreach ($entities as $entity) {
            /** @var \Magento\Quote\Setup\QuoteSetup $quoteSetup */
            $quoteSetup = $this->quoteSetupFactory->create(['setup' => $setup]);
            $quoteSetup->addAttribute($entity, 'carrier_id', $options);
        }
        $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);
        $salesSetup->addAttribute('order', 'carrier_id', $options);


//        $text = Varien_Db_Ddl_Table::TYPE_TEXT;

//        $isCheckout = [
//            'type'  => Varien_Db_Ddl_Table::TYPE_SMALLINT,
//            'comment' => 'ShipperHQ Shipper',
//            'nullable' => 'false',
//            'default'  => '0'
//        ];


//        $carrierId = [
//            'type' => $text,
//            'length'	=> 20,
//            'comment' => 'ShipperHQ Carrier ID',
//            'nullable' => 'true',
//        ];


//        $carriergroupAttr = [
//            'type' => $text,
//            'comment' => 'Carrier Group',
//            'nullable' => 'true',
//        ];
//
//        $carriergroupID  = [
//            'type' => $text,
//            'comment' => 'Carrier Group ID',
//            'nullable' => 'true',
//        ];
//
//        $carriergroupDetails = [
//            'type' => $text,
//            'comment' => 'Carrier Group Details',
//            'nullable' => 'true',
//        ];
//
//        $carriergroupHtml = [
//            'type' => $text,
//            'comment' => 'Carrier Group Html',
//            'nullable' => 'true',
//        ];

//        $displayMerged = [
//            'type'  => Varien_Db_Ddl_Table::TYPE_SMALLINT,
//            'comment' => 'Checkout display type',
//            'nullable' => 'false',
//            'default'  => '1'
//        ];
//
//        $carriergroupShipping = [
//            'type' => $text,
//            'comment' => 'Shipping Description',
//            'nullable' => 'true',
//        ];

//        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/quote_address'), 'is_checkout')){
//            $installer->getConnection()->addColumn($installer->getTable('sales/quote_address'), 'is_checkout', $isCheckout);
//        }

//        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/quote_address'), 'carrier_type')){
//            $installer->getConnection()->addColumn($installer->getTable('sales/quote_address'), 'carrier_type', $carrierType);
//        }

//        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/quote_address_shipping_rate'), 'carrier_id')){
//            $installer->getConnection()->addColumn($installer->getTable('sales/quote_address_shipping_rate'), 'carrier_id', $carrierId);
//        }
//

//        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/quote_address'), 'carrier_id')){
//            $installer->getConnection()->addColumn($installer->getTable('sales/quote_address'),'carrier_id', $carrierId );
//        }

//        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/order'), 'carrier_id')){
//            $installer->getConnection()->addColumn($installer->getTable('sales/order'),'carrier_id', $carrierId );
//        }


//        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/order'), 'carrier_type')){
//            $installer->getConnection()->addColumn($installer->getTable('sales/order'),'carrier_type', $carrierType );
//        }

//        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/quote_address'), 'carriergroup_shipping_details')){
//            $installer->getConnection()->addColumn($installer->getTable('sales/quote_address'), 'carriergroup_shipping_details', $carriergroupDetails);
//        }
//        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/quote_address'), 'carriergroup_shipping_html')){
//            $installer->getConnection()->addColumn($installer->getTable('sales/quote_address'), 'carriergroup_shipping_html', $carriergroupHtml);
//        }
//        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/quote_address'), 'checkout_display_merged')){
//            $installer->getConnection()->addColumn($installer->getTable('sales/quote_address'), 'checkout_display_merged', $displayMerged);
//        }

//        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/quote_address_shipping_rate'), 'carriergroup_id')){
//            $installer->getConnection()->addColumn($installer->getTable('sales/quote_address_shipping_rate'), 'carriergroup_id', $carriergroupID);
//        }
//        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/quote_address_shipping_rate'), 'carriergroup')){
//            $installer->getConnection()->addColumn($installer->getTable('sales/quote_address_shipping_rate'), 'carriergroup', $carriergroupAttr);
//        }
//        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/quote_address_shipping_rate'), 'carriergroup_shipping_details')){
//            $installer->getConnection()->addColumn($installer->getTable('sales/quote_address_shipping_rate'), 'carriergroup_shipping_details', $carriergroupDetails);
//        }


//        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/quote_item'), 'carriergroup')){
//            $installer->getConnection()->addColumn($installer->getTable('sales/quote_item'), 'carriergroup', $carriergroupAttr);
//        }
//        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/order_item'), 'carriergroup')){
//            $installer->getConnection()->addColumn($installer->getTable('sales/order_item'), 'carriergroup', $carriergroupAttr);
//        }
//        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/quote_item'), 'carriergroup_id')){
//            $installer->getConnection()->addColumn($installer->getTable('sales/quote_item'), 'carriergroup_id', $carriergroupID);
//        }
//        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/order_item'), 'carriergroup_id')){
//            $installer->getConnection()->addColumn($installer->getTable('sales/order_item'), 'carriergroup_id', $carriergroupID);
//        }
//        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/quote_item'), 'carriergroup_shipping')){
//            $installer->getConnection()->addColumn($installer->getTable('sales/quote_item'), 'carriergroup_shipping', $carriergroupShipping);
//        }
//        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/order_item'), 'carriergroup_shipping')){
//            $installer->getConnection()->addColumn($installer->getTable('sales/order_item'), 'carriergroup_shipping', $carriergroupShipping);
//        }
//        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/quote_address_item'), 'carriergroup_shipping')){
//            $installer->getConnection()->addColumn($installer->getTable('sales/quote_address_item'), 'carriergroup_shipping', $carriergroupShipping);
//        }
//        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/quote_address_item'), 'carriergroup')){
//            $installer->getConnection()->addColumn($installer->getTable('sales/quote_address_item'), 'carriergroup', $carriergroupAttr);
//        }
//        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/quote_address_item'), 'carriergroup_id')){
//            $installer->getConnection()->addColumn($installer->getTable('sales/quote_address_item'), 'carriergroup_id', $carriergroupID);
//        }
//        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/order'), 'carriergroup_shipping_html')){
//            $installer->getConnection()->addColumn($installer->getTable('sales/order'), 'carriergroup_shipping_html', $carriergroupHtml);
//        }
//        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/order'), 'carriergroup_shipping_details')){
//            $installer->getConnection()->addColumn($installer->getTable('sales/order'), 'carriergroup_shipping_details', $carriergroupDetails);
//        }

   //     $installer->endSetup();

    }
}