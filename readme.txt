=== Custom Emails for WooCommerce ===
Contributors: wpcodefactory, algoritmika, anbinder, karzin, omardabbas, kousikmukherjeeli
Tags: woocommerce, emails, email, custom email, custom emails, woo commerce
Requires at least: 4.4
Tested up to: 6.4
Stable tag: 2.5.0
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Add custom emails to WooCommerce.

== Description ==

**Custom Emails for WooCommerce** plugin lets you add custom emails to WooCommerce.

### &#9989; Main Features ###

* Set custom email **trigger(s)**. For example, send email when order status changed to "Completed", or only when it changed from "Pending payment" to "Completed". Custom order statuses are automatically added to the plugin's triggers list. You can choose from numerous triggers, like sending email on a new order, or when order was fully or partially refunded, or when note was added to the order. In addition you can set non-order emails, like sending an email to admin if anyone's password was reset, or new customer was created, or when a product is on backorder, and so on...
* **Delay** emails. For example, send custom email one week after order was "Completed".
* **Require/exclude order product(s)** - sent email only if there were selected products, product categories or tags in the order.
* Set **minimum/maximum order amount** - minimum/maximum order amount (subtotal) for email to be sent.
* Require/exclude order **payment gateways** or **shipping methods**.
* Require/exclude order **user roles** or **users**.
* Set **subject**, **heading** and **content**. You can use the plugin's [shortcodes](https://wpfactory.com/docs/custom-emails-for-woocommerce/) or standard WooCommerce email placeholders here.
* **Attach files** to the custom emails.
* Set **email type** - choose which format of email to send (plain text, HTML, multipart). Optionally wrap email in **WooCommerce email template**.
* Send emails **manually**, for example, from the **Order actions** meta box on single order edit page, or in bulk from the **Bulk actions** dropdown on admin orders list.
* Set email **recipient(s)** - customer, admin, custom email address.
* Set email's **admin title**.
* **WPML** and **Polylang** compatible.
* **"High-Performance Order Storage (HPOS)"** compatible.
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

= 2.5.0 - 22/11/2023 =
* Dev - Order Options - "Require users" option added.
* Dev - Order Options - "Exclude users" option added.
* Dev - Order Options - "Require user roles" option added.
* Dev - Order Options - "Exclude user roles" option added.
* Dev - Code refactoring.

= 2.4.0 - 17/11/2023 =
* Dev - General - Advanced Options - "Use actions for WC email template" option removed (now always using actions).
* Dev - Email Data - "WC email template" option renamed to "Header & footer". Now the option is applied to "Plain text" emails as well.
* Dev - Using overridable email templates now.
* Dev - Code refactoring.
* Dev - Developers - `alg_wc_custom_emails_get_wc_email_template_part` filter removed.
* Tested up to: 6.4.
* WC tested up to: 8.3.

= 2.3.0 - 03/11/2023 =
* Fix - Admin Options - Admin actions - Orders > Bulk actions - HPOS compatibility (`wc-orders` page).
* WC tested up to: 8.2.

= 2.2.9 - 25/09/2023 =
* WC tested up to: 8.1.
* Plugin icon, banner updated.

= 2.2.8 - 17/08/2023 =
* Dev - Developers - `alg_wc_custom_emails_is_enabled` filter - `$do_force_send` parameter added.

= 2.2.7 - 16/08/2023 =
* Fix - Declaring HPOS compatibility for the free plugin version, even if the Pro version is activated.
* Dev - General - "Base dir" option added (defaults to "WP root directory"). Affects the "Email attachments" options.
* Dev - Debug - Email attachments info added.

= 2.2.6 - 09/08/2023 =
* Dev - Admin settings - Delay - Description updated.
* Tested up to: 6.3.
* WC tested up to: 8.0.

= 2.2.5 - 04/08/2023 =
* Dev - Shortcodes - `[user_prop]` shortcode added.
* Dev - Developers - `alg_wc_custom_emails_is_user_email` filter - `current_filter()` parameter added.

= 2.2.4 - 03/08/2023 =
* Dev - Shortcodes - `[order_item_meta]` shortcode added.

= 2.2.3 - 18/07/2023 =
* Dev – "High-Performance Order Storage (HPOS)" compatibility.
* Dev - Developers - `alg_wc_custom_emails_subject` filter added.
* Dev - Developers - `alg_wc_custom_emails_content` filter added.
* WC tested up to: 7.9.

= 2.2.2 - 18/07/2023 =
* Dev - Developers - `alg_wc_custom_emails_do_send_order_email` filter added.

= 2.2.1 - 28/06/2023 =
* Dev - Shortcodes - `[order_payment_method_id]` shortcode added.

= 2.2.0 - 22/06/2023 =
* Dev - Order Options - "Require payment gateways" option added.
* Dev - Order Options - "Exclude payment gateways" option added.
* Dev - Order Options - "Require shipping methods" option added.
* Dev - Order Options - "Exclude shipping methods" option added.
* Dev - General - Advanced Options - "Use actions for WC email template" option added (defaults to `no`).
* Dev - Developers - `alg_wc_custom_emails_get_wc_email_template_part` filter added.

= 2.1.0 - 18/06/2023 =
* Fix - Admin settings - "Shortcodes" link fixed in the "placeholder text".
* Dev - General - "Enable plugin" option removed.
* Dev - General - "Custom triggers" option added.
* Dev - Shortcodes - `[if]` - `operator` - `in` and `not_in` operators added.
* Dev - Shortcodes - `[order_item_product_ids]` shortcode added.
* Dev - Shortcodes - `[order_downloads]` shortcode added.
* Dev - Shortcodes - `[order_user_data]` shortcode added. E.g., `[order_user_data key="user_nicename"]`.
* Dev - Shortcodes - `[order_user_id]` shortcode added.
* Dev - Developers - `alg_wc_ce_send_email()` function added.
* Dev - Developers - `alg_wc_custom_emails_check_order_products` filter added.
* Dev - Developers - `alg_wc_custom_emails_check_order_product_terms` filter added.
* Dev - Code refactoring.
* WC tested up to: 7.8.

= 2.0.0 - 09/05/2023 =
* Dev - Code refactoring - `Alg_WC_Custom_Email` class.
* WC tested up to: 7.6.

= 1.9.7 - 13/04/2023 =
* Fix - Email Data - Email attachments - Handling empty option value properly now.

= 1.9.6 - 10/04/2023 =
* Dev - Advanced Options - "Exclude recipients" option added.
* Dev - Admin Settings - Minor option titles, descriptions, style update.

= 1.9.5 - 05/04/2023 =
* Dev - Scheduled - "Unschedule email" (i.e., "Delete") buttons added.
* Dev - Developers - `alg_wc_custom_emails_do_send` filter added.

= 1.9.4 - 05/04/2023 =
* Fix - Admin Options - Admin actions - Empty value fixed.

= 1.9.3 - 31/03/2023 =
* Dev - Shortcodes - `[order_details]` - Now passing the email object to the `WC_Emails::order_details()` function.

= 1.9.2 - 31/03/2023 =
* Dev - Email Data - "Email attachments" option added.
* Tested up to: 6.2.

= 1.9.1 - 23/03/2023 =
* Dev - WPML/Polylang language - Order language detection algorithm improved.
* Dev - WPML/Polylang language - Admin option renamed (was "Require WPML language").
* Dev - WPML/Polylang language - Code moved to the `Alg_WC_Custom_Email_Order_Validator` class.
* WC tested up to: 7.5.

= 1.9.0 - 08/03/2023 =
* Dev - Admin Options - Admin actions - "Orders > Preview" option added.
* Dev - Admin Options - Admin actions - "Orders > Actions column" option added.
* Dev - Admin Options - Admin actions - Code refactoring.
* Dev - Developers - `alg_wc_custom_emails_order_product_term_ids` filter added.

= 1.8.0 - 08/03/2023 =
* Dev - Admin Options - "Admin actions" option added (defaults to "Edit order > Order actions" and "Orders > Bulk actions").
* Dev - Admin Options - Admin settings rearranged ("Settings Tools" subsection added).
* Dev - Order Options - Require/Exclude product categories/tags - Listing empty categories/tags as well now.
* Dev - Order Options - "Logical operator" option added (defaults to `AND`).
* Dev - Order Options - Code refactoring (`Alg_WC_Custom_Email_Order_Validator` class added).
* Dev - Shortcodes - `[if]` - Code refactoring.
* Dev - Compatibility - "Email Customizer for WooCommerce (Pro)" by ThemeHigh - PHP notice (regarding calling the `wc_get_product()` function too early) fixed.
* WC tested up to: 7.4.

= 1.7.2 - 26/01/2023 =
* Dev - Admin Options - "Copy settings" tool added.
* Dev - Admin Options - "Reset settings" tool added.
* Dev - Shortcodes - `[generate_coupon_code]` - Coupon description (`post_excerpt`) updated.

= 1.7.1 - 21/01/2023 =
* Dev - Order Options - Require/Exclude products - Now using AJAX in admin settings.
* WC tested up to: 7.3.

= 1.7.0 - 24/12/2022 =
* Dev - Email Data - "Require WPML language" option added.
* Dev - Shortcodes - `[translate]` shortcode added (for WPML and Polylang plugins).

= 1.6.0 - 21/12/2022 =
* Dev - Order Options - "Require product categories" option added.
* Dev - Order Options - "Exclude product categories" option added.
* Dev - Order Options - "Require product tags" option added.
* Dev - Order Options - "Exclude product tags" option added.
* Dev - Admin settings rearranged; subsection titles added.
* Dev - Code refactoring.
* Deploy script added.
* Tested up to: 6.1.
* WC tested up to: 7.2.

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
