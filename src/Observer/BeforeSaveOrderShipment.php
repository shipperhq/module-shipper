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

namespace ShipperHQ\Shipper\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use ShipperHQ\Shipper\Helper\Data as ShipperHQDataHelper;
use ShipperHQ\Shipper\Model\Order\DetailFactory;
use ShipperHQ\Shipper\Model\Order\PackagesFactory;

/**
 * ShipperHQ Shipper module observer
 */
class BeforeSaveOrderShipment implements ObserverInterface
{

    /**
     * @var DetailFactory
     */
    protected $orderDetailFactory;

    /**
     * @var ShipperHQDataHelper
     */
    protected $dataHelper;

    /**
     * @var PackagesFactory
     */
    protected $packagesFactory;

    /**
     * BeforeSaveOrderShipment constructor.
     * @param DetailFactory $orderDetailFactory
     */
    public function __construct(
        DetailFactory $orderDetailFactory,
        ShipperHQDataHelper $dataHelper,
        PackagesFactory $packagesFactory
    ) {
        $this->orderDetailFactory = $orderDetailFactory;
        $this->dataHelper = $dataHelper;
        $this->packagesFactory = $packagesFactory;
    }

    /**
     * Update saved shipping methods available for ShipperHQ
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        /** @var \Magento\Sales\Model\Order\Shipment $shipment */
        $shipment = $observer->getShipment();
        $order = $shipment->getOrder();

        $cgDetails = $this->orderDetailFactory->create()
            ->loadByOrder($order->getId())
            ->getFirstItem()
            ->getCarrierGroupDetail();
        $cgDetails = $this->dataHelper->decodeShippingDetails($cgDetails);

        if ($cgDetails) {
            $cgDetails = (array)$cgDetails;
            foreach ($cgDetails as $cg) {
                if (!isset($cg['carrierGroupId'])) {
                    continue;
                }
                $packages = $this->packagesFactory->create()
                    ->loadByOrderId($order->getId())
                    ->addFieldToFilter('carrier_group_id', $cg['carrierGroupId']);
                if ($packageText = $this->dataHelper->getPackageBreakdownText($packages, $cg['name'])) {
                    $shipment->addComment($packageText);
                }
            }
        }
    }
}
