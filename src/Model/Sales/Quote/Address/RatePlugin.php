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


namespace ShipperHQ\Shipper\Model\Sales\Quote\Address;

class RatePlugin
{
    /**
     * Set additional information on shipping rate
     *
     * @param \Magento\Quote\Model\Quote\Address\Rate                      $subject
     * @param                                                              $result
     * @param \Magento\Quote\Model\Quote\Address\RateResult\AbstractResult $rate
     *
     * @return \Magento\Quote\Model\Quote\Address\Rate
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterImportShippingRate(
        \Magento\Quote\Model\Quote\Address\Rate $subject,
        $result,
        \Magento\Quote\Model\Quote\Address\RateResult\AbstractResult $rate
    ) {
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
                ->setHideNotifications($rate->getHideNotifications())
                ->setTooltip($rate->getTooltip())
                ->setNypAmount($rate->getNypAmount());
        }
        return $result;
    }
}
