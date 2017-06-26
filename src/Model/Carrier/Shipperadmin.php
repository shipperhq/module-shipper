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
namespace ShipperHQ\Shipper\Model\Carrier;

/**
 * Shipper shipping model
 *
 * @category ShipperHQ
 * @package ShipperHQ_Shipper
 */

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Rate\Result;
use Magento\Quote\Model\Quote\Item as QuoteItem;

class Shipperadmin
    extends \Magento\Shipping\Model\Carrier\AbstractCarrier
    implements \Magento\Shipping\Model\Carrier\CarrierInterface
{
    /**
     * @var string
     */
    protected $_code = 'shipperadmin';
    /**
     * @var \ShipperHQ\Shipper\Helper\Data
     */
    protected $shipperDataHelper;
    /**
     * @var \ShipperHQ\Shipper\Helper\LogAssist
     */
    private $shipperLogger;
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;
    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    protected $rateFactory;
    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    protected $rateMethodFactory;

    /**
     * @param \ShipperHQ\Shipper\Helper\Data $shipperDataHelper
     * @param \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Shipping\Model\Rate\ResultFactory $resultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param array $data
     */
    public function __construct(
        \ShipperHQ\Shipper\Helper\Data $shipperDataHelper,
        \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger,
        \Magento\Framework\Registry $registry,
        \Magento\Shipping\Model\Rate\ResultFactory $resultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        array $data = []
    )
    {
        $this->shipperDataHelper = $shipperDataHelper;
        $this->shipperLogger = $shipperLogger;
        $this->registry = $registry;
        $this->rateFactory = $resultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * Collect and get rates
     *
     * @param RateRequest $request
     * @return bool|Result|Error
     */
    public function collectRates(RateRequest $request)
    {
        $result = $this->rateFactory->create();

        if ($shipData = $this->registry->registry('shqadminship_data')) {
            foreach ($shipData->getData() as $carrierGroupId => $rateInfo) {
                $carrierGroupShippingDetail = array(
                    "checkoutDescription" => '',//$rateInfo['carriergroup'],
                    "name" => '',//$rateInfo['carriergroup'],
                    "carrierGroupId" => '',//$carrierGroupId,
                    "carrierType" => "custom_admin",
                    "carrierTitle" => $this->getConfigData('title'),
                    "carrier_code" => $this->_code,
                    "carrierName" => __('Custom Shipping'),
                    "methodTitle" => $rateInfo['customCarrier'],
                    "price" => $rateInfo['customPrice'],
                    "cost" => $rateInfo['customPrice'],
                    "code" => 'adminshipping',
                    "transaction" => ''
                );
                $method = $this->rateMethodFactory->create();
                $method->setCarrier($this->_code);
                $method->setPrice($rateInfo['customPrice']);
                $method->setCarrierTitle($this->getConfigData('title'));
                $method->setMethod('adminshipping');
                $method->setMethodTitle($rateInfo['customCarrier']);
                $method->setCarriergroupId($carrierGroupId);
                $method->setCarriergroupShippingDetails(
                    $this->shipperDataHelper->encode($carrierGroupShippingDetail));
                $result->append($method);
            }
            $this->shipperLogger->postDebug('Shipperhq_Shipper', 'ShipperHQ Admin - created custom shipping rate ', $shipData);
        }

        return $result;

    }

    /**
     * Get allowed shipping methods
     * @return array
     */
    public function getAllowedMethods()
    {
        return array('adminshipping'=>'adminshipping');

    }

}

