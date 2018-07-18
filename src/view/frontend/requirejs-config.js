/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
    map: {
        "*": {
            "Magento_Checkout/template/shipping-information/address-renderer/default.html":
                "ShipperHQ_Shipper/template/shipping-information/address-renderer/default.html",
            "Magento_Checkout/template/shipping-address/address-renderer/default.html":
                "ShipperHQ_Shipper/template/shipping-address/address-renderer/default.html",
            "Magento_Checkout/template/billing-address/details.html":
                "ShipperHQ_Shipper/template/billing-address/details.html"
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
