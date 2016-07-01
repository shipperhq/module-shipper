# ShipperHQ
Integrate Magento 2 with ShipperHQ to provide highly flexible shipping rate management.
More information on ShipperHQ capabilities are available at www.shipperhq.com

Facts
-----
- [extension on GitHub](https://github.com/shipperhq/module-shipper)
- Magento v1.0 available for download from www.shipperhq.com

Description
-----------
ShipperHQ will install as a simple shipping carrier in Magento 2. To get started you'll need to sign up for a ShipperHQ account at https://shipperhq.com/magento2. There's no cost for the extension and ShipperHQ is free for 30 days. Once installed you can configure with your ShipperHQ platform credentials to provide multiple carrier rates and manage complex shipping rules via our ShipperHQ dashboard.

Compatibility
-------------
- Magento >= 2.0

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

Further information is available from ShipperHQ docs http://docs.shipperhq.com/installing-magento-2-shipperhq-extension/

Support
-------
If you have any issues with this extension, open an issue on [GitHub](https://github.com/shipperhq/module-shipper/issues).
Alternatively you can contact us via email at support@shipperhq.com or via our website https://shipperhq.com/contact

Magento Issues Impacting ShipperHQ
-------
1. "Malformed Request" error when saving configuration or attempting to synchronize - environment is PHP 7
    - Github Issue: https://github.com/magento/magento2/issues/2984
    - Code change required to fix: https://github.com/magento/magento2/commit/32ca5c97304a1bd84cfbee7cec3d57c9307da9a6
2. Only country, region and postcode are included in shipping request at checkout - you may not see correct rates returned if filtering on city or PO box addresses
    - Github Issue: https://github.com/magento/magento2/issues/3789

Contribution
------------
Any contribution is highly appreciated. The best way to contribute code is to open a [pull request on GitHub](https://help.github.com/articles/using-pull-requests).

License
-------
See license files

Copyright
---------
Copyright (c) 2015 Zowta LLC (http://www.ShipperHQ.com)