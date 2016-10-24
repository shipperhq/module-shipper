<?php
/**
 *
 * ShipperHQ Shipping Module
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * Shipper HQ Shipping
 *
 * @category ShipperHQ
 * @package ShipperHQ_Shipping_Carrier
 * @copyright Copyright (c) 2015 Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace ShipperHQ\Shipper\Ui\Component\Listing\Column;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Ui\Component\Listing\Columns\Date;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

/**
 * Class Address
 */
class DeliveryDate extends Date
{
    /**
     * @var \ShipperHQ\Shipper\Helper\CarrierGroup
     */
    protected $carrierGroupHelper;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param TimezoneInterface $timezone
     * @param \ShipperHQ\Shipper\Helper\CarrierGroup $carrierGroupHelper
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        TimezoneInterface $timezone,
        \ShipperHQ\Shipper\Helper\CarrierGroup $carrierGroupHelper,
        array $components = [],
        array $data = []
    ) {
        $this->carrierGroupHelper = $carrierGroupHelper;
        parent::__construct($context, $uiComponentFactory, $timezone, $components, $data);
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
                $item[$this->getData('name')]  = NULL;
                $orderGridDetails = $this->carrierGroupHelper->loadOrderGridDetailByOrderId($item["entity_id"]);
                foreach($orderGridDetails as $orderDetail) {
                   if($orderDetail->getDeliveryDate() != '') {
                        $deliveryDate = $orderDetail->getDeliveryDate();
                        $date = $this->timezone->date(new \DateTime($deliveryDate));
                        if (isset($this->getConfiguration()['timezone']) && !$this->getConfiguration()['timezone']) {
                            $date = new \DateTime($deliveryDate);
                        }
                        $item[$this->getData('name')] = $date->format('Y-m-d H:i:s');
                   }
                }

            }
        }

        return $dataSource;
    }
}
