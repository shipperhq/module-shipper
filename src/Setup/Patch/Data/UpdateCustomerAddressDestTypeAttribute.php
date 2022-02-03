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

use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class UpdateCustomerAddressDestTypeAttribute implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;
    /**
     * Customer setup factory
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory     $customerSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [InstallDestTypeAttributes::class];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '1.0.16';
    }

    /**
     * Do Upgrade
     * @return void
     */
    public function apply()
    {
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $customerSetup->updateAttribute(
            'customer_address',
            'destination_type',
            [
                'source_model'   => 'ShipperHQ\Shipper\Model\Customer\Attribute\Source\AddressType',
                'frontend_input' => 'select'
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
