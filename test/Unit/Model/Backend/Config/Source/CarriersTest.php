<?php

/*
 * Shipper HQ
 *
 * @category ShipperHQ
 * @package ShipperHQ_Shipper
 * @copyright Copyright (c) 2020 Zowta LTD and Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */

declare(strict_types=1);

namespace ShipperHQ\Shipper\Test\Unit\Model\Backend\Config\Source;

use Fooman\PhpunitBridge\BaseUnitTestCase;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Shipping\Model\Config;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;

class CarriersTest extends BaseUnitTestCase
{

    protected $scopeConfig;

    protected $shippingConfig;

    protected $carriersMock;

    protected $storeManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->scopeConfig = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->shippingConfig = $this->createMock(Config::class);
        $this->storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
    }

    public function testToOptionArray()
    {
        $store = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->storeManager->method('getStore')
            ->with()
            ->willReturn($store);

        $expectedArray = ['shipper' => ""];

        $this->shippingConfig->expects($this->once())
            ->method('getAllCarriers')
            ->willReturn($expectedArray);

        $helper = new ObjectManager($this);

        /** @var \ShipperHQ\Shipper\Model\Backend\Config\Source\Carriers $model */
        $model = $helper->getObject(
            'ShipperHQ\Shipper\Model\Backend\Config\Source\Carriers',
            ['storeManager' => $this->storeManager, 'config' => $this->scopeConfig, 'shippingConfig' => $this->shippingConfig]
        );
        $response = [];
        $response[] = ['value' => false, 'label' => 'No Carrier'];

        $this->assertEquals($response, $model->toOptionArray());
    }
}
