<?php
/**
 * Shipper HQ
 *
 * @category ShipperHQ
 * @package ShipperHQ_Shipper
 * @copyright Copyright (c) 2019 Zowta LTD and Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */
namespace ShipperHQ\Shipper\Block\Adminhtml\System\Config;

/**
 * Refresh authorisation button renderer
 */
class RefreshAuthToken extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Refresh button Label
     *
     * @var string
     */
    protected $_refreshAuthTokenLabel = 'Refresh Authorization Token';

    /**
     * Set button label
     *
     * @param $refreshAuthButtonLabel
     *
     * @return RefreshAuthToken
     */
    public function setRefreshAuthTokenLabel($refreshAuthButtonLabel): RefreshAuthToken
    {
        $this->_refreshAuthTokenLabel = $refreshAuthButtonLabel;
        return $this;
    }

    /**
     * Set template to itself
     * @return RefreshAuthToken
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('ShipperHQ_Shipper::system/config/refreshAuthToken.phtml');
        }
        return $this;
    }
    /**
     * Unset some non-related element parameters
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }
    /**
     * Get the button and scripts contents
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $originalData = $element->getOriginalData();
        $buttonLabel = !empty($originalData['button_label']) ? $originalData['button_label'] : $this->_refreshAuthTokenLabel;
        $this->addData(
            [
                'button_label' => __($buttonLabel),
                'html_id' => $element->getHtmlId(),
                'ajax_url' => $this->_urlBuilder->getUrl('shipperhq/system_config/refreshAuthToken'),
            ]
        );
        return $this->_toHtml();
    }
}
