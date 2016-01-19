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

use ShipperHQ\Shipper\Helper\Config;

/**
 * Shipping data helper
 */
class Logger extends  \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var Data
     */
    private $shipperDataHelper;

    public function __construct(
        \ShipperHQ\Shipper\Helper\Data $shipperDataHelper,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->shipperDataHelper = $shipperDataHelper;
    }
    /**
     * Log debug data to file
     *
     * @param mixed $debugData
     * @return void
     */
    public function debug($debugData)
    {
        if ($this->shipperDataHelper->getConfigValue('carriers/shipper/debug')) {
            $this->logger->debug(var_export($debugData, true));
        }
    }

    /**
     * TODO push out to separate logger
     *
     * @param $module
     * @param $debugData
     * @param $errorDetailsT
     */
    public function postInfo($module, $debugData, $payload) {
        if ($this->shipperDataHelper->getConfigValue('carriers/shipper/debug')) {
            $this->logger->info(var_export($module.': '.$debugData, true));
            $this->logger->info(var_export($payload, true));
        }
    }

    /**
     * TODO push out to separate logger
     *
     * @param $module
     * @param $debugData
     * @param $errorDetailsT
     */
    public function postDebug($module, $debugData, $payload) {
        if ($this->shipperDataHelper->getConfigValue('carriers/shipper/debug')) {
            $this->logger->debug(var_export($module.': '.$debugData, true));
            $this->logger->debug(var_export($payload, true));
        }
    }


    /**
     * TODO push out to separate logger
     *
     * @param $module
     * @param $debugData
     * @param $errorDetailsT
     */
    public function postWarning($module, $debugData, $payload) {
        if ($this->shipperDataHelper->getConfigValue('carriers/shipper/debug')) {
            $this->logger->warning(var_export($module.': '.$debugData, true));
            $this->logger->warning(var_export($payload, true));
        }
    }

    /**
     * Define if debugging is enabled
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @api
     */
    public function getDebugFlag()
    {
        return $this->shipperDataHelper->getConfigValue('carriers/shipper/debug');
    }
}