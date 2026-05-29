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


namespace ShipperHQ\Shipper\Plugin\Order;

use Magento\Framework\App\ResourceConnection;

class CollectionFactory
{
    /** @var ResourceConnection */
    private $resource;

    public function __construct(
        ResourceConnection $resource
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
