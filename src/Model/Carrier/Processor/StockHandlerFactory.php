<?php

namespace ShipperHQ\Shipper\Model\Carrier\Processor;

use Magento\Framework\Module\Manager;
use Magento\Framework\ObjectManagerInterface;
use ShipperHQ\Shipper\Model\Carrier\Processor\StockHandler\StockHandlerInterface;

class StockHandlerFactory
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    private $objectManager = null;

    /**
     * @var Manager
     */
    private Manager $moduleManager;

    /**
     * Factory constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param Manager                $moduleManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Manager $moduleManager
    ) {
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

        // MNB-3173 Added check for MSI being configured as well as installed
        if ($this->isAdobeMSIInstalled() && $this->isAdobeMSIConfigured()) {
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

    private function isAdobeMSIConfigured(): bool
    {
        if (interface_exists('Magento\InventoryApi\Api\SourceRepositoryInterface') &&
            interface_exists('Magento\InventoryApi\Api\StockRepositoryInterface')) {

            // Unless there's more than one source repo MSI is not configured
            $sourceRepo = $this->objectManager->create('\Magento\InventoryApi\Api\SourceRepositoryInterface');
            $sourceRepoConfigured = $sourceRepo->getList()->getTotalCount() > 1;

            // Unless there's more than one stock repo MSI is not configured
            $stockRepo = $this->objectManager->create('\Magento\InventoryApi\Api\StockRepositoryInterface');
            $stockRepoConfigured = $stockRepo->getList()->getTotalCount() > 1;

            return $sourceRepoConfigured && $stockRepoConfigured;
        }

        return false;
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
