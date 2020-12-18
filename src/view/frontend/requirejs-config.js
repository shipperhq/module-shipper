/*
 * Shipper HQ
 *
 * @category ShipperHQ
 * @package ShipperHQ_Shipper
 * @copyright Copyright (c) 2020 Zowta LTD and Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */


var config = {
    map: {
        "*": {
            "Magento_Checkout/template/shipping-information/address-renderer/default.html":
                "ShipperHQ_Shipper/template/shipping-information/address-renderer/default.html",
            "Magento_Checkout/template/shipping-address/address-renderer/default.html":
                "ShipperHQ_Shipper/template/shipping-address/address-renderer/default.html",
            "Magento_Checkout/template/billing-address/details.html":
                "ShipperHQ_Shipper/template/billing-address/details.html",
            "shq_logos_manifest":
                'ShipperHQ_Shipper/images/carriers/manifest'
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/model/shipping-service': {
                'ShipperHQ_Shipper/js/model/shipping-service-mixin': true
            }
        }
    }
};
