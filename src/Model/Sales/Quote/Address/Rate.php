<?php

/**
 *
 * Webshopapps Shipping Module
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
 * @copyright Copyright (c) 2014 Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */

namespace ShipperHQ\Shipper\Model\Sales\Quote\Address;

class Rate extends \Magento\Quote\Model\Quote\Address\Rate
{

    public function importShippingRate(\Magento\Quote\Model\Quote\Address\RateResult\AbstractResult $rate)
    {
        if ($rate instanceof Mage_Shipping_Model_Rate_Result_Error) {
            $this
                ->setCode($rate->getCarrier() . '_error')
                ->setCarrier($rate->getCarrier())
                ->setCarrierTitle($rate->getCarrierTitle())
                ->setErrorMessage($rate->getErrorMessage())
                ->setCarrierId($rate->getCarrierId())
                ->setCarriergroupId($rate->getCarriergroupId())
                ->setCarriergroup($rate->getCarriergroup());
        } elseif ($rate instanceof Mage_Shipping_Model_Rate_Result_Method) {
            $this
                ->setCode($rate->getCarrier() . '_' . $rate->getMethod())
                ->setCarrier($rate->getCarrier())
                ->setCarrierTitle($rate->getCarrierTitle())
                ->setMethod($rate->getMethod())
                ->setMethodTitle($rate->getMethodTitle())
                ->setMethodDescription($rate->getMethodDescription())
                ->setPrice($rate->getPrice())
                ->setCarriergroupId($rate->getCarriergroupId())
                ->setCarriergroup($rate->getCarriergroup())
                ->setCarrierType($rate->getCarrierType())
                ->setDispatchDate($rate->getDispatchDate())
                ->setDeliveryDate($rate->getDeliveryDate())
                ->setCarriergroupShippingDetails($rate->getCarriergroupShippingDetails())
                ->setCarrierNotice($rate->getCarrierNotice())
                ->setFreightRate($rate->getFreightRate())
                ->setCustomDescription($rate->getCustomDescription())
                ->setCarrierId($rate->getCarrierId())
                ->setCustomDuties($rate->getCustomDuties());;
        }
        return $this;
    }

}