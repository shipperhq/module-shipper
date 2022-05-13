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

use Magento\Framework\Locale\Bundle\DataBundle;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Address
 */
class DispatchDate extends Column
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
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var DataBundle
     */
    private $dataBundle;

    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * @var string
     */
    private $locale;

    /**
     * @param \ShipperHQ\Shipper\Helper\CarrierGroup      $carrierGroupHelper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param ContextInterface                            $context
     * @param UiComponentFactory                          $uiComponentFactory
     * @param TimezoneInterface                           $timezone
     * @param ResolverInterface|null                      $localeResolver
     * @param DataBundle|null                             $dataBundle
     * @param array                                       $components
     * @param array                                       $data
     */
    public function __construct(
        \ShipperHQ\Shipper\Helper\CarrierGroup $carrierGroupHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        TimezoneInterface $timezone,
        ResolverInterface $localeResolver,
        DataBundle $dataBundle,
        array $components = [],
        array $data = []
    ) {
        $this->carrierGroupHelper = $carrierGroupHelper;
        $this->date = $date;
        $this->timezone = $timezone;
        $this->localeResolver = $localeResolver;
        $this->dataBundle = $dataBundle;
        $this->locale = $this->localeResolver->getLocale();
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepare()
    {
        $config = $this->getData('config');
        if (isset($config['filter'])) {
            $config['filter'] = [
                'filterType' => 'dateRange',
                'templates' => [
                    'date' => [
                        'options' => [
                            // MNB-764 Always use the store date format. M2 won't filter reliably using MMM dd, YYYY
                            'dateFormat' => $this->timezone->getDateFormatWithLongYear()
                        ]
                    ]
                ]
            ];
        }

        $localeData = $this->dataBundle->get($this->locale);
        /** @var \ResourceBundle $monthsData */
        $monthsData = $localeData['calendar']['gregorian']['monthNames'];
        $months = array_values(iterator_to_array($monthsData['format']['wide']));
        $monthsShort = array_values(
            iterator_to_array(
                null !== $monthsData->get('format')->get('abbreviated')
                    ? $monthsData['format']['abbreviated']
                    : $monthsData['format']['wide']
            )
        );

        $config['storeLocale'] = $this->locale;
        $config['calendarConfig'] = [
            'months' => $months,
            'monthsShort' => $monthsShort,
        ];
        if (!isset($config['dateFormat'])) {
            $config['dateFormat'] = $this->timezone->getDateTimeFormat(\IntlDateFormatter::MEDIUM);
        }
        $this->setData('config', $config);

        parent::prepare();
    }

    /**
     * @inheritdoc
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item[$this->getData('name')] = null;
                $orderGridDetails = $this->carrierGroupHelper->loadOrderGridDetailByOrderId($item["entity_id"]);
                foreach ($orderGridDetails as $orderDetail) {
                    if ($orderDetail->getDispatchDate() != '') {
                        $dispatchDate = $orderDetail->getDispatchDate();

                        $date = $this->timezone->date(new \DateTime($dispatchDate), null, false, false);

                        $item[$this->getData('name')] = $date->format('Y-m-d');
                    }
                }
            }
        }

        return $dataSource;
    }
}
