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
