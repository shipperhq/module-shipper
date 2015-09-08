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


        $entityTypeId = $installer->getEntityTypeId('catalog_product');

        $attributeSetArr = $installer->getAllAttributeSetIds($entityTypeId);


        $stdAttributeCodes = array('shipperhq_shipping_group' => '1',  'shipperhq_warehouse' => '10');


        foreach ($attributeSetArr as $attributeSetId) {

            $installer->addAttributeGroup($entityTypeId, $attributeSetId, 'Shipping', '99');

            $attributeGroupId = $installer->getAttributeGroupId($entityTypeId, $attributeSetId, 'Shipping');

            foreach($stdAttributeCodes as $code => $sort) {
                $attributeId = $installer->getAttributeId($entityTypeId, $code);
                $installer->addAttributeToGroup($entityTypeId, $attributeSetId, $attributeGroupId, $attributeId, $sort);
            }

        };

        $text = Varien_Db_Ddl_Table::TYPE_TEXT;

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

        $carrierId = array(
            'type' => $text,
            'length'	=> 20,
            'comment' => 'Shipperhq Carrier ID',
            'nullable' => 'true',
        );


        $carriergroupAttr = array(
            'type' => $text,
            'comment' => 'Carrier Group',
            'nullable' => 'true',
        );

        $carriergroupID  = array(
            'type' => $text,
            'comment' => 'Carrier Group ID',
            'nullable' => 'true',
        );

        $carriergroupDetails = array(
            'type' => $text,
            'comment' => 'Carrier Group Details',
            'nullable' => 'true',
        );

        $carriergroupHtml = array(
            'type' => $text,
            'comment' => 'Carrier Group Html',
            'nullable' => 'true',
        );

        $displayMerged = array(
            'type'  => Varien_Db_Ddl_Table::TYPE_SMALLINT,
            'comment' => 'Checkout display type',
            'nullable' => 'false',
            'default'  => '1'
        );

        $carriergroupShipping = array(
            'type' => $text,
            'comment' => 'Shipping Description',
            'nullable' => 'true',
        );

        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/quote_address'), 'is_checkout')){
            $installer->getConnection()->addColumn($installer->getTable('sales/quote_address'), 'is_checkout', $isCheckout);
        }

        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/quote_address'), 'carrier_type')){
            $installer->getConnection()->addColumn($installer->getTable('sales/quote_address'), 'carrier_type', $carrierType);
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


        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/order'), 'carrier_type')){
            $installer->getConnection()->addColumn($installer->getTable('sales/order'),'carrier_type', $carrierType );
        }

        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/quote_address'), 'carriergroup_shipping_details')){
            $installer->getConnection()->addColumn($installer->getTable('sales/quote_address'), 'carriergroup_shipping_details', $carriergroupDetails);
        }
        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/quote_address'), 'carriergroup_shipping_html')){
            $installer->getConnection()->addColumn($installer->getTable('sales/quote_address'), 'carriergroup_shipping_html', $carriergroupHtml);
        }
        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/quote_address'), 'checkout_display_merged')){
            $installer->getConnection()->addColumn($installer->getTable('sales/quote_address'), 'checkout_display_merged', $displayMerged);
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
        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/order'), 'carriergroup_shipping_html')){
            $installer->getConnection()->addColumn($installer->getTable('sales/order'), 'carriergroup_shipping_html', $carriergroupHtml);
        }
        if(!$installer->getConnection()->tableColumnExists($installer->getTable('sales/order'), 'carriergroup_shipping_details')){
            $installer->getConnection()->addColumn($installer->getTable('sales/order'), 'carriergroup_shipping_details', $carriergroupDetails);
        }

        $installer->endSetup();

    }
}