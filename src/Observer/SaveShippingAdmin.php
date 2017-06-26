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

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;

/**
 * ShipperHQ Shipper module observer
 */
class SaveShippingAdmin implements ObserverInterface
{
    /**
     * @var \ShipperHQ\Shipper\Helper\Data
     */
    private $shipperDataHelper;

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
    /*
     * @var \ShipperHQ\Common\Model\Quote\Service
     */
    protected $quoteService;

    /**
     * @param \ShipperHQ\Shipper\Helper\Data $shipperDataHelper
     * @param  \ShipperHQ\Shipper\Helper\CarrierGroup $carrierGroupHelper
     * @param  \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger
     * @param \Magento\Framework\Registry $registry
     * @param \ShipperHQ\Common\Model\Quote\Service $quoteService
     */
    public function __construct(
        \ShipperHQ\Shipper\Helper\Data $shipperDataHelper,
        \ShipperHQ\Shipper\Helper\CarrierGroup $carrierGroupHelper,
        \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger,
        \Magento\Framework\Registry $registry,
        \ShipperHQ\Common\Model\Quote\Service $quoteService
    ) {
        $this->shipperDataHelper = $shipperDataHelper;
        $this->carrierGroupHelper = $carrierGroupHelper;
        $this->shipperLogger = $shipperLogger;
        $this->registry = $registry;
        $this->quoteService = $quoteService;
    }
    /**
     * Record order shipping information after order is placed
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        if ($this->shipperDataHelper->getConfigValue('carriers/shipper/active')) {
            $requestData = $observer->getRequestModel()->getPost();
            if (isset($requestData['order'])) {
                $orderData = $requestData['order'];
                $quote = $observer->getSession()->getQuote();
                if (!empty($orderData['shipping_method'])) {
                    $shippingMethod = $orderData['shipping_method'];
                    if(!empty($orderData['custom_price'])) {
                        $this->processAdminShipping($orderData, $quote);
                    }
                    $this->carrierGroupHelper->saveCarrierGroupInformation($quote->getShippingAddress(), $shippingMethod);
                    if(strstr($shippingMethod, 'shipperadmin') && $requestData['collect_shipping_rates'] === 1) {
                        $observer->getRequestModel()->setPostValue('collect_shipping_rates', 0);
                    }
                }
            }
        }
    }

    protected function processAdminShipping($data, $quote)
    {
        $found = false;
        $customCarrierGroupData = array();
        if (isset($data['custom_price'])) {
            $adminData  = array('customPrice' => $data['custom_price']);
            if (isset($data['custom_description'])) {
                $adminData['customCarrier'] = $data['custom_description'];
                $found = true;
            }
            //use CG id here
            $customCarrierGroupData[] = $adminData;
        }

        if ($found) {
            $shippingAddress =  $quote->getShippingAddress();
            $this->quoteService->cleanDownRates($shippingAddress, 'shipperadmin', '');
            $this->registry->register('shqadminship_data', new \Magento\Framework\DataObject($customCarrierGroupData));
            $storedLimitCarrier = $shippingAddress->getLimitCarrier();
            $shippingAddress->setLimitCarrier('shipperadmin');
            $rateFound = $shippingAddress->requestShippingRates();
            $shippingAddress->setLimitCarrier($storedLimitCarrier);
        } else {
            $this->registry->unregister('shqadminship_data');
        }
    }
}
