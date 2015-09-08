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

class Shipperhq_Shipper_Model_Observer extends Mage_Core_Model_Abstract
{
    /*
     * Refresh carriers in configuration pane
     *
     */
    public function updateTitles()
    {
        if(Mage::getStoreConfig('carriers/shipper/active')) {
            $refreshResult = Mage::getModel('shipperhq_shipper/carrier_shipper')->refreshCarriers();
            if (array_key_exists('error', $refreshResult)) {
                $session = Mage::getSingleton('Mage_Adminhtml_Model_Session');
                $message = $refreshResult['error'];
                $session->addError($message);
            } else {
                $session = Mage::getSingleton('Mage_Adminhtml_Model_Session');
                $message = Mage::helper('shipperhq_shipper')->__('%s shipping methods have been updated from ShipperHQ', count($refreshResult));
                $session->addSuccess($message);
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
        foreach($addresses as $address)
        {
            $address->setIsCheckout(1);
        }
    }


    public function saveOrderAfter($observer)
    {
        try
        {
            $recordOrderPackages = true;

            if ($recordOrderPackages)
            {
                $order = $observer->getOrder();
                $quote = $order->getQuote();

                $shippingAddress = $quote->getShippingAddress();
                $carrierGroupDetail = json_decode($shippingAddress->getCarriergroupShippingDetails());
                if(is_array($carrierGroupDetail)){
                    foreach($carrierGroupDetail as $carrier_group) {
                        if(!isset($carrier_group->carrierGroupId)) {
                            continue;
                        }
                    }
                }
            }
        }
        catch (Exception $e) {
            Mage::logException($e);
        }
    }



    public function salesConvertQuoteItemToOrderItem($observer)
    {
        try {
            if (!Mage::getStoreConfig('carriers/shipper/active')) {
                return;
            }
            $quoteItem = $observer->getEvent()->getItem();
            $orderItem = $observer->getEvent()->getOrderItem();
            $carriergroupId = $quoteItem->getCarriergroupId();

            $orderItem->setCarriergroupId($carriergroupId);
            $orderItem->setCarriergroup($quoteItem->getCarriergroup());
        } catch (Exception $e) {
            Mage::logException($e);
        }

    }

    protected function _getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    public function setCurrentQuoteObjectInAdmin(Varien_Event_Observer $observer)
    {
        Mage::helper('shipperhq_shipper')->setQuote(
            Mage::getSingleton('adminhtml/sales_order_create')->getQuote()
        );
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

        Mage::helper('shipperhq_shipper')->setQuote($observer->getOrderCreateModel()->getQuote());
    }

    /**
     * Loads storage data for quote if it was not loaded
     *
     * @param Varien_Event_Observer $observer
     */
    public function onQuoteAfterLoad(Varien_Event_Observer $observer)
    {
        $quote = $observer->getQuote();
        Mage::helper('shipperhq_shipper')->getQuoteStorage($quote);
    }

    /**
     * Saves storage data if quote is saved
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     * @throws Exception
     */
    public function onQuoteAfterSave(Varien_Event_Observer $observer)
    {
        $quote = $observer->getQuote();
        $storage = Mage::helper('shipperhq_shipper')->storageManager()->findByQuote($quote);
        $this->_saveStorageInstance($storage);
        return $this;
    }

    /**
     * Saves modified data objects on post dispatch,
     * if modifications has been done after quote has been saved
     *
     *
     */
    public function onPostDispatch()
    {
        /** @var Shipperhq_Shipper_Model_Storage[] $storageList */
        $storageList = Mage::helper('shipperhq_shipper')->storageManager()->getStorageObjects();
        foreach ($storageList as $storage) {
            if ($storage->hasDataChanges() && $storage->getId()) {
                $this->_saveStorageInstance($storage);
            }
        }
    }


}
