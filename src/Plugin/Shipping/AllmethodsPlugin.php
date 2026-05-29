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


namespace ShipperHQ\Shipper\Plugin\Shipping;

class AllmethodsPlugin
{
    /*
     * Ignore carrier codes
     */
    private $ignoreCarrierCodes = ['multicarrier', 'shipper', 'calendar', 'pickup'];

    /**
     * @var \ShipperHQ\Shipper\Helper\LogAssist
     */
    private $shipperLogger;

    /**
     * @var \Magento\Shipping\Model\Config
     */
    private $shippingConfig;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger,
        \Magento\Shipping\Model\Config $shippingConfig,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->shipperLogger = $shipperLogger;
        $this->shippingConfig = $shippingConfig;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Return array of carriers.
     * If $isActiveOnlyFlag is set to true, will return only active carriers
     *
     * @param bool $isActiveOnlyFlag
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterToOptionArray(
        \Magento\Shipping\Model\Config\Source\Allmethods $subject,
        $result,
        $isActiveOnlyFlag = false
    ) {
        $methods = [['value' => '', 'label' => '']];
        $carriers = $this->shippingConfig->getAllCarriers();
        foreach ($carriers as $carrierCode => $carrierModel) {
            if ((!$carrierModel->isActive() && (bool)$isActiveOnlyFlag === true) ||
                in_array($carrierCode, $this->ignoreCarrierCodes)) {
                continue;
            }
            if (strstr((string) $carrierCode, 'shq') && $carrierModel instanceof \ShipperHQ\Shipper\Model\Carrier\Shipper) {
                $carrierMethods = $carrierModel->getAllowedMethodsByCode($carrierCode);
            } else {
                $carrierMethods = $carrierModel->getAllowedMethods();
            }
            if (!$carrierMethods) {
                continue;
            }
            $carrierTitle = $this->scopeConfig->getValue(
                'carriers/' . $carrierCode . '/title',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $methods[$carrierCode] = ['label' => $carrierTitle, 'value' => []];
            foreach ($carrierMethods as $methodCode => $methodTitle) {
                $methods[$carrierCode]['value'][] = [
                    'value' => $carrierCode . '_' . $methodCode,
                    'label' => '[' . $carrierCode . '] ' . $methodTitle,
                ];
            }
        }

        $this->shipperLogger->postDebug('ShipperHQ', 'Modifying shipping all methods response', '');
        return $methods;
    }
}
