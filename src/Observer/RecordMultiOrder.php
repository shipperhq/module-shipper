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

namespace ShipperHQ\Shipper\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use ShipperHQ\Shipper\Helper\Listing as ListingHelper;

/**
 * ShipperHQ Shipper module observer
 */
class RecordMultiOrder extends AbstractRecordOrder implements ObserverInterface
{

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    private $orderFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @param \Magento\Sales\Model\OrderFactory          $orderFactory
     * @param \ShipperHQ\Shipper\Helper\Data             $shipperDataHelper
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \ShipperHQ\Shipper\Helper\LogAssist        $shipperLogger
     * @param \ShipperHQ\Shipper\Helper\Package          $packageHelper
     * @param \ShipperHQ\Shipper\Helper\CarrierGroup     $carrierGroupHelper
     * @param \Magento\Checkout\Model\Session            $checkoutSession
     */
    public function __construct(
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \ShipperHQ\Shipper\Helper\Data $shipperDataHelper,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger,
        \ShipperHQ\Shipper\Helper\Package $packageHelper,
        \ShipperHQ\Shipper\Helper\CarrierGroup $carrierGroupHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \ShipperHQ\Shipper\Model\Listing\ListingService $listingService,
        ListingHelper $listingHelper
    ) {

        $this->orderFactory = $orderFactory;
        $this->checkoutSession = $checkoutSession;
        parent::__construct($shipperDataHelper, $quoteRepository, $shipperLogger, $packageHelper, $carrierGroupHelper, $listingService, $listingHelper);
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
            $orderIds = $observer->getEvent()->getOrderIds();
            if (empty($orderIds) || !is_array($orderIds)) {
                return;
            }
            foreach ($orderIds as $orderId) {
                $order = $this->orderFactory->create()->load($orderId);
                if ($order->getIncrementId()) {
                    $this->recordOrder($order);
                }
            }

            $this->checkoutSession->setShipperHQPackages('');
        }
    }
}
