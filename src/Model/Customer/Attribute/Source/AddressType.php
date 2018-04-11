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
 * @copyright Copyright (c) 2017 Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */

/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace ShipperHQ\Shipper\Model\Customer\Attribute\Source;

class AddressType extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * Address types
     */
    const SHQ_ADDRESS_TYPE_RESIDENTIAL = 'RESIDENTIAL';
    const SHQ_ADDRESS_TYPE_BUSINESS = 'BUSINESS';

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory
     */
    private $attrOptionFactory;

    /**
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory
    ) {
        $this->attrOptionFactory = $attrOptionFactory;
    }

    /**
     * Retrieve All options
     *
     * @return array
     */
    public function getAllOptions()
    {
        $arr = $this->toOptionArray();
        array_unshift($arr, ['value' => '', 'label' => __('--- Unknown ---')]);
        return $arr;
    }

    public function toOptionArray()
    {
        return [
            ['label' => __('Residential'), 'value' => self::SHQ_ADDRESS_TYPE_RESIDENTIAL],
            ['label' => __('Business'), 'value' => self::SHQ_ADDRESS_TYPE_BUSINESS]
        ];
    }
}
