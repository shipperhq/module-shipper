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


namespace ShipperHQ\Shipper\Model;

use Magento\Framework\Model\AbstractModel;
use ShipperHQ\Shipper\Model\ResourceModel\Synchronize as ResourceModel;

class Synchronize extends AbstractModel
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }
}
