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

class ShippingInformationPlugin
{
    /**
     * @var \ShipperHQ\Shipper\Helper\Data
     */
    protected $shipperDataHelper;
    /**
     * @var \ShipperHQ\Shipper\Helper\CarrierGroup
     */
    protected $carrierGroupHelper;
    /**
     * Quote repository.
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;
    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var \ShipperHQ\Shipper\Model\Quote\AddressDetailFactory
     */
    private $addressDetailFactory;
    /**
     * @var \ShipperHQ\Shipper\Helper\LogAssist
     */
    private $shipperLogger;
    /**
     * Application Event Dispatcher
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    public function __construct(
        \ShipperHQ\Shipper\Helper\Data $shipperDataHelper,
        \ShipperHQ\Shipper\Helper\CarrierGroup $carrierGroupHelper,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \ShipperHQ\Shipper\Model\Quote\AddressDetailFactory $addressDetailFactory,
        \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->shipperDataHelper = $shipperDataHelper;
        $this->carrierGroupHelper = $carrierGroupHelper;
        $this->quoteRepository = $quoteRepository;
        $this->checkoutSession = $checkoutSession;
        $this->addressRepository = $addressRepository;
        $this->addressDetailFactory = $addressDetailFactory;
        $this->shipperLogger = $shipperLogger;
        $this->eventManager = $eventManager;

    }

    /**
     *Set additional information for shipping address
     *
     * @param \Magento\Checkout\Model\ShippingInformationManagement $subject
     * @param callable $proceed
     *
     * @return \Magento\Checkout\Api\Data\PaymentDetailsInterface $paymentDetails
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function aroundSaveAddressInformation(\Magento\Checkout\Model\ShippingInformationManagement $subject, $proceed,
                                                 $cartId,
                                                 \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation)
    {
        try {
            $carrierCode = $addressInformation->getShippingCarrierCode();
            $methodCode = $addressInformation->getShippingMethodCode();
            $shippingMethod = $carrierCode . '_' . $methodCode;
            $quote = $this->quoteRepository->getActive($cartId);
            $address = $quote->getShippingAddress();
            $validation = $this->checkoutSession->getShipAddressValidation();
            if (is_array($validation) && isset($validation['key'])) {
                if (isset($validation['validation_status'])) {
                    $additionalDetail['address_valid'] = $validation['validation_status'];
                    $address->setValidationStatus($validation['validation_status']);
                }
                if (isset($validation['destination_type'])) {
                    $additionalDetail['destination_type'] = $validation['destination_type'];
                    $address->setDestinationType($validation['destination_type']);
                }
                $this->checkoutSession->setShipAddressValidation(null);
            }
            $address->save();
            $additionalDetail = new \Magento\Framework\DataObject;
            $extAttributes = $addressInformation->getExtensionAttributes();
            //push out event so other modules can save their data TODO add carrier_group_id
            $this->eventManager->dispatch('shipperhq_additional_detail_checkout',
                ['address_extn_attributes' => $extAttributes, 'additional_detail'=> $additionalDetail,
                'carrier_code' => $carrierCode]);
            $additionalDetailArray = $additionalDetail->convertToArray();
            $this->carrierGroupHelper->saveCarrierGroupInformation($address,
                $shippingMethod, $additionalDetailArray);

        } catch (\Exception $e) {
            $this->shipperLogger->postCritical('Shipperhq_Shipper',
                'Shipping Information Plugin',
                'Exception raised ' .$e->getMessage());
        }

        $result = $proceed($cartId, $addressInformation);

        if($address->getCustomerId()) {
            $customerAddresses = $quote->getCustomer()->getAddresses();
            foreach($customerAddresses as $oneAddress) {

                if ($oneAddress->getId() == $address->getCustomerAddressId()) {

                    if($address->getValidationStatus()) {
                        $oneAddress->setCustomAttribute('validation_status', $address->getValidationStatus());
                    }

                    if($address->getDestinationType()) {
                        $oneAddress->setCustomAttribute('destination_type',$address->getDestinationType());
                    }
                    $this->addressRepository->save($oneAddress);

                }
            }
        }
        return $result;

    }

}
