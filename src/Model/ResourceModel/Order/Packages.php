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


namespace ShipperHQ\Shipper\Model\ResourceModel\Order;

/**
 * Gift Message resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Packages extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('shipperhq_order_packages', 'package_id');
    }

    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        parent::_afterLoad($object);
        $connection = $this->getConnection();
        $select = $connection->select()->from($this->getTable('shipperhq_order_package_items'));
        $select->where('package_id=?', $object->getId());
        $items = $connection->fetchAll($select);
        if ($items) {
            $object->setData('items', $items);
        }
        return $this;
    }

    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        parent::_afterSave($object);

        $connection = $this->getConnection();
        $itemsTable = $this->getTable('shipperhq_order_package_items');
        $packageId = $object->getId();

        // Delete existing package items, if any
        $select = $connection->select()
            ->from($itemsTable, 'COUNT(*)')
            ->where('package_id = ?', $packageId);
        $itemCount = (int)$connection->fetchOne($select);
        if ($itemCount) {
            $connection->delete($itemsTable, ['package_id = ?' => $packageId]);
        }

        // Add new package items
        $items = [];
        foreach ((array)$object->getData('items') as $item) {
            $qtyPacked = array_key_exists('qty_packed', $item) ? $item['qty_packed'] : $item['qtyPacked'];
            $weightPacked = array_key_exists('weight_packed', $item) ? $item['weight_packed'] : $item['weightPacked'];

            $items[] = [
                'package_id' => $packageId,
                'sku' => $item['sku'],
                'weight_packed' => $weightPacked,
                'qty_packed' => $qtyPacked
            ];
        }
        if (count($items) > 0) {
            $connection->insertMultiple($itemsTable, $items);
        }
        return $this;
    }
}
