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

namespace ShipperHQ\Shipper\Plugin\Checkout;

use Magento\Framework\DataObject\Factory as DataObjectFactory;

class ShippingInformationPlugin
{
    /**
     * @var \ShipperHQ\Shipper\Helper\CarrierGroup
     */
    private $carrierGroupHelper;

    /**
     * Quote repository.
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var \ShipperHQ\Shipper\Helper\LogAssist
     */
    private $shipperLogger;

    /**
     * Application Event Dispatcher
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;
    /**
     * @var DataObjectFactory
     */
    private $objectFactory;

    public function __construct(
        \ShipperHQ\Shipper\Helper\CarrierGroup $carrierGroupHelper,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        DataObjectFactory $objectFactory
    ) {
        $this->carrierGroupHelper = $carrierGroupHelper;
        $this->quoteRepository = $quoteRepository;
        $this->checkoutSession = $checkoutSession;
        $this->addressRepository = $addressRepository;
        $this->shipperLogger = $shipperLogger;
        $this->eventManager = $eventManager;
        $this->objectFactory = $objectFactory;
    }

    /**
     * Set additional information for shipping address
     *
     * @param \Magento\Checkout\Model\ShippingInformationManagement $subject
     * @param callable $proceed
     *
     * @return \Magento\Checkout\Api\Data\PaymentDetailsInterface $paymentDetails
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        $proceed,
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {

        $carrierCode = $addressInformation->getShippingCarrierCode();
        $methodCode = $addressInformation->getShippingMethodCode();
        $shippingMethod = $carrierCode . '_' . $methodCode;
        $quote = $this->quoteRepository->getActive($cartId);
        $address = $quote->getShippingAddress();
        $validation = false;
        try {
            if ($this->checkoutSession) {
                $validation = $this->checkoutSession->getShipAddressValidation();
                if (is_array($validation) && isset($validation['key'])) {
                    if (isset($validation['validation_status'])) {
                        $address->setValidationStatus($validation['validation_status']);
                    }
                    if (isset($validation['destination_type'])) {
                        $address->setDestinationType($validation['destination_type']);
                    }
                    $this->checkoutSession->setShipAddressValidation(null);
                }
                $this->checkoutSession->setShipAddressValidation(null);
                $address->save();
            }
        } catch (\Exception $e) {
            $this->shipperLogger->postCritical(
                'Shipperhq_Shipper',
                'Shipping Information Plugin',
                'Exception raised ' . $e->getMessage()
            );
        }

        $additionalDetail = $this->objectFactory->create();
        $extAttributes = $addressInformation->getShippingAddress()->getExtensionAttributes();

        //push out event so other modules can save their data - in future add carrier_group_id
        //Observers add to additionalDetail object
        $this->eventManager->dispatch(
            'shipperhq_additional_detail_checkout',
            [
                'address_extn_attributes' => $extAttributes,
                'additional_detail' => $additionalDetail,
                'carrier_code' => $carrierCode,
                'address' => $address,
                'shipping_method' => $shippingMethod
            ]
        );
        $additionalDetailArray = $additionalDetail->convertToArray();
        //SHQ18-141 record validation status, address type and validated address
        if (is_array($validation)) {
            $additionalDetailArray = array_merge($validation, $additionalDetailArray);
        }

        $this->shipperLogger->postDebug(
            'ShipperHQ Shipper',
            'Processed the following extra fields from checkout ',
            $additionalDetail
        );
        $result = $proceed($cartId, $addressInformation);

        $this->carrierGroupHelper->saveCarrierGroupInformation(
            $address,
            $shippingMethod,
            $additionalDetailArray
        );

        if ($address->getCustomerId()) {
            $customerAddresses = $quote->getCustomer()->getAddresses();
            foreach ($customerAddresses as $oneAddress) {
                if ($oneAddress->getId() == $address->getCustomerAddressId() &&
                    is_array($validation) && isset($validation['key'])
                ) {
                    if (isset($validation['validation_status'])) {
                        $oneAddress->setCustomAttribute('validation_status', $validation['validation_status']);
                    }
                    if (isset($validation['destination_type'])) {
                        $oneAddress->setCustomAttribute('destination_type', $validation['destination_type']);
                    }
                    $this->addressRepository->save($oneAddress);
                }
            }
        }
        //SHQ16-2456
        $this->eventManager->dispatch(
            'shipperhq_additional_detail_checkout_post',
            [
                'address_extn_attributes' => $extAttributes,
                'additional_detail' => $additionalDetail,
                'carrier_code' => $carrierCode,
                'address' => $address,
                'shipping_method' => $shippingMethod
            ]
        );
        return $result;
    }
}
