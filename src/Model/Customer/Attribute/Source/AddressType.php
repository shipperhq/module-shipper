<?php
/**
 * ShipperHQ
 *
 * @category ShipperHQ
 * @package ShipperHQ\Shipper
 * @copyright Copyright (c) 2017 Zowta LTD and Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */


namespace ShipperHQ\Shipper\Model\Customer\Attribute\Source;

class AddressType extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * Address types
     */
    const SHQ_ADDRESS_TYPE_RESIDENTIAL = 'RESIDENTIAL';
    const SHQ_ADDRESS_TYPE_BUSINESS = 'BUSINESS';

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory
     */
    private $attrOptionFactory;

    /**
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory
    ) {
        $this->attrOptionFactory = $attrOptionFactory;
    }

    /**
     * Retrieve All options
     *
     * @return array
     */
    public function getAllOptions()
    {
        $arr = $this->toOptionArray();
        array_unshift($arr, ['value' => '', 'label' => __('--- Unknown ---')]);
        return $arr;
    }

    public function toOptionArray()
    {
        return [
            ['label' => __('Residential'), 'value' => self::SHQ_ADDRESS_TYPE_RESIDENTIAL],
            ['label' => __('Business'), 'value' => self::SHQ_ADDRESS_TYPE_BUSINESS]
        ];
    }
}
