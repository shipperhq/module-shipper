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
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

class InstallFreightAttributes implements DataPatchInterface, PatchRevertableInterface
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
     * @var AttributeCollectionFactory
     */
    private $attributeCollectionFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface   $moduleDataSetup,
        CategorySetupFactory       $categorySetupFactory,
        AttributeCollectionFactory $attributeCollectionFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->categorySetupFactory = $categorySetupFactory;
        $this->attributeCollectionFactory = $attributeCollectionFactory
            ?: ObjectManager::getInstance()->get(AttributeCollectionFactory::class);
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
        return '1.0.12';
    }

    /**
     * Do Upgrade
     * @return void
     */
    public function apply()
    {
        /** @var \Magento\Catalog\Setup\CategorySetup $catalogSetup */
        $catalogSetup = $this->categorySetupFactory->create(['setup' => $this->moduleDataSetup]);
        /* ------ freight_class -------- */
        $catalogSetup->addAttribute(Product::ENTITY, 'freight_class', [
            'type'                     => 'text',
            'source'                   => 'ShipperHQ\Shipper\Model\Product\Attribute\Source\FreightClass',
            'input'                    => 'select',
            'label'                    => 'Freight Class',
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
        /* ------ shipperhq_nmfc_class -------- */
        $catalogSetup->addAttribute(Product::ENTITY, 'shipperhq_nmfc_class', [
            'type'                     => 'text',
            'input'                    => 'text',
            'label'                    => 'NMFC',
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
        /* ------ must_ship_freight -------- */
        $catalogSetup->addAttribute(Product::ENTITY, 'must_ship_freight', [
            'type'                     => 'int',
            'input'                    => 'boolean',
            'label'                    => 'Must Ship Freight',
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
            'used_in_product_listing'  => false,
            'note'                     => 'Can be overridden at Carrier level within ShipperHQ'
        ]);
        /* ------ shipperhq_nmfc_sub -------- */
        $catalogSetup->addAttribute(Product::ENTITY, 'shipperhq_nmfc_sub', [
            'type'                     => 'text',
            'input'                    => 'text',
            'label'                    => 'NMFC Sub',
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
            'used_in_product_listing'  => false,
            'note'                     => 'Only required to support ABF Freight'
        ]);
        $entityTypeId = $catalogSetup->getEntityTypeId(Product::ENTITY);
        $attributeSetArr = $catalogSetup->getAllAttributeSetIds($entityTypeId);
        $freightAttributeCodes = [
            'freight_class'     => '1',
            'must_ship_freight' => '10'
        ];
        foreach ($attributeSetArr as $attributeSetId) {
            //SHQ16-2123 handle migrated instances from M1 to M2
            $migrateGroupId = $catalogSetup->getAttributeGroup($entityTypeId, $attributeSetId, 'migration-freight-shipping');
            $existingFreightAttributeIds = [];
            if ($migrateGroupId) {
                $existingFreightAttributeIds = $this->getNonShqAttributeIds(
                    $catalogSetup,
                    'migration-freight-shipping',
                    $attributeSetId
                );
                $catalogSetup->removeAttributeGroup($entityTypeId, $attributeSetId, 'migration-freight-shipping');
            }
            $attributeGroupId = $catalogSetup->getAttributeGroup(
                $entityTypeId,
                $attributeSetId,
                'Freight Shipping'
            );
            if (!$attributeGroupId) {
                $catalogSetup->addAttributeGroup($entityTypeId, $attributeSetId, 'Freight Shipping', '101');
            }
            $attributeGroupId = $catalogSetup->getAttributeGroupId(
                $entityTypeId,
                $attributeSetId,
                'Freight Shipping'
            );
            $ourFreightAttributeIds = [];
            foreach ($freightAttributeCodes as $code => $sort) {
                $attributeId = $catalogSetup->getAttributeId($entityTypeId, $code);
                $ourFreightAttributeIds[] = $attributeId;
                $catalogSetup->addAttributeToGroup(
                    $entityTypeId,
                    $attributeSetId,
                    $attributeGroupId,
                    $attributeId,
                    $sort
                );
            }
            // SHQ18-2825 Add any attributes that were in migration-freight-shipping that were not our attributes back
            if (count($existingFreightAttributeIds)) {
                $attributeIdsToAdd = array_diff($existingFreightAttributeIds, $ourFreightAttributeIds);
                foreach ($attributeIdsToAdd as $attributeId) {
                    $catalogSetup->addAttributeToGroup(
                        $entityTypeId,
                        $attributeSetId,
                        $attributeGroupId,
                        $attributeId,
                        10
                    );
                }
            }
        }
    }

    /**
     * SHQ18-2825 Gets all attribute IDs for a given attribute group
     *
     * @param $catalogSetup
     * @param $attributeGroupName
     * @param $attributeSetId
     *
     * @return array
     */
    private function getNonShqAttributeIds($catalogSetup, $attributeGroupName, $attributeSetId)
    {
        $entityTypeId = $catalogSetup->getEntityTypeId(Product::ENTITY);
        $attributeGroupId = $catalogSetup->getAttributeGroupId(
            $entityTypeId,
            $attributeSetId,
            $attributeGroupName
        );
        $collection = $this->attributeCollectionFactory->create();
        $collection->setAttributeGroupFilter($attributeGroupId);
        $allAttributeIds = [];
        foreach ($collection->getItems() as $attribute) {
            $allAttributeIds[] = $attribute->getAttributeId();
        }

        return $allAttributeIds;
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    public function revert()
    {
        /** @var \Magento\Catalog\Setup\CategorySetup $catalogSetup */
        $catalogSetup = $this->categorySetupFactory->create(['setup' => $this->moduleDataSetup]);

        $attributeCodes = [
            'freight_class',
            'shipperhq_nmfc_class',
            'must_ship_freight',
            'shipperhq_nmfc_sub'
        ];

        foreach ($attributeCodes as $attributeCode) {
            $catalogSetup->removeAttribute(Product::ENTITY, $attributeCode);
        }

        $entityTypeId = $catalogSetup->getEntityTypeId(Product::ENTITY);
        $attributeSets = $catalogSetup->getAllAttributeSetIds($entityTypeId);

        foreach ($attributeSets as $attributeSet) {
            $attributeGroupId = $catalogSetup->getAttributeGroupId(
                $entityTypeId,
                $attributeSet,
                'Freight Shipping'
            );

            if ($attributeGroupId) {
                $catalogSetup->removeAttributeGroup($entityTypeId, $attributeSet, $attributeGroupId);
            }
        }
    }
}
