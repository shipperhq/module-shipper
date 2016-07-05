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

namespace ShipperHQ\Shipper\Helper;

/**
 * Carrier Group Processing helper
 */
class CarrierGroup extends Data
{
    /*
    * @var \ShipperHQ\Shipper\Model\CarrierGroupFactory
    */
    protected $carrierGroupFactory;
    /*
    * @var Data
    */
    protected $shipperDataHelper;

    /**
     * @param \ShipperHQ\Lib\Helper\Rest $restHelper
     * @param Data $shipperHelperData
     */
    public function __construct(\ShipperHQ\Shipper\Model\CarrierGroupFactory $carrierGroupFactory,
                                Data $shipperDataHelper)
    {
        $this->carrierGroupFactory = $carrierGroupFactory;
        $this->shipperDataHelper = $shipperDataHelper;
    }

    /**
     * Save the carrier group shipping details for single carriergroup orders and
     * set carrier information on shipping address
     *
     * @param $shippingAddress
     * @param $shippingMethod
     * @return array
     */
    public function saveCarrierGroupInformation($shippingAddress, $shippingMethod)
    {
        //admin and front end orders use method
        $foundRate = $shippingAddress->getShippingRateByCode($shippingMethod);
        if($foundRate && $foundRate->getCarriergroupShippingDetails() != '') {
            $shipDetails = $this->shipperDataHelper->decodeShippingDetails($foundRate->getCarriergroupShippingDetails());
            if(array_key_exists('carrierGroupId', $shipDetails)) {
                $arrayofShipDetails = [];
                $arrayofShipDetails[] = $shipDetails;
            }
            else {
                $arrayofShipDetails = $shipDetails;
            }
            $encodedShipDetails = $this->shipperDataHelper->encode($arrayofShipDetails);

            $shippingAddress
                ->setCarrierId($foundRate->getCarrierId())
                ->setCarrierType($foundRate->getCarrierType())
                ->save();

            $carrierGroupDetail = $this->carrierGroupFactory->create();
            $update = ['quote_address_id' => $shippingAddress->getId(),
                'carrier_group_detail' => $encodedShipDetails,
                'carrier_group_html' => $this->getCarriergroupShippingHtml(
                    $encodedShipDetails)];
            $carrierGroupDetail->setData($update);
            $carrierGroupDetail->save();
            //save selected shipping options to items
            $this->shipperDataHelper->setShippingOnItems($arrayofShipDetails,  $shippingAddress);
        }
        return true;
    }

}