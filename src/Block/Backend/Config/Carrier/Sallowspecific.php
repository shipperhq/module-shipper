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
        $class = $this->isHidden($element) ? 'class="hidden"' : '';
        $out = parent::render($element);

        $search = "/<tr id=\"row_{$element->getHtmlId()}\">/";
        $replace = "<tr id=\"row_{$element->getHtmlId()}\" $class>";
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
