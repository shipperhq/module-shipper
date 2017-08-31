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
namespace ShipperHQ\Shipper\Plugin\Shipping;

class ShippingPlugin
{
    /*
     * Ignore carrier codes
     */
    protected $ignoreCarrierCodes = ['multicarrier', 'shipper', 'calendar', 'pickup'];

    /**
     * @var \ShipperHQ\Shipper\Helper\Data
     */
    protected $shipperDataHelper;

    /**
     * @var \ShipperHQ\Shipper\Helper\LogAssist
     */
    protected $shipperLogger;

    /*
     * @var \Magento\Shipping\Model\Config
     */
    protected $shippingConfig;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        \ShipperHQ\Shipper\Helper\Data $shipperDataHelper,
        \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger,
        \Magento\Shipping\Model\Config $shippingConfig,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->shipperDataHelper = $shipperDataHelper;
        $this->shipperLogger = $shipperLogger;
        $this->shippingConfig = $shippingConfig;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Return array of carriers.
     * If $isActiveOnlyFlag is set to true, will return only active carriers
     *
     * @param string $carrierCode
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     * @return $this
     */
    public function aroundCollectCarrierRates(
        \Magento\Shipping\Model\Shipping $subject,
        \Closure $proceed,
        $carrierCode,
        \Magento\Quote\Model\Quote\Address\RateRequest $request
    ) {
        $limitCarrier = $request->getLimitCarrier();
        $path = 'carriers/'.$carrierCode.'/model';
        $carrierModel = $this->shipperDataHelper->getConfigValue($path);
        if($limitCarrier === null && $carrierModel == 'ShipperHQ\Shipper\Model\Carrier\Shipper'  && $carrierCode !== 'shipper') {
            return $subject;
        }
        $result = $proceed($carrierCode, $request);
        return $result;
    }
}
