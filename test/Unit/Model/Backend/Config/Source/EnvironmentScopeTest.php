<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace ShipperHQ\Shipper\Test\Unit\Model\Backend\Config\Source;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class EnvironmentScopeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \ShipperHQ\Shipper\Model\Backend\Config\Source\EnvironmentScope
     */
    protected $model;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->model = $this->objectManagerHelper->getObject('ShipperHQ\Shipper\Model\Backend\Config\Source\EnvironmentScope');
    }

    public function testToOptionArray()
    {
        $expected = [
        [
            'value' =>  'LIVE',
            'label' => __('Live')
        ],
        [
            'value' =>   'DEVELOPMENT',
            'label' => __('Development')
        ],
        [
            'value' =>   'TEST',
            'label' => __('Test')
        ],
        [
            'value' => 'INTEGRATION',
            'label' => __('Integration')
        ],
    ];

        $this->assertEquals($expected, $this->model->toOptionArray());
    }
}
