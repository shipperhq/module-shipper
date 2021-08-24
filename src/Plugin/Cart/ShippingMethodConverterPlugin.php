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

namespace ShipperHQ\Shipper\Plugin\Cart;

class ShippingMethodConverterPlugin
{
    /**
     * @var \Magento\Quote\Api\Data\ShippingMethodExtensionFactory
     */
    private $shippingMethodExtensionFactory;

    public function __construct(
        \Magento\Quote\Api\Data\ShippingMethodExtensionFactory $shippingMethodExtensionFactory
    ) {
        $this->shippingMethodExtensionFactory = $shippingMethodExtensionFactory;
    }

    /**
     * Set additional information for shipping method
     *
     * @param \Magento\Quote\Model\Cart\ShippingMethodConverter $subject
     * @param                                                   $result
     * @param \Magento\Quote\Model\Quote\Address\Rate           $rateModel         The rate model.
     * @param string                                            $quoteCurrencyCode The quote currency code.
     *
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface Shipping method data object
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterModelToDataObject(
        \Magento\Quote\Model\Cart\ShippingMethodConverter $subject,
        $result,
        \Magento\Quote\Model\Quote\Address\Rate $rateModel,
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
