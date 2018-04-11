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
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * ShipperHQ Shipper module observer
 */
class SaveShippingAdmin implements ObserverInterface
{
    /**
     * @var \ShipperHQ\Shipper\Helper\CarrierGroup
     */
    private $carrierGroupHelper;

    /**
     * @var \ShipperHQ\Shipper\Helper\LogAssist
     */
    private $shipperLogger;
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;
    /**
     * @var \ShipperHQ\Common\Model\Quote\Service
     */
    private $quoteService;
    /**
     * Application Event Dispatcher
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;
    /**
     * @var DataObjectFactory
     */
    private $objectFactory;
    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @param ScopeConfigInterface $config
     * @param  \ShipperHQ\Shipper\Helper\CarrierGroup $carrierGroupHelper
     * @param  \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger
     * @param \Magento\Framework\Registry $registry
     * @param \ShipperHQ\Common\Model\Quote\Service $quoteService
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param  DataObjectFactory $objectFactory
     */
    public function __construct(
        ScopeConfigInterface $config,
        \ShipperHQ\Shipper\Helper\CarrierGroup $carrierGroupHelper,
        \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger,
        \Magento\Framework\Registry $registry,
        \ShipperHQ\Common\Model\Quote\Service $quoteService,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        DataObjectFactory $objectFactory
    ) {
        $this->carrierGroupHelper = $carrierGroupHelper;
        $this->shipperLogger = $shipperLogger;
        $this->registry = $registry;
        $this->quoteService = $quoteService;
        $this->eventManager = $eventManager;
        $this->objectFactory = $objectFactory;
        $this->config = $config;
    }

    /**
     * Record order shipping information after order is placed
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        if ($this->config->isSetFlag('carriers/shipper/active', ScopeInterface::SCOPE_STORES)) {
            $requestData = $observer->getRequestModel()->getPost();
            if (isset($requestData['order'])) {
                $orderData = $requestData['order'];
                $quote = $observer->getSession()->getQuote();
                if (!empty($orderData['shipping_method'])) {
                    $additionalDetail = $this->objectFactory->create();
                    $this->eventManager->dispatch(
                        'shipperhq_additional_detail_admin',
                        [
                            'order_data' => $orderData,
                            'additional_detail' => $additionalDetail,
                            'shipping_address' => $quote->getShippingAddress()
                        ]
                    );
                    $this->shipperLogger->postDebug(
                        'ShipperHQ Shipper',
                        'Persisting admin shipping details',
                        $additionalDetail
                    );
                    $shippingMethod = $orderData['shipping_method'];
                    if (!empty($orderData['custom_price'])) {
                        $this->processAdminShipping($orderData, $quote);
                    }
                    $additionalDetailArray = $additionalDetail->convertToArray();
                    $this->carrierGroupHelper->saveCarrierGroupInformation(
                        $quote->getShippingAddress(),
                        $shippingMethod,
                        $additionalDetailArray
                    );
                    if (strstr($shippingMethod, 'shipperadmin') && $requestData['collect_shipping_rates'] === 1) {
                        $observer->getRequestModel()->setPostValue('collect_shipping_rates', 0);
                    }
                }
            }
        }
    }

    private function processAdminShipping($data, $quote)
    {
        $found = false;
        $customCarrierGroupData = [];
        if (isset($data['custom_price'])) {
            $adminData = ['customPrice' => $data['custom_price']];
            if (isset($data['custom_description'])) {
                $adminData['customCarrier'] = $data['custom_description'];
                $found = true;
            }
            //use CG id here
            $customCarrierGroupData[] = $adminData;
        }

        if ($found) {
            $shippingAddress = $quote->getShippingAddress();
            $this->quoteService->cleanDownRates($shippingAddress, 'shipperadmin', '');
            $detail = $this->objectFactory->create();
            $detail->addData($customCarrierGroupData);
            $this->registry->register('shqadminship_data', $detail);
            $storedLimitCarrier = $shippingAddress->getLimitCarrier();
            $shippingAddress->setLimitCarrier('shipperadmin');
            $rateFound = $shippingAddress->requestShippingRates();
            $shippingAddress->setLimitCarrier($storedLimitCarrier);
        } else {
            $this->registry->unregister('shqadminship_data');
        }
    }
}
