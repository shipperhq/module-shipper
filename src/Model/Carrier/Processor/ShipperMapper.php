<?php
/**
 *
 * Webshopapps Shipping Module
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
use ShipperHQ\WS;
use ShipperHQ\WS\Rate\Request;


class ShipperMapper
{

    protected static $ecommerceType = 'magento';
    protected static $stdAttributeNames = [
        'shipperhq_shipping_group', 'shipperhq_post_shipping_group',
        'shipperhq_warehouse', 'shipperhq_royal_mail_group', 'shipperhq_shipping_qty',
        'shipperhq_shipping_fee', 'shipperhq_additional_price', 'freight_class',
        'shipperhq_nmfc_class', 'shipperhq_nmfc_sub', 'shipperhq_handling_fee', 'shipperhq_carrier_code',
        'shipperhq_volume_weight', 'shipperhq_declared_value', 'ship_separately',
        'shipperhq_dim_group', 'shipperhq_poss_boxes', 'ship_box_tolerance', 'must_ship_freight', 'packing_section_name'
    ];

    protected static $dim_height = 'ship_height';
    protected static $dim_width = 'ship_width';
    protected static $dim_length = 'ship_length';
    protected static $alt_height = 'height';
    protected static $alt_width = 'width';
    protected static $alt_length = 'length';

    protected static $useDefault = 'Use Default';

    protected static $dim_group = 'shipperhq_dim_group';
    protected static $conditional_dims = ['shipperhq_poss_boxes',
        'shipperhq_volume_weight', 'ship_box_tolerance', 'ship_separately', 'ship_height', 'ship_width', 'ship_length',
        'height', 'width', 'length'
    ];

    protected static $legacyAttributeNames = [
        'package_id', 'special_shipping_group', 'volume_weight', 'warehouse', 'handling_id',
        'package_type' // royal mail
    ];

    protected static $shippingOptions = ['liftgate_required', 'notify_required', 'inside_delivery', 'destination_type'];

    protected static $prodAttributes;

    /**
     * @var \ShipperHQ\Shipper\Helper\Data
     */
    protected $shipperDataHelper;
    /**
     * @var \Magento\Customer\Model\GroupFactory
     */
    private $groupFactory;
    /**
     * @var \Magento\Framework\App\ProductMetadata
     */
    private $productMetadata;
    /**
     * @var \Magento\Sales\Model\Config\Data
     */
    private $dataContainer;
    /**
     * @var \Magento\Tax\Model\Calculation
     */
    private $taxCalculation;
    /**
     * @var \Magento\Catalog\Helper\Product\Configuration
     */
    private $productConfiguration;
    /**
     * @var Request\RateRequestFactory
     */
    private $rateRequestFactory;
    /**
     * @var \ShipperHQ\Shipper\Helper\Logger
     */
    private $shipperLogger;
    /**
     * @var Request\Shipping\SelectedOptionsFactory
     */
    private $selectedOptionsFactory;
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

    function __construct(\ShipperHQ\Shipper\Helper\Data $shipperDataHelper,
                         \Magento\Customer\Model\GroupFactory $groupFactory,
                         \ShipperHQ\WS\Rate\Request\RateRequestFactory $rateRequestFactory,
                         \ShipperHQ\WS\Rate\Request\InfoRequestFactory $infoRequestFactory,
                         \ShipperHQ\WS\Shared\AddressFactory $addressFactory,
                         \ShipperHQ\WS\Shared\CredentialsFactory $credentialsFactory,
                         \ShipperHQ\WS\Shared\SiteDetailsFactory $siteDetailsFactory,
                         \ShipperHQ\WS\Rate\Request\CustomerDetailsFactory $customerDetailsFactory,
                         \ShipperHQ\WS\Rate\Request\ShipDetailsFactory $shipDetailsFactory,
                         \ShipperHQ\WS\Rate\Request\Shipping\SelectedOptionsFactory $selectedOptionsFactory,
                         \Magento\Tax\Model\Calculation $taxCalculation,
                         \ShipperHQ\Shipper\Helper\Logger $shipperLogger,
                         \Magento\Catalog\Helper\Product\Configuration $productConfiguration,
                         \Magento\Framework\App\ProductMetadata $productMetadata,
                         \Magento\Sales\Model\Config\Data $dataContainer,
                         \Magento\Backend\Block\Template\Context $context
    )
    {

        $this->shipperDataHelper = $shipperDataHelper;
        $this->storeManager = $context->getStoreManager();
        self::$prodAttributes = $this->shipperDataHelper->getProductAttributes();
        $this->groupFactory = $groupFactory;
        $this->productMetadata = $productMetadata;
        $this->dataContainer = $dataContainer;
        $this->taxCalculation = $taxCalculation;
        $this->productConfiguration = $productConfiguration;
        $this->rateRequestFactory = $rateRequestFactory;
        $this->shipperLogger = $shipperLogger;
        $this->selectedOptionsFactory = $selectedOptionsFactory;
        $this->addressFactory = $addressFactory;
        $this->infoRequestFactory = $infoRequestFactory;
        $this->credentialsFactory = $credentialsFactory;
        $this->siteDetailsFactory = $siteDetailsFactory;
        $this->customerDetailsFactory = $customerDetailsFactory;
        $this->shipDetailsFactory = $shipDetailsFactory;
    }

    /**
     * Set up values for ShipperHQ Rates Request
     *
     * @param $magentoRequest
     * @return \ShipperHQ\WS\Rate\Request\RateRequest
     */
    public function getShipperTranslation($magentoRequest)
    {
        $shipperHQRequest = $this->rateRequestFactory->create([
            'cart' => $this->getCartDetails($magentoRequest),
            'destination' => $this->getDestination($magentoRequest),
            'customerDetails' => $this->getCustomerGroupDetails($magentoRequest),
            'cartType' => $this->getCartType($magentoRequest)]);

        if ($shipDetails = $this->getShipDetails($magentoRequest)) {
            $shipperHQRequest->setShipDetails($shipDetails);
        }
        if ($carrierGroupId = $this->getCarrierGroupId($magentoRequest)) {
            $shipperHQRequest->setCarrierGroupId($carrierGroupId);
        }

        if ($carrierId = $this->getCarrierId($magentoRequest)) {
            $shipperHQRequest->setCarrierId($carrierId);
        }

        $storeId = $magentoRequest->getStore();
        $shipperHQRequest->setSiteDetails($this->getSiteDetails($storeId));
        $shipperHQRequest->setCredentials($this->getCredentials($storeId));
        return $shipperHQRequest;
    }

    /**
     * Set up values for ShipperHQ getAllowedMethods()
     *
     * @return string
     */
    public function getCredentialsTranslation($storeId = null)
    {
        $shipperHQRequest = $this->infoRequestFactory->create();
        $shipperHQRequest->setCredentials($this->getCredentials($storeId));
        $shipperHQRequest->setSiteDetails($this->getSiteDetails($storeId));
        return $shipperHQRequest;
    }


    /**
     * Return credentials for ShipperHQ login
     *
     * @return array
     */
    public function getCredentials($storeId = null)
    {
        $credentials = $this->credentialsFactory->create(['apiKey' => $this->shipperDataHelper->getConfigValue('carriers/shipper/api_key'),
            'password' => $this->shipperDataHelper->getConfigValue('carriers/shipper/password', $storeId)]);
        return $credentials;
    }

    /**
     * Format cart for from shipper for Magento
     *
     * @param $request
     * @return array
     */
    public function getCartDetails($request)
    {
        $cart = [];
        $cart['declaredValue'] = $request->getPackageValue();
        $cart['freeShipping'] = (bool)$request->getFreeShipping();
        $cart['items'] = $this->getFormattedItems($request, $request->getAllItems());

        return $cart;
    }


    /**
     * Return site specific information
     *
     * @return array
     */
    public function getSiteDetails($storeId = null)
    {
        $edition = $this->productMetadata->getEdition();
        $url = $this->storeManager->getStore($storeId)->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK);
        $siteDetails = $this->siteDetailsFactory->create([
            'ecommerceCart' => 'Magento ' . $edition,
            'ecommerceVersion' => $this->productMetadata->getVersion(),
            'websiteUrl' => $url,
            'environmentScope' => $this->shipperDataHelper->getConfigValue('carriers/shipper/environment_scope', $storeId),
            'appVersion' =>$this->shipperDataHelper->getConfigValue('carriers/shipper/extension_version')
        ]);
        return $siteDetails;
    }

    /*
     * Return customer group details
     *
     */
    public function getCustomerGroupDetails($request)
    {
        $code = $this->getCustomerGroupId($request->getAllItems());

        $group = $this->groupFactory->create()->load($code);

        $custGroupDetails = $this->customerDetailsFactory->create(
            ['name' => $group->getCustomerGroupCode()]
        );

        return $custGroupDetails;
    }

    /*
    * Return ship Details selected
    *
    */
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

    /*
    * Return cartType String
    *
    */
    public function getCartType($request)
    {
        $cartType = $request->getCartType();
        return $cartType;
    }

    /*
    * Return Delivery Date selected
    *
    */
    public function getDeliveryDateUTC($request)
    {
        $timeStamp = $request->getDeliveryDateSelected();
        if (!is_null($timeStamp)) {
            $inMilliseconds = $timeStamp * 1000;
            return $inMilliseconds;
        }
        return null;
    }

    public function getDeliveryDate($request)
    {
        return $request->getDeliveryDate();
    }

    /*
    * Return pickup location selected
    *
    */
    public function getLocation($request)
    {
        $selectedLocationId = $request->getLocationSelected();
        return $selectedLocationId;
    }

    /*
     * Return selected carrierGroup id
     */
    public function getCarrierGroupId($request)
    {
        $carrierGroupId = $request->getCarriergroupId();
        return $carrierGroupId;
    }

    /*
   * Return selected carrier id
   *
   */
    public function getCarrierId($request)
    {
        $carrierId = $request->getCarrierId();
        return $carrierId;
    }

    /**
     * Get values for items
     *
     * @param $request
     * @param $magentoItems
     * @param bool $childItems
     * @return array
     */
    private function getFormattedItems($request, $magentoItems, $childItems = false)
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
            // TODO Excluded from first release
            //$taxRequest = $this->taxCalculation->getRateOriginRequest();
            //$taxRequest->setProductClassId($magentoItem->getProduct()->getTaxClassId());
            //$taxPercentage = $this->taxCalculation->getRate($taxRequest);
            $taxPercentage = 0; // Not in first release of M2
            $fixedPrice = $magentoItem->getProduct()->getPriceType() == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED;
            $fixedWeight = $magentoItem->getProduct()->getWeightType() == 1 ? true : false;
            $id = $magentoItem->getItemId() ? $magentoItem->getItemId() : $magentoItem->getQuoteItemId();
            $productType = $magentoItem->getProductType() ? $magentoItem->getProductType() : $magentoItem->getProduct()->getTypeId();
            $stdAttributes = array_merge($this->getDimensionalAttributes($magentoItem), self::$stdAttributeNames);
            $options = self::populateCustomOptions($magentoItem);
            $formattedItem = [
                'id' => $id,
                'sku' => $magentoItem->getSku(),
                'storePrice' => $magentoItem->getPrice() ? $magentoItem->getPrice() : 0,
                'weight' => $magentoItem->getWeight() ? $magentoItem->getWeight() : 0,
                'qty' => $magentoItem->getQty() ? floatval($magentoItem->getQty()) : 0,
                'type' => $productType,
                'items' => [], // child items
                'basePrice' => $magentoItem->getBasePrice(),
                'taxInclBasePrice' => $magentoItem->getBasePriceInclTax() ? $magentoItem->getBasePriceInclTax() : 0,
                'taxInclStorePrice' => $magentoItem->getPriceInclTax() ? $magentoItem->getPriceInclTax() : 0,
                'rowTotal' => $magentoItem->getRowTotal(),
                'baseRowTotal' => $magentoItem->getBaseRowTotal(),
                'discountPercent' => $magentoItem->getDiscountPercent(),
                'discountedBasePrice' => $magentoItem->getBasePrice() - ($magentoItem->getBaseDiscountAmount() / $magentoItem->getQty()),
                'discountedStorePrice' => $magentoItem->getPrice() - ($magentoItem->getDiscountAmount() / $magentoItem->getQty()),
                'discountedTaxInclBasePrice' => $magentoItem->getBasePrice() - ($magentoItem->getBaseDiscountAmount() / $magentoItem->getQty()) + ($magentoItem->getBaseTaxAmount() / $magentoItem->getQty()),
                'discountedTaxInclStorePrice' => $magentoItem->getPrice() - ($magentoItem->getDiscountAmount() / $magentoItem->getQty()) + ($magentoItem->getTaxAmount() / $magentoItem->getQty()),
                'attributes' => $options ? array_merge(self::populateAttributes($stdAttributes, $magentoItem), $options) : self::populateAttributes($stdAttributes, $magentoItem),
                'legacyAttributes' => self::populateAttributes(self::$legacyAttributeNames, $magentoItem),
                'baseCurrency' => $request->getBaseCurrency()->getCurrencyCode(),
                'packageCurrency' => $request->getPackageCurrency()->getCurrencyCode(),
                'storeBaseCurrency' => $this->storeManager->getStore()->getBaseCurrencyCode(),
                'storeCurrentCurrency' => $this->storeManager->getStore()->getCurrentCurrencyCode(),
                'taxPercentage' => $taxPercentage,
                'freeShipping' => (bool)$magentoItem->getFreeShipping(),
                'additionalAttributes' => self::getCustomAttributes($magentoItem),
                'fixedPrice' => $fixedPrice,
                'fixedWeight' => $fixedWeight,
            ];

            if (!$childItems) {
                $formattedItem['items'] = $this->getFormattedItems(
                    $request, $magentoItem->getChildren(), true);
            }

            $formattedItems[] = $formattedItem;

        }
        return $formattedItems;
    }

    protected function getCustomerGroupId($items)
    {
        if (count($items) > 0) {
            return $items[0]->getQuote()->getCustomerGroupId();
        }
        return null;
    }

    /**
     * Get values for destination
     *
     * @param $request
     * @return array
     */
    private function getDestination($request)
    {
        $selectedOptions = $this->getSelectedOptions($request);

        if (self::getCartType($request) == "CART") {
            // Don't pass in street for this scenario
            $destination = $this->addressFactory->create([
                    'city' => $request->getDestCity(),
                    'country' => $request->getDestCountryId(),
                    'region' => $request->getDestRegionCode(),
                    'zipcode' => $request->getDestPostcode(),
                    'selectedOptions' => $selectedOptions]
            );
        } else {
            $destination = $this->addressFactory->create([
                    'city' => $request->getDestCity(),
                    'country' => $request->getDestCountryId(),
                    'region' => $request->getDestRegionCode(),
                    'street' => $request->getDestStreet(),
                    'zipcode' => $request->getDestPostcode(),
                    'selectedOptions' => $selectedOptions]
            );
        }

        return $destination;
    }

    protected function getDimensionalAttributes($item)
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

    /**
     * Reads attributes from the item
     *
     * @param $reqdAttributeNames
     * @param $item
     * @return array
     */
    protected function populateAttributes($reqdAttributeNames, $item)
    {
        $attributes = [];
        $product = $item->getProduct();

        if (!in_array(self::$dim_group, self::$prodAttributes)) {
//            $this->shipperLogger->postWarning('ShipperHQ', self::$dim_group . ' attribute does not exist',
//                'Review installation to ensure latest version is installed and SQL install script has completed');
        } elseif ($product->getAttributeText(self::$dim_group) != '') {
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
                $attributeString = $product->getData($attribute->getAttributeCode());
                $attributeValue = explode(',', $attributeString);
                if (is_array($attributeValue)) {
                    $valueString = [];
                    foreach ($attributeValue as $aValue) {
                        $admin_value = $attribute->setStoreId(0)->getSource()->getOptionText($aValue);
                        $valueString[] = $admin_value;
                    }
                    $attributeValue = implode('#', $valueString);
                } else {
                    $attributeValue = $attribute->setStoreId(0)->getSource()->getOptionText($attributeValue);
                }
            } else {
                $attributeValue = $product->getData($attributeName);
            }

            if (!empty($attributeValue) && !strstr($attributeValue, self::$useDefault)) {
                $attributes[] = [
                    'name' => $attributeName,
                    'value' => $attributeValue
                ];
            }
        }

        return $attributes;
    }

    /**
     * Reads attributes from the item
     *
     * @param $reqdAttributeNames
     * @param $item
     * @return array
     */
    protected function populateCustomOptions($item)
    {
        $option_values = [];
        $options = $this->productConfiguration->getCustomOptions($item);
        $value = '';
        foreach ($options as $customOption) {
            $value .= $customOption['value'];
        }
        if ($value != '') {
            $option_values[] = [
                'name' => 'shipperhq_custom_options',
                'value' => $value
            ];
            return $option_values;
        }
        return false;

    }


    /**
     * Set up additional attribute array
     * This takes the values from core_config_data
     *
     * Not currently implemented for v1 Magento2.
     *
     * @param $item
     * @return array
     */
    protected function getCustomAttributes($item)
    {
        $rawCustomAttributes = explode(',', $this->shipperDataHelper->getConfigValue('carriers/shipper/item_attributes'));
        $customAttributes = [];
        foreach ($rawCustomAttributes as $attribute) {
            $attribute = str_replace(' ', '', $attribute);
            if (!in_array($attribute, self::$stdAttributeNames) && !in_array($attribute, self::$legacyAttributeNames) && $attribute != '') {
                $customAttributes[] = $attribute;
            }
        }

        return self::populateAttributes($customAttributes, $item);

    }

    protected function getSelectedOptions($request)
    {
        $shippingOptions = [];
        if ($request->getQuote() && $shippingAddress = $request->getQuote()->getShippingAddress()) {
            foreach (self::$shippingOptions as $option) {
                if ($shippingAddress->getData($option) != '') {
                    $shippingOptions[] = ['name' => $option, 'value' => $shippingAddress->getData($option)];
                }
            }
        }

        return $this->selectedOptionsFactory->create(['options' => $shippingOptions]);

    }

    /**
     * Gets the magento order number
     * @param $order
     * @return mixed
     */
    protected function getMagentoOrderNumber($order)
    {
        return $order->getRealOrderId();

    }
}
