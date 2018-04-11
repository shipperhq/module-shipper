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

namespace ShipperHQ\Shipper\Model\Sales\Quote\Address;

class RatePlugin
{
    /**
     * Set additional information on shipping rate
     *
     * @param \Magento\Quote\Model\Quote\Address\Rate $subject
     * @param callable $proceed
     *
     * @return \Magento\Quote\Model\Quote\Address\Rate
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundImportShippingRate(
        \Magento\Quote\Model\Quote\Address\Rate $subject,
        $proceed,
        \Magento\Quote\Model\Quote\Address\RateResult\AbstractResult $rate
    ) {

        $result = $proceed($rate);
        if ($rate instanceof \Magento\Quote\Model\Quote\Address\RateResult\Error) {
            $result
                ->setCarrierId($rate->getCarrierId())
                ->setCarriergroupId($rate->getCarriergroupId())
                ->setCarriergroup($rate->getCarriergroup());
        } elseif ($rate instanceof \Magento\Quote\Model\Quote\Address\RateResult\Method) {
            $result
                ->setCarriergroupId($rate->getCarriergroupId())
                ->setCarriergroup($rate->getCarriergroup())
                ->setCarrierType($rate->getCarrierType())
                ->setShqDispatchDate($rate->getDispatchDate())
                ->setShqDeliveryDate($rate->getDeliveryDate())
                ->setCarriergroupShippingDetails($rate->getCarriergroupShippingDetails())
                ->setCarrierNotice($rate->getCarrierNotice())
                ->setFreightRate($rate->getFreightRate())
                ->setCustomDescription($rate->getCustomDescription())
                ->setCarrierId($rate->getCarrierId())
                ->setCustomDuties($rate->getCustomDuties())
                ->setTooltip($rate->getTooltip());
        }
        return $result;
    }
}
