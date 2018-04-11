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
            $items[] = [
                'package_id' => $packageId,
                'sku' => $item['sku'],
                'weight_packed' => $item['weight_packed'],
                'qty_packed' => $item['qty_packed']
            ];
        }
        if (count($items) > 0) {
            $connection->insertMultiple($itemsTable, $items);
        }
        return $this;
    }
}
