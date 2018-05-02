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

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * SHQ16-2375
     * Declare connection name to support split database architecture in EE
     * connectes to 'sales' database; falls back to default for a standard installation
     * @var string
     */
    private static $connectionName = 'sales';

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        //SHQ16-2375 use standard connection, not sales
        if (!$installer->getConnection()->isTableExists($installer->getTable('shipperhq_synchronize'))) {
            $table = $installer->getConnection()->newTable($installer->getTable('shipperhq_synchronize'));
            $table->addColumn(
                'synch_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['primary' => true, 'nullable' => false, 'unsigned' => true, 'auto_increment' => true],
                'Synch ID'
            )->addColumn(
                'attribute_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Attribute code'
            )->addColumn(
                'attribute_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Type of synch data'
            )->addColumn(
                'value',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Synchronize data value'
            )->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Synch status'
            )->addColumn(
                'date_added',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                ['nullable' => false],
                'Synch entry date stamp'
            )->addIndex(
                $installer->getIdxName('shipperhq_synchronize', ['synch_id']),
                ['synch_id']
            )->setComment(
                'ShipperHQ Synchronize data table'
            );

            $installer->getConnection()->createTable($table);
        }

        $quoteAddressDetailTable = $installer->getTable('shipperhq_quote_address_detail');
        if (!$installer->getConnection(self::$connectionName)->isTableExists($quoteAddressDetailTable)) {
            $table = $installer->getConnection(self::$connectionName)->newTable($quoteAddressDetailTable);
            $table
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['primary' => true, 'nullable' => false, 'unsigned' => true, 'auto_increment' => true]
                )->addColumn(
                    'quote_address_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    50,
                    ['nullable' => false],
                    'address id'
                )->addColumn(
                    'carrier_group_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => false],
                    'carrier group id'
                )->addColumn(
                    'carrier_type',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Carrier Type'
                )->addColumn(
                    'carrier_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Carrier ID'
                )->addColumn(
                    'carrier_group',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Carrier Group'
                )->addColumn(
                    'carrier_group_detail',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Carrier Group Detail'
                )->addColumn(
                    'carrier_group_html',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Carrier Group Information Formatted'
                )->addColumn(
                    'dispatch_date',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                    null,
                    ['nullable' => true],
                    'Dispatch Date'
                )->addColumn(
                    'delivery_date',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                    null,
                    ['nullable' => true],
                    'Delivery Date'
                )->addColumn(
                    'time_slot',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Time Slot'
                )->addColumn(
                    'pickup_location',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Pickup Location'
                )->addColumn(
                    'pickup_location_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Pickup Location ID'
                )->addColumn(
                    'pickup_latitude',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Pickup Latitude'
                )->addColumn(
                    'pickup_longitude',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Pickup Longitude'
                )->addColumn(
                    'pickup_email',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Pickup Email'
                )->addColumn(
                    'pickup_contact',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Pickup Contact Name'
                )->addColumn(
                    'pickup_email_option',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Pickup Email Option'
                )->addColumn(
                    'is_checkout',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Checkout flag'
                )->addColumn(
                    'delivery_comments',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Delivery Comments'
                )->addColumn(
                    'destination_type',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Destination Type'
                )->addColumn(
                    'liftgate_required',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Liftgate Required'
                )->addColumn(
                    'notify_required',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Notify Required'
                )->addColumn(
                    'inside_delivery',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Inside Delivery'
                )->addColumn(
                    'freight_quote_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Freight Quote ID'
                )->addColumn(
                    'customer_carrier',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Customer Carrier'
                )->addColumn(
                    'customer_carrier_account',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Customer Carrier Account Number'
                )->addColumn(
                    'customer_carrier_ph',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Customer Carrier Phone Number'
                )->addColumn(
                    'address_valid',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Address Valid Status'
                )->addColumn(
                    'limited_delivery',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    10,
                    ['nullable' => true],
                    'Limited Delivery'
                )->addColumn(
                    'validated_shipping_street',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'Validated Shipping Street'
                )->addColumn(
                    'validated_shipping_street2',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'Validated Shipping Street 2'
                )->addColumn(
                    'validated_shipping_city',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    40,
                    ['nullable' => true],
                    'Validated Shipping City'
                )->addColumn(
                    'validated_shipping_postcode',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    20,
                    ['nullable' => true],
                    'Validated Shipping Postcode'
                )->addColumn(
                    'validated_shipping_region',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    40,
                    ['nullable' => true],
                    'Validated Shipping Region'
                )->addColumn(
                    'validated_shipping_country',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    30,
                    ['nullable' => true],
                    'Validated Shipping Country'
                )->addIndex(
                    $installer->getIdxName('shipperhq_quote_address_detail', ['quote_address_id']),
                    ['quote_address_id']
                )->setComment(
                    'ShipperHQ Quote Carrier Group Information'
                );
            //Foreign key to quote address table - if permitted
            $installer->getConnection(self::$connectionName)->createTable($table);
        }

        $orderDetailTable = $installer->getTable('shipperhq_order_detail');
        if (!$installer->getConnection(self::$connectionName)->isTableExists($orderDetailTable)) {
            $table = $installer->getConnection(self::$connectionName)->newTable($orderDetailTable);
            $table
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['primary' => true, 'nullable' => false, 'unsigned' => true, 'auto_increment' => true]
                )->addColumn(
                    'order_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    10,
                    ['nullable' => false, 'default' => '0', 'unsigned' => true],
                    'Order ID'
                )->addColumn(
                    'carrier_group_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => false],
                    'carrier group id'
                )->addColumn(
                    'carrier_type',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Carrier Type'
                )->addColumn(
                    'carrier_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Carrier ID'
                )->addColumn(
                    'carrier_group',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Carrier Group'
                )->addColumn(
                    'carrier_group_detail',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Carrier Group Detail'
                )->addColumn(
                    'carrier_group_html',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Carrier Group Information Formatted'
                )->addColumn(
                    'dispatch_date',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                    null,
                    ['nullable' => true],
                    'Dispatch Date'
                )->addColumn(
                    'delivery_date',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                    null,
                    ['nullable' => true],
                    'Delivery Date'
                )->addColumn(
                    'time_slot',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Time Slot'
                )->addColumn(
                    'pickup_location',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Pickup Location'
                )->addColumn(
                    'pickup_location_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Pickup Location ID'
                )->addColumn(
                    'pickup_latitude',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Pickup Latitude'
                )->addColumn(
                    'pickup_longitude',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Pickup Longitude'
                )->addColumn(
                    'pickup_email',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Pickup Email'
                )->addColumn(
                    'pickup_contact',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Pickup Contact Name'
                )->addColumn(
                    'pickup_email_option',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Pickup Email Option'
                )->addColumn(
                    'delivery_comments',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Delivery Comments'
                )->addColumn(
                    'destination_type',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Destination Type'
                )->addColumn(
                    'liftgate_required',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Liftgate Required'
                )->addColumn(
                    'notify_required',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Notify Required'
                )->addColumn(
                    'inside_delivery',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Inside Delivery'
                )->addColumn(
                    'freight_quote_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Freight Quote ID'
                )->addColumn(
                    'customer_carrier',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Customer Carrier'
                )->addColumn(
                    'customer_carrier_account',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Customer Carrier Account Number'
                )->addColumn(
                    'customer_carrier_ph',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Customer Carrier Phone Number'
                )->addColumn(
                    'address_valid',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Address Valid Status'
                )->addColumn(
                    'limited_delivery',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    10,
                    ['nullable' => true],
                    'Limited Delivery'
                )->addColumn(
                    'validated_shipping_street',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'Validated Shipping Street'
                )->addColumn(
                    'validated_shipping_street2',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'Validated Shipping Street 2'
                )->addColumn(
                    'validated_shipping_city',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    40,
                    ['nullable' => true],
                    'Validated Shipping City'
                )->addColumn(
                    'validated_shipping_postcode',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    20,
                    ['nullable' => true],
                    'Validated Shipping Postcode'
                )->addColumn(
                    'validated_shipping_region',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    40,
                    ['nullable' => true],
                    'Validated Shipping Region'
                )->addColumn(
                    'validated_shipping_country',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    30,
                    ['nullable' => true],
                    'Validated Shipping Country'
                )->addIndex(
                    $installer->getIdxName('shipperhq_order_detail', ['order_id']),
                    ['order_id']
                )->setComment(
                    'ShipperHQ Order Carrier Group Information'
                );
            $installer->getConnection(self::$connectionName)->createTable($table);
        }

        $quoteItemDetailTable = $installer->getTable('shipperhq_quote_item_detail');
        if (!$installer->getConnection(self::$connectionName)->isTableExists($quoteItemDetailTable)) {
            $table = $installer->getConnection(self::$connectionName)->newTable($quoteItemDetailTable);
            $table
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['primary' => true, 'nullable' => false, 'unsigned' => true, 'auto_increment' => true]
                )->addColumn(
                    'quote_item_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    50,
                    ['nullable' => false, 'default' => ''],
                    'Quote Item ID'
                )->addColumn(
                    'carrier_group_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => false],
                    'carrier group id'
                )->addColumn(
                    'carrier_group',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Carrier Group'
                )->addColumn(
                    'carrier_group_shipping',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Shipping Details'
                )->addIndex(
                    $installer->getIdxName('shipperhq_quote_item_detail', ['quote_item_id']),
                    ['quote_item_id']
                )->setComment(
                    'ShipperHQ Quote Item Carrier Group Information'
                );

            $installer->getConnection(self::$connectionName)->createTable($table);
        }

        $quoteAddressItemDetailTable = $installer->getTable('shipperhq_quote_address_item_detail');
        if (!$installer->getConnection(self::$connectionName)->isTableExists($quoteAddressItemDetailTable)) {
            $table = $installer->getConnection(self::$connectionName)->newTable($quoteAddressItemDetailTable);
            $table
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['primary' => true, 'nullable' => false, 'unsigned' => true, 'auto_increment' => true]
                )->addColumn(
                    'quote_address_item_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    50,
                    ['nullable' => false, 'default' => ''],
                    'Quote Address Item ID'
                )->addColumn(
                    'carrier_group_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => false],
                    'carrier group id'
                )->addColumn(
                    'carrier_group',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Carrier Group'
                )->addColumn(
                    'carrier_group_shipping',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Shipping Details'
                )->addIndex(
                    $installer->getIdxName('shipperhq_quote_address_item_detail', ['quote_address_item_id']),
                    ['quote_address_item_id']
                )->setComment(
                    'ShipperHQ Quote Address Item Carrier Group Information'
                );

            $installer->getConnection(self::$connectionName)->createTable($table);
        }

        $orderItemDetailTable = $installer->getTable('shipperhq_order_item_detail');
        if (!$installer->getConnection(self::$connectionName)->isTableExists($orderItemDetailTable)) {
            $table = $installer->getConnection(self::$connectionName)->newTable($orderItemDetailTable);
            $table
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['primary' => true, 'nullable' => false, 'unsigned' => true, 'auto_increment' => true]
                )->addColumn(
                    'order_item_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    50,
                    ['nullable' => false, 'default' => ''],
                    'Order Item ID'
                )->addColumn(
                    'carrier_group_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => false],
                    'carrier group id'
                )->addColumn(
                    'carrier_group',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Carrier Group'
                )->addColumn(
                    'carrier_group_shipping',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Shipping Details'
                )->addIndex(
                    $installer->getIdxName('shipperhq_order_item_detail', ['order_item_id']),
                    ['order_item_id']
                )->setComment(
                    'ShipperHQ Order Item Carrier Group Information'
                );

            $installer->getConnection(self::$connectionName)->createTable($table);
        }

        $quotePackagesTable = $installer->getTable('shipperhq_quote_packages');
        if (!$installer->getConnection(self::$connectionName)->isTableExists($quotePackagesTable)) {
            $table = $installer->getConnection(self::$connectionName)->newTable($quotePackagesTable);

            $table
                ->addColumn(
                    'package_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['primary' => true, 'nullable' => false, 'unsigned' => true, 'auto_increment' => true]
                )->addColumn(
                    'quote_address_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    50,
                    ['nullable' => false, 'default' => ''],
                    'address id'
                )->addColumn(
                    'carrier_group_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => false],
                    'Carrier Group ID'
                )->addColumn(
                    'carrier_code',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => false],
                    'Carrier Code'
                )->addColumn(
                    'package_name',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => false],
                    'Package Name'
                )->addColumn(
                    'length',
                    \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                    null,
                    ['nullable' => true],
                    'Package length'
                )->addColumn(
                    'width',
                    \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                    null,
                    ['nullable' => true],
                    'Package width'
                )->addColumn(
                    'height',
                    \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                    null,
                    ['nullable' => true],
                    'Package height'
                )->addColumn(
                    'weight',
                    \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                    null,
                    ['nullable' => true],
                    'Package weight'
                )->addColumn(
                    'declared_value',
                    \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                    null,
                    ['nullable' => true],
                    'Package declared value'
                )->addColumn(
                    'surcharge_price',
                    \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                    null,
                    ['nullable' => true],
                    'Surcharge price'
                )->addIndex(
                    $installer->getIdxName('shipperhq_quote_packages', ['quote_address_id']),
                    ['quote_address_id']
                )->setComment(
                    'ShipperHQ Quote Address Package Information'
                );
            $installer->getConnection(self::$connectionName)->createTable($table);
        }

        $quotePackageItemsTable = $installer->getTable('shipperhq_quote_package_items');
        if (!$installer->getConnection(self::$connectionName)->isTableExists($quotePackageItemsTable)) {
            $table = $installer->getConnection(self::$connectionName)->newTable($quotePackageItemsTable);

            $table
                ->addColumn(
                    'package_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    50,
                    ['nullable' => false, 'default' => 0, 'unsigned' => true]
                )->addColumn(
                    'sku',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => false],
                    'SKU'
                )->addColumn(
                    'qty_packed',
                    \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                    null,
                    ['nullable' => true],
                    'Qty packed'
                )->addColumn(
                    'weight_packed',
                    \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                    null,
                    ['nullable' => true],
                    'Weight packed'
                )->addIndex(
                    $installer->getIdxName('shipperhq_quote_package_items', ['package_id']),
                    ['package_id']
                )->setComment(
                    'ShipperHQ Quote Address Package Items Information'
                )->addForeignKey(
                    $installer->getFkName(
                        'shipperhq_quote_package_items',
                        'package_id',
                        'shipperhq_quote_packages',
                        'package_id'
                    ),
                    'package_id',
                    $quotePackagesTable,
                    'package_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                );
            $installer->getConnection(self::$connectionName)->createTable($table);
        }

        $orderPackagesTable = $installer->getTable('shipperhq_order_packages');
        if (!$installer->getConnection(self::$connectionName)->isTableExists($orderPackagesTable)) {
            $table = $installer->getConnection(self::$connectionName)->newTable($orderPackagesTable);

            $table
                ->addColumn(
                    'package_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['primary' => true, 'nullable' => false, 'unsigned' => true, 'auto_increment' => true]
                )->addColumn(
                    'order_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    10,
                    ['nullable' => false, 'default' => '0', 'unsigned' => true],
                    'Order ID'
                )->addColumn(
                    'carrier_group_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => false],
                    'Carrier Group ID'
                )->addColumn(
                    'carrier_code',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => false],
                    'Carrier Code'
                )->addColumn(
                    'package_name',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => false],
                    'Package Name'
                )->addColumn(
                    'length',
                    \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                    null,
                    ['nullable' => true],
                    'Package length'
                )->addColumn(
                    'width',
                    \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                    null,
                    ['nullable' => true],
                    'Package width'
                )->addColumn(
                    'height',
                    \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                    null,
                    ['nullable' => true],
                    'Package height'
                )->addColumn(
                    'weight',
                    \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                    null,
                    ['nullable' => true],
                    'Package weight'
                )->addColumn(
                    'declared_value',
                    \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                    null,
                    ['nullable' => true],
                    'Package declared value'
                )->addColumn(
                    'surcharge_price',
                    \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                    null,
                    ['nullable' => true],
                    'Surcharge price'
                )->addIndex(
                    $installer->getIdxName('shipperhq_order_packages', ['order_id']),
                    ['order_id']
                )->setComment(
                    'ShipperHQ Quote Address Package Information'
                );
            $installer->getConnection(self::$connectionName)->createTable($table);
        }

        $orderPackageItemsTable = $installer->getTable('shipperhq_order_package_items');
        if (!$installer->getConnection(self::$connectionName)->isTableExists($orderPackageItemsTable)) {
            $table = $installer->getConnection(self::$connectionName)->newTable($orderPackageItemsTable);

            $table
                ->addColumn(
                    'package_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    50,
                    ['nullable' => false, 'default' => 0, 'unsigned' => true]
                )->addColumn(
                    'sku',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => false],
                    'SKU'
                )->addColumn(
                    'qty_packed',
                    \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                    null,
                    ['nullable' => true],
                    'Qty packed'
                )->addColumn(
                    'weight_packed',
                    \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                    null,
                    ['nullable' => true],
                    'Weight packed'
                )->addIndex(
                    $installer->getIdxName('shipperhq_order_package_items', ['package_id']),
                    ['package_id']
                )->setComment(
                    'ShipperHQ Quote Address Package Items Information'
                )->addForeignKey(
                    $installer->getFkName(
                        'shipperhq_order_package_items',
                        'package_id',
                        'shipperhq_order_packages',
                        'package_id'
                    ),
                    'package_id',
                    $orderPackagesTable,
                    'package_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                );
            $installer->getConnection(self::$connectionName)->createTable($table);
        }

        $orderDetailGridTable = $installer->getTable('shipperhq_order_detail_grid');
        if (!$installer->getConnection(self::$connectionName)->isTableExists($orderDetailGridTable)) {
            $table = $installer->getConnection(self::$connectionName)->newTable($orderDetailGridTable);
            $table
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['primary' => true, 'nullable' => false, 'unsigned' => true, 'auto_increment' => true]
                )->addColumn(
                    'order_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    10,
                    ['nullable' => false, 'default' => '0', 'unsigned' => true],
                    'Order ID'
                )->addColumn(
                    'carrier_group',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'Carrier Group(s)'
                )->addColumn(
                    'dispatch_date',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                    null,
                    ['nullable' => true],
                    'Dispatch Date'
                )->addColumn(
                    'delivery_date',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                    null,
                    ['nullable' => true],
                    'Delivery Date'
                )->addColumn(
                    'time_slot',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Time Slot'
                )->addColumn(
                    'pickup_location',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Pickup Location'
                )->addColumn(
                    'delivery_comments',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Delivery Comments'
                )->addColumn(
                    'destination_type',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Destination Type'
                )->addColumn(
                    'liftgate_required',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Liftgate Required'
                )->addColumn(
                    'notify_required',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Notify Required'
                )->addColumn(
                    'inside_delivery',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Inside Delivery'
                )->addColumn(
                    'address_valid',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Address Valid Status'
                )->addColumn(
                    'carrier_type',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'Carrier Type'
                )->addIndex(
                    $installer->getIdxName('shipperhq_order_detail_grid', ['order_id']),
                    ['order_id']
                )->addIndex(
                    $installer->getIdxName('shipperhq_order_detail_grid', ['carrier_group']),
                    ['carrier_group']
                )->setComment(
                    'ShipperHQ Order Grid Information'
                );

            $installer->getConnection(self::$connectionName)->createTable($table);
        }
        $installer->endSetup();
    }
}
