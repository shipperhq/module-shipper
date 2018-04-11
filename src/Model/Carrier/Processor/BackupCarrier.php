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

use Magento\Store\Model\StoreManagerInterface;

class BackupCarrier
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \ShipperHQ\Shipper\Helper\Data
     */
    private $shipperDataHelper;
    /**
     * @var \ShipperHQ\Shipper\Helper\LogAssist
     */
    private $shipperLogger;
    /**
     * @var CarrierConfigHandler
     */
    private $carrierConfigHandler;

    /**
     * BackupCarrier constructor.
     * @param \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger
     * @param StoreManagerInterface $storeManager
     * @param \ShipperHQ\Shipper\Helper\Data $shipperDataHelper
     * @param CarrierConfigHandler
     */
    public function __construct(
        \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger,
        StoreManagerInterface $storeManager,
        \ShipperHQ\Shipper\Helper\Data $shipperDataHelper,
        CarrierConfigHandler $carrierConfigHandler
    ) {
        $this->shipperDataHelper = $shipperDataHelper;
        $this->storeManager = $storeManager;
        $this->shipperLogger = $shipperLogger;
        $this->carrierConfigHandler = $carrierConfigHandler;
    }

    public function getBackupCarrierRates($rawRequest, $backupCarrierDetails)
    {

        $carrierCode = $this->retrieveBackupCarrier($backupCarrierDetails);
        if (!$carrierCode) {
            return false;
        }
        $storeId = $rawRequest->getStoreId();
        $tempEnabledCarrier = $this->tempSetCarrierEnabled($carrierCode, true);

        $carrier = $this->shipperDataHelper->getCarrierByCode($carrierCode, $storeId);

        if (!$carrier) {
            $this->tempSetCarrierEnabled($carrierCode, false);
            $this->shipperLogger->postInfo('Shipperhq_Shipper', 'Unable to activate backup carrier', $carrierCode);
            return false;
        }

        $result = $carrier->collectRates($rawRequest);
        $this->shipperLogger->postInfo(
            'Shipperhq_Shipper',
            'Backup carrier result: ',
            'returned ' . count($result) . ' results'
        );

        if ($tempEnabledCarrier) {
            $this->tempSetCarrierEnabled($carrierCode, false);
        }
        return $result;
    }

    /**
     * Get backup carrier if configured
     * @return mixed
     */
    private function retrieveBackupCarrier($backupCarrierDetails)
    {
        $this->shipperLogger->postInfo(
            'Shipperhq_Shipper',
            'Unable to establish connection with ShipperHQ',
            'Attempting to use backup carrier: ' . $backupCarrierDetails
        );
        if (!$backupCarrierDetails) {
            $this->shipperLogger->postDebug(
                'Shipperhq_Shipper',
                'Backup carrier: ',
                'No backup carrier is configured'
            );
            return false;
        }
        return $backupCarrierDetails;
    }

    /**
     * Enable or disable carrier
     * @return boolean
     */
    private function tempSetCarrierEnabled($carrierCode, $enabled)
    {
        $carrierPath = 'carriers/' . $carrierCode . '/active';
        $tempEnabledCarrier = false;
        // if $enabled set to false was previously enabled!
        if (!$this->shipperDataHelper->getConfigFlag($carrierPath) || !$enabled) {
            $this->carrierConfigHandler->saveConfig($carrierPath, $enabled);
            $this->carrierConfigHandler->refreshConfig();
            $tempEnabledCarrier = true;
        }
        return $tempEnabledCarrier;
    }
}
