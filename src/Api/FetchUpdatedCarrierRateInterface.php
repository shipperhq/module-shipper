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

namespace ShipperHQ\Shipper\Api;

interface FetchUpdatedCarrierRateInterface
{
    /**
     * @param string $order_number
     * @param string $carrierCodePattern regex string to match carrier codes against
     * @param \ShipperHQ\Shipper\Api\FetchUpdatedCarrierRate\ItemInterface[] $items
     * @return \ShipperHQ\Shipper\Api\FetchUpdatedCarrierRate\RateInterface[]
     */
    public function fetchRate($order_number, $carrierCodePattern, $items = []);
}
