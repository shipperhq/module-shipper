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

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * SHQ16-2375
     * Declare connection name to support split database architecture in EE
     * connectes to 'sales' database; falls back to default for a standard installation
     * @var string
     */
    private static $connectionName = 'sales';

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $isFreshInstall = $context->getVersion() == ""; // MNB-809 if empty safe to assume InstallSchema has just been run

        //1.0.6
        $addressDetailTable = $installer->getTable('shipperhq_quote_address_detail');
        if (!$installer->getConnection(self::$connectionName)->isTableExists($addressDetailTable)) {
            $table = $installer->getConnection(self::$connectionName)->newTable($addressDetailTable);
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
                )->addIndex(
                    $installer->getIdxName('shipperhq_quote_address_detail', ['quote_address_id']),
                    ['quote_address_id']
                )->setComment(
                    'ShipperHQ Quote Carrier Group Information'
                );
            //Foreign key to quote address table - if permitted
            $installer->getConnection(self::$connectionName)->createTable($table);
        }

        if (!$isFreshInstall && version_compare($context->getVersion(), '1.0.10', '<')) {
            $connection = $installer->getConnection(self::$connectionName);
            $connection->modifyColumn(
                $setup->getTable('shipperhq_quote_address_detail'),
                'quote_address_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 50,
                    'nullable' => false,
                    'default' => '',
                ]
            );
            $this->addIndexToTable($installer, 'shipperhq_quote_address_detail', ['quote_address_id']);
        }

        //1.0.11 - SHQ16-1967
        if (!$isFreshInstall && version_compare($context->getVersion(), '1.0.11', '<')) {
            if (!$installer->getConnection(self::$connectionName)->tableColumnExists(
                $addressDetailTable,
                'limited_delivery'
            )) {
                $installer->getConnection(self::$connectionName)
                    ->addColumn(
                        $addressDetailTable,
                        'limited_delivery',
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            'default' => null,
                            'length' => 10,
                            'nullable' => true,
                            'comment' => 'Limited Delivery',
                        ]
                    );
            }
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
                    [
                        'nullable' => false,
                        'default' => '0',
                        'unsigned' => true
                    ],
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
                )->addIndex(
                    $installer->getIdxName('shipperhq_order_detail', ['order_id']),
                    ['order_id']
                )->setComment(
                    'ShipperHQ Order Carrier Group Information'
                );
            $installer->getConnection(self::$connectionName)->createTable($table);
        }

        //1.0.11 - SHQ16-1967
        if (!$isFreshInstall && version_compare($context->getVersion(), '1.0.11', '<')) {
            if (!$installer->getConnection(self::$connectionName)->tableColumnExists(
                $orderDetailTable,
                'limited_delivery'
            )) {
                $installer->getConnection(self::$connectionName)
                    ->addColumn(
                        $orderDetailTable,
                        'limited_delivery',
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            'default' => null,
                            'length' => 10,
                            'nullable' => true,
                            'comment' => 'Limited Delivery',
                        ]
                    );
            }
        }

        if (!$isFreshInstall && version_compare($context->getVersion(), '1.0.13', '<')) {
            $connection = $installer->getConnection(self::$connectionName);
            $connection->modifyColumn(
                $setup->getTable('shipperhq_order_detail'),
                'order_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => 10,
                    'nullable' => false,
                    'default' => '0',
                    'unsigned' => true
                ]
            );
            $this->addIndexToTable($installer, 'shipperhq_order_detail', ['order_id']);
        }

        $itemDetailTable = $installer->getTable('shipperhq_quote_item_detail');
        if (!$installer->getConnection(self::$connectionName)->isTableExists($itemDetailTable)) {
            $table = $installer->getConnection(self::$connectionName)->newTable($itemDetailTable);
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

        if (!$isFreshInstall && version_compare($context->getVersion(), '1.0.10', '<')) {
            $connection = $installer->getConnection(self::$connectionName);
            $connection->modifyColumn(
                $setup->getTable('shipperhq_quote_item_detail'),
                'quote_item_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 50,
                    'nullable' => false,
                    'default' => '',
                ]
            );
            $this->addIndexToTable($installer, 'shipperhq_quote_item_detail', ['quote_item_id']);
        }

        $addressItemDetailTable = $installer->getTable('shipperhq_quote_address_item_detail');
        if (!$installer->getConnection(self::$connectionName)->isTableExists($addressItemDetailTable)) {
            $table = $installer->getConnection(self::$connectionName)->newTable($addressItemDetailTable);
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

        if (!$isFreshInstall && version_compare($context->getVersion(), '1.0.10', '<')) {
            $installer->getConnection(self::$connectionName)->modifyColumn(
                $setup->getTable('shipperhq_quote_address_item_detail'),
                'quote_address_item_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 50,
                    'nullable' => false,
                    'default' => '',
                ]
            );
            $this->addIndexToTable($installer, 'shipperhq_quote_address_item_detail', ['quote_address_item_id']);
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
        if (!$isFreshInstall && version_compare($context->getVersion(), '1.0.10', '<')) {
            $installer->getConnection(self::$connectionName)->modifyColumn(
                $setup->getTable('shipperhq_order_item_detail'),
                'order_item_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 50,
                    'nullable' => false,
                    'default' => '',
                ]
            );
            $this->addIndexToTable($installer, 'shipperhq_order_item_detail', ['order_item_id']);
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

        if (!$isFreshInstall && version_compare($context->getVersion(), '1.0.13', '<')) {
            $installer->getConnection(self::$connectionName)->modifyColumn(
                $setup->getTable('shipperhq_order_packages'),
                'order_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => 10,
                    'nullable' => false,
                    'default' => '0',
                    'unsigned' => true
                ]
            );

            $this->addIndexToTable($installer, 'shipperhq_order_packages', ['order_id']);
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

        if (!$isFreshInstall && version_compare($context->getVersion(), '1.0.10', '<')) {
            $installer->getConnection(self::$connectionName)->modifyColumn(
                $setup->getTable('shipperhq_order_package_items'),
                'package_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => 50,
                    'nullable' => false,
                    'default' => 0,
                    'unsigned' => true
                ]
            );
            $this->addIndexToTable($installer, 'shipperhq_order_package_items', ['package_id']);
        }

        //Version 1.0.8
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
                )->addIndex(
                    $installer->getIdxName('shipperhq_order_detail_grid', ['order_id'], AdapterInterface::INDEX_TYPE_UNIQUE),
                    ['order_id'],
                    ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                )->addIndex(
                    $installer->getIdxName('shipperhq_order_detail_grid', ['carrier_group']),
                    ['carrier_group']
                )->setComment(
                    'ShipperHQ Order Grid Information'
                );

            $installer->getConnection(self::$connectionName)->createTable($table);
        } else {
            if (!$isFreshInstall && version_compare($context->getVersion(), '1.0.9') < 0) {
                $connection = $installer->getConnection(self::$connectionName);

                $connection->modifyColumn(
                    $setup->getTable('shipperhq_order_detail_grid'),
                    'carrier_group',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'default' => '',
                    ]
                );

                $connection->addIndex(
                    $orderDetailGridTable,
                    $installer->getIdxName('shipperhq_order_detail_grid', ['carrier_group']),
                    ['carrier_group']
                );
            }

            if (!$isFreshInstall && version_compare($context->getVersion(), '1.0.13') < 0) {
                $connection = $installer->getConnection(self::$connectionName);
                $connection->modifyColumn(
                    $setup->getTable('shipperhq_order_detail_grid'),
                    'order_id',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        'length' => 10,
                        'nullable' => false,
                        'default' => '0',
                        'unsigned' => true
                    ]
                );

                $connection->addIndex(
                    $orderDetailGridTable,
                    $installer->getIdxName('shipperhq_order_detail_grid', ['order_id']),
                    ['order_id']
                );
            }

            // MNB-605 Prevent duplicate entries. Adding unique index
            if (!$isFreshInstall && version_compare($context->getVersion(), '1.1.22') < 0) {
                $connection = $installer->getConnection(self::$connectionName);

                $connection->addIndex(
                    $orderDetailGridTable,
                    'SHIPPERHQ_TEMP_PATCH_INDEX',
                    ['order_id']
                );

                $connection->dropIndex($orderDetailGridTable, $installer->getIdxName(
                    'shipperhq_order_detail_grid',
                    ['order_id']
                ));

                $this->cleanOrderGridTable($installer);

                $connection->addIndex(
                    $orderDetailGridTable,
                    $installer->getIdxName('shipperhq_order_detail_grid', ['order_id']),
                    ['order_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                );

                $connection->dropIndex(
                    $orderDetailGridTable,
                    'SHIPPERHQ_TEMP_PATCH_INDEX'
                );
            }
        }

        if (!$isFreshInstall && version_compare($context->getVersion(), '1.0.14') < 0) {
            $this->cleanOrderGridTable($installer);
        }

        if (!$isFreshInstall && version_compare($context->getVersion(), '1.1.18') < 0) {
            $this->addValidatedAddressColumns($installer, 'shipperhq_quote_address_detail');
            $this->addValidatedAddressColumns($installer, 'shipperhq_order_detail');
        }

        if (!$isFreshInstall && version_compare($context->getVersion(), '1.1.20') < 0) {
            $this->addCarrierTypeToOrderDetailGrid($installer);
        }

        if (!$isFreshInstall && version_compare($context->getVersion(), '1.1.23', '<')) {
            $this->changeIdColumnToInteger($installer, 'shipperhq_quote_address_detail', 'quote_address_id');
            $this->changeIdColumnToInteger($installer, 'shipperhq_quote_item_detail', 'quote_item_id');
            $this->changeIdColumnToInteger($installer, 'shipperhq_quote_address_item_detail', 'quote_address_item_id');
            $this->changeIdColumnToInteger($installer, 'shipperhq_order_item_detail', 'order_item_id');
            $this->changeIdColumnToInteger($installer, 'shipperhq_order_packages', 'order_id');
            $this->changeIdColumnToInteger($installer, 'shipperhq_order_detail_grid', 'order_id');

            $this->addForeignKeysToTables($installer);
        }

        if (!$isFreshInstall && version_compare($context->getVersion(), '1.1.24', '<')) {
            $this->dropQuotePackageTables($installer);
        }

        if (!$isFreshInstall && version_compare($context->getVersion(), '1.1.25', '<')) {
            $this->dropForeignKeysMNB1060($installer);
        }

        if (!$isFreshInstall && version_compare($context->getVersion(), '1.1.26', '<')) {
            $this->addPrimaryKeyToOrderPackageItems($installer);
        }

        $installer->endSetup();
    }

    public function addCarrierTypeToOrderDetailGrid(SchemaSetupInterface $installer)
    {
        $connection = $installer->getConnection(self::$connectionName);
        $table = $installer->getTable('shipperhq_order_detail_grid');
        $connection->addColumn(
            $table,
            'carrier_type',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'default' => '',
                'comment' => 'Carrier Type'
            ]
        );
    }

    public function addValidatedAddressColumns(SchemaSetupInterface $installer, $tableName)
    {
        $connection = $installer->getConnection(self::$connectionName);
        $table = $installer->getTable($tableName);
        $connection->addColumn(
            $table,
            'validated_shipping_street',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'default' => '',
                'comment' => 'Validated Shipping Street'
            ]
        );
        $connection->addColumn(
            $table,
            'validated_shipping_street2',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'default' => '',
                'comment' => 'Validated Shipping Street 2'
            ]
        );
        $connection->addColumn(
            $table,
            'validated_shipping_city',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 40,
                'nullable' => true,
                'default' => '',
                'comment' => 'Validated Shipping City'
            ]
        );
        $connection->addColumn(
            $table,
            'validated_shipping_postcode',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 20,
                'nullable' => true,
                'default' => '',
                'comment' => 'Validated Shipping Postcode'
            ]
        );
        $connection->addColumn(
            $table,
            'validated_shipping_region',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 40,
                'nullable' => true,
                'default' => '',
                'comment' => 'Validated Shipping Region'
            ]
        );
        $connection->addColumn(
            $table,
            'validated_shipping_country',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 30,
                'nullable' => true,
                'default' => '',
                'comment' => 'Validated Shipping Country'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param String $tableName
     * @param array $columns
     * @return void
     */
    public function addIndexToTable(SchemaSetupInterface $setup, $tableName, array $columns)
    {
        $setup->getConnection(self::$connectionName)->addIndex(
            $setup->getTable($tableName),
            $setup->getIdxName($tableName, $columns),
            $columns
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function cleanOrderGridTable(SchemaSetupInterface $setup)
    {
        $shqOrderGridTable = $setup->getTable('shipperhq_order_detail_grid');

        $select = $setup->getConnection(self::$connectionName)
            ->select()
            ->from($shqOrderGridTable)
            ->group('order_id')
            ->having('count(*) >1');

        $duplicateShqOrderGrids = $setup->getConnection(self::$connectionName)->fetchAll($select);
        foreach ($duplicateShqOrderGrids as $shqOrderGridEntry) {
            $condition = ['id =?' => $shqOrderGridEntry['id']];
            $setup->getConnection(self::$connectionName)->delete($shqOrderGridTable, $condition);
        }
    }

    private function changeIdColumnToInteger(SchemaSetupInterface $setup, $tableName, $columnName)
    {
        $setup->getConnection()->modifyColumn(
            $setup->getTable($tableName),
            $columnName,
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'length' => 10,
                'nullable' => false,
                'unsigned' => true
            ]
        );
    }

    private function addForeignKeysToTables(SchemaSetupInterface $setup)
    {
        $setup->getConnection(self::$connectionName)->addForeignKey(
            $setup->getFkName(
                'shipperhq_quote_address_detail',
                'quote_address_id',
                'quote_address',
                'address_id'
            ),
            $setup->getTable('shipperhq_quote_address_detail'),
            'quote_address_id',
            $setup->getTable('quote_address'),
            'address_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );

        $setup->getConnection(self::$connectionName)->addForeignKey(
            $setup->getFkName(
                'shipperhq_order_detail',
                'order_id',
                'sales_order',
                'entity_id'
            ),
            $setup->getTable('shipperhq_order_detail'),
            'order_id',
            $setup->getTable('sales_order'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );

        // MNB-1060, need to drop the FK for now, this can be revisited in MNB-1064
//        $setup->getConnection(self::$connectionName)->addForeignKey(
//            $setup->getFkName(
//                'shipperhq_quote_item_detail',
//                'quote_item_id',
//                'quote_item',
//                'item_id'
//            ),
//            $setup->getTable('shipperhq_quote_item_detail'),
//            'quote_item_id',
//            $setup->getTable('quote_item'),
//            'item_id',
//            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
//        );

        $setup->getConnection(self::$connectionName)->addForeignKey(
            $setup->getFkName(
                'shipperhq_quote_address_item_detail',
                'quote_address_item_id',
                'quote_address_item',
                'address_item_id'
            ),
            $setup->getTable('shipperhq_quote_address_item_detail'),
            'quote_address_item_id',
            $setup->getTable('quote_address_item'),
            'address_item_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );

        $setup->getConnection(self::$connectionName)->addForeignKey(
            $setup->getFkName(
                'shipperhq_order_item_detail',
                'order_item_id',
                'sales_order_item',
                'item_id'
            ),
            $setup->getTable('shipperhq_order_item_detail'),
            'order_item_id',
            $setup->getTable('sales_order_item'),
            'item_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );

        $setup->getConnection(self::$connectionName)->addForeignKey(
            $setup->getFkName(
                'shipperhq_order_packages',
                'order_id',
                'sales_order',
                'entity_id'
            ),
            $setup->getTable('shipperhq_order_packages'),
            'order_id',
            $setup->getTable('sales_order'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );

        $setup->getConnection(self::$connectionName)->addForeignKey(
            $setup->getFkName(
                'shipperhq_order_detail_grid',
                'order_id',
                'sales_order',
                'entity_id'
            ),
            $setup->getTable('shipperhq_order_detail_grid'),
            'order_id',
            $setup->getTable('sales_order'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );
    }

    // MNB-1060, need to drop the FK for now, this can be revisited in MNB-1064
    private function dropForeignKeysMNB1060(SchemaSetupInterface $setup)
    {
        $fkName = $setup->getFkName(
            'shipperhq_quote_item_detail',
            'quote_item_id',
            'quote_item',
            'item_id'
        );
        // dropForeignKey checks for existence first so we don't have to
        $setup->getConnection(self::$connectionName)->dropForeignKey(
            $setup->getTable('shipperhq_quote_item_detail'),
            $fkName
        );
    }

    // MNB-1111 Table was missing primary key. Need to add one to ensure performant
    private function addPrimaryKeyToOrderPackageItems(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable("shipperhq_order_package_items");
        $setup->getConnection(self::$connectionName)
            ->addColumn(
                $table,
                'id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'unsigned' => true,
                    'nullable' => false,
                    'auto_increment' => true,
                    'primary' => true,
                    'comment' => 'Primary Key'
                ]
            );
    }

    /**
     * MNB-881 No longer used. Quote packages stored in session
     *
     * @param SchemaSetupInterface $setup
     */
    private function dropQuotePackageTables(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection(self::$connectionName);

        $quotePackagesTable = $connection->getTableName("shipperhq_quote_packages");
        $quoteItemsPackagesTable = $connection->getTableName("shipperhq_quote_package_items");

        if (!$connection->isTableExists($quotePackagesTable) || !$connection->isTableExists($quoteItemsPackagesTable)) {
            return;
        }

        $rowsCountPackage = $connection->fetchOne(
            $connection->select()->from($quotePackagesTable, 'COUNT(*)')
        );

        if (!$rowsCountPackage) {
            $connection->dropTable('shipperhq_quote_packages');
        }

        $rowsCountItem = $connection->fetchOne(
            $connection->select()->from($quoteItemsPackagesTable, 'COUNT(*)')
        );

        if (!$rowsCountItem) {
            $connection->dropTable('shipperhq_quote_package_items');
        }
    }
}
