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

namespace ShipperHQ\Shipper\Helper;

use ShipperHQ\Shipper\Helper\Config;

/**
 * Shipping data helper
 */
class Data extends  \Magento\Framework\App\Helper\AbstractHelper
{
    protected static $showTransId;
    protected static $wsTimeout;
    protected $prodAttributes;
    protected $baseCurrencyRate;

    /**
     * @var Mage_Sales_Model_Quote
     */
    protected $quote;

    public $magentoCarrierCodes =
        array( 'ups' => 'ups',
            'fedEx' => 'fedex',
            'usps' => 'usps',
            'dhl' => 'dhl',
            'dhlint' => 'dhlint'
        );

    CONST SHIPPERHQ_SHIPPER_CARRIERGROUP_DESC_PATH = 'carriers/shipper/carriergroup_describer';
    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    /**
     * @var
     */
    protected $storeManager;
    /**
     * @var \Magento\Config\Model\Resource\Config
     */
    protected $resourceConfig;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    public function __construct(Config $shipperConfig,
                                \Magento\Eav\Model\Config $eavConfig,
                                \Magento\Framework\Registry $registry,
                                \Magento\Backend\Block\Template\Context $context,
                                \Magento\Catalog\Model\ProductFactory $productFactory,
                                \Magento\Checkout\Model\Session $checkoutSession,
                                \Magento\Config\Model\Resource\Config $resourceConfig,
                                \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->shipperConfig = $shipperConfig;
        $this->scopeConfig = $scopeConfig;
        $this->eavConfig = $eavConfig;
        $this->registry = $registry;
        $this->storeManager = $context->getStoreManager();
        $this->resourceConfig = $resourceConfig;
        $this->productFactory = $productFactory;
        $this->checkoutSession = $checkoutSession;
    }
    
    public function isModuleActive() {
        return self::isModuleEnabled("ShipperHQ_Shipper");
    }

    public function getConfigValue($configField) {
        return $this->scopeConfig->getValue($configField,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Retrieve debug configuration
     * @return boolean
     */
    public function isTransactionIdEnabled()
    {
        if (self::$showTransId == NULL) {
            self::$showTransId = $this->getConfigValue('carriers/shipper/display_transaction');
        }
        return self::$showTransId;

    }

    public function getTransactionId()
    {
        $id = $this->registry->registry('shipperhq_transaction');
        return $id;
    }
    

    public function chooseCarrierAndProcess($carrierRate, $carrierGroupId = null, $carrierGroupDetail = null)
    {
        $carrierCode = $carrierRate->carrierCode;
        $sort = isset($carrierRate->sortOrder) ? $carrierRate->sortOrder : false;
        $this->dynamicCarrierConfig($carrierCode, $carrierRate->carrierTitle, $sort);

        $this->populateCarrierLevelDetails((array)$carrierRate, $carrierGroupDetail);

        //Always process rates with standard if not already done
        if (!$this->registry->registry('shipper_carrier')) {
            $model = $this->carrierShipper;
            $this->registry->register('shipper_carrier', $model);
        }
        return $this->registry->registry('shipper_carrier')->extractShipperhqRates($carrierRate, $carrierGroupId, $carrierGroupDetail);
    }

    public function populateCarrierLevelDetails($carrierRate, &$carrierGroupDetail)
    {

        $carrierGroupDetail['carrierType'] = $carrierRate['carrierType'];
        $carrierGroupDetail['carrierTitle'] = $carrierRate['carrierTitle'];
        $carrierGroupDetail['carrier_code'] = $carrierRate['carrierCode'];
        $carrierGroupDetail['carrierName'] = $carrierRate['carrierName'];
        
    }


    public function getProductBreakdownText($box) {
        $productText = '';
        $weightUnit = $this->getGlobalSetting('weightUnit');
        if(!$weightUnit) {
            $weightUnit = '';
        }
        if (array_key_exists('items',$box)  || (is_object($box) && !is_null($box->getItems()))) {
            if (is_array($box['items'])) {
                foreach ($box['items'] as $item) {
                    $productText .= ' SKU=' .$item['qty_packed'] .' * '.$item['sku'] .' ' .$item['weight_packed'] .$weightUnit .';  ';
                }
            } else {
                $productText = $box['items'];
            }
        }
        return $productText;
    }


    public function populateRateLevelDetails($rate, &$carrierGroupDetail, $currencyConversionRate)
    {
        $carrierGroupDetail['methodTitle'] = $rate['name'];
        $carrierGroupDetail['price'] = (float)$rate['totalCharges']*$currencyConversionRate;
        $carrierGroupDetail['cost'] = (float)$rate['shippingPrice']*$currencyConversionRate;
        $carrierGroupDetail['code'] = $rate['code'];


    }

    public function getBaseCurrencyRate($code)
    {
        $allowedCurrencies = $this->directoryCurrency->getConfigAllowCurrencies();
        if (!in_array($code, $allowedCurrencies)) {
            return false;
        }
        $baseCurrencyCode = $this->storeManager->getStore((int)$this->getParam('store'))->getBaseCurrencyCode();
        if (!$this->baseCurrencyRate) {
            $this->baseCurrencyRate = $this->directoryCurrency
                ->load($code)
                ->getAnyRate($baseCurrencyCode);
        }

        return $this->baseCurrencyRate > 0 ? $this->baseCurrencyRate : false;

    }
    public function setStandardShipperResponseType()
    {
        $shipping = $this->getQuote()->getShippingAddress();
        if($shipping->getSplitRates()) {
            $shipping->setSplitRates(0);
        }
    }

    public function isCheckout()
    {
        $shipping = $this->getQuote()->getShippingAddress();
        $isCheckout =  $shipping->getIsCheckout();
        if($this->getQuote()->getIsMultiShipping()) {
            return true;
        }
        return $isCheckout;
    }

    public function isMultiAddressCheckout()
    {
        return $this->getQuote()->getIsMultiShipping();
    }

    /**
     * Set template for multiaddress checkout if enabled
     *
     * @return string
     */
    public function getMultiAddressTemplate()
    {
        if($this->isModuleActive()) {
            return 'shipperhq/checkout/multishipping/shipping.phtml';
        }
        return 'checkout/multishipping/shipping.phtml';
    }


    /**
     * Retrieve url for getting allowed methods
     * @return string
     */
    public function getAllowedMethodGatewayUrl()
    {
        return $this->_getGatewayUrl().'allowed_methods';
    }

    /**
     * Retrieve url for getting shipping rates
     * @return string
     */
    public function getRateGatewayUrl()
    {
        return  $this->_getGatewayUrl().'rates';

    }

    /*
     * *Retrieve url for retrieving attributes
     */
    public function getAttributeGatewayUrl()
    {
        return $this->_getGatewayUrl().'attributes/get';
    }

    /*
     * *Retrieve url for retrieving attributes
     */
    public function getCheckSynchronizedUrl()
    {
        return $this->_getGatewayUrl().'attributes/check';
    }

    /*
     * *Retrieve url for retrieving attributes
     */
    public function getSetSynchronizedUrl()
    {
        return $this->_getGatewayUrl().'attributes/set/updated';
    }

    /*
     * Retrieve configured timeout for webservice
     */
    public function getWebserviceTimeout()
    {

        if (self::$wsTimeout==NULL) {
            $timeout =  $this->getConfigValue('carriers/shipper/ws_timeout');
            if(!is_numeric($timeout) || $timeout < 120) {
                $timeout = 120;
            }
            self::$wsTimeout = $timeout;
        }
        return self::$wsTimeout;
    }

    /**
     * Saves the carrier title to core_config_data
     * Need to do this as doesnt read from the shipping rate quote table!
     * @param $carrierCode
     * @param $carrierTitle
     */
    public function saveCarrierTitle($carrierCode,$carrierTitle)
    {
        $this->resourceConfig->saveConfig('carriers/'.$carrierCode.'/title',$carrierTitle);
    }

    /**
     * Save config value to db
     * @param $path
     * @param $value
     * @param string $scope
     * @param int $scopeId
     * @return $this
     */
    public function saveConfig($path, $value, $scope = 'default', $scopeId = 0)
    {
        if ($this->getConfigValue($path) != $value) {
            $this->resourceConfig->saveConfig(rtrim($path, '/'), $value, $scope, $scopeId);
            $this->getQuote()->setConfigUpdated(true);
        }
    }

    public function encodeShippingDetails($shippingDetails)
    {
        return Zend_Json::encode($shippingDetails);
    }

    public function decodeShippingDetails($shippingDetailsEnc)
    {
        return Zend_Json::decode($shippingDetailsEnc);
    }

    /**
     * Retrieve checkout quote model object
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        if ($this->quote !== null) {
            return $this->quote;
        }

        $this->quote = $this->checkoutSession->getQuote();

        return $this->quote;

    }

    /**
     * Overrides quote model
     *
     * @param Mage_Sales_Model_Quote|null $quote
     * @return $this
     */
    public function setQuote($quote)
    {
        $this->quote = $quote;
        return $this;
    }


    public function getGlobalSetting($code)
    {
        $globals = self::getGlobalSettings();
        if(!is_null($globals) && array_key_exists($code, $globals)
            && $globals[$code] != '') {
            return $globals[$code];
        }
        return false;
    }

    /*
     * Retrieve global settings saved to session
     *
     * @return array
     */
    public function getGlobalSettings()
    {
        return $this->getQuote()->getShipperGlobal();
    }

    /*
    *
    */
    public function getOurCarrierCode()
    {
        return $this->carrierShipper->getCarrierCode();
    }

    public function mapToMagentoCarrierCode($carrierType, $carrierCode)
    {
        if(array_key_exists($carrierType, $this->magentoCarrierCodes)) {
            return $this->magentoCarrierCodes[$carrierType];
        }
        return $carrierCode;
    }

    public function refreshConfig()
    {
        if($this->getQuote()->getConfigUpdated()) {
            $this->resourceConfig->resetConfig();
            $this->getQuote()->setConfigUpdated(false);
        }
    }

    /**
     *
     * @return array
     */
    public function getProductAttributes()
    {
        if(is_null($this->prodAttributes)) {
            $this->prodAttributes = $this->eavConfig->getEntityAttributeCodes(Mage_Catalog_Model_Product::ENTITY);
        }

        return $this->prodAttributes;
    }

    public function getProductsWithAttributeValue($attribute_code, $value, $storeId = null, $isSelect = false, $returnCount = true)
    {
        if($isSelect) {
            $value = $this->_getOptionId($this->getAttribute($attribute_code, $storeId), $value);
        }

        $collection = $this->productFactory->create()->setStoreId($storeId)->getCollection();

        if(!is_null($storeId) && $storeId != '') {
            $collection->addStoreFilter((int)$storeId);
        }

        $collection->addAttributeToSelect($attribute_code);
        $collection->addFieldToFilter(array(
            array('attribute'=>$attribute_code,'finset'=>$value),
        ));
        if($returnCount) {
            return count($collection);
        }

        return $collection;
    }

    public function extractAddressIdAndCarriergroupId(&$addressId, &$carrierGroupId)
    {
        if(strstr($carrierGroupId, 'ma')) {
            $addressId = str_replace('ma', '', $carrierGroupId);
            $carrierGroupId = '';
            if(strstr($addressId, 'ZZ')) {
                $idArray = explode('ZZ', $addressId);
                $addressId = $idArray[0];
                $carrierGroupId = $idArray[1];
            }
        }
    }


    public function getCarriergroupShippingHtml($encodedDetails)
    {
        $decodedDetails = self::decodeShippingDetails($encodedDetails);
        $htmlText='';
        foreach ($decodedDetails as $shipLine) {
            if(!is_array($shipLine) || !array_key_exists('name', $shipLine)) {
                continue;
            }
            $htmlText .= $shipLine['name'].
                ' : '.$shipLine['carrierTitle'].' - '. $shipLine['methodTitle'].' ';
            $htmlText .= " ". $this->getQuote()->getStore()->formatPrice($shipLine['price']).'<br/>';

        }
        return $htmlText;
    }

    public function setShippingOnItems($shippingDetails, $shippingAddress)
    {
        $itemsGrouped = $this->getItemsGroupedByCarrierGroup($shippingAddress->getAllItems());
        foreach($shippingDetails as $carrierGroupDetail)
        {
            if(is_array($carrierGroupDetail) && array_key_exists('carrierTitle', $carrierGroupDetail)) {
                $carrierGroupId = $carrierGroupDetail['carrierGroupId'];
                $shippingText = $carrierGroupDetail['carrierTitle'] .' - ' .$carrierGroupDetail['methodTitle'];
                if(array_key_exists($carrierGroupId, $itemsGrouped)) {
                    foreach($itemsGrouped[$carrierGroupId] as $item) {
                        $item->setCarriergroupShipping($shippingText);
                    }
                }


            }

        }
    }

    /**
     * Format items based on carrier group
     *
     * @return array
     */
    public function getItemsGroupedByCarrierGroup($cartItems)
    {
        $groupedItems = array();
        foreach($cartItems as $item)
        {
            if(array_key_exists($item->getCarriergroupId(), $groupedItems)) {
                $groupedItems[$item->getCarriergroupId()][] = $item;
            }
            else {
                $groupedItems[$item->getCarriergroupId()]= array($item);
            }
        }

        return $groupedItems;
    }

    protected function _getOptionId($attribute, $value)
    {
        //get the source
        $source = $attribute->getSource();
        //get the id
        $id = $source->getOptionId($value);
        return $id;
    }

    protected function getAttribute($attribute_code, $store = null) {

        $attribute = $this->productFactory->create()->getAttribute($attribute_code);

        if(is_null($store) || $store == '') {
            $store = Store::DEFAULT_STORE_ID;
        }
        $attribute->setStoreId($store);

        return $attribute;
    }

    protected function dynamicCarrierConfig($carrierCode, $carrierTitle, $sortOrder = false)
    {
        $modelPath = 'carriers/'.$carrierCode.'/model';
        if(!$this->getConfigValue($modelPath)) {
            $model = 'shipperhq_shipper/carrier_shipper';
            $this->saveConfig($modelPath, $model);
            $this->saveConfig('carriers/'.$carrierCode.'/active', 0);
        }
        $this->saveCarrierTitle($carrierCode, $carrierTitle);

        if($sortOrder) {
            $this->saveConfig('carriers/'.$carrierCode.'/sort_order', $sortOrder);
        }
    }

    /**
     * Returns url to use - live if present, otherwise dev
     * @return array
     */
    protected function _getGatewayUrl()
    {
        $live = $this->_cleanUpUrl($this->getConfigValue('carriers/shipper/live_url'));

        $test = $this->_cleanUpUrl($this->getConfigValue('carriers/shipper/url'));
        return $this->getConfigValue('carriers/shipper/sandbox_mode') ? $test : $live;
    }

    protected function _cleanUpUrl($urlStart)
    {
        $url = trim($urlStart);
        $lastChar = substr("abcdef", -1);
        if($lastChar != '/') {
            $url .= '/';
        }
        return $url;
    }

}
