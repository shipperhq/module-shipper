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


namespace ShipperHQ\Shipper\Model\ResourceModel\Order\Packages;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    public function addOrderIdToFilter($orderId)
    {
        $this->addFieldToFilter('order_id', $orderId);
        return $this;
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ShipperHQ\Shipper\Model\Order\Packages', 'ShipperHQ\Shipper\Model\ResourceModel\Order\Packages');
    }
}
