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
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Address
 */
class DeliveryDate extends Column
{
    /**
     * @var \ShipperHQ\Shipper\Helper\CarrierGroup
     */
    private $carrierGroupHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $date;

    /**
     *  \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;
    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @param \ShipperHQ\Shipper\Helper\CarrierGroup $carrierGroupHelper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param TimezoneInterface $timezone
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \ShipperHQ\Shipper\Helper\CarrierGroup $carrierGroupHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        TimezoneInterface $timezone,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        array $components = [],
        array $data = []
    ) {
        $this->carrierGroupHelper = $carrierGroupHelper;
        $this->date = $date;
        $this->productMetadata = $productMetadata;
        $this->timezone = $timezone;
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
                $item[$this->getData('name')] = null;
                $orderGridDetails = $this->carrierGroupHelper->loadOrderGridDetailByOrderId($item["entity_id"]);
                foreach ($orderGridDetails as $orderDetail) {
                    if ($orderDetail->getDeliveryDate() != '') {
                        $deliveryDate = $orderDetail->getDeliveryDate();
                        $date = $this->timezone->date($this->date->timestamp($deliveryDate), null, false);
                        if (isset($this->getConfiguration()['timezone']) && !$this->getConfiguration()['timezone']) {
                            $date = $this->timezone->date($this->date->timestamp($deliveryDate), null, false);
                        }
                        $item[$this->getData('name')] = $date->format('Y-m-d H:i:s');
                    }
                }
            }
        }

        return $dataSource;
    }
}
