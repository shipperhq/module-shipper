<?php
/*
 * ShipperHQ
 *
 * @category ShipperHQ
 * @package ShipperHQ\Shipper
 * @copyright Copyright (c) 2025 Zowta LTD and Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */

namespace ShipperHQ\Shipper\Block\Adminhtml\Synchronize;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

class Status extends AbstractRenderer
{

    CONST HELP_URL = "https://docs.shipperhq.com/resolve-manual-delete-required-magento";
    /**
     * Renders grid column
     *
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        $value = $row->getData($this->getColumn()->getIndex());

        if ($value === 'Manual delete required') {

            return '<a href="' . $this->escapeUrl(SELF::HELP_URL) . '" target="_blank">' . $this->escapeHtml($value) . '</a>';
        }

        return $this->escapeHtml($value);
    }
}
