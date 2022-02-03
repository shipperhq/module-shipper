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
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class InstallCrossBorderAttributes implements DataPatchInterface
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
     * @param CategorySetupFactory     $categorySetupFactory
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
        return '1.1.21';
    }

    /**
     * Do Upgrade
     * @return void
     */
    public function apply()
    {
        $catalogSetup = $this->categorySetupFactory->create(['setup' => $this->moduleDataSetup]);
        /* ------ shq_hs_code -------- */
        $catalogSetup->addAttribute(Product::ENTITY, 'shipperhq_hs_code', [
            'type'                     => 'text',
            'input'                    => 'text',
            'label'                    => 'HS Code',
            'global'                   => false,
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
        $entityTypeId = $catalogSetup->getEntityTypeId(Product::ENTITY);
        $attributeSetArr = $catalogSetup->getAllAttributeSetIds($entityTypeId);
        $crossAttributeCodes = [
            'shipperhq_hs_code' => '25',
        ];
        foreach ($attributeSetArr as $attributeSetId) {

            $attributeGroupId = $catalogSetup->getAttributeGroup(
                $entityTypeId,
                $attributeSetId,
                'Shipping'
            );
            if (!$attributeGroupId) {
                $catalogSetup->addAttributeGroup($entityTypeId, $attributeSetId, 'Shipping', '99');
            }
            $attributeGroupId = $catalogSetup->getAttributeGroupId(
                $entityTypeId,
                $attributeSetId,
                'Shipping'
            );
            foreach ($crossAttributeCodes as $code => $sort) {
                $attributeId = $catalogSetup->getAttributeId($entityTypeId, $code);
                $catalogSetup->addAttributeToGroup(
                    $entityTypeId,
                    $attributeSetId,
                    $attributeGroupId,
                    $attributeId,
                    $sort
                );
            }
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
