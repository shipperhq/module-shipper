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

namespace ShipperHQ\Shipper\Block\Backend\Config\Carrier;

class Sallowspecific extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Retrieve HTML markup for given form element
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $class = $this->isHidden($element) ? "class=\"hidden\"" : "";
        $out = parent::render($element);

        $search = '<tr id="row_' . $element->getHtmlId() . '">';
        $replace = '<tr id="row_' . $element->getHtmlId() . '" ' . $class . '>';
        $out = preg_replace($search, $replace, $out);
        return $out;
    }

    /**
     * We only want to show this option for legacy customers who have already turned the switch on.
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return bool
     */
    public function isHidden(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        // For option values see: Magento\Shipping\Model\Config\Source\Allspecificcountries
        return $element->getValue() == 0;
    }
}
