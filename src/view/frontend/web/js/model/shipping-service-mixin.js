/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'underscore',
        'jquery',
        'knockout',
        'uiRegistry',
        'mage/utils/wrapper'
    ],
    function (_, $, ko, registry, wrapper) {
        'use strict';

        var appendHeading = function(viewModel, methodTable) {
            if ($(methodTable).find('thead tr td.col-description').length === 0) {
                var heading = $('<th class="col col-description" data-bind="i18n: \'\'"></th>');
                heading.appendTo(methodTable.find('thead tr'));
                ko.applyBindings(viewModel, heading[0]);
            }
        };

        var appendMethodTooltips = function(viewModel, methodTable) {
            if (viewModel.rates().length) {
                _.each(viewModel.rates(), function(method) {
                    // Can't use ID selection, must use attr selection, because methods may have special chars
                    var row = methodTable.find('[id="s_method_' + method.carrier_code + '_' + method.method_code + '"]').closest('tr');
                    if (row.length && method.extension_attributes) {
                        row.find('.col-description').remove(); // Delete previous tooltip if exists
                        var tooltip = $('' +
                            '<td class="col col-description">' +
                            '    <div class="field-tooltip">\n' +
                            '        <span class="field-tooltip-action"\n' +
                            '            tabindex="0"\n' +
                            '            data-toggle="dropdown"\n' +
                            '            data-bind="mageInit: {\'dropdown\':{\'activeClass\': \'_active\'}}">\n' +
                            '                <!-- ko i18n: \'More Information\' --><!-- /ko -->\n' +
                            '        </span>\n' +
                            '        <div class="field-tooltip-content"\n' +
                            '             data-target="dropdown">\n' +
                            '                 <span data-bind="text: extension_attributes.tooltip"></span>\n' +
                            '        </div>\n' +
                            '    </div>' +
                            '</td>');
                        tooltip.appendTo(row);
                        ko.applyBindings(method, tooltip[0]);
                    }
                });
            }
        };

        return function (target) {
            var setShippingRates = target.setShippingRates;
            target.setShippingRates = wrapper.wrap(setShippingRates, function(fn, ratesData) {
                fn(ratesData); // Call original method

                var methodTbl = $('#opc-shipping_method .table-checkout-shipping-method');
                var shippingVM = registry.get("checkout.steps.shipping-step.shippingAddress");
                if (methodTbl.length && shippingVM) {
                    appendHeading(shippingVM, methodTbl);
                    appendMethodTooltips(shippingVM, methodTbl);
                }
            });
            return target;
        };
    }
);
