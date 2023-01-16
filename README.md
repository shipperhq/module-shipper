# ShipperHQ
Integrate Magento 2 with ShipperHQ to provide highly flexible shipping rate management.
More information on ShipperHQ capabilities are available at <https://shipperhq.com>

Facts
-----
- [extension on GitHub](https://github.com/shipperhq/module-shipper)
- Magento v1.0 available for download from <https://shipperhq.com>

Description
-----------
ShipperHQ will install as a simple shipping carrier in Magento 2. To get started you'll need to sign up for a ShipperHQ account at [ShipperHQ](https://shipperhq.com/magento2).

There's no cost for the extension and ShipperHQ is free for 15 days.

Once installed, you can configure with your ShipperHQ platform credentials to provide multiple carrier rates and manage complex shipping rules via our ShipperHQ dashboard.

Compatibility
-------------
This module supports and is tested against the following Magento versions:

* 2.4.5-p1
* 2.4.5
* 2.4.4-p2
* 2.4.4-p1
* 2.4.4
* 2.4.3-p3
* 2.4.3-p2
* 2.4.3
* 2.4.2
* 2.4.1
* 2.4.0

per the [official Magento 2 requirements](https://experienceleague.adobe.com/docs/commerce-operations/installation-guide/system-requirements.html)

Supports both Magento Opensource (Community) and Magento Commerce (Enterprise)

Compatibility with earlier editions is possible but not maintained.

Installation Instructions
-------------------------
Install using composer by adding to your composer file using commands:

1. composer require shipperhq/module-shipper
2. composer update
3. bin/magento setup:upgrade

We recommend you also install our logging module

1. composer require shipperhq/module-logger
2. composer update
3. bin/magento setup:upgrade

Configure ShipperHQ for shipping rates on your Magento 2 Store
-------------------------
If you don't have a ShipperHQ account:

1. Sign up for a free trial at www.shipperhq.com
2. After you have signed up to ShipperHQ, follow the steps of the basic configuration wizard

Connect your ShipperHQ account and get shipping rates:

1. In your ShipperHQ account, navigate to > Websites > edit your existing site or Add a New one
2. In the “eCommerce Configuration” panel, copy your API Key and Generate your Authentication Code
3. In your Magento 2 admin, navigate to Stores > Configuration > Sales > Shipping Methods > ShipperHQ
4. Enter the API Key and the Authentication Code you have copied from step 2
5. Make sure to update the Enabled dropdown to “Yes”
6. Save the store configuration settings
7. You should see "1 carriers have been updated from ShipperHQ"

Your ShipperHQ account is now connected to your Magento 2 store, and you will now be able to show ShipperHQ shipping rates in your cart and checkout. 

To continue setting up your ShipperHQ account to match your shipping requirements, see [ShipperHQ documentation](https://docs.shipperhq.com/installing-magento-2-shipperhq-extension/)

Support
-------
If you have any issues with this extension, open an issue on [GitHub](https://github.com/shipperhq/module-shipper/issues).
Alternatively you can contact us via email at support@shipperhq.com or via our website https://shipperhq.com/contact

Magento Issues Impacting ShipperHQ
-------
1. Magento v2.1.3 - Website specific shipping rates or configuration are not working
    - Github Issue: https://github.com/magento/magento2/issues/7840
    - Related Issue: https://github.com/magento/magento2/issues/7943
    - Code change required to fix: https://github.com/magento/magento2/issues/7943#issuecomment-269508822
2. "Malformed Request" error when saving configuration or attempting to synchronize - environment is PHP 7
    - Github Issue: https://github.com/magento/magento2/issues/2984
    - Code change required to fix: https://github.com/magento/magento2/issues/7943#issuecomment-269508822
3. Only country, region and postcode are included in shipping request at checkout - you may not see correct rates returned if filtering on city or PO box addresses
    - Github Issue: https://github.com/magento/magento2/issues/3789
    - Resolved in Magento 2.1 and above for Guest checkout, logged in customers will still only see region/state, postcode and country
4. Error thrown when using Elastic search and Magento Enterprise " error: MapperParsingException[No handler for type [array] declared on field [shipperhq_master_boxes]]"
    - Magento issue number/patch reference: MDVA-791 - contact Enterprise support for patch
5. Error thrown when placing an order with some shipping methods. Root cause is that some shipping methods have shipping method codes longer than the column length on quote for shipping_method field. Field is truncating the code and order cannot be placed.
   - Github Issue: https://github.com/magento/magento2/issues/6475
6. Free shipping via cart rules are never removed once they have been applied, even if conditions are no longer met
   - Github Issue: https://github.com/magento/magento2/issues/7388
7. Shipping step of checkout times out and returns multiple blank radio buttons. Specific to PHP5.x. Ensure you have set always_populate_raw_post_data to -1 in your php.ini file


Contribution
------------
Any contribution is highly appreciated. The best way to contribute code is to open a [pull request on GitHub](https://help.github.com/articles/using-pull-requests).

License
-------
See license files

Copyright
---------
Copyright (c) 2015 Zowta LLC (http://www.ShipperHQ.com)
