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

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * ShipperHQ Shipper module observer
 */
class RefreshCarriers implements ObserverInterface
{
    /**
     * @var \ShipperHQ\Shipper\Model\Carrier\Shipper
     */
    private $shipperCarrier;
    /**
     * @var ManagerInterface
     */
    private $messageManager;
    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @var \ShipperHQ\Shipper\Helper\Data
     */
    private $shipperDataHelper;

    /**
     * @param ScopeConfigInterface $config
     * @param  \ShipperHQ\Shipper\Model\Carrier\Shipper $carrier
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        ScopeConfigInterface $config,
        \ShipperHQ\Shipper\Model\Carrier\Shipper $carrier,
        ManagerInterface $messageManager,
        \ShipperHQ\Shipper\Helper\Data $shipperDataHelper
    ) {
        $this->shipperCarrier = $carrier;
        $this->messageManager = $messageManager;
        $this->config = $config;
        $this->shipperDataHelper = $shipperDataHelper;
    }

    /**
     * Update saved shipping methods available for ShipperHQ
     *
     * @param EventObserver $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(EventObserver $observer)
    {
        if ($this->config->isSetFlag('carriers/shipper/active', ScopeInterface::SCOPE_STORES)) {
            if (!$this->shipperDataHelper->getCredentialsEntered()) {
                $message = __('Missing credentials for ShipperHQ. Can\'t update carriers');
                $this->messageManager->addError($message);

                return;
            }

            $refreshResult = $this->shipperCarrier->refreshCarriers();
            if (array_key_exists('error', $refreshResult)) {
                $message = __($refreshResult['error']);
                $this->messageManager->addError($message);
            } else {
                $message = __('%1 carriers have been updated from ShipperHQ', count($refreshResult));
                $this->messageManager->addSuccess($message);
            }
        }
    }
}
