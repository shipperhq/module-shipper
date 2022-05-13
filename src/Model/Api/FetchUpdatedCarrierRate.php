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

use Magento\Quote\Model\QuoteRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Model\OrderRepository;
use ShipperHQ\Shipper\Api\FetchUpdatedCarrierRateInterface;
use ShipperHQ\Shipper\Api\FetchUpdatedCarrierRate\ItemInterface;
use ShipperHQ\Shipper\Api\FetchUpdatedCarrierRate\RateInterface;
use ShipperHQ\Shipper\Model\Api\FetchUpdatedCarrierRate\RateFactory;
use Magento\Shipping\Model\Config as ShippingConfig;
use Magento\Catalog\Model\ProductRepository;
use Magento\Quote\Model\Quote\ItemFactory;

class FetchUpdatedCarrierRate implements FetchUpdatedCarrierRateInterface
{
    /** @var OrderRepository */
    private $orderRepository;

    /** @var QuoteRepository */
    private $quoteRepository;

    /** @var SearchCriteriaBuilder */
    private $searchCriteriaBuilder;

    /** @var ShippingConfig */
    private $shippingConfig;

    /** @var ProductRepository */
    private $productRepository;

    /** @var ItemFactory */
    private $quoteItemFactory;

    /** @var RateFactory */
    private $rateFactory;

    /**
     * FetchUpdatedCarrierRate constructor.
     * @param OrderRepository $orderRepository
     * @param QuoteRepository $quoteRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ShippingConfig $shippingConfig
     * @param ProductRepository $productRepository
     * @param ItemFactory $quoteItemFactory
     * @param RateFactory $rateFactory
     */
    public function __construct(
        OrderRepository $orderRepository,
        QuoteRepository $quoteRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ShippingConfig $shippingConfig,
        ProductRepository $productRepository,
        ItemFactory $quoteItemFactory,
        RateFactory $rateFactory
    ) {
        $this->orderRepository = $orderRepository;
        $this->quoteRepository = $quoteRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->shippingConfig = $shippingConfig;
        $this->productRepository = $productRepository;
        $this->quoteItemFactory = $quoteItemFactory;
        $this->rateFactory = $rateFactory;
    }

    /**
     * @param string $order_number
     * @param string $carrierCodePattern regex string to match carrier codes against
     * @param ItemInterface[] $items empty array for all items
     * @return RateInterface[]
     */
    public function fetchRate($order_number, $carrierCodePattern, $items = [])
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('increment_id', $order_number, 'eq')->create();
        $matchingOrders = $this->orderRepository->getList($searchCriteria)->getItems();
        $order = reset($matchingOrders);

        $rates = $this->collectShippingRate($order, $carrierCodePattern, $items);

        if ($rates) {
            $rateFactory = $this->rateFactory;
            $mappedRates = array_map(function ($rate) use ($rateFactory) {
                /** @var \Magento\Quote\Model\Quote\Address\Rate $rate */
                return $rateFactory->create([
                    "carrier_code" => $rate->getCarrier(),
                    "carrier_title" => $rate->getCarrierTitle(),
                    "carrier_type" => $rate->getData('carrier_type'),
                    "method_code" => $rate->getMethod(),
                    "method_title" => $rate->getMethodTitle(),
                    "price" => $rate->getPrice(),
                    "nyp_amount" => $rate->getNypAmount()
                ]);
            }, $rates);
            return $mappedRates;
        }

        return [];
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param string $carrierCodePattern regex string to match carrier codes against
     * @param ItemInterface[] $items empty array for all items
     * @return false|\Magento\Quote\Model\Quote\Address\Rate[]
     */
    private function collectShippingRate($order, $carrierCodePattern, $items = [])
    {
        $carrierCodes = $this->getCarrierCodesMatching($carrierCodePattern);
        if ($carrierCodes === false) {
            return false;
        }

        $quote = $this->createQuote($order, $items);

        $quote->getShippingAddress()->setCollectShippingRates(true)->setLimitCarrier($carrierCodes)->collectShippingRates();

        $rates = $quote->getShippingAddress()->getAllShippingRates();

        $matchingRates = array_filter($rates, function ($rate) use ($carrierCodes) {
            return in_array($rate->getCarrier(), $carrierCodes);
        });

        return count($matchingRates) ? $matchingRates : false;
    }


    /**
     * @param string $carrierCodePattern regex string to match carrier codes against
     * @return false|string[]
     */
    private function getCarrierCodesMatching($carrierCodePattern)
    {
        $carrierCodePattern = preg_replace("/[^a-zA-Z_0-9-]/", "", (string) $carrierCodePattern);
        $carriers = $this->shippingConfig->getAllCarriers();
        $foundCarriers = array_filter($carriers, function ($carrier) use ($carrierCodePattern) {
            return preg_match("/(^shq|^)($carrierCodePattern)(.*)/i", $carrier->getId());
        });
        $foundCarrierCodes = array_map(function ($carrier) {
            return $carrier->getId();
        }, $foundCarriers);

        return count($foundCarrierCodes) ? $foundCarrierCodes : false;
    }

    /**
     * @param $order
     * @param ItemInterface[] $items empty array for all items
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function createQuote($order, $items = [])
    {
        $quoteId = $order->getQuoteId();
        $quote = $this->quoteRepository->get($quoteId, [$order->getStoreId()]);

        if (count($items)) {
            $quote->setItems($this->itemsToQuoteItems($items));
        }

        // Do not save the quote, we're reusing the previous one

        return $quote;
    }

    /**
     * @param ItemInterface[] $items
     * @return array
     */
    private function itemsToQuoteItems($items)
    {
        $quoteItemList = [];
        foreach ($items as $item) {
            $product = $this->productRepository->getById($item->getItemId());
            $quoteItem = $this->quoteItemFactory->create();
            $quoteItem->setProduct($product);
            $quoteItem->setQty($item->getQty());
            $quoteItemList[] = $quoteItem;
        }
        return $quoteItemList;
    }
}
