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
use Magento\Store\Model\ScopeInterface;
use ShipperHQ\Shipper\Helper\CarrierGroup;

/**
 * ShipperHQ Shipper module observer
 */
class SaveEmailVariables implements ObserverInterface
{
    /**
     * @var CarrierGroup
     */
    private $carrierGroupHelper;
    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @param ScopeConfigInterface $config
     * @param CarrierGroup         $carrierGroupHelper
     */
    public function __construct(
        ScopeConfigInterface $config,
        CarrierGroup $carrierGroupHelper
    ) {
        $this->config = $config;
        $this->carrierGroupHelper = $carrierGroupHelper;
    }

    /**
     * Record order shipping information after order is placed
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        if ($this->config->isSetFlag('carriers/shipper/active', ScopeInterface::SCOPE_STORES)) {
            $data = $observer->getTransport();
            $order = $data->getOrder();
            $orderDetail = $this->carrierGroupHelper->getOrderCarrierGroupInfo($order->getId());
            if (count($orderDetail) > 0) {
                foreach ($orderDetail as $orderData) {
                    // SHQ23-2090 These fields are saved with 0 rather than being null. M2 interprets 0 as a value,
                    // and so we can't use them as conditions in email templates. Need to convert to null
                    $liftgateRequired = $orderData['liftgate_required'] ?? null;
                    $notifyRequired = $orderData['notify_required'] ?? null;
                    $insideDelivery = $orderData['inside_delivery'] ?? null;
                    $limitedDelivery = $orderData['limited_delivery'] ?? null;

                    $data['deliveryDate']           = $orderData['delivery_date'] ?? null;
                    $data['dispatchDate']           = $orderData['dispatch_date'] ?? null;
                    $data['customerCarrier']        = $orderData['customer_carrier'] ?? null;
                    $data['customerCarrierAccount'] = $orderData['customer_carrier_account'] ?? null;
                    $data['customerCarrierPh']      = $orderData['customer_carrier_ph'] ?? null;
                    $data['liftgateRequired']       = $liftgateRequired == 0 ? null : $liftgateRequired;
                    $data['notifyRequired']         = $notifyRequired == 0 ? null : $notifyRequired;
                    $data['insideDelivery']         = $insideDelivery == 0 ? null : $insideDelivery;
                    $data['limitedDelivery']        = $limitedDelivery == 0 ? null : $limitedDelivery;
                    break;
                }
            } else {
                $quoteShippingAddress = $this->carrierGroupHelper->getQuoteShippingAddressFromOrder($order);
                if ($quoteShippingAddress) {
                    $quoteAddressDetailsCollection = $this->carrierGroupHelper->loadAddressDetailByShippingAddress(
                        $quoteShippingAddress->getId()
                    );

                    $quoteAddressData = $quoteAddressDetailsCollection->getData();

                    if (count($quoteAddressData) > 0) {
                        foreach ($quoteAddressData as $quoteAddressDetail) {
                            $liftgateRequired = $quoteAddressDetail['liftgate_required'] ?? null;
                            $notifyRequired = $quoteAddressDetail['notify_required'] ?? null;
                            $insideDelivery = $quoteAddressDetail['inside_delivery'] ?? null;
                            $limitedDelivery = $quoteAddressDetail['limited_delivery'] ?? null;

                            $data['deliveryDate']           = $quoteAddressDetail['delivery_date'] ?? null;
                            $data['dispatchDate']           = $quoteAddressDetail['dispatch_date'] ?? null;
                            $data['customerCarrier']        = $quoteAddressDetail['customer_carrier'] ?? null;
                            $data['customerCarrierAccount'] = $quoteAddressDetail['customer_carrier_account'] ?? null;
                            $data['customerCarrierPh']      = $quoteAddressDetail['customer_carrier_ph'] ?? null;
                            $data['liftgateRequired']       = $liftgateRequired == 0 ? null : $liftgateRequired;
                            $data['notifyRequired']         = $notifyRequired == 0 ? null : $notifyRequired;
                            $data['insideDelivery']         = $insideDelivery == 0 ? null : $insideDelivery;
                            $data['limitedDelivery']        = $limitedDelivery == 0 ? null : $limitedDelivery;
                            break;
                        }
                    }
                }
            }
        }
    }
}
