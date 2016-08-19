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

    public function loadByOrderId($object, $orderId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getMainTable()
        )->where(
            'order_id = :order_id'
        );
        $data = $connection->fetchRow($select, [':order_id' => $orderId]);
        if ($data) {
            $object->addData($data);
        }
        return $this;
    }


    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object) {
        parent::_afterLoad($object);
        $connection = $this->getConnection();
        $select = $connection->select()->from($this->getTable('shipperhq_order_package_items'));
        $select->where('package_id=?', $object->getId());
        $items = $connection->fetchAll($select);
        if($items) {
            $object->setData('items', $items);
        }
        return $this;
    }

    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object) {
        parent::_afterSave($object);

        // now save the package items
        $this->getConnection()->delete(
            $this->getTable('shipperhq_order_package_items'),
            ['package_id = ?' => $object->getId()]
        );
        foreach ((array)$object->getData('items') as $item) {
            if(is_object($item)) {
                $item = (array)$item;
            }
            $itemArray = [
                'package_id' => $object->getId(),
                'sku' => $item['sku'],
                'weight_packed' => $item['weight_packed'],
                'qty_packed' => $item['qty_packed']
            ];
            $this->getConnection()->insert($this->getTable('shipperhq_order_package_items'), $itemArray);
        }

        return $this;
    }
}