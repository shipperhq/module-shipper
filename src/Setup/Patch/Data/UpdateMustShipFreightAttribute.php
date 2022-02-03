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
declare(strict_types=1);

namespace ShipperHQ\Shipper\Setup\Patch\Data;

use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class UpdateMustShipFreightAttribute implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;
    /**
     * Category setup factory
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CategorySetupFactory     $categorySetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->categorySetupFactory = $categorySetupFactory;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [InstallFreightAttributes::class];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '1.1.17';
    }

    /**
     * Do Upgrade
     * @return void
     */
    public function apply()
    {
        $catalogSetup = $this->categorySetupFactory->create(['setup' => $this->moduleDataSetup]);
        $catalogSetup->updateAttribute(
            'catalog_product',
            'must_ship_freight',
            ['note' => 'Can be overridden at Carrier level within ShipperHQ']
        );
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
