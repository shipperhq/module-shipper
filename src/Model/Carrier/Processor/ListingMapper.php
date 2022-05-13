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
 * @package ShipperHQ_Shipper
 * @copyright Copyright (c) 2017 Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */
namespace ShipperHQ\Shipper\Model\Carrier\Processor;

use ShipperHQ\GraphQL\Types\Input\Carrier;
use ShipperHQ\GraphQL\Types\Input\Sender;
use ShipperHQ\GraphQL\Types\Input\Address;
use ShipperHQ\GraphQL\Types\Input\RMSSiteDetails;
use ShipperHQ\GraphQL\Types\Input\Piece;
use ShipperHQ\GraphQL\Types\Input\ListingInfo;
use ShipperHQ\GraphQL\Types\Input\Listing;
use ShipperHQ\GraphQL\Types\Input\ListingDetail;
use ShipperHQ\WS;
use \Magento\Sales\Model\Order\Item;
use \Magento\Sales\Model\Order;
use \Magento\Catalog\Helper\Product;
use \Magento\Catalog\Model\Product\Gallery\Processor;

class ListingMapper
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\App\ProductMetadata
     */
    private $productMetadata;

    /**
     * @var \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger
     */
    private $shipperLogger;

    /**
     * @var WS\Shared\CredentialsFactory
     */
    private $credentialsFactory;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $mediaDirectory;

    private static $dim_height = 'ship_height';
    private static $dim_width = 'ship_width';
    private static $dim_length = 'ship_length';

    /**
     * ShipperMapper constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\ProductMetadata $productMetadata
     * @param \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger
     * @param WS\Shared\CredentialsFactory $credentialsFactory
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Filesystem $filesystem
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\ProductMetadata $productMetadata,
        \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger,
        \ShipperHQ\WS\Shared\CredentialsFactory $credentialsFactory,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->productMetadata = $productMetadata;
        $this->shipperLogger = $shipperLogger;
        $this->credentialsFactory = $credentialsFactory;
        $this->storeManager = $context->getStoreManager();
        $this->mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
    }

    /**
     * @param Order $order
     * @param ShippingAddress $shippingAddress
     * @param $carrierType
     * @param $originName
     * @param $shippingCost
     * @param \ShipperHQ\Shipper\Model\Api\CreateListing\Item[] $withItems
     * @param false|\ShipperHQ\Shipper\Api\FetchUpdatedCarrierRate\RateInterface $rate
     * @return ListingInfo
     * @throws \ShipperHQ\GraphQL\Exception\SerializerException
     */
    public function mapCreateListingRequest(Order $order, $shippingAddress, $carrierType, $originName, $shippingCost, $withItems = [], $rate = false)
    {
        $shippingMethod = $order->getShippingMethod();

        if ($rate) {
            $shippingMethod = "{$rate->getCarrierCode()}_{$rate->getMethodCode()}";
            $carrierType = $rate->getCarrierType();
            $shippingCost = $rate->getNypAmount() ? $rate->getNypAmount() : $rate->getPrice();
        }

        try {

            $carrier = $this->mapCarrier($shippingMethod, $carrierType);

            $sender = $this->mapSender($originName);

            $recipient = $this->mapRecipient($order);

            $listingArray = $this->mapListing($order, $shippingMethod, $shippingCost, $withItems);

            $siteDetails = $this->mapSiteDetails($order);

            $listingInfo = new ListingInfo($carrier, $sender, $recipient, $listingArray, $siteDetails);

        } catch (\Exception $e) {
            $this->shipperLogger->postCritical('ShipperHQ Shipper', 'Listing issue', $e->getMessage());
        }

        return $listingInfo;
    }

    /**
     * @param Order $order
     * @param string $shippingMethod
     * @param $shippingCost
     * @param \ShipperHQ\Shipper\Model\Api\CreateListing\Item[] $withItems
     * @return Listing[]
     * @throws \ShipperHQ\GraphQL\Exception\SerializerException
     */
    private function mapListing(Order $order, $shippingMethod, $shippingCost, $withItems = [])
    {
        list($carrierCode, $method) = explode('_', (string) $shippingMethod, 2);

        $listingDetail = new ListingDetail(
            $this->getShipmentId($order),
            $method,
            $shippingCost
        );

        $items = (array)$order->getAllItems(); // Coerce into being an array

        if (count($withItems)) {

            // TODO: Refactor to extract
            $withItems = array_reduce($withItems, function ($carry, $item) {
                /** @var \ShipperHQ\Shipper\Model\Api\CreateListing\Item $item */
                $carry[$item->getItemId()] = $item->getQty();
                return $carry;
            }, []);

            $items = array_filter($items, function (Item &$item) use ($withItems) {
                $product = $item->getProduct();
                if (isset($withItems[$product->getEntityId()])) {
                    $item->setData('listing_qty', $withItems[$product->getEntityId()]);
                    return true;
                }
                return false;
            });
        }

        $pieces = [];
        /** @var Item $item */
        foreach ($items as $k => $item) {
            if ($item->getParentItemId()) {
                continue;
            }

            $pieces[] = $this->mapProductDetails($item);
        }

        $listing = new Listing($listingDetail, $pieces);

        return [$listing];
    }

    /**
     * @param Item $item
     * @return Piece
     */
    private function mapProductDetails(Item $item)
    {
        $product = $item->getProduct();

        $listingQty = $item->getData('listing_qty') ?: $item->getQtyOrdered();

        $piece = new Piece(
            $item->getId(),
            $item->getName(), //referenceID?
            $item->getPrice() ? (int)$item->getPrice() : 0,
            (int)$item->getWeight() * $listingQty,
            $product->getData(self::$dim_length) ? $product->getData(self::$dim_length) : 1,
            $product->getData(self::$dim_width) ? $product->getData(self::$dim_width) : 1,
            $product->getData(self::$dim_height) ? $product->getData(self::$dim_height) : 1
        );

        try {
            $thumbnail = $product->getThumbnail();
            $fullPath =  'catalog/product/' . ltrim($thumbnail, '/');
            $fileContent = $this->mediaDirectory->readFile($fullPath);
            $encodedContent = base64_encode($fileContent);
            $piece->setImage($encodedContent);
        } catch (\Exception $e) {
            $this->shipperLogger->postCritical('ShipperHQ', 'Exception during image upload to listing', $e->getMessage());
        }

        return $piece;
    }

    /**
     * @param Order $order
     * @return Address
     */
    private function mapRecipient(Order $order)
    {
        $shippingAddress = $order->getShippingAddress();

        $recipient = new Address(
            $shippingAddress->getCountryId() === null ? '' : $shippingAddress->getCountryId(),
            $shippingAddress->getRegionCode(),
            $shippingAddress->getCity() === null ? '' : $shippingAddress->getCity(),
            $shippingAddress->getStreetLine(1),
            $shippingAddress->getStreetLine(2),
            $shippingAddress->getPostcode() === null ? '' : $shippingAddress->getPostcode()
        );

        $recipient->setEmail($shippingAddress->getEmail());
        $recipient->setTelNo($shippingAddress->getTelephone());
        $recipient->setGivenName($shippingAddress->getFirstname());
        $recipient->setFamilyName($shippingAddress->getLastname());

        return $recipient;
    }

    /**
     * @param string $originName
     * @return Sender
     */
    public function mapSender($originName)
    {
        $sender = new Sender($originName);

        return $sender;
    }

    /**
     * @param string $shippingMethod
     * @param string $carrierType
     * @return Carrier
     */
    public function mapCarrier($shippingMethod, $carrierType)
    {

        list($carrierCode, $method) = explode('_', (string) $shippingMethod, 2);

        $carrier = new Carrier($carrierType, $carrierCode);
        return $carrier;
    }

    /**
     * @param RateRequest $request
     * @return RMSSiteDetails
     */
    public function mapSiteDetails($order)
    {
        $storeId = $order->getStoreId();
        $edition = $this->productMetadata->getEdition();
        $url = $this->getStoreUrl($storeId);

        $siteDetails = new RMSSiteDetails(
            $this->scopeConfig->getValue('carriers/shipper/extension_version', 'store', $storeId),
            'Magento 2 ' . $edition,
            $this->scopeConfig->getValue('carriers/shipper/magento_version', 'store', $storeId),
            $url,
            '' //TODO efficient way to get the IP address, old way was through getting the quote object. There has to be a less expensive way
        );

        return $siteDetails;
    }

    /**
     * @param $storeId
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getStoreUrl($storeId)
    {
        return $this->storeManager->getStore($storeId)->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK);
    }

    /**
     * @param Order $order
     * @return string
     */
    private function getShipmentId(Order $order): string
    {
        $incrementId = $order->getIncrementId();
        $storeUrl = $this->getStoreUrl($order->getStoreId());
        $storeUrlHash =  hash('sha256', $storeUrl);
        return "$storeUrlHash-$incrementId";
    }
}
