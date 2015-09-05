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
namespace ShipperHQ\Shipper\Model\Carrier;

/**
 * Shipper shipping model
 *
 * @category ShipperHQ
 * @package ShipperHQ_Shipper
 */

include_once 'ShipperHQ/WS/Client/WebServiceClient.php';
include_once 'ShipperHQ/WS/Response/ErrorMessages.php';

use ShipperHQ\Shipper\Helper\Config;


class Shipper
    extends \Magento\Shipping\Model\Carrier\AbstractCarrier
    implements \Magento\Shipping\Model\Carrier\CarrierInterface
{

    /**
     * Flag for check carriers for activity
     *
     * @var string
     */
    protected $activeFlag = 'active';

    /**
     * Identifies this shipping carrier
     * @var string
     */
    protected $code = 'shipper';

    /**
     * Raw rate request data
     *
     * @var Varien_Object|null
     */
    protected $rawRequest = null;

    /*
     * Rate request object
     */
    protected $shipperRequest = null;

    /**
     * Shipper Web Service instance
     *
     * @var Shipper_Shipper|null
     */
    protected $shipperWSInstance = null;

    /**
     * Error Message Lookup Object
     *
     */
    protected $errorMessageLookup = null;

    /**
     * Rate result data
     *
     * @var Mage_Shipping_Model_Rate_Result|null
     */
    protected $result = null;

    /*
     * Cache of rate results
     */
    protected static $quotesCache = array();

    /*
     * Cache setting
     */
    protected $cacheEnabled;


    /**
     * Part of carrier xml config path
     *
     * @var string
     */
    protected $availabilityConfigField = 'active';

    /**
     * Code for Wsalogger to pickup
     *
     * @var string
     */
    protected $modName = 'Shipperhq_Shipper';

    /**
     * @var \ShipperHQ\Shipper\Helper\Data
     */
    protected $shipperDataHelper;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    
    /**
     * @var Config
     */
    protected $configHelper;

    /**
     * @var Convert\ShipperMapper
     */
    protected $shipperMapper;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory
     */
    protected $rateErrorFactory;

    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    protected $rateFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;


    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    protected $rateMethodFactory;

    /**
     * ShipperHQ specific result
     *
     * @var \ShipperHQ\Shipper\Model\Rate\Result
     */
    protected $shipperResult;

    /**
     * @param Config $configHelper
     *
     */
    public function __construct(
        \ShipperHQ\Shipper\Helper\Data $shipperDataHelper,
        Config $configHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Convert\ShipperMapper $shipperMapper,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $errorFactory,
        \Magento\Shipping\Model\Rate\ResultFactory $resultFactory,
        \ShipperHQ\Shipper\Model\Rate\Result $shipperResult,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
    ) {
        $this->shipperDataHelper = $shipperDataHelper;
        $this->configHelper = $configHelper;
        $this->scopeConfig = $scopeConfig;
        $this->shipperMapper = $shipperMapper;
        $this->rateErrorFactory = $errorFactory;
        $this->rateFactory = $resultFactory;
        $this->storeManager = $storeManager;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->shipperResult = $shipperResult;
    }


        /**
     *  Retrieve sort order of current carrier
     *
     * @return mixed
     */
    public function getSortOrder()
    {
        $path = 'carriers/'.$this->getId().'/sort_order';

        return $this->_scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
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


        /**
         * TODO How to include classes that are not yet declared
        **/
//        if($this->shipperDataHelper->isModuleEnabled('Shipperhq_Pickup')) {
//            Mage::helper('shipperhq_pickup')->addPickupToRequest($request);
//        }
//
//        if($this->shipperDataHelper->isModuleEnabled('Shipperhq_Calendar')) {
//            Mage::helper('shipperhq_calendar')->addSelectedDatesToRequest($request);
//        }
//
//        if($this->shipperDataHelper->isModuleEnabled('Shipperhq_Freight')) {
//            Mage::helper('shipperhq_freight')->addSelectedFreightOptionsToRequest($request);
//        }

        $isCheckout = $this->shipperDataHelper->isCheckout();
        $cartType = (!is_null($isCheckout) && $isCheckout != 1) ? "CART" : "STD";
        if($this->shipperDataHelper->isMultiAddressCheckout()) {
            $cartType = 'MAC';
//            if($this->shipperDataHelper->isModuleEnabled('Shipperhq_Pickup')&&
//                Mage::helper('shipperhq_pickup')->pickupPreselected($request)) {
//                    $cartType = 'MAC_PICKUP';
//            }
        }
        $request->setCartType($cartType);
        $request->setStore($this->storeManager->getStore());
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


    public function refreshCarriers()
    {
        $allowedMethods =  $this->getAllowedMethods();
        if(count($allowedMethods) == 0 ) {
//            if ($this->shipperDataHelper->isDebug()) {
//                $this->logger->postInfo('Shipperhq_Shipper',
//                    'Allowed methods web service did not contain any shipping methods for carriers');
//            }
            $result['result'] = false;
            $result['error'] = 'ShipperHQ Error: No shipping methods for carrier '.end($carrierTitles) .' setup in your ShipperHQ account';
            return $result;
        }
        return $allowedMethods;

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

//            if ($this->shipperDataHelper->isDebug()) {
//                $this->logger->postDebug('Shipperhq_Shipper', 'Allowed methods response:',
//                    $resultSet['debug']);
//            }
            if (!is_object($allowedMethodResponse)) {
//                if ($this->shipperDataHelper->isDebug()) {
//                    $this->logger->postInfo('Shipperhq_Shipper',
//                        'Allowed Methods: No or invalid response received from Shipper HQ',
//                        $allowedMethodResponse);
//                }

                $shipperHQ = "<a href=https://shipperhq.com/ratesmgr/websites>ShipperHQ</a> ";
                $result['result'] = false;
                $result['error'] = 'ShipperHQ is not contactable, verify the details from the website configuration in ' .$shipperHQ;
                return $result;
            }
            else if (count($allowedMethodResponse->errors)){
//                if ($this->shipperDataHelper->isDebug()) {
//                    $this->logger->postInfo('Shipperhq_Shipper', 'Allowed methods: response contained following errors',
//                        $allowedMethodResponse);
//
//                }
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
//                if ($this->shipperDataHelper->isDebug()) {
//                    $this->logger->postInfo('Shipperhq_Shipper',
//                        'Allowed methods web service did not return any carriers or shipping methods',
//                        $allowedMethodResponse);
//                }
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

//            if ($this->shipperDataHelper->isDebug()) {
//                $this->logger->postDebug('Shipperhq_Shipper', 'Allowed methods parsed result: ',  $allowedMethods);
//            }
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
        $obj = Mage::getModel($className);
        if ($storeId) {
            $obj->setStore($storeId);
        }
        return $obj;
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
            $method->setFreightQuoteId($rateToAdd['freight_quote_id']);
            $method->setMethodDescription($rateToAdd['mergedDescription']);
            $method->setCarrierType(__('multiple_shipments'));
         //   $method->setExpectedDelivery($rateToAdd['expected_delivery']);
         //   $method->setDispatchDate($rateToAdd['dispatch_date']);
            $result->append($method);
        }
        return $result;
    }

    public function extractShipperhqRates($carrierRate, $carrierGroupId, $carrierGroupDetail, $isSplit)
    {

        $carrierResultWithRates = array(
            'code'  => $carrierRate->carrierCode,
            'title' => $carrierRate->carrierTitle);

//        if($this->shipperDataHelper->isModuleEnabled('Shipperhq_Calendar')) { // TODO
//            Mage::helper('shipperhq_calendar')->cleanUpCalendarsInSession($carrierRate->carrierCode, $carrierGroupId);
//        }
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
        $hideNotify = $this->_scopeConfig->getValue('carriers/shipper/hide_notify', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $dateOption = $carrierRate->dateOption;
        $deliveryMessage = isset($carrierRate->deliveryDateMessage) ?
            __($carrierRate->deliveryDateMessage) : '';
        if(is_null($deliveryMessage) || $deliveryMessage == '') {
            $deliveryMessage = $dateOption == $this->shipperDataHelper->TIME_IN_TRANSIT ? 'business day(s)' :
                __('Delivers :');
        }
        $customDescription = isset($carrierRate->customDescription) ?
            __($carrierRate->customDescription) : false;
        $freightRate = isset($carrierRate->availableOptions) && !empty($carrierRate->availableOptions);
        $baseRate = 1;
        $baseCurrencyCode = $this->storeManager->getStore()->getBaseCurrency()->getCode();
        $dateFormat = isset($carrierRate->deliveryDateFormat) ?
           $this->getCldrDateFormat('en_US', $carrierRate->deliveryDateFormat) : $dateFormat = $this->shipperDataHelper->getZendDateFormat();
        $latestCurrencyCode = '';
        $methodDescription = false;
        $isCheckout = $this->getQuote()->getShippingAddress()->getIsCheckout();
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
                        $carrierResultWithRates['error'] =  Mage::helper('directory')
                            ->__('Can\'t convert rate from "%s".',
                                $oneRate->currency);
                        $carrierResultWithRates['carriergroup_detail']['carrierGroupId'] = $carrierGroupId;
                        continue;
                    }

                }
            }
            $this->shipperDataHelper->populateRateLevelDetails((array)$oneRate, $carrierGroupDetail, $baseRate);
            if($oneRate->deliveryDate && is_numeric($oneRate->deliveryDate)) {
                $deliveryDate = Mage::app()->getLocale()->date($oneRate->deliveryDate/1000, null, null, true)->toString($dateFormat);
                if($dateOption == Shipperhq_Shipper_Helper_Data::DELIVERY_DATE_OPTION && isset($oneRate->deliveryDate)) {
                    $methodDescription = __(' %s %s',$deliveryMessage, $deliveryDate);
                    if($oneRate->latestDeliveryDate && is_numeric($oneRate->latestDeliveryDate)) {
                        $latestDeliveryDate = Mage::app()->getLocale()->date($oneRate->latestDeliveryDate/1000, null, null, true)->toString($dateFormat);
                        $methodDescription.= ' - ' .$latestDeliveryDate;
                    }
                }
                else if($dateOption == Shipperhq_Shipper_Helper_Data::TIME_IN_TRANSIT
                    && isset($oneRate->dispatchDate)) {
                    $deliveryMessage = __('business days');
                    $numDays = floor(abs($oneRate->deliveryDate/1000 - $oneRate->dispatchDate/1000)/60/60/24);
                    if($oneRate->latestDeliveryDate && is_numeric($oneRate->latestDeliveryDate)) {
                        $maxNumDays = floor(abs($oneRate->latestDeliveryDate/1000 - $oneRate->dispatchDate/1000)/60/60/24);
                        $methodDescription = __(' (%s - %s %s)',$numDays, $maxNumDays, $deliveryMessage);
                    }
                    else {
                        $methodDescription = __(' (%s %s)',$numDays, $deliveryMessage);
                    }
                }
            }
            if($oneRate->dispatchDate && is_numeric($oneRate->dispatchDate)) {
                $dispatchDate = Mage::app()->getLocale()->date($oneRate->dispatchDate/1000, null, null, true)->toString($dateFormat);
            }
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
                'freight_rate'  => $freightRate
            );

            if($oneRate->customDuties) {
                $rateToAdd['custom_duties'] = $oneRate->customDuties;
            }

            if($oneRate->deliveryDate && is_numeric($oneRate->deliveryDate)) {
                $carrierGroupDetail['delivery_date'] = $deliveryDate;
                $rateToAdd['delivery_date'] = $deliveryDate;
            }

            if($oneRate->dispatchDate && is_numeric($oneRate->dispatchDate)) {
                $carrierGroupDetail['dispatch_date'] = $dispatchDate;
                $rateToAdd['dispatch_date'] = $dispatchDate;
            }
            if($methodDescription) {
                $rateToAdd['method_description'] = $methodDescription;
            }
            $rateToAdd['carriergroup_detail'] = $carrierGroupDetail;

            if(!$hideNotify && isset($carrierRate->notices)) {
                foreach($carrierRate->notices as $notice) {
                    if(array_key_exists('carrier_notice', $rateToAdd)) {
                        $rateToAdd['carrier_notice'] .=  ' ' .(string)$notice ;
                    } else {
                        $rateToAdd['carrier_notice'] =  (string)$notice ;
                    }
                }
            }

            if($customDescription) {
                $rateToAdd['custom_description'] = $customDescription;
            }

            $thisCarriersRates[] = $rateToAdd;
        }
        return $thisCarriersRates;
    }

    protected function getCldrDateFormat($locale, $code)
    {
        $dateFormatArray = $this->configHelper->getCode('cldr_date_format', $locale);
        $dateFormat = is_array($dateFormatArray) && array_key_exists($code, $dateFormatArray) ? $dateFormatArray[$code]:
            $this->shipperDataHelper->getZendDateFormat();
        return $dateFormat;
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
                $backupRates = $this->_getBackupCarrierRates();
                if ($backupRates) {
                    return $backupRates;
                }
            }
            $this->_setCachedQuotes($requestString, $resultSet);

        }

        /**
         *
         * This holds the raw json
         */
        /**
         * if ($this->shipperDataHelper->isDebug()) {
            $this->logger->postInfo('Shipperhq_Shipper', 'Request/Response',
                $resultSet);
        }
        **/
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
            $this->shipperDataHelper->getQuoteStorage()->setShipperGlobal($globals);
        }
        
        if($this->shipperDataHelper->isSortOnPrice()) {
            $result = $this->rateFactory->create();
        }
        else {
            $result = $this->shipperResult->create();  // TODO - is this going to work
        }
        // If no rates are found return error message
        if (!is_object($shipperResponse)) {
//            if ($this->shipperDataHelper->isDebug()) {
//                $this->logger->postInfo('Shipperhq_Shipper', 'Shipper HQ did not return a response',
//                    $debugData);
//            }
            $message = $this->configHelper->getCode('error', 1550);

            return $this->returnGeneralError($message);
        }
        elseif(!empty($shipperResponse->errors)) {
//            if ($this->shipperDataHelper->isDebug()) {
//                $this->logger->postInfo('Shipperhq_Shipper', 'Shipper HQ returned an error',
//                    $debugData);
//            }
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
            if(count($shipperResponse->carrierGroups) > 1 && !isset($shipperResponse->mergedRateResponse )) {
//                    if ($this->shipperDataHelper->isDebug()) {
//                        $this->logger->postInfo('Shipperhq_Shipper',
//                            'Shipper HQ returned multi origin/group rates without any merged rate details',$debugData);
//                    }
            }
            $carrierRates = $this->_processRatesResponse($shipperResponse);
        }
        else {
            $carrierRates = array();
        }
        if(count($carrierRates) == 0) {
//            if ($this->shipperDataHelper->isDebug()) {
//                $this->logger->postInfo('Shipperhq_Shipper', 'Shipper HQ did not return any carrier rates',$debugData);
//            }
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
                    if(array_key_exists('custom_duties', $rateDetails)) {
                        $rate->setCustomDuties($rateDetails['custom_duties']);
                    }

                    if(array_key_exists('carrier_type', $rateDetails)) {
                        $rate->setCarrierType($rateDetails['carrier_type']);
                    }

                    if(array_key_exists('carrier_id', $rateDetails)) {
                        $rate->setCarrierId($rateDetails['carrier_id']);
                    }

                    if(array_key_exists('dispatch_date', $rateDetails)) {
                        $rate->setDispatchDate($rateDetails['dispatch_date']);
                    }

                    if(array_key_exists('delivery_date', $rateDetails)) {
                        $rate->setDeliveryDate($rateDetails['delivery_date']);
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

                    if(array_key_exists('carrier_notice', $rateDetails)) {
                        $rate->setCarrierNotice($rateDetails['carrier_notice']);
                    }

                    if(array_key_exists('freight_rate', $rateDetails)) {
                        $rate->setFreightRate($rateDetails['freight_rate']);
                    }

                    if(array_key_exists('custom_description', $rateDetails)) {
                        $rate->setCustomDescription($rateDetails['custom_description']);
                    }

                    $result->append($rate);
                }
            }
        }

//        if ($this->shipperDataHelper->isDebug()) {
//            $this->logger->postDebug('Shipperhq_Shipper', 'Rate request and result', $debugData);
//        }
        return $result;

    }

    /*
     *
     * Build array of rates based on split or merged rates display
     */
    protected function _processRatesResponse($shipperResponse)
    {
        // TODO
//        if($this->shipperDataHelper->isModuleEnabled('Shipperhq_Freight')) {
//            Mage::helper('shipperhq_freight')->parseFreightDetails($shipperResponse, $this->getQuote()->getShippingAddress()->getIsCheckout());
//        }

        //Use multi-origin/group processing
//        if($this->shipperDataHelper->isModuleEnabled('Shipperhq_Splitrates')
//            && isset($shipperResponse->mergedRateResponse) && count($shipperResponse->carrierGroups) > 1) {
//            return Mage::helper('shipperhq_splitrates')->parseCarrierGroupRates($shipperResponse, $this->rawRequest);
//        }

        $this->shipperDataHelper->setStandardShipperResponseType();

        $carrierGroups = $shipperResponse->carrierGroups;
        $ratesArray = array();
        $globals = (array)$shipperResponse->globalSettings;
        $responseSummary = (array)$shipperResponse->responseSummary;
        foreach($carrierGroups as $carrierGroup)
        {
            $carrierGroupDetail = (array)$carrierGroup->carrierGroupDetail;
            $carriergroupId = array_key_exists('carrierGroupId', $carrierGroupDetail) ? $carrierGroupDetail['carrierGroupId'] : 0;

            Mage::unregister('shipperhq_transaction');
            Mage::register('shipperhq_transaction', $responseSummary['transactionId']);
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
        $this->shipperDataHelper->getQuoteStorage()->setShipperGlobal($globals);

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
//            if ($this->logger->isDebugError() && array_key_exists('internalErrorMessage', $errorDetails)
//                && $errorDetails['internalErrorMessage'] != '') {
//                $errorMessage = $errorDetails['internalErrorMessage'];
//            }
//            else if(array_key_exists('externalErrorMessage', $errorDetails)
//                && $errorDetails['externalErrorMessage'] != '') {
//                $errorMessage = $errorDetails['externalErrorMessage'];
//            }
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

    protected function _getBackupCarrierRates()
    {
        $carrierCode = $this->_getBackupCarrierDetails();
        if(!$carrierCode) {
            return false;
        }

        $tempEnabledCarrier = $this->_tempSetCarrierEnabled($carrierCode,true);
        $carrier = $this->getCarrierByCode($carrierCode, $this->rawRequest->getStoreId());

        if (!$carrier) {
            $this->_tempSetCarrierEnabled($carrierCode,false);
//            if ($this->shipperDataHelper->isDebug()) {
//                $this->logger->postInfo('Shipperhq_Shipper', 'Unable to activate backup carrier',
//                    $carrierCode);
//            }
            return false;
        }

        $result = $carrier->collectRates($this->rawRequest);
//        if ($this->shipperDataHelper->isDebug()) {
//            $this->logger->postInfo('Shipperhq_Shipper', 'Backup carrier result: ',
//                $result);
//        }

        if ($tempEnabledCarrier) {
            $this->_tempSetCarrierEnabled($carrierCode,false);
        }
        return $result;
    }

    /**
     * Enable or disable carrier
     * @return boolean
     */
    protected function _tempSetCarrierEnabled ($carrierCode,$enabled) {
        $carrierPath='carriers/'.$carrierCode.'/'.$this->availabilityConfigField;
        $store = $this->storeManager->getStore();
        $tempEnabledCarrier = false;

        if (!$this->scopeConfig->isSetFlag($carrierPath,\Magento\Store\Model\ScopeInterface::SCOPE_STORE) || !$enabled) { // if $enabled set to false was previously enabled!
            $store->setConfig($carrierPath,$enabled);
            $tempEnabledCarrier = true;
        }

        return $tempEnabledCarrier;

    }

    /**
     * Get backup carrier if configured
     * @return mixed
     */
    protected function _getBackupCarrierDetails() {
        $carrierDetails = $this->getConfigData('backup_carrier');
//        if ($this->shipperDataHelper->isDebug()) {
//            $this->logger->postInfo('Shipperhq_Shipper', 'Unable to establish connection with ShipperHQ',
//                'Attempting to use backup carrier: ' .$carrierDetails);
//        }
        if(!$carrierDetails) {
//            if ($this->shipperDataHelper->isDebug()) {
//                $this->logger->postDebug('Shipperhq_Shipper', 'Backup carrier: ',
//                    'No backup carrier is configured');
//            }
            return false;
        }
        return $carrierDetails;
    }

    /**
     * Initialise shipper library class
     *
     * @return null|Shipper_Shipper
     */
    protected function _getShipperInstance()
    {
        if (empty($this->shipperWSInstance)) {
            $this->shipperWSInstance = new \ShipperHQ\WS\Client\WebServiceClient();
        }
        return $this->shipperWSInstance;
    }

    /**
     * Initialise shipper library class
     *
     * @return null|Shipper_Shipper
     */
    protected function _getErrorMessageLookup()
    {
        if (empty($this->errorMessageLookup)) {
            $this->errorMessageLookup = new \ShipperHQ\WS\Response\ErrorMessages();
        }
        return $this->errorMessageLookup;
    }

    /**
     * Returns cache key for some request to carrier quotes service
     *
     * @param string|array $requestParams
     * @return string
     */
    protected function _getQuotesCacheKey($requestParams)
    {
        if (is_array($requestParams)) {
            $requestParams = implode(',', array_merge(
                    array($this->getCarrierCode()),
                    array_keys($requestParams),
                    $requestParams)
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
    protected function _getCachedQuotes($requestParams)
    {
        $result = false;
        $key = $this->_getQuotesCacheKey($requestParams);
        if($this->cacheEnabled) {
            $cache = Mage::app()->getCache();
            $result = $cache->load($key);
            if($result) {
                $result = unserialize($result);
            }
        }
        else {
            $result = isset(self::$quotesCache[$key]) ? self::$quotesCache[$key] : false;
        }
        return $result;

    }

    /**
     * Sets received carrier quotes to cache
     *
     * @param string|array $requestParams
     * @param string $response
     * @return Mage_Usa_Model_Shipping_Carrier_Abstract
     */
    protected function _setCachedQuotes($requestParams, $response)
    {
        $key = $this->_getQuotesCacheKey($requestParams);
        if($this->cacheEnabled) {
            $cache = Mage::app()->getCache();
            $cache->save(serialize($response), $key, array("shipperhq_shipper"), 5*60);
        }
        else {
            self::$quotesCache[$key] = $response;
        }
        return $this;
    }

}
