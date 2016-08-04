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

class CarrierCache extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    const CACHE_TAG = 'ShipperHQ';

    /**
     * Array of quotes
     *
     * @var array
     */
    protected static $quotesCache = [];

    /**
     * @param Context $context
     */
    public function __construct(\Magento\Framework\App\CacheInterface $cache, \Magento\Framework\App\Helper\Context $context)
    {
        $this->cache = $cache;
        parent::__construct($context);
    }

    /**
     * Returns cache key for some request to carrier quotes service
     *
     * @param string|array $requestParams
     * @return string
     */
    protected function getQuotesCacheKey($requestParams, $carrierCode)
    {
        if (is_array($requestParams)) {
            $requestParams = implode(
                ',',
                array_merge([$carrierCode], array_keys($requestParams), $requestParams)
            );
        }
        return crc32($requestParams);
    }


    /**
     * Checks whether some request to rates have already been done, so we have cache for it
     * Used to reduce number of same requests done to carrier service during one session
     *
     * Returns cached response or null
     *
     * @param string|array $requestParams
     * @return null|string
     */
    public function getCachedQuotes($requestParams, $carrierCode)
    {
        $key = $this->getQuotesCacheKey($requestParams, $carrierCode);
        return isset(self::$quotesCache[$key]) ? self::$quotesCache[$key] : null;
   //     $cachedResult = $this->cache->load($key);
    //    return $cachedResult ? unserialize($cachedResult) : $cachedResult;
    }

    /**
     * Sets received carrier quotes to cache
     *
     * @param string|array $requestParams
     * @param string $response
     * @return $this
     */
    public function setCachedQuotes($requestParams, $response, $carrierCode)
    {
        $key = $this->getQuotesCacheKey($requestParams, $carrierCode);
      //  $this->cache->save(serialize($response), $key, [self::CACHE_TAG]);
        self::$quotesCache[$key] = $response;
        return $this;
    }

    public function cleanDownCachedRates()
    {
        $this->cache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, [self::CACHE_TAG]);

    }
}