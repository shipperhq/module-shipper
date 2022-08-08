# Change Log

## 20.0.23
SHQ16-1395 remove references to carrier group factory

## 20.0.24
SHQ16-1543 belts and braces check on quote item before accessing

## 20.0.25
SHQ16-1582 display pickup location and time slot in order view
SHQ16-1682 Changed version to placeholder

## 20.0.26
SHQ16-745 eCommerce cart in request corrected

## 20.1.0
SHQ16-1619 Config flag to ignore requests if zip code blank 

## 20.1.1
SHQ16-1731 corrected function name
SHQ16-1730 resolved package helper constructor
SHQ16-1730 resolved constructor on package helper

## 20.1.2
SHQ16-1739 modify usage of checkout session, Updated issue reference in readme

## 20.1.3
SHQ16-1745 handle exception on addresses without region

## 20.1.4
SHQ16-992 implemented caching of rates using cache

## 20.1.5
Updated README with latest github issues

## 20.1.6
SHQ16-1774 record order packages

## 20.1.7
SHQ16-1708 display notification when credentials invalid in admin
SHQ16-1859 allow extension of shipper mapper functions

## 20.1.8
SHQ16-1851 removed columns from sales order grid until we find a solution

## 20.1.9
SHQ16-992 implement config switch for caching rates

## 20.2.0
SHQ16-992 implement config switch for cached rates

## 20.2.1
SHQ16-1902 use stored address type in subsequent requests

## 20.2.2
SHQ16-1922 save package detail on first pass through checkout, set carrier code when settings are shqshared
SHQ16-1927 pass store id when retrieving quote

## 20.2.3
SHQ16-1925 Resolved issue with merged rates not showing

## 20.2.4
SHQ16-594 populate shipperhq_location in request

## 20.3.0
SHQ16-1851_A reinstate order grid columns

## 20.3.1
SHQ16-1851_A reinstate order grid columns

## 20.3.2
SHQ16-1968 save packages when carrier and name is duplicated

## 20.3.3
SHQ16-2002 Fixed issue with SHQ using wrong stores API credentials within admin

## 20.3.4
SHQ16-2002 Fixed issue with SHQ using wrong stores API credentials within admin

## 20.3.5
SHQ16-2004 - custom admin shipping
SHQ16-2029 - code sniffer changes

## 20.4.0
SHQ16-2032 and SHQ16-2041 pickup date is used as start when it's today, otherwise use start date
SHQ16-2041 extended unit tests
SHQ16-2029 changes from code sniffer review

## 20.5.0
SHQ16-1967 Accessorials in M2

## 20.5.1
Corrected version number display

## 20.5.2
SHQ16-2117 resolved issue with Authorize.net and custom admin shipping

## 20.5.3
SHQ16-2123 resolve issue with install and upgrade when database has been migrated from M1 to M2

## 20.5.4
SHQ16-2123 prevent deleting default attribute group

## 20.5.5
SHQ16-2141 UPS LTL use carrier code as type

## 20.5.6
SHQ16-2154 clean up step to remove duplicates from shq order grid table

## 20.5.7
SHQ16-2159 add custom admin shipping when no rates returned

## 20.5.8
SHQ16-2171 ensure internal error message can be displayed

## 20.5.9
SHQ16-2178 prevent display of address type and validation result on front end
SHQ16-2200 Added in master packing boxes attribute to mapping

## 20.6.0
SHQ16-2178 set address type on new quote

## 20.6.1
SHQ16-2238 prevent null for city field
SHQ16-2236 class variables made public to reuse in children
SHQ16-2235 add freight attributes to catalog attributes xml
SHQ16-2219 display location selected as pickup at checkout

## 20.6.2
SHQ16-2250 add destination_type attribute if not present

## 20.7.0
SHQ16-2205 - compatibility with release candidate M2.2

## 20.7.1
SHQ16-2205 - compatibility with release candidate M2.2

## 20.7.2
SHQ16-2205 DeliveryDate column backwards compatible with 2.2 RC

## 20.7.3
SHQ16-2258 add config for backup carrier timeout

## 20.7.4
SHQ16-2281 use mutable config when getting carrier by code

## 20.7.5
SHQ16-2266 added some debug logging to assist in customer diagnosis

## 20.7.6
SHQ16-2281 use system config to enable backup carrier temporarily

## 20.7.7
SHQ16-2298 set address type when request being made from cart on logged in customer

## 20.8.0
SHQ16-2231 support for customer account carrier

## 20.8.1
SHQ16-2300 Fixed issue with deleting old carriers from config

## 20.8.2
M2-45 Ensure we check array key exists on admin order view
M2-47 Deadlocking issue work

## 20.8.3
SHQ16-2078 add unit tests for time slot calculations

## 20.8.4
M2-47 added fix to record order package items

## 20.8.5
SHQ16-2372 Fixed issue in SQL scripts

## 20.8.6
M2-51 reduce the maximum length of carrier and method code combination
SHQ16-2374 move caching of allowedMethods response to only cache if it is successful

## 20.8.7
SHQ16-2375 support for split database architecture in EE

## 20.8.8
SHQ16-2392 added comments and refactored unit tests, added tests for calendarDetails default date
M2-52 remove dependency on debug setting to enable logging

## 20.8.9
SHQ16-2346 Updated must_ship_freight with a note to say it can be overriden at carrier level

## 20.9.0
SHQ16-2417 ignore time zone when reformating dates

## 20.10.0
SHQ16-2435 Fixed issue with custom options causing a 403 error
SHQ16-2255 Support for calendar and pickup in admin orders
SHQ16-2419 Caching improvements
M2-55 Modify order recording for MAC

## 20.12.0
SHQ16-2938 option to not modify carrier, SHQ16-2434 add IP to request, SHQ16-2489 handle empty street

## 20.13.0
SHQ16-2492 modified hiding of sallowspecific field

## 20.13.1
INFRA-62 Brought repo up to date

## 20.13.2
SHQ18-97 Update placeholder in config.xml

## 20.13.3
SHQ18-57 re-request of rates is for all carriers, so need to clean down all rates

## 20.14.0
SHQ18-58 Improve compatability with other extns, SHQ18-118/SHQ18-121 Improve module lookup, SHQ18-104 Fix timeslots ending at midnight

## 20.14.1
SHQ18-142 Remove redundant dependency from Helper/Modules

## 20.15.0
SHQ18-141 record validated address if a single corrected address is returned

## 20.15.1
SHQ18-141 record validated address if a single corrected address is returned

## 20.16.0
SHQ18-112 use Magento scope config

## 20.17.0
SHQ18-147 specify version constraints for required modules

## 20.17.1
SHQ18-147 specify version constraints for required modules

## 20.17.2
SHQ18-184 Explicitly defined table name to avoid SQL error in order grid

## 20.18.0
SHQ18-112 code clean up

## 20.18.1
SHQ18-169 only reset selected date when returning on checkout

## 20.18.2
SHQ18-196 add version to composer file

## 20.18.3
SHQ18-209 Fixed issue with inventoryCount being 0 instead of null

## 20.18.4
SHQ18-206/SHQ18-193 Fixed duplicate rates when changing date/options

## 20.18.5
SHQ18-220 - Made delivery date available in email template

## 20.18.6
SHQ18-227 Fix sorting order grid by SHQ fields

## 20.18.7
SHQ18-240 check for checkout_selections before accessing

## 20.18.8
SHQ18-220 refactored use of quote and order detail in email observer

## 20.18.9
SHQ18-237 - Fix for multiple rate currencies being returned when multiple origins

## 20.18.11 (2018-05-31)
SHQ18-261 Fix ambiguous where clause SQL error in M2.2.4 with Amazon Pay


## 20.18.12 (2018-06-12)
SHQ18-289 Fetch rates if out of stock item allows backorders


## 20.18.13 (2018-06-25)
SHQ18-344 correct variable names for time slot and pickup location name


## 20.18.14 (2018-07-17)
SHQ18-438 Implement rounding for decimal quantities under 1


## 20.19.0 (2018-07-18)
SHQ18-127 Fix conflict with B2B module


## 20.20.0 (2018-08-02)
SHQ18-155 support map and location details for in store pickup


## 20.20.1 (2018-08-06)
SHQ18-511 Fix tooltips breaking checkout when method has special chars


## 20.20.2 (2018-09-19)
SHQ18-774 Ensure correct store configuration is used in admin orders


## 20.21.0 (2018-10-03)
SHQ18-860 Wipe selections when returning to cart from other pages


## 20.21.1 (2018-10-04)
SHQ18-889 Resolved issue with getting store id in multi address checkout


## 20.21.2 (2018-10-23)
SHQ18-964 Add availability date to attributes array


## 20.21.3 (2018-10-23)
SHQ18-955 Convert html in origin and pickup names to corresponding character


## 20.21.4 (2018-11-29)
SHQ18-1120 Perform dynamic address type lookup within PayPal express checkout


## 20.21.5 (2018-12-06)
SHQ18-1159 Prevent calls to ShipperHQ API if credentials are not entered. SHQ18-1143 Autoselect method if only one present


## 20.21.6 (2018-12-10)
SHQ18-944 Remove restricted characters from order grid column names


## 20.21.7 (2019-01-22)
SHQ18-1335 Fixing Array to String Conversion


## 20.21.8 (2019-02-04)
SHQ18-1310 Convert HTML in attribute values to character before sending in request


## 20.21.9 (2019-02-12)
SHQ18-1391 Ensure shipping rate saved to order matches shipping rate in order details


## 20.22.0 (2019-03-11)
SHQ18-1613 Support displaying actual method rate shopping selected in admin order view


## 20.22.1 (2019-03-13)
SHQ18-1644 Fix case sensitivity in carrier logos / SHQ18-1620 Use numeric method codes for UPS


## 20.23.0 (2019-03-14)
SHQ18-1651 Update dependencies in composer.json


## 20.24.0 (2019-03-21)
SHQ18-1666 Display rate shopped method as order comment


## 20.24.1 (2019-03-21)
SHQ18-1700 Code improvements to mitigate deadlocking


## 20.24.2 (2019-04-03)
SHQ18-1613 Added carrier title to order comment. SHQ18-1777 Updated Zenda logo


## 20.24.3 (2019-04-04)
SHQ18-1823 don't show logos for errors or non SHQ methods


## 20.25.0 (2019-05-07)
SHQ18-1866 Save quote packages to session rather than DB. SHQ18-1947 Fix to ensure saving correct address details in multi address checkout. SHQ18-1804 Custom dutires in admin support


## 20.25.1 (2019-05-28)
SHQ18-1907 Ensure duties and taxes do not duplicate. SHQ18-1945 Add FlavorCloud logo


## 20.26.0 (2019-06-12)
SHQ18-2107 Support for allowed methods for multiple API keys on one store


## 20.27.0 (2019-07-15)
SHQ18-2267 Make accessorials available on order emails


## 20.28.0 (2019-07-30)
SHQ18-2247 uShip Integration


## 20.29.0 (2019-08-19)
SHQ18-2416 Add support for showing rate shopped method in admin panel


## 20.30.0 (2019-08-30)
SHQ18-2440/2431/2517/2518 Implement manual listing utility


## 20.31.0 (2019-09-06)
SHQ18-2198 Support syncronisation from multiple API keys


## 20.31.1 (2019-09-11)
SHQ18-2587 Added support for uShip NYP Amount (Price less fees) and improved uniqueness of shipmentId


## 20.31.2 (2019-09-12)
SHQ18-2576/SHQ18-2577/SHQ18-2613 Fix multiple issues with missing data


## 20.31.3 (2019-10-02)
SHQ18-2693 Fix exception when using Magento API


## 20.31.4 (2019-10-16)
SHQ18-2787 Fix for exception when getting customer group


## 20.31.5 (2019-10-30)
SHQ18-2575 Allow custom admin shipping price of 0


## 20.31.6 (2019-10-31)
M2-63 changed modules in require-dev to lcase


## 20.32.0 (2019-11-04)
SHQ18-2680 Minor performance enhancement


## 20.32.1 (2019-11-06)
SHQ18-2851 Fix issue with email variables not being set


## 20.33.0 (2019-11-18)
SHQ18-2284 PHP 7.3 compatibility


## 20.34.0 (2019-11-20)
SHQ18-2869 Process rate response as array


## 20.34.1 (2019-11-21)
SHQ18-2788 500 Error when missing BackupCarriers / SHQ18-2761 Carrier Logos not Showing


## 20.34.2 (2019-11-21)
SHQ18-2940 Update composer dependencies


## 20.35.0 (2020-01-16)
SHQ18-2929 Dont create duplicate attribute group, SHQ18-2825 Ensure migrated data from M1 migrates correctly, SHQ18-597 Uninstall script for use with composer


## 20.35.1 (2020-03-16)
MNB-138 Fix for carrier logos not showing when CSS bundling enabled


## 20.36.0 (2020-04-03)
MNB-176 Reset delivery date on new rate request. MNB-231 Set weight to null on 0 weight products


## 20.36.1 (2020-04-16)
MNB-221 Change dimensional rule to shipping rule


## 20.37.0 (2020-05-04)
MNB-279 Fix filtering in admin order grid


## 20.37.1 (2020-05-19)
MNB-292 Fix issue with backup carrier not switching off. MNB-302 Fix alignment of UI table on checkout when tooltips enabled


## 20.38.0 (2020-06-08)
MNB-358 Sync attributes from all API keys. MNB-271 Remove URL and sandbox mode


## 20.38.1 (2020-06-18)
MNB-386 Prevent negative values in admin shipping. MNB-440 Add disptach date to email variables


## 20.38.2 (2020-07-28)
MNB-459 Fix filtering by delivery and dispatch date in order grid


## 20.38.3 (2020-08-18)
MNB-618 Fix delivery and dispatch date showing wrong dates in order grid


## 20.39.0 (2020-08-20)
MNB-604 Add support for box comments for merged rates


## 20.39.1 (2020-09-15)
MNB-578 Improved Logging


## 20.39.2 (2020-09-17)
MNB-605 Add unique index to ShipperHQ sales order grid table


## 20.39.3 (2020-10-19)
MNB-726 Fix issue around calendar showing cached rates when no rates are returned


## 20.39.4 (2020-10-29)
 MNB-764 Fix filtering in order grid for international date formats


## 20.39.5 (2020-11-11)
MNB-808 fix php 7.4 deprecation issue


## 20.39.6 (2020-12-18)
MNB-916 Fix warning generated in package processing


## 20.39.7 (2020-12-21)
MNB-861 Tidy up DB. MNB-448 Fix carrier logos showing on date and accessorial change.


## 20.39.8 (2021-02-08)
MNB-887 Fix issue with AV attributes showing on frontend


## 20.40.0 (2021-02-11)
MNB-1060 remove a problematic FK


## 20.40.1 (2021-02-26)
MNB-24 Updated proxy-manager-bridge version in composer.lock


## 20.40.2 (2021-03-18)
MNB-1114 Fix for missing data in order grid, order comments and order


## 20.41.0 (2021-03-29)
RIV-443 Add place order support for order management feature


## 20.41.1 (2021-03-29)
RIV-443 Update composer to require new libraries


## 20.42.0 (2021-03-29)
RIV-443 Fix lib-graphql dependency


## 20.42.1 (2021-03-30)
RIV-443 Require updated library


## 20.42.2 (2021-03-30)
RIV-443 Fix issues with Postorder


## 20.43.0 (2021-03-31)
MNB-1186 Fix issue with obtaining transaction id on placeorder. MNB-1111 Add primary key to order items table. MNB-1102 Update address templates


## 20.43.1 (2021-04-22)
MNB-1204 Fix issue around date select requesting rates for all carriers and errors showing when shouldnt


## 20.43.2 (2021-04-29)
MNB-1260 Fix billing template to address MDVA-33289


## 20.43.3 (2021-06-07)
MNB-1251 Add check for if in admin panel


## 20.44.0 (2021-06-10)
MNB-1303 Add note to order containing customer account carrier details


## 20.44.1 (2021-06-16)
MNB-1337 Fix issue when switching from backup to live rates


## 20.44.2 (2021-07-01)
MNB-1385 Add check for zip required for destination


## 20.44.3 (2021-07-09)
MNB-1429 Ensure correct method code is passed to shipping insights


## 20.45.0 (2021-07-30)
MNB-1471 Add cache timeout. MNB-1465 Ensure order comment containing packing info is accurate. MNB-1464 Dont send details to Shipping Insights for non SHQ methods


## 20.45.1 (2021-08-04)
MNB-1493 Move UK out of list of countries where postcode is optional


## 20.45.2 (2021-08-13)
MNB-1340 Fix for incorrect dispatch date showing in admin


## 20.45.3 (2021-08-24)
MNB-1576 Magento coding standards


## 20.45.3 (2021-08-24)
MNB-1576 Magento coding standards


## 20.45.4 (2021-08-25)
MNB-1575 Fix compilation error


## 20.45.4 (2021-08-25)
MNB-1575 Fix compilation error


## 20.45.5 (2021-08-27)
MNB-1592 move logic to prevent exception when order has no shipping rate


## 20.45.6 (2021-09-29)
MNB-1597 Fix issue with pickup carrier logo failing to render


## 20.45.7 (2021-11-01)
MNB-1836 Fix issue in upgrade script around foreign key requires index


## 20.45.8 (2021-11-02)
MNB-1797 Fix unit tests to work with Magento 2.3+


## 20.45.8 (2021-11-02)
MNB-1797 Fix unit tests to work with Magento 2.3+


## 20.45.9 (2021-11-03)
MNB-1583 Update admin sales order item view to not use a plugin to add columns


## 20.45.10 (2021-12-21)
RIV-530 submit address with placeOrder


## 20.45.11 (2021-12-21)
RIV-530 Update composer to use new library


## 20.46.0 (2022-01-13)
MNB-2023 Remove support for custom options in rate request


## 20.46.1 (2022-01-17)
MNB-1467 Fix for shipping showing as not yet calculated on payment step


## 20.47.0 (2022-02-03)
MNB-2102 Custom Admin Rates w/ Payflow // MNB-2112 declarative schema


## 20.47.1 (2022-02-23)
MNB-2105 update shipping method on items when selecting custom rate


## 20.47.2 (2022-02-24)
MNB-2253 Fix SQL error when upgrading from 20.38.1 or earlier


## 20.47.3 (2022-02-28)
MNB-2270 Remove index from db_schema_whitelist.json


## 20.47.4 (2022-03-09)
MNB-2285 Fix Shipping Insights integration


## 20.47.5 (2022-03-15)
MNB-2338 Fix issue in data patch InstallDestTypeAttributes


## 20.47.6 (2022-03-23)
MNB-2342 Fix issue with storing methods containing spaces on Shipping Insights


## 20.48.0 (2022-05-13)
MNB-2430 M2.4.4 compatibility


## 20.48.1 (2022-05-23)
MNB-2474 Stop using default saved address destination_type for new addresses when address validation enabled


## 20.48.2 (2022-05-25)
MNB-2546 Fix issue with admin orders throwing errors


## 20.49.0 (2022-06-27)
MNB-2591 use InventoryManagement instead of StockRegistry


## 20.49.1 (2022-07-15)
MNB-2717 Prevent exception being thrown when configurable product with options added to cart


## 20.50.0 (2022-07-19)
MNB-2726 fix issue when MSI uninstalled


## 20.50.1 (2022-08-08)
MNB-2863 Fix error thrown when Magento inventory is disabled


