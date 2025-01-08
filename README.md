# ShipperHQ for Magento 2

ShipperHQ is the ultimate solution for advanced shipping rate management and checkout customization in Magento 2. With ShipperHQ, you can optimize your shipping strategy, integrate with multiple carriers, and provide a seamless checkout experience for your customers.

---

## Features

- **Real-Time Shipping Rates**: Display accurate shipping rates at checkout based on product details, customer location, and shipping preferences.
- **Carrier Integration**: Support for major carriers like UPS, FedEx, USPS, DHL, and custom/local carriers.
- **Custom Shipping Rules**: Set rules for free shipping thresholds, surcharges, restricted zones, and more.
- **Multi-Origin Shipping**: Handle shipments from multiple warehouses or drop shippers.
- **Pickup & Delivery Options**: Offer in-store pickup, curbside delivery, and date-specific delivery services.
- **Smart Packaging**: Optimize shipping costs with advanced packaging algorithms.
- **Customizable Checkout**: Tailor the shipping options displayed during checkout for a better customer experience.

---

## Installation

Install using composer, you can find full instructions in the [ShipperHQ documentation](https://docs.shipperhq.com/installing-magento-2-shipperhq-extension/).

---

## Requirements

- Magento 2.4.4+
  - Compatibility with earlier editions is possible but not maintained
  - Supports both Magento Opensource (Community) and Magento Commerce (Enterprise)
- A valid ShipperHQ account. [Sign up here](https://shipperhq.com/) for your 15-day free trial!

---

## Configuration

**If you don't have a ShipperHQ account**:
1. Sign up for a free 15-day trial at www.shipperhq.com
2. You can then follow the steps the basic configuration wizard will guide you through

**Connect your ShipperHQ account and get shipping rates**:
1. **API Credentials**:
    - Log in to your ShipperHQ account.
    - Navigate to `Websites` and find your API key and generate an Authentication Code.
    - Paste the key and code into the plugin settings under `Stores > Configuration > Sales > Shipping Methods > ShipperHQ`.
    - Set the `Enabled` dropdown to `Yes`. 
    - Save the store configuration settings.
    - You should see "1 carriers have been updated from ShipperHQ".

2. **Shipping Rules**:
    - Configure shipping rules, methods, and carriers in your ShipperHQ dashboard.
      - You can find extensive examples and guides in our [documentation](https://docs.shipperhq.com/).
    - Most settings will sync with your Magento 2 store automatically. If you add shipping groups, origins or packing rules, you will need to [sync these manually](https://docs.shipperhq.com/synchronize-groups-origins-dimensionalrules-magento/). 

3. **Test Your Checkout**:
    - Add a product to the cart and proceed to checkout to verify shipping rates and options.

---

## Frequently Asked Questions

### 1. Do I need a ShipperHQ account to use this plugin?
Yes, an active ShipperHQ account is required to use this plugin.

### 2. Is there a free trial available for ShipperHQ?
Yes, ShipperHQ offers a free trial for new users. You can sign up for the trial on the [ShipperHQ website](https://shipperhq.com/) and explore the platform's features before committing to a subscription.

### 3. How do I troubleshoot issues with shipping rates?
- Verify your API credentials in the plugin settings.
- Ensure the shipping rules and methods are correctly configured in your ShipperHQ dashboard.
- Check for plugin conflicts by disabling other shipping-related plugins temporarily.
- Check our extensive [documentation](https://docs.shipperhq.com/category/troubleshooting/) for troubleshooting tips.

### 4. Can I customize shipping options for specific products?
Yes, you can configure product-specific rules in your ShipperHQ dashboard. Take a look at our [extensive examples](https://docs.shipperhq.com/category/examples/) guide for some ideas of what ShipperHQ can do for you.

---

## Support

For assistance, please visit our [Help Center](https://docs.shipperhq.com/) or contact ShipperHQ support at [support@shipperhq.com](mailto:support@shipperhq.com).

For alternative contact methods, please visit our [Contact Us](https://shipperhq.com/contact/) page.

---

## Contributing

Any contribution is highly appreciated. The best way to contribute code is to open a [pull request on GitHub](https://help.github.com/articles/using-pull-requests).

---

## License

See license files

---

## Copyright
Copyright (c) 2015 Zowta LLC (http://www.ShipperHQ.com)
