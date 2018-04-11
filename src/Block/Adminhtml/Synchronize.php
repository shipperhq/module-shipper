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
