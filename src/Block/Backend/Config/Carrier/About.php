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
namespace ShipperHQ\Shipper\Block\Backend\Config\Carrier;

class About extends \Magento\Config\Block\System\Config\Form\Fieldset//\Magento\Config\Block\System\Config\Form\Field
{


    /**
     */
    protected $shipperDataHelper;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\View\Helper\Js $jsHelper
     * @param \Magento\Sales\Model\Config\Data
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\View\Helper\Js $jsHelper,
        \ShipperHQ\Shipper\Helper\Data $shipperDataHelper,
        array $data = []
    ) {
        $this->shipperDataHelper = $shipperDataHelper;
        parent::__construct($context, $authSession, $jsHelper, $data);
    }


    /**
     * Return header comment part of html for fieldset
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getHeaderCommentHtml($element)
    {
        $beforeDiv = '<div style="padding:10px;background-color:#fff;border:1px solid #ddd;margin-bottom:7px;">';
        $afterDiv = '</div>';
        $synch = __('Click here to <a href="%1">Synchronize</a> with ShipperHQ.', $this->getUrl('shipperhq/synchronize/index'));
        $element->getComment()
            ? $comment =   $element->getComment()
            : $comment =  '';
        $html =$beforeDiv. '<table>
            <tr>
                <td style="vertical-align:bottom">
                <b>ShipperHQ installed version '. $this->getModuleVersion() .'</b>
                 </td>
            </tr>
            <tr>
             <td colspan="3">
                <p>'.$comment.'</p>
              </td>
            </tr>
            <tr>
                <td colspan="3">
                <p>'. $synch .'</p>
                </td>
            </tr>
            </table>' .$afterDiv;
        return $html;
    }

    protected function getModuleVersion() {
       return (string) $this->shipperDataHelper->getConfigValue('carriers/shipper/extension_version');
    }
}