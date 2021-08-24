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

namespace ShipperHQ\Shipper\Api\CreateListing;

interface ItemInterface
{
    /**
     * @return string
     */
    public function getItemId();

    /**
     * @param string $itemId
     */
    public function setItemId($itemId);

    /**
     * @return float
     */
    public function getQty();

    /**
     * @param float $qty
     */
    public function setQty($qty);
}
