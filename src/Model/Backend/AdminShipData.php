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

declare(strict_types=1);

namespace ShipperHQ\Shipper\Model\Backend;

/**
 * Class ShipAdminData
 * DTO Class - do not create a resource model
 * @package ShipperHQ\Shipper\Model\Backend
 */
class AdminShipData
{
    /** @var string */
    private $customCarrier;

    /** @var float */
    private $customPrice;

    /**
     * @return string
     */
    public function getCustomCarrier(): string
    {
        return $this->customCarrier;
    }

    /**
     * @param string $customCarrier
     */
    public function setCustomCarrier(string $customCarrier)
    {
        $this->customCarrier = $customCarrier;
    }

    /**
     * @return float
     */
    public function getCustomPrice(): float
    {
        return $this->customPrice;
    }

    /**
     * @param float $customPrice
     */
    public function setCustomPrice(float $customPrice)
    {
        $this->customPrice = $customPrice;
    }
}
