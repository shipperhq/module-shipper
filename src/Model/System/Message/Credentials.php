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

class Credentials implements \Magento\Framework\Notification\MessageInterface
{
    const SHIPPERHQ_INVALID_CREDENTIALS_SUPPLIED = 'carriers/shipper/invalid_credentials_supplied';
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;
    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @param ScopeConfigInterface $config
     * @param \Magento\Framework\UrlInterface $urlBuilder
     */
    public function __construct(
        ScopeConfigInterface $config,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->config = $config;
    }

    /**
     * Retrieve unique message identity
     *
     * @return string
     */
    public function getIdentity()
    {
        return hash('sha256', 'SHIPPERHQ_CREDENTIALS_INVALID');
    }

    /**
     * Check whether
     *
     * @return bool
     */
    public function isDisplayed()
    {
        if ($this->config->isSetFlag('carriers/shipper/active', ScopeInterface::SCOPE_STORES)) {
            if ($this->config->getValue(self::SHIPPERHQ_INVALID_CREDENTIALS_SUPPLIED, ScopeInterface::SCOPE_STORES)) {
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
            'Your ShipperHQ credentials saved in Magento are invalid.'
                . ' You will no longer receive shipping rates until this is rectified.'
        ) . ' ';
        $url = $this->urlBuilder->getUrl('adminhtml/system_config/edit/section/carriers');
        $message .= __(
            'Click here to go to <a href="%1">Shipping Method Configuration</a> and enter correct credentials.',
            $url
        );
        return $message;
    }

    /**
     * Retrieve problem management url
     *
     * @return string|null
     */
    public function getLink()
    {
        return $this->urlBuilder->getUrl('adminhtml/system_config/edit/section/carriers');
    }

    /**
     * Retrieve message severity
     *
     * @return int
     */
    public function getSeverity()
    {
        return \Magento\Framework\Notification\MessageInterface::SEVERITY_CRITICAL;
    }
}
