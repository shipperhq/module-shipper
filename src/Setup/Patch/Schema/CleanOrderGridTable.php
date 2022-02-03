<?php
/*
 * ShipperHQ
 *
 * @category ShipperHQ
 * @package ShipperHQ_Shipper
 * @copyright Copyright (c) 2022 Zowta LTD and Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */

namespace ShipperHQ\Shipper\Setup\Patch\Schema;

use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class CleanOrderGridTable implements SchemaPatchInterface
{
    /**
     * SHQ16-2375
     * Declare connection name to support split database architecture in EE
     * connects to 'sales' database; falls back to default for a standard installation
     * @var string
     */
    private static $connectionName = 'sales';
    /**
     * @var SchemaSetupInterface
     */
    private $schemaSetup;

    /**
     * @param SchemaSetupInterface $schemaSetup
     */
    public function __construct(
        SchemaSetupInterface $schemaSetup
    ) {
        $this->schemaSetup = $schemaSetup;
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }

    public function apply()
    {
        $this->schemaSetup->startSetup();
        $connection = $this->schemaSetup->getConnection(self::$connectionName);
        $shqOrderGridTable = $this->schemaSetup->getTable('shipperhq_order_detail_grid');
        $select = $connection
            ->select()
            ->from($shqOrderGridTable)
            ->group('order_id')
            ->having('count(*) >1');
        $duplicateShqOrderGrids = $connection->fetchAll($select);
        foreach ($duplicateShqOrderGrids as $shqOrderGridEntry) {
            $condition = ['id =?' => $shqOrderGridEntry['id']];
            $connection->delete($shqOrderGridTable, $condition);
        }
        $this->schemaSetup->endSetup();
    }
}
