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


namespace ShipperHQ\Shipper\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Address
 */
class CarrierType extends Column
{
    /**
     * @var \ShipperHQ\Shipper\Helper\CarrierGroup
     */
    private $carrierGroupHelper;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \ShipperHQ\Shipper\Helper\CarrierGroup $carrierGroupHelper
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \ShipperHQ\Shipper\Helper\CarrierGroup $carrierGroupHelper,
        array $components = [],
        array $data = []
    ) {
        $this->carrierGroupHelper = $carrierGroupHelper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $orderGridDetails = $this->carrierGroupHelper->loadOrderGridDetailByOrderId($item["entity_id"]);
                foreach ($orderGridDetails as $orderDetail) {
                    if ($orderDetail->getCarrierType()) {
                        $item[$this->getData('name')] = $orderDetail->getCarrierType();
                    }
                }
            }
        }

        return $dataSource;
    }
}
