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
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
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
class RecordOrder extends AbstractRecordOrder implements ObserverInterface
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
     * @param Data                    $shipperDataHelper
     * @param CartRepositoryInterface $quoteRepository
     * @param LogAssist               $shipperLogger
     * @param OrderFactory            $orderFactory
     * @param Session                 $checkoutSession
     * @param Package                 $packageHelper
     * @param CarrierGroup            $carrierGroupHelper
     * @param ListingService          $listingService
     * @param ListingHelper           $listingHelper
     * @param PostOrder               $postOrder
     * @param Module                  $moduleHelper
     * @param Authorization           $authHelper
     */
    public function __construct(
        Data $shipperDataHelper,
        CartRepositoryInterface $quoteRepository,
        LogAssist $shipperLogger,
        OrderFactory $orderFactory,
        Session $checkoutSession,
        Package $packageHelper,
        CarrierGroup $carrierGroupHelper,
        ListingService $listingService,
        ListingHelper $listingHelper,
        PostOrder $postOrder,
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
            $postOrder,
            $moduleHelper,
            $authHelper
        );
    }

    /**
     * Record order shipping information after order is placed
     *
     * @param EventObserver $observer
     * @return void
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(EventObserver $observer)
    {
        if ($this->shipperDataHelper->getConfigValue('carriers/shipper/active')) {
            $order = $this->orderFactory->create()->loadByIncrementId(
                $this->checkoutSession->getLastRealOrderId()
            );
            if ($order->getIncrementId()) {
                $this->recordOrder($order);
                //SHQ16-1967 reset all checkout data
                $this->checkoutSession->setShipperhqData([]);
                $this->checkoutSession->setShipperHQPackages('');
            }
        }
    }
}
