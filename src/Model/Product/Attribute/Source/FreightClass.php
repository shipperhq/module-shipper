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
        $freight_class = [
            50 => '50',
            55 => '55',
            60 => '60',
            65 => '65',
            70 => '70',
            77.5 => '77.5',
            85 => '85',
            92.5 => '92.5',
            100 => '100',
            110 => '110',
            125 => '125',
            150 => '150',
            175 => '175',
            200 => '200',
            250 => '250',
            300 => '300',
            400 => '400',
            500 => '500',
        ];

        foreach ($freight_class as $k => $v) {
            $arr[] = ['value' => $k, 'label' => $v];
        }

        return $arr;
    }

    public function getFlatColums()
    {
        $columns = [
            $this->getAttribute()->getAttributeCode() => [
                'type' => 'int',
                'unsigned' => false,
                'is_null' => true,
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
