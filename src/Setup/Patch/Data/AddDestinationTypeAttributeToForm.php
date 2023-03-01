<?php
/*
 * ShipperHQ
 *
 * @category ShipperHQ
 * @package ShipperHQ_Shipper
 * @copyright Copyright (c) 2023 Zowta LTD and Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */
declare(strict_types=1);

namespace ShipperHQ\Shipper\Setup\Patch\Data;

use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddDestinationTypeAttributeToForm implements DataPatchInterface
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
     * @var string[]
     */
    private array $attributes = [
        'destination_type',
        'validation_status',
    ];

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CustomerSetupFactory     $customerSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory $customerSetupFactory
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
     * SHQ23-295 Adds attributes to form. Moved out of InstallDestTypeAttributes to avoid potential deadlocking
     *
     * Special thanks to https://github.com/robaimes who discovered this issue and provided this patch
     *
     * @return void
     * @throws LocalizedException
     */
    public function apply()
    {
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);
        foreach ($this->attributes as $attributeCode) {
            // add attribute to form
            $attribute = $customerSetup->getEavConfig()->getAttribute('customer_address', $attributeCode);
            $attribute->setData('used_in_forms', [
                'adminhtml_customer_address',
            ]);
            $attribute->save();
        }
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
