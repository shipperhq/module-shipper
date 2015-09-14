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

namespace ShipperHQ\Shipper\Model;

class Observer
{

    /**
     * @var \ShipperHQ\Shipper\Helper\Data
     */
    protected $shipperDataHelper;
    /**
     * @var Carrier\Shipper
     */
    private $shipper;
    /**
     * @var \Magento\Backend\Model\Session
     */
    private $backendSession;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    function __construct(\ShipperHQ\Shipper\Helper\Data $shipperDataHelper,
                         \Magento\Backend\Model\Session $backendSession,
                         \Psr\Log\LoggerInterface $logger,
                         \ShipperHQ\Shipper\Model\Carrier\Shipper $shipper
    )
    {

        $this->shipperDataHelper = $shipperDataHelper;
        $this->shipper = $shipper;
        $this->backendSession = $backendSession;
        $this->logger = $logger;
    }


    /*
     * Refresh carriers in configuration pane
     *
     */
    public function updateTitles()
    {
        if ($this->shipperDataHelper->getConfigValue('carriers/shipper/active')) {
            $refreshResult = $this->shipper->refreshCarriers();
            if (array_key_exists('error', $refreshResult)) {
                $message = $refreshResult['error'];
                $this->backendSession->addError($message);
            } else {
                $message = __('%s shipping methods have been updated from ShipperHQ', count($refreshResult));
                $this->backendSession->addSuccess($message);
            }
        }
    }

    /**
     * Remove flag for checkout on quote address
     *
     * @param object $observer
     */
    public function onCheckoutCartEstimatePost($observer)
    {
        $quote = $observer->getQuote();
        $quote->setIsMultiShipping(false);
        $shipping = $quote->getShippingAddress();
        $shipping->setIsCheckout(0);
        $shipping->save();
    }

    /**
     * Set flag for checkout on quote address
     *
     * @param object $observer
     */
    public function onCheckoutSaveBilling($observer)
    {
        $quote = $observer->getQuote();
        $shipping = $quote->getShippingAddress();
        $shipping->setIsCheckout(1);
        $billing = $quote->getBillingAddress();
        $billing->setIsCheckout(1);
    }

    public function multiCheckoutShippingPredispatch($observer)
    {
        $quote = $observer->getQuote();
        $addresses = $quote->getAllAddresses();
        foreach ($addresses as $address) {
            $address->setIsCheckout(1);
        }
    }


    public function saveOrderAfter($observer)
    {
        try {
            $recordOrderPackages = true;

            if ($recordOrderPackages) {
                $order = $observer->getOrder();
                $quote = $order->getQuote();

                $shippingAddress = $quote->getShippingAddress();
                $carrierGroupDetail = json_decode($shippingAddress->getCarriergroupShippingDetails());
                if (is_array($carrierGroupDetail)) {
                    foreach ($carrierGroupDetail as $carrier_group) {
                        if (!isset($carrier_group->carrierGroupId)) {
                            continue;
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $this->logger->error($e);
        }
    }


    public function salesConvertQuoteItemToOrderItem($observer)
    {
        try {
            if (!$this->shipperDataHelper->getConfigValue('carriers/shipper/active')) {
                return;
            }
            $quoteItem = $observer->getEvent()->getItem();
            $orderItem = $observer->getEvent()->getOrderItem();
            $carriergroupId = $quoteItem->getCarriergroupId();

            $orderItem->setCarriergroupId($carriergroupId);
            $orderItem->setCarriergroup($quoteItem->getCarriergroup());
        } catch (Exception $e) {
            $this->logger->error($e);
        }

    }

    public function setCurrentQuoteObjectInAdminFromSaveData(Varien_Event_Observer $observer)
    {

        $quote = $observer->getOrderCreateModel()->getQuote();
        $shipping = $quote->getShippingAddress();
        $shipping->setIsCheckout(1);
        $billing = $quote->getBillingAddress();
        $billing->setIsCheckout(1);

        $request = $observer->getRequestModel();
        if ($request->getActionName() === 'save') {
            $orderData = $request->getPost('order');

            if (isset($orderData['shipping_address'])) {
                unset($orderData['shipping_address']);
            }

            if (isset($orderData['billing_address'])) {
                unset($orderData['billing_address']);
            }

            if (isset($orderData['shipping_method'])) {
                unset($orderData['shipping_method']);
            }

            $request->setPost('order', $orderData);
            $request->setPost('shipping_as_billing', 0);
        }

        $this->shipperDataHelper->setQuote($observer->getOrderCreateModel()->getQuote());
    }
}
