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

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Shipping\Model\Config;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Carriers
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Config
     */
    private $shippingConfig;
    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * Carriers constructor.
     * @param ScopeConfigInterface $config
     * @param StoreManagerInterface $storeManager
     * @param Config $shippingConfig
     */
    public function __construct(
        ScopeConfigInterface $config,
        StoreManagerInterface $storeManager,
        Config $shippingConfig
    ) {
        $this->shippingConfig = $shippingConfig;
        $this->storeManager = $storeManager;
        $this->config = $config;
    }

    public function toOptionArray()
    {
        $arr = [];

        $carriers = $this->shippingConfig->getAllCarriers($this->storeManager->getStore());

        $carriers = array_keys($carriers);
        foreach ($carriers as $carrierCode) {
            $carrierTitle = $this->config->getValue(
                'carriers/' . $carrierCode . '/title',
                ScopeInterface::SCOPE_STORES
            );
            if (strpos($carrierCode, 'shipper') === 0 || $carrierTitle == '') {
                continue;
            }
            if ($this->config->getValue(
                'carriers/' . $carrierCode . '/model',
                ScopeInterface::SCOPE_STORES
            ) === 'ShipperHQ\Shipper\Model\Carrier\Shipper'
            ) {
                continue;
            }
            $arr[] = ['value' => $carrierCode, 'label' => $carrierTitle];
        }
        array_unshift($arr, ['value' => false, 'label' => __('No Carrier')]);

        return $arr;
    }
}
