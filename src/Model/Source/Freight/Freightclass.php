<?php
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

namespace ShipperHQ\Shipper\Model\Source\Freight;

class Freightclass extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
    implements
    \Magento\Eav\Model\Entity\Attribute\Source\SourceInterface
{
    public function toOptionArray()
    {
       $freight_class = array(
            50 	=> '50',
            55 	=> '55',
            60 	=> '60',
            65 	=> '65',
            70 	=> '70',
            77.5 	=> '77.5',
            85 	=> '85',
            92.5 	=> '92.5',
            100 	=> '100',
            110 	=> '110',
            125 	=> '125',
            150 	=> '150',
            175 	=> '175',
            200 	=> '200',
            250 	=> '250',
            300 	=> '300',
            400 	=> '400',
            500 	=> '500',
        );

        foreach ($freight_class as $k=>$v) {
            $arr[] = array('value'=>$k, 'label'=>$v);
        }

        return $arr;
    }
    /**
     * Retrieve All options
     *
     * @return array
     */
    public function getAllOptions()
    {
        $arr = $this->toOptionArray();
        array_unshift($arr, array('value'=>'', 'label'=>__('--- Use Default ---')));
        return $arr;
    }
}
