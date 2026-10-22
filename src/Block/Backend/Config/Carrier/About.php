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


namespace ShipperHQ\Shipper\Block\Backend\Config\Carrier;

class About extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    private const GRADIENT = 'linear-gradient(135deg,#2aaee9,#3a6edc)';
    private const BRAND_BLUE = '#3a6edc';

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
        $logo = $this->_escaper->escapeUrl($this->getViewFileUrl('ShipperHQ_Shipper::images/shipperhq_logo.png'));

        return '<div style="background:#fff;border:1px solid #e1e5ea;border-radius:6px;margin-bottom:12px;'
            . 'overflow:hidden;text-align:left;">'
            . '<div style="height:4px;background:' . self::GRADIENT . ';"></div>'
            . '<div style="display:flex;flex-wrap:wrap;gap:24px;padding:24px;color:#22272e;">'
            . '<div style="flex:1 1 380px;min-width:0;">'
            . '<img src="' . $logo . '" alt="ShipperHQ" style="max-width:200px;height:auto;margin-bottom:16px;">'
            . '<h3 style="margin:0 0 10px;font-size:20px;color:#22272e;">'
            . $this->_escaper->escapeHtml(__('ShipperHQ for Magento')) . '</h3>'
            . '<p style="margin:0 0 14px;font-size:14px;line-height:1.5;">'
            . $this->_escaper->escapeHtml(__(
                'This extension connects Magento to ShipperHQ, a powerful eCommerce shipping management '
                . 'platform that is easy to use.'
            )) . '</p>'
            . '<p style="margin:0 0 18px;font-size:13px;color:#555;">'
            . $this->_escaper->escapeHtml(__('If you have questions about ShipperHQ or need support, visit'))
            . ' ' . $this->link('ShipperHQ.com', 'https://shipperhq.com') . '.</p>'
            . $this->button('Synchronize with ShipperHQ', $this->getUrl('shipperhq/synchronize/index'), true, false)
            . $this->button('ShipperHQ Help Docs', 'https://docs.shipperhq.com', false, true)
            . '</div>'
            . '<div style="flex:0 1 280px;min-width:220px;align-self:flex-start;background:#f6f8fa;'
            . 'border:1px solid #e1e5ea;border-radius:6px;padding:16px;font-size:13px;">'
            . $this->sidebarTitle('Installed Version')
            . '<div style="color:' . self::BRAND_BLUE . ';">' . $this->getAdditionalModulesOutput() . '</div>'
            . '<hr style="border:0;border-top:1px solid #e1e5ea;margin:12px 0;">'
            . $this->sidebarTitle('Resources')
            . '<ul style="margin:0;padding-left:18px;line-height:1.8;">'
            . '<li>' . $this->link('Documentation & examples', 'https://docs.shipperhq.com') . '</li>'
            . '<li>' . $this->link('Contact ShipperHQ support', 'https://shipperhq.com/contact') . '</li>'
            . '</ul></div>'
            . '</div></div>';
    }

    private function sidebarTitle(string $label): string
    {
        return '<div style="margin:0 0 10px;font-size:14px;font-weight:600;color:#22272e;">'
            . $this->_escaper->escapeHtml(__($label)) . '</div>';
    }

    private function link(string $label, string $url): string
    {
        return '<a href="' . $this->_escaper->escapeUrl($url) . '" target="_blank" rel="noopener">'
            . $this->_escaper->escapeHtml(__($label)) . '</a>';
    }

    private function button(string $label, string $url, bool $primary, bool $newTab): string
    {
        $style = 'display:inline-block;margin:0 10px 8px 0;padding:10px 20px;border-radius:4px;font-size:14px;'
            . 'font-weight:600;text-decoration:none;';
        $style .= $primary
            ? 'background:' . self::GRADIENT . ';color:#fff;border:1px solid ' . self::BRAND_BLUE . ';'
            : 'background:#fff;color:' . self::BRAND_BLUE . ';border:1px solid ' . self::BRAND_BLUE . ';';
        $target = $newTab ? ' target="_blank" rel="noopener"' : '';

        return '<a href="' . $this->_escaper->escapeUrl($url) . '"' . $target . ' style="' . $style . '">'
            . $this->_escaper->escapeHtml(__($label)) . '</a>';
    }

    private function getAdditionalModulesOutput()
    {
        $output = '';
        $additionalModules = $this->moduleHelper->getInstalledModules(true);
        foreach ($additionalModules as $moduleName => $info) {
            if (!$info['installed']) {
                continue;
            }
            $output .= '<div style="margin-bottom:6px;"><strong>' . $this->_escaper->escapeHtml($moduleName)
                . '</strong>';
            if ($info['version'] !== false) {
                $output .= ': ' . $this->_escaper->escapeHtml($info['version']);
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
