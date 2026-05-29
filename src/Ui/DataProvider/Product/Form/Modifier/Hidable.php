<?php
/**
 * ShipperHQ
 *
 * @category ShipperHQ
 * @package ShipperHQ\Shipper
 * @copyright Copyright (c) 2015 Zowta LTD and Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */

namespace ShipperHQ\Shipper\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Model\ProductAttributeGroupRepository;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Api\SearchCriteriaBuilder;

class Hidable extends AbstractModifier
{
    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var ProductAttributeGroupRepository
     */
    protected $productAttributeGroupRepository;

    /**
     * Attribute codes that need to have logic added to make them hidable
     * @var array $hidableAttrs
     */
    protected $hidableAttrs = [
        'shipperhq_poss_boxes',
        'shipperhq_volume_weight',
        'ship_box_tolerance',
        'ship_separately'
    ];

    /**
     * Hidable's constructor.
     * @param LocatorInterface $locator
     * @param ProductAttributeRepositoryInterface $attributeRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ProductAttributeGroupRepository $productAttributeGroupRepository
     */
    public function __construct(
        LocatorInterface $locator,
        ProductAttributeRepositoryInterface $attributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ProductAttributeGroupRepository $productAttributeGroupRepository
    ) {
        $this->locator = $locator;
        $this->attributeRepository = $attributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->productAttributeGroupRepository = $productAttributeGroupRepository;
    }

    /**
     * {@inheritdoc}
     * @since 101.0.0
     */
    public function modifyMeta(array $meta)
    {
        foreach ($this->hidableAttrs as $attr) {
            if ($group = $this->getAttributeGroupCode($attr)) {
                $container = &$meta[$group]['children']['container_' . $attr];
                $node = &$container['children'][$attr]['arguments']['data']['config'];
                $node['visible'] = 'false';
                $node['imports'] = [
                    'visible' => '!${$.provider}:' . self::DATA_SCOPE_PRODUCT . '.'
                        . 'shipperhq_dim_group'
                ];
            }
        }

        return $meta;
    }

    /**
     * Returns the group code for the given attribute (using the current product as it's context)
     *
     * @param string $attrCode
     * @return mixed
     */
    protected function getAttributeGroupCode($attrCode)
    {
        $attributes = $this->getProduct()->getAttributes();

        if (!isset($attributes[$attrCode])) {
            return false;
        }

        $groupId = $attributes[$attrCode]->getAttributeSetInfo()[$this->getAttributeSetId()]['group_id'];

        $group = $this->productAttributeGroupRepository->get($groupId);
        $groupCode = $group->getAttributeGroupCode();

        return $groupCode;
    }

    private function getProduct()
    {
        return $this->locator->getProduct();
    }

    private function getAttributeSetId()
    {
        return $this->getProduct()->getAttributeSetId();
    }

    /**
     * {@inheritdoc}
     * @since 101.0.0
     */
    public function modifyData(array $data)
    {
        return $data;
    }
}
