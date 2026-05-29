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


namespace ShipperHQ\Shipper\Model\ResourceModel\Quote\ItemDetail;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    public function addItemToFilter($itemId)
    {
        $this->addFieldToFilter('quote_item_id', $itemId);
        return $this;
    }

    public function addCarrierGroupToFilter($carrierGroupId)
    {
        $this->addFieldToFilter('carrier_group_id', $carrierGroupId);
        return $this;
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'ShipperHQ\Shipper\Model\Quote\ItemDetail',
            'ShipperHQ\Shipper\Model\ResourceModel\Quote\ItemDetail'
        );
    }
}
