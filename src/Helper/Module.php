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

use \Magento\Framework\Component\ComponentRegistrarInterface;
use \Magento\Framework\Filesystem\Directory\ReadFactory;

/**
 * Mapper for a data arrays tranformation
 */
class Module extends \Magento\Framework\App\Helper\AbstractHelper
{
    const INSTALLED = "installed";
    const VERSION = "version";
    const ENABLED = "enabled";
    const OUTPUT_ENABLED = "output_enabled";
    const MODULES_MISSING = 'carriers/shipper/modules_missing';
    /**
     * @var ComponentRegistrarInterface
     */
    protected $componentRegistrar;
    /**
     * @var ReadFactory
     */
    protected $readFactory;
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

    /**
     * Module constructor.
     * @param ComponentRegistrarInterface $componentRegistrar
     * @param ReadFactory $readFactory
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        ComponentRegistrarInterface $componentRegistrar,
        ReadFactory $readFactory,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->componentRegistrar = $componentRegistrar;
        $this->readFactory = $readFactory;
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
                if (!isset($modules[$moduleRequired])) {
                    $target[] = $moduleRequired;
                }
            }
        }

        return array_unique($target);
    }

    /**
     * @param bool $forDisplay
     * @return array
     */
    public function getInstalledModules($forDisplay = false)
    {
        $foundModules = [];
        foreach ($this->modules as $displayModuleName => $moduleName) {
            if ($moduleInfo = $this->getModuleInfo($moduleName)) {
                $name = $forDisplay ? $displayModuleName : $moduleName;
                $foundModules[$name] = $moduleInfo;
            }
        }
        return $foundModules;
    }

    /**
     * Get module information
     *
     * @param $moduleName
     * @return array|false
     */
    protected function getModuleInfo($moduleName)
    {
        $path = $this->componentRegistrar->getPath(
            \Magento\Framework\Component\ComponentRegistrar::MODULE,
            $moduleName
        );
        $data = false;
        if ($path) {
            $directoryRead = $this->readFactory->create($path);
            try {
                $composerJsonData = $directoryRead->readFile('composer.json');
                $data = json_decode($composerJsonData);
            } catch (\Exception $e) {
                $data = false;
            }
        }

        $info[self::INSTALLED] = $data !== false;
        $info[self::VERSION] = ($data && !empty($data->version)) ? $data->version : false;
        $info[self::ENABLED] = $this->_moduleManager->isEnabled($moduleName);
        $info[self::OUTPUT_ENABLED] = $this->_moduleManager->isOutputEnabled($moduleName);

        return $info;
    }
}
