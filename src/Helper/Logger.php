<?php
/* ExtName
 *
 * User        karen
 * Date        9/9/15
 * Time        1:25 AM
 * @category   Webshopapps
 * @package    Webshopapps_ExtnName
 * @copyright   Copyright (c) 2015 Zowta Ltd (http://www.WebShopApps.com)
 *              Copyright, 2015, Zowta, LLC - US license
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
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