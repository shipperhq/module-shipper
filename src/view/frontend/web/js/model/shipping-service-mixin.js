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
        'Magento_Checkout/js/action/select-shipping-method',
        'uiRegistry',
        'mage/utils/wrapper',
        'shq_logos_manifest'
    ],
    function (_, $, ko, selectShippingMethodAction, registry, wrapper, manifest) {
        'use strict';

        var findMethodLabel = function (methodTable, method) {
            var methodLabel = methodTable.find('[id="label_method_' + method.method_code + '_' + method.carrier_code + '"]');
            if (!methodLabel.length) {
                methodLabel = methodTable.find('[id="s_method_' + method.carrier_code + '_' + method.method_code + '"]');
            }

            return methodLabel
        };

        var findMethodRow = function (methodTable, method) {
            var methodLabel = findMethodLabel(methodTable, method);

            return methodLabel.closest('tr')
        };

        var appendTooltipColumn = function (viewModel, methodTable) {
            if ($(methodTable).find('tr td.col-description').length === 0) {
                var heading = $('<th class="col col-description" data-bind="i18n: \'\'"></th>');
                heading.appendTo(methodTable.find('thead tr'));
                var column = $('<td class="col col-description"></td>');
                column.appendTo(methodTable.find('tbody tr, tfoot tr'));
                ko.applyBindings(viewModel, heading[0]);
            }
        };

        var appendMethodTooltips = function (viewModel, methodTable) {
            if (viewModel.rates().length) {
                _.each(viewModel.rates(), function (method) {
                    // Can't use ID selection, must use attr selection, because methods may have special chars
                    var row = findMethodRow(methodTable, method);
                    if (row.length && method.extension_attributes && method.extension_attributes.tooltip) {
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

        var appendCustomDuties = function (viewModel, methodTable) {
            if (viewModel.rates().length) {
                _.each(viewModel.rates(), function (method) {
                    // Can't use ID selection, must use attr selection, because methods may have special chars
                    var label = findMethodLabel(methodTable, method);
                    var row = findMethodRow(methodTable, method);
                    if (label.length && method.extension_attributes && method.extension_attributes.custom_duties) {
                        row.find('.shq-method-subtext').remove(); // Delete previous duties if exists
                        var customDuties = $('' +
                            '<div class="shq-method-subtext" data-bind="text: extension_attributes.custom_duties"></div>\n'
                        );
                        customDuties.appendTo(label);
                        ko.applyBindings(method, customDuties[0]);
                    }
                });
            }
        };

        var addCarrierLogos = function (viewModel, methodTable) {
            var logoPathElement = document.querySelector('link[rel=shq-carriers-logos-path]');
            if (logoPathElement && logoPathElement.href) {
                var logosBasePath = logoPathElement.href;
                if (/^http/.test(logosBasePath) && viewModel.rates().length) {
                    _.each(viewModel.rates(), function (method) {
                        if (
                            method.extension_attributes &&
                            method.extension_attributes.hide_notifications &&
                            method.extension_attributes.hide_notifications === "1"
                        ) {
                            return
                        }

                        // SHQ18-1823 don't show logos for non ShipperHQ methods or errors
                        if (method.error_message !== "" || method.carrier_code.toString().indexOf("shq") === -1) {
                            return
                        }

                        var row = findMethodRow(methodTable, method);
                        var label = row.find('.col-carrier');
                        if (label.length) {
                            var strippedCarrierCode = method.carrier_code.toString().replace(/^shq|[^a-z]/ig, '');
                            var manifestEntries = manifest.filter(function (el) {
                                return el.toLowerCase() === strippedCarrierCode.toLowerCase()
                            });
                            if (manifestEntries.length > 0) {
                                var logoFile = manifestEntries.shift();
                                var logo = logosBasePath + '/' + logoFile + '.png';
                            } else {
                                logo = logosBasePath + '/smpkg.png';
                                if (/pickup/i.test(method.carrier_code)) {
                                    logo = logoBasePath + '/pickup.png';
                                } else if (/freight/i.test(method.carrier_code)) {
                                    logo = logosBasePath + '/freight.png';
                                }
                            }
                            var img = $('<div class="shq-method-carrier-logo"><img src="' + logo + '" alt="' + method.carrier_title + '"/></div><div class="shq-method-carrier-title">' + method.carrier_title + '</div>')
                            label.html(img)
                        }
                    });
                }
            } else {
                console.info("SHIPPERHQ NOTICE: Carrier logos will not load. The link[rel=shq-carriers-logos-path] element could not be found or is invalid")
            }
        };

        var appendSHQData = function () {
            var methodTbl = $('#opc-shipping_method .table-checkout-shipping-method');
            var shippingVM = registry.get("checkout.steps.shipping-step.shippingAddress");
            if (methodTbl.length && methodTbl.find('tbody tr').length && shippingVM) {
                appendTooltipColumn(shippingVM, methodTbl);
                appendMethodTooltips(shippingVM, methodTbl);
                appendCustomDuties(shippingVM, methodTbl);
                addCarrierLogos(shippingVM, methodTbl);
                return true;
            }
            return false;
        };

        var appendSHQDataWhenLoaded = function (retries, delayMs, firstDelayMs) {
            var delay = firstDelayMs !== undefined ? firstDelayMs : delayMs;
            setTimeout(function () {
                if (!appendSHQData() && retries) {
                    appendSHQDataWhenLoaded(--retries, delayMs)
                }
            }, delay)
        };

        var initialLoad = true;

        return function (target) {
            var setShippingRates = target.setShippingRates;
            target.setShippingRates = wrapper.wrap(setShippingRates, function (fn, ratesData) {
                fn(ratesData); // Call original method

                // M2.2 has split shipping methods into their own module. Wait for up to 2.5s for it to load
                appendSHQDataWhenLoaded(10, 250, 0);

                // SHQ18-860 clear selected shipping method on reload
                if (initialLoad) {
                    // SHQ18-1143 - In M2.1 if there's only one method it's hardcoded to be checked
                    selectShippingMethodAction(
                        ratesData.length == 1
                            ? ratesData[0]
                            : null
                    );
                    initialLoad = false
                }
            });
            return target;
        };
    }
);
