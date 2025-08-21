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
 * @copyright Copyright (c) 2015 Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace ShipperHQ\Shipper\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * ShipperHQ Shipper module observer
 */
class CustomerAddressEdit implements ObserverInterface
{
    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     */
    public function __construct(
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
    ) {
        $this->addressRepository = $addressRepository;
    }

    /**
     * Set Checked status of "Remember Me"
     *
     * SHQ18-1001 Fix for 500 error when street address exceeds 255 chars. Thanks to @vkalchenko for the fix!
     *
     * @param EventObserver $observer
     *
     * @return void
     * @throws LocalizedException
     */
    public function execute(EventObserver $observer)
    {
        $request = $observer->getEvent()->getRequest();
        if ($request) {
            if ($addressId = $request->getParam('id')) {
                $existingAddress = $this->addressRepository->getById($addressId);
                foreach ($existingAddress->getCustomAttributes() as $customAttribute) {
                    if ($customAttribute->getAttributeCode() == 'destination_type') {
                        $existingAddress->setCustomAttribute('destination_type', '');
                    } elseif ($customAttribute->getAttributeCode() == 'validation_status') {
                        $existingAddress->setCustomAttribute('validation_status', '');
                    }
                }
                try {
                    $this->addressRepository->save($existingAddress);
                } catch (LocalizedException $e) {
                    //do nothing, message has already been added to the messsage queue
                }
            }
        }
    }
}
