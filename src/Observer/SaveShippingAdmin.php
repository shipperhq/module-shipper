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
use ShipperHQ\Shipper\Service\Backend\GetAdminShipData;
use ShipperHQ\Shipper\Service\Backend\SetAdminShipData;
use ShipperHQ\Shipper\Service\Backend\UnsetAdminShipData;
use ShipperHQ\Shipper\Model\Backend\AdminShipDataFactory;

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
     * @var SetAdminShipData
     */
    private $setAdminShipData;
    /**
     * @var UnsetAdminShipData
     */
    private $unsetAdminShipData;
    /**
     * @var GetAdminShipData
     */
    private $getAdminShipData;
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
     * @var AdminShipDataFactory
     */
    private $adminShipDataFactory;
    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @param ScopeConfigInterface $config
     * @param \ShipperHQ\Shipper\Helper\CarrierGroup $carrierGroupHelper
     * @param \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger
     * @param SetAdminShipData $setAdminShipData
     * @param UnsetAdminShipData $unsetAdminShipData
     * @param \ShipperHQ\Common\Model\Quote\Service $quoteService
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param DataObjectFactory $objectFactory
     * @param AdminShipDataFactory $adminShipDataFactory
     * @param GetAdminShipData $getAdminShipData
     */
    public function __construct(
        ScopeConfigInterface $config,
        \ShipperHQ\Shipper\Helper\CarrierGroup $carrierGroupHelper,
        \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger,
        SetAdminShipData $setAdminShipData,
        UnsetAdminShipData $unsetAdminShipData,
        \ShipperHQ\Common\Model\Quote\Service $quoteService,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        DataObjectFactory $objectFactory,
        AdminShipDataFactory $adminShipDataFactory,
        GetAdminShipData $getAdminShipData
    ) {
        $this->carrierGroupHelper = $carrierGroupHelper;
        $this->shipperLogger = $shipperLogger;
        $this->setAdminShipData = $setAdminShipData;
        $this->unsetAdminShipData = $unsetAdminShipData;
        $this->getAdminShipData = $getAdminShipData;
        $this->quoteService = $quoteService;
        $this->eventManager = $eventManager;
        $this->objectFactory = $objectFactory;
        $this->adminShipDataFactory = $adminShipDataFactory;
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

                    //SHQ18-2575 Changed to check array key exists instead of !empty
                    if (array_key_exists('custom_price', $orderData)) {
                        $this->processAdminShipping($orderData, $quote);
                    }
                    $additionalDetailArray = $additionalDetail->convertToArray();

                    $isCustomMethodSelected = strstr((string) $shippingMethod, 'shipperadmin');
                    if ($isCustomMethodSelected) {
                        $shipData = $this->getAdminShipData->execute();
                        $customMethodTitle = $shipData ? 'Custom Shipping Rate - ' . $shipData->getCustomCarrier() : 'Custom Shipping Rate';
                        $this->carrierGroupHelper->setCustomShippingOnItems(
                            $quote->getShippingAddress(),
                            $customMethodTitle
                        );
                    } else {
                        $this->carrierGroupHelper->saveCarrierGroupInformation(
                            $quote->getShippingAddress(),
                            $shippingMethod,
                            $additionalDetailArray
                        );
                    }
                    if ($isCustomMethodSelected && $requestData['collect_shipping_rates'] === 1) {
                        $observer->getRequestModel()->setPostValue('collect_shipping_rates', 0);
                    }
                }
            }
        }
    }

    private function processAdminShipping($data, $quote)
    {
        $found = false;
        /** @var \ShipperHQ\Shipper\Model\Backend\AdminShipData $adminShipData */
        $adminShipData = $this->adminShipDataFactory->create();
        if (isset($data['custom_price'])) {
            $adminShipData->setCustomPrice($data['custom_price']);
            if (isset($data['custom_description'])) {
                $adminShipData->setCustomCarrier($data['custom_description']);
                $found = true;
            }
        }

        if ($found) {
            $shippingAddress = $quote->getShippingAddress();
            $this->quoteService->cleanDownRates($shippingAddress, 'shipperadmin', '');
            $this->setAdminShipData->execute($adminShipData);
            $storedLimitCarrier = $shippingAddress->getLimitCarrier();
            $shippingAddress->setLimitCarrier('shipperadmin');
            $rateFound = $shippingAddress->requestShippingRates();
            $shippingAddress->setLimitCarrier($storedLimitCarrier);
        } else {
            $this->unsetAdminShipData->execute();
        }
    }
}
