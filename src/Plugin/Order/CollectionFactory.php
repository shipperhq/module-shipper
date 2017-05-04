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
    /**
     * @var \ShipperHQ\Shipper\Helper\LogAssist
     */
    private $shipperLogger;
    /** @var \Magento\Framework\App\ResourceConnection */
    protected $resource;

    public function __construct(
        \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->shipperLogger = $shipperLogger;
        $this->resource = $resource;
    }

    public function aroundGetReport(
        \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $subject,
        \Closure $proceed,
        $requestName
    ) {
    
        $result = $proceed($requestName);
        if ($requestName == 'sales_order_grid_data_source') {
            if ($result instanceof \Magento\Sales\Model\ResourceModel\Order\Grid\Collection) {
                $select = $result->getSelect();
                $select->joinLeft(
                    ['shipper_order_join' => $this->resource->getTableName('shipperhq_order_detail_grid')],
                    'entity_id' . '=shipper_order_join.' . 'order_id',
                    []
                );
            }
        }
        return $result;
    }
}
