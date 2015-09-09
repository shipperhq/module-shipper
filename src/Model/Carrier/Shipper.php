<?php
/**
 *
 * WebShopApps Shipping Module
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
namespace ShipperHQ\Shipper\Model\Carrier;

/**
 * Shipper shipping model
 *
 * @category ShipperHQ
 * @package ShipperHQ_Shipper
 */

use ShipperHQ\WS\Client;
use ShipperHQ\WS\Rate\Response;

use ShipperHQ\Shipper\Helper\Config;


class Shipper
    extends \Magento\Shipping\Model\Carrier\AbstractCarrier
    implements \Magento\Shipping\Model\Carrier\CarrierInterface
{
    /**
     * @var Config
     */
    protected $configHelper;
    /**
     * @var \ShipperHQ\Shipper\Helper\Data
     */
    protected $shipperDataHelper;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    protected $rateMethodFactory;
    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    protected $rateFactory;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Array of quotes
     *
     * @var array
     */
    protected static $quotesCache = [];
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;


    /**
     * @param Config $configHelper
     *
     */
    public function __construct(
        \ShipperHQ\Shipper\Helper\Data $shipperDataHelper,
        Config $configHelper,
        Convert\ShipperMapper $shipperMapper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $errorFactory,
        \Magento\Shipping\Model\Rate\ResultFactory $resultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
    ) {
        $this->shipperDataHelper = $shipperDataHelper;
        $this->configHelper = $configHelper;
        $this->shipperMapper = $shipperMapper;
        $this->rateErrorFactory = $errorFactory;
        $this->rateFactory = $resultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->objectManager = $objectManager;
        $this->registry = $registry;
    }



    /**
     * Collect and get rates
     *
     * @param \Magento\Framework\Object  $request
     * @return Mage_Shipping_Model_Rate_Result|bool|null
     */
    public function collectRates(\Magento\Framework\Object $request)
    {
        if (!$this->getConfigFlag($this->activeFlag)) {

            return false;
        }
        $this->cacheEnabled = $this->getConfigFlag('use_cache');
        $this->setRequest($request);

        $this->result = $this->_getQuotes();

        return $this->getResult();

    }

    /**
     * Prepare and set request to this instance
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return Shipperhq_Shipper_Model_Carrier_Shipper
     */
    public function setRequest(Mage_Shipping_Model_Rate_Request $request)
    {


        if (is_array($request->getAllItems())) {
            $item = current($request->getAllItems());
            if ($item instanceof Mage_Sales_Model_Quote_Item_Abstract) {
                $request->setQuote($item->getQuote());
            }
        }


        $isCheckout = $this->shipperDataHelper->isCheckout();
        $cartType = (!is_null($isCheckout) && $isCheckout != 1) ? "CART" : "STD";
        if($this->shipperDataHelper->isMultiAddressCheckout()) {
            $cartType = 'MAC';
        }
        $request->setCartType($cartType);

        $this->shipperRequest = $this->shipperMapper->getShipperTranslation($request);
        $this->rawRequest = $request;
        return $this;

    }

    /**
     * Get result of request
     *
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        $ourCarrierCode = $this->getId();
        $result = array();
        $allowedMethods = array();

        if($this->_scopeConfig->getValue(
            'carriers/shipper/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $allowedMethodUrl = $this->shipperDataHelper->getAllowedMethodGatewayUrl();
            $timeout = $this->shipperDataHelper->getWebserviceTimeout();
            $resultSet = $this->_getShipperInstance()->sendAndReceive(
                $this->shipperMapper->getCredentialsTranslation(), $allowedMethodUrl, $timeout);

            $allowedMethodResponse = $resultSet['result'];

            if (!is_object($allowedMethodResponse)) {

                $shipperHQ = "<a href=https://shipperhq.com/ratesmgr/websites>ShipperHQ</a> ";
                $result['result'] = false;
                $result['error'] = 'ShipperHQ is not contactable, verify the details from the website configuration in ' .$shipperHQ;
                return $result;
            }
            else if (count($allowedMethodResponse->errors)){

                $error = 'ShipperHQ Error: ';
                foreach($allowedMethodResponse->errors as $anError) {
                    if(isset($anError->internalErrorMessage)) {
                        $error .=  ' ' .$anError->internalErrorMessage;
                    }
                    elseif(isset($anError->externalErrorMessage) && $anError->externalErrorMessage != '') {
                        $error .=  ' ' .$anError->externalErrorMessage;
                    }
                }
                $result['result'] = false;
                $result['error'] = $error;
                return $result;
            }
            else if ( !count($allowedMethodResponse->carrierMethods)) {
                $result['result'] = false;
                $result['warning'] = 'ShipperHQ Warning: No carriers setup, log in to ShipperHQ Dashboard and create carriers';
                return $result;
            }

            $returnedMethods = $allowedMethodResponse->carrierMethods;

            $carrierConfig = array();

            foreach ($returnedMethods as $carrierMethod) {

                $rateMethods = $carrierMethod->methods;

                foreach ($rateMethods as $method) {
                    if(!is_null($ourCarrierCode) && $carrierMethod->carrierCode != $ourCarrierCode) {
                        continue;
                    }

                    $allowedMethodCode = /*$carrierMethod->carrierCode . '_' .*/ $method->methodCode;
                    $allowedMethodCode = preg_replace('/&|;| /', "_", $allowedMethodCode);

                    if (!array_key_exists($allowedMethodCode, $allowedMethods)) {
                        $allowedMethods[$allowedMethodCode] = $carrierMethod->title . '(' . $method->name . ')';
                    }
                }

                $carrierConfig[$carrierMethod->carrierCode]['title'] = $carrierMethod->title;
                if(isset($carrierMethod->sortOrder)) {
                    $carrierConfig[$carrierMethod->carrierCode]['sortOrder'] = $carrierMethod->sortOrder;
                }
            }

            // go set carrier titles
            $this->setCarrierConfig($carrierConfig);
        }
        return $allowedMethods;
    }

    /**
     * Get carrier by its code
     *
     * @param string $carrierCode
     * @param null|int $storeId
     * @return bool|Mage_Core_Model_Abstract
     */
    public function getCarrierByCode($carrierCode, $storeId = null)
    {
        if (!$this->_scopeConfig->getValue('carriers/'.$carrierCode.'/'.$this->availabilityConfigField, $storeId)) {
            return false;
        }
        $className = $this->_scopeConfig->getValue('carriers/'.$carrierCode.'/model', $storeId);
        if (!$className) {
            return false;
        }
        $carrier = $this->objectManager->create($className);
        if ($storeId) {
            $carrier->setStore($storeId);
        }
        return $carrier;
    }


    public function createMergedRate($ratesToAdd)
    {
        $result = $this->rateFactory->create();
        foreach ($ratesToAdd as $rateToAdd) {
            $method = $this->rateMethodFactory->create();
            $method->setPrice((float)$rateToAdd['price']);
            $method->setCost((float)$rateToAdd['price']);
            $method->setCarrier($this->code);
            $method->setCarrierTitle($rateToAdd['mergedTitle']);
            $method->setMethod($rateToAdd['title']);
            $method->setMethodTitle($rateToAdd['title']);
            $method->setMethodDescription($rateToAdd['mergedDescription']);
            $method->setCarrierType(__('multiple_shipments'));
            $result->append($method);
        }
        return $result;
    }

    public function extractShipperhqRates($carrierRate, $carrierGroupId, $carrierGroupDetail, $isSplit)
    {

        $carrierResultWithRates = array(
            'code'  => $carrierRate->carrierCode,
            'title' => $carrierRate->carrierTitle);

        if(isset($carrierRate->error)) {
            $carrierResultWithRates['error'] = (array)$carrierRate->error;
            $carrierResultWithRates['carriergroup_detail']['carrierGroupId'] = $carrierGroupId;
        }

        if(isset($carrierRate->rates) && !array_key_exists('error', $carrierResultWithRates)) {
            $thisCarriersRates = $this->populateRates($carrierRate, $carrierGroupDetail, $carrierGroupId, true);
            $carrierResultWithRates['rates'] = $thisCarriersRates;
        }

        return $carrierResultWithRates;
    }

    protected function populateRates($carrierRate, &$carrierGroupDetail, $carrierGroupId)
    {
        $thisCarriersRates = array();
        $baseRate = 1;
        $baseCurrencyCode = $this->storeManager->getStore()->getBaseCurrency()->getCode();
        $latestCurrencyCode = '';
        $methodDescription = false;
        foreach($carrierRate->rates as $oneRate) {
            $title = $this->shipperDataHelper->isTransactionIdEnabled() ?
                __($oneRate->name).' (' .$carrierGroupDetail['transaction'] .')'
                : __($oneRate->name);
            //currency conversion required
            if(isset($oneRate->currency) && $oneRate->currency != $latestCurrencyCode) {
                if($oneRate->currency != $baseCurrencyCode || $baseRate != 1) {
                    $baseRate = $this->shipperDataHelper->getBaseCurrencyRate($oneRate->currency);
                    $latestCurrencyCode = $oneRate->currency;
                    if(!$baseRate) {
                        $carrierResultWithRates['error'] =  __('Can\'t convert rate from "%s".',
                                $oneRate->currency);
                        $carrierResultWithRates['carriergroup_detail']['carrierGroupId'] = $carrierGroupId;
                        continue;
                    }

                }
            }
            $this->shipperDataHelper->populateRateLevelDetails((array)$oneRate, $carrierGroupDetail, $baseRate);

            if($methodDescription) {
                $title.= ' ' .$methodDescription;
            }
            $rateToAdd = array(
                'methodcode'   => $oneRate->code,
                'method_title'  => $title,
                'cost'          => (float)$oneRate->shippingPrice * $baseRate,
                'price'         => (float)$oneRate->totalCharges * $baseRate,
                'carrier_type'  => $carrierRate->carrierType,
                'carrier_id'    => $carrierRate->carrierId,
            );

            if($methodDescription) {
                $rateToAdd['method_description'] = $methodDescription;
            }
            $rateToAdd['carriergroup_detail'] = $carrierGroupDetail;

            $thisCarriersRates[] = $rateToAdd;
        }
        return $thisCarriersRates;
    }

    /**
     * Do remote request for and handle errors
     *
     * @return Mage_Shipping_Model_Rate_Result
     */
    protected function _getQuotes()
    {
        $requestString = serialize($this->shipperRequest);
        $resultSet = $this->_getCachedQuotes($requestString);
        $timeout = $this->shipperDataHelper->getWebserviceTimeout();
        if (!$resultSet) {
            $resultSet = $this->_getShipperInstance()->sendAndReceive($this->shipperRequest,
                $this->shipperDataHelper->getRateGatewayUrl(), $timeout);
            if(!$resultSet['result']){
                $backupRates = $this->getBackupCarrierRates();
                if ($backupRates) {
                    return $backupRates;
                }
            }
            $this->_setCachedQuotes($requestString, $resultSet);

        }

        return $this->_parseShipperResponse($resultSet['result']);

    }


    /**
     * @param $shipperResponse
     * @return Mage_Shipping_Model_Rate_Result
     */
    protected function _parseShipperResponse($shipperResponse)
    {
        $debugRequest = $this->shipperRequest;


        $debugRequest->credentials = null;
        $debugData = array('request' => $debugRequest, 'response' => $shipperResponse);

        //first check and save globals for display purposes
        if(is_object($shipperResponse) && isset($shipperResponse->globalSettings)) {
            $globals = (array)$shipperResponse->globalSettings;
            $this->shipperDataHelper->getQuote()->setShipperGlobal($globals);
        }
        
        $result = $this->rateFactory->create();

        // If no rates are found return error message
        if (!is_object($shipperResponse)) {
            $message = $this->configHelper->getCode('error', 1550);

            return $this->returnGeneralError($message);
        }
        elseif(!empty($shipperResponse->errors)) {
            if(isset($shipperResponse->errors)) {
                foreach($shipperResponse->errors as $error) {
                    $this->appendError($result, $error, $this->code, $this->getConfigData('title'));
                }
            }
            return $result;
        }
        elseif(!isset($shipperResponse->carrierGroups)) {

        }

        if(isset($shipperResponse->carrierGroups)) {
            $carrierRates = $this->_processRatesResponse($shipperResponse);
        }
        else {
            $carrierRates = array();
        }
        if(count($carrierRates) == 0) {

            return $result;
        }

        foreach ($carrierRates as $carrierRate) {
            if (isset($carrierRate['error'])) {
                $carriergroupId = null;
                $carrierGroupDetail = null;
                if(array_key_exists('carriergroup_detail', $carrierRate)
                    && !is_null($carrierRate['carriergroup_detail'])) {
                    if(array_key_exists('carrierGroupId', $carrierRate['carriergroup_detail'])) {
                        $carriergroupId = $carrierRate['carriergroup_detail']['carrierGroupId'];
                    }
                    $carrierGroupDetail = $carrierRate['carriergroup_detail'];
                }
                $this->appendError($result, $carrierRate['error'], $carrierRate['code'], $carrierRate['title'],
                    $carriergroupId,$carrierGroupDetail);
                continue;
            }

            if (!array_key_exists('rates', $carrierRate)) {
//                if ($this->shipperDataHelper->isDebug()) {
//                    $this->logger->postInfo('Shipperhq_Shipper',
//                        'Shipper HQ did not return any rates for '. $carrierRate['code'] .' ' .$carrierRate['title']
//                        ,$debugData);
//                }
            } else {

                foreach ($carrierRate['rates'] as $rateDetails) {
                    $rate = $this->rateFactory->create();
                    $rate->setCarrier($carrierRate['code']);
                    $rate->setCarrierTitle($carrierRate['title']);
                    $methodCombineCode = preg_replace('/&|;| /', "_", $rateDetails['methodcode']);
                    $rate->setMethod($methodCombineCode);
                    $rate->setMethodTitle(__($rateDetails['method_title']));
                    if(array_key_exists('method_description', $rateDetails)) {
                        $rate->setMethodDescription(__($rateDetails['method_description']));
                    }
                    $rate->setCost($rateDetails['cost']);
                    $rate->setPrice($rateDetails['price']);

                    if(array_key_exists('carrier_type', $rateDetails)) {
                        $rate->setCarrierType($rateDetails['carrier_type']);
                    }

                    if(array_key_exists('carrier_id', $rateDetails)) {
                        $rate->setCarrierId($rateDetails['carrier_id']);
                    }

                    if(array_key_exists('carriergroup_detail', $rateDetails)
                        && !is_null($rateDetails['carriergroup_detail'])) {
                        $rate->setCarriergroupShippingDetails(
                            $this->shipperDataHelper->encodeShippingDetails($rateDetails['carriergroup_detail']));
                        if(array_key_exists('carrierGroupId', $rateDetails['carriergroup_detail'])) {
                            $rate->setCarriergroupId($rateDetails['carriergroup_detail']['carrierGroupId']);
                        }
                        if(array_key_exists('checkoutDescription', $rateDetails['carriergroup_detail'])) {
                            $rate->setCarriergroup($rateDetails['carriergroup_detail']['checkoutDescription']);
                        }
                    }

                    $result->append($rate);
                }
            }
        }

        return $result;

    }

    /*
     *
     * Build array of rates based on split or merged rates display
     */
    protected function _processRatesResponse($shipperResponse)
    {


        $this->shipperDataHelper->setStandardShipperResponseType();

        $carrierGroups = $shipperResponse->carrierGroups;
        $ratesArray = array();
        $globals = (array)$shipperResponse->globalSettings;
        $responseSummary = (array)$shipperResponse->responseSummary;
        foreach($carrierGroups as $carrierGroup)
        {
            $carrierGroupDetail = (array)$carrierGroup->carrierGroupDetail;
            $carriergroupId = array_key_exists('carrierGroupId', $carrierGroupDetail) ? $carrierGroupDetail['carrierGroupId'] : 0;

            $this->registry->unregister('shipperhq_transaction');
            $this->registry->register('shipperhq_transaction', $responseSummary['transactionId']);
            $carrierGroupDetail['transaction'] = $responseSummary['transactionId'];

            $this->_setCarriergroupOnItems($carrierGroupDetail, $carrierGroup->products);
            $globals = array_merge($globals,$carrierGroupDetail);
            //Pass off each carrier group to helper to decide best fit to process it.
            //Push result back into our array
            foreach($carrierGroup->carrierRates as $carrierRate) {
                $carrierResultWithRates = $this->shipperDataHelper->chooseCarrierAndProcess($carrierRate, $carriergroupId, $carrierGroupDetail, false);
                $ratesArray[] = $carrierResultWithRates;
            }
        }
        $this->shipperDataHelper->getQuote()->setShipperGlobal($globals);

        $carriergroupDescriber = $shipperResponse->globalSettings->carrierGroupDescription;
        if($carriergroupDescriber != '') {
            $this->shipperDataHelper->saveConfig($this->shipperDataHelper->SHIPPERHQ_SHIPPER_CARRIERGROUP_DESC_PATH,
                $carriergroupDescriber);
        }

        $this->shipperDataHelper->refreshConfig();

        return $ratesArray;
    }

    protected function _setCarriergroupOnItems($carriergroupDetails, $productInRateResponse)
    {
        $quoteItems = $this->getQuote()->getAllItems();
        $rateItems = array();
        foreach($productInRateResponse as $item) {
            $item = (array)$item;
            $rateItems[$item['sku']] = $item['qty'];
        }

        foreach($quoteItems as $item)
        {
            if(array_key_exists($item->getSku(), $rateItems)) {
                $item->setCarriergroupId($carriergroupDetails['carrierGroupId']);
                $item->setCarriergroup($carriergroupDetails['name']);
            }
        }
        foreach($this->rawRequest->getAllItems() as $quoteItem)
        {
            if(array_key_exists($quoteItem->getSku(), $rateItems)) {
                $quoteItem->setCarriergroupId($carriergroupDetails['carrierGroupId']);
                $quoteItem->setCarriergroup($carriergroupDetails['name']);
            }
        }
    }

    /**
     * Retrieve checkout quote model object
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return $this->shipperDataHelper->getQuote();
    }
    
    /**
     *
     * Build up an error message when no carrier rates returned
     * @return Mage_Shipping_Model_Rate_Result
     */
    protected function returnGeneralError($message = null)
    {
        $result = $this->rateFactory->create();
        $error = $this->_rateErrorFactory->create();
        $error->setCarrier($this->code);
        $error->setCarrierTitle($this->getConfigData('title'));
        $error->setCarriergroupId('');
        if($message && $this->shipperDataHelper->isDebug()) {
            $error->setErrorMessage($message);
        }
        else {
            $error->setErrorMessage($this->getConfigData('specificerrmsg'));
        }
        $result->append($error);
        return $result;
    }

    /**
     *
     * Generate error message from ShipperHQ response.
     * Display of error messages per carrier is managed in SHQ configuration
     *
     * @param $result
     * @param $errorDetails
     * @return Mage_Shipping_Model_Rate_Result
     */
    protected function appendError($result, $errorDetails, $carrierCode, $carrierTitle, $carrierGroupId = null, $carrierGroupDetail = null)
    {
        if(is_object($errorDetails)) {
            $errorDetails = get_object_vars($errorDetails);
        }
        if ((array_key_exists('internalErrorMessage', $errorDetails) && $errorDetails['internalErrorMessage'] != '')
        || (array_key_exists('externalErrorMessage', $errorDetails) && $errorDetails['externalErrorMessage'] != ''))
        {
            $errorMessage = false;

            if ($this->getConfigData("debug") && array_key_exists('internalErrorMessage', $errorDetails)
                && $errorDetails['internalErrorMessage'] != '') {
                $errorMessage = $errorDetails['internalErrorMessage'];
            }
            else if(array_key_exists('externalErrorMessage', $errorDetails)
                && $errorDetails['externalErrorMessage'] != '') {
                $errorMessage = $errorDetails['externalErrorMessage'];
            }
            if(array_key_exists('externalErrorMessage', $errorDetails)
                && $errorDetails['externalErrorMessage'] != '') {
                $errorMessage = $errorDetails['externalErrorMessage'];
            }


            if($errorMessage) {
                $error = $this->_rateErrorFactory->create();
                $error->setCarrier($carrierCode);
                $error->setCarrierTitle($carrierTitle);
                $error->setErrorMessage($errorMessage);
                if(!is_null($carrierGroupId)) {
                   $error->setCarriergroupId($carrierGroupId);
                }
                if(is_array($carrierGroupDetail) && array_key_exists('checkoutDescription', $carrierGroupDetail)) {
                    $error->setCarriergroup($carrierGroupDetail['checkoutDescription']);
                }

                $result->append($error);
//                if($this->shipperDataHelper->isDebug()) {
//                    $this->logger->postInfo('Shipperhq_Shipper', 'Shipper HQ returned error', $errorDetails);
//                }
            }

        }
        return $result;
    }

    /*
     * This dynamically updates the carrier titles from ShipperHQ
     * Is required as don't want to set these on every quote request
     */
    protected function setCarrierConfig($carrierConfig)
    {
        foreach ($carrierConfig as $carrierCode=>$config) {
            $this->shipperDataHelper->saveCarrierTitle($carrierCode, $config['title']);
            if(array_key_exists('sortOrder', $config)) {
                $this->shipperDataHelper->saveConfig('carriers/'.$carrierCode.'/sort_order', $config['sortOrder']);
            }
        }

    }

    protected function getBackupCarrierRates()
    {
        $carrierCode = $this->getBackupCarrierDetails();
        if(!$carrierCode) {
            return false;
        }

        $tempEnabledCarrier = $this->tempSetCarrierEnabled($carrierCode,true);
        $carrier = $this->getCarrierByCode($carrierCode, $this->rawRequest->getStoreId());

        if (!$carrier) {
            $this->tempSetCarrierEnabled($carrierCode,false);
            return false;
        }

        $result = $carrier->collectRates($this->rawRequest);


        if ($tempEnabledCarrier) {
            $this->tempSetCarrierEnabled($carrierCode,false);
        }
        return $result;
    }

    /**
     * Enable or disable carrier
     * @return boolean
     */
    protected function tempSetCarrierEnabled ($carrierCode,$enabled) {
        $carrierPath='carriers/'.$carrierCode.'/'.$this->availabilityConfigField;
        $store = $this->storeManager->getStore();
        $tempEnabledCarrier = false;

        if (!$this->_scopeConfig->isSetFlag($carrierPath,\Magento\Store\Model\ScopeInterface::SCOPE_STORE) || !$enabled) { // if $enabled set to false was previously enabled!
            $store->setConfig($carrierPath,$enabled);
            $tempEnabledCarrier = true;
        }

        return $tempEnabledCarrier;

    }

    /**
     * Get backup carrier if configured
     * @return mixed
     */
    protected function getBackupCarrierDetails() {
        $carrierDetails = $this->getConfigData('backup_carrier');
        if(!$carrierDetails) {
            return false;
        }
        return $carrierDetails;
    }

    /**
     * Initialise shipper library class
     *
     * @return null|Shipper_Shipper
     */
    protected function getShipperInstance()
    {
        if (empty($this->shipperWSInstance)) {
            $this->shipperWSInstance = new WebServiceClient();
        }
        return $this->shipperWSInstance;
    }

    /**
     * Initialise shipper library class
     *
     * @return null|Shipper_Shipper
     */
    protected function getErrorMessageLookup()
    {
        if (empty($this->errorMessageLookup)) {
            $this->errorMessageLookup = new ErrorMessages();
        }
        return $this->errorMessageLookup;
    }




    // Taken from AbstractCarrierOnline - caching

    /**
     * Returns cache key for some request to carrier quotes service
     *
     * @param string|array $requestParams
     * @return string
     */
    protected function getQuotesCacheKey($requestParams)
    {
        if (is_array($requestParams)) {
            $requestParams = implode(
                ',',
                array_merge([$this->getCarrierCode()], array_keys($requestParams), $requestParams)
            );
        }
        return crc32($requestParams);
    }


    /**
     * Checks whether some request to rates have already been done, so we have cache for it
     * Used to reduce number of same requests done to carrier service during one session
     *
     * Returns cached response or null
     *
     * @param string|array $requestParams
     * @return null|string
     */
    protected function getCachedQuotes($requestParams)
    {
        $key = $this->getQuotesCacheKey($requestParams);
        return isset(self::$quotesCache[$key]) ? self::$quotesCache[$key] : null;
    }

    /**
     * Sets received carrier quotes to cache
     *
     * @param string|array $requestParams
     * @param string $response
     * @return $this
     */
    protected function setCachedQuotes($requestParams, $response)
    {
        $key = $this->getQuotesCacheKey($requestParams);
        self::$quotesCache[$key] = $response;
        return $this;
    }

}
