=== Simpaisa Credit/Debit Card Payment Service ===
Contributors: maqsoodali
Tags: woocommerce, credit, debit, card, simpaisa, payment,master,visa,1link
Requires PHP: 5.4
Requires at least: 4.4
Tested up to: 6.4.2
Stable tag: 1.1.5
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html


Providing Easy To Integrate Credit & Debit Card Payment Services.

== Upgrade Notice ==

This is an upgrade of Simpaisa Credit/Debit Card Payment Service version 1.1.5

== Description ==

Simpaisa plug-in for card is a swift way to collect payments from customers on WordPress sites.
Your customers can easily make payments using their debit and credit cards via 3D secure mechanism.
Once the user clicks on checkout they can simply choose the payment method and enter their card details, once the card details have been entered and approved by the user, the flow gets redirected to a 3D secure/OTP page.

When the user enters the  OTP the payment will get deducted from customer's account.
All the transactions can easily be monitored on the Simpaisa dedicated portal. To get access to the portal simply contact the Simpaisa team for credentials.
Note: In order to use the plug-in, merchant ID is required which can be collected from the Simpaisa team via e-mail. - See more at: **[www.simpaisa.com](https://www.simpaisa.com)**


> **Support policy**
>
> * If you need assistance, please open a support request in the **[Support section, above](https://wordpress.org/support/plugin/simpaisa-card-payment-service/)**, and we will look into it as soon as possible (usually within a couple of days).
> * If you need support urgently, or you require a customisation, you can avail of our paid support and consultancy services. To do so, please contact us (https://www.simpaisa.com), specifying that you are using our WooCommerce Simpaisa Card plugin. You will receive direct assistance from our team, who will troubleshoot your site and help you to make it work smoothly. We can also help you with installation, customisation and development of new features.

= IMPORTANT =
**Make sure that you read and understand the plugin requirements and the FAQ before installing this plugin**. Almost all support requests we receive are related to missing requirements, and incomplete reading of the message that is displayed when such requirements are not met.

= Included localisations =
* English (GB)

= Requirements =
* A Simpaisa Merchant account. The plugin was not tested with personal accounts and might not work correctly with them.
* WordPress 4.4 upto 6.4.2
* PHP 5.4 or greater
* WooCommerce 4.9.0 upto 8.3.1

= Current limitations =
* Plugin does not yet support pre-authorisation or subscriptions.

= Notes =
* This plugin is provided as a **Free** alternative to the many commercial plugins that add the Simpaisa payment services to WooCommerce. See FAQ for more details.

== Automatic Installation ==
Automatic installation is the easiest option — WordPress will handle the file transfer, and you won't need to leave your web browser. To do an automatic install of Simpaisa, log in to your WordPress dashboard, navigate to the Plugins menu, and click "Add New."

In the search field type "Simpaisa" then click "Search Plugins." Once you've found us, you can view details about it such as the point release, rating, and description. Most importantly of course, you can install it by! Click "Install Now," and WordPress will take it from there.

== Manual Installation ==
1. Manual installation method requires downloading the WooCommerce plugin and uploading it to your web server via your favorite FTP application. 
2. Extract the zip file and drop the contents in the ```wp-content/plugins/``` directory of your WordPress installation.
3. Activate the plugin through the **Plugins** menu in WordPress.
4. Go to ```WooCommerce > Settings > Payments > Simpaisa``` to configure the plugin.

For more information about installation and management of plugins, please refer to [WordPress documentation](http://codex.wordpress.org/Managing_Plugins#Installing_Plugins).

= Setup =
On the settings page, the following settings are required:

* **MerchantID**: this is the ID associated to your Simpaisa merchant account.
* **PaymentUrl**: this is the Payment url provided by Simpaisa support.
* **PublicKey**: this is the Public Key provided by Simpaisa support.

If you wish to get more details about Simpaisa, please refer to [Simpaisa website](https://www.simpaisa.com/) or Email : hasan.iqbal@simpaisa.com

== Changelog ==

= 1.0.0 =
* First official release
= 1.0.1 =
* Add email and fullname in CKO service
= 1.0.2 =
* Change Layout
= 1.0.3 =
* Add Bin Level Discount
= 1.0.4 =
* Integrate general postback
= 1.0.5 =
* Webhook changes
= 1.0.6 =
* Postback bug fixes
= 1.0.7 =
* Redirect back bug fixes
= 1.0.8 =
* Add error logs
= 1.0.9 = 
* Add access logs
= 1.1.0 =
* Multi postback bug fixes
= 1.1.1 =
* Postback improvement
= 1.1.2 =
* Service improvement
= 1.1.3 =
* Service improvement
= 1.1.4 =
* Woocommerce version support
= 1.1.5 =
* Wordpress version support