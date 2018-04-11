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
    private $shipperDataHelper;
    /**
     * @var \ShipperHQ\Shipper\Helper\CarrierGroup
     */
    private $carrierGroupHelper;
    /**
     * @var \ShipperHQ\Shipper\Helper\Package
     */
    private $packageHelper;

    private $cgInfo = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Helper\Admin $adminHelper
     * @param array $data
     */
    public function __construct(
        \ShipperHQ\Shipper\Helper\Data $shipperDataHelper,
        \ShipperHQ\Shipper\Helper\CarrierGroup $carrierGroupHelper,
        \ShipperHQ\Shipper\Helper\Package $packageHelper,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        array $data = []
    ) {
        $this->shipperDataHelper = $shipperDataHelper;
        $this->carrierGroupHelper = $carrierGroupHelper;
        $this->packageHelper = $packageHelper;
        $this->_adminHelper = $adminHelper;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $registry, $adminHelper, $data);
    }

    public function getCarrierGroupTitle()
    {
        $describer = $this->shipperDataHelper->getConfigValue($this->shipperDataHelper->getCarrierGroupDescPath());
        if ($describer) {
            $heading = $describer;
        } else {
            $heading = __('Origin');
        }
        $heading = $heading . ' ' . __("Shipping Information");
        return $heading;
    }

    public function getCarrierGroupBreakdownText()
    {
        $cginfo = $this->shipperDataHelper->decodeShippingDetails($this->getFieldValue('carrier_group_detail'));
        $result = $this->carrierGroupHelper->getCarrierGroupText($cginfo, $this->getOrder());
        return $result;
    }

    public function getFieldValue($fieldName)
    {
        $info = $this->getCarriergroupInfo();
        $result = null;
        foreach ($info as $carrierGroupDetail) {
            if (isset($carrierGroupDetail[$fieldName])) {
                $result = $carrierGroupDetail[$fieldName];
            }
        }

        if ($result === null && $this->getOrder()->getData($fieldName)) {
            $result = $this->getOrder()->getData($fieldName);
        }
        return $result;
    }

    public function getCarriergroupInfo()
    {
        $order = $this->getOrder();

        if ($this->cgInfo === null) {
            $this->cgInfo = $this->carrierGroupHelper->getOrderCarrierGroupInfo($order->getId());
            if (empty($this->cgInfo)) {
                //retrieve using quote shipping address ID from carrier group helper
                //legacy
                if ($order->getCarriergroupShippingDetails() != '') {
                    $this->cgInfo = $this->shipperDataHelper->decodeShippingDetails(
                        $order->getCarriergroupShippingDetails()
                    );
                } else {
                    //if we have no information, check for flag if we've checked already
                    $canLookupQuote = $this->carrierGroupHelper->canCheckForQuoteInformation($order);
                    if ($canLookupQuote) {
                        $this->cgInfo = $this->carrierGroupHelper->recoverOrderInfoFromQuote($order);
                        $this->packageHelper->recoverOrderPackageDetail($order);
                    }
                }
            }
        }

        return $this->cgInfo;
    }

    public function getAddressValidStatus()
    {
        $info = $this->getCarriergroupInfo();
        $result = null;
        foreach ($info as $carrierGroupDetail) {
            if (isset($carrierGroupDetail['address_valid'])) {
                $result = $carrierGroupDetail['address_valid'];
            }
        }

        if ($result === null && $this->getOrder()->getValidationStatus()) {
            $result = $this->getOrder()->getValidationStatus();
        }
        return $result;
    }

    public function getFormattedDate($date, $format)
    {
        $formattedDate = date($format, strtotime($date));
        return $formattedDate;
    }

    public function getFormattedTimeSlot($timeSlot)
    {
        $formattedTimeSlot = str_replace('_', ' - ', $timeSlot);
        return $formattedTimeSlot;
    }
}
