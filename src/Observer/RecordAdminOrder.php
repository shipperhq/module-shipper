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
 * ShipperHQ Shipping
 *
 * @category ShipperHQ
 * @package ShipperHQ\Shipper
 * @copyright Copyright (c) 2015 Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace ShipperHQ\Shipper\Observer;

use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Model\OrderFactory;
use ShipperHQ\Shipper\Helper\Authorization;
use ShipperHQ\Shipper\Helper\CarrierGroup;
use ShipperHQ\Shipper\Helper\Data;
use ShipperHQ\Shipper\Helper\Listing as ListingHelper;
use ShipperHQ\Shipper\Helper\LogAssist;
use ShipperHQ\Shipper\Helper\Module;
use ShipperHQ\Shipper\Helper\Package;
use ShipperHQ\Shipper\Helper\PostOrder;
use ShipperHQ\Shipper\Model\Listing\ListingService;

/**
 * ShipperHQ Shipper module observer
 */
class RecordAdminOrder extends AbstractRecordOrder implements ObserverInterface
{
    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @param OrderFactory            $orderFactory
     * @param Data                    $shipperDataHelper
     * @param CartRepositoryInterface $quoteRepository
     * @param LogAssist               $shipperLogger
     * @param Package                 $packageHelper
     * @param CarrierGroup            $carrierGroupHelper
     * @param Session                 $checkoutSession
     * @param ListingService          $listingService
     * @param ListingHelper           $listingHelper
     * @param PostOrder               $postOrderHelper
     * @param Module                  $moduleHelper
     * @param Authorization           $authHelper
     */
    public function __construct(
        OrderFactory $orderFactory,
        Data $shipperDataHelper,
        CartRepositoryInterface $quoteRepository,
        LogAssist $shipperLogger,
        Package $packageHelper,
        CarrierGroup $carrierGroupHelper,
        Session $checkoutSession,
        ListingService $listingService,
        ListingHelper $listingHelper,
        PostOrder $postOrderHelper,
        Module $moduleHelper,
        Authorization $authHelper
    ) {
        $this->orderFactory = $orderFactory;
        $this->checkoutSession = $checkoutSession;
        parent::__construct(
            $shipperDataHelper,
            $quoteRepository,
            $shipperLogger,
            $packageHelper,
            $carrierGroupHelper,
            $listingService,
            $listingHelper,
            $postOrderHelper,
            $moduleHelper,
            $authHelper
        );
    }
    /**
     * Record order shipping information after order is placed
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        if ($this->shipperDataHelper->getConfigValue('carriers/shipper/active')) {
            $order = $observer->getEvent()->getData('order');
            if ($order->getIncrementId()) {
                $this->recordOrder($order);
                $this->checkoutSession->setShipperHQPackages('');
            }
        }
    }
}
