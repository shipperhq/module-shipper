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

    public function __construct(
        \ShipperHQ\Shipper\Helper\Data $shipperDataHelper,
        \ShipperHQ\Shipper\Helper\CarrierGroup $carrierGroupHelper,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
    ) {
        $this->shipperDataHelper = $shipperDataHelper;
        $this->carrierGroupHelper = $carrierGroupHelper;
        $this->quoteRepository = $quoteRepository;

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

        $result = $proceed($cartId, $addressInformation);
        $quote = $this->quoteRepository->getActive($cartId);
        $address = $quote->getShippingAddress();
        $this->carrierGroupHelper->saveCarrierGroupInformation($address,
            $address->getShippingMethod());

        return $result;

    }

}
