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
use Magento\Store\Model\ScopeInterface;

/**
 * ShipperHQ Shipper module observer
 */
class SaveShippingMulti implements ObserverInterface
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
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @param ScopeConfigInterface $config
     * @param  \ShipperHQ\Shipper\Helper\CarrierGroup $carrierGroupHelper
     * @param  \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger
     */
    public function __construct(
        ScopeConfigInterface $config,
        \ShipperHQ\Shipper\Helper\CarrierGroup $carrierGroupHelper,
        \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger
    ) {
        $this->carrierGroupHelper = $carrierGroupHelper;
        $this->shipperLogger = $shipperLogger;
        $this->config = $config;
    }

    /**
     * Process shipping method and save
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        if ($this->config->isSetFlag('carriers/shipper/active', ScopeInterface::SCOPE_STORES)) {
            $request = $observer->getEvent()->getRequest();
            $shippingMethods = $request->getPost('shipping_method', '');
            if (!is_array($shippingMethods)) {
                return;
            }
            foreach ($shippingMethods as $addressId => $shippingMethod) {
                if (empty($shippingMethod)) {
                    return;
                }
                $quote = $observer->getEvent()->getQuote();
                $addresses = $quote->getAllShippingAddresses();
                $shippingAddress = false;
                foreach ($addresses as $address) {
                    if ($address->getId() == $addressId) {
                        $shippingAddress = $address;
                        break;
                    }
                }
                $this->carrierGroupHelper->saveCarrierGroupInformation($shippingAddress, $shippingMethod);
            }
        }
    }
}
