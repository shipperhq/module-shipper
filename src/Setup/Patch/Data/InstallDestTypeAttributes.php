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
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class InstallDestTypeAttributes implements DataPatchInterface
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
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '1.0.5';
    }

    /**
     * Do Upgrade
     * @return void
     */
    public function apply()
    {
        /** @var QuoteSetup $quoteSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $existingDestTypeAttribute = $customerSetup->getAttribute('customer_address', 'destination_type');

        if (empty($existingDestTypeAttribute)) {
            $destinationTypeAddressAttr = [
                'type'     => Table::TYPE_TEXT,
                'label'    => 'Address Type',
                'input'    => 'select',
                'source'   => 'ShipperHQ\Shipper\Model\Customer\Attribute\Source\AddressType',
                'system'   => 0, // <-- important, otherwise values aren't saved.
                // @see Magento\Customer\Model\Metadata\AddressMetadata::getCustomAttributesMetadata()
                //            'visible' => false,
                'required' => false,
                'position' => 100,
                'comment'  => 'ShipperHQ Address Type'
            ];
            $customerSetup->addAttribute('customer_address', 'destination_type', $destinationTypeAddressAttr);

            // add attribute to form
            $attribute = $customerSetup->getEavConfig()->getAttribute('customer_address', 'destination_type');
            $attribute->setData('used_in_forms', ['adminhtml_customer_address']);
            $attribute->save();
        }

        $existingvalidationStatusAttribute = $customerSetup->getAttribute('customer_address', 'validation_status');

        if (empty($existingvalidationStatusAttribute)) {

            $validationStatusAddressAttr = [
                'type'     => Table::TYPE_TEXT,
                'label'    => 'Address Validation',
                'system'   => 0, // <-- important, otherwise values aren't saved.
                // @see Magento\Customer\Model\Metadata\AddressMetadata::getCustomAttributesMetadata()
                //            'visible' => false,
                'required' => false,
                'position' => 101,
                'comment'  => 'ShipperHQ Address Validation Status'
            ];
            $customerSetup->addAttribute('customer_address', 'validation_status', $validationStatusAddressAttr);

            // add attribute to form
            /** @var  $attribute */
            $attribute = $customerSetup->getEavConfig()->getAttribute('customer_address', 'validation_status');
            $attribute->setData('used_in_forms', ['adminhtml_customer_address']);
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
