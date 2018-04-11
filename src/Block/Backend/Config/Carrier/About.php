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

class About extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    /**
     * @var \ShipperHQ\Shipper\Helper\Module
     */
    private $moduleHelper;

    /**
     * About constructor.
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\View\Helper\Js $jsHelper
     * @param \ShipperHQ\Shipper\Helper\Module $moduleHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\View\Helper\Js $jsHelper,
        \ShipperHQ\Shipper\Helper\Module $moduleHelper,
        array $data = []
    ) {
        $this->moduleHelper = $moduleHelper;
        parent::__construct($context, $authSession, $jsHelper, $data);
    }

    /**
     * Return header comment part of html for fieldset
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getHeaderCommentHtml($element)
    {
        $logo = $this->getViewFileUrl('ShipperHQ_Shipper::images/shipperhq_logo.png');
        $docs = $this->getViewFileUrl('ShipperHQ_Shipper::images/docs_logo.png');
        $additionalModules = $this->getAdditionalModulesOutput();
        $html = '<div style="padding:30px;background-color:#f2fcfe ;border-radius:5px;border:1px solid #e8f6fe ;margin-bottom:12px;overflow:auto;">
        <div style="width:63%;float:left;text-align:left;">
        <img src="' . $logo . '" style="max-width: 198px;margin-bottom:22px;">
        <p style="margin-bottom:12px;font-size:15px;">This extension connects Magento to ShipperHQ, a powerful, easy-to-use eCommerce shipping management platform</p>
        <p style="margin-bottom:18px;font-size:12px;">If you have questions about ShipperHQ or need support, visit <a href="http://www.ShipperHQ.com" target="_blank">ShipperHQ.com</a>. ShipperHQ is a product of <a href="http://www.webshopapps.com" target="_blank">WebShopApps</a>, developers of powerful shipping solutions for Magento.</p>
        <p style="font-size:12px"><a href="' . $this->getUrl('shipperhq/synchronize/index') . '">Synchronize with ShipperHQ</a></p></div>
        <div style="width:35%;max-width:285px;float:right;text-align:center;font-size:12px;">
        <div style="background:#fff; border:1px solid #e8f6fe;padding:15px 15px 9px;"><div style="margin-bottom:6px;">Installed Version</div>
            <span style="color:#00aae5;">' . $additionalModules . '</span></div>' . '
            <a href="http://docs.shipperhq.com" target="_blank">
        <div style="background:#fff; border:1px solid #e8f6fe ;margin-bottom:12px;padding:15px;border-top:0;">
            <img src="' . $docs . '" style="width:36px;height:36px;margin:0 auto 6px auto;display:block;">
            <strong style="font-weight:bold;text-decoration:none;color:#f77746 ;">ShipperHQ Help Docs</strong><br><p style="font-size:12px;color:#555;">Documentation &amp; Examples</p>
        </div></a>
        </div></div>';
        return $html;
    }

    private function getAdditionalModulesOutput()
    {
        $output = '';
        $additionalModules = $this->moduleHelper->getInstalledModules(true);
        foreach ($additionalModules as $moduleName => $info) {
            if (!$info['installed']) {
                continue;
            }
            $output .= '<div style="margin-bottom:6px;"><strong>' . $moduleName . '</strong>';
            if ($info['version'] !== false) {
                $output .= ": {$info['version']}";
            }
            if (!$info['enabled']) {
                $output .= " - <strong>Disabled</strong>";
            } elseif (!$info['output_enabled']) {
                $output .= " - <strong>Muted</strong>";
            }
            $output .= '</div>';
        }
        return $output;
    }
}
