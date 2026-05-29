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

class IndexAlert implements \Magento\Framework\Notification\MessageInterface
{
    /**
     * @var \Magento\Indexer\Model\IndexerFactory
     */
    private $indexFactory;
    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @param \Magento\Indexer\Model\IndexerFactory $indexerFactory
     * @param ScopeConfigInterface $config
     */
    public function __construct(
        \Magento\Indexer\Model\IndexerFactory $indexerFactory,
        ScopeConfigInterface $config
    ) {
        $this->indexFactory = $indexerFactory;
        $this->config = $config;
    }

    /**\
     * Retrieve unique message identity
     *
     * @return string
     */
    public function getIdentity()
    {
        return hash('sha256', 'SHIPPERHQ_INDEX_ALERT');
    }

    /**
     * Check whether
     *
     * @return bool
     */
    public function isDisplayed()
    {
        if ($this->config->isSetFlag('carriers/shipper/active', ScopeInterface::SCOPE_STORES)) {
            $eavIndexer = $this->indexFactory->create()->load('catalog_product_attribute');

            if ($eavIndexer->getStatus() != \Magento\Framework\Indexer\StateInterface::STATUS_VALID) {
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
        $message = __('Product EAV index being out of date may cause incorrect shipping rates from ShipperHQ.' .
            ' We strongly recommend you reindex');

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
