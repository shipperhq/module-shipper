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


namespace ShipperHQ\Shipper\Model\System\Message;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ModuleAlert implements \Magento\Framework\Notification\MessageInterface
{
    const MODULES_MISSING = 'carriers/shipper/modules_missing';
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;
    /**
     * @var \ShipperHQ\Shipper\Model\Carrier\Processor\CarrierConfigHandler
     */
    private $carrierConfigHandler;
    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @param ScopeConfigInterface $config
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \ShipperHQ\Shipper\Model\Carrier\Processor\CarrierConfigHandler $carrierConfigHandler
     */
    public function __construct(
        ScopeConfigInterface $config,
        \Magento\Framework\UrlInterface $urlBuilder,
        \ShipperHQ\Shipper\Model\Carrier\Processor\CarrierConfigHandler $carrierConfigHandler
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->carrierConfigHandler = $carrierConfigHandler;
        $this->config = $config;
    }

    /**
     * Retrieve unique message identity
     *
     * @return string
     */
    public function getIdentity()
    {
        return hash('sha256', 'SHIPPERHQ_FEATURE_MODULE_ALERT');
    }

    /**
     * Check whether
     *
     * @return bool
     */
    public function isDisplayed()
    {
        if ($this->config->isSetFlag('carriers/shipper/active', ScopeInterface::SCOPE_STORES)) {
            $moduleMissing = $this->config->getValue(self::MODULES_MISSING, ScopeInterface::SCOPE_STORES);
            if ($moduleMissing != '') {
                return true;
            }
        }
        return false;
    }

    /**
     * Retrieve message text
     *
     * @return string
     */
    public function getText()
    {
        $message = __(
            'Your ShipperHQ installation may be missing some modules: '
        ) .
            $this->config->getValue(self::MODULES_MISSING, ScopeInterface::SCOPE_STORES) .
            __('. Please verify with ShipperHQ if these are required') . ' ';
        return $message;
    }

    /**
     * Retrieve message severity
     *
     * @return int
     */
    public function getSeverity()
    {
        return \Magento\Framework\Notification\MessageInterface::SEVERITY_MAJOR;
    }
}
