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

use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\QuoteManagement;
use ShipperHQ\Shipper\Helper\CarrierGroup;
use ShipperHQ\Shipper\Helper\LogAssist;

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
     * @var LogAssist
     */
    private $logger;

    public function __construct(
        CartRepositoryInterface $quoteRepository,
        ManagerInterface $eventManager,
        DataObjectFactory $objectFactory,
        CarrierGroup $carrierGroupHelper,
        LogAssist $logger
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->eventManager = $eventManager;
        $this->objectFactory = $objectFactory;
        $this->carrierGroupHelper = $carrierGroupHelper;
        $this->logger = $logger;
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

            $additionalDetail = $this->objectFactory->create();
            $carrierCode = '';
            if (strpos($shippingMethod, '_') !== false) {
                $parts = explode('_', $shippingMethod, 2);
                $carrierCode = $parts[0];
            }

            $this->eventManager->dispatch(
                'shipperhq_additional_detail_checkout',
                [
                    'address_extn_attributes' => $address->getExtensionAttributes(),
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
}
