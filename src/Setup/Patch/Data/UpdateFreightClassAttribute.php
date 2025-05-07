<?php
/*
 * ShipperHQ
 *
 * @category ShipperHQ
 * @package ShipperHQ_Shipper
 * @copyright Copyright (c) 2023 Zowta LTD and Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */
declare(strict_types=1);

namespace ShipperHQ\Shipper\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class UpdateFreightClassAttribute implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory          $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [InstallFreightAttributes::class];
    }

    /**
     * SHQ23-46 Change freight_class attribute from type int to type text
     * Based on \Magento\Catalog\Setup\Patch\Data\UpdateMultiselectAttributesBackendTypes
     *
     * @return void
     * @throws LocalizedException
     */
    public function apply()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $entityTypeId = $eavSetup->getEntityTypeId(Product::ENTITY);

        /** @var AbstractAttribute $freightClassAttribute */
        $freightClassAttribute = $eavSetup->getAttribute($entityTypeId, "freight_class");

        if (is_array($freightClassAttribute) && $freightClassAttribute['backend_type'] == 'int') {
            $this->moduleDataSetup->startSetup();
            $connection = $this->moduleDataSetup->getConnection();
            $intTable = $this->moduleDataSetup->getTable('catalog_product_entity_int');
            $textTable = $this->moduleDataSetup->getTable('catalog_product_entity_text');
            $intTableDataSql = $connection
                ->select()
                ->from($intTable)
                ->where('attribute_id = ?', $freightClassAttribute['attribute_id']);
            $dataToMigrate = array_map(static function ($row) {
                $row['value_id'] = null;

                return $row;
            }, $connection->fetchAll($intTableDataSql));

            // Clean up data. In some instances the .5 is missing
            foreach ($dataToMigrate as $key => $data) {
                if ($data['value'] == "92") {
                    $dataToMigrate[$key]['value'] = "92.5";
                } elseif ($data['value'] == "77") {
                    $dataToMigrate[$key]['value'] = "77.5";
                }
            }

            foreach (array_chunk($dataToMigrate, 2000) as $dataChunk) {
                $connection->insertMultiple($textTable, $dataChunk);
            }
            $connection->query($connection->deleteFromSelect($intTableDataSql, $intTable));
            $eavSetup->updateAttribute($entityTypeId, $freightClassAttribute['attribute_id'], 'backend_type', 'text');
            $this->moduleDataSetup->endSetup();
        }
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
