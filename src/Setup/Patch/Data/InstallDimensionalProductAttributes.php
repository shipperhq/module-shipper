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
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class InstallDimensionalProductAttributes implements DataPatchInterface
{
    /**
     * Category setup factory
     * @var CategorySetupFactory
     */
    protected $categorySetupFactory;
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;
    /**
     * @var AttributeCollectionFactory
     */
    private $attributeCollectionFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface   $moduleDataSetup,
        AttributeCollectionFactory $attributeCollectionFactory,
        CategorySetupFactory       $categorySetupFactory
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
        return '1.0.1';
    }

    /**
     * Do Upgrade
     * @return void
     */
    public function apply()
    {
        $catalogSetup = $this->categorySetupFactory->create(['setup' => $this->moduleDataSetup]);
        /* ------ shipperhq_shipping_fee -------- */
        $catalogSetup->addAttribute(Product::ENTITY, 'shipperhq_shipping_fee', [
            'type'                     => 'decimal',
            'backend'                  => 'Magento\Catalog\Model\Product\Attribute\Backend\Price',
            'input'                    => 'price',
            'label'                    => 'Shipping Fee',
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
        /* ------ shipperhq_handling_fee -------- */
        $catalogSetup->addAttribute(Product::ENTITY, 'shipperhq_handling_fee', [
            'type'                     => 'decimal',
            'backend'                  => 'Magento\Catalog\Model\Product\Attribute\Backend\Price',
            'input'                    => 'price',
            'label'                    => 'Handling Fee',
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
        /* ------ shipperhq_volume_weight -------- */
        $catalogSetup->addAttribute(Product::ENTITY, 'shipperhq_volume_weight', [
            'type'                     => 'varchar',
            'input'                    => 'text',
            'label'                    => 'Volume Weight',
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
            'used_in_product_listing'  => false,
            'note'                     => 'This value is only used in conjunction with shipping filters'
        ]);
        /* ------ shipperhq_declared_value -------- */
        $catalogSetup->addAttribute(Product::ENTITY, 'shipperhq_declared_value', [
            'type'                     => 'decimal',
            'backend'                  => 'Magento\Catalog\Model\Product\Attribute\Backend\Price',
            'input'                    => 'price',
            'label'                    => 'Declared Value',
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
            'used_in_product_listing'  => false,
            'note'                     => 'The deemed cost of this product for customs & insurance purposes'
        ]);
        /* ------ ship_separately -------- */
        $catalogSetup->addAttribute(Product::ENTITY, 'ship_separately', [
            'type'                     => 'int',
            'input'                    => 'boolean',
            'label'                    => 'Ship Separately',
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
        /* ------ shipperhq_dim_group -------- */
        $catalogSetup->addAttribute(Product::ENTITY, 'shipperhq_dim_group', [
            'type'                     => 'int',
            'backend'                  => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
            'frontend'                 => '',
            'label'                    => 'ShipperHQ Packing Rule',
            'input'                    => 'select',
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
        /* ------ ship_length -------- */
        $catalogSetup->addAttribute(Product::ENTITY, 'ship_length', [
            'type'                     => 'decimal',
            'input'                    => 'text',
            'label'                    => 'Dimension Length',
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
        /* ------ ship_width -------- */
        $catalogSetup->addAttribute(Product::ENTITY, 'ship_width', [
            'type'                     => 'decimal',
            'input'                    => 'text',
            'label'                    => 'Dimension Width',
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
        /* ------ ship_height -------- */
        $catalogSetup->addAttribute(Product::ENTITY, 'ship_height', [
            'type'                     => 'decimal',
            'input'                    => 'text',
            'label'                    => 'Dimension Height',
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
        /* ------ shipperhq_poss_boxes -------- */
        $catalogSetup->addAttribute(Product::ENTITY, 'shipperhq_poss_boxes', [
            'type'                     => 'text',
            'backend'                  => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
            'input'                    => 'multiselect',
            'label'                    => 'Possible Packing Boxes',
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
        /* ------ shipperhq_malleable_product -------- */
        $catalogSetup->addAttribute(Product::ENTITY, 'shipperhq_malleable_product', [
            'type'                     => 'int',
            'input'                    => 'boolean',
            'label'                    => 'Malleable Product',
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
            'used_in_product_listing'  => false,
            'note'                     => 'Ignore if unsure. Indicates the product dimensions can be adjusted to fit box',
        ]);
        /* ------ shipperhq_master_boxes -------- */
        $catalogSetup->addAttribute(Product::ENTITY, 'shipperhq_master_boxes', [
            'type'                     => 'text',
            'backend'                  => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
            'input'                    => 'multiselect',
            'label'                    => 'Master Packing Boxes',
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
        $dimAttributeCodes = [
            'ship_separately'      => '2',
            'shipperhq_dim_group'  => '1',
            'ship_length'          => '10',
            'ship_width'           => '11',
            'ship_height'          => '12',
            'shipperhq_poss_boxes' => '20'
        ];
        foreach ($attributeSetArr as $attributeSetId) {
            //SHQ16-2123 handle migrated instances from M1 to M2
            $migrateGroupId = $catalogSetup->getAttributeGroup($entityTypeId, $attributeSetId, 'migration-dimensional-shipping');
            $existingDimAttributeIds = [];
            if ($migrateGroupId) {
                $existingDimAttributeIds = $this->getNonShqAttributeIds($catalogSetup, 'migration-dimensional-shipping', $attributeSetId);
                $catalogSetup->removeAttributeGroup($entityTypeId, $attributeSetId, 'migration-dimensional-shipping');
            }
            $attributeGroupId = $catalogSetup->getAttributeGroup(
                $entityTypeId,
                $attributeSetId,
                'Dimensional Shipping'
            );
            if (!$attributeGroupId) {
                $catalogSetup->addAttributeGroup($entityTypeId, $attributeSetId, 'Dimensional Shipping', '100');
            }
            $attributeGroupId = $catalogSetup->getAttributeGroupId(
                $entityTypeId,
                $attributeSetId,
                'Dimensional Shipping'
            );
            $ourDimAttributeIds = [];
            foreach ($dimAttributeCodes as $code => $sort) {
                $attributeId = $catalogSetup->getAttributeId($entityTypeId, $code);
                $ourDimAttributeIds[] = $attributeId;
                $catalogSetup->addAttributeToGroup(
                    $entityTypeId,
                    $attributeSetId,
                    $attributeGroupId,
                    $attributeId,
                    $sort
                );
            }
            // SHQ18-2825 Add any attributes that were in migration-dimensional-shipping that were not our attributes back
            if (count($existingDimAttributeIds)) {
                $attributeIdsToAdd = array_diff($existingDimAttributeIds, $ourDimAttributeIds);
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
}
