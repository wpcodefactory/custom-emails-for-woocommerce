=== Custom Emails for WooCommerce ===
Contributors: wpcodefactory, algoritmika, anbinder
Tags: woocommerce, emails, email, custom email, custom emails, woo commerce
Requires at least: 4.4
Tested up to: 6.0
Stable tag: 1.5.5
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Add custom emails to WooCommerce.

== Description ==

**Custom Emails for WooCommerce** plugin lets you add custom emails to WooCommerce.

### &#9989; Main Features ###

* Set custom email **trigger(s)**. For example, send email when order status changed to "Completed", or only when it changed from "Pending payment" to "Completed". Custom order statuses are automatically added to the plugin's triggers list. You can choose from numerous triggers, like sending email on a new order, or when order was fully or partially refunded, or when note was added to the order. In addition you can set non-order emails, like sending an email to admin if anyone's password was reset, or new customer was created, or when a product is on backorder, and so on...
* **Delay** emails. For example, send custom email one week after order was "Completed".
* **Require and/or exclude order product(s)** - sent email only if there were selected products in the order.
* Set **minimum and/or maximum order amount** - minimum/maximum order amount (subtotal) for email to be sent.
* Set **subject**, **heading** and **content**. You can use the plugin's [shortcodes](https://wpfactory.com/item/custom-emails-for-woocommerce/#shortcodes) and/or standard WooCommerce email placeholders here.
* Set **email type** - choose which format of email to send (plain text, HTML, multipart). Optionally wrap email in **WooCommerce email template**.
* Send emails **manually** from **order actions** meta box on single order edit page, and/or in bulk from **bulk actions** dropdown on admin orders list.
* Set email **recipient(s)** - customer, admin, custom email address.
* Set email's **admin title**.
* And more...

### &#127942; Premium Version ###

Free plugin version allows you to set up one custom email with all available features. With [Custom Emails for WooCommerce Pro](https://wpfactory.com/item/custom-emails-for-woocommerce/) plugin you can add unlimited number of custom emails.

### &#128472; Feedback ###

* We are open to your suggestions and feedback. Thank you for using or trying out one of our plugins!
* [Visit plugin site](https://wpfactory.com/item/custom-emails-for-woocommerce/).

== Installation ==

1. Upload the entire plugin folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Start by visiting plugin settings at "WooCommerce > Settings > Custom Emails".

== Changelog ==

= 1.5.6 - 06/10/2022 =
* Add deploy script.

= 1.5.5 - 06/10/2022 =
* WC tested up to: 6.9.
* Readme.txt updated.

= 1.5.4 - 12/09/2022 =
* Dev - Shortcodes - `[order_details]` - `plain_text` and `sent_to_admin` attributes added (both default to `no`).
* WC tested up to: 6.8.

= 1.5.3 - 20/07/2022 =
* Dev - Triggers - "Subscription status updated" trigger groups added ("WooCommerce Subscriptions" plugin).
* WC tested up to: 6.7.

= 1.5.2 - 30/06/2022 =
* Fix - Settings - Trigger(s) - "Extra" triggers were not added to the list. This is fixed now.
* Dev - Developers - `alg_wc_custom_emails_is_enabled` filter added.
* WC tested up to: 6.6.
* Tested up to: 6.0.

= 1.5.1 - 01/08/2021 =
* Fix - Error on WooCommerce Analytics page fixed.

= 1.5.0 - 01/08/2021 =
* Fix - Shortcodes - `[order_shipping_total]` shortcode fixed.
* Fix - Shortcodes - `[order_shipping_method]` shortcode fixed.
* Fix - Shortcodes - `[order_payment_method_title]` shortcode fixed.
* Dev - General - "Enabled triggers groups" option added.
* Dev - Triggers - "Subscriptions: Renewal order" trigger groups added.
* Dev - Shortcodes - `[order_item_names]` shortcode added.
* Dev - Debug - More info is added to the log now.

= 1.4.1 - 28/07/2021 =
* Dev - Escaping all output now.

= 1.4.0 - 27/07/2021 =
* Dev - All user input is properly sanitized now.
* Dev - Localisation - `load_plugin_textdomain()` is called on `init` action now.
* Dev - Code refactoring.
* Tested up to: 5.8.
* WC tested up to: 5.5.
* Free plugin version released.

= 1.3.1 - 05/04/2021 =
* Dev - Email content - "Default content" button added.
* Dev - Settings - Link to each email settings added.
* Dev - Email settings - Triggers list rearranged; settings descriptions updated.
* Dev - Code refactoring.

= 1.3.0 - 30/03/2021 =
* Dev - "Delay" options added (including "Scheduled" section).
* Dev - Now always adding order note when sending an email.
* Dev - Debug - More info added.
* Dev - Code refactoring.

= 1.2.1 - 22/03/2021 =
* Dev - "Send email: ..." actions added to the "Bulk actions" select box in admin "Orders" list.
* Dev - Code refactoring.

= 1.2.0 - 19/03/2021 =
* Dev - "Minimum order amount" option added.
* Dev - "Maximum order amount" option added.
* Dev - "Require order product(s)" option added.
* Dev - "Exclude order product(s)" option added.
* Dev - General - Advanced - "Debug" option added.
* Dev - Settings - Minor descriptions update.
* WC tested up to: 5.1.
* Tested up to: 5.7.

= 1.1.0 - 07/10/2020 =
* Dev - `[generate_coupon_code]` shortcode added.
* Dev - Trigger - `alg_wc_custom_emails_is_user_email` filter added.
* Dev - Settings - Minor descriptions update.
* WC tested up to: 4.5.
* Tested up to: 5.5.

= 1.0.0 - 21/01/2020 =
* Initial Release.

== Upgrade Notice ==

= 1.0.0 =
This is the first release of the plugin.
