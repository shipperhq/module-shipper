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
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace ShipperHQ\Shipper\Helper;

/**
 * Mapper for a data arrays tranformation
 */
class Module extends \Magento\Framework\App\Helper\AbstractHelper
{

    private $feature_set = [
      //  'dimship' => '',
        'ltl_freight' => 'ShipperHQ_Option',
       // 'validation' => '',
        'storepickup' => 'ShipperHQ_Pickup',
     //   'dropship' => '',
        'residential' => 'ShipperHQ_Option',
        'shipcal' => 'ShipperHQ_Calendar'
    ];

     private $modules = [
        'ShipperHQ' => 'ShipperHQ_Shipper',
        'Freight Options' => 'ShipperHQ_Option',
        'Date & Calendar' => 'ShipperHQ_Calendar',
        'In-store Pickup' => 'ShipperHQ_Pickup'
     ];

    const MODULES_MISSING = 'carriers/shipper/modules_missing';
    /**
     * @var  \Magento\Framework\Module\PackageInfoFactory
     */
    private $packageInfoFactory;

    /**
     * @param \Magento\Framework\Module\PackageInfoFactory $packageInfoFactory
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\Module\PackageInfoFactory $packageInfoFactory,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->packageInfoFactory = $packageInfoFactory;
        parent::__construct($context);
    }

    /**
     * Maps data by specified rules
     *
     * @param array $mapping
     * @param array $source
     * @return array
     */
    public function checkForMissingModules($config)
    {
        $target = [];
        $modules = $this->getInstalledModules();
        $featuresInstalled = explode('|', $config);
        foreach ($featuresInstalled as $feature) {
            if (isset($this->feature_set[$feature])) {
                $moduleRequired = $this->feature_set[$feature];
                //check module is present
                if (!in_array($moduleRequired, $modules))  {
                    $target[] = $moduleRequired;
                }
            }
        }

        return array_unique($target);
    }

    public function getInstalledModules($forDisplay = false)
    {
         $foundModules = [];
         $packageInfo = $this->packageInfoFactory->create();
         foreach ($this->modules as $displayModuleName => $moduleName) {
             if($name = $packageInfo->getPackageName($moduleName)) {
                 $name = $forDisplay ? $displayModuleName : $moduleName;
                 $foundModules[$name] = $packageInfo->getVersion($moduleName);
             }
        }
         return $foundModules;
     }
}
