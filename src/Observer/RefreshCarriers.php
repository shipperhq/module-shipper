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

namespace ShipperHQ\Shipper\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * ShipperHQ Shipper module observer
 */
class RefreshCarriers implements ObserverInterface
{
    /**
     * @var \ShipperHQ\Shipper\Model\Carrier\Shipper
     */
    private $shipperCarrier;
    /**
     * @var ManagerInterface
     */
    private $messageManager;
    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @param ScopeConfigInterface $config
     * @param  \ShipperHQ\Shipper\Model\Carrier\Shipper $carrier
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        ScopeConfigInterface $config,
        \ShipperHQ\Shipper\Model\Carrier\Shipper $carrier,
        ManagerInterface $messageManager
    ) {
        $this->shipperCarrier = $carrier;
        $this->messageManager = $messageManager;
        $this->config = $config;
    }

    /**
     * Update saved shipping methods available for ShipperHQ
     *
     * @param EventObserver $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(EventObserver $observer)
    {
        if ($this->config->isSetFlag('carriers/shipper/active', ScopeInterface::SCOPE_STORES)) {
            $refreshResult = $this->shipperCarrier->refreshCarriers();
            if (array_key_exists('error', $refreshResult)) {
                $message = __($refreshResult['error']);
                $this->messageManager->addError($message);
            } else {
                $message = __('%1 carriers have been updated from ShipperHQ', count($refreshResult));
                $this->messageManager->addSuccess($message);
            }
        }
    }
}
