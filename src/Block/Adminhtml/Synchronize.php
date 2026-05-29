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


namespace ShipperHQ\Shipper\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

class Synchronize extends Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'shipperhq';
        $this->_blockGroup = 'Shipperhq_Shipper';
        $this->_headerText = __('Synchronize with ShipperHQ');
        parent::_construct();

        $this->buttonList->remove('add');
        $url = $this->getUrl('shipperhq/synchronize/index');
        $this->buttonList->add(
            'refresh',
            [
                'label' => __('Reload Synchronize Data'),
                'onclick' => 'setLocation(\'' . $url . '\')',
                'class' => 'add primary'
            ],
            0
        );
        $synchurl = $this->getUrl('shipperhq/synchronize/synchronize');
        $message = __('Are you sure you are ready to synchronize?');
        $this->buttonList->add(
            'synchronize',
            [
                'label' => __('Synchronize with ShipperHQ'),
                'onclick' => 'confirmSetLocation(\'' . $message . '\', \'' . $synchurl . '\')',
                'class' => 'add primary'
            ],
            0
        );
    }
}
