<?php
namespace ShipperHQ\Shipper\Model\Config\Backend\Source;
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

class Carriers  {

    public function __construct(
    ) {}


    public function toOptionArray() {

        $arr = array();

        $storeId = Mage::app()->getStore()->getStoreId() ;

        $carriers = Mage::getSingleton('shipping/config')->getAllCarriers($storeId);

        foreach ($carriers as $carrierCode=>$carrierModel) {
            $carrierTitle = Mage::getStoreConfig('carriers/' . $carrierCode . '/title', $storeId);
            if (strpos($carrierCode, 'shipper') === 0 || $carrierTitle == '') {
                continue;
            }
            if(Mage::getStoreConfig('carriers/'.$carrierCode.'/model') == 'shipperhq_shipper/carrier_shipper') {
                continue;
            }
            $arr[] = array('value' => $carrierCode, 'label' => $carrierTitle);
        }
        array_unshift($arr, array('value' => false, 'label' => __('No Carrier')));

        return $arr;

    }

}