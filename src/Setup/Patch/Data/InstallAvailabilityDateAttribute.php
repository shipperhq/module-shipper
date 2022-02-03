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

use Magento\Catalog\Model\Product;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class InstallAvailabilityDateAttribute implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

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
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '1.0.3';
    }

    /**
     * Do Upgrade
     * @return void
     */
    public function apply()
    {
        $catalogSetup = $this->categorySetupFactory->create(['setup' => $this->moduleDataSetup]);
        $catalogSetup->addAttribute(Product::ENTITY, 'shipperhq_availability_date', [
            'type'                     => 'datetime',
            'backend'                  => 'Magento\Eav\Model\Entity\Attribute\Backend\Datetime',
            'input'                    => 'date',
            'label'                    => 'Availability Date',
            'global'                   => ScopedAttributeInterface::SCOPE_STORE,
            'visible'                  => true,
            'required'                 => false,
            'visible_on_front'         => false,
            'is_html_allowed_on_front' => false,
            'searchable'               => false,
            'filterable'               => false,
            'comparable'               => false,
            'is_configurable'          => false,
            'unique'                   => false,
            'user_defined'             => true,
            'used_in_product_listing'  => false
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
