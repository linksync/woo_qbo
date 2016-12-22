=== Woocommerce Quickbooks ===
Tags: linksync, woocommerce, download, downloadable
Requires at least: 3.0.1
Tested up to: 3.4
Stable tag: 4.3
License: GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)

WooCommerce extension for syncing inventory and order data with other apps

== Description ==
WooCommerce extension for syncing inventory and order data with other apps, including Xero, QuickBooks Online, Vend, Saasu and other WooCommerce sites.

For backwards compatibility, if this section is missing, the full length of the short description will be used, and
Markdown parsed.

A few notes about the sections above:

*   "Contributors" is a comma separated list of wordpress.org usernames
*   "Tags" is a comma separated list of tags that apply to the plugin
*   "Requires at least" is the lowest version that the plugin will work on
*   "Tested up to" is the highest version that you've *successfully used to test the plugin*. Note that it might work on
higher versions... this is just the highest one you've verified.
*   Stable tag should indicate the Subversion "tag" of the latest stable version, or "trunk," if you use `/trunk/` for
stable.

    Note that the `readme.txt` of the stable tag is the one that is considered the defining one for the plugin, so
if the `/trunk/readme.txt` file says that the stable tag is `4.3`, then it is `/tags/4.3/readme.txt` that'll be used
for displaying information about the plugin.  In this situation, the only thing considered from the trunk `readme.txt`
is the stable tag pointer.  Thus, if you develop in trunk, you can update the trunk `readme.txt` to reflect changes in
your in-development version, without having that information incorrectly disclosed about the current stable version
that lacks those changes -- as long as the trunk's `readme.txt` points to the correct stable tag.

    If no stable tag is provided, it is assumed that trunk is stable, but you should specify "trunk" if that's where
you put the stable version, in order to eliminate any doubt.

== Installation ==

This section describes how to install the plugin and get it working.

e.g.

1. Upload the plugin files to the `/wp-content/plugins/linksync` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Use the Settings->Plugin Name screen to configure the plugin
1. (Make your instructions match the desired user flow for activating and installing your plugin. Include any steps that might be needed for explanatory purposes)

= Minimum Requirements =

* WordPress 3.8 or greater
* PHP version 5.2.4 or greater
* MySQL version 5.0 or greater

== Frequently Asked Questions ==
= Can I have a product in my Vend store that's not in my WooCommerce site, and vice versa? =
The answer to this questions depends on how you are syncing orders between the two systems -  the products MUST exist in the destination store, otherwise the sync will fail. For example, if you sync orders from WooCommerce to Vend, and you had products listed online that do not exist in Vend, orders with those products will be rejected by Vend.

= Can images be synced between Vend and WooCommerce? =
Yes and no. We can sync images from Vend to WooCommerce, so if you have product image in Vend then we can sync them to products in WooCommerce, but Vend does not support images uploading at this time, so we are not able to sync or push images from WooCommerce to Vend.

= Does linksync for Vend and WooCommerce support Composite products in Vend? =
At this time we do not support syncing of Composite products to WooCommerce as there is no concept at this time of 'composite' products in WooCommerce.

= Do I need to do a manual sync to get inventory for orders? =
No, you don't. linksync would automatically sync your orders and products.

= Do we support the latest version for WooCommerce and Vend? =
Yes, we do support the latest version on both apps.

= Help! Products have dissapeared from my WooCommerce store =
All SKUs in Vend and WooCommerce must be unique for linksync to work without issues. Please refer to FAQ 3 above.

If you have synced data from Vend to WooCommerce and products have mysteriously disappeared, it's a near certainty that you have duplicate SKUs in your Vend store.

If you think this might be an issue for you, then we suggest the following steps:

Disable product syncing in linksync.
If you have a backup of your WooCommerce database from before you synced data with WooCommerce, then restore it now. If not, they you'll need to use the WP-Sweep plugin to identify and remove duplicates.
Export all your products from Vend per instructions at How do I export my product list?
Use a tool like Excel's Conditional Formatting for Duplicate Values to identify duplicate SKUs in your Vend product data, and update the SKUs in Vend to make them unique.
Once this is done, delete your current linksync API Key and create a new one, covered in the first FAQ on this page. This will result in a 'clean' sync with Vend with no duplicate SKUs.
Update linksync plugin settings in WooCommerce with the new API Key, and re-sync.

= How are variants synced between Vend and WooCommerce? =
WooCommerce applies an SKU to a Variable Product, and has separate SKUs for each variation, whereas Vend simply has an SKU for each variation, with no SKU for the 'group' of variations the way WooCommerce does. Instead, they use the product Handle for this purpose, so linksync uses the Vend product Handle for the Variable product SKU in WooCommerce.

= I am syncing by tag from Vend to WooCommerce - is there anything I need to be aware of? =
Yes, there is an important limitation that you should be aware of. Once a product is in WooCommerce, changing its tag will not remove it from WooCommerce.

For example, lets say that you tag products in Vend with 'online' that you want to sync to WooCommerce. Later, if you remove that tag from product/s, linksync will not remove them from WooCommerce - you will have to that manually

= What do I need to know about syncing orders with Vend/Woocommerce? =
Syncing orders between the two apps would keep your sales inventory up to date with each other. However, unlike with product syncing, you are only able to do a one-way sync with orders. On the order syncing setting tab,   you have the option to choose a sync between: Vend to WooCommerce, WooCommerce to Vend  or disable the feature. See Syncing Options for WooCommerce and Vend for more information.

= What fields are used between Vend and WooCommerce to sync data? =
We use the SKU field from from both systems as the 'common identifier' when syncing product information between the two systems.

= What happens if a refund is created for an order? =
linksync only supports syncing of order data one-way. Refunds do not sync or show in your other app, you will need to process this manually.

= What happens to SKUs with spaces in WooCommerce when linksync is enabled? =
Vend does not support spaces in SKUs, so when linksync is first installed on WooCommerce, it will find and remove any spaces in existing product and variant SKUs. Spaces in SKUs will also be removed when creating new product in WooCommerce.

= What if I have duplicate SKUs in either or both systems? =
All SKUs in Vend and WooCommerce must be unique for linksync to work without issues.

Vend does not require each SKU to be unique, meaning you can have multiple products with the same SKU in Vend. This is bad news for WooCommerce, where every SKU must be unique.

Note: In the event you have duplicate SKUs in WooCommerce, only one of those SKUs will be correctly synced to Vend, or worst still your products with duplicate SKU will disappear from WooCommerce. See FAQ Help! Products have dissapeared from my WooCommerce store

below for more info on this.

If you think this might be an issue for you the see FAQ Help! Products have dissapeared from my WooCommerce store.


== Screenshots ==

1. You'll see linksync settings under Woocommerce menu.

== Changelog ==

= 2.4.3 - 9 June 2016 =

* Fix issue product name having "\"
* Fix attribute option still changes woocommerce attribute on disable
* Fix some undefined variable error

= 2.4.2 - 27 May 2016 =

* Fixes some error
* Add fix for Constants variable and undefined variable
* Fix some SQL error

= 2.4.1 - 13 April 2016 =

* fix 'api key is empty' on update of api key

= 2.4.0 - 8 April 2016 =

* mysql_* functions was removed
* Manage API Tab was removed
* UI Update for API key's modal
* Updating API key was transferred to Configuration Tab
* Plugin file was restructured

= 2.3.2 - 29 March 2016 =

* Fix variable product becoming out of stock if it has one variants out of stock.

= 2.3.1 - 7 March 2016 =

* Product category with ampersand sign is now syncing properly
* Fix issue in syncing product tags in vend to woo
* Fix Internal connection error

= 2.3.0 - 5 January 2016 =

* New products created in Woo with ‘published’ status not showing until the product is re-saved via WooCommerce.
* Orders in Woo not updating the correct Outlet in Vend in some instances.
* Product description not updating correctly in some instances.
* Updates to the linksync plugin can be done with a single click via the WordPress plugin page.

= 2.2.9 - 17 Dec 2015 =

* [LWE-402] - Internal Connection Error
* [LWE-397] - Sync incorrect product image
* [LWE-394] - Sync Removing product variants
* [LWE-390] - Extra Variant is created on linksync in some instances
* [LWE-388] - Some products not showing on WooCommerce site
* [LWE-387] - Update URL not accessible
* [LWE-380] - Product Price Not Displaying in some instances
* Images are syncing into year/month folders when this options is disabled.
* Performance improvements with image syncing
* Product description and images added in Woo getting removed on sync.

= 2.2.8 - 18 Sep 2015 =

* [LWE-385] - Incorrect Tax rate pushing to Vend
* [LWE-384] - Woo to Vend Product syncs incorrect product price in some cases
* [LWE-383] - Prices not syncing for Woo to Vend in some cases
* [LWE-382] - Disabled ' Change product status' in WooCommerce based on stock quantity not working
* [LWE-377] - Product is getting deleted in Woo when full sync is happening from Vend to Woo
* [LWE-376] - Draft Products with Variants in WooCommerce when set to Publish creates a duplicate product variant in Vend
* [LWE-375] - triggering of update URL will only process 50 products in some instances
* [LWE-374] - Product Image Sync - if Vend products do not have product image, then image in Woo is being removed
* [LWE-372] - Duplicate Product Images in Media Library

= Version 2.2.7 - 30 Jul 2015 =

* [LWE-370] - syncs stuck on 'starting....' for some users.

= Version 2.2.6 - 18 Jul 2015 =

* [LWE-366] - Incorrect URL path used by linksync to update WooCommerce with changes from Vend, introduced in 2.2.5
* [LWE-363] - New product from Vend not creating in WooCommerce if 'sync title' disabled.

= Version 2.2.5 - 15 Jul 2015 =

* [LWE-362] - Attributes and values having issues with foreign language (eg Norwegian characters ø å and the like).
* [LWE-360] - Adding empty attribute to products in WooCommerce in some instances.
* [LWE-358] - change order "created" time when sending order data to Vend.
* [LWE-353] - resolved some minor page/console errors.
* [LWE-344] - sync stuck on 'starting....' in some cases.

= Version 2.2.4 - 30 Jun 2015 =

* [LWE-354] - in some instances two full syncs were required to update product variants in Woo.
* [LWE-352] - Under some circumstance, and when a variable product had no SKU, that product was being deleted from Woo.
* [LWE-350] - product 'Catalog Visibility' was being updated when 'change product status' option was disabled.
* [LWE-341] - order POST from Woo to Vend getting json error and failing in some instances.
* [LWE-313] - improved image handling for sites with many and/or large sized images.

= Version 2.2.3 - 12 Jun 2015 =

* [LWE-333] - new orders in WooCommerce not updating inventory to Vend due to recent changes to WooCommerce
* [LWE-329] - new logic to determine if Vend prices include tax or not, depending on the country a Vend store is associated with
* [LWE-327] - sync says 'starting' but never starts
* [LWE-326] - resolved error messages on settings pages. eg. 'Outlet data not found'
* [LWE-325] - changes to Product Syncing UI to make option settings simpler
* [LWE-324] - wrong logic for order sync for outlet settings Woo to Vend
* [LWE-323] - Change product tax logic and UI settings to now default to WooCommerce tax settings for including or excluding tax in prices
* [LWE-319] - changed logic for discounts on order sync Woo to Vend
* [LWE-318] - Tax mapping issues for orders Woo to Vend
* [LWE-317] - add 'waiting/loading' gif to settings pages
* [LWE-316] - tax on shipping not included in orders synced to Vend
* [LWE-310] - Changes not syncing to Vend when using product "Quick Edit" in Woo
* [LWE-309] - price being deleted from Vend
* [LWE-308] - payment mapping for orders Woo to Vend updated
* [LWE-307] - Sync not completing
* [LWE-300] - error when attempting to disable quantity sync on Product Sync Settings
* [LWE-297] - Check permissions are correct on plugin folder (wp-content/plugins/linksync) and displaying message if they are incorrect
* [LWE-293] - Time offset in log is odd
* [LWE-292] - add Vend order number to WooCommerce orders synced from Vend
* [LWE-291] - improved logic for 'Change product status in WooCommerce based on stock quantity' option
* [LWE-289] - issue with underscores and non-standard characters in sku field in WooCommerce
* [LWE-286] - Orders are not syncing from Vend to WooCommerce in some instances
* [LWE-285] - issue with product 'disappearing' from Woo after sync from Vend
* [LWE-284] - log truncation logic to prevent linksync log getting too big
* [LWE-283] - fatal error on enabling extension in some instances
* [LWS-126] - wrong price being set when syncing from Vend to Woo in some instances when multiple pricebooks existed for the same product in Vend.
* [LWS-107] - incomplete tag data being retrieved from Vend in some instances

= Version 2.2.1 - 9 Apr 2015 =

* [LWE-168] - One Order in vend is displaying as two Orders in WooCommerce
* [LWE-249] - resolved issue where some products in WooCommerce were failing to sync to Vend
* [LWE-250] - No option to add new API key after we delete it
* [LWE-255] - Created option to not remove attribute types or values in WooCommerce when syncing from Vend.
* [LWE-256] - vend price incorrect on order export
* [LWE-259] - Client issues with attributes, and tax on order export
* [LWE-262] - Multiple Random Images from Vend to Woo
* [LWE-263] - Total order value is displaying as 0 in vend when discount is applied in woo
* [LWE-264] - custom product field being wiped on sync
* [LWE-266] - admin option to set attributes as 'visible on product page'
* [LWE-267] - Order export off by 1c
* [LWE-272] - remove forward slash and unsupported characters from WooCommerce sku field to ensure syncing to Vend will not fail.
* [LWE-277] - Displaying blank pop up box when user clicks on sync all product to vend
* [LWE-278] - Attributes are not syncing to vend from woocommerce
* [LWE-279] - no access to linksycn API causes site performance issues

= Version 2.1.9 beta - 19 Feb 2015 =

* [LWE-165] - order POST - discount applied twice
* [LWE-200] - 'Import as guest' is importing the billing address also
* [LWE-212] - Product prices not being set correctly in the database for 'on sale'
* [LWE-222] - Price is displaying even though it is disabled while syncing
* [LWE-224] - add actions/outcomes to log
* [LWE-228] - register_id not being populated on order POST
* [LWE-229] - Save changes button disabled
* [LWE-231] - Order number in order POST is not correct
* [LWE-232] - add 'settings' link to plugin page
* [LWE-233] - variant values still being modified on sync
* [LWE-235] - Add comment to order generated by Order GET
* [LWE-240] - Issue where attributes and values where getting out of sync.
* [LWE-243] - Option added to let user choose direction of initial product sync - from Vend to WooCommerce, or to Vend from WooCommerce.
* [LWE-245] - issue with 'copy to short description' option not saving.
* [LWE-246] - Add additional product images from Vend if more than one exists.
* Minor bug fixes.

= Version 2.1.8 beta - 28 Jan 2015 =

* Performance improvements
* [LWS-44] - Support for international characters in product description.
* [LWE-188] - Initial sync of product from Vend to WooCommerce no longer times out, and now includes a progress indicator.
* [LWE-199] - order POST to Vend not using payment ID from WooCommerce.
* [LWE-202] - Hide linksync admin settings if using less than WooCommerce 2.2.x.
* [LWE-203] - update order import to include 'shipping details' as well as billing address.
* [LWE-207] - sync to Vend - auto-add sku to product WooCommerce if none exists, using product ID.
* [LWE-208] - Euro decimal separator causing product updates to Vend to fail.
* [LWE-210] - Product sync settings - if more than one outlet, then outlet is now mandatory.
* [LWE-211] - keep settings after deactivation of module.
* [LWE-212] - 'Sale price' for WooCommerce product being overwritten on sync from Vend.
* [LWE-213] - Attribute values aren't keeping their cases.
* [LWE-214] - New admin option - sync Vend price to 'Regular' or 'Sale Price' field in WooCommerce.
* [LWE-217] - attribute name getting modified on sync from Vend to WooCommerce.
* [LWE-218] - Order POST primary_email for 'guest' checkout.

= Version 2.1.7 beta - 10 Jan 2015 =

* Added support for non-inventory products.
* Added a time offset if host/server time is different to linksync server time.
* Introduced a 'lock' to the update process to prevent simultaneous linksync updates happening at the same time in WooCommerce, and causing duplicate product and/or product mis-configuration.
* Fixes to 'Sync product to Vend' function, and now includes status of sync. eg. syncing product 118 of 354.
* Fixes to order import and export operations.
* [LWE-52] - Product images from Vend are sometimes not saved correctly.
* [LWE-170] - linksync API Key must be valid for Vend and WooCommerce.
* [LWE-196] - Product categories in WooCommerce removed from product, even if linksync category sync option not enabled.
* Minor bug fixes.

= Version 2.1.5 beta - 18 Dec 2014 =

* Image import refactoring to improve performance.
* [LWE-132] - Fix creation of duplicate orders in Vend on change of order status in WooCommerce.
* [LWE-129] - Create/update of orders in WooCommerce now updating inventory in Vend when Order Syncing 'WooCommerce to Vend' is disabled.

= Version 2.1.4 beta - 17 Dec 2014 =

* Order syncing enabled.
* [LWE-130] - Added an option 'Set new product to Pending' so that new product imported from Vend aren't automatically published.
* Minor bug fixes.
= Version 2.1.2 beta - 11 Dec 2014 - initial beta release =