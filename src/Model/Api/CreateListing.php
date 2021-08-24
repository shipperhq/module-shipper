<?php
/**
 * Shipper HQ
 *
 * @category ShipperHQ
 * @package ShipperHQ_Shipper
 * @copyright Copyright (c) 2019 Zowta LTD and Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */

declare(strict_types=1);

namespace ShipperHQ\Shipper\Model\Api;

use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Model\OrderRepository;
use ShipperHQ\Shipper\Api\CreateListingInterface;
use ShipperHQ\Shipper\Model\Listing\ListingService;
use Magento\Shipping\Model\Config as ShippingConfig;
use Magento\Shipping\Model\Shipping;
use Magento\Catalog\Model\ProductRepository;
use Magento\Quote\Model\Quote\ItemFactory;

class CreateListing implements CreateListingInterface
{
    /** @var ListingService */
    private $listingService;

    /** @var OrderRepository */
    private $orderRepository;

    /** @var QuoteRepository */
    private $quoteRepository;

    /** @var QuoteFactory */
    private $quoteFactory;

    /** @var SearchCriteriaBuilder */
    private $searchCriteriaBuilder;

    /** @var Shipping */
    private $shipper;

    /** @var ShippingConfig */
    private $shippingConfig;
    
    /** @var ProductRepository */
    private $productRepository;

    /** @var ItemFactory */
    private $quoteItemFactory;

    /**
     * CreateListing constructor.
     * @param ListingService $listingService
     * @param OrderRepository $orderRepository
     * @param QuoteRepository $quoteRepository
     * @param QuoteFactory $quoteFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Shipping $shipper
     * @param ShippingConfig $shippingConfig
     * @param ProductRepository $productRepository
     * @param ItemFactory $quoteItemFactory
     */
    public function __construct(
        ListingService $listingService,
        OrderRepository $orderRepository,
        QuoteRepository $quoteRepository,
        QuoteFactory $quoteFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Shipping $shipper,
        ShippingConfig $shippingConfig,
        ProductRepository $productRepository,
        ItemFactory $quoteItemFactory
    ) {
        $this->listingService = $listingService;
        $this->orderRepository = $orderRepository;
        $this->quoteRepository = $quoteRepository;
        $this->quoteFactory = $quoteFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->shipper = $shipper;
        $this->shippingConfig = $shippingConfig;
        $this->productRepository = $productRepository;
        $this->quoteItemFactory = $quoteItemFactory;
    }

    /**
     * @param string $order_number
     * @param \ShipperHQ\Shipper\Api\CreateListing\ItemInterface[] $items
     * @param \ShipperHQ\Shipper\Api\FetchUpdatedCarrierRate\RateInterface|false $rate
     * @return false|string
     */
    public function createListing($order_number, $items, $rate = false)
    {
        try {
            $searchCriteria = $this->searchCriteriaBuilder->addFilter('increment_id', $order_number, 'eq')->create();
            $matchingOrders = $this->orderRepository->getList($searchCriteria)->getItems();
            $order = reset($matchingOrders);
            $quoteId = $order->getQuoteId();
            $quote = $this->quoteRepository->get($quoteId, [$order->getStoreId()]);
            $shippingAddress = $quote->getShippingAddress();
            $shippingRate = $shippingAddress->getShippingRateByCode($order->getShippingMethod());
            $carrierType = $shippingRate->getCarrierType();
        } catch (\Exception $e) {
            return false;
        }

        $listingCreated = $this->listingService->createListing($order, $shippingAddress, $carrierType, $items, $rate);

        return $listingCreated !== false ? $listingCreated->getData()->getCreateListing()->getListingId() : false;
    }
}
