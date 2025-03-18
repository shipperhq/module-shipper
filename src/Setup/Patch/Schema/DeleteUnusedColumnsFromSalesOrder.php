<?php
/*
 * ShipperHQ
 *
 * @category ShipperHQ
 * @package ShipperHQ_Shipper
 * @copyright Copyright (c) 2025 Zowta LTD and Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */

namespace ShipperHQ\Shipper\Setup\Patch\Schema;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class DeleteUnusedColumnsFromSalesOrder implements SchemaPatchInterface
{
    /**
     * SHQ16-2375
     * Declare connection name to support split database architecture in EE
     * connects to 'sales' database; falls back to default for a standard installation
     * @var string
     */
    private static $connectionName = 'sales';

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(ModuleDataSetupInterface $moduleDataSetup)
    {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '1.1.22';
    }

    /**
     * Removes the unused columns carrier_type, carrier_id, carriergroup_shipping_details and
     * carriergroup_shipping_html from sales_order table
     */
    public function apply()
    {
        $connection = $this->moduleDataSetup->getConnection(self::$connectionName);
        $tableName = $this->moduleDataSetup->getTable('sales_order');

        if ($connection->tableColumnExists($tableName, 'carrier_type')) {
            $connection->dropColumn($tableName, 'carrier_type');
        }

        if ($connection->tableColumnExists($tableName, 'carrier_id')) {
            $connection->dropColumn($tableName, 'carrier_id');
        }

        if ($connection->tableColumnExists($tableName, 'carriergroup_shipping_details')) {
            $connection->dropColumn($tableName, 'carriergroup_shipping_details');
        }

        if ($connection->tableColumnExists($tableName, 'carriergroup_shipping_html')) {
            $connection->dropColumn($tableName, 'carriergroup_shipping_html');
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
