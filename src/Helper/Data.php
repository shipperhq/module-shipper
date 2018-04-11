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

namespace ShipperHQ\Shipper\Helper;

use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Webapi\Exception;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Shipping data helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const SHIPPERHQ_SHIPPER_CARRIERGROUP_DESC_PATH = 'carriers/shipper/carriergroup_describer';
    const SHIPPERHQ_LAST_SYNC = 'carriers/shipper/last_sync';
    const SHIPPERHQ_SHIPPER_ALLOWED_METHODS_PATH = 'carriers/shipper/allowed_methods';
    private static $showTransId;
    public $magentoCarrierCodes =
        [
            'ups' => 'ups',
            'fedEx' => 'fedex',
            'usps' => 'usps',
            'dhl' => 'dhl',
            'dhlint' => 'dhlint',
            'upsFreight' => 'upsfreight'
        ];
    private $prodAttributes;
    private $baseCurrencyRate;
    /**
     * @var Mage_Sales_Model_Quote
     */
    private $quote;
    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;
    /**
     * @var
     */
    private $storeManager;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;
    /**
     * @var JsonHelper
     */
    private $jsonHelper;
    /**
     * @var \Magento\Shipping\Model\CarrierFactoryInterface
     */
    private $carrierFactory;
    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    private $dirCurrencyFactory;

    /**
     * @var \Magento\Checkout\Helper\Data
     */
    private $checkoutHelper;

    public function __construct(
        Config $shipperConfig,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Helper\Context $context,
        JsonHelper $jsonHelper,
        \Magento\Directory\Model\CurrencyFactory $dirCurrencyFactory,
        \Magento\Shipping\Model\CarrierFactoryInterface $carrierFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        StoreManagerInterface $storeManager,
        \Magento\Checkout\Helper\Data $checkoutHelper
    ) {
        parent::__construct($context);
        $this->shipperConfig = $shipperConfig;
        $this->eavConfig = $eavConfig;
        $this->registry = $registry;
        $this->storeManager = $storeManager;
        $this->productFactory = $productFactory;
        $this->checkoutSession = $checkoutSession;
        $this->jsonHelper = $jsonHelper;
        $this->dirCurrencyFactory = $dirCurrencyFactory;
        $this->carrierFactory = $carrierFactory;
        $this->checkoutHelper = $checkoutHelper;
    }

    public function getCarrierGroupDescPath()
    {
        return self::SHIPPERHQ_SHIPPER_CARRIERGROUP_DESC_PATH;
    }

    public function getLastSyncPath()
    {
        return self::SHIPPERHQ_LAST_SYNC;
    }

    public function getAllowedMethodsPath()
    {
        return self::SHIPPERHQ_SHIPPER_ALLOWED_METHODS_PATH;
    }

    /**
     * Gets a config flag
     *
     * @param $configField
     * @return mixed
     */
    public function getConfigFlag($configField)
    {
        return $this->scopeConfig->isSetFlag($configField, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Retrieve debug configuration
     * @return boolean
     */
    public function isTransactionIdEnabled()
    {
        if (self::$showTransId == null) {
            self::$showTransId = $this->getConfigValue('carriers/shipper/display_transaction');
        }
        return self::$showTransId;
    }

    /**
     * Get Config Value
     *
     * @param $configField
     * @return mixed
     */
    public function getConfigValue($configField, $store = null)
    {
        return $this->scopeConfig->getValue(
            $configField,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getTransactionId()
    {
        $id = $this->registry->registry('shipperhq_transaction');
        return $id;
    }

    public function getBaseCurrencyCode()
    {
        return $this->storeManager->getStore()->getBaseCurrency()->getCode();
    }

    public function getBaseCurrencyRate($code)
    {
        $currencyModel = $this->dirCurrencyFactory->create();
        $allowedCurrencies = $currencyModel->getConfigAllowCurrencies();
        if (!in_array($code, $allowedCurrencies)) {
            return false;
        }
        $baseCurrencyCode = $this->storeManager->getStore()->getBaseCurrencyCode();
        if (!$this->baseCurrencyRate) {
            $this->baseCurrencyRate = $currencyModel
                ->load($code)
                ->getAnyRate($baseCurrencyCode);
        }

        return $this->baseCurrencyRate > 0 ? $this->baseCurrencyRate : false;
    }

    public function useDefaultCarrierCodes()
    {
        $result = false;
        if ($this->getDefaultConfigValue('carriers/shipper/CARRIER_STRIP_CODE')) {
            $result = true;
        }
        return $result;
    }

    /**
     * Get Default Scope Config Value
     *
     * @param $configField
     * @return mixed
     */
    public function getDefaultConfigValue($configField)
    {
        return $this->scopeConfig->getValue($configField);
    }

    public function getStoreDimComments()
    {
        $result = false;
        if ($this->getDefaultConfigValue('carriers/shipper/STORE_DIM_COMMENTS')) {
            $result = true;
        }
        return $result;
    }

    public function getAlwaysShowSingleCarrierTitle()
    {
        $result = false;
        if ($this->getDefaultConfigValue('carriers/shipper/ALWAYS_SHOW_SINGLE_CARRIER_TITLE')) {
            $result = true;
        }
        return $result;
    }

    public function isCheckout($quote)
    {

        $isCheckout = $this->checkoutSession->getIsCheckout();
        if ($quote->getIsMultiShipping()) {
            return true;
        }
        return $isCheckout;
    }
    
    public function encode($data)
    {
        return $this->jsonHelper->jsonEncode($data);
    }

    public function decodeShippingDetails($shippingDetailsEnc)
    {
        $decoded = [];
        if ($shippingDetailsEnc !== null && $shippingDetailsEnc != '') {
            $decoded = $this->jsonHelper->jsonDecode($shippingDetailsEnc);
        }
        return $decoded;
    }

    /**
     * Retrieve checkout quote model object
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        if ($this->quote !== null) {
            return $this->quote;
        }

        $this->quote = $this->checkoutSession->getQuote();

        return $this->quote;
    }

    /**
     * Overrides quote model
     *
     * @param Mage_Sales_Model_Quote|null $quote
     * @return $this
     */
    public function setQuote($quote)
    {
        $this->quote = $quote;
        return $this;
    }

    public function setGlobalSettings($globals)
    {
        $this->checkoutSession->setShipperGlobal($globals);
    }

    public function getCheckout()
    {
        return $this->checkoutSession;
    }

    public function mapToMagentoCarrierCode($carrierType, $carrierCode)
    {
        if (array_key_exists($carrierType, $this->magentoCarrierCodes)) {
            return $this->magentoCarrierCodes[$carrierType];
        }
        return $carrierCode;
    }

    /**
     *
     * @return array
     */
    public function getProductAttributes()
    {
        if ($this->prodAttributes == null) {
            $this->prodAttributes = $this->eavConfig->getEntityAttributeCodes(\Magento\Catalog\Model\Product::ENTITY);
        }

        return $this->prodAttributes;
    }

    public function extractAddressIdAndCarriergroupId(&$addressId, &$carrierGroupId)
    {
        if (strstr($carrierGroupId, 'ma')) {
            $addressId = str_replace('ma', '', $carrierGroupId);
            $carrierGroupId = '';
            if (strstr($addressId, 'ZZ')) {
                $idArray = explode('ZZ', $addressId);
                $addressId = $idArray[0];
                $carrierGroupId = $idArray[1];
            }
        }
    }

    public function getCarriergroupShippingHtml($encodedDetails)
    {
        $decodedDetails = self::decode($encodedDetails);
        $htmlText = '';
        foreach ($decodedDetails as $shipLine) {
            if (!is_array($shipLine) || !array_key_exists('name', $shipLine)) {
                continue;
            }
            $htmlText .= $shipLine['name'] .
                ' : ' . $shipLine['carrierTitle'] . ' - ' . $shipLine['methodTitle'] . ' ';
            $htmlText .= " " . $this->checkoutHelper->formatPrice($shipLine['price']) . '<br/>';
        }
        return $htmlText;
    }

    public function decode($data)
    {
        $decoded = [];
        if ($data !== null && $data != '') {
            try {
                $result = json_decode($data);
                if ($result !== null) {
                    $decoded = $result;
                }
            } catch (Exception $e) {
                return $decoded;
            }
        }
        return $decoded;
    }

    public function getPackageBreakdownText($packages, $carrierGroupName = false)
    {
        $boxText = '';
        $count = 1;
        if ($carrierGroupName) {
            $boxText .= $carrierGroupName . ': ';
        }
        foreach ($packages as $box) {
            $boxText .= __('Package') . ' #' . ($count++);

            if ($box != null) {
                $boxText .= ' Box name: ' . $box['package_name'];
                $boxText .= ' : ' . $box['length'];
                $boxText .= 'x' . $box['width'];
                $boxText .= 'x' . $box['height'];
                $boxText .= ': W=' . $box['weight'] . ':';
                $boxText .= ' Value=' . $box['declaredValue'] . ':';
                $boxText .= $this->getProductBreakdownText($box);
            }
            $boxText .= '<br/>';
        }
        return $boxText;
    }

    public function getProductBreakdownText($box)
    {
        $productText = '';
        $weightUnit = $this->getGlobalSetting('weightUnit');
        if (!$weightUnit) {
            $weightUnit = '';
        }
        if (array_key_exists('items', $box) || (is_object($box) && $box->getItems() !== null)) {
            if (is_array($box['items'])) {
                foreach ($box['items'] as $item) {
                    $productText .= ' SKU='
                        . $item['qty_packed']
                        . ' * ' . $item['sku']
                        . ' ' . $item['weight_packed']
                        . $weightUnit . ';  ';
                }
            } else {
                $productText = $box['items'];
            }
        }
        return $productText;
    }

    public function getGlobalSetting($code)
    {
        $globals = self::getGlobalSettings();
        if ($globals !== null && array_key_exists($code, $globals)
            && $globals[$code] != '') {
            return $globals[$code];
        }
        return false;
    }

    public function getGlobalSettings()
    {
        return $this->checkoutSession->getShipperGlobal();
    }

    public function getAttribute($attribute_code, $store = null)
    {

        $product = $this->productFactory->create();
        $attribute = $product->getResource()->getAttribute($attribute_code);
        if ($store === null || $store == '') {
            $store = Store::DEFAULT_STORE_ID;
        }
        $attribute->setStoreId($store);

        return $attribute;
    }

    /**
     * Get carrier by its code
     *
     * @param string $carrierCode
     * @param null|int $storeId
     * @return bool|\Magento\Shipping\Model\Carrier\AbstractCarrier
     */
    public function getCarrierByCode($carrierCode, $storeId = null)
    {
        if (!$this->getConfigValue('carriers/' . $carrierCode . '/active', $storeId)) {
            return false;
        }
        $className = $this->getConfigValue('carriers/' . $carrierCode . '/model', $storeId);
        if (!$className) {
            return false;
        }
        $carrier = $this->carrierFactory->get($carrierCode);
        if ($storeId) {
            $carrier->setStore($storeId);
        }
        return $carrier;
    }

    /**
     * @param $shippingAddress
     * @return int
     */
    public function getAddressKey($shippingAddress)
    {

        $addressArray = [
            implode(',', $shippingAddress->getStreet()),
            $shippingAddress->getCity(),
            $shippingAddress->getPostcode(),
            $shippingAddress->getRegionId(),
            $shippingAddress->getCountryCode()
        ];
        $key = implode(',', $addressArray);

        return crc32($key);
    }

    public function adminShippingEnabled()
    {
        return $this->getConfigValue('carriers/shipper/custom_admin');
    }

    public function isMobile($data)
    {
        $uaSignatures = '/(nokia|iphone|android|motorola|^mot\-|softbank|foma|docomo|kddi|up\.browser|up\.link|'
            . 'htc|dopod|blazer|netfront|helio|hosin|huawei|novarra|CoolPad|webos|techfaith|palmsource|'
            . 'blackberry|alcatel|amoi|ktouch|nexian|samsung|^sam\-|s[cg]h|^lge|ericsson|philips|sagem|wellcom|'
            . 'bunjalloo|maui|symbian|smartphone|mmp|midp|wap|phone|windows ce|iemobile|^spice|^bird|^zte\-|longcos|'
            . 'pantech|gionee|^sie\-|portalmmm|jig\s browser|hiptop|^ucweb|^benq|haier|^lct|opera\s*mobi|opera\*mini|'
            . '320x320|240x320|176x220)/i';

        if (preg_match($uaSignatures, $data)) {
            return true;
        }
        $mobile_ua = strtolower(substr($data, 0, 4));
        $mobile_agents = array(
            'w3c ',
            'acs-',
            'alav',
            'alca',
            'amoi',
            'audi',
            'avan',
            'benq',
            'bird',
            'blac',
            'blaz',
            'brew',
            'cell',
            'cldc',
            'cmd-',
            'dang',
            'doco',
            'eric',
            'hipt',
            'inno',
            'ipaq',
            'java',
            'jigs',
            'kddi',
            'keji',
            'leno',
            'lg-c',
            'lg-d',
            'lg-g',
            'lge-',
            'maui',
            'maxo',
            'midp',
            'mits',
            'mmef',
            'mobi',
            'mot-',
            'moto',
            'mwbp',
            'nec-',
            'newt',
            'noki',
            'oper',
            'palm',
            'pana',
            'pant',
            'phil',
            'play',
            'port',
            'prox',
            'qwap',
            'sage',
            'sams',
            'sany',
            'sch-',
            'sec-',
            'send',
            'seri',
            'sgh-',
            'shar',
            'sie-',
            'siem',
            'smal',
            'smar',
            'sony',
            'sph-',
            'symb',
            't-mo',
            'teli',
            'tim-',
            'tosh',
            'tsm-',
            'upg1',
            'upsi',
            'vk-v',
            'voda',
            'wap-',
            'wapa',
            'wapi',
            'wapp',
            'wapr',
            'webc',
            'winw',
            'winw',
            'xda ',
            'xda-'
        );

        if (in_array($mobile_ua, $mobile_agents)) {
            return true;
        }
        return false;
    }
}
