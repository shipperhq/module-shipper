<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace ShipperHQ\Shipper\Test\Unit\Model\Backend\Config\Source;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class CarriersTest extends \PHPUnit_Framework_TestCase
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
//        $carrier = $this->getMock(
//            'Magento\OfflineShipping\Model\Carrier\Freeshipping',
//            ['isTrackingAvailable', 'getConfigData'],
//            [],
//            '',
//            false
//        );
//        $carrier->expects(
//            $this->once()
//        )->method(
//                'getConfigData'
//            )->with(
//                'title'
//            )->will(
//                $this->returnValue('configdata')
//            );
//
//
//        $config = $this->getMock('Magento\Shipping\Model\Config', ['getAllCarriers'], [], '', false);
//        $config->expects(
//            $this->once()
//        )->method(
//                'getAllCarriers'
//            )->will(
//                $this->returnValue(['free' => $carrier])
//            );

        $this->model = $this->objectManagerHelper->getObject('ShipperHQ\Shipper\Model\Backend\Config\Source\Carriers'/*,
            ['shippingConfig' =>$config]*/);
    }



    public function testToOptionArray()
    {
//        $this->assertEquals(
//            [
//                ['value' => 'free', 'label' => 'configdata'],
//                ['value' => false, 'label' => 'No Carrier'],
//            ],
//            $this->model->toOptionArray()
//        );
        $this->assertNotEmpty($this->model->toOptionArray());
    }
}
