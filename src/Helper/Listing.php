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


namespace ShipperHQ\Shipper\Helper;

use Magento\Sales\Api\OrderStatusHistoryRepositoryInterface;
use ShipperHQ\Shipper\Model\Listing\ListingService;

/**
 * Listing Helper
 */
class Listing
{
    /**
     * @var OrderStatusHistoryRepositoryInterface
     */
    protected $orderStatusHistoryRepository;

    /**
     * Listing constructor.
     *
     * @param OrderStatusHistoryRepositoryInterface $orderStatusHistoryRepository
     */
    public function __construct(OrderStatusHistoryRepositoryInterface $orderStatusHistoryRepository)
    {
        $this->orderStatusHistoryRepository = $orderStatusHistoryRepository;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param string $listingCreated
     * @param string $listingId
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function saveListingDetailsToOrderComments($order, $listingCreated, $listingId)
    {
        $listingIdMsg = $listingCreated === ListingService::LISTING_CREATED ? "-- Listing ID $listingId" : "";
        $listingMessage = "uShip {$listingCreated} {$listingIdMsg}";

        $this->orderStatusHistoryRepository->save($order->addStatusHistoryComment($listingMessage, $order->getStatus()));
    }
}
