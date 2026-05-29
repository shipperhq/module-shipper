<?php
/**
 * ShipperHQ
 *
 * @category ShipperHQ
 * @package ShipperHQ\Shipper
 * @copyright Copyright (c) 2022 Zowta LTD and Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */


namespace ShipperHQ\Shipper\Observer;

use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * ShipperHQ Shipper module observer
 */
class SetAdminCheckoutFlag implements ObserverInterface
{

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @param Session $checkoutSession
     */
    public function __construct(
        Session $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Set the checkout flag on the session. Will enable the use of calendar & pickup.
     * Accessorials are not currently supported
     *
     * @param EventObserver $observer
     *
     * @return void
     * @SuppressWarnings(PMD.UnusedFormalParameter)
     */
    public function execute(EventObserver $observer)
    {
        $this->checkoutSession->setIsCheckout(1);

        // Adding this for future so as that we can more easily determine if we're in admin vs checkout if needed
        $this->checkoutSession->setIsAdminCheckout(1);
    }
}
