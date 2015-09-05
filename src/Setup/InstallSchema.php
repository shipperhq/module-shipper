<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace ShipperHQ\Shipper\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();


        if(!$installer->getConnection()->isTableExists($this->getTable('shipperhq_shipper/attributeupdate'))) {
            $table = $this->getConnection()->newTable($this->getTable('shipperhq_shipper/attributeupdate'));

            $table
                ->addColumn('synch_id', Varien_Db_Ddl_Table::TYPE_INTEGER, NULL, array(
                    'primary' => true,
                    'nullable' => false,
                    'unsigned' => true,
                    'auto_increment' => true
                ))
                ->addColumn('notification_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 0, array(
                    'nullable' => false,
                    'unsigned' => true,
                ))
                ->addColumn('attribute_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, NULL, array(
                    'nullable' => false,
                ))
                ->addColumn('attribute_type', Varien_Db_Ddl_Table::TYPE_VARCHAR, NULL, array(
                    'nullable' => false,
                ))
                ->addColumn('value', Varien_Db_Ddl_Table::TYPE_VARCHAR, NULL, array(
                    'nullable' => false,
                ))
                ->addColumn('status', Varien_Db_Ddl_Table::TYPE_VARCHAR, NULL, array(
                    'nullable' => false,
                ))
                ->addColumn('date_added', Varien_Db_Ddl_Table::TYPE_DATETIME, NULL, array(
                    'nullable' => false,
                ))
            ;
            $this->getConnection()->createTable($table);
        }

        /* ------ shipperhq_shipping_group -------- */
        $this->addAttribute('catalog_product', 'shipperhq_shipping_group', array(
            'type'                     => 'varchar',
            'backend'                  => 'eav/entity_attribute_backend_array',
            'input'                    => 'multiselect',
            'label'                    => 'Shipping Group',
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
        ));

        /* ------ shipperhq_warehouse -------- */
        $this->addAttribute('catalog_product', 'shipperhq_warehouse', array(
            'type'                     => 'text',
            'backend'                  => 'eav/entity_attribute_backend_array',
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
        ));

        /* ------ shipperhq_shipping_qty -------- */
        $this->addAttribute('catalog_product', 'shipperhq_shipping_qty', array(
            'type'                     => 'int',
            'input'                    => 'text',
            'label'                    => 'Shipping Qty',
            'global'                   => false,
            'visible'                  => 1,
            'required'                 => 0,
            'visible_on_front'         => 0,
            'is_html_allowed_on_front' => 0,
            'searchable'               => 0,
            'filterable'               => 0,
            'comparable'               => 0,
            'unique'                   => false,
            'user_defined'			   => true,
            'used_in_product_listing'  => false
        ));

        /* ------ shipperhq_shipping_fee -------- */
        $this->addAttribute('catalog_product', 'shipperhq_shipping_fee', array(
            'type'                     => 'decimal',
            'input'                    => 'price',
            'label'                    => 'Shipping Fee',
            'global'                   => false,
            'visible'                  => 1,
            'required'                 => 0,
            'visible_on_front'         => 0,
            'is_html_allowed_on_front' => 0,
            'searchable'               => 0,
            'filterable'               => 0,
            'comparable'               => 0,
            'unique'                   => false,
            'user_defined'			   => true,
            'used_in_product_listing'  => false
        ));

        /* ------ shipperhq_additional_price -------- */
        $this->addAttribute('catalog_product', 'shipperhq_additional_price', array(
            'type'                     => 'decimal',
            'input'                    => 'price',
            'label'                    => 'Additional Price',
            'global'                   => false,
            'visible'                  => 1,
            'required'                 => 0,
            'visible_on_front'         => 0,
            'is_html_allowed_on_front' => 0,
            'searchable'               => 0,
            'filterable'               => 0,
            'comparable'               => 0,
            'unique'                   => false,
            'user_defined'			   => true,
            'used_in_product_listing'  => false
        ));

        /* ------ shipperhq_handling_fee -------- */
        $this->addAttribute('catalog_product', 'shipperhq_handling_fee', array(
            'type'                     => 'decimal',
            'input'                    => 'price',
            'label'                    => 'Handling Fee',
            'global'                   => false,
            'visible'                  => 1,
            'required'                 => 0,
            'visible_on_front'         => 0,
            'is_html_allowed_on_front' => 0,
            'searchable'               => 0,
            'filterable'               => 0,
            'comparable'               => 0,
            'unique'                   => false,
            'user_defined'			   => true,
            'used_in_product_listing'  => false
        ));

        /* ------ shipperhq_carrier_code -------- */
        $this->addAttribute('catalog_product', 'shipperhq_carrier_code', array(
            'type'                     => 'text',
            'input'                    => 'text',
            'label'                    => 'Carrier Code',
            'global'                   => false,
            'visible'                  => 1,
            'required'                 => 0,
            'visible_on_front'         => 0,
            'is_html_allowed_on_front' => 0,
            'searchable'               => 0,
            'filterable'               => 0,
            'comparable'               => 0,
            'unique'                   => false,
            'user_defined'			   => true,
            'used_in_product_listing'  => false
        ));

        /* ------ shipperhq_volume_weight -------- */
        $this->addAttribute('catalog_product', 'shipperhq_volume_weight', array(
            'type'                     => 'decimal',
            'input'                    => 'text',
            'label'                    => 'Volume Weight',
            'global'                   => false,
            'visible'                  => 1,
            'required'                 => 0,
            'visible_on_front'         => 0,
            'is_html_allowed_on_front' => 0,
            'searchable'               => 0,
            'filterable'               => 0,
            'comparable'               => 0,
            'unique'                   => false,
            'user_defined'			   => true,
            'used_in_product_listing'  => false,
            'note'                     => 'This value is only used in conjunction with shipping filters'
        ));

        /* ------ shipperhq_declared_value -------- */
        $this->addAttribute('catalog_product', 'shipperhq_declared_value', array(
            'type'                     => 'decimal',
            'input'                    => 'price',
            'label'                    => 'Declared Value',
            'global'                   => false,
            'visible'                  => 1,
            'required'                 => 0,
            'visible_on_front'         => 0,
            'is_html_allowed_on_front' => 0,
            'searchable'               => 0,
            'filterable'               => 0,
            'comparable'               => 0,
            'unique'                   => false,
            'user_defined'			   => true,
            'used_in_product_listing'  => false,
            'note'                     => 'The deemed cost of this product for customs & insurance purposes'
        ));

        /* ------ ship_separately -------- */
        $this->addAttribute('catalog_product', 'ship_separately', array(
            'type'                     => 'int',
            'input'                    => 'boolean',
            'label'                    => 'Ship Separately',
            'global'                   => false,
            'visible'                  => 1,
            'required'                 => 0,
            'visible_on_front'         => 0,
            'is_html_allowed_on_front' => 0,
            'searchable'               => 0,
            'filterable'               => 0,
            'comparable'               => 0,
            'unique'                   => false,
            'user_defined'			   => true,
            'used_in_product_listing'  => false
        ));

        /* ------ shipperhq_dim_group -------- */
        $this->addAttribute('catalog_product', 'shipperhq_dim_group', array(
            'type'                     => 'varchar',
            'input'                    => 'select',
            'label'                    => 'ShipperHQ Dimensional Rule Group',
            'backend'                  => 'eav/entity_attribute_backend_array',
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
            'user_defined'			   => true,
            'used_in_product_listing'  => false
        ));

        /* ------ ship_length -------- */
        $this->addAttribute('catalog_product', 'ship_length', array(
            'type'                     => 'decimal',
            'input'                    => 'text',
            'label'                    => 'Dimension Length',
            'global'                   => false,
            'visible'                  => 1,
            'required'                 => 0,
            'visible_on_front'         => 0,
            'is_html_allowed_on_front' => 0,
            'searchable'               => 0,
            'filterable'               => 0,
            'comparable'               => 0,
            'unique'                   => false,
            'user_defined'			   => true,
            'used_in_product_listing'  => false
        ));

        /* ------ ship_width -------- */
        $this->addAttribute('catalog_product', 'ship_width', array(
            'type'                     => 'decimal',
            'input'                    => 'text',
            'label'                    => 'Dimension Width',
            'global'                   => false,
            'visible'                  => 1,
            'required'                 => 0,
            'visible_on_front'         => 0,
            'is_html_allowed_on_front' => 0,
            'searchable'               => 0,
            'filterable'               => 0,
            'comparable'               => 0,
            'unique'                   => false,
            'user_defined'			   => true,
            'used_in_product_listing'  => false
        ));

        /* ------ ship_height -------- */
        $this->addAttribute('catalog_product', 'ship_height', array(
            'type'                     => 'decimal',
            'input'                    => 'text',
            'label'                    => 'Dimension Height',
            'global'                   => false,
            'visible'                  => 1,
            'required'                 => 0,
            'visible_on_front'         => 0,
            'is_html_allowed_on_front' => 0,
            'searchable'               => 0,
            'filterable'               => 0,
            'comparable'               => 0,
            'unique'                   => false,
            'user_defined'			   => true,
            'used_in_product_listing'  => false
        ));

        /* ------ shipperhq_poss_boxes -------- */
        $this->addAttribute('catalog_product', 'shipperhq_poss_boxes', array(
            'type'                     => 'varchar',
            'backend'                  => 'eav/entity_attribute_backend_array',
            'input'                    => 'multiselect',
            'label'                    => 'Possible Packing Boxes',
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
            'user_defined'			   => true,
            'used_in_product_listing'  => false
        ));

        /* ------ ship_box_tolerance -------- */
        $this->addAttribute('catalog_product', 'ship_box_tolerance', array(
            'type'                     => 'int',
            'input'                    => 'text',
            'label'                    => 'Box Tolerance',
            'global'                   => false,
            'visible'                  => 1,
            'required'                 => 0,
            'visible_on_front'         => 0,
            'is_html_allowed_on_front' => 0,
            'searchable'               => 0,
            'filterable'               => 0,
            'comparable'               => 0,
            'unique'                   => false,
            'used_in_product_listing'  => false,
            'user_defined'			   => true,
            'note'                     => 'Note: Ignore if unsure'
        ));

        /*
         *
         *
         *
         */

        $entityTypeId = $installer->getEntityTypeId('catalog_product');

        $attributeSetArr = $installer->getAllAttributeSetIds($entityTypeId);

//        if($this->shipperDataHelper->isModuleEnabled('Shipperhq_Splitrates')) {
//            $stdAttributeCodes = array('shipperhq_shipping_group' => '1', 'shipperhq_warehouse' => '10');
//        }
//        else {
            $stdAttributeCodes = array('shipperhq_shipping_group' => '1');
       // }
        $dimAttributeCodes = array('ship_separately' => '2',
            'shipperhq_dim_group' => '1',
            'ship_length' => '10',
            'ship_width' => '11',
            'ship_height' => '12',
            'shipperhq_poss_boxes' => '20',
        );

        foreach ($attributeSetArr as $attributeSetId) {

            $installer->addAttributeGroup($entityTypeId, $attributeSetId, 'Shipping', '99');
            $installer->addAttributeGroup($entityTypeId, $attributeSetId, 'Dimensional Shipping', '100');

            $attributeGroupId = $installer->getAttributeGroupId($entityTypeId, $attributeSetId, 'Shipping');
            $dimAttributeGroupId = $installer->getAttributeGroupId($entityTypeId, $attributeSetId, 'Dimensional Shipping');

            foreach($stdAttributeCodes as $code => $sort) {
                $attributeId = $installer->getAttributeId($entityTypeId, $code);
                $installer->addAttributeToGroup($entityTypeId, $attributeSetId, $attributeGroupId, $attributeId, $sort);
            }

            foreach($dimAttributeCodes as $code => $sort) {
                $attributeId = $installer->getAttributeId($entityTypeId, $code);
                $installer->addAttributeToGroup($entityTypeId, $attributeSetId, $dimAttributeGroupId, $attributeId, $sort);
            }

        };



        $text = $logger->getNewVersion() > 10 ? Varien_Db_Ddl_Table::TYPE_TEXT : 'text';

        $isCheckout = array(
            'type'  => Varien_Db_Ddl_Table::TYPE_SMALLINT,
            'comment' => 'Shipperhq Shipper',
            'nullable' => 'false',
            'default'  => '0'
        );

        $carrierType = array(
            'type' => $text,
            'comment' => 'Shipperhq Carrier Type',
            'nullable' => 'true',
        );

        $dispatchDate =  array(
            'type'    	=> $text,
            'length'	=> 20,
            'comment' 	=> 'Dispatch Date',
            'nullable' 	=> 'true');

        $deliveryDate =  array(
            'type'    	=>  $text,
            'length'	=> 20,
            'comment' 	=> 'Expected Delivery',
            'nullable' 	=> 'true');

        $carrierNotice = array(
            'type' => $text,
            'comment' => 'Shipperhq Carrier Notice',
            'nullable' => 'true',
        );

        $customDescription = array(
            'type' => $text,
            'comment' => 'Shipperhq Custom Description',
            'nullable' => 'true',
        );

        $carrierId = array(
            'type' => $text,
            'length'	=> 20,
            'comment' => 'Shipperhq Carrier ID',
            'nullable' => 'true',
        );

        $customDuties = array(
            'type'    => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'scale'     => 4,
            'precision' => 12,
            'comment' => 'Shipperhq Custom Duties',
        );

        $confirmationNo = array(
            'type' => $text,
            'length'	=> 20,
            'comment' => 'Shipperhq Confirmation Number',
            'nullable' => 'true',
        );

        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/quote_address'), 'is_checkout')){
            $installer->getConnection()->addColumn($installer->getTable('sales/quote_address'), 'is_checkout', $isCheckout);
        }

        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/quote_address'), 'carrier_type')){
            $installer->getConnection()->addColumn($installer->getTable('sales/quote_address'), 'carrier_type', $carrierType);
        }

        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/quote_address'), 'dispatch_date')){
            $installer->getConnection()->addColumn($installer->getTable('sales/quote_address'),'dispatch_date', $dispatchDate );
        }

        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/quote_address'), 'delivery_date')){
            $installer->getConnection()->addColumn($installer->getTable('sales/quote_address'),'delivery_date', $deliveryDate );
        }

        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/quote_address_shipping_rate'), 'carrier_type')){
            $installer->getConnection()->addColumn($installer->getTable('sales/quote_address_shipping_rate'), 'carrier_type', $carrierType);
        }

        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/quote_address_shipping_rate'), 'dispatch_date')){
            $installer->getConnection()->addColumn($installer->getTable('sales/quote_address_shipping_rate'),'dispatch_date', $dispatchDate );
        }

        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/quote_address_shipping_rate'), 'delivery_date')){
            $installer->getConnection()->addColumn($installer->getTable('sales/quote_address_shipping_rate'),'delivery_date', $deliveryDate );
        }

        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/quote_address_shipping_rate'), 'carrier_notice')){
            $installer->getConnection()->addColumn($installer->getTable('sales/quote_address_shipping_rate'), 'carrier_notice', $carrierNotice);
        }

        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/quote_address_shipping_rate'), 'custom_description')){
            $installer->getConnection()->addColumn($installer->getTable('sales/quote_address_shipping_rate'), 'custom_description', $customDescription);
        }

        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/quote_address_shipping_rate'), 'carrier_id')){
            $installer->getConnection()->addColumn($installer->getTable('sales/quote_address_shipping_rate'), 'carrier_id', $carrierId);
        }

        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/quote_address'), 'carrier_id')){
            $installer->getConnection()->addColumn($installer->getTable('sales/quote_address'),'carrier_id', $carrierId );
        }

        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/order'), 'carrier_id')){
            $installer->getConnection()->addColumn($installer->getTable('sales/order'),'carrier_id', $carrierId );
        }

        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/order'), 'confirmation_number')){
            $installer->getConnection()->addColumn($installer->getTable('sales/order'),'confirmation_number', $confirmationNo );
        }

        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/quote_address_shipping_rate'), 'custom_duties')){
            $installer->getConnection()->addColumn($installer->getTable('sales/quote_address_shipping_rate'), 'custom_duties', $customDuties);
        }

        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/order'), 'carrier_type')){
            $installer->getConnection()->addColumn($installer->getTable('sales/order'),'carrier_type', $carrierType );
        }

        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/order'), 'dispatch_date')){
            $installer->getConnection()->addColumn($installer->getTable('sales/order'),'dispatch_date', $dispatchDate );
        }

        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/order'), 'delivery_date')){
            $installer->getConnection()->addColumn($installer->getTable('sales/order'),'delivery_date', $deliveryDate );
        }

        /*carrier group attributes*/
        $version = $logger->getNewVersion();

        $carriergroupAttr = array(
            'type' => $version > 10 ? Varien_Db_Ddl_Table::TYPE_TEXT : Varien_Db_Ddl_Table::TYPE_VARCHAR,
            'comment' => 'Carrier Group',
            'nullable' => 'true',
        );

        $carriergroupID  = array(
            'type' => $version > 10 ? Varien_Db_Ddl_Table::TYPE_TEXT : Varien_Db_Ddl_Table::TYPE_VARCHAR,
            'comment' => 'Carrier Group ID',
            'nullable' => 'true',
        );

        $carriergroupDetails = array(
            'type' => $version > 10 ? Varien_Db_Ddl_Table::TYPE_TEXT : Varien_Db_Ddl_Table::TYPE_VARCHAR,
            'comment' => 'Carrier Group Details',
            'nullable' => 'true',
        );

        $carriergroupHtml = array(
            'type' => $version > 10 ? Varien_Db_Ddl_Table::TYPE_TEXT : Varien_Db_Ddl_Table::TYPE_VARCHAR,
            'comment' => 'Carrier Group Html',
            'nullable' => 'true',
        );

        $displayMerged = array(
            'type'  => Varien_Db_Ddl_Table::TYPE_SMALLINT,
            'comment' => 'Checkout display type',
            'nullable' => 'false',
            'default'  => '1'
        );

        $splitRates = array(
            'type'  => Varien_Db_Ddl_Table::TYPE_SMALLINT,
            'comment' => 'Shipperhq Split Rates',
            'nullable' => 'false',
            'default'  => '0'
        );

        $carriergroupShipping = array(
            'type' => $version > 10 ? Varien_Db_Ddl_Table::TYPE_TEXT : Varien_Db_Ddl_Table::TYPE_VARCHAR,
            'comment' => 'Shipping Description',
            'nullable' => 'true',
        );

        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/quote_address'), 'carriergroup_shipping_details')){
            $installer->getConnection()->addColumn($installer->getTable('sales/quote_address'), 'carriergroup_shipping_details', $carriergroupDetails);
        }
        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/quote_address'), 'carriergroup_shipping_html')){
            $installer->getConnection()->addColumn($installer->getTable('sales/quote_address'), 'carriergroup_shipping_html', $carriergroupHtml);
        }
        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/quote_address'), 'checkout_display_merged')){
            $installer->getConnection()->addColumn($installer->getTable('sales/quote_address'), 'checkout_display_merged', $displayMerged);
        }
        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/quote_address'), 'split_rates')){
            $installer->getConnection()->addColumn($installer->getTable('sales/quote_address'), 'split_rates', $splitRates);
        }

        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/quote_address_shipping_rate'), 'carriergroup_id')){
            $installer->getConnection()->addColumn($installer->getTable('sales/quote_address_shipping_rate'), 'carriergroup_id', $carriergroupID);
        }
        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/quote_address_shipping_rate'), 'carriergroup')){
            $installer->getConnection()->addColumn($installer->getTable('sales/quote_address_shipping_rate'), 'carriergroup', $carriergroupAttr);
        }
        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/quote_address_shipping_rate'), 'carriergroup_shipping_details')){
            $installer->getConnection()->addColumn($installer->getTable('sales/quote_address_shipping_rate'), 'carriergroup_shipping_details', $carriergroupDetails);
        }

        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/quote_item'), 'carriergroup')){
            $installer->getConnection()->addColumn($installer->getTable('sales/quote_item'), 'carriergroup', $carriergroupAttr);
        }
        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/order_item'), 'carriergroup')){
            $installer->getConnection()->addColumn($installer->getTable('sales/order_item'), 'carriergroup', $carriergroupAttr);
        }
        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/quote_item'), 'carriergroup_id')){
            $installer->getConnection()->addColumn($installer->getTable('sales/quote_item'), 'carriergroup_id', $carriergroupID);
        }
        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/order_item'), 'carriergroup_id')){
            $installer->getConnection()->addColumn($installer->getTable('sales/order_item'), 'carriergroup_id', $carriergroupID);
        }
        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/quote_item'), 'carriergroup_shipping')){
            $installer->getConnection()->addColumn($installer->getTable('sales/quote_item'), 'carriergroup_shipping', $carriergroupShipping);
        }
        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/order_item'), 'carriergroup_shipping')){
            $installer->getConnection()->addColumn($installer->getTable('sales/order_item'), 'carriergroup_shipping', $carriergroupShipping);
        }
        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/quote_address_item'), 'carriergroup_shipping')){
            $installer->getConnection()->addColumn($installer->getTable('sales/quote_address_item'), 'carriergroup_shipping', $carriergroupShipping);
        }
        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/quote_address_item'), 'carriergroup')){
            $installer->getConnection()->addColumn($installer->getTable('sales/quote_address_item'), 'carriergroup', $carriergroupAttr);
        }
        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/quote_address_item'), 'carriergroup_id')){
            $installer->getConnection()->addColumn($installer->getTable('sales/quote_address_item'), 'carriergroup_id', $carriergroupID);
        }


        if ($logger->getNewVersion() >= 8) {
            if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/shipment_grid'), 'carriergroup')){
                $installer->getConnection()->addColumn($installer->getTable('sales/shipment_grid'),'carriergroup',$carriergroupAttr);
            }
            if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/shipment'), 'carriergroup')){
                $installer->getConnection()->addColumn($installer->getTable('sales/shipment'),'carriergroup',$carriergroupAttr);
            }
            if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/shipment'), 'shipping_description')){
                $installer->getConnection()->addColumn($installer->getTable('sales/shipment'),'shipping_description',array(
                    'type'    	=> Varien_Db_Ddl_Table::TYPE_TEXT,
                    'length'	=> 255,
                    'comment' 	=> 'Shipping Description',
                    'nullable' 	=> 'true',));
            }
            if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/order'), 'carriergroup_shipping_html')){
                $installer->getConnection()->addColumn($installer->getTable('sales/order'), 'carriergroup_shipping_html', $carriergroupHtml);
            }
            if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/order'), 'carriergroup_shipping_details')){
                $installer->getConnection()->addColumn($installer->getTable('sales/order'), 'carriergroup_shipping_details', $carriergroupDetails);
            }
            if  ($logger->isEnterpriseEdition() && $installer->tableExists('enterprise_sales_shipment_grid_archive')) {
                if(!$installer->getConnection()->tableColumnExists($installer->getTable('enterprise_sales_shipment_grid_archive'), 'carriergroup')){
                    $installer->getConnection()->addColumn($installer->getTable('enterprise_sales_shipment_grid_archive'),'carriergroup',$carriergroupAttr);
                }
            }
        }

        if(!$installer->getConnection()->isTableExists($this->getTable('shipperhq_shipper/storage'))) {
            $table = $this->getConnection()->newTable($this->getTable('shipperhq_shipper/storage'));

            $table
                ->addColumn('quote_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                    'primary' => true,
                    'nullable' => false,
                    'unsigned' => true
                ))
                ->addColumn('data', Varien_Db_Ddl_Table::TYPE_TEXT, '512k', array('nullable' => false))
                ->addForeignKey(
                    $this->getFkName('shipperhq_shipper/storage', 'quote_id', 'sales/quote', 'entity_id'),
                    'quote_id',
                    $this->getTable('sales/quote'),
                    'entity_id',
                    Varien_Db_Adapter_Interface::FK_ACTION_CASCADE
                )
            ;
            $this->getConnection()->createTable($table);
        }

        if(!$installer->getConnection()->isTableExists($this->getTable('shipperhq_shipper/quote_packages'))) {
            $table = $this->getConnection()->newTable($this->getTable('shipperhq_shipper/quote_packages'));

            $table
                ->addColumn('package_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                    'primary' => true,
                    'nullable' => false,
                    'unsigned' => true,
                    'auto_increment' => true
                ))
                ->addColumn('address_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                    'nullable' => false,
                    'unsigned' => true
                ))
                ->addColumn('carrier_group_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
                    'nullable' => false,
                ))
                ->addColumn('carrier_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
                    'nullable' => false,
                ))
                ->addColumn('package_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
                    'nullable' => false,
                ))
                ->addColumn('length', Varien_Db_Ddl_Table::TYPE_FLOAT , array(
                    'nullable' => true
                ))
                ->addColumn('width', Varien_Db_Ddl_Table::TYPE_FLOAT , array(
                    'nullable' => true
                ))
                ->addColumn('height', Varien_Db_Ddl_Table::TYPE_FLOAT , array(
                    'nullable' => true
                ))
                ->addColumn('weight', Varien_Db_Ddl_Table::TYPE_FLOAT , array(
                    'nullable' => true
                ))
                ->addColumn('declared_value', Varien_Db_Ddl_Table::TYPE_FLOAT , array(
                    'nullable' => true
                ))
                ->addColumn('surcharge_price', Varien_Db_Ddl_Table::TYPE_FLOAT , array(
                    'nullable' => true
                ))
                ->addForeignKey(
                    $this->getFkName('shipperhq_shipper/quote_packages', 'address_id', 'sales/quote_address', 'address_id'),
                    'address_id',
                    $this->getTable('sales/quote_address'),
                    'address_id',
                    Varien_Db_Adapter_Interface::FK_ACTION_CASCADE
                )
            ;
            $this->getConnection()->createTable($table);
        }

        if(!$installer->getConnection()->isTableExists($this->getTable('shipperhq_shipper/quote_package_items'))) {
            $table = $this->getConnection()->newTable($this->getTable('shipperhq_shipper/quote_package_items'));

            $table
                ->addColumn('package_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                    'nullable' => false,
                ))
                ->addColumn('sku', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
                    'nullable' => false,
                ))
                ->addColumn('qty_packed', Varien_Db_Ddl_Table::TYPE_FLOAT , array(
                    'nullable' => true
                ))
                ->addColumn('weight_packed', Varien_Db_Ddl_Table::TYPE_FLOAT , array(
                    'nullable' => true
                ))
                ->addForeignKey(
                    $this->getFkName('shipperhq_shipper/quote_package_items', 'package_id', 'shipperhq_shipper/quote_packages', 'package_id'),
                    'package_id',
                    $this->getTable('shipperhq_shipper/quote_packages'),
                    'package_id',
                    Varien_Db_Adapter_Interface::FK_ACTION_CASCADE
                )
            ;
            $this->getConnection()->createTable($table);
        }

        if(!$installer->getConnection()->isTableExists($this->getTable('shipperhq_shipper/order_packages'))) {
            $table = $this->getConnection()->newTable($this->getTable('shipperhq_shipper/order_packages'));

            $table
                ->addColumn('package_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                    'primary' => true,
                    'nullable' => false,
                    'unsigned' => true,
                    'auto_increment' => true
                ))
                ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                    'nullable' => false,
                    'unsigned' => true
                ))
                ->addColumn('carrier_group_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
                    'nullable' => false,
                ))
                ->addColumn('package_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
                    'nullable' => false,
                ))
                ->addColumn('length', Varien_Db_Ddl_Table::TYPE_FLOAT , array(
                    'nullable' => true
                ))
                ->addColumn('width', Varien_Db_Ddl_Table::TYPE_FLOAT , array(
                    'nullable' => true
                ))
                ->addColumn('height', Varien_Db_Ddl_Table::TYPE_FLOAT , array(
                    'nullable' => true
                ))
                ->addColumn('weight', Varien_Db_Ddl_Table::TYPE_FLOAT , array(
                    'nullable' => true
                ))
                ->addColumn('declared_value', Varien_Db_Ddl_Table::TYPE_FLOAT , array(
                    'nullable' => true
                ))
                ->addColumn('surcharge_price', Varien_Db_Ddl_Table::TYPE_FLOAT , array(
                    'nullable' => true
                ))
                ->addForeignKey(
                    $this->getFkName('shipperhq_shipper/order_packages', 'order_id', 'sales/order', 'entity_id'),
                    'order_id',
                    $this->getTable('sales/order'),
                    'entity_id',
                    Varien_Db_Adapter_Interface::FK_ACTION_CASCADE
                )
            ;
            $this->getConnection()->createTable($table);
        }

        if(!$installer->getConnection()->isTableExists($this->getTable('shipperhq_shipper/order_package_items'))) {
            $table = $this->getConnection()->newTable($this->getTable('shipperhq_shipper/order_package_items'));

            $table
                ->addColumn('package_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                    'nullable' => false,
                    'unsigned' => true,
                ))
                ->addColumn('sku', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
                    'nullable' => false,
                ))
                ->addColumn('qty_packed', Varien_Db_Ddl_Table::TYPE_FLOAT , array(
                    'nullable' => true
                ))
                ->addColumn('weight_packed', Varien_Db_Ddl_Table::TYPE_FLOAT , array(
                    'nullable' => true
                ))
                ->addForeignKey(
                    $this->getFkName('shipperhq_shipper/order_package_items', 'package_id', 'shipperhq_shipper/order_packages', 'package_id'),
                    'package_id',
                    $this->getTable('shipperhq_shipper/order_packages'),
                    'package_id',
                    Varien_Db_Adapter_Interface::FK_ACTION_CASCADE
                )
            ;
            $this->getConnection()->createTable($table);
        }

        $installer->endSetup();

    }
}