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

namespace ShipperHQ\Shipper\Model\Carrier\Processor;

use Magento\InventoryCatalog\Model\GetStockIdForCurrentWebsite;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;

class StockHandler
{
    private static $origin = 'shipperhq_warehouse';
    private static $location = 'shipperhq_location';
    private static $available_date = 'shipperhq_availability_date';

    /**
     * @var \ShipperHQ\Shipper\Helper\LogAssist
     */
    public $shipperLogger;

    /**
     * @var GetStockIdForCurrentWebsite
     */
    private $getStockIdForCurrentWebsite;

    /**
     * @var GetProductSalableQtyInterface
     */
    private $getProductSalableQty;


    /**
     * @var GetStockItemConfigurationInterface
     */
    private $getStockItemConfiguration;


    public function __construct(
        \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger,
        GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite,
        GetProductSalableQtyInterface $getProductSalableQty,
        GetStockItemConfigurationInterface $getStockItemConfiguration
    ) {

        $this->shipperLogger = $shipperLogger;
        $this->getStockIdForCurrentWebsite = $getStockIdForCurrentWebsite;
        $this->getProductSalableQty = $getProductSalableQty;
        $this->getStockItemConfiguration = $getStockItemConfiguration;
    }

    public function getOriginInstock($origin, $item, $product)
    {
        return $this->getInstock($item, $product);
    }

    public function getInstock($item, $product)
    {
        $stockId = $this->getStockIdForCurrentWebsite->execute();
        $stockItemConfiguration = $this->getStockItemConfiguration->execute($product->getSku(), $stockId);
        $isManageStock = $stockItemConfiguration->isManageStock();
        if (!$isManageStock) {
            return true;
        }

        $productSalableQty = $this->getProductSalableQty->execute($product->getSku(), $stockId);

        $inStock = $productSalableQty !== null ? $productSalableQty >= $item->getQty() : true;
        return $inStock;
    }

    public function getInventoryCount($item, $product)
    {

        $stockId = $this->getStockIdForCurrentWebsite->execute();
        $stockItemConfiguration = $this->getStockItemConfiguration->execute($product->getSku(), $stockId);
        $isManageStock = $stockItemConfiguration->isManageStock();
        if (!$isManageStock) {
            return null;
        }

        $productSalableQty = $this->getProductSalableQty->execute($product->getSku(), $stockId);

        return $productSalableQty;

    }

    public function getOriginInventoryCount($origin, $item, $product)
    {
        return $this->getInventoryCount($item, $product);
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
