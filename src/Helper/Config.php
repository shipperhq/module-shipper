<?php
/**
 * ShipperHQ
 *
 * @category ShipperHQ
 * @package ShipperHQ\Shipper
 * @copyright Copyright (c) 2015 Zowta LTD and Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */


namespace ShipperHQ\Shipper\Helper;

use Magento\Framework\App\Cache\Manager;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\MutableScopeConfig;
use ShipperHQ\Common\Helper\AbstractConfig;
use ShipperHQ\Common\Model\ConfigInterface;

/**
 * Class Config
 */
class Config extends AbstractConfig implements ConfigInterface
{
    /**
     * Copy of the config loaded into this process's memory
     * @var MutableScopeConfig
     */
    private $localConfig;

    /**
     * Class used to write persistent config
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * Cache manager used to flush cache
     * @var Manager
     */
    private $cacheManager;

    /** @var boolean */
    private $isConfigCacheCleanScheduled = false;

    /**
     * Config constructor.
     *
     * @param MutableScopeConfig $localConfig
     * @param WriterInterface             $configWriter
     * @param Manager                     $cacheManager
     */
    public function __construct(
        MutableScopeConfig $localConfig,
        WriterInterface $configWriter,
        Manager $cacheManager
    ) {
        $this->localConfig = $localConfig;
        $this->configWriter = $configWriter;
        $this->cacheManager = $cacheManager;
    }


    /**
     * Get configuration data of carrier
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getCodes()
    {
        return [
            'date_format' => [
                'dd-mm-yyyy' => 'd-m-Y',
                'mm/dd/yyyy' => 'm/d/Y',
                'EEE dd-MM-yyyy' => 'D d-m-Y'
            ],
            'short_date_format' => [
                'dd-mm-yyyy' => 'd-m-Y',
                'mm/dd/yyyy' => 'm/d/Y',
                'EEE dd-MM-yyyy' => 'D d-m-Y'
            ],
            'datepicker_format' => [
                'dd-mm-yyyy' => 'dd-mm-yy',
                'mm/dd/yyyy' => 'mm/dd/yy',
                'EEE dd-MM-yyyy' => 'DD d-MM-yy'

            ],
            'zend_date_format' => [
                'dd-mm-yyyy' => 'dd-MM-y',
                'mm/dd/yyyy' => 'MM/dd/y',
                'EEE dd-MM-yyyy' => 'E d-M-y'
            ],
            'cldr_date_format' => [
                'en-US' => [
                    'yMd' => 'M/d/Y',
                    'yMMMd' => 'MMM d, Y',
                    'yMMMEd' => 'EEE, MMM d, Y',
                    'yMEd' => 'EEE, M/d/Y',
                    'MMMd' => 'MMM d',
                    'MMMEd' => 'EEE, MMM d',
                    'MEd' => 'EEE, M/d',
                    'Md' => 'M/d',
                    'yM' => 'M/Y',
                    'yMMM' => 'MMM Y',
                    'MMM' => 'MMM',
                    'E' => 'EEE',
                    'Ed' => 'd EEE',
                ],
                'en-GB' => [
                    'yMd' => 'd/M/Y',
                    'yMMMd' => 'd MMM Y',
                    'yMMMEd' => 'EEE, d MMM Y',
                    'yMEd' => 'EEE, d/M/Y',
                    'MMMd' => 'd MMM',
                    'MMMEd' => 'EEE, d MMM',
                    'MEd' => 'EEE, d/M',
                    'Md' => 'd/M',
                    'yM' => 'M/Y',
                    'yMMM' => 'MMM Y',
                    'MMM' => 'MMM',
                    'E' => 'EEE',
                    'Ed' => 'EEE d',
                ]
            ]
        ];
    }

    /**
     * Wraps WriterInterface->save() but also schedules the config cache to be cleaned
     *
     * @param $path
     * @param $value
     * @param null $scope
     * @param null $scopeId
     */
    public function writeToConfig($path, $value, $scope = null, $scopeId = null)
    {
        $currentValue = $this->getConfigValue(...array_filter([$path, $scope, $scopeId]));
        if ($value === $currentValue) {
            return;
        }

        $args = array_filter([$path, $value, $scope, $scopeId], function ($e) {
            return ($e !== null);
        });
        $this->configWriter->save(...$args);
        $this->localConfig->setValue(...$args);
        $this->scheduleConfigCacheClean();
    }

    /**
     * Wraps WriterInterface->delete() but also schedules the config cache to be cleaned
     *
     * @param $path
     * @param null $scope
     * @param null $scopeId
     */
    public function deleteFromConfig($path, $scope = null, $scopeId = null)
    {
        //        $currentValue = $this->getConfigValue(...array_filter([$path, $scope, $scopeId]));
        //        if ($value === $currentValue) {
        //            return;
        //        }
        // TODO: Short circuit if delete is not needed

        $args = array_filter([$path, $scope, $scopeId]);
        $this->configWriter->delete(...$args);
        array_splice($args, 1, 0, [null]); // Insert a null Value field at position 1, move the other elements down
        $this->localConfig->setValue(...$args); // Be setting to null we indicate value should be fetched again
        $this->scheduleConfigCacheClean();
    }

    /**
     * Wraps MutableScopeConfigInterface->getValue except allows for smartly invalidating config cache
     * @param $path
     * @param null $scopeType
     * @param null $scopeCode
     * @return mixed
     */
    public function getConfigValue($path, $scopeType = null, $scopeCode = null)
    {
        $resolvedScopeType = $scopeType ?? ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
        // ENG26-1125: MutableScopeConfig::setValue stores under '' when scopeCode is null, but
        // getValue looks up null directly — PHP 8.5 null-as-array-offset deprecation on line 32.
        // Only apply '' for default scope; for website/store scopes, null must remain null so
        // ScopeCodeResolver in parent Config::getValue resolves the scope correctly.
        $resolvedScopeCode = ($resolvedScopeType === ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
            ? ($scopeCode ?? '')
            : $scopeCode;
        return $this->localConfig->getValue($path, $resolvedScopeType, $resolvedScopeCode);
    }

    /**
     * Try to use sparingly. Will flush the cache immediately if there are uncommitted changes.
     */
    public function runScheduledCleaningNow()
    {
        $this->cleanConfigCacheIfScheduled();
    }

    /**
     * @return Config
     */
    private function scheduleConfigCacheClean(): Config
    {
        $this->isConfigCacheCleanScheduled = true;
        return $this;
    }

    /**
     * Cleans the config if a change has been made since the last read
     * @return Config
     */
    private function cleanConfigCacheIfScheduled(): Config
    {
        if ($this->isConfigCacheCleanScheduled) {
            $this->localConfig->clean();
            $this->cacheManager->clean(["config"]);
            $this->isConfigCacheCleanScheduled = false;
        }
        return $this;
    }
}
