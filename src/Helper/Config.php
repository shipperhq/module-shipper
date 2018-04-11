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

namespace ShipperHQ\Shipper\Helper;

use ShipperHQ\Common\Helper\AbstractConfig;
use ShipperHQ\Common\Model\ConfigInterface;

/**
 * Class Config
 */
class Config extends AbstractConfig implements ConfigInterface
{
    /**
     * Get configuration data of carrier
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getCodes()
    {
        return [
            'date_format' => [
                'dd-mm-yyyy' => 'd-m-Y',
                'mm/dd/yyyy' => 'm/d/Y',
                'EEE dd-MM-yyyy' => 'D d-m-Y'
            ],
            'short_date_format' => [
                'dd-mm-yyyy' => 'd-m-Y',
                'mm/dd/yyyy' => 'm/d/Y',
                'EEE dd-MM-yyyy' => 'D d-m-Y'
            ],
            'datepicker_format' => [
                'dd-mm-yyyy' => 'dd-mm-yy',
                'mm/dd/yyyy' => 'mm/dd/yy',
                'EEE dd-MM-yyyy' => 'DD d-MM-yy'

            ],
            'zend_date_format' => [
                'dd-mm-yyyy' => 'dd-MM-y',
                'mm/dd/yyyy' => 'MM/dd/y',
                'EEE dd-MM-yyyy' => 'E d-M-y'
            ],
            'cldr_date_format' => [
                'en-US' => [
                    'yMd' => 'M/d/Y',
                    'yMMMd' => 'MMM d, Y',
                    'yMMMEd' => 'EEE, MMM d, Y',
                    'yMEd' => 'EEE, M/d/Y',
                    'MMMd' => 'MMM d',
                    'MMMEd' => 'EEE, MMM d',
                    'MEd' => 'EEE, M/d',
                    'Md' => 'M/d',
                    'yM' => 'M/Y',
                    'yMMM' => 'MMM Y',
                    'MMM' => 'MMM',
                    'E' => 'EEE',
                    'Ed' => 'd EEE',
                ],
                'en-GB' => [
                    'yMd' => 'd/M/Y',
                    'yMMMd' => 'd MMM Y',
                    'yMMMEd' => 'EEE, d MMM Y',
                    'yMEd' => 'EEE, d/M/Y',
                    'MMMd' => 'd MMM',
                    'MMMEd' => 'EEE, d MMM',
                    'MEd' => 'EEE, d/M',
                    'Md' => 'd/M',
                    'yM' => 'M/Y',
                    'yMMM' => 'MMM Y',
                    'MMM' => 'MMM',
                    'E' => 'EEE',
                    'Ed' => 'EEE d',
                ]
            ]
        ];
    }
}
