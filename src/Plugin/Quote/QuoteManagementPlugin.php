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
 * @copyright Copyright (c) 2025 Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */

namespace ShipperHQ\Shipper\Plugin\Quote;

use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\QuoteManagement;
use ShipperHQ\Shipper\Helper\CarrierGroup;
use ShipperHQ\Shipper\Helper\Data;
use ShipperHQ\Shipper\Helper\LogAssist;
use ShipperHQ\Shipper\Model\ResourceModel\Quote\AddressDetail\CollectionFactory as AddressDetailCollectionFactory;

class QuoteManagementPlugin
{
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var DataObjectFactory
     */
    private $objectFactory;

    /**
     * @var CarrierGroup
     */
    private $carrierGroupHelper;

    /**
     * @var Data
     */
    private $shipperDataHelper;

    /**
     * @var LogAssist
     */
    private $logger;

    /**
     * @var AddressDetailCollectionFactory
     */
    private $addressDetailCollectionFactory;

    public function __construct(
        CartRepositoryInterface $quoteRepository,
        ManagerInterface $eventManager,
        DataObjectFactory $objectFactory,
        CarrierGroup $carrierGroupHelper,
        LogAssist $logger,
        AddressDetailCollectionFactory $addressDetailCollectionFactory,
        Data $shipperDataHelper
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->eventManager = $eventManager;
        $this->objectFactory = $objectFactory;
        $this->carrierGroupHelper = $carrierGroupHelper;
        $this->logger = $logger;
        $this->addressDetailCollectionFactory = $addressDetailCollectionFactory;
        $this->shipperDataHelper = $shipperDataHelper;
    }

    /**
     * SHQ23-6083 This method makes a final call to save the carrierGroupDetails to the quote.
     * This is necessary because if the shipping price has changed between the shipping step/payment step vs place order
     * the wrong price will be saved in the carrierGroupDetails resulting in a mismatch of shipping price charged vs
     * what is shown in carrierGroupDetails. It also ensures the correct transaction ID is used to retrieve the quote
     * from shipping insights.
     *
     * A good example of this happening is backup rates (from the SHQ API, not M2 backup carrier) being returned on the
     * place order call; without this code the backup rate would be charged (correct) but the carrierGroupDetails and
     * shipping insights would show the original price
     *
     * @param QuoteManagement $subject
     * @param callable $proceed
     * @param int $cartId
     * @param PaymentInterface|null $paymentMethod
     * @return int
     */
    public function aroundPlaceOrder(
        QuoteManagement $subject,
        callable $proceed,
        $cartId,
        ?PaymentInterface $paymentMethod = null
    ) {
        $this->updateCarrierGroupDetails((int) $cartId);

        return $proceed($cartId, $paymentMethod);
    }

    private function updateCarrierGroupDetails(int $cartId): void
    {
        try {
            $quote = $this->quoteRepository->getActive($cartId);
            $address = $quote->getShippingAddress();
            if (!$address) {
                return;
            }

            $shippingMethod = (string) $address->getShippingMethod();
            if (!$shippingMethod) {
                return;
            }

            // Extension attributes have been wiped by this point as they're just held in memory and Magento
            // has reloaded the quote address from the DB now. Let's get them from shipperhq_quote_address_detail
            // which has been updated at this point to have the correct values
            $addressExtensionAttributes = $this->getQuoteAddressDetails($address->getId());

            // ENG26-88 If the shipping price is the same then there's no point in continuing.
            // This class is intended for the rare occurrences when backup rates are triggered or the carriers
            // shipping price has changed on the place order call
            if ($addressExtensionAttributes->getShippingPrice() == $address->getShippingAmount()) {
                return;
            }

            // The shipping price IS different. We need to proceed. It's worth noting that some details may not be
            // saved at this point such as custom carrier details, destination types etc. This is a last ditch attempt
            // to store the correct details in a backup rates or carrier response changed scenario

            $additionalDetail = $this->objectFactory->create();
            $carrierCode = '';
            if (strpos($shippingMethod, '_') !== false) {
                $parts = explode('_', $shippingMethod, 2);
                $carrierCode = $parts[0];
            }

            $this->eventManager->dispatch(
                'shipperhq_additional_detail_checkout',
                [
                    'address_extn_attributes' => $addressExtensionAttributes,
                    'additional_detail' => $additionalDetail,
                    'carrier_code' => $carrierCode,
                    'address' => $address,
                    'shipping_method' => $shippingMethod
                ]
            );

            $this->carrierGroupHelper->saveCarrierGroupInformation(
                $address,
                $shippingMethod,
                $additionalDetail->convertToArray()
            );


        } catch (\Throwable $e) {
            $this->logger->postCritical(
                'Shipperhq_Shipper',
                'Place Order CarrierGroupDetails update failed',
                $e->getMessage()
            );
        }
    }

    /**
     * Get quote address details for a given address ID and extract what would have been extension attributes
     *
     * @param int $addressId
     * @return DataObject
     */
    private function getQuoteAddressDetails(int $addressId)
    {
        $collection = $this->addressDetailCollectionFactory->create()
            ->addAddressToFilter($addressId);

        $extensionAttributes = $this->objectFactory->create();

        foreach ($collection as $detail) {

            // ENG26-88 Let's extract the shipping price so we can check if it's different to the price on the quote
            if ($detail->getCarrierGroupDetail()) {
                $decodedDetails = $this->shipperDataHelper->decodeShippingDetails($detail->getCarrierGroupDetail());

                $carrierGroupDetail = !empty($decodedDetails) ? $decodedDetails[0] : [];

                if (array_key_exists('price', $carrierGroupDetail)) {
                    $extensionAttributes->setShippingPrice($carrierGroupDetail['price']);
                }
            }

            if ($detail->getDeliveryDate()) {
                $extensionAttributes->setDeliveryDate($detail->getDeliveryDate());

                if ($detail->getTimeSlot()) {
                    $extensionAttributes->setTimeSlot($detail->getTimeSlot());
                }
            }

            if ($detail->getPickupLocationId()) {
                $extensionAttributes->setLocationId($detail->getPickupLocationId());
            }

            if ($detail->getPickupLocation()) {
                $extensionAttributes->setLocationAddress($detail->getPickupLocation());
            }

            $this->logger->postDebug(
                'Shipperhq_Shipper',
                'Extension Attributes Extracted from Address Detail',
                $extensionAttributes->getData()
            );
        }

        return $extensionAttributes;
    }
}
