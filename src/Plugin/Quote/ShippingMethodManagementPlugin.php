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

namespace ShipperHQ\Shipper\Plugin\Quote;

use Magento\Quote\Api\Data\AddressInterface;

class ShippingMethodManagementPlugin
{
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;
    /**
     * Quote repository.
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;
    /**
     * Customer Address repository
     *
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    private $addressRepository;
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->addressRepository = $addressRepository;
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Add customers address type to shipping address on quote
     *
     * @param \Magento\Quote\Model\ShippingMethodManagement $subject
     * @param $cartId
     * @param int $addressId
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[]
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeEstimateByAddressId(
        \Magento\Quote\Model\ShippingMethodManagement $subject,
        $cartId,
        $addressId
    ) {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        // no methods applicable for empty carts or carts with virtual products
        if ($quote->isVirtual() || 0 == $quote->getItemsCount()) {
            return [$cartId, $addressId];
        }
        $address = $this->addressRepository->getById($addressId);

        if ($custom = $address->getCustomAttributes()) {
            foreach ($custom as $custom_attribute) {
                if ($custom_attribute->getAttributeCode() == 'destination_type') {
                    $quote->getShippingAddress()->setData('destination_type', $custom_attribute->getValue());
                } elseif ($custom_attribute->getAttributeCode() == 'validation_status') {
                    $quote->getShippingAddress()->setData('validation_status', $custom_attribute->getValue());
                }
            }
        }

        return [$cartId, $addressId];
    }

    /**
     * @param \Magento\Quote\Model\ShippingMethodManagement $subject
     * @param $cartId
     * @param AddressInterface $address
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeEstimateByExtendedAddress(
        \Magento\Quote\Model\ShippingMethodManagement $subject,
        $cartId,
        \Magento\Quote\Api\Data\AddressInterface $address
    ) {

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        // no methods applicable for empty carts or carts with virtual products
        if ($quote->isVirtual() || 0 == $quote->getItemsCount()) {
            return [$cartId, $address];
        }
        //if logged in, get the default address and apply address type to address
        if ($this->customerSession->isLoggedIn()) {
            $customer = $this->customerRepository->getById($this->customerSession->getCustomerId());
            if ($defaultShipping = $customer->getDefaultShipping()) {
                $defaultAddress = $this->addressRepository->getById($defaultShipping);
                if ($custom = $defaultAddress->getCustomAttributes()) {
                    foreach ($custom as $custom_attribute) {
                        if ($custom_attribute->getAttributeCode() == 'destination_type') {
                            $quote->getShippingAddress()->setData('destination_type', $custom_attribute->getValue());
                        }
                    }
                }
            }
        }

        return [$cartId, $address];
    }
}
