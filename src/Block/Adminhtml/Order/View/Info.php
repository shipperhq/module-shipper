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
namespace ShipperHQ\Shipper\Block\Adminhtml\Order\View;

use Magento\Sales\Block\Adminhtml\Order\AbstractOrder;

class Info extends AbstractOrder
{
    /**
     * @var \ShipperHQ\Shipper\Helper\Data
     */
    protected $shipperDataHelper;
    /**
     * @var \ShipperHQ\Shipper\Helper\CarrierGroup
     */
    protected $carrierGroupHelper;

    protected $cgInfo = null;

    protected $defaultDateFormat = 'm/d/y';

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Helper\Admin $adminHelper
     * @param array $data
     */
    public function __construct(
        \ShipperHQ\Shipper\Helper\Data $shipperDataHelper,
        \ShipperHQ\Shipper\Helper\CarrierGroup $carrierGroupHelper,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        array $data = []
    ) {
        $this->shipperDataHelper = $shipperDataHelper;
        $this->carrierGroupHelper = $carrierGroupHelper;
        $this->_adminHelper = $adminHelper;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $registry, $adminHelper, $data);
    }

    public function getCarriergroupInfo()
    {
        $order = $this->getOrder();
        if(is_null($this->cgInfo)) {
            $this->cgInfo = $this->carrierGroupHelper->getOrderCarrierGroupInfo($order->getId());

            if(empty($this->cgInfo)) {
                //retrieve using quote shipping address ID from carrier group helper
                //legacy
                $this->cgInfo = $this->shipperDataHelper->decodeShippingDetails($order->getCarriergroupShippingDetails());
            }
        }

        return $this->cgInfo;

    }

    public function getAddressValidStatus()
    {
        $info = $this->getCarriergroupInfo();
        $result = null;
        foreach($info as $carrierGroupDetail)
        {
            if(isset($carrierGroupDetail['address_valid'])) {
                $result = $carrierGroupDetail['address_valid'];
            }
        }

        if(is_null($result) && $this->getOrder()->getValidationStatus()) {
            $result = $this->getOrder()->getValidationStatus();
        }
        return $result;
    }

    public function getFieldValue($fieldName)
    {
        $info = $this->getCarriergroupInfo();
        $result = null;
        foreach($info as $carrierGroupDetail)
        {
            if(isset($carrierGroupDetail[$fieldName])) {
                $result = $carrierGroupDetail[$fieldName];
            }
        }

        if(is_null($result) && $this->getOrder()->getData($fieldName)) {
            $result = $this->getOrder()->getData($fieldName);
        }
        return $result;
    }

    public function getFormattedDate($date, $carrierGroupDetail)
    {
        $detail = $this->shipperDataHelper->decodeShippingDetails($carrierGroupDetail);
        $format = isset($detail[0]['display_date_format']) ? $detail[0]['display_date_format'] : $this->defaultDateFormat;
        $formattedDate = date($format, strtotime($date));
        return $formattedDate;
    }

    public function getFormattedTimeSlot($timeSlot)
    {
        $formattedTimeSlot = str_replace('_', ' - ', $timeSlot);
        return $formattedTimeSlot;
    }
}