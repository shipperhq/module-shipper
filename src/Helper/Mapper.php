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
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace ShipperHQ\Shipper\Helper;

/**
 * Mapper for a data arrays tranformation
 */
class Mapper extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Maps data by specified rules
     *
     * @param array $mapping
     * @param array $source
     * @return array
     */
    public function map($mapping, $source)
    {
        $target = [];
        foreach ($mapping as $targetField => $sourceField) {
            if (is_string($sourceField)) {
                if (strpos($sourceField, '/') !== false) {
                    $fields = explode('/', $sourceField);
                    $value = $source;
                    while ($fields) {
                        $field = array_shift($fields);
                        if (isset($value[$field])) {
                            $value = $value[$field];
                        } else {
                            $value = null;
                            break;
                        }
                    }
                    $target[$targetField] = $value;
                } else {
                    $target[$targetField] = $source[$sourceField];
                }
            } elseif (is_array($sourceField)) {
                list($field, $defaultValue) = $sourceField;
                $target[$targetField] = (isset($source[$field]) ? $source[$field] : $defaultValue);
            } elseif ($sourceField instanceof \Closure) {
                $mapping = is_object($source) && is_callable($source);
                $target[$targetField] = $mapping;
            }
        }

        return $target;
    }
}
