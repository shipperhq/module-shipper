<?php
/**
 * ShipperHQ
 *
 * @category ShipperHQ
 * @package ShipperHQ\Shipper
 * @copyright Copyright (c) 2015 Zowta LTD and Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */


namespace ShipperHQ\Shipper\Plugin\Quote;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\ShippingMethodManagement;
use ShipperHQ\Shipper\Helper\Data;

class ShippingMethodManagementPlugin
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;
    /**
     * Quote repository.
     *
     * @var CartRepositoryInterface
     */
    private $quoteRepository;
    /**
     * Customer Address repository
     *
     * @var AddressRepositoryInterface
     */
    private $addressRepository;
    /**
     * @var Session
     */
    private $customerSession;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;
    /**
     * @var Data
     */
    protected $shipperDataHelper;

    public function __construct(
        CartRepositoryInterface $quoteRepository,
        AddressRepositoryInterface $addressRepository,
        Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Checkout\Model\Session $checkoutSession,
        Data $shipperDataHelper
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->addressRepository = $addressRepository;
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->checkoutSession = $checkoutSession;
        $this->shipperDataHelper = $shipperDataHelper;
    }

    /**
     * Add customers address type to shipping address on quote
     *
     * @param ShippingMethodManagement $subject
     * @param                                               $cartId
     * @param int                                           $addressId
     *
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeEstimateByAddressId(
        ShippingMethodManagement $subject,
        $cartId,
        $addressId
    ) {
        /** @var Quote $quote */
        $quote = $this->checkoutSession->getQuote();

        $quoteAddress = $quote->getShippingAddress();

        // no methods applicable for empty carts or carts with virtual products
        if ($quote->isVirtual() || 0 == $quote->getItemsCount()) {
            return [$cartId, $addressId];
        }
        $address = $this->addressRepository->getById($addressId);

        /**
         * SHQ18-993 Reset so values from previously selected address aren't carrier over
         */
        $quoteAddress->unsetData('destination_type');
        $quoteAddress->unsetData('validation_status');

        if ($custom = $address->getCustomAttributes()) {
            foreach ($custom as $custom_attribute) {
                if ($custom_attribute->getAttributeCode() == 'destination_type') {
                    $quoteAddress->setData('destination_type', $custom_attribute->getValue());
                } elseif ($custom_attribute->getAttributeCode() == 'validation_status') {
                    $quoteAddress->setData('validation_status', $custom_attribute->getValue());
                }
            }
        }

        return [$cartId, $addressId];
    }

    /**
     * This function looks at the default saved addresses destination_type and applies it to any new saved address
     *
     * @param ShippingMethodManagement $subject
     * @param $cartId
     * @param AddressInterface $address
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeEstimateByExtendedAddress(
        ShippingMethodManagement $subject,
        $cartId,
        AddressInterface $address
    ) {

        /** @var Quote $quote */
        $quote = $this->checkoutSession->getQuote();

        // No methods applicable for empty carts or carts with virtual products
        // MNB-2474 We don't want to assume an address type based on default if AV is enabled. Let AV do its thing
        if ($this->shipperDataHelper->getAddressValidationEnabled()
            || $quote->isVirtual() || 0 == $quote->getItemsCount()) {
            return [$cartId, $address];
        }
        // If logged in, get the default address and apply address type to address
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
