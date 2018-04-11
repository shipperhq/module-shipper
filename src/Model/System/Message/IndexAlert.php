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
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
        return md5('SHIPPERHQ_INDEX_ALERT');
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
