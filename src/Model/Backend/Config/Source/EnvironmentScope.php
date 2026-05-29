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

namespace ShipperHQ\Shipper\Model\Backend\Config\Source;

/**
 * Class Shipperhq_Shipper_Model_Adminhtml_System_Config_Source_Environmentscope
 *
 * This class provides options for environment scope to configuration
 *
 */

use ShipperHQ\WS\Shared\SiteDetails as SiteDetails;

class EnvironmentScope implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        return [
            [
                'value' => SiteDetails::LIVE,
                'label' => __('Live')
            ],
            [
                'value' => SiteDetails::DEV,
                'label' => __('Development')
            ],
            [
                'value' => SiteDetails::TEST,
                'label' => __('Test')
            ],
            [
                'value' => SiteDetails::INTEGRATION,
                'label' => __('Integration')
            ],
        ];
    }
}
