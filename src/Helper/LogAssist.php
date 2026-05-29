<?php
/**
 * ShipperHQ
 *
 * @category ShipperHQ
 * @package ShipperHQ\Shipper
 * @copyright Copyright (c) 2015 Zowta LTD and Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */


namespace ShipperHQ\Shipper\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * ShipperHQ Log Helper.
 * Contains methods to output logging to admin and file system log
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
     * Logs a debug message
     *
     * @param $module
     * @param $message
     * @param $data
     * @param array $context
     */
    public function postDebug($module, $message, $data, array $context = []): void
    {
        $this->logger->debug($this->getMessage($module, $message, $data), $context);
    }

    /**
     * Gets the log message as a string
     *
     * @param $module
     * @param $message
     * @param $data
     * @return string
     */
    private function getMessage($module, $message, $data): string
    {
        $data = is_string($data) ? $data : var_export($data, true);
        return $module . '-- ' . $message . '-- ' . $data;
    }

    /**
     * Logs a info message
     * @param $module
     * @param $message
     * @param $data
     * @param array $context
     */
    public function postInfo($module, $message, $data, array $context = []): void
    {
        $this->logger->info($this->getMessage($module, $message, $data), $context);
    }

    /**
     * Logs a warning message
     *
     * @param $module
     * @param $message
     * @param $data
     * @param array $context
     */
    public function postWarning($module, $message, $data, array $context = []): void
    {
        $this->logger->warning($this->getMessage($module, $message, $data), $context);
    }

    /**
     * Posts a critical log
     *
     * @param $module
     * @param $message
     * @param $data
     * @param array $context
     */
    public function postCritical($module, $message, $data, array $context = []): void
    {
        $this->logger->critical($this->getMessage($module, $message, $data), $context);
    }

    /**
     * Define if debugging is enabled
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @api
     */
    public function getDebugFlag(): bool
    {
        return $this->config->isSetFlag('carriers/shipper/debug', ScopeInterface::SCOPE_STORE);
    }
}
