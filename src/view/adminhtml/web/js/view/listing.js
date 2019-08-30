/*
 * Shipper HQ
 *
 * @category ShipperHQ
 * @package ShipperHQ_Shipper
 * @copyright Copyright (c) 2019 Zowta LTD and Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */

define([
    'uiElement',
    'ko',
    'jquery',
    'shipperhq-listing-bundle'
], function(
    Element,
    ko,
    $,
    shqListing
){
    "use strict";

    var viewModelConstructor = Element.extend({
        defaults: {
            template: 'ShipperHQ_Shipper/listing'
        },

        onTemplateRendered: function (element, viewmodel) {
            this.attachComponent({}, element);
        },

        attachComponent: function (config, element) {
            var listingElement = document.getElementById("shqshipperlisting");
            var listingConfig = window.shqConfig.listing;

            shqListing.attach(listingElement, listingConfig);
        }
    });

    return viewModelConstructor;
});