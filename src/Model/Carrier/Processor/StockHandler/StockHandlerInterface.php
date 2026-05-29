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

namespace ShipperHQ\Shipper\Model\Carrier\Processor\StockHandler;

interface StockHandlerInterface
{
    public function getOriginInstock($origin, $item, $product);

    public function getInstock($item, $product);

    public function getOriginInventoryCount($origin, $item, $product);

    public function getInventoryCount($item, $product);

    public function getOriginAvailabilityDate($origin, $item, $product);

    public function getAvailabilityDate($item, $product);

    public function getLocationInstock($origin, $item, $product);

    public function getLocationInventoryCount($origin, $item, $product);

    public function getLocationAvailabilityDate($origin, $item, $product);
}
