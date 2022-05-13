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
 * @copyright Copyright (c) 2022 Zowta LLC (http://www.ShipperHQ.com)
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
