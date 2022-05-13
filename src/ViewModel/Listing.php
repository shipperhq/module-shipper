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

namespace ShipperHQ\Shipper\ViewModel;

use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Quote\Model\QuoteRepository;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Item;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use ShipperHQ\Shipper\Helper\CarrierGroup;
use ShipperHQ\Shipper\Helper\Integration;
use ShipperHQ\Shipper\Model\Listing\ListingService;
use Magento\Framework\App\Config\ReinitableConfigInterface;

class Listing implements ArgumentInterface
{
    const DIM_HEIGHT = 'ship_height';
    const DIM_WIDTH = 'ship_width';
    const DIM_LENGTH = 'ship_length';
    const PRODUCT_WEIGHT = 'weight';

    /** @var array */
    private $config;

    /** @var OrderInterface */
    private $order;

    /** @var Image */
    private $imageHelper;

    /** @var ProductRepository */
    private $productRepository;

    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var Registry */
    protected $coreRegistry = null;

    /** @var Integration */
    private $integrationHelper;

    /** @var ReinitableConfigInterface */
    private $configReader;

    /** @var CarrierGroup */
    private $carrierGroupHelper;

    /** @var \ShipperHQ\Shipper\Helper\Data */
    protected $shipperDataHelper;

    /** @var QuoteRepository */
    protected $quoteRepository;

    /**
     * Listing constructor.
     * @param Image $imageHelper
     * @param ProductRepository $productRepository
     * @param Registry $coreRegistry
     * @param Integration $integrationHelper
     * @param StoreManagerInterface $storeManager
     * @param ReinitableConfigInterface $configReader
     * @param CarrierGroup $carrierGroupHelper
     * @param \ShipperHQ\Shipper\Helper\Data $shipperDataHelper
     * @param QuoteRepository $quoteRepository
     */
    public function __construct(
        Image $imageHelper,
        ProductRepository $productRepository,
        Registry $coreRegistry,
        Integration $integrationHelper,
        StoreManagerInterface $storeManager,
        ReinitableConfigInterface $configReader,
        CarrierGroup $carrierGroupHelper,
        \ShipperHQ\Shipper\Helper\Data $shipperDataHelper,
        QuoteRepository $quoteRepository
    ) {
        $this->imageHelper = $imageHelper;
        $this->productRepository = $productRepository;
        $this->coreRegistry = $coreRegistry;
        $this->integrationHelper = $integrationHelper;
        $this->storeManager = $storeManager;
        $this->configReader = $configReader;
        $this->carrierGroupHelper = $carrierGroupHelper;
        $this->shipperDataHelper = $shipperDataHelper;
        $this->quoteRepository = $quoteRepository;

        $this->initializeConfig();
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return false|string
     */
    public function getSerializedConfig()
    {
        return json_encode($this->getConfig(), JSON_HEX_TAG);
    }

    /**
     * @return string
     */
    public function showListingButton()
    {
        if ($this->areListingsDisabled()) {
            return false;
        }

        if ($this->hasListingAlreadyProcessed()) {
            return false;
        }

        return true;
    }

    private function areListingsDisabled()
    {
        $createListing = $this->configReader->getValue('carriers/shipper/create_listing');

        return $createListing === 'NONE' || $createListing === null;
    }

    private function hasListingAlreadyProcessed()
    {
        $order = $this->getOrder();
        $orderDetailArray = $this->carrierGroupHelper->loadOrderDetailByOrderId($order->getId());
        foreach ($orderDetailArray as $orderDetail) {
            $cgArray = $this->shipperDataHelper->decodeShippingDetails($orderDetail->getCarrierGroupDetail());
            foreach ($cgArray as $key => $cgDetail) {
                if ((isset($cgDetail['listing_created']) && $cgDetail['listing_created'] === ListingService::LISTING_CREATED)
                    || isset($cgDetail['listing_id'])
                ) {
                    return true;
                }
            }
        }
        return false;
    }

    private function initializeConfig()
    {
        if ($this->areListingsDisabled()) {
            $this->config['listing'] = [];
            return;
        }

        $order = $this->getOrder();

        $this->config['listing'] = [
            'order_id' => $order->getEntityId(),
            'order_number' => $order->getIncrementId(),
            'items' => array_filter($this->getItemConfig($order), function ($var) {
                return ($var !== null);
            }),
            'existing_rate' => $this->getExistingRateForOrder($order),
            'fetch_updated_rate' => [
                'api_key' => $this->getCreateListingApiKey(),
                'endpoint' => $this->getFetchUpdatedRateEndpoint(),
            ],
            'create_listing' => [
                'api_key' => $this->getCreateListingApiKey(),
                'endpoint' => $this->getCreateListingEndpoint(),
            ],
        ];
    }

    /**
     * @param $order
     * @return array|false
     */
    private function getExistingRateForOrder($order)
    {
        $quoteId = $order->getQuoteId();
        $quote = $this->quoteRepository->get($quoteId, [$order->getStoreId()]);
        $shippingAddress = $quote->getShippingAddress();
        $shippingRate = $shippingAddress->getShippingRateByCode($order->getShippingMethod());

        if ($shippingRate === false) {
            return [
                "carrier_title" => "-",
                "method_title" => "-",
                "price" => 0.0
            ];
        }

        return [
            "carrier_title" => $shippingRate->getCarrierTitle(),
            "method_title" => $shippingRate->getMethodTitle(),
            "price" => $shippingRate->getPrice()
        ];
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return array|null
     */
    private function getItemConfig($order)
    {
        /** @var Item[] $items */
        $items = $order->getItems();
        return array_values(array_map(function ($item) {
            /** @var Item $item */
            $productId = $item->getProductId();
            /** @var Product $product */
            $product = $item->getProduct();

            if ($product === null) {
                return null;
            }

            $imgUrl = $this->imageHelper
                ->init($product, 'product_page_image_small')
                ->setImageFile($product->getThumbnail())
                ->resize(100, 100)->getUrl();

            $height = $product->getData(self::DIM_HEIGHT);
            $length = $product->getData(self::DIM_LENGTH);
            $width = $product->getData(self::DIM_WIDTH);
            $weight = $product->getData(self::PRODUCT_WEIGHT);
            $volume = $height * $length * $width;
            $amount = $item->getQtyOrdered() - $item->getQtyShipped();

            if (!($height === null || $length === null || $width === null || $weight === null)) {
                if ($volume > 0 && $weight > 0 && $amount > 0) {

                    return [
                        "id" => $productId,
                        "name" => $item->getName(),
                        "qtyOrdered" => $item->getQtyOrdered(),
                        "qtyShipped" => $item->getQtyShipped(),
                        "imgSrc" => $imgUrl
                    ];
                } else {

                    return null;
                }
            } else {

                return null;
            }
        }, $items));
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    private function getOrder()
    {
        if (!$this->order) {
            // Registry is deprecated as of M2.3 but the alternatives for AdminHTML stuff aren't any better right now
            $this->order = $this->coreRegistry->registry('sales_order');
        }

        return $this->order;
    }

    /**
     * @return false|string
     */
    private function getCreateListingApiKey()
    {
        $apiKey = false;
        try {
            $apiKey = $this->integrationHelper->getApiKey();
        } catch (\Exception $e) {
            // TODO
        }
        return $apiKey;
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCreateListingEndpoint()
    {
        /** @var Store $store */
        $store = $this->storeManager->getStore();
        $baseUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
        $baseUrl = trim($baseUrl, "/ \t\n\r\0\v");
        return "$baseUrl/rest/V2/shipperhq/createListing";
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getFetchUpdatedRateEndpoint()
    {
        /** @var Store $store */
        $store = $this->storeManager->getStore();
        $baseUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
        $baseUrl = trim($baseUrl, "/ \t\n\r\0\v");
        return "$baseUrl/rest/V2/shipperhq/fetchUpdatedCarrierRate";
    }
}
