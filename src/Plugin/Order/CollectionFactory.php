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

namespace ShipperHQ\Shipper\Plugin\Order;

class CollectionFactory
{
    /** @var \Magento\Framework\App\ResourceConnection */
    private $resource;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->resource = $resource;
    }

    /**
     * @param \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $subject
     * @param \Magento\Sales\Model\ResourceModel\Order\Grid\Collection $collection
     * @param $requestName
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetReport(
        \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $subject,
        $collection,
        $requestName
    ) {
        if ($requestName == 'sales_order_grid_data_source') {
            if ($collection instanceof \Magento\Sales\Model\ResourceModel\Order\Grid\Collection) {
                // SHQ18-944 Need to alias columns to simpler names to pass Magento's field validation rules
                // MNB-279 Renamed to match actual names in DB. This fixes filtering in order grid
                $collection->getSelect()->joinLeft(
                    ['shipper_order_join' => $this->resource->getTableName('shipperhq_order_detail_grid')],
                    'main_table.entity_id = shipper_order_join.order_id',
                    [
                        'carrier_group' => 'shipper_order_join.carrier_group',
                        'delivery_date' => 'shipper_order_join.delivery_date',
                        'dispatch_date' => 'shipper_order_join.dispatch_date',
                        'time_slot' => 'shipper_order_join.time_slot',
                        'pickup_location' => 'shipper_order_join.pickup_location',
                        'carrier_type' => 'shipper_order_join.carrier_type'
                    ]
                );
            }
        }

        return $collection;
    }
}
