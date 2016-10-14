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

use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        //1.0.6
        if (!$installer->getConnection()->isTableExists($installer->getTable('shipperhq_quote_address_detail'))) {
            $table = $installer->getConnection()->newTable($installer->getTable('shipperhq_quote_address_detail'));
            $table
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['primary' => true,
                        'nullable' => false,
                        'unsigned' => true,
                        'auto_increment' => true ]
                )->addColumn(
                    'quote_address_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
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
                )->setComment(
                    'ShipperHQ Quote Carrier Group Information'
                );
            //Foreign key to quote address table - if permitted
            $installer->getConnection()->createTable($table);
        }

        if (!$installer->getConnection()->isTableExists($installer->getTable('shipperhq_order_detail'))) {
            $table = $installer->getConnection()->newTable($installer->getTable('shipperhq_order_detail'));
            $table
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['primary' => true,
                        'nullable' => false,
                        'unsigned' => true,
                        'auto_increment' => true ]
                )->addColumn(
                    'order_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => false],
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
                )->setComment(
                    'ShipperHQ Order Carrier Group Information'
                );

            $installer->getConnection()->createTable($table);
        }

        if (!$installer->getConnection()->isTableExists($installer->getTable('shipperhq_quote_item_detail'))) {
            $table = $installer->getConnection()->newTable($installer->getTable('shipperhq_quote_item_detail'));
            $table
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['primary' => true,
                        'nullable' => false,
                        'unsigned' => true,
                        'auto_increment' => true ]
                )->addColumn(
                    'quote_item_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => false],
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
                )->setComment(
                    'ShipperHQ Quote Item Carrier Group Information'
                );

            $installer->getConnection()->createTable($table);
        }

        if (!$installer->getConnection()->isTableExists($installer->getTable('shipperhq_quote_address_item_detail'))) {
            $table = $installer->getConnection()->newTable($installer->getTable('shipperhq_quote_address_item_detail'));
            $table
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['primary' => true,
                        'nullable' => false,
                        'unsigned' => true,
                        'auto_increment' => true ]
                )->addColumn(
                    'quote_address_item_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => false],
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
                )->setComment(
                    'ShipperHQ Quote Address Item Carrier Group Information'
                );

            $installer->getConnection()->createTable($table);
        }

        if (!$installer->getConnection()->isTableExists($installer->getTable('shipperhq_order_item_detail'))) {
            $table = $installer->getConnection()->newTable($installer->getTable('shipperhq_order_item_detail'));
            $table
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['primary' => true,
                        'nullable' => false,
                        'unsigned' => true,
                        'auto_increment' => true ]
                )->addColumn(
                    'order_item_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => false],
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
                )->setComment(
                    'ShipperHQ Order Item Carrier Group Information'
                );

            $installer->getConnection()->createTable($table);
        }

        if (!$installer->getConnection()->isTableExists($installer->getTable('shipperhq_quote_packages'))) {
            $table = $installer->getConnection()->newTable($installer->getTable('shipperhq_quote_packages'));

            $table
                ->addColumn(
                    'package_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['primary' => true,
                        'nullable' => false,
                        'unsigned' => true,
                        'auto_increment' => true]
                )->addColumn(
                    'quote_address_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => false],
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
                )->setComment(
                    'ShipperHQ Quote Address Package Information'
                );
            //TODO when we resolve the enterprise install thing
//            ->addForeignKey(
//                $this->getFkName('shipperhq_shipper/quote_packages', 'address_id', 'sales/quote_address', 'address_id'),
//                'address_id',
//                $this->getTable('sales/quote_address'),
//                'address_id',
//                Varien_Db_Adapter_Interface::FK_ACTION_CASCADE
//            )

            $installer->getConnection()->createTable($table);
        }

        if (!$installer->getConnection()->isTableExists($installer->getTable('shipperhq_quote_package_items'))) {
            $table = $installer->getConnection()->newTable($installer->getTable('shipperhq_quote_package_items'));

            $table
            ->addColumn(
                'package_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false,
                    'unsigned' => true]
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
            )->setComment(
                'ShipperHQ Quote Address Package Items Information'
            )->addForeignKey(
                $installer->getFkName('shipperhq_quote_package_items', 'package_id', 'shipperhq_quote_packages', 'package_id'),
                'package_id',
                $installer->getTable('shipperhq_quote_packages'),
                'package_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            );
            $installer->getConnection()->createTable($table);
        }

        if (!$installer->getConnection()->isTableExists($installer->getTable('shipperhq_order_packages'))) {
            $table = $installer->getConnection()->newTable($installer->getTable('shipperhq_order_packages'));

            $table
                ->addColumn(
                    'package_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['primary' => true,
                        'nullable' => false,
                        'unsigned' => true,
                        'auto_increment' => true]
                )->addColumn(
                    'order_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => false],
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
                )->setComment(
                    'ShipperHQ Quote Address Package Information'
                );
            //TODO when we resolve the enterprise install thing
//            ->addForeignKey(
//                $this->getFkName('shipperhq_shipper/order_packages', 'order_id', 'sales/order', 'entity_id'),
//                'order_id',
//                $this->getTable('sales/order'),
//                'entity_id',
//                Varien_Db_Adapter_Interface::FK_ACTION_CASCADE
//            )
            $installer->getConnection()->createTable($table);

        }

        if (!$installer->getConnection()->isTableExists($installer->getTable('shipperhq_order_package_items'))) {
            $table = $installer->getConnection()->newTable($installer->getTable('shipperhq_order_package_items'));

            $table
                ->addColumn(
                    'package_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false,
                        'unsigned' => true]
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
                )->setComment(
                    'ShipperHQ Quote Address Package Items Information'
                )->addForeignKey(
                $installer->getFkName('shipperhq_order_package_items', 'package_id', 'shipperhq_order_packages', 'package_id'),
                'package_id',
                $installer->getTable('shipperhq_order_packages'),
                'package_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                );
            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();

    }
}
