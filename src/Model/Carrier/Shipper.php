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

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace ShipperHQ\Shipper\Model\Carrier;

/**
 * Shipper shipping model
 *
 * @category ShipperHQ
 * @package ShipperHQ_Shipper
 */

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use ShipperHQ\Shipper\Helper\Config;
use ShipperHQ\WS\Client;

class Shipper extends AbstractCarrier implements CarrierInterface
{

    /**
     * Flag for check carriers for activity
     *
     * @var string
     */
    const ACTIVE_FLAG = 'active';

    /*
     * Rate request object
     * @var \ShipperHQ\WS\Rate\Request\RateRequest
     */
    /**
     * Flag for check carriers for activity
     *
     * @var string
     */
    const IGNORE_EMPTY_ZIP = 'ignore_empty_zip';
    private static $shippingOptions = ['liftgate_required', 'notify_required', 'inside_delivery', 'destination_type'];
    /**
     * @var string
     */
    protected $_code = 'shipper';
    protected $shipperRequest = null;
    /**
     * Raw rate request data
     *
     * @var Varien_Object|null
     */
    protected $rawRequest = null;
    /**
     * @var Config
     */
    protected $configHelper;
    /**
     * @var \ShipperHQ\Shipper\Helper\Data
     */
    protected $shipperDataHelper;
    /**
     * @var \ShipperHQ\Shipper\Helper\Rest
     */
    protected $restHelper;
    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    protected $rateMethodFactory;
    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    protected $rateFactory;
    /**
     * Application Event Dispatcher
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;
    /**
     * @var \ShipperHQ\Shipper\Helper\Package
     */
    protected $packageHelper;
    /**
     *
     * @var \ShipperHQ\Shipper\Helper\CarrierGroup
     */
    protected $carrierGroupHelper;
    /**
     * Rate result data
     *
     * @var Mage_Shipping_Model_Rate_Result|null
     */
    protected $result = null;
    protected $quote;
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;
    /**
     * @var \ShipperHQ\Shipper\Helper\LogAssist
     */
    private $shipperLogger;
    /**
     * @var Client\WebServiceClientFactory
     */
    private $shipperWSClientFactory;
    /**
     * @var Processor\CarrierConfigHandler
     */
    private $carrierConfigHandler;
    /**
     * @var \ShipperHQ\Shipper\Helper\CarrierCache
     */
    private $carrierCache;
    /**
     * @var Processor\BackupCarrier
     */
    private $backupCarrier;
    /**
     * @var \ShipperHQ\Lib\Rate\Helper
     */
    private $shipperRateHelper;
    /**
     * @var \ShipperHQ\Lib\Rate\ConfigSettingsFactory
     */
    private $configSettingsFactory;
    /**
     * @var \ShipperHQ\Lib\AllowedMethods\Helper
     */
    private $allowedMethodsHelper;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @param \ShipperHQ\Shipper\Helper\Data $shipperDataHelper
     * @param \ShipperHQ\Shipper\Helper\Rest $restHelper
     * @param \ShipperHQ\Shipper\Helper\CarrierCache $carrierCache
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger
     * @param \Psr\Log\LoggerInterface $logger
     * @param Config $configHelper
     * @param Processor\ShipperMapper $shipperMapper
     * @param Processor\CarrierConfigHandler $carrierConfigHandler
     * @param Processor\BackupCarrier $backupCarrier
     * @param \Magento\Framework\Registry $registry
     * @param Client\WebServiceClientFactory $shipperWSClientFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Magento\Shipping\Model\Rate\ResultFactory $resultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \ShipperHQ\Lib\Rate\Helper $shipperWSRateHelper
     * @param \ShipperHQ\Lib\Rate\ConfigSettingsFactory $configSettingsFactory
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \ShipperHQ\Shipper\Helper\Package $packageHelper
     * @param array $data
     */
    public function __construct(
        \ShipperHQ\Shipper\Helper\Data $shipperDataHelper,
        \ShipperHQ\Shipper\Helper\Rest $restHelper,
        \ShipperHQ\Shipper\Helper\CarrierCache $carrierCache,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger,
        \Psr\Log\LoggerInterface $logger,
        Config $configHelper,
        Processor\ShipperMapper $shipperMapper,
        Processor\CarrierConfigHandler $carrierConfigHandler,
        Processor\BackupCarrier $backupCarrier,
        \Magento\Framework\Registry $registry,
        \ShipperHQ\WS\Client\WebServiceClientFactory $shipperWSClientFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Magento\Shipping\Model\Rate\ResultFactory $resultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \ShipperHQ\Shipper\Helper\CarrierGroup $carrierGroupHelper,
        \ShipperHQ\Lib\Rate\Helper $shipperLibRateHelper,
        \ShipperHQ\Lib\Rate\ConfigSettingsFactory $configSettingsFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \ShipperHQ\Lib\AllowedMethods\Helper $allowedMethodsHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \ShipperHQ\Shipper\Helper\Package $packageHelper,
        array $data = []
    ) {

        $this->shipperDataHelper = $shipperDataHelper;
        $this->restHelper = $restHelper;
        $this->configHelper = $configHelper;
        $this->shipperMapper = $shipperMapper;
        $this->rateFactory = $resultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->registry = $registry;
        $this->shipperLogger = $shipperLogger;
        $this->shipperWSClientFactory = $shipperWSClientFactory;
        $this->carrierConfigHandler = $carrierConfigHandler;
        $this->carrierCache = $carrierCache;
        $this->backupCarrier = $backupCarrier;
        $this->carrierGroupHelper = $carrierGroupHelper;
        $this->shipperRateHelper = $shipperLibRateHelper;
        $this->configSettingsFactory = $configSettingsFactory;
        $this->eventManager = $eventManager;
        $this->allowedMethodsHelper = $allowedMethodsHelper;
        $this->checkoutSession = $checkoutSession;
        $this->packageHelper = $packageHelper;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * Collect and get rates
     *
     * @param RateRequest $request
     * @return bool|Result|Error
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag(self::ACTIVE_FLAG)) {
            return false;
        }

        if ($request->getDestPostcode() === null && $this->getConfigFlag(self::IGNORE_EMPTY_ZIP)) {
            $this->shipperLogger->postDebug(
                'Shipperhq_Shipper',
                'Ignoring rate request',
                'Configuration settings are to ignore requests as zipcode is empty'
            );
            return false;
        }
        $initVal = microtime(true);

        $this->cacheEnabled = $this->getConfigFlag('use_cache');
        $this->setRequest($request);

        $this->result = $this->getQuotes();
        $elapsed = microtime(true) - $initVal;
        $this->shipperLogger->postDebug('Shipperhq_Shipper', 'Long lapse', $elapsed);

        return $this->getResult();
    }

    /**
     * Prepare and set request to this instance
     *
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     * @return $this
     */
    public function setRequest(\Magento\Quote\Model\Quote\Address\RateRequest $request)
    {
        if (is_array($request->getAllItems())) {
            $item = current($request->getAllItems());
            if ($item instanceof QuoteItem) {
                $request->setQuote($item->getQuote());
                $this->quote = $item->getQuote();
            }
        }

        //SHQ16-1261 - further detail as values not on shipping address
        if (!$this->quote) {
            $this->quote = $this->shipperDataHelper->getQuote();
        }
        $shippingAddress = $this->quote->getShippingAddress();

        $key = $this->shipperDataHelper->getAddressKey($shippingAddress);
        $existing = $this->getExistingValidation($key); //SHQ16-1902
        $validate = true;
        if (is_array($existing) && !empty($existing)) {
            if (isset($existing['key']) && $existing['key'] == $key) {
                $validate = false;
            }
        } else {
            $validate = $this->shipperRateHelper->shouldValidateAddress(
                $shippingAddress->getValidationStatus(),
                $shippingAddress->getDestinationType()
            );
        }
        $request->setValidateAddress($validate);

        $request->setSelectedOptions($this->getSelectedOptions($shippingAddress, $existing));

        $isCheckout = $this->shipperDataHelper->isCheckout($this->quote);
        $cartType = ($isCheckout !== null && $isCheckout != 1) ? "CART" : "STD";
        if ($this->quote->getIsMultiShipping()) {
            $cartType = 'MAC';
        }
        $request->setCartType($cartType);

        $remoteIP = $this->quote->getRemoteIp();
        $request->setIpAddress($remoteIP);
        $this->eventManager->dispatch(
            'shipperhq_carrier_set_request',
            ['request' => $request]
        );

        $this->shipperRequest = $this->shipperMapper->getShipperTranslation($request);
        $this->rawRequest = $request;
        return $this;
    }

    private function getExistingValidation($key)
    {
        $sessionValues = $this->checkoutSession->getShipAddressValidation();
        if (is_array($sessionValues)) {
            if (isset($sessionValues['key']) && $sessionValues['key'] == $key) {
                return $sessionValues;
            }
        }
        return [];
    }

    private function getSelectedOptions($shippingAddress, $sessionValues = [])
    {
        $shipOptions = [];

        foreach (self::$shippingOptions as $option) {
            if (is_array($sessionValues) && isset($sessionValues[$option])) {
                $this->shipperLogger->postDebug(
                    'ShipperHQ Shipper',
                    'Using Session value for setting option ' . $option,
                    $sessionValues[$option]
                );
                $shipOptions[] = ['name' => $option, 'value' => $sessionValues[$option]];
            } elseif ($shippingAddress->getData($option) != '') {
                $this->shipperLogger->postDebug(
                    'ShipperHQ Shipper',
                    'Using Shipping Address value for setting option ' . $option,
                    $shippingAddress->getData($option)
                );
                $shipOptions[] = ['name' => $option, 'value' => $shippingAddress->getData($option)];
            }
        }
        return $shipOptions;
    }

    /**
     * Do remote request for and handle errors
     *
     * @return Mage_Shipping_Model_Rate_Result
     */
    private function getQuotes()
    {
        $requestString = serialize($this->shipperRequest);
        $resultSet = $this->carrierCache->getCachedQuotes($requestString, $this->getCarrierCode());
        $timeout = $this->restHelper->getWebserviceTimeout();
        if (!$resultSet) {
            $initVal = microtime(true);
            $resultSet = $this->shipperWSClientFactory->create()->sendAndReceive(
                $this->shipperRequest,
                $this->restHelper->getRateGatewayUrl(),
                $timeout
            );
            $elapsed = microtime(true) - $initVal;
            $this->shipperLogger->postDebug('Shipperhq_Shipper', 'Short lapse', $elapsed);

            if (!$resultSet['result']) {
                $backupRates = $this->backupCarrier->getBackupCarrierRates(
                    $this->rawRequest,
                    $this->getConfigData("backup_carrier")
                );
                if ($backupRates) {
                    return $backupRates;
                }
            }
            $this->carrierCache->setCachedQuotes($requestString, $resultSet, $this->getCarrierCode());
        }
        $this->shipperLogger->postInfo('Shipperhq_Shipper', 'Rate request and result', $resultSet['debug']);
        return $this->parseShipperResponse($resultSet['result']);
    }

    /**
     * @param $shipperResponse
     * @return Mage_Shipping_Model_Rate_Result
     */
    private function parseShipperResponse($shipperResponse)
    {
        $debugRequest = $this->shipperRequest;

        $debugData = ['request' => json_encode($debugRequest, JSON_PRETTY_PRINT), 'response' => $shipperResponse];
        if (!is_object($shipperResponse)) {
            $this->shipperLogger->postInfo('Shipperhq_Shipper', 'Shipper HQ did not return a response', $debugData);

            return $this->returnGeneralError(
                'Shipper HQ did not return a response - could not contact ShipperHQ. Please review your settings'
            );
        }
        $transactionId = $this->shipperRateHelper->extractTransactionId($shipperResponse);
        $this->registry->unregister('shipperhq_transaction');

        $this->registry->register('shipperhq_transaction', $transactionId);

        //first check and save globals for display purposes
        $globals = [];
        if (is_object($shipperResponse) && isset($shipperResponse->globalSettings)) {
            $globals = $this->shipperRateHelper->extractGlobalSettings($shipperResponse);
            $globals['transaction'] = $transactionId;
            $this->shipperDataHelper->setGlobalSettings($globals);
        }

        $result = $this->rateFactory->create();

        // If no rates are found return error message
        if (!empty($shipperResponse->errors)) {
            $this->shipperLogger->postInfo('Shipperhq_Shipper', 'Shipper HQ returned an error', $debugData);
            if (isset($shipperResponse->errors)) {
                foreach ($shipperResponse->errors as $error) {
                    $this->appendError($result, $error, $this->_code, $this->getConfigData('title'));
                }
            }
            return $result;
        }

        if (isset($shipperResponse->carrierGroups)) {
            $carrierRates = $this->processRatesResponse($shipperResponse, $transactionId);
        } else {
            $carrierRates = [];
        }

        if ($this->rawRequest->getValidateAddress()) { //SHQ18-141 only persist address validation if we have validated
            $this->persistAddressValidation($shipperResponse);
        }

        if (count($carrierRates) == 0) {
            $this->shipperLogger->postInfo(
                'Shipperhq_Shipper',
                'Shipper HQ did not return any carrier rates',
                $debugData
            );
            return $result;
        }

        foreach ($carrierRates as $carrierRate) {
            if (isset($carrierRate['error'])) {
                $carriergroupId = null;
                $carrierGroupDetail = null;
                if (array_key_exists('carriergroup_detail', $carrierRate)
                    && isset($carrierRate['carriergroup_detail'])
                ) {
                    if (array_key_exists('carrierGroupId', $carrierRate['carriergroup_detail'])) {
                        $carriergroupId = $carrierRate['carriergroup_detail']['carrierGroupId'];
                    }
                    $carrierGroupDetail = $carrierRate['carriergroup_detail'];
                }
                $this->appendError(
                    $result,
                    $carrierRate['error'],
                    $carrierRate['code'],
                    $carrierRate['title'],
                    $carriergroupId,
                    $carrierGroupDetail
                );
                continue;
            }

            if (!array_key_exists('rates', $carrierRate)) {
                $this->shipperLogger->postInfo(
                    'Shipperhq_Shipper',
                    'Shipper HQ did not return any rates for '
                    . $carrierRate['code']
                    . ' '
                    . $carrierRate['title'],
                    $debugData
                );
            } else {
                $baseRate = 1;
                $baseCurrencyCode = $this->shipperDataHelper->getBaseCurrencyCode();
                foreach ($carrierRate['rates'] as $rateDetails) {
                    if (isset($rateDetails['currency'])) {
                        if ($rateDetails['currency'] != $baseCurrencyCode || $baseRate != 1) {
                            $baseRate = $this->shipperDataHelper->getBaseCurrencyRate($rateDetails['currency']);
                            if (!$baseRate) {
                                $error = __('Can\'t convert rate from "%1".', $rateDetails['currency']);
                                $this->appendError(
                                    $result,
                                    $error,
                                    $carrierRate['code'],
                                    $carrierRate['title'],
                                    $rateDetails['carriergroup_detail']['carrierGroupId'],
                                    $rateDetails['carriergroup_detail']
                                );
                                $this->shipperLogger->postCritical(
                                    'Shipperhq_Shipper',
                                    'Currency Rate Missing',
                                    'Currency code in shipping rate is ' . $rateDetails['currency']
                                    . ' but there is no currency conversion rate configured'
                                    . ' so we cannot display this shipping rate'
                                );
                                continue;
                            }
                        }
                    }

                    $rate = $this->rateMethodFactory->create();
                    $rate->setCarrier($carrierRate['code']);
                    $lengthCarrierCode = strlen($carrierRate['code']);

                    $rate->setCarrierTitle(__($carrierRate['title']));

                    $methodCombineCode = preg_replace('/&|;| /', "", $rateDetails['methodcode']);
                    //SHQ16-1520 - enforce limit on length of shipping carrier code
                    // and method code of less than 35 - M2 hard limit of 40
                    $lengthMethodCode = strlen($methodCombineCode);

                    if ($lengthCarrierCode + $lengthMethodCode > 38) {
                        $total = $lengthCarrierCode + $lengthMethodCode;
                        $trim = $total - 35;
                        $methodCombineCode = substr($methodCombineCode, $trim, $lengthMethodCode);
                    }
                    $rate->setMethod($methodCombineCode);

                    $rate->setMethodTitle(__($rateDetails['method_title']));
                    $rate->setTooltip($rateDetails['tooltip']);

                    if (array_key_exists('method_description', $rateDetails)) {
                        $rate->setMethodDescription(__($rateDetails['method_description']));
                    }
                    $rate->setCost($rateDetails['cost'] * $baseRate);

                    $rate->setPrice($rateDetails['price'] * $baseRate);

                    if (array_key_exists('carrier_type', $rateDetails)) {
                        $rate->setCarrierType($rateDetails['carrier_type']);
                    }

                    if (array_key_exists('carrier_id', $rateDetails)) {
                        $rate->setCarrierId($rateDetails['carrier_id']);
                    }

                    if (array_key_exists('dispatch_date', $rateDetails)) {
                        $rate->setDispatchDate($rateDetails['dispatch_date']);
                    }

                    if (array_key_exists('delivery_date', $rateDetails)) {
                        $rate->setDeliveryDate($rateDetails['delivery_date']);
                    }

                    if (array_key_exists('carriergroup_detail', $rateDetails)
                        && isset($rateDetails['carriergroup_detail'])
                    ) {
                        $carrierGroupDetail = $baseRate != 1 ? $this->updateWithCurrrencyConversion(
                            $rateDetails['carriergroup_detail'],
                            $baseRate
                        ) :
                            $rateDetails['carriergroup_detail'];

                        $rate->setCarriergroupShippingDetails(
                            $this->shipperDataHelper->encode($carrierGroupDetail)
                        );
                        if (array_key_exists('carrierGroupId', $carrierGroupDetail)) {
                            $rate->setCarriergroupId($carrierGroupDetail['carrierGroupId']);
                        }

                        if (array_key_exists('checkoutDescription', $carrierGroupDetail)) {
                            $rate->setCarriergroup($carrierGroupDetail['checkoutDescription']);
                        }
                    }
                    $result->append($rate);
                }
                if (isset($carrierRate['shipments'])) {
                    $this->persistShipments($carrierRate['shipments']);
                }
            }
        }
        return $result;
    }

    /**
     *
     * Build up an error message when no carrier rates returned
     * @return Mage_Shipping_Model_Rate_Result
     */
    private function returnGeneralError($message = null)
    {
        $result = $this->rateFactory->create();
        $error = $this->_rateErrorFactory->create();
        $error->setCarrier($this->_code);
        $error->setCarrierTitle($this->getConfigData('title'));
        $error->setCarriergroupId('');
        if ($message && $this->shipperDataHelper->getConfigValue('carriers/shipper/debug')) {
            $error->setErrorMessage($message);
        } else {
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
    private function appendError(
        $result,
        $errorDetails,
        $carrierCode,
        $carrierTitle,
        $carrierGroupId = null,
        $carrierGroupDetail = null
    ) {

        if (is_object($errorDetails)) {
            $errorDetails = get_object_vars($errorDetails);
        }
        if ((array_key_exists('internalErrorMessage', $errorDetails) && $errorDetails['internalErrorMessage'] != '')
            || (array_key_exists('externalErrorMessage', $errorDetails) && $errorDetails['externalErrorMessage'] != '')
        ) {
            $errorMessage = false;

            if ($this->getConfigData("debug") && array_key_exists('internalErrorMessage', $errorDetails)
                && $errorDetails['internalErrorMessage'] != ''
            ) {
                $errorMessage = $errorDetails['internalErrorMessage'];
            } elseif (array_key_exists('externalErrorMessage', $errorDetails)
                && $errorDetails['externalErrorMessage'] != ''
            ) {
                $errorMessage = $errorDetails['externalErrorMessage'];
            }

            if ($errorMessage) {
                $error = $this->_rateErrorFactory->create();
                $error->setCarrier($carrierCode);
                $error->setCarrierTitle($carrierTitle);
                $error->setErrorMessage($errorMessage);
                if ($carrierGroupId !== null) {
                    $error->setCarriergroupId($carrierGroupId);
                }
                if (is_array($carrierGroupDetail) && array_key_exists('checkoutDescription', $carrierGroupDetail)) {
                    $error->setCarriergroup($carrierGroupDetail['checkoutDescription']);
                }

                $result->append($error);

                $this->shipperLogger->postInfo('Shipperhq_Shipper', 'Shipper HQ returned error', $errorDetails);
            }
        }
        return $result;
    }

    private function processRatesResponse($shipperResponse, $transactionId)
    {
        $carrierGroups = $shipperResponse->carrierGroups;
        $ratesArray = [];

        $timezone = $this->shipperDataHelper->getConfigValue('general/locale/timezone');
        $configSettings = $this->configSettingsFactory->create([
            'hideNotifications' => $this->getConfigFlag('carriers/shipper/hide_notify'),
            'transactionIdEnabled' => $this->shipperDataHelper->isTransactionIdEnabled(),
            'locale' => $this->getLocaleInGlobals(),
            'shipperHQCode' => $this->_code,
            'shipperHQTitle' => $this->getConfigFlag('carriers/shipper/title'),
            'timezone' => $timezone
        ]);

        $splitCarrierGroupDetail = [];
        foreach ($carrierGroups as $carrierGroup) {
            $carrierGroupDetail = $this->shipperRateHelper->extractCarrierGroupDetail(
                $carrierGroup,
                $transactionId,
                $configSettings
            );
            $this->setCarriergroupOnItems($carrierGroupDetail, $carrierGroup->products);
            //Pass off each carrier group to helper to decide best fit to process it.
            //Push result back into our array
            foreach ($carrierGroup->carrierRates as $carrierRate) {
                $this->carrierConfigHandler->saveCarrierResponseDetails($carrierRate, $carrierGroupDetail);
                $carrierResultWithRates = $this->shipperRateHelper->extractShipperHQRates(
                    $carrierRate,
                    $carrierGroupDetail,
                    $configSettings,
                    $splitCarrierGroupDetail
                );
                $ratesArray[] = $carrierResultWithRates;
                //push out event so other modules can save their data
                $this->eventManager->dispatch(
                    'shipperhq_carrier_rate_response_received',
                    ['carrier_rate_response' => $carrierRate, 'carrier_group_detail' => $carrierGroupDetail]
                );
            }
        }

        //check for configuration here for display
        if ($shipperResponse->mergedRateResponse) {
            $mergedRatesArray = [];
            foreach ($shipperResponse->mergedRateResponse->carrierRates as $carrierRate) {
                $this->carrierConfigHandler->saveCarrierResponseDetails($carrierRate, null);
                $mergedResultWithRates = $this->shipperRateHelper->extractShipperHQMergedRates(
                    $carrierRate,
                    $splitCarrierGroupDetail,
                    $configSettings,
                    $transactionId
                );
                $mergedRatesArray[] = $mergedResultWithRates;
            }
            $ratesArray = $mergedRatesArray;
        }

        $carriergroupDescriber = $shipperResponse->globalSettings->carrierGroupDescription;
        if ($carriergroupDescriber != '') {
            $this->carrierConfigHandler->saveConfig(
                $this->shipperDataHelper->getCarrierGroupDescPath(),
                $carriergroupDescriber
            );
        }

        $this->carrierConfigHandler->refreshConfig();

        return $ratesArray;
    }

    private function getLocaleInGlobals()
    {
        $locale = $this->shipperDataHelper->getGlobalSetting('preferredLocale');
        return $locale ? $locale : 'en-US';
    }

    private function setCarriergroupOnItems($carriergroupDetails, $productInRateResponse)
    {
        $rateItems = [];
        foreach ($productInRateResponse as $item) {
            $item = (array)$item;
            $rateItems[$item['sku']] = $item['qty'];
        }

        foreach ($this->rawRequest->getAllItems() as $quoteItem) {
            if (array_key_exists($quoteItem->getSku(), $rateItems)) {
                $quoteItem->setCarriergroupId($carriergroupDetails['carrierGroupId']);
                $quoteItem->setCarriergroup($carriergroupDetails['name']);

                //need to work out how to distinguish between quote address items on multi address checkout
                $this->carrierGroupHelper->saveCarrierGroupItem(
                    $quoteItem,
                    $carriergroupDetails['carrierGroupId'],
                    $carriergroupDetails['name']
                );
            }
        }
    }

    private function persistAddressValidation($shipperResponse)
    {
        //we've validated so we need to save
        $shippingAddress = $this->quote->getShippingAddress();
        $key = $this->shipperDataHelper->getAddressKey($shippingAddress);
        $existing = [];
        $addressType = $this->shipperRateHelper->extractDestinationType($shipperResponse);
        $validationStatus = $this->shipperRateHelper->extractAddressValidationStatus($shipperResponse);
        $validatedAddress = $this->shipperRateHelper->extractValidatedAddress($shipperResponse);
        if ($validationStatus) {
            $existing['validation_status'] = $validationStatus;
        }

        if ($addressType) {
            $existing['destination_type'] = $addressType;
        }

        if ($validatedAddress) {
           foreach ($validatedAddress as $field => $value) {
               if ($field == 'addressType') continue;
               if($field == 'zipcode') { // handle where it's returning zipcode instead of postcode
                   $field == 'postcode';
               }
               $existing['validated_shipping_' .$field] = $value;
           }
        }

        if (!empty($existing)) {
            $existing['key'] = $key;
            $this->checkoutSession->setShipAddressValidation($existing);
        }
    }

    /*
     *
     * Build array of rates based on split or merged rates display
     */

    private function updateWithCurrrencyConversion($carrierGroupDetail, $currencyConversionRate)
    {
        if (is_array($carrierGroupDetail) && isset($carrierGroupDetail[0])) {
            // Merged rates return a numeric array of assoc arrays. If there is a 0 key we know this is the case
            foreach ($carrierGroupDetail as $k=>$detail) {
                $carrierGroupDetail[$k]['cost'] *= $currencyConversionRate;
                $carrierGroupDetail[$k]['price'] *= $currencyConversionRate;
            }
        }
        else {
            $carrierGroupDetail['cost'] *= $currencyConversionRate;
            $carrierGroupDetail['price'] *= $currencyConversionRate;
        }
        return $carrierGroupDetail;
    }

    private function persistShipments($shipmentArray)
    {
        $shippingAddress = $this->quote->getShippingAddress();
        $addressId = $shippingAddress->getId();
        $this->packageHelper->saveQuotePackages($addressId, $shipmentArray);
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
     * Refresh saved carrier methods
     *
     * @return mixed
     */
    public function refreshCarriers()
    {
        $allowedMethods = $this->getAllShippingMethods();
        if (count($allowedMethods) == 0) {
            $this->shipperLogger->postDebug(
                'Shipperhq_Shipper',
                'Refresh carriers',
                'Allowed methods web service did not contain any shipping methods for carriers'
            );
            $result['result'] = false;
            $result['error'] = 'ShipperHQ Error: No shipping methods for carrier setup in your ShipperHQ account';
            return $result;
        }
        return $allowedMethods;
    }

    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getAllShippingMethods()
    {
        $this->carrierConfigHandler->saveConfig(
            \ShipperHQ\Shipper\Model\System\Message\Credentials::SHIPPERHQ_INVALID_CREDENTIALS_SUPPLIED,
            0
        );

        $result = [];
        $allowedMethods = [];

        $allMethodsRequest = $this->shipperMapper->getCredentialsTranslation();
        $requestString = serialize($allMethodsRequest);
        $resultSet = $this->carrierCache->getCachedQuotes($requestString, $this->getCarrierCode());
        $timeout = $this->restHelper->getWebserviceTimeout();
        if (!$resultSet) {
            $allowedMethodUrl = $this->restHelper->getAllowedMethodGatewayUrl();
            $resultSet = $this->shipperWSClientFactory->create()->sendAndReceive(
                $allMethodsRequest,
                $allowedMethodUrl,
                $timeout
            );
        }

        $allowedMethodResponse = $resultSet['result'];
        $debugData = $resultSet['debug'];
        $this->shipperLogger->postDebug('Shipperhq_Shipper', 'Allowed methods response', $debugData);

        if (!is_object($allowedMethodResponse)) {
            $this->shipperLogger->postInfo(
                'Shipperhq_Shipper',
                'Allowed Methods: No or invalid response received from Shipper HQ',
                $allowedMethodResponse
            );

            $shipperHQ = "<a href=https://shipperhq.com/ratesmgr/websites>ShipperHQ</a> ";
            $result['result'] = false;
            $result['error'] = 'ShipperHQ is not contactable, verify the details from the website configuration in '
                . $shipperHQ;
            return $result;
        } elseif (!empty($allowedMethodResponse->errors)) {
            $this->shipperLogger->postInfo(
                'Shipperhq_Shipper',
                'Allowed methods: response contained following errors',
                $allowedMethodResponse
            );
            $error = 'ShipperHQ Error: ';
            foreach ($allowedMethodResponse->errors as $anError) {
                if (isset($anError->internalErrorMessage)) {
                    $error .= ' ' . $anError->internalErrorMessage;
                } elseif (isset($anError->externalErrorMessage) && $anError->externalErrorMessage != '') {
                    $error .= ' ' . $anError->externalErrorMessage;
                }

                //SHQ16-1708
                if (isset($anError->errorCode) && $anError->errorCode == '3') {
                    $this->carrierConfigHandler->saveConfig(
                        \ShipperHQ\Shipper\Model\System\Message\Credentials::SHIPPERHQ_INVALID_CREDENTIALS_SUPPLIED,
                        1
                    );
                }
            }
            $result['result'] = false;
            $result['error'] = $error;
            return $result;
        } elseif (empty($allowedMethodResponse->carrierMethods)) {
            $this->shipperLogger->postInfo(
                'Shipperhq_Shipper',
                'Allowed methods web service did not return any carriers or shipping methods',
                $allowedMethodResponse
            );
            $result['result'] = false;
            $result['warning'] =
                'ShipperHQ Warning: No carriers setup, log in to ShipperHQ Dashboard and create carriers';
            return $result;
        }
        $this->carrierCache->setCachedQuotes($requestString, $resultSet, $this->getCarrierCode());

        $carrierConfig = $this->allowedMethodsHelper->extractAllowedMethodsAndCarrierConfig(
            $allowedMethodResponse,
            $allowedMethods
        );

        $this->shipperLogger->postDebug(
            'Shipperhq_Shipper',
            'Allowed methods parsed result ',
            $allowedMethods
        );
        // go set carrier titles
        $this->carrierConfigHandler->setCarrierConfig($carrierConfig);
        $this->saveAllowedMethods($allowedMethods);

        //SHQ18-112 persist Magento version to config to increase efficiency of future requests
        $this->carrierConfigHandler->saveConfig(
            'carriers/shipper/magento_version',
            $this->shipperMapper->getMagentoVersion()
        );
        return $allowedMethods;
    }

    public function saveAllowedMethods($allowedMethodsArray)
    {
        $carriersCodesString = $this->shipperDataHelper->encode($allowedMethodsArray);
        $this->carrierConfigHandler->saveConfig(
            $this->shipperDataHelper->getAllowedMethodsPath(),
            $carriersCodesString
        );
    }

    /**
     * Get allowed shipping methods
     * @param $requestedCode
     * @return array
     */
    public function getAllowedMethods()
    {
        return $this->getAllowedMethodsByCode();
    }

    /**
     * Get allowed shipping methods
     * @param $requestedCode
     * @return array
     */
    public function getAllowedMethodsByCode($requestedCode = null)
    {
        $arr = [];

        $allowedConfigValue = $this->shipperDataHelper->getConfigValue(
            $this->shipperDataHelper->getAllowedMethodsPath()
        );

        $allowed = $this->shipperDataHelper->decode($allowedConfigValue);
        if ($allowed === null) {
            $this->shipperLogger->postDebug(
                'Shipperhq_Shipper',
                'Allowed methods config is empty',
                'Please refresh your carriers by pressing Save button on the shipping method configuration screen '
                . 'from Stores > Configuration > Shipping Methods'
            );
            return $arr;
        }
        $arr = $this->allowedMethodsHelper->getAllowedMethodsArray($allowed, $requestedCode);

        if (count($arr) < 1 && $this->getConfigFlag(self::ACTIVE_FLAG)) {
            $this->shipperLogger->postDebug(
                'Shipperhq_Shipper',
                'There are no allowed methods for ' . $requestedCode,
                'If you expect to see shipping methods for this carrier, '
                . 'please refresh your carriers by pressing Save button on the shipping method configuration screen '
                . 'from Stores > Configuration > Shipping Methods'
            );
        }
        return $arr;
    }

    /**
     *  Retrieve sort order of current carrier
     *
     * @return mixed
     */
    public function getSortOrder()
    {
        $path = 'carriers/' . $this->getId() . '/sort_order';
        $value = $this->shipperDataHelper->getConfigValue($path);
        return $value;
    }

    /**
     * @param $ratesToAdd
     * @return \Magento\Shipping\Model\Rate\Result
     */
    public function createMergedRate($ratesToAdd)
    {
        $result = $this->rateFactory->create();
        foreach ($ratesToAdd as $rateToAdd) {
            $method = $this->rateMethodFactory->create();
            $method->setPrice((float)$rateToAdd['price']);
            $method->setCost((float)$rateToAdd['price']);
            $method->setCarrier($this->_code);
            $method->setCarrierTitle($rateToAdd['mergedTitle']);
            $method->setMethod($rateToAdd['title']);
            $method->setMethodTitle($rateToAdd['title']);
            $method->setMethodDescription($rateToAdd['mergedDescription']);
            $method->setCarrierType(__('multiple_shipments'));
            $result->append($method);
        }
        return $result;
    }
}
