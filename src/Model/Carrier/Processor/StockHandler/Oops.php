<?php
/**
 * ShipperHQ
 *
 * @category ShipperHQ
 * @package ShipperHQ\Shipper
 * @copyright Copyright (c) 2015 Zowta LTD and Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */

namespace ShipperHQ\Shipper\Model\Carrier\Processor\StockHandler;

use ShipperHQ\Shipper\Helper\LogAssist;
use Magento\CatalogInventory\Api\StockRegistryInterface;

class Oops implements StockHandlerInterface
{
    private static $available_date = 'shipperhq_availability_date';

    public function __construct()
    {
    }

    public function getOriginInstock($origin, $item, $product)
    {
        return $this->getInstock($item, $product);
    }

    public function getInstock($item, $product)
    {
        return true;
    }

    public function getOriginInventoryCount($origin, $item, $product)
    {
        return $this->getInventoryCount($item, $product);
    }

    public function getInventoryCount($item, $product)
    {
        return null;
    }

    public function getOriginAvailabilityDate($origin, $item, $product)
    {
        return $this->getAvailabilityDate($item, $product);
    }

    public function getAvailabilityDate($item, $product)
    {
        return $product->getData(self::$available_date);
    }

    public function getLocationInstock($origin, $item, $product)
    {
        return $this->getInstock($item, $product);
    }

    public function getLocationInventoryCount($origin, $item, $product)
    {
        return $this->getInventoryCount($item, $product);
    }

    public function getLocationAvailabilityDate($origin, $item, $product)
    {
        return $this->getAvailabilityDate($item, $product);
    }
}
