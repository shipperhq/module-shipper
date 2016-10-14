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

class ShippingMethodManagementPlugin
{
    /**
     * Quote repository.
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;
    /**
     * @var \ShipperHQ\Shipper\Helper\Data
     */
    protected $shipperDataHelper;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;
    /**
     * @var \ShipperHQ\Shipper\Helper\LogAssist
     */
    private $shipperLogger;

    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \ShipperHQ\Shipper\Helper\Data $shipperDataHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
         \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->shipperDataHelper = $shipperDataHelper;
        $this->checkoutSession = $checkoutSession;
        $this->shipperLogger = $shipperLogger;
    }

    /**
     *Persist shipping address details so they are available when rates re-requested
     *
     * @param \Magento\Quote\Model\ShippingMethodManagement $subject
     * @param callable $proceed
     * @param $cartId
     * @param \Magento\Quote\Api\Data\AddressInterface $address
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[]
     *
     */
    public function aroundEstimateByExtendedAddress(\Magento\Quote\Model\ShippingMethodManagement $subject, $proceed,
                                                    $cartId, \Magento\Quote\Api\Data\AddressInterface $address)
    {
        $result = $proceed($cartId, $address);
        $this->saveShippingAddress($cartId);
        return $result;
    }

    /**
     *Persist shipping address details so they are available when rates re-requested
     *
     * @param \Magento\Quote\Model\ShippingMethodManagement $subject
     * @param callable $proceed
     * @param $cartId
     * @param \Magento\Quote\Api\Data\EstimateAddressInterface $address
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[]
     *
     */
    public function aroundEstimateByAddress(\Magento\Quote\Model\ShippingMethodManagement $subject, $proceed,
                                                    $cartId, \Magento\Quote\Api\Data\EstimateAddressInterface $address)
    {
        $result = $proceed($cartId, $address);
        $this->saveShippingAddress($cartId);
        return $result;
    }

    /**
     *Persist shipping address details so they are available when rates re-requested
     *
     * @param \Magento\Quote\Model\ShippingMethodManagement $subject
     * @param callable $proceed
     * @param $cartId
     * @param $addressId
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[]
     *
     */
    public function aroundEstimateByAddressId(\Magento\Quote\Model\ShippingMethodManagement $subject, $proceed,
                                            $cartId, $addressId)
    {
        $result = $proceed($cartId, $addressId);
        $this->saveShippingAddress($cartId);
        return $result;
    }

    protected function saveShippingAddress($cartId)
    {
        $quote = $this->quoteRepository->getActive($cartId);
        $address = $quote->getShippingAddress();
        $address->save();
    }
}
