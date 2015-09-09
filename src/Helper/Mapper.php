<?php
namespace ShipperHQ\Shipper\Helper;

/**
 * Mapper for a data arrays tranformation
 */
class Mapper
    extends  \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Maps data by specified rules
     * 
     * @param array $mapping
     * @param array $source
     * @return array
     */
    public function map($mapping, $source)
    {
        $target = array();
        foreach ($mapping as $targetField => $sourceField) {
            if (is_string($sourceField)) {
                if (strpos($sourceField, '/') !== false) {
                    $fields = explode('/', $sourceField);
                    $value = $source;
                    while ($fields) {
                        $field = array_shift($fields);
                        if (isset($value[$field])) {
                            $value = $value[$field];
                        } else {
                            $value = null;
                            break;
                        }
                    }
                    $target[$targetField] = $value;
                } else {
                    $target[$targetField] = $source[$sourceField];
                }
            } elseif (is_array($sourceField)) {
                list($field, $defaultValue) = $sourceField;
                $target[$targetField] = (isset($source[$field]) ? $source[$field] : $defaultValue);
            } elseif ($sourceField instanceof Closure) {
                $mapping = is_object($source) && is_callable($source);
                $target[$targetField] = $mapping;
            }
        }
        
        return $target;
    }
}