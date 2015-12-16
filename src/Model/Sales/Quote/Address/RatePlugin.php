<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace ShipperHQ\Shipper\Model\Sales\Quote\Address;

class RatePlugin
{

    public function __construct() {

    }

    /**
     *Set additional information on shipping rate
     *
     * @param \Magento\Quote\Model\Quote\Address\Rate $subject
     * @param callable $proceed
     *
     * @return \Magento\Quote\Model\Quote\Address\Rate
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function aroundImportShippingRate(\Magento\Quote\Model\Quote\Address\Rate $subject, $proceed,
                                             \Magento\Quote\Model\Quote\Address\RateResult\AbstractResult $rate)
    {

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
                ->setDispatchDate($rate->getDispatchDate())
                ->setDeliveryDate($rate->getDeliveryDate())
                ->setCarriergroupShippingDetails($rate->getCarriergroupShippingDetails())
                ->setCarrierNotice($rate->getCarrierNotice())
                ->setFreightRate($rate->getFreightRate())
                ->setCustomDescription($rate->getCustomDescription())
                ->setCarrierId($rate->getCarrierId())
                ->setCustomDuties($rate->getCustomDuties());
        }
        return $result;
    }
}
