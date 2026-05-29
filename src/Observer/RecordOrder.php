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

use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Model\OrderFactory;
use ShipperHQ\Shipper\Helper\Authorization;
use ShipperHQ\Shipper\Helper\CarrierGroup;
use ShipperHQ\Shipper\Helper\Data;
use ShipperHQ\Shipper\Helper\Listing as ListingHelper;
use ShipperHQ\Shipper\Helper\LogAssist;
use ShipperHQ\Shipper\Helper\Module;
use ShipperHQ\Shipper\Helper\Package;
use ShipperHQ\Shipper\Helper\PostOrder;
use ShipperHQ\Shipper\Model\Listing\ListingService;

/**
 * ShipperHQ Shipper module observer
 */
class RecordOrder extends AbstractRecordOrder implements ObserverInterface
{
    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @param Data                    $shipperDataHelper
     * @param CartRepositoryInterface $quoteRepository
     * @param LogAssist               $shipperLogger
     * @param OrderFactory            $orderFactory
     * @param Session                 $checkoutSession
     * @param Package                 $packageHelper
     * @param CarrierGroup            $carrierGroupHelper
     * @param ListingService          $listingService
     * @param ListingHelper           $listingHelper
     * @param PostOrder               $postOrder
     * @param Module                  $moduleHelper
     * @param Authorization           $authHelper
     */
    public function __construct(
        Data $shipperDataHelper,
        CartRepositoryInterface $quoteRepository,
        LogAssist $shipperLogger,
        OrderFactory $orderFactory,
        Session $checkoutSession,
        Package $packageHelper,
        CarrierGroup $carrierGroupHelper,
        ListingService $listingService,
        ListingHelper $listingHelper,
        PostOrder $postOrder,
        Module $moduleHelper,
        Authorization $authHelper
    ) {
        $this->orderFactory = $orderFactory;
        $this->checkoutSession = $checkoutSession;
        parent::__construct(
            $shipperDataHelper,
            $quoteRepository,
            $shipperLogger,
            $packageHelper,
            $carrierGroupHelper,
            $listingService,
            $listingHelper,
            $postOrder,
            $moduleHelper,
            $authHelper
        );
    }

    /**
     * Record order shipping information after order is placed
     *
     * @param EventObserver $observer
     * @return void
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(EventObserver $observer)
    {
        if ($this->shipperDataHelper->getConfigValue('carriers/shipper/active')) {
            $order = $this->orderFactory->create()->loadByIncrementId(
                $this->checkoutSession->getLastRealOrderId()
            );
            if ($order->getIncrementId()) {
                $this->recordOrder($order);
                //SHQ16-1967 reset all checkout data
                $this->checkoutSession->setShipperhqData([]);
                $this->checkoutSession->setShipperHQPackages('');
            }
        }
    }
}
