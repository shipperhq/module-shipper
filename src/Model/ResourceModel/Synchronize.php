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


namespace ShipperHQ\Shipper\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Synchronize extends AbstractDb
{
    /**
     * @throws LocalizedException
     */
    public function deleteAllSynchData(): Synchronize
    {
        $this->getConnection()->delete($this->getMainTable());
        return $this;
    }

    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('shipperhq_synchronize', 'synch_id');
    }
}
