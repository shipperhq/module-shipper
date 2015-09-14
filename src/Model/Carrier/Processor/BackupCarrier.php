<?php
/* ExtName
 *
 * User        karen
 * Date        9/13/15
 * Time        12:23 PM
 * @category   Webshopapps
 * @package    Webshopapps_ExtnName
 * @copyright   Copyright (c) 2015 Zowta Ltd (http://www.WebShopApps.com)
 *              Copyright, 2015, Zowta, LLC - US license
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */

namespace ShipperHQ\Shipper\Model\Carrier\Processor;


class BackupCarrier
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \ShipperHQ\Shipper\Helper\Data
     */
    private $shipperDataHelper;
    /**
     * @var \ShipperHQ\Shipper\Helper\Logger
     */
    private $shipperLogger;
    /**
     * @var \Magento\Framework\App\Config\MutableScopeConfigInterface
     */
    private $mutableConfig;

    /**
     * @param Config $configHelper
     *
     */
    public function __construct(
        \ShipperHQ\Shipper\Helper\Logger $shipperLogger,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\App\Config\MutableScopeConfigInterface $mutableConfig,
        \ShipperHQ\Shipper\Helper\Data $shipperDataHelper)
    {

        $this->shipperDataHelper = $shipperDataHelper;
        $this->storeManager = $context->getStoreManager();
        $this->shipperLogger = $shipperLogger;
        $this->mutableConfig = $mutableConfig;
    }

    public function getBackupCarrierRates($rawRequest, $backupCarrierDetails)
    {
        $carrierCode = $this->retrieveBackupCarrier($backupCarrierDetails);
        if (!$carrierCode) {
            return false;
        }

        $tempEnabledCarrier = $this->tempSetCarrierEnabled($carrierCode, true);
        $carrier = $this->shipperDataHelper->getCarrierByCode($carrierCode, $rawRequest->getStoreId());

        if (!$carrier) {
            $this->tempSetCarrierEnabled($carrierCode, false);
            $this->shipperLogger->postInfo('Shipperhq_Shipper', 'Unable to activate backup carrier', $carrierCode);
            return false;
        }

        $result = $carrier->collectRates($rawRequest);
        $this->shipperLogger->postInfo('Shipperhq_Shipper', 'Backup carrier result: ',
            $result);


        if ($tempEnabledCarrier) {
            $this->tempSetCarrierEnabled($carrierCode, false);
        }
        return $result;
    }

    /**
     * Enable or disable carrier
     * @return boolean
     */
    protected function tempSetCarrierEnabled($carrierCode, $enabled)
    {
        $carrierPath = 'carriers/' . $carrierCode . '/active';
        $tempEnabledCarrier = false;

        if (!$this->shipperDataHelper->getConfigFlag($carrierPath) || !$enabled) { // if $enabled set to false was previously enabled!
            $this->mutableConfig->setValue($carrierPath, $enabled);
            $tempEnabledCarrier = true;
        }

        return $tempEnabledCarrier;

    }

    /**
     * Get backup carrier if configured
     * @return mixed
     */
    protected function retrieveBackupCarrier($backupCarrierDetails)
    {
        $this->shipperLogger->postInfo('Shipperhq_Shipper', 'Unable to establish connection with ShipperHQ',
            'Attempting to use backup carrier: ' . $backupCarrierDetails);
        if (!$backupCarrierDetails) {
            $this->shipperLogger->postDebug('Shipperhq_Shipper', 'Backup carrier: ',
                'No backup carrier is configured');
            return false;
        }
        return $backupCarrierDetails;
    }


}