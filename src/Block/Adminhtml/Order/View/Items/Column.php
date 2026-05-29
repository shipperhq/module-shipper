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


namespace ShipperHQ\Shipper\Block\Adminhtml\Order\View\Items;

use Magento\Sales\Block\Adminhtml\Items\Column\DefaultColumn;

class Column extends DefaultColumn
{
    /**
     * @var \ShipperHQ\Shipper\Helper\CarrierGroup
     */
    private $carrierGroupHelper;

    private $itemDetail = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Helper\Admin $adminHelper
     * @param array $data
     */
    public function __construct(
        \ShipperHQ\Shipper\Helper\CarrierGroup $carrierGroupHelper,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\Product\OptionFactory $optionFactory,
        array $data = []
    ) {
        $this->carrierGroupHelper = $carrierGroupHelper;
        parent::__construct($context, $stockRegistry, $stockConfiguration, $registry, $optionFactory, $data);
    }

    public function getCarrierGroup($itemId)
    {
        return $this->getDetail($itemId, 'carrier_group');
    }

    private function getDetail($itemId, $detail)
    {
        $result = '';
        $orderItemDetails = $this->getItemDetail($itemId);
        if ($orderItemDetails) {
            foreach ($orderItemDetails as $itemDetail) {
                if (isset($itemDetail[$detail])) {
                    $result = $itemDetail[$detail];
                }
            }
        }
        return $result;
    }

    private function getItemDetail($itemId)
    {
        $this->itemDetail = $this->carrierGroupHelper->loadOrderItemDetailByOrderItemId($itemId);
        return $this->itemDetail;
    }

    public function getCarrierGroupShipping($itemId)
    {
        return $this->getDetail($itemId, 'carrier_group_shipping');
    }
}
