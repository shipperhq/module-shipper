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

namespace ShipperHQ\Shipper\Plugin\Shipping;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Shipping\Model\Rate\Result;
use Magento\Store\Model\ScopeInterface;
use ShipperHQ\Shipper\Helper\LogAssist;
use ShipperHQ\Shipper\Model\Carrier\Processor\BackupCarrier;

class ShippingPlugin
{
    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @var LogAssist
     */
    private $shipperLogger;

    /**
     * @var BackupCarrier
     */
    private $backupCarrier;

    /**
     * ShippingPlugin constructor.
     * @param ScopeConfigInterface $config
     * @param LogAssist $shipperLogger ;
     * @param BackupCarrier $backupCarrier
     */
    public function __construct(ScopeConfigInterface $config, LogAssist $shipperLogger, BackupCarrier $backupCarrier)
    {
        $this->config = $config;
        $this->shipperLogger = $shipperLogger;
        $this->backupCarrier = $backupCarrier;
    }

    /**
     * Return array of carriers.
     * If $isActiveOnlyFlag is set to true, will return only active carriers
     *
     * @param \Magento\Shipping\Model\Shipping $subject
     * @param \Closure $proceed
     * @param string $carrierCode
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     * @return \Magento\Shipping\Model\Shipping|mixed
     */
    public function aroundCollectCarrierRates(
        \Magento\Shipping\Model\Shipping $subject,
        \Closure $proceed,
        $carrierCode,
        \Magento\Quote\Model\Quote\Address\RateRequest $request
    ) {
        $limitCarrier = $request->getLimitCarrier();
        $path = 'carriers/' . $carrierCode . '/model';
        $carrierModel = $this->config->getValue($path, ScopeInterface::SCOPE_STORES);

        if ($limitCarrier === null &&
            $carrierModel == 'ShipperHQ\Shipper\Model\Carrier\Shipper' &&
            $carrierCode !== 'shipper'
        ) {
            return $subject;
        }

        if ($this->isUsingBackupRates($carrierCode, $limitCarrier)) {
            $this->shipperLogger->postInfo(
                'Shipperhq_Shipper',
                'Checkout rated with backup carrier, continuing with backup rates',
                'Attempting to use backup carrier: ' . $limitCarrier[0]
            );
            /** @var Result $backupRates */
            $backupRates = $this->backupCarrier->getBackupCarrierRates(
                $request,
                $this->config->getValue('carriers/shipper/backup_carrier', ScopeInterface::SCOPE_STORES)
            );
            if ($backupRates) {
                $subject->getResult()->append($backupRates);
                return $subject;
            } else {
                $this->shipperLogger->postWarning(
                    'Shipperhq_Shipper',
                    'Failed to fetch backup rates',
                    'Attempting to use backup carrier: ' . $limitCarrier[0]
                );
            }
        }
        $result = $proceed($carrierCode, $request);
        return $result;
    }

    /**
     * When fetching rates for the checkout page magento will not set the $limitCarrier.  However when proceeding to
     * payment or place order it will limitCarrier to the carrier that the user selected.
     *
     * When initial rates were offered via backup carrier, then all other rate requests for that cart session should
     * continue to use the backup carrier. This method offers a fairly accurate way to detect if backup rates are
     * required.
     *
     * If SHQ is active, but the backup carrier is being used to get rates we can infer backup rates were triggered on
     * the checkout.  Further if the backup carrier is inactive, that implies the carrier is not in normal use but is
     * intended as a backup only carrier.
     *
     * @param string $carrierCode
     * @param string|null $limitCarrier
     * @return bool
     */
    private function isUsingBackupRates($carrierCode, $limitCarrier)
    {
        if (!$limitCarrier || !$limitCarrier === $carrierCode) {
            return false;
        }

        $isSHQActive = $this->config->isSetFlag('carriers/shipper/active', ScopeInterface::SCOPE_STORES);
        $backupCarrierValue = $this->config->getValue('carriers/shipper/backup_carrier', ScopeInterface::SCOPE_STORES);
        $backupCarrierActive = $this->config->isSetFlag("carriers/$backupCarrierValue/active", ScopeInterface::SCOPE_STORES);

        return $isSHQActive
            && $backupCarrierValue
            && $backupCarrierValue === $limitCarrier
            && !$backupCarrierActive;
    }
}
