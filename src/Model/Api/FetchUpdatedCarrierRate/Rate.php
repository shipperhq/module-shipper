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

namespace ShipperHQ\Shipper\Model\Api\FetchUpdatedCarrierRate;

use ShipperHQ\Shipper\Api\FetchUpdatedCarrierRate\RateInterface;

class Rate implements RateInterface
{

    /** @var string */
    private $carrierCode;

    /** @var string */
    private $carrierTitle;

    /** @var string */
    private $carrierType;

    /** @var string */
    private $methodCode;

    /** @var string */
    private $methodTitle;

    /** @var float */
    private $price;

    /** @var float */
    private $nypAmount;

    /**
     * Rate constructor.
     * @param string $carrier_code
     * @param string $carrier_title
     * @param string $carrier_type
     * @param string $method_code
     * @param string $method_title
     * @param float $price
     * @param float|null $nyp_amount
     */
    public function __construct(string $carrier_code, string $carrier_title, string $carrier_type, string $method_code, string $method_title, float $price, float $nyp_amount = null)
    {
        $this->carrierCode = $carrier_code;
        $this->carrierTitle = $carrier_title;
        $this->carrierType = $carrier_type;
        $this->methodCode = $method_code;
        $this->methodTitle = $method_title;
        $this->price = $price;
        $this->nypAmount = $nyp_amount;
    }

    /**
     * @return string
     */
    public function getCarrierCode(): string
    {
        return $this->carrierCode;
    }

    /**
     * @param string $carrierCode
     * @return Rate
     */
    public function setCarrierCode(string $carrierCode): Rate
    {
        $this->carrierCode = $carrierCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getCarrierTitle(): string
    {
        return $this->carrierTitle;
    }

    /**
     * @param string $carrierTitle
     * @return Rate
     */
    public function setCarrierTitle(string $carrierTitle): Rate
    {
        $this->carrierTitle = $carrierTitle;
        return $this;
    }

    /**
     * @return string
     */
    public function getCarrierType(): string
    {
        return $this->carrierType;
    }

    /**
     * @param string $carrierType
     * @return Rate
     */
    public function setCarrierType(string $carrierType)
    {
        $this->carrierType = $carrierType;
        return $this;
    }

    /**
     * @return string
     */
    public function getMethodCode(): string
    {
        return $this->methodCode;
    }

    /**
     * @param string $methodCode
     * @return Rate
     */
    public function setMethodCode(string $methodCode): Rate
    {
        $this->methodCode = $methodCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getMethodTitle(): string
    {
        return $this->methodTitle;
    }

    /**
     * @param string $methodTitle
     * @return Rate
     */
    public function setMethodTitle(string $methodTitle): Rate
    {
        $this->methodTitle = $methodTitle;
        return $this;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @param float $price
     * @return Rate
     */
    public function setPrice(float $price): Rate
    {
        $this->price = $price;
        return $this;
    }

    public function getNypAmount()
    {
        return $this->nypAmount;
    }

    public function setNypAmount(float $nypAmount)
    {
        $this->nypAmount = $nypAmount;
        return $this;
    }
}
