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
 * @copyright Copyright (c) 2014 Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */
namespace ShipperHQ\Shipper\Model\Carrier\Convert;

/**
 * Class Shipperhq_Shipper_Model_Carrier_Convert_ShipperMapper
 *
 * This class converts the Magento Request into a format that
 * is usable by the ShipperHQ webservice
 */
use ShipperHQ\WS;
use ShipperHQ\WS\Rate\Request;


class ShipperMapper {

    protected static $ecommerceType = 'magento';
    protected static $_stdAttributeNames = array(
        'shipperhq_shipping_group', 'shipperhq_post_shipping_group',
        'shipperhq_warehouse', 'shipperhq_royal_mail_group', 'shipperhq_shipping_qty',
        'shipperhq_shipping_fee','shipperhq_additional_price','freight_class',
        'shipperhq_nmfc_class', 'shipperhq_nmfc_sub', 'shipperhq_handling_fee','shipperhq_carrier_code',
        'shipperhq_volume_weight', 'shipperhq_declared_value', 'ship_separately',
        'shipperhq_dim_group', 'shipperhq_poss_boxes', 'ship_box_tolerance', 'must_ship_freight', 'packing_section_name'
    );

    protected static $dim_height = 'ship_height';
    protected static $dim_width = 'ship_width';
    protected static $dim_length = 'ship_length';
    protected static $alt_height = 'height';
    protected static $alt_width = 'width';
    protected static $alt_length = 'length';

    protected static $useDefault = 'Use Default';

    protected static $dim_group = 'shipperhq_dim_group';
    protected static $conditional_dims = array('shipperhq_poss_boxes',
        'shipperhq_volume_weight', 'ship_box_tolerance', 'ship_separately', 'ship_height', 'ship_width', 'ship_length',
        'height' , 'width', 'length'
    );

    protected static $legacyAttributeNames = array(
        'package_id', 'special_shipping_group', 'volume_weight', 'warehouse', 'handling_id',
        'package_type' // royal mail
    );

    protected static $shippingOptions = array('liftgate_required', 'notify_required', 'inside_delivery', 'destination_type');

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

    function __construct(\ShipperHQ\Shipper\Helper\Data $shipperDataHelper,
                         \Magento\Customer\Model\GroupFactory $groupFactory,
                         \Magento\Tax\Model\Calculation $taxCalculation,
                         \Magento\Catalog\Helper\Product\Configuration $productConfiguration,
                         \Magento\Framework\App\ProductMetadata $productMetadata,
                         \Magento\Sales\Model\Config\Data $dataContainer,
                         \Magento\Backend\Block\Template\Context $context
   ) {

        $this->shipperDataHelper = $shipperDataHelper;
        $this->storeManager = $context->getStoreManager();
        self::$prodAttributes = $this->shipperDataHelper->getProductAttributes();
        $this->groupFactory = $groupFactory;
        $this->productMetadata = $productMetadata;
        $this->dataContainer = $dataContainer;
        $this->taxCalculation = $taxCalculation;
        $this->productConfiguration = $productConfiguration;
    }
    /**
     * Set up values for ShipperHQ Rates Request
     *
     * @param $magentoRequest
     * @return string
     */
    public function getShipperTranslation($magentoRequest)
    {

        $shipperHQRequest = new RateRequest(
            self::getCartDetails($magentoRequest),
            self::getDestination($magentoRequest),
            self::getCustomerGroupDetails($magentoRequest),
            self::getCartType($magentoRequest)
        );
          if($shipDetails = self::getShipDetails($magentoRequest)) {
              $shipperHQRequest->setShipDetails($shipDetails);
          }
        if($carrierGroupId = self::getCarrierGroupId($magentoRequest)) {
            $shipperHQRequest->setCarrierGroupId($carrierGroupId);
        }

        if($carrierId = self::getCarrierId($magentoRequest)) {
            $shipperHQRequest->setCarrierId($carrierId);
        }

        $storeId = $magentoRequest->getStore();
        $shipperHQRequest->setSiteDetails(self::getSiteDetails($storeId));
        $shipperHQRequest->setCredentials(self::getCredentials($storeId));

        return $shipperHQRequest;
    }

    /**
     * Set up values for ShipperHQ getAllowedMethods()
     *
     * @return string
     */
    public function getCredentialsTranslation($storeId = null)
    {
        $shipperHQRequest = new InfoRequest();
        $shipperHQRequest->setCredentials(self::getCredentials($storeId));
        $shipperHQRequest->setSiteDetails(self::getSiteDetails($storeId));
        return $shipperHQRequest;
    }


    /**
     * Return credentials for ShipperHQ login
     *
     * @return array
     */
    public function getCredentials($storeId = null)
    {

        $credentials = new Credentials($this->shipperDataHelper->getConfigValue('carriers/shipper/api_key'),
            $this->shipperDataHelper->getConfigValue('carriers/shipper/password', $storeId));
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
        $cart = array();
        $cart['declaredValue'] = $request->getPackageValue();
        $cart['freeShipping'] = (bool)$request->getFreeShipping();
        $cart['items'] = self::getFormattedItems($request,$request->getAllItems());

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
        $url = $this->storeManager->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);
        $siteDetails = new SiteDetails('Magento ' . $edition, $this->productMetadata->getVersion(),
            $url, $this->shipperDataHelper->getConfigValue('carriers/shipper/environment_scope', $storeId),
            $this->dataContainer->get('modules/ShipperHQ_Shipper/extension_version'));

        return $siteDetails;
    }

    /*
     * Return customer group details
     *
     */
    public function getCustomerGroupDetails($request)
    {
        $code = self::getCustomerGroupId($request->getAllItems());

        $group = $this->groupFactory->create()->load($code);

        $custGroupDetails = new CustomerDetails(
            $group->getCustomerGroupCode()
        );

        return $custGroupDetails;
    }

    /*
    * Return ship Details selected
    *
    */
    public static function getShipDetails($request)
    {
        $pickupId = self::getLocation($request);
        if($pickupId != '') {
            $shipDetails = new ShipDetails(
                $pickupId
            );

            return $shipDetails;
        }
        return false;
    }

    /*
    * Return cartType String
    *
    */
    public static function getCartType($request)
    {
        $cartType = $request->getCartType();
        return $cartType;
    }

    /*
    * Return Delivery Date selected
    *
    */
    public static function getDeliveryDateUTC($request)
    {
        $timeStamp = $request->getDeliveryDateSelected();
        if(!is_null($timeStamp)) {
            $inMilliseconds = $timeStamp * 1000;
            return $inMilliseconds;
        }
        return null;
    }

    public static function getDeliveryDate($request)
    {
        return $request->getDeliveryDate();
    }

    /*
    * Return pickup location selected
    *
    */
      public static function getLocation($request)
      {
          $selectedLocationId = $request->getLocationSelected();
          return $selectedLocationId;
      }

    /*
     * Return selected carrierGroup id
     */
    public static function getCarrierGroupId($request)
    {
        $carrierGroupId = $request->getCarriergroupId();
        return $carrierGroupId;
    }
    /*
   * Return selected carrier id
   *
   */
    public static function getCarrierId($request)
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
     * @param null $taxPercentage
     * @return array
     */
    private function getFormattedItems($request,$magentoItems, $childItems = false, $taxPercentage = null)
    {
        $formattedItems = array();
        if (empty($magentoItems)) {
            return $formattedItems;
        }
        $selectedCarriergroupId = false;

        if($request->getCarriergroupId() != '') {
            $selectedCarriergroupId = $request->getCarriergroupId();
        }
        foreach ($magentoItems as $magentoItem) {

            if (!$childItems && $magentoItem->getParentItemId()) {
                continue;
            }
            //strip out items not required in carriergroup specific request
            if($selectedCarriergroupId && $magentoItem->getCarriergroupId() != $selectedCarriergroupId) {
                continue;
            }
            $taxRequest = $this->taxCalculation->getRateOriginRequest();
            $taxRequest->setProductClassId($magentoItem->getProduct()->getTaxClassId());
            $taxPercentage = $this->taxCalculation->getRate($taxRequest);
            $fixedPrice = $magentoItem->getProduct()->getPriceType() == Mage_Bundle_Model_Product_Price::PRICE_TYPE_FIXED;
            $fixedWeight =  $magentoItem->getProduct()->getWeightType() == 1 ? true : false;
            $id = $magentoItem->getItemId()? $magentoItem->getItemId() : $magentoItem->getQuoteItemId();
            $productType = $magentoItem->getProductType()? $magentoItem->getProductType() : $magentoItem->getProduct()->getTypeId();
            $stdAttributes = array_merge(self::getDimensionalAttributes($magentoItem), self::$_stdAttributeNames);
            $options = self::populateCustomOptions($magentoItem);
            $formattedItem = array(
                'id'                          => $id,
                'sku'                         => $magentoItem->getSku(),
                'storePrice'                  => $magentoItem->getPrice() ? $magentoItem->getPrice() : 0,
                'weight'                      => $magentoItem->getWeight() ? $magentoItem->getWeight(): 0,
                'qty'                         => $magentoItem->getQty() ? floatval($magentoItem->getQty()): 0,
                'type'                        => $productType,
                'items'                       => array(), // child items
                'basePrice'                   => $magentoItem->getBasePrice(),
                'taxInclBasePrice'            => $magentoItem->getBasePriceInclTax()? $magentoItem->getBasePriceInclTax(): 0,
                'taxInclStorePrice'           => $magentoItem->getPriceInclTax() ? $magentoItem->getPriceInclTax() : 0,
                'rowTotal'                    => $magentoItem->getRowTotal(),
                'baseRowTotal'                => $magentoItem->getBaseRowTotal(),
                'discountPercent'             => $magentoItem->getDiscountPercent(),
                'discountedBasePrice'         => $magentoItem->getBasePrice() - ($magentoItem->getBaseDiscountAmount()/$magentoItem->getQty()),
                'discountedStorePrice'        => $magentoItem->getPrice() - ($magentoItem->getDiscountAmount()/$magentoItem->getQty()),
                'discountedTaxInclBasePrice'  => $magentoItem->getBasePrice() - ($magentoItem->getBaseDiscountAmount()/$magentoItem->getQty()) + ($magentoItem->getBaseTaxAmount()/$magentoItem->getQty()),
                'discountedTaxInclStorePrice' => $magentoItem->getPrice() - ($magentoItem->getDiscountAmount()/$magentoItem->getQty()) +  ($magentoItem->getTaxAmount()/$magentoItem->getQty()),
                'attributes'                  => $options? array_merge(self::populateAttributes($stdAttributes, $magentoItem), $options) : self::populateAttributes($stdAttributes, $magentoItem),
                'legacyAttributes'            => self::populateAttributes(self::$legacyAttributeNames, $magentoItem),
                'baseCurrency'                => $request->getBaseCurrency()->getCurrencyCode(),
                'packageCurrency'             => $request->getPackageCurrency()->getCurrencyCode(),
                'storeBaseCurrency'           => $this->storeManager->getStore()->getBaseCurrencyCode(),
                'storeCurrentCurrency'        => $this->storeManager->getStore()->getCurrentCurrencyCode(),
                'taxPercentage'               => $taxPercentage,
                'freeShipping'                => (bool)$magentoItem->getFreeShipping(),
                'additionalAttributes'        => self::getCustomAttributes($magentoItem),
                'fixedPrice'                  => $fixedPrice,
                'fixedWeight'                 => $fixedWeight,
            );

            if (!$childItems) {
                $formattedItem['items'] = self::getFormattedItems(
                    $request, $magentoItem->getChildren(), true, null );
            }

            $formattedItems[] = $formattedItem;

        }
        return $formattedItems;
    }

    protected static function getCustomerGroupId($items)
    {
        if(count($items) > 0) {
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
    private static function getDestination($request)
    {

       $selectedOptions = self::getSelectedOptions($request);

        if (self::getCartType($request)=="CART") {
            // Don't pass in street for this scenario
            $destination = new Address(
               null,
                $request->getDestCity(),
                $request->getDestCountryId(),
                $request->getDestRegionCode(),
                null,
                null,
                $request->getDestPostcode(),
                $selectedOptions
            );
        } else {
            $destination = new Address(
                null,
                $request->getDestCity(),
                $request->getDestCountryId(),
                $request->getDestRegionCode(),
                $request->getDestStreet(),
                null,
                $request->getDestPostcode(),
                $selectedOptions
            );
        }

        return $destination;
    }

    protected static function getDimensionalAttributes($item)
    {
        $attributes = array();
        $product = $item->getProduct();
        if(in_array(self::$dim_length, self::$prodAttributes) && $product->getData(self::$dim_length) != '') {
            $attributes =  array(self::$dim_length, self::$dim_height, self::$dim_width);
        }
        elseif(in_array(self::$alt_length, self::$prodAttributes) && $product->getData(self::$alt_length) != '') {
            $attributes =  array(self::$alt_length, self::$alt_height, self::$alt_width);
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
    protected static function populateAttributes($reqdAttributeNames,$item)
    {
        $attributes = array();
        $product = $item->getProduct();


        if(!in_array(self::$dim_group, self::$prodAttributes)) {
//            if ($this->shipperDataHelper->isDebug()) {
//                $this->logger->postWarning('ShipperHQ',self::$dim_group .' attribute does not exist',
//                    'Review installation to ensure latest version is installed and SQL install script has completed');
//            }
        }
        elseif($product->getAttributeText(self::$dim_group) != '') {
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
                if(is_array($attributeValue)) {
                    $valueString = array();
                    foreach($attributeValue as $aValue) {
                        $admin_value= $attribute->setStoreId(0)->getSource()->getOptionText($aValue);
                        $valueString[]= $admin_value;
                    }
                    $attributeValue = implode('#', $valueString);
                }
                else {
                    $attributeValue= $attribute->setStoreId(0)->getSource()->getOptionText($attributeValue);
                }
            } else {
                $attributeValue = $product->getData($attributeName);
            }

            if (!empty($attributeValue) && !strstr($attributeValue,self::$useDefault)) {
                $attributes[] = array (
                    'name' => $attributeName,
                    'value' => $attributeValue
                );
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
        $option_values = array();
        $options = $this->productConfiguration->getCustomOptions($item);
        $value = '';
        foreach($options as $customOption) {
            $value .= $customOption['value'];
        }
        if($value != '') {
            $option_values[] =  array (
                'name' => 'shipperhq_custom_options',
                'value' => $value
            );
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
        $customAttributes = array();
        foreach ($rawCustomAttributes as $attribute) {
            $attribute = str_replace(' ', '', $attribute);
            if(!in_array($attribute, self::$_stdAttributeNames) && !in_array($attribute, self::$legacyAttributeNames) && $attribute != '') {
                $customAttributes[] = $attribute;
            }
        }

        return self::populateAttributes($customAttributes,$item);

    }

    protected function getSelectedOptions($request)
    {
        $shippingOptions = array();
        if($request->getQuote() && $shippingAddress = $request->getQuote()->getShippingAddress()) {
            foreach(self::$shippingOptions as $option) {
                if($shippingAddress->getData($option) != '') {
                    $shippingOptions[] = array('name'=> $option, 'value' => $shippingAddress->getData($option));
                }
            }
        }

        $selectedOptions = new SelectedOptions(
            $shippingOptions
        );
        return $selectedOptions;

    }

    /**
     * Gets the magento order number
     * @param $order
     * @return mixed
     */
    protected static function getMagentoOrderNumber($order)
    {
        return  $order->getRealOrderId();

    }
}
