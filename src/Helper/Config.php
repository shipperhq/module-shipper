<?php
/* ExtName
 *
 * User        karen
 * Date        8/9/15
 * Time        2:31 PM
 * @category   Webshopapps
 * @package    Webshopapps_ExtnName
 * @copyright   Copyright (c) 2014 Zowta Ltd (http://www.WebShopApps.com)
 *              Copyright, 2014, Zowta, LLC - US license
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */

namespace ShipperHQ\Shipper\Helper;

use WebShopApps\Common\Model\ConfigInterface;
use WebShopApps\Common\Helper\AbstractConfig;

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
            'date_format'   => [
                'dd-mm-yyyy'    	    => 'd-m-Y',
                'mm/dd/yyyy'    	    => 'm/d/Y',
                'EEE dd-MM-yyyy'        => 'D d-m-Y'
            ],
            'short_date_format'   => [
                'dd-mm-yyyy'   	    => 'd-m-Y',
                'mm/dd/yyyy'    	    => 'm/d/Y',
                'EEE dd-MM-yyyy'        => 'D d-m-Y'
            ],
            'datepicker_format' => [
                'dd-mm-yyyy'         => 'dd-mm-yy',
                'mm/dd/yyyy'         => 'mm/dd/yy',
                'EEE dd-MM-yyyy'        => 'DD d-MM-yy'

            ],
            'zend_date_format'     => [
                'dd-mm-yyyy'         => 'dd-MM-y',
                'mm/dd/yyyy'         => 'MM/dd/y',
                'EEE dd-MM-yyyy'        => 'E d-M-y'
            ],
            'cldr_date_format'      => [
                'en_US'            => [
                    'yMd'           => 'M/d/Y',
                    'yMMMd'         => 'MMM d, Y',
                    'yMMMEd'        => 'EEE, MMM d, Y',
                    'yMEd'          => 'EEE, M/d/Y',
                    'MMMd'          => 'MMM yy',
                    'MMMEd'         => 'EEE, MMM d',
                    'MEd'           => 'EEE, M/d',
                    'Md'            => 'M/d',
                    'yM'            => 'M/Y',
                    'yMMM'          => 'MMM Y',
                    'MMM'          => 'MMM',
                    'E'             => 'EEE',
                    'Ed'            => 'd EEE',
                ]
            ]
        ];
    }
}
