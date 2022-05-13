<?php
/**
 *
 * ShipperHQ Shipping Module
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * Shipper HQ Shipping
 *
 * @category ShipperHQ
 * @package ShipperHQ_Shipping_Carrier
 * @copyright Copyright (c) 2015 Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */

/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace ShipperHQ\Shipper\Model\Product\Attribute\Source;

class FreightClass extends \Magento\Eav\Model\Entity\Attribute\Source\Config
{
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory
     */
    protected $optionFactory;

    /**
     * Construct
     *
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $optionFactory
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $optionFactory
    ) {
        $this->storeManager = $storeManager;
        $this->optionFactory = $optionFactory;
    }

    /**
     * Retrieve All options
     *
     * @return array
     */
    public function getAllOptions()
    {
        $arr = $this->toOptionArray();
        array_unshift($arr, ['value' => '', 'label' => __('--- Use Default ---')]);
        return $arr;
    }

    public function toOptionArray()
    {
        return array_map(
            function ($v) {
                return ['value' => $v, 'label' => (string)$v];
            },
            [
                50, 55, 60, 65, 70, 77.5, 85, 92.5, 100,
                110, 125, 150, 175, 200, 250, 300, 400, 500,
            ]
        );
    }

    /**
     * SHQ18-1019 Resolve issue with function name and type.
     *
     * Thanks to @dfg-ck on GitHub for this fix
     */
    public function getFlatColumns()
    {
        $columns = [
            $this->getAttribute()->getAttributeCode() => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'unsigned' => false,
                'nullable' => true,
                'default' => null,
                'extra' => null
            ]
        ];
        return $columns;
    }

    public function getFlatUpdateSelect($store)
    {
        /** @var $option \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option */
        $option = $this->optionFactory->create();
        return $option->getFlatUpdateSelect($this->getAttribute(), $store, false);
    }
}
