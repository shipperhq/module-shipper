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

namespace ShipperHQ\Shipper\Model\Carrier\Processor;

/**
 * Class Shipperhq_Shipper_Model_Carrier_Convert_ShipperMapper
 *
 * This class converts the Magento Request into a format that
 * is usable by the ShipperHQ webservice
 */
use Magento\Bundle\Model\Product\Price as Price;
use ShipperHQ\WS;
use ShipperHQ\WS\Rate\Request;
use ShipperHQ\Shipper\Model\Carrier\Processor\StockHandler\StockHandlerInterface;

class ShipperMapper
{
    private static $ecommerceType = 'magento';
    private static $stdAttributeNames = [
        'shipperhq_shipping_group',
        'shipperhq_post_shipping_group',
        'shipperhq_location',
        'shipperhq_royal_mail_group',
        'shipperhq_shipping_qty',
        'shipperhq_shipping_fee',
        'shipperhq_additional_price',
        'freight_class',
        'shipperhq_nmfc_class',
        'shipperhq_nmfc_sub',
        'shipperhq_handling_fee',
        'shipperhq_carrier_code',
        'shipperhq_volume_weight',
        'shipperhq_declared_value',
        'ship_separately',
        'shipperhq_dim_group',
        'shipperhq_poss_boxes',
        'shipperhq_master_boxes',
        'ship_box_tolerance',
        'must_ship_freight',
        'packing_section_name',
        'shipperhq_availability_date',
        'shipperhq_hs_code'
    ];

    private static $dim_height = 'ship_height';
    private static $dim_width = 'ship_width';
    private static $dim_length = 'ship_length';
    private static $alt_height = 'height';
    private static $alt_width = 'width';
    private static $alt_length = 'length';
    private static $origin = 'shipperhq_warehouse';
    private static $location = 'shipperhq_location';
    private static $available_date = 'shipperhq_availability_date';

    private static $useDefault = 'Use Default';

    private static $dim_group = 'shipperhq_dim_group';
    private static $conditional_dims = [
        'shipperhq_poss_boxes',
        'shipperhq_volume_weight',
        'ship_box_tolerance',
        'ship_separately'
    ];

    private static $legacyAttributeNames = [
        'package_id',
        'special_shipping_group',
        'volume_weight',
        'warehouse',
        'handling_id',
        'package_type' // royal mail
    ];

    private static $shippingOptions = ['liftgate_required', 'notify_required', 'inside_delivery', 'destination_type'];

    private static $prodAttributes;

    /**
     * @var \ShipperHQ\Shipper\Helper\Data
     */
    private $shipperDataHelper;

    /**
     * @var \Magento\Customer\Model\GroupFactory
     */
    private $groupFactory;
    /**
     * @var \Magento\Framework\App\ProductMetadata
     */
    private $productMetadata;

    /**
     * @var \Magento\Catalog\Helper\Product\Configuration
     */
    private $productConfiguration;

    /**
     * @var Request\RateRequestFactory
     */
    private $rateRequestFactory;

    /**
     * @var \ShipperHQ\Shipper\Helper\LogAssist
     */
    private $shipperLogger;

    /**
     * @var Request\Shipping\SelectedOptionsFactory
     */
    private $selectedOptionsFactory;

    /**
     * @var \ShipperHQ\WS\Rate\Request\Checkout\CartFactory
     */
    private $cartFactory;

    /**
     * @var \ShipperHQ\WS\Rate\Request\Checkout\Item
     */
    private $itemFactory;

    /**
     * @var WS\Shared\AddressFactory
     */
    private $addressFactory;

    /**
     * @var Request\InfoRequestFactory
     */
    private $infoRequestFactory;

    /**
     * @var WS\Shared\CredentialsFactory
     */
    private $credentialsFactory;

    /**
     * @var WS\Shared\SiteDetailsFactory
     */
    private $siteDetailsFactory;

    /**
     * @var Request\CustomerDetailsFactory
     */
    private $customerDetailsFactory;

    /**
     * @var Request\ShipDetailsFactory
     */
    private $shipDetailsFactory;

    /**
     * @var StockHandlerInterface
     */
    private $stockHandler;

    /**
     * @var  \ShipperHQ\WS\Rate\Request\Checkout\PhysicalBuildingDetailFactory
     */
    private $physicalBuildingDetailFactory;

    /**
     * @var  \ShipperHQ\WS\Rate\Request\Checkout\StockDetailFactory
     */
    private $stockDetailFactory;
    /**
     * Tax data
     *
     * @var \Magento\Tax\Helper\Data
     */
    protected $taxHelper;
    /**
     * @var \Magento\Framework\HTTP\Header
     */
    private $httpHeader;

    /**
     * ShipperMapper constructor.
     * @param \ShipperHQ\Shipper\Helper\Data $shipperDataHelper
     * @param \Magento\Customer\Model\GroupFactory $groupFactory
     * @param \Magento\Framework\App\ProductMetadata $productMetadata
     * @param \Magento\Catalog\Helper\Product\Configuration $productConfiguration
     * @param Request\RateRequestFactory $rateRequestFactory
     * @param \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger
     * @param Request\Shipping\SelectedOptionsFactory $selectedOptionsFactory
     * @param Request\Checkout\CartFactory $cartFactory
     * @param Request\Checkout\ItemFactory $itemFactory
     * @param WS\Shared\AddressFactory $addressFactory
     * @param Request\InfoRequestFactory $infoRequestFactory
     * @param WS\Shared\CredentialsFactory $credentialsFactory
     * @param WS\Shared\SiteDetailsFactory $siteDetailsFactory
     * @param Request\CustomerDetailsFactory $customerDetailsFactory
     * @param \Magento\Backend\Block\Template\Context $context
     * @param Request\ShipDetailsFactory $shipDetailsFactory
     * @param StockHandlerFactory $stockHandlerFactory
     * @param Request\Checkout\PhysicalBuildingDetailFactory $physicalBuildingDetailFactory
     * @param Request\Checkout\StockDetailFactory $stockDetailFactory
     * @param \Magento\Tax\Helper\Data $taxHelper
     * @param \Magento\Framework\HTTP\Header $httpHeader
     */
    public function __construct(
        \ShipperHQ\Shipper\Helper\Data $shipperDataHelper,
        \Magento\Customer\Model\GroupFactory $groupFactory,
        \Magento\Framework\App\ProductMetadata $productMetadata,
        \Magento\Catalog\Helper\Product\Configuration $productConfiguration,
        \ShipperHQ\WS\Rate\Request\RateRequestFactory $rateRequestFactory,
        \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger,
        \ShipperHQ\WS\Rate\Request\Shipping\SelectedOptionsFactory $selectedOptionsFactory,
        \ShipperHQ\WS\Rate\Request\Checkout\CartFactory $cartFactory,
        \ShipperHQ\WS\Rate\Request\Checkout\ItemFactory $itemFactory,
        \ShipperHQ\WS\Shared\AddressFactory $addressFactory,
        \ShipperHQ\WS\Rate\Request\InfoRequestFactory $infoRequestFactory,
        \ShipperHQ\WS\Shared\CredentialsFactory $credentialsFactory,
        \ShipperHQ\WS\Shared\SiteDetailsFactory $siteDetailsFactory,
        \ShipperHQ\WS\Rate\Request\CustomerDetailsFactory $customerDetailsFactory,
        \Magento\Backend\Block\Template\Context $context,
        \ShipperHQ\WS\Rate\Request\ShipDetailsFactory $shipDetailsFactory,
        StockHandlerFactory $stockHandlerFactory,
        \ShipperHQ\WS\Rate\Request\Checkout\PhysicalBuildingDetailFactory $physicalBuildingDetailFactory,
        \ShipperHQ\WS\Rate\Request\Checkout\StockDetailFactory $stockDetailFactory,
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Framework\HTTP\Header $httpHeader
    ) {
        $this->shipperDataHelper = $shipperDataHelper;
        $this->groupFactory = $groupFactory;
        $this->productMetadata = $productMetadata;
        $this->productConfiguration = $productConfiguration;
        $this->rateRequestFactory = $rateRequestFactory;
        $this->shipperLogger = $shipperLogger;
        $this->selectedOptionsFactory = $selectedOptionsFactory;
        $this->cartFactory = $cartFactory;
        $this->itemFactory = $itemFactory;
        $this->addressFactory = $addressFactory;
        $this->infoRequestFactory = $infoRequestFactory;
        $this->credentialsFactory = $credentialsFactory;
        $this->siteDetailsFactory = $siteDetailsFactory;
        $this->customerDetailsFactory = $customerDetailsFactory;
        $this->storeManager = $context->getStoreManager();
        $this->shipDetailsFactory = $shipDetailsFactory;
        $this->physicalBuildingDetailFactory = $physicalBuildingDetailFactory;
        $this->stockDetailFactory = $stockDetailFactory;
        $this->taxHelper = $taxHelper;
        self::$prodAttributes = $this->shipperDataHelper->getProductAttributes();
        $this->httpHeader = $httpHeader;

        $this->stockHandler = $stockHandlerFactory->create();

        $this->shipperLogger->postDebug(
            'ShipperHQ_Shipper',
            'StockHandler selected based on installed modules',
            get_class($this->stockHandler)
        );
    }

    /**
     * Set up values for ShipperHQ Rates Request
     *
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $magentoRequest
     * @return \ShipperHQ\WS\Rate\Request\RateRequest
     */
    public function getShipperTranslation($magentoRequest)
    {
        $shipperHQRequest = $this->rateRequestFactory->create([
            'cart' => $this->getCartDetails($magentoRequest),
            'destination' => $this->getDestination($magentoRequest),
            'customerDetails' => $this->getCustomerGroupDetails($magentoRequest),
            'cartType' => $this->getCartType($magentoRequest),
            'validateAddress' => $this->getValidateAddress($magentoRequest)
        ]);

        if ($delDate = $this->getDeliveryDateUTC($magentoRequest)) {
            $shipperHQRequest->setDeliveryDate($this->getDeliveryDate($magentoRequest));
            $shipperHQRequest->setDeliveryDateUTC($delDate);
        }

        if ($shipDetails = $this->getShipDetails($magentoRequest)) {
            $shipperHQRequest->setShipDetails($shipDetails);
        }
        if ($carrierGroupId = $this->getCarrierGroupId($magentoRequest)) {
            $shipperHQRequest->setCarrierGroupId($carrierGroupId);
        }

        if ($carrierId = $this->getCarrierId($magentoRequest)) {
            $shipperHQRequest->setCarrierId($carrierId);
        }

        //SHQ18-774
        $storeId = $this->shipperDataHelper->getStoreIdFromRequest($magentoRequest);

        $ipAddress = $magentoRequest->getIpAddress();
        $shipperHQRequest->setSiteDetails($this->getSiteDetails($storeId, $ipAddress));
        $shipperHQRequest->setCredentials($this->getCredentials($storeId));
        return $shipperHQRequest;
    }

    /**
     * Format cart for from shipper for Magento
     *
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     * @return array
     */
    public function getCartDetails($request)
    {
        $cartDetails = $this->cartFactory->create([
            'declaredValue' => $request->getPackageValue(),
            'freeShipping' => (bool)$request->getFreeShipping(),
            'items' => $this->getFormattedItems($request, $request->getAllItems())
        ]);
        return $cartDetails;
    }

    /**
     * Get values for items
     *
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     * @param $magentoItems
     * @param bool $childItems
     * @return array
     */
    public function getFormattedItems($request, $magentoItems, $childItems = false)
    {
        $formattedItems = [];
        if (empty($magentoItems)) {
            return $formattedItems;
        }
        $selectedCarriergroupId = false;

        if ($request->getCarriergroupId() != '') {
            $selectedCarriergroupId = $request->getCarriergroupId();
        }
        foreach ($magentoItems as $magentoItem) {
            if (!$childItems && $magentoItem->getParentItemId()) {
                continue;
            }
            //strip out items not required in carriergroup specific request
            if ($selectedCarriergroupId && $magentoItem->getCarriergroupId() != $selectedCarriergroupId) {
                continue;
            }
            $taxPercentage = 0; // Not in first release of M2
            $fixedPrice = $magentoItem->getProduct()->getPriceType() == Price::PRICE_TYPE_FIXED;
            $fixedWeight = $magentoItem->getProduct()->getWeightType() == 1 ? true : false;
            $id = $magentoItem->getItemId() ? $magentoItem->getItemId() : $magentoItem->getQuoteItemId();
            $productType = $magentoItem->getProductType() ?
                $magentoItem->getProductType() :
                $magentoItem->getProduct()->getTypeId();
            $stdAttributes = array_merge($this->getDimensionalAttributes($magentoItem), self::$stdAttributeNames);
            $weight = $this->getAdjustedItemWeight($magentoItem);
            $warehouseDetails = $this->getWarehouseDetails($magentoItem);
            $pickupLocationDetails = $this->getPickupLocationDetails($magentoItem);
            $itemAttributes = '';
            $itemAttributes = $this->populateAttributes($stdAttributes, $magentoItem);

            if ($this->taxHelper->discountTax() && $magentoItem->getTaxPercent() > 0) {
                $discountAmount = round($magentoItem->getDiscountAmount() / ($magentoItem->getTaxPercent()/100+1), 2);
                $baseDiscountAmount = round($magentoItem->getBaseDiscountAmount() / ($magentoItem->getTaxPercent()/100+1), 2);
            } else {
                $discountAmount = $magentoItem->getDiscountAmount();
                $baseDiscountAmount = $magentoItem->getBaseDiscountAmount();
            }

            $qty = $magentoItem->getQty() ? floatval($magentoItem->getQty()) : 0;

            if ($qty < 1 && $qty > 0) {
                $qty = 1; //SHQ18-438
                $weight = ($weight !== null && $weight != 0) ? $weight * $magentoItem->getQty() : $weight;

                $this->shipperLogger->postInfo(
                    'ShipperHQ_Shipper',
                    'Item quantity is decimal and less than 1, rounding quantity up to 1.'.
                    'Setting weight to fractional value',
                    'SKU: '.$magentoItem->getSku(). ' Weight: '. $weight
                );
            }

            $formattedItem = $this->itemFactory->create([
                'id' => $id,
                'sku' => $magentoItem->getSku(),
                'qty' => $qty,
                'weight' => $weight,
                'rowTotal' => $magentoItem->getRowTotal(),
                'basePrice' => $magentoItem->getBasePrice(),
                'baseRowTotal' => $magentoItem->getBaseRowTotal(),
                'discountAmount' => (float)$discountAmount,
                'discountPercent' => (float)$magentoItem->getDiscountPercent(),
                'discountedBasePrice' => $magentoItem->getBasePrice() -
                    ($baseDiscountAmount / $magentoItem->getQty()),
                'discountedStorePrice' => $magentoItem->getPrice() -
                    ($discountAmount / $magentoItem->getQty()),
                'discountedTaxInclBasePrice' => $magentoItem->getBasePrice() -
                    ($baseDiscountAmount / $magentoItem->getQty()) +
                    ($magentoItem->getBaseTaxAmount() / $magentoItem->getQty()),
                'discountedTaxInclStorePrice' => $magentoItem->getPrice() -
                    ($discountAmount / $magentoItem->getQty()) +
                    ($magentoItem->getTaxAmount() / $magentoItem->getQty()),
                'fixedPrice' => $fixedPrice,
                'fixedWeight' => $fixedWeight,
                'freeShipping' => (bool)$magentoItem->getFreeShipping(),
                'packageCurrency' => $request->getPackageCurrency()->getCurrencyCode(),
                'baseCurrency' => $request->getBaseCurrency()->getCurrencyCode(),
                'storeBaseCurrency' => $this->storeManager->getStore()->getBaseCurrencyCode(),
                'storeCurrentCurrency' => $this->storeManager->getStore()->getCurrentCurrencyCode(),
                'storePrice' => $magentoItem->getPrice() ? $magentoItem->getPrice() : 0,
                'taxInclBasePrice' => $magentoItem->getBasePriceInclTax() ? $magentoItem->getBasePriceInclTax() : 0,
                'taxInclStorePrice' => $magentoItem->getPriceInclTax() ? $magentoItem->getPriceInclTax() : 0,
                'taxPercentage' => $taxPercentage,
                'type' => $productType,
                'items' => [], // child items
                'attributes' => $itemAttributes,
                'additionalAttributes' => $this->getCustomAttributes($magentoItem),
                'warehouseDetails' => $warehouseDetails,
                'pickupLocationDetails' => $pickupLocationDetails
            ]);
            if (empty($warehouseDetails)) {
                $formattedItem->setDefaultWarehouseStockDetail($this->getDefaultWarehouseStockDetail($magentoItem));
            }

            if (!$childItems) {
                $formattedItem->setItems($this->getFormattedItems(
                    $request,
                    $magentoItem->getChildren(),
                    true
                ));
            }

            $formattedItems[] = $formattedItem;
        }
        return $formattedItems;
    }

    public function getDimensionalAttributes($item)
    {
        $attributes = [];
        $product = $item->getProduct();
        if (in_array(self::$dim_length, self::$prodAttributes) && $product->getData(self::$dim_length) != '') {
            $attributes = [self::$dim_length, self::$dim_height, self::$dim_width];
        } elseif (in_array(self::$alt_length, self::$prodAttributes) && $product->getData(self::$alt_length) != '') {
            $attributes = [self::$alt_length, self::$alt_height, self::$alt_width];
        }
        return $attributes;
    }

    /*
     * Return customer group details
     *
     */

    public function getWarehouseDetails($item)
    {
        $details = [];
        /** @var string|null $itemOriginsString */
        $itemOriginsString = $item->getProduct()->getData(self::$origin);
        if ($itemOriginsString == '') {
            return $details;
        }
        $itemOrigins = explode(',', (string) $itemOriginsString);
        if (is_array($itemOrigins)) {
            $product = $item->getProduct();
            $attribute = $product->getResource()->getAttribute(self::$origin);
            $valueString = [];
            if ($attribute) {
                foreach ($itemOrigins as $aValue) {
                    if ($aValue == '') {
                        continue;
                    }
                    $admin_value = $attribute->setStoreId(0)->getSource()->getOptionText($aValue);
                    $rawValueString = is_array($admin_value) ? implode('', $admin_value) : $admin_value;
                    $valueString = html_entity_decode($rawValueString); //SHQ18-955
                    $warehouseDetail = $this->physicalBuildingDetailFactory->create([
                        'name' => $valueString,
                        'inventoryCount' => $this->stockHandler->getOriginInventoryCount($valueString, $item, $product),
                        'availabilityDate' => $this->stockHandler->getOriginAvailabilityDate(
                            $valueString,
                            $item,
                            $product
                        ),
                        'inStock' => $this->stockHandler->getOriginInstock($valueString, $item, $product)
                    ]);
                    $details[] = $warehouseDetail;
                }
            }
        }
        return $details;
    }

    /*
    * Return ship Details selected
    *
    */

    public function getPickupLocationDetails($item)
    {
        $details = [];
        $itemLocationsString = (string) $item->getProduct()->getData(self::$location);
        if ($itemLocationsString == '') {
            return $details;
        }
        $itemLocations = explode(',', $itemLocationsString);
        if (is_array($itemLocations)) {
            $product = $item->getProduct();
            $attribute = $product->getResource()->getAttribute(self::$location);
            $valueString = [];
            if ($attribute) {
                foreach ($itemLocations as $aValue) {
                    $admin_value = $attribute->setStoreId(0)->getSource()->getOptionText($aValue);
                    $rawValueString = is_array($admin_value) ? implode('', $admin_value) : $admin_value;
                    $valueString = html_entity_decode($rawValueString); //SHQ18-955
                    $locationDetail = $this->physicalBuildingDetailFactory->create([
                        'name' => $valueString,
                        'inventoryCount' => $this->stockHandler->getLocationInventoryCount(
                            $valueString,
                            $item,
                            $product
                        ),
                        'availabilityDate' => $this->stockHandler->getLocationAvailabilityDate(
                            $valueString,
                            $item,
                            $product
                        ),
                        'inStock' => $this->stockHandler->getLocationInstock($valueString, $item, $product)
                    ]);
                    $details[] = $locationDetail;
                }
            }
        }
        return $details;
    }

    /*
    * Return cartType String
    *
    */

    /**
     * Reads attributes from the item
     *
     * @param $reqdAttributeNames
     * @param $item
     * @return array
     */
    public function populateAttributes($reqdAttributeNames, $item)
    {
        $attributes = [];
        $product = $item->getProduct();

        if ($product->getAttributeText(self::$dim_group) != '') {
            $reqdAttributeNames = array_diff($reqdAttributeNames, self::$conditional_dims);
        }

        foreach ($reqdAttributeNames as $attributeName) {
            $attribute = $product->getResource()->getAttribute($attributeName);
            if ($attribute) {
                $attributeType = $attribute->getFrontendInput();
            } else {
                continue;
            }
            if ($attributeType == 'select' || $attributeType == 'multiselect') {
                $attributeString = (string) $product->getData($attribute->getAttributeCode());
                $attributeValue = explode(',', $attributeString);
                if (is_array($attributeValue)) {
                    $valueString = [];
                    foreach ($attributeValue as $aValue) {
                        $admin_value = $attribute->setStoreId(0)->getSource()->getOptionText($aValue);
                        // SHQ18-1335 - getOptionsText may return an array in some scenarios -- see vendor/magento/module-eav/Model/Entity/Attribute/Source/Table.php
                        // SHQ18-1310 - Don't sent HTML in request. Convert to actual character
                        if (is_array($admin_value)) {
                            $valueString = array_merge(array_map("html_entity_decode", $valueString), $admin_value);
                        } else {
                            $valueString[] = html_entity_decode($admin_value);
                        }
                    }
                    $attributeValue = implode('#', $valueString);
                } else {
                    $attributeValue = $attribute->setStoreId(0)->getSource()->getOptionText($attributeValue);
                }
            } else {
                $attributeValue = $product->getData($attributeName);
            }

            if (!empty($attributeValue) && !strstr((string) $attributeValue, self::$useDefault)) {
                $attributes[] = [
                    'name' => $attributeName,
                    'value' => $attributeValue
                ];
            }
        }

        return $attributes;
    }

    /*
    * Return cartType String
    *
    */

    /**
     * Set up additional attribute array
     * This takes the values from core_config_data
     *
     * Not currently implemented for v1 Magento2.
     *
     * @param $item
     * @return array
     */
    public function getCustomAttributes($item)
    {
        $rawCustomAttributes = explode(
            ',',
            (string) $this->shipperDataHelper->getConfigValue('carriers/shipper/item_attributes')
        );
        $customAttributes = [];
        foreach ($rawCustomAttributes as $attribute) {
            $attribute = str_replace(' ', '', $attribute);
            if (!in_array((string) $attribute, self::$stdAttributeNames) &&
                !in_array((string) $attribute, self::$legacyAttributeNames) && $attribute != '') {
                $customAttributes[] = $attribute;
            }
        }

        return $this->populateAttributes($customAttributes, $item);
    }

    /*
    * Return Delivery Date selected
    *
    */

    public function getDefaultWarehouseStockDetail($item)
    {
        $product = $item->getProduct();
        $details = $this->stockDetailFactory->create([
            'inventoryCount' => $this->stockHandler->getInventoryCount($item, $product),
            'availabilityDate' => $this->stockHandler->getAvailabilityDate($item, $product),
            'inStock' => $this->stockHandler->getInstock($item, $product)
        ]);
        return $details;
    }

    /**
     * Get values for destination
     *
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     * @return array
     */
    public function getDestination($request)
    {
        $selectedOptions = $this->getSelectedOptions($request);

        $region = $request->getDestRegionCode();
        $country = $request->getDestCountryId() === null ? '' : $request->getDestCountryId();

        if ($region === null) { //SHQ16-2098
            $region = "";
        } elseif (strpos($region, $country . "-") !== false) {
            $region = str_replace($country . "-", "", $region);
        }

        //SHQ16-2238 prevent sending null in the address
        if ($this->getCartType($request) == "CART") {
            // Don't pass in street for this scenario
            $destination = $this->addressFactory->create([
                'city' => $request->getDestCity() === null ? '' : $request->getDestCity(),
                'country' => $country,
                'region' => $region,
                'zipcode' => $request->getDestPostcode() === null ? '' : $request->getDestPostcode(),
                'selectedOptions' => $selectedOptions
            ]);
        } else {
            $street = $request->getDestStreet();
            $destination = $this->addressFactory->create([
                'city' => $request->getDestCity() === null ? '' : $request->getDestCity(),
                'country' => $country,
                'region' => $region,
                'street' => $street === null || !is_string($street) ? '' : str_replace("\n", ' ', $street),
                'zipcode' => $request->getDestPostcode() === null ? '' : $request->getDestPostcode(),
                'selectedOptions' => $selectedOptions
            ]);
        }

        return $destination;
    }

    /**
     * Return pickup location selected
     *
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     *
     * @return Request\Shipping\SelectedOptions
     */
    public function getSelectedOptions($request)
    {
        $shippingOptions = $request->getSelectedOptions();
        return $this->selectedOptionsFactory->create(['options' => $shippingOptions]);
    }

    /**
     * Return selected carrierGroup id
     *
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     *
     * @return mixed
     */
    public function getCartType($request)
    {
        $cartType = $request->getCartType();
        return $cartType;
    }

    /**
     * Return selected carrier id
     *
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     *
     * @return Request\CustomerDetails
     */
    public function getCustomerGroupDetails($request)
    {
        $code = $this->getCustomerGroupId($request->getAllItems());

        $group = $this->groupFactory->create()->load($code);
        $custGroupDetails = $this->customerDetailsFactory->create(
            ['customerGroup' => $group->getCustomerGroupCode()]
        );

        return $custGroupDetails;
    }

    public function getCustomerGroupId($items)
    {
        if (!empty($items)) {
            // SHQ18-2787 Thanks to https://github.com/tristanhofman for this fix.
            // Ensure items array at 0 exists
            $item = reset($items);

            if ($item) {
                return $item->getQuote()->getCustomerGroupId();
            }
        }

        return null;
    }

    public function getValidateAddress($request)
    {
        return $request->getValidateAddress();
    }

    public function getDeliveryDateUTC($request)
    {
        $timeStamp = $request->getDeliveryDateTimestamp();
        if ($timeStamp !== null) {
            $inMilliseconds = $timeStamp * 1000;
            return $inMilliseconds;
        }
        return null;
    }

    public function getDeliveryDate($request)
    {
        return $request->getDeliveryDate();
    }

    public function getShipDetails($request)
    {
        $pickupId = $this->getLocation($request);
        if ($pickupId != '') {
            $shipDetails = $this->shipDetailsFactory->create(
                ['pickupId' => $pickupId]
            );

            return $shipDetails;
        }
        return false;
    }

    public function getLocation($request)
    {
        $selectedLocationId = $request->getLocationSelected();
        return $selectedLocationId;
    }

    public function getCarrierGroupId($request)
    {
        $carrierGroupId = $request->getCarriergroupId();
        return $carrierGroupId;
    }

    public function getCarrierId($request)
    {
        $carrierId = $request->getCarrierId();
        return $carrierId;
    }

    /**
     * Return site specific information
     *
     * @param null $storeId
     * @param null $ipAddress
     *
     * @return WS\Shared\SiteDetails
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSiteDetails($storeId = null, $ipAddress = null)
    {
        $edition = $this->shipperDataHelper->getConfigValue('carriers/shipper/magento_edition');
        $url = $this->storeManager->getStore($storeId)->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK);
        $mobilePrepend = $this->shipperDataHelper->isMobile($this->httpHeader->getHttpUserAgent()) ? 'm' : '';

        $siteDetails = $this->siteDetailsFactory->create([
            'ecommerceCart' => 'Magento 2 ' . $edition,
            'ecommerceVersion' => $this->shipperDataHelper->getConfigValue('carriers/shipper/magento_version'),
            'websiteUrl' => $url,
            'environmentScope' => $this->shipperDataHelper->getConfigValue(
                'carriers/shipper/environment_scope',
                $storeId
            ),
            'appVersion' => $this->shipperDataHelper->getConfigValue('carriers/shipper/extension_version'),
            'ipAddress' => ($ipAddress === null) ? $mobilePrepend : $mobilePrepend . $ipAddress
        ]);
        return $siteDetails;
    }

    /**
     * Return credentials for ShipperHQ login
     *
     * @return array
     */
    public function getCredentials($storeId = null)
    {
        $credentials = $this->credentialsFactory->create([
            'apiKey' => $this->shipperDataHelper->getConfigValue('carriers/shipper/api_key', $storeId),
            'password' => $this->shipperDataHelper->getConfigValue('carriers/shipper/password', $storeId)
        ]);

        return $credentials;
    }

    /**
     * Set up values for ShipperHQ getAllowedMethods()
     *
     * @param null $storeId
     * @param null $ipAddress
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCredentialsTranslation($storeId = null, $ipAddress = null)
    {
        $shipperHQRequest = $this->infoRequestFactory->create();
        $shipperHQRequest->setCredentials($this->getCredentials($storeId));
        $shipperHQRequest->setSiteDetails($this->getSiteDetails($storeId, $ipAddress));
        return $shipperHQRequest;
    }

    /**
     * Gets credentials from all websites/stores in Magento
     *
     * @return array
     */
    public function getAllCredentialsTranslation()
    {
        $credentialsPerStore = [];
        $allStoreIds = $this->shipperDataHelper->getAllStoreIds();

        foreach ($allStoreIds as $storeId) {
            $credentials = $this->getCredentialsTranslation($storeId);

            if ($credentials != null) {
                $apiKey = $credentials->getCredentials()->getApiKey();

                if (!array_key_exists($apiKey, $credentialsPerStore)) {
                    $credentialsPerStore[$apiKey] = $credentials;
                }
            }
        }

        return $credentialsPerStore;
    }

    /**
     * Gets the magento order number
     * @param $order
     * @return mixed
     */
    public function getMagentoOrderNumber($order)
    {
        return $order->getRealOrderId();
    }

    /**
     * Gets the version number. E.g 2.3.0
     *
     * @return string
     */
    public function getMagentoVersion()
    {
        return $this->productMetadata->getVersion();
    }

    /**
     * Gets the edition. E.g Commerce/Enterprise/Community
     *
     * @return string
     */
    public function getMagentoEdition()
    {
        return $this->productMetadata->getEdition();
    }

    /**
     * @param $magentoItem
     * @return null
     */
    private function getAdjustedItemWeight($magentoItem)
    {
        $weight = $magentoItem->getWeight();

        if ($weight === null || $weight == 0) {
            // Log the "raw" value of the weight field
            $weightLogString = $weight === null ? "EMPTY" : (string)$weight;
            $this->shipperLogger->postInfo(
                'ShipperHQ_Shipper',
                "Item {$magentoItem->getSku()} weight is $weightLogString." .
                "The web service will substitute the account's Default Weight",
                'SKU: ' . $magentoItem->getSku() . ' Weight: ' . $weightLogString
            );

            return null;
        }

        return $weight;
    }
}
