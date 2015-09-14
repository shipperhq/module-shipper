<?php
/* ExtName
 *
 * User        karen
 * Date        9/13/15
 * Time        12:18 PM
 * @category   Webshopapps
 * @package    Webshopapps_ExtnName
 * @copyright   Copyright (c) 2015 Zowta Ltd (http://www.WebShopApps.com)
 *              Copyright, 2015, Zowta, LLC - US license
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */

namespace ShipperHQ\Shipper\Helper;


class CarrierCache extends \Magento\Framework\App\Helper\AbstractHelper
{


    /**
     * Array of quotes
     *
     * @var array
     */
    protected static $quotesCache = [];

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
        self::$quotesCache[$key] = $response;
        return $this;
    }
}