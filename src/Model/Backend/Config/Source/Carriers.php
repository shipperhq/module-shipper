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

namespace ShipperHQ\Shipper\Model\Backend\Config\Source;

class Carriers {
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Shipping\Model\Config
     */
    private $shippingConfig;
    /**
     * @var \ShipperHQ\Shipper\Helper\Data
     */
    private $shipperDataHelper;

    public function __construct( \ShipperHQ\Shipper\Helper\Data $shipperDataHelper,
                                 \Magento\Backend\Block\Template\Context $context,
                                 \Magento\Shipping\Model\Config $shippingConfig
    ) {

        $this->shippingConfig = $shippingConfig;
        $this->storeManager = $context->getStoreManager();
        $this->shipperDataHelper = $shipperDataHelper;
    }


    public function toOptionArray() {

        $arr = [];

        $carriers = $this->shippingConfig->getAllCarriers($this->storeManager->getStore());

        foreach ($carriers as $carrierCode=>$carrierModel) {
            $carrierTitle = $this->shipperDataHelper->getConfigValue('carriers/' . $carrierCode . '/title');
            if (strpos($carrierCode, 'shipper') === 0 || $carrierTitle == '') {
                continue;
            }
            if($this->shipperDataHelper->getConfigValue('carriers/'.$carrierCode.'/model') == 'ShipperHQ\Shipper\Model\Carrier\Shipper') {
                continue;
            }
           $arr[] = ['value' => $carrierCode, 'label' => $carrierTitle];
        }
        array_unshift($arr, ['value' => false, 'label' => __('No Carrier')]);

        return $arr;

    }

}