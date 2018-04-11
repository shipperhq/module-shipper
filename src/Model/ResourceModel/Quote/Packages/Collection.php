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

namespace ShipperHQ\Shipper\Model\ResourceModel\Quote\Packages;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    public function addAddressToFilter($addressId)
    {
        $this->addFieldToFilter('quote_address_id', $addressId);
        return $this;
    }

    public function addCarrierCodeToFilter($carrierCode)
    {
        $this->addFieldToFilter('carrier_code', $carrierCode);
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
        $this->_init('ShipperHQ\Shipper\Model\Quote\Packages', 'ShipperHQ\Shipper\Model\ResourceModel\Quote\Packages');
    }

    protected function _afterLoad()
    {
        $this->performAfterLoad();
        return parent::_afterLoad();
    }

    protected function performAfterLoad()
    {
        $connection = $this->getConnection();
        foreach ($this as $item) {
            $packageId = $item->getData('package_id');
            $select = $connection->select()->from($this->getTable('shipperhq_quote_package_items'));
            $select->where('package_id=?', $packageId);
            $items = $connection->fetchAll($select);
            if ($items) {
                $item->setData('items', $items);
            }
        }
    }
}
