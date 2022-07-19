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

namespace ShipperHQ\Shipper\Model\Carrier\Processor\StockHandler;

use Exception;
use Magento\InventoryCatalog\Model\GetStockIdForCurrentWebsite;
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventoryCatalogApi\Model\GetSkusByProductIdsInterface;

class AdobeMSI implements StockHandlerInterface
{
    private static $available_date = 'shipperhq_availability_date';

    /**
     * @var GetStockIdForCurrentWebsite
     */
    private $getStockIdForCurrentWebsite;

    /**
     * @var GetProductSalableQtyInterface
     */
    private $getProductSalableQty;
    /**
     * @var GetSkusByProductIdsInterface
     */
    private $getSkusByProductIds;

    /**
     * @var GetStockItemConfigurationInterface
     */
    private $getStockItemConfiguration;

    /**
     * @var IsSourceItemManagementAllowedForProductTypeInterface
     */
    private $isSourceItemMgmtAllowedForProductType;


    /**
     * @param GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite
     * @param GetProductSalableQtyInterface $getProductSalableQty
     * @param GetStockItemConfigurationInterface $getStockItemConfiguration
     *
     * Can't use proper type hints on the constructor otherwise the DI compiler will flip out
     * See StockHandlerFactory
     */
    public function __construct(
        $getStockIdForCurrentWebsite,
        $getProductSalableQty,
        $getStockItemConfiguration,
        $getSkusByProductIds,
        $isSourceItemManagementAllowedForProductType
    ) {
        $this->getStockIdForCurrentWebsite = $getStockIdForCurrentWebsite;
        $this->getProductSalableQty = $getProductSalableQty;
        $this->getStockItemConfiguration = $getStockItemConfiguration;
        $this->getSkusByProductIds = $getSkusByProductIds;
        $this->isSourceItemMgmtAllowedForProductType = $isSourceItemManagementAllowedForProductType;
    }

    public function getOriginInstock($origin, $item, $product)
    {
        return $this->getInstock($item, $product);
    }

    /**
     * Gets the stock status of the item. If requested qty > qty in stock then returns false. Defaults to true on error
     *
     * @param $item
     * @param $product
     *
     * @return bool
     */
    public function getInstock($item, $product)
    {
        try {
            $stockId = $this->getStockIdForCurrentWebsite->execute();
            $stockItemConfiguration = $this->getStockItemConfiguration->execute($product->getSku(), $stockId);
            $isManageStock = $stockItemConfiguration->isManageStock();

            if (!$isManageStock || !$this->isSourceItemMgmtAllowedForProductType->execute($product->getTypeId())) {
                return true;
            }

            $sku = $this->getSkusByProductIds->execute([$product->getId()])[$product->getId()];
            $productSalableQty = $this->getProductSalableQty->execute($sku, $stockId);
            $inStock = $productSalableQty !== null ? $productSalableQty >= $item->getQty() : true;
        } catch (Exception $e) {
            $inStock = true;
        }

        return $inStock;
    }

    public function getOriginInventoryCount($origin, $item, $product)
    {
        return $this->getInventoryCount($item, $product);
    }

    /**
     * Gets the salable qty of a product
     *
     * @param $item
     * @param $product
     *
     * @return float|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getInventoryCount($item, $product)
    {
        try {
            $stockId = $this->getStockIdForCurrentWebsite->execute();
            $stockItemConfiguration = $this->getStockItemConfiguration->execute($product->getSku(), $stockId);
            $isManageStock = $stockItemConfiguration->isManageStock();

            if (!$isManageStock || !$this->isSourceItemMgmtAllowedForProductType->execute($product->getTypeId())) {
                return null;
            }

            $sku = $this->getSkusByProductIds->execute([$product->getId()])[$product->getId()];
            $productSalableQty = $this->getProductSalableQty->execute($sku, $stockId);
        } catch (Exception $e) {
            $productSalableQty = null;
        }

        return $productSalableQty;
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
