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
