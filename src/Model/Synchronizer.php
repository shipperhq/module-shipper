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

namespace ShipperHQ\Shipper\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Model\AbstractModel;
use ShipperHQ\WS\Client;
use \ShipperHQ\Shipper\Model\ResourceModel\SynchronizeFactory as SynchronizeResourceModelFactory;

class Synchronizer extends AbstractModel
{
    /**
     * Attribute add
     *
     * @var string
     */
    const ADD_ATTRIBUTE_OPTION = 'Add';

    /**
     * Attribute manual delete
     *
     * @var string
     */
    const REMOVE_ATTRIBUTE_OPTION = 'Manual delete required';

    /**
     * Attribute delete
     *
     * @var string
     */
    const AUTO_REMOVE_ATTRIBUTE_OPTION = 'Delete';
    /**
     * Config setting for features enabled
     *
     * @var string
     */
    const FEATURES_ENABLED_CONFIG = 'carriers/shipper/features_enabled';

    const MODULES_MISSING = 'carriers/shipper/modules_missing';

    /**
     * @var \ShipperHQ\Shipper\Helper\Data
     */
    private $shipperDataHelper;

    /**
     * @var \ShipperHQ\Shipper\Helper\Rest
     */
    private $restHelper;

    /**
     * @var \ShipperHQ\Shipper\Helper\LogAssist
     */
    private $shipperLogger;

    /**
     * @var Carrier\Processor\ShipperMapper
     */
    private $shipperMapper;

    /**
     * @var Carrier\Processor\CarrierConfigHandler
     */
    private $carrierConfigHandler;

    /**
     * @var Client\WebServiceClientFactory
     */
    private $shipperWSClientFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\OptionManagement
     */
    private $attributeOptionManagement;

    /**
     * @var \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory
     */
    private $optionDataFactory;
    /**
     * @var SynchronizeFactory
     */
    private $synchronizeFactory;
    /**
     * @var SynchronizeResourceModelFactory
     */
    private $synchronizeResourceModelFactory;

    /**
     * Database connection
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    /**
     * @var \ShipperHQ\Shipper\Helper\Module
     */
    private $moduleHelper;

    /**
     * @param \ShipperHQ\Shipper\Helper\Data $shipperDataHelper
     * @param \ShipperHQ\Shipper\Helper\Rest $restHelper
     * @param \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger
     * @param Carrier\Processor\ShipperMapper $shipperMapper
     * @param Carrier\Processor\CarrierConfigHandler $carrierConfigHandler
     * @param Client\WebServiceClientFactory $shipperWSClientFactory
     * @param \Magento\Catalog\Model\Product\Attribute\OptionManagement $attributeOptionManagement
     * @param \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory $optionDataFactory ,
     * @param SynchronizeFactory $synchronizeFactory
     * @param SynchronizeResourceModelFactory $synchronizeResourceModelFactory
     * @param \ShipperHQ\Shipper\Helper\Module $moduleHelper
     *
     */
    public function __construct(
        \ShipperHQ\Shipper\Helper\Data $shipperDataHelper,
        \ShipperHQ\Shipper\Helper\Rest $restHelper,
        \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger,
        Carrier\Processor\ShipperMapper $shipperMapper,
        Carrier\Processor\CarrierConfigHandler $carrierConfigHandler,
        \ShipperHQ\WS\Client\WebServiceClientFactory $shipperWSClientFactory,
        \Magento\Catalog\Model\Product\Attribute\OptionManagement $attributeOptionManagement,
        \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory $optionDataFactory,
        SynchronizeFactory $synchronizeFactory,
        SynchronizeResourceModelFactory $synchronizeResourceModelFactory,
        ResourceConnection $resource,
        \ShipperHQ\Shipper\Helper\Module $moduleHelper
    ) {

        $this->shipperDataHelper = $shipperDataHelper;
        $this->restHelper = $restHelper;
        $this->shipperLogger = $shipperLogger;
        $this->shipperMapper = $shipperMapper;
        $this->shipperWSClientFactory = $shipperWSClientFactory;
        $this->carrierConfigHandler = $carrierConfigHandler;
        $this->attributeOptionManagement = $attributeOptionManagement;
        $this->optionDataFactory = $optionDataFactory;
        $this->synchronizeFactory = $synchronizeFactory;
        $this->synchronizeResourceModelFactory = $synchronizeResourceModelFactory;
        $this->connection = $resource->getConnection();
        $this->moduleHelper = $moduleHelper;
    }

    /*
     *Review latest attribute data and save changes required to database
     */
    public function updateSynchronizeData()
    {
        $latestAttributes = $this->getLatestAttributeData();
        $result = [];
        if ($latestAttributes && array_key_exists('error', $latestAttributes)) {
            $result['error'] = $latestAttributes['error'];
        } elseif ($latestAttributes && !empty($latestAttributes)) {
            $updateData = $this->compareAttributeData($latestAttributes);
            $result['result'] = $this->saveSynchData($updateData);
        } else {
            $result['error']
                = 'ShipperHQ is not responding, please check your settings to ensure they are correct';
        }

        return $result;
    }

    /**
     * Get latest attribute data and perform changes required
     *
     * MNB-358 Modified to sync shipping groups and boxes from all API keys present inc. from other scopes
     *
     * @return array
     */
    private function getLatestAttributeData()
    {
        $result = [];

        if (!$this->shipperDataHelper->getCredentialsEntered()) {
            $result['error'] = 'Missing API key or Authentication key. Can\'t connect to ShipperHQ';

            return $result;
        }

        $synchronizeUrl = $this->restHelper->getAttributeGatewayUrl();
        $credentialsPerStore = $this->shipperMapper->getAllCredentialsTranslation();

        $resultSetArray = [];

        $attributesToSyncFromAllApiKeys = [
            'shipperhq_warehouse',
            'shipperhq_poss_boxes',
            'shipperhq_shipping_group',
            'shipperhq_dim_group',
            'shipperhq_master_boxes'
        ];

        $attributesFromAllApiKeys = [];

        foreach ($credentialsPerStore as $credentials) {
            $resultSetArray[] = $this->send($synchronizeUrl, $credentials);
        }

        if (count($resultSetArray) > 0) {
            foreach ($resultSetArray as $key => $resultSet) {
                $resultObject = $resultSet['result'];
                if (!$this->validateAllAtrributesResponse($resultObject)) {
                    continue;
                }
                $attributes = $resultObject->attributeTypes;
                foreach ($attributes as $attribute) {
                    if (in_array($attribute->code, $attributesToSyncFromAllApiKeys)) {
                        $this->extractAttribute($attribute, $attributesFromAllApiKeys);
                    }
                }
            }
            //Use as master result set. Will be from default configuration scope
            $allAttributesResponse = $resultSetArray[0]['result'];

            /*
             * Merge in the origins from all API keys present in M2 configuration
             */
            if ($this->validateAllAtrributesResponse($allAttributesResponse)) {
                $this->mergeAttributesToMasterResponse($allAttributesResponse, $attributesFromAllApiKeys);

                $result = $allAttributesResponse->attributeTypes;
            }
        }

        return $result;
    }

    /**
     * Extracts attributes for a single API key and saves them to an array that holds attribute data for all API keys present
     *
     * @param $apiKeysAttributes
     * @param $allApiKeyAttributes
     */
    private function extractAttribute($apiKeysAttributes, &$allApiKeyAttributes)
    {
        $attributeObjArr = $apiKeysAttributes->attributes;
        $attributeCode = $apiKeysAttributes->code;
        $allApiKeyAttributeValues = array_key_exists($attributeCode, $allApiKeyAttributes) ? $allApiKeyAttributes[$attributeCode] : [];

        foreach ($attributeObjArr as $attribute) {
            if (!array_key_exists($attribute->name, $allApiKeyAttributeValues)) {
                $allApiKeyAttributeValues[$attribute->name] = $attribute;
            }
        }

        if (count($allApiKeyAttributeValues) > 0) {
            $allApiKeyAttributes[$attributeCode] = $allApiKeyAttributeValues;
        }
    }

    /**
     * Merges in the array of attributes matching the attributeCode from all found API keys
     *
     * @param $masterResponse
     * @param $allWebsiteAttributes
     * @param $attributeCode
     */
    private function mergeAttributesToMasterResponse(&$masterResponse, $allWebsiteAttributes)
    {
        $attributes = $masterResponse->attributeTypes;

        foreach ($attributes as $attribute) {
            if (array_key_exists($attribute->code, $allWebsiteAttributes)) {
                $attribute->attributes = $allWebsiteAttributes[$attribute->code];
            }
        }
    }

    /**
     * Validates the attribute response is valid and contains data TODO: log store name as this is now potenitally per store
     *
     * @param $response
     *
     * @return bool
     */
    private function validateAllAtrributesResponse($response)
    {
        $validResponse = false;

        $this->shipperLogger->postDebug(
            'Shipperhq_Shipper',
            'Latest attributes response',
            (array)$response
        );

        if (!is_object($response)) {
            $this->shipperLogger->postInfo(
                'Shipperhq_Shipper',
                'Retrieving attributes: No or invalid response received from Shipper HQ',
                $response
            );
        } elseif (isset($response->errors) && !empty($response->errors)) {
            foreach ($response->errors as $errorDetails) {
                $errorDetails = (array)$errorDetails;
                if (array_key_exists('internalErrorMessage', $errorDetails)
                    && $errorDetails['internalErrorMessage'] != ''
                ) {
                    $result['error'] = $errorDetails['internalErrorMessage'];
                } elseif (array_key_exists('externalErrorMessage', $errorDetails)
                    && $errorDetails['externalErrorMessage'] != ''
                ) {
                    $result['error'] = $errorDetails['externalErrorMessage'];
                }
            }
            $this->shipperLogger->postInfo(
                'Shipperhq_Shipper',
                'Shipper HQ returned error',
                $response->errors
            );
        } elseif (!$response || !isset($response->responseSummary) ||
            (string)$response->responseSummary->status != 1 ||
            !$response->attributeTypes) {
            $this->shipperLogger->postInfo(
                'Shipperhq_Shipper',
                'Unable to parse latest attributes response : ',
                $response
            );
        } else {
            $validResponse = true;
        }

        return $validResponse;
    }

    private function send($url, $request = null)
    {
        $timeout = $this->restHelper->getWebserviceTimeout();
        if ($request === null) {
            $request = $this->shipperMapper->getCredentialsTranslation();
        }
        $result = $this->shipperWSClientFactory->create()->sendAndReceive($request, $url, $timeout);
        $this->shipperLogger->postInfo('Shipperhq_Shipper', 'Synch request and response', $result['debug']);
        return $result;
    }

    private function compareAttributeData($latestAttributes)
    {
        $result = [];
        $productAttributes = $this->shipperDataHelper->getProductAttributes();

        foreach ($latestAttributes as $attribute) {
            switch ($attribute->type) {
                case 'product':
                    try {
                        $existingAttributeOptions = [];
                        if (!in_array($attribute->code, $productAttributes)) {
                            $this->shipperLogger->postDebug(
                                'Shipperhq_Shipper',
                                'Attribute ' . $attribute->code . ' does not exist.',
                                ''
                            );
                            break;
                        }
                        $existingAttributeInfo = $this->attributeOptionManagement->getItems($attribute->code);
                        if (is_array($existingAttributeInfo)) {
                            $existingAttributeOptions = $existingAttributeInfo;
                        }
                    } catch (\Exception $e) {
                        $e->getMessage();
                        $this->shipperLogger->postDebug(
                            'Shipperhq_Shipper',
                            'Unable to find attribute ' . $attribute->code,
                            'Error: ' . $e->getMessage()
                        );
                        $result = false;
                        break;
                    }
                    $trackValues = $existingAttributeOptions;
                    foreach ($attribute->attributes as $latestValue) {
                        $found = false;
                        foreach ($existingAttributeOptions as $key => $option) {
                            if ($option->getLabel() == $latestValue->name) {
                                $found = true;
                                unset($trackValues[$key]);
                                break;
                            }
                        }
                        if (!$found) {
                            $result[] = [
                                'attribute_type' => $attribute->type,
                                'attribute_code' => $attribute->code,
                                'value' => $latestValue->name,
                                'status' => self::ADD_ATTRIBUTE_OPTION,
                                'date_added' => date('Y-m-d H:i:s')

                            ];
                        }
                    }
                    if (!empty($trackValues)) {
                        // add store selector in here
                        $storeId = '';
                        foreach ($trackValues as $key => $option) {
                            if (ctype_space($option->getLabel()) || $option->getLabel() == '') {
                                unset($trackValues[$key]);
                                continue;
                            }
                            $isAssigned = $this->getIsAttributeValueUsed(
                                $attribute->code,
                                $option->getValue(),
                                $storeId,
                                true
                            );
                            $deleteFlag = self::AUTO_REMOVE_ATTRIBUTE_OPTION;
                            if ($isAssigned) {
                                $deleteFlag = self::REMOVE_ATTRIBUTE_OPTION;
                            }

                            $result[] = [
                                'attribute_type' => $attribute->type,
                                'attribute_code' => $attribute->code,
                                'value' => $option->getLabel(),
                                'option_id' => $option->getValue(),
                                'status' => $deleteFlag,
                                'date_added' => date('Y-m-d H:i:s')
                            ];
                        }
                    }
                    break;
                case 'global':
                    if ($attribute->code == 'global_settings') {
                        foreach ($attribute->attributes as $globalSetting) {
                            $value = $globalSetting->value == 'true' ? 1 : 0;
                            $configValue = $this->shipperDataHelper->getDefaultConfigValue(
                                'carriers/shipper/' . $globalSetting->code
                            );
                            if ($configValue != $value || ($configValue === null)) {
                                $result[] = [
                                    'attribute_type' => 'global_setting',
                                    'attribute_code' => $globalSetting->code,
                                    'value' => $value,
                                    'option_id' => '',
                                    'status' => self::ADD_ATTRIBUTE_OPTION,
                                    'date_added' => date('Y-m-d H:i:s')
                                ];
                            }
                        }
                    }
                    break;
                case 'customer':
                    //compare customer groups
                    break;
                case 'feature':
                    $configValue = $this->shipperDataHelper->getConfigValue(self::FEATURES_ENABLED_CONFIG);
                    $neededFeatures = [];
                    foreach ($attribute->attributes as $featureEnabled) {
                        $neededFeatures[] = $featureEnabled->code;
                    }
                    $neededFeaturesStr = strtolower(implode('|', $neededFeatures));
                    if ($neededFeaturesStr != $configValue) {
                        $result[] = [
                            'attribute_type' => 'feature',
                            'attribute_code' => 'ShipperHQ Features',
                            'value' => "Needs refresh",
                            'option_id' => $neededFeaturesStr,
                            'status' => self::ADD_ATTRIBUTE_OPTION,
                            'date_added' => date('Y-m-d H:i:s')
                        ];
                    }
                    break;
                case 'carrier':
                    if ($attribute->code == 'carrier_listing_type') {
                        // If any carrier is AUTO then treat all as auto, otherwise use manual if manual was found
                        $value = 'NONE';
                        foreach ($attribute->attributes as $carrierSetting) {
                            if ($carrierSetting->createListing == "AUTO") {
                                $value = "AUTO";
                                break;
                            } elseif ($carrierSetting->createListing == "MANUAL") {
                                $value = "MANUAL";
                            }
                        }
                        $configValue = $this->shipperDataHelper->getDefaultConfigValue(
                            'carriers/shipper/create_listing'
                        );
                        if ($configValue != $value) {
                            $result[] = [
                                'attribute_type' => 'carrier',
                                'attribute_code' => 'carriers/shipper/create_listing',
                                'value' => $value,
                                'option_id' => '',
                                'status' => self::ADD_ATTRIBUTE_OPTION,
                                'date_added' => date('Y-m-d H:i:s')
                            ];
                        }
                    }
                    break;
                default:
                    break;
            }
        }
        $this->shipperLogger->postDebug('Shipperhq_Shipper', 'Compare attributes result: ', $result);
        return $result;
    }

    private function getIsAttributeValueUsed($attribute_code, $value, $storeId, $isSelect = false)
    {
        $attributeModel = $this->shipperDataHelper->getAttribute($attribute_code, $storeId);

        $select = $this->connection->select()->distinct(
            true
        )->from(
            $attributeModel->getBackend()->getTable(),
            ['value']
        )->where(
            'attribute_id=?',
            $attributeModel->getId()
        )->where(
            'value!=?',
            ''
        );

        $usedAttributeValues = $this->connection->fetchCol($select);

        if ($isSelect) {
            //account for multiselect values
            $separated = [];
            foreach ($usedAttributeValues as $key => $aValue) {
                if (strstr((string) $aValue, ',')) {
                    $values = explode(',', (string) $aValue);
                    $separated = array_merge($separated, $values);
                    unset($usedAttributeValues[$key]);
                }
            }
            $usedAttributeValues = array_merge($usedAttributeValues, $separated);
        }
        return in_array($value, $usedAttributeValues);
    }

    private function saveSynchData($data)
    {
        $result = 0;
        try {
            $this->synchronizeResourceModelFactory->create()->deleteAllSynchData();

        } catch (\Exception $e) {
            $result = false;
            $this->shipperLogger->postDebug(
                'Shipperhq_Shipper',
                'Unable to remove existing attribute update data',
                $e->getMessage()
            );
        }
        if (empty($data)) {
            $this->shipperLogger->postDebug(
                'Shipperhq_Shipper',
                'Saving synch data',
                'No attribute changes required, 0 rows saved'
            );
            return $result;
        }

        $synchronizeRM = $this->synchronizeResourceModelFactory->create();
        foreach ($data as $update) {
            $synchronize = $this->synchronizeFactory->create();
            $synchronize->setData($update);
            $synchronizeRM->save($synchronize);
            $result++;
        }
        return $result;
    }

    public function synchronizeData()
    {
        $latestAttributes = $this->getLatestAttributeData();
        $result = [];
        if ($latestAttributes && array_key_exists('error', $latestAttributes)) {
            $result['error'] = $latestAttributes['error'];
        } elseif ($latestAttributes && !empty($latestAttributes)) {
            $updateData = $this->compareAttributeData($latestAttributes);
            $updateResult = $this->updateAll($updateData);
            $result['result'] = $updateResult;
        } else {
            $result['error']
                = 'ShipperHQ is not responding, please check your settings to ensure they are correct';
        }

        return $result;
    }

    /*
     * Add new option values to attributes
     *
     */

    private function updateAll($updateData)
    {
        $result = 0;

        foreach ($updateData as $attributeUpdate) {
            if ($attributeUpdate['attribute_type'] == 'product') {
                if ($attributeUpdate['status'] == self::ADD_ATTRIBUTE_OPTION) {
                    $optionToAdd = $this->optionDataFactory->create();
                    $optionToAdd->setLabel($attributeUpdate['value'])
                        ->setSortOrder(0)
                        ->setIsDefault(0);
                    try {
                        $this->attributeOptionManagement->add($attributeUpdate['attribute_code'], $optionToAdd);
                        $result++;
                    } catch (\Exception $e) {
                        $this->shipperLogger->postInfo(
                            'Shipperhq_Shipper',
                            'Unable to add attribute option',
                            'Error: ' . $e->getMessage()
                        );
                        $result = false;
                    }
                } elseif ($attributeUpdate['status'] == self::AUTO_REMOVE_ATTRIBUTE_OPTION) {
                    try {
                        $this->attributeOptionManagement->delete(
                            $attributeUpdate['attribute_code'],
                            $attributeUpdate['option_id']
                        );
                        $result++;
                    } catch (\Exception $e) {
                        $this->shipperLogger->postInfo(
                            'Shipperhq_Shipper',
                            'Unable to remove attribute option',
                            'Error: ' . $e->getMessage()
                        );
                        $result = false;
                    }
                }
            } elseif ($attributeUpdate['attribute_type'] == 'global_setting') {
                $this->carrierConfigHandler->saveConfig(
                    'carriers/shipper/' . $attributeUpdate['attribute_code'],
                    $attributeUpdate['value'],
                    'default',
                    0,
                    true
                );
                $result++;
            } elseif ($attributeUpdate['attribute_type'] == 'feature') {
                if ($attributeUpdate['status'] == self::ADD_ATTRIBUTE_OPTION) {
                    $this->carrierConfigHandler->saveConfig(
                        self::FEATURES_ENABLED_CONFIG,
                        $attributeUpdate["option_id"],
                        'default',
                        0,
                        true
                    );
                    $result++;
                }
            } elseif ($attributeUpdate['attribute_type'] == 'carrier') {
                if ($attributeUpdate['status'] == self::ADD_ATTRIBUTE_OPTION) {
                    $this->carrierConfigHandler->saveConfig(
                        $attributeUpdate['attribute_code'],
                        $attributeUpdate['value'],
                        'default',
                        0,
                        true
                    );
                    $result++;
                }
            }
        }
        $configValue = $this->shipperDataHelper->getConfigValue(self::FEATURES_ENABLED_CONFIG);
        $missingModules = $this->moduleHelper->checkForMissingModules($configValue);
        $moduleAlert = '';
        if (!empty($missingModules)) {
            $moduleAlert = implode(', ', $missingModules);
            $this->shipperLogger->postWarning(
                'ShipperHQ Shipper',
                'IMPORTANT! You may be missing modules from your ShipperHQ installation.
                This could cause missing features or unexpected behaviour. Modules missing could include: ',
                $missingModules
            );
        }
        //check for missing modules
        $this->carrierConfigHandler->saveConfig(
            self::MODULES_MISSING,
            $moduleAlert,
            'default',
            0,
            true
        );

        $this->carrierConfigHandler->refreshConfig();

        if ($result >= 0) {
            $this->checkSynchStatus(true);
        }
        return $result;
    }

    public function checkSynchStatus($saveTime = false)
    {
        if ($this->shipperDataHelper->getConfigValue('carriers/shipper/active')) {
            $synchCheckUrl = $this->restHelper->getCheckSynchronizedUrl();
            $result = $this->send($synchCheckUrl);
            $synchResult = $result['result'];
            $debugData = [
                'result' => json_decode((string) $result['debug']['response']),
                'url' => $result['debug']['url']
            ];
            $this->shipperLogger->postDebug('Shipperhq_Shipper', 'Check synchronized status', $debugData);

            if (!empty($synchResult->errors)) {
                $this->shipperLogger->postWarning(
                    'Shipperhq_Shipper',
                    'Check synchronized status failed. Error: ',
                    $synchResult->errors
                );
                return false;
            }

            if (!isset($synchResult->responseSummary) || $synchResult->responseSummary->status != 1) {
                $this->shipperLogger->postWarning(
                    'Shipperhq_Shipper',
                    'Check Synchronized Status failed with no error. ',
                    $synchResult
                );
                return false;
            }
            $currentVal = $this->shipperDataHelper->getConfigValue($this->shipperDataHelper->getLastSyncPath());
            $latestSync = $synchResult->lastSynchronization;
            $result = $latestSync == $currentVal ? '1' : "Required";
            if ($saveTime) {
                $this->carrierConfigHandler->saveConfig(
                    $this->shipperDataHelper->getLastSyncPath(),
                    $latestSync,
                    'default',
                    0,
                    false
                );
            }
            return $result;
        }
    }
}
