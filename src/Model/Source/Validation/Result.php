<?php
/**
 * Magento
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
 * @category    Mage
 * @package     Mage_Core
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 *
 * Webshopapps Shipping Module
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
 * @copyright Copyright (c) 2014 Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */

namespace ShipperHQ\Shipper\Model\Source\Validation;

class Result extends \Magento\Framework\Model\AbstractModel
{
    const NOT_VALIDATED = 'UNCHECKED';
    const VALID = 'EXACT_MATCH';
    const VALID_CORRECTED = 'CORRECTED_EXACT_MATCH';
    const AMBIGUOUS = 'NEAR_MATCH_WITH_OPTIONS';
    const INVALID = 'INVALID';
    const ERROR = 'ERROR';
    const CUSTOMER_OVERRIDE = 'CUSTOMER_OVERRIDE';
    const MANUAL_OVERRIDE = 'MANUAL_OVERRIDE';

    const NOT_VALIDATED_PERCENTAGE = 'NOT_VALIDATED_PERCENTAGE';
    const NOT_VALIDATED_NO_CARRIER = 'NOT_VALIDATED_NO_CARRIER';
    const NOT_VALIDATED_DISABLED = 'NOT_VALIDATED_DISABLED';
    const NOT_AUTHORIZED = 'NOT_AUTHORIZED';
    const VALIDATION_NOT_ENABLED = 'VALIDATION_NOT_ENABLED';
    const COUNTRY_NOT_SUPPORTED = 'COUNTRY_NOT_SUPPORTED';


    protected $options;


    public function getAllOptions()
    {
        if (!$this->options) {
            $this->options = $this->toOptionArray();
        }
        return $this->options;
    }

    public function toOptionArray()
    {
        $possibleResults = array(
            self::NOT_VALIDATED    	=> self::NOT_VALIDATED,
            self::VALID      =>  self::VALID ,
            self::AMBIGUOUS     =>  self::AMBIGUOUS,
            self::MANUAL_OVERRIDE		=> self::MANUAL_OVERRIDE
        );

        $arr = array();
        foreach ($possibleResults as $k=>$v) {
            $arr[] = array('value'=>$k, 'label'=>__($v));
        }
        return $arr;
    }

}