<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ShipperHQ\Shipper\Setup\Patch\Schema;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class AddUniqueIndexToDetailGrid implements SchemaPatchInterface
{
    /**
     * SHQ16-2375
     * Declare connection name to support split database architecture in EE
     * connects to 'sales' database; falls back to default for a standard installation
     * @var string
     */
    private static $connectionName = 'sales';

    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(SchemaSetupInterface $schemaSetup)
    {
        $this->schemaSetup = $schemaSetup;
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '1.1.22';
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $this->schemaSetup->startSetup();
        $connection = $this->schemaSetup->getConnection(self::$connectionName);
        $orderDetailGridTable = $this->schemaSetup->getTable('shipperhq_order_detail_grid');

        // MNB-1836 Need to create a temp index because order_id is referenced in a FK so requires an index
        $connection->addIndex(
            $orderDetailGridTable,
            'SHIPPERHQ_TEMP_PATCH_INDEX',
            ['order_id']
        );

        $connection->dropIndex($orderDetailGridTable, $this->schemaSetup->getIdxName(
            'shipperhq_order_detail_grid',
            ['order_id']
        ));

        $connection->addIndex(
            $orderDetailGridTable,
            $this->schemaSetup->getIdxName('shipperhq_order_detail_grid', ['order_id'], AdapterInterface::INDEX_TYPE_UNIQUE),
            ['order_id'],
            AdapterInterface::INDEX_TYPE_UNIQUE
        );

        $connection->dropIndex(
            $orderDetailGridTable,
            'SHIPPERHQ_TEMP_PATCH_INDEX'
        );
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [CleanOrderGridTable::class];
    }
}
