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

namespace ShipperHQ\Shipper\Test\Unit\Model\Backend\Config\Source;

use Fooman\PhpunitBridge\BaseUnitTestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class EnvironmentScopeTest extends BaseUnitTestCase
{
    /**
     * @var \ShipperHQ\Shipper\Model\Backend\Config\Source\EnvironmentScope
     */
    private $model;

    /** @var ObjectManagerHelper */
    private $objectManagerHelper;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->model = $this->objectManagerHelper->getObject(
            'ShipperHQ\Shipper\Model\Backend\Config\Source\EnvironmentScope'
        );
    }

    public function testToOptionArray()
    {
        $expected = [
            [
                'value' => 'LIVE',
                'label' => __('Live')
            ],
            [
                'value' => 'DEVELOPMENT',
                'label' => __('Development')
            ],
            [
                'value' => 'TEST',
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
