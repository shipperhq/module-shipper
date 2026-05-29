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
