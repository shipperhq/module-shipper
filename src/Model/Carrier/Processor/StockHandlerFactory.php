<?php

namespace ShipperHQ\Shipper\Model\Carrier\Processor;

use Magento\Framework\ObjectManagerInterface;
use ShipperHQ\Shipper\Model\Carrier\Processor\StockHandler\StockHandlerInterface;
use Magento\Framework\Module\Manager;

class StockHandlerFactory
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    private $objectManager = null;

    /**
     * Factory constructor
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Manager $moduleManager
    )
    {
        $this->objectManager = $objectManager;
        $this->moduleManager = $moduleManager;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return StockHandlerInterface
     */
    public function create(array $data = []): StockHandlerInterface
    {
        // TODO: We could do some sort of registry system here instead of hardcoding and then customers could BYOInventory

        if ($this->isAdobeMSIInstalled()) {
            $injection = [
                "getStockIdForCurrentWebsite" => $this->objectManager->create('Magento\InventoryCatalog\Model\GetStockIdForCurrentWebsite', []),
                "getProductSalableQty" => $this->objectManager->create('Magento\InventorySalesApi\Api\GetProductSalableQtyInterface', []),
                "getStockItemConfiguration" => $this->objectManager->create('Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface', []),
                "getSkusByProductIds" => $this->objectManager->create('Magento\InventoryCatalogApi\Model\GetSkusByProductIdsInterface', []),
                "isSourceItemManagementAllowedForProductType" => $this->objectManager->create('Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface', [])
            ];
            return $this->objectManager->create('\ShipperHQ\Shipper\Model\Carrier\Processor\StockHandler\AdobeMSI', $injection);
        } elseif ($this->isLegacyStockRegistryInstalled()) {
            $injection = [
                "stockRegistry" => $this->objectManager->create('Magento\CatalogInventory\Api\StockRegistryInterface', []),
            ];
            return $this->objectManager->create('\ShipperHQ\Shipper\Model\Carrier\Processor\StockHandler\LegacyStockRegistry', $injection);
        }
        return $this->objectManager->create('\ShipperHQ\Shipper\Model\Carrier\Processor\StockHandler\Oops', $data);
    }

    /**
     * @return bool
     */
    private function isAdobeMSIInstalled(): bool
    {
        return class_exists('Magento\InventoryCatalog\Model\GetStockIdForCurrentWebsite')
            && $this->moduleManager->isEnabled('Magento_InventoryCatalog');
    }

    /**
     * @return bool
     */
    private function isLegacyStockRegistryInstalled(): bool
    {
        return interface_exists('Magento\CatalogInventory\Api\StockRegistryInterface')
            && $this->moduleManager->isEnabled('Magento_CatalogInventory');
    }
}
