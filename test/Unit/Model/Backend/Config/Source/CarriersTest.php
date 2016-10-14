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

//    protected function setUp()
//    {
//        $this->objectManagerHelper = new ObjectManagerHelper($this);
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
//
//        $this->model = $this->objectManagerHelper->getObject('ShipperHQ\Shipper\Model\Backend\Config\Source\Carriers',
//            ['shippingConfig' =>$config]);
//    }



    public function testToOptionArray()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $carrier = $this->getMock(
            'Magento\OfflineShipping\Model\Carrier\Freeshipping',
            ['isTrackingAvailable', 'getConfigData'],
            [],
            '',
            false
        );

        $config = $this->getMock('Magento\Shipping\Model\Config', ['getAllCarriers'], [], '', false);
        $config->expects(
            $this->once()
        )->method(
            'getAllCarriers'
        )->with(
            null
        )->will(
            $this->returnValue(['free' => $carrier])
        );

        $shipHelper = $this->getMock('\ShipperHQ\Shipper\Helper\Data', ['getConfigValue'], [], '', false);
        $shipHelper->expects(
            $this->atLeastOnce()
        )->method(
                'getConfigValue'
            )->will(
                $this->returnValue('configValue')
            );



        /** @var 'ShipperHQ\Shipper\Model\Backend\Config\Source\Carriers' $model */
        $model = $helper->getObject(
            'ShipperHQ\Shipper\Model\Backend\Config\Source\Carriers',
            ['shippingConfig' =>$config, 'shipperDataHelper' => $shipHelper]
        );
        $response = [];
        $response[] =  ['value' => false, 'label' => 'No Carrier'];
        $response[] =  ['value' => 'free', 'label' => 'configValue'];

        $this->assertEquals($response, $model->toOptionArray());
    }
}
