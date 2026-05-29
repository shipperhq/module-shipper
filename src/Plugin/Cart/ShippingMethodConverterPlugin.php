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


namespace ShipperHQ\Shipper\Plugin\Cart;

use Magento\Quote\Api\Data\ShippingMethodExtensionFactory;
use Magento\Quote\Model\Cart\ShippingMethodConverter;
use Magento\Quote\Model\Quote\Address\Rate;

class ShippingMethodConverterPlugin
{
    /**
     * @var ShippingMethodExtensionFactory
     */
    private $shippingMethodExtensionFactory;

    public function __construct(
        ShippingMethodExtensionFactory $shippingMethodExtensionFactory
    ) {
        $this->shippingMethodExtensionFactory = $shippingMethodExtensionFactory;
    }

    /**
     * Set additional information for shipping method
     *
     * @param ShippingMethodConverter $subject
     * @param                                                   $result
     * @param Rate           $rateModel         The rate model.
     * @param string                                            $quoteCurrencyCode The quote currency code.
     *
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface Shipping method data object
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterModelToDataObject(
        ShippingMethodConverter $subject,
        $result,
        Rate $rateModel,
        $quoteCurrencyCode
    ) {

        $extensionAttributes = $result->getExtensionAttributes();
        if ($extensionAttributes &&
            ($extensionAttributes->getTooltip() || $rateModel->getTooltip() == '') &&
            ($extensionAttributes->getCustomDuties() || $rateModel->getCustomDuties() == '') &&
            ($extensionAttributes->getHideNotifications() || $rateModel->getHideNotifications() == '')
        ) {
            return $result;
        }

        $shippingMethodExtension = $extensionAttributes ?
            $extensionAttributes : $this->shippingMethodExtensionFactory->create();
        $shippingMethodExtension->setTooltip($rateModel->getTooltip());
        $shippingMethodExtension->setCustomDuties($rateModel->getCustomDuties());
        $shippingMethodExtension->setHideNotifications($rateModel->getHideNotifications());
        $result->setExtensionAttributes($shippingMethodExtension);

        return $result;
    }
}
