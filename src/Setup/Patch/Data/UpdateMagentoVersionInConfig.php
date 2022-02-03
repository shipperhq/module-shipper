<?php
/*
 * ShipperHQ
 *
 * @category ShipperHQ
 * @package ShipperHQ_Shipper
 * @copyright Copyright (c) 2022 Zowta LTD and Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */
declare(strict_types=1);

namespace ShipperHQ\Shipper\Setup\Patch\Data;

use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\ProductMetadata;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class UpdateMagentoVersionInConfig implements DataPatchInterface
{
    /**
     * @var WriterInterface
     */
    private $configStorageWriter;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ProductMetadata                $productMetadata,
        WriterInterface $configStorageWriter
    ) {
        $this->productMetadata = $productMetadata;
        $this->configStorageWriter = $configStorageWriter;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '1.1.19';
    }

    /**
     * Do Upgrade
     * @return void
     */
    public function apply()
    {
        $this->configStorageWriter->save('carriers/shipper/magento_version', $this->productMetadata->getVersion());
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
