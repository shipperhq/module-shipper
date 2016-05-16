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

namespace ShipperHQ\Shipper\Model;

class TestStockHandler extends \ShipperHQ\Shipper\Model\Carrier\Processor\StockHandler
{
    protected static $origin = 'shipperhq_warehouse';
    protected static $location = 'shipperhq_location';
    protected static $available_date = 'shipperhq_availability_date';

    /**
     * @var \ShipperHQ\Shipper\Helper\LogAssist
     */
    private $shipperLogger;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    function __construct(\ShipperHQ\Shipper\Helper\LogAssist $shipperLogger,
                         \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
    )
    {
        $this->shipperLogger = $shipperLogger;
        $this->stockRegistry = $stockRegistry;
    }

    public function getOriginInstock($origin, $item, $product)
    {
        $stockItem = $this->stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
        $inStock =  $stockItem->getQty() >= $item->getQty();
        $this->shipperLogger->postWarning('ShipperHQ','in our test di file ',  $inStock .' is the instock value');
        return $inStock;
    }

    public function getOriginInventoryCount($origin, $item, $product)
    {
        $stockItem = $this->stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
        return $stockItem->getQty();
    }

    public function getOriginAvailabilityDate($origin, $item, $product)
    {
        return $product->getData(self::$available_date);
    }

    public function getLocationInstock($origin, $item, $product)
    {
        $stockItem = $this->stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
        $inStock =  $stockItem->getQty() >= $item->getQty();
        return $inStock;
    }

    public function getLocationInventoryCount($origin, $item, $product)
    {
        $stockItem = $this->stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
        return $stockItem->getQty();
    }

    public function getLocationAvailabilityDate($origin, $item, $product)
    {
        return $product->getData(self::$available_date);
    }

}
