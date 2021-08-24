<?php
/**
 * Shipper HQ
 *
 * @category ShipperHQ
 * @package ShipperHQ_Shipper
 * @copyright Copyright (c) 2019 Zowta LTD and Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */
declare(strict_types=1);

namespace ShipperHQ\Shipper\Api\FetchUpdatedCarrierRate;

interface RateInterface
{

    /**
     * @return string
     */
    public function getCarrierCode();

    /**
     * @param string $carrierCode
     * @return RateInterface
     */
    public function setCarrierCode(string $carrierCode);

    /**
     * @return string
     */
    public function getCarrierTitle();

    /**
     * @param string $carrierTitle
     * @return RateInterface
     */
    public function setCarrierTitle(string $carrierTitle);

    /**
     * @return string
     */
    public function getCarrierType();

    /**
     * @param string $carrierType
     * @return RateInterface
     */
    public function setCarrierType(string $carrierType);

    /**
     * @return string
     */
    public function getMethodCode();

    /**
     * @param string $methodCode
     * @return RateInterface
     */
    public function setMethodCode(string $methodCode);

    /**
     * @return string
     */
    public function getMethodTitle();

    /**
     * @param string $methodTitle
     * @return RateInterface
     */
    public function setMethodTitle(string $methodTitle);

    /**
     * @return float
     */
    public function getPrice();

    /**
     * @param float $price
     * @return RateInterface
     */
    public function setPrice(float $price);

    /**
     * @return float
     */
    public function getNypAmount();

    /**
     * @param float $nypAmount
     * @return RateInterface
     */
    public function setNypAmount(float $nypAmount);
}
