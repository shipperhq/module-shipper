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
 * Shipping data helper
 */
class Rest extends Data
{
    protected static $wsTimeout;

    /*
     * @var \ShipperHQ\Lib\Helper\Rest
     */
    protected $restHelper;
    /*
     * $var Data $shipperHelperData
     */

    /**
     * @param \ShipperHQ\Lib\Helper\Rest $restHelper
     * @param Data $shipperHelperData
     */
    public function __construct(\ShipperHQ\Lib\Helper\Rest $restHelper,
                                Data $shipperHelperData)
    {
        $this->restHelper = $restHelper;
        $this->shipperHelperData = $shipperHelperData;
        $this->restHelper->setBaseUrl($this->getGatewayUrl());
    }

    /**
     * Retrieve url for getting allowed methods
     * @return string
     */
    public function getAllowedMethodGatewayUrl()
    {
        return $this->restHelper->getAllowedMethodGatewayUrl();
    }

    /**
     * Retrieve url for getting shipping rates
     * @return string
     */
    public function getRateGatewayUrl()
    {
        return  $this->restHelper->getRateGatewayUrl();

    }

    /*
     * *Retrieve url for retrieving attributes
     */
    public function getAttributeGatewayUrl()
    {
        return $this->restHelper->getAttributeGatewayUrl();
    }

    /*
     * *Retrieve url for retrieving attributes
     */
    public function getCheckSynchronizedUrl()
    {
        return $this->restHelper->getCheckSynchronizedUrl();
    }

    /*
     * Retrieve configured timeout for webservice
     */
    public function getWebserviceTimeout()
    {

        if (self::$wsTimeout==NULL) {
            $timeout =  $this->shipperHelperData->getConfigValue('carriers/shipper/ws_timeout');
            if(!is_numeric($timeout) || $timeout < 30) {
                $timeout = 30;
            }
            self::$wsTimeout = $timeout;
        }
        return self::$wsTimeout;
    }


    /**
     * Returns url to use - live if present, otherwise dev
     * @return array
     */
    protected function getGatewayUrl()
    {
        $live = $this->cleanUpUrl($this->shipperHelperData->getConfigValue('carriers/shipper/live_url'));

        $test = $this->cleanUpUrl($this->shipperHelperData->getConfigValue('carriers/shipper/url'));
        return $this->shipperHelperData->getConfigValue('carriers/shipper/sandbox_mode') ? $test : $live;
    }

    protected function cleanUpUrl($urlStart)
    {
        $url = trim($urlStart);
        $lastChar = substr("abcdef", -1);
        if($lastChar != '/') {
            $url .= '/';
        }
        return $url;
    }
}