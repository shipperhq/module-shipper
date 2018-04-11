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

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Shipping data helper
 */
class LogAssist
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * LogAssist constructor.
     *
     * @param ScopeConfigInterface $config
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(ScopeConfigInterface $config, \Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->config = $config;
    }

    /**
     *
     * @param $module
     * @param $logData
     */
    public function postDebug($module, $message, $data, array $context = [])
    {
        $this->logger->debug($this->getMessage($module, $message, $data), $context);
    }

    private function getMessage($module, $message, $data)
    {
        $data = is_string($data) ? $data : var_export($data, true);
        return $module . '-- ' . $message . '-- ' . $data;
    }

    /**
     *
     * @param $module
     * @param $logData
     */
    public function postInfo($module, $message, $data, array $context = [])
    {
        $this->logger->info($this->getMessage($module, $message, $data), $context);
    }

    /**
     *
     * @param $module
     * @param $logData
     */
    public function postWarning($module, $message, $data, array $context = [])
    {
        $this->logger->warning($this->getMessage($module, $message, $data), $context);
    }

    /**
     *
     * @param $module
     * @param $logData
     */
    public function postCritical($module, $message, $data, array $context = [])
    {
        $this->logger->warning($this->getMessage($module, $message, $data), $context);
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
        return $this->config->isSetFlag('carriers/shipper/debug', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
