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
 * @copyright Copyright (c) 2020 Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */

/**
 * Copyright © 2020 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace ShipperHQ\Shipper\Setup;

use Magento\Framework\Setup\UninstallInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Exception;

// Setup factories
use Magento\Catalog\Setup\CategorySetupFactory;

class Uninstall implements UninstallInterface
{
    /**
     * SHQ16-2375
     * Declare connection name to support split database architecture in EE
     * connects to 'sales' database; falls back to default for a standard installation
     * @var string
     */
    private static $connectionName = 'sales';

    /**
     * Category setup factory
     *
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @param CategorySetupFactory     $categorySetupFactory
     * @param ModuleDataSetupInterface $moduleDataSetupInterface
     */
    public function __construct(
        CategorySetupFactory $categorySetupFactory,
        ModuleDataSetupInterface $moduleDataSetupInterface
    ) {
        $this->categorySetupFactory = $categorySetupFactory;
        $this->moduleDataSetup = $moduleDataSetupInterface;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        // Quote tables
        $setup->getConnection(self::$connectionName)->dropTable("shipperhq_quote_item_detail");
        $setup->getConnection(self::$connectionName)->dropTable("shipperhq_quote_address_item_detail");
        $setup->getConnection(self::$connectionName)->dropTable("shipperhq_quote_address_detail");
        $setup->getConnection(self::$connectionName)->dropTable("shipperhq_quote_package_items");
        $setup->getConnection(self::$connectionName)->dropTable("shipperhq_quote_packages");

        // Order tables
        $setup->getConnection(self::$connectionName)->dropTable("shipperhq_order_item_detail");
        $setup->getConnection(self::$connectionName)->dropTable("shipperhq_order_package_items");
        $setup->getConnection(self::$connectionName)->dropTable("shipperhq_order_packages");
        $setup->getConnection(self::$connectionName)->dropTable("shipperhq_order_detail");
        $setup->getConnection(self::$connectionName)->dropTable("shipperhq_order_detail_grid");
        $setup->getConnection(self::$connectionName)->dropTable("shipperhq_synchronize");

        // Instantiate setup classes
        $catalogSetup = $this->categorySetupFactory->create(['setup' => $this->moduleDataSetup]);

        // Remove quote table columns
        $quoteColumns = [
            'carrier_type',
            'carrier_id',
            'carriergroup_shipping_details',
            'is_checkout',
            'split_rates',
            'checkout_display_merged',
            'carriergroup_shipping_html',
            'destination_type',
            'validation_status',
            'validated_country_code',
            'validated_vat_number',
            'carriergroup_id',
            'carriergroup',
            'shq_dispatch_date',
            'shq_delivery_date'
        ];

        foreach ($quoteColumns as $attributeName) {
            try {
                $setup->getConnection(self::$connectionName)->dropColumn('quote_address', $attributeName);
                $setup->getConnection(self::$connectionName)->dropColumn('quote_shipping_rate', $attributeName);
            } catch (Exception $exception) {
                //Do nothing
            }
        }

        // Remove quote item columns
        $quoteItemColumns = [
            'carriergroup',
            'carriergroup_id',
            'carriergroup_shipping',
        ];

        foreach ($quoteItemColumns as $attributeName) {
            try {
                $setup->getConnection(self::$connectionName)->dropColumn('quote_item', $attributeName);
                $setup->getConnection(self::$connectionName)->dropColumn('quote_address_item', $attributeName);
            } catch (Exception $exception) {
                //Do nothing
            }
        }

        // Remove customer columns
        $customerAddressAttributes = [
            'destination_type',
            'validation_status'
        ];

        foreach ($customerAddressAttributes as $attributeName) {
            try {
                $catalogSetup->removeAttribute('customer_address', $attributeName);
            } catch (Exception $exception) {
                //Do nothing
            }
        }

        // Remove order columns
        $orderColumns = [
            'carrier_type',
            'carrier_id',
            'carriergroup_shipping_details',
            'carriergroup_shipping_html',
            'destination_type',
            'validation_status',
            'carriergroup_shipping',
            'carriergroup'
        ];

        foreach ($orderColumns as $attributeName) {
            try {
                $setup->getConnection(self::$connectionName)->dropColumn('sales_order', $attributeName);
                $setup->getConnection(self::$connectionName)->dropColumn('sales_order_item', $attributeName);
            } catch (Exception $exception) {
                //Do nothing
            }
        }

        $setup->endSetup();

        /*
         * Remove catalogue attributes - this is intentionally AFTER endSetup because we want the foreign key checks to be enabled
         * such that when we delete the attribute, its removed from ALL tables
         *
         * See https://community.magento.com/t5/Magento-DevBlog/Mysterious-startSetup-and-endSetup-Methods/ba-p/68483
         */
        $catalogueAttributeNames = [
            'shipperhq_shipping_group',
            'shipperhq_post_shipping_group',
            'shipperhq_location',
            'shipperhq_royal_mail_group',
            'shipperhq_shipping_qty',
            'shipperhq_shipping_fee',
            'shipperhq_additional_price',
            'freight_class',
            'shipperhq_nmfc_class',
            'shipperhq_nmfc_sub',
            'shipperhq_handling_fee',
            'shipperhq_carrier_code',
            'shipperhq_volume_weight',
            'shipperhq_declared_value',
            'ship_separately',
            'shipperhq_dim_group',
            'shipperhq_poss_boxes',
            'shipperhq_master_boxes',
            'ship_box_tolerance',
            'must_ship_freight',
            'packing_section_name',
            'shipperhq_availability_date',
            'shipperhq_hs_code',
            'ship_height',
            'ship_width',
            'ship_length',
            'shipperhq_warehouse',
            'shipperhq_malleable_product'
        ];

        foreach ($catalogueAttributeNames as $attributeName) {
            try {
                $catalogSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeName);
            } catch (Exception $exception) {
                //Do nothing
            }
        }
    }
}
