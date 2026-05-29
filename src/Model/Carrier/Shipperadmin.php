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


namespace ShipperHQ\Shipper\Model\Carrier;

/**
 * Shipper shipping model
 *
 * @category ShipperHQ
 * @package ShipperHQ\Shipper
 */

use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use ShipperHQ\Shipper\Service\Backend\GetAdminShipData;

class Shipperadmin extends AbstractCarrier implements CarrierInterface
{
    /**
     * @var string
     */
    protected $_code = 'shipperadmin';
    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    protected $rateFactory;
    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    protected $rateMethodFactory;
    /**
     * @var \ShipperHQ\Shipper\Helper\LogAssist
     */
    private $shipperLogger;
    /**
     * @var GetAdminShipData
     */
    private $getAdminShipData;
    /**
     * @var JsonHelper
     */
    private $jsonHelper;

    /**
     * @param JsonHelper $jsonHelper
     * @param \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger
     * @param GetAdminShipData $getAdminShipData
     * @param \Magento\Shipping\Model\Rate\ResultFactory $resultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param array $data
     */
    public function __construct(
        JsonHelper $jsonHelper,
        \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger,
        GetAdminShipData $getAdminShipData,
        \Magento\Shipping\Model\Rate\ResultFactory $resultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        array $data = []
    ) {
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
        $this->shipperLogger = $shipperLogger;
        $this->getAdminShipData = $getAdminShipData;
        $this->rateFactory = $resultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * Collect and get rates
     *
     * @param RateRequest $request
     * @return bool|Result|Error
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function collectRates(RateRequest $request)
    {
        $result = $this->rateFactory->create();

        $shipData = $this->getAdminShipData->execute();
        if ($shipData) {
            $carrierGroupShippingDetail = [
                "checkoutDescription" => '',//$rateInfo['carriergroup'],
                "name" => '',//$rateInfo['carriergroup'],
                "carrierGroupId" => '',//$carrierGroupId,
                "carrierType" => "custom_admin",
                "carrierTitle" => $this->getConfigData('title'),
                "carrier_code" => $this->_code,
                "carrierName" => __('Custom Shipping'),
                "methodTitle" => $shipData->getCustomCarrier(),
                "price" => $shipData->getCustomPrice(),
                "cost" => $shipData->getCustomPrice(),
                "code" => 'adminshipping',
                "transaction" => ''
            ];
            $method = $this->rateMethodFactory->create();
            $method->setCarrier($this->_code);
            $method->setPrice($shipData->getCustomPrice());
            $method->setCarrierTitle((string) $this->getConfigData('title'));
            $method->setMethod('adminshipping');
            $method->setMethodTitle((string) $shipData->getCustomCarrier());
            $method->setCarriergroupId(0);
            $method->setCarriergroupShippingDetails(
                $this->jsonHelper->jsonEncode($carrierGroupShippingDetail)
            );
            $result->append($method);

            $this->shipperLogger->postDebug(
                'Shipperhq_Shipper',
                'ShipperHQ Admin - created custom shipping rate ',
                $shipData
            );
        }

        return $result;
    }

    /**
     * Get allowed shipping methods
     * @return array
     */
    public function getAllowedMethods()
    {
        return ['adminshipping' => 'adminshipping'];
    }
}
