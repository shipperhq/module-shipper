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
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Quote\Setup\QuoteSetup;
use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Sales\Setup\SalesSetupFactory;

class UpdateIgnoreEmptyZipConfig implements DataPatchInterface
{
    /**
     * @var WriterInterface
     */
    private $configStorageWriter;

    /**
     * @param WriterInterface          $configStorageWriter
     */
    public function __construct(
        WriterInterface          $configStorageWriter
    ) {
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
        return '1.0.0';
    }

    /**
     * Do Upgrade
     * @return void
     */
    public function apply()
    {
        $this->configStorageWriter->save('carriers/shipper/ignore_empty_zip', 1);
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
