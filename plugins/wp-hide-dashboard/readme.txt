=== WP Hide Dashboard ===
Contributors: kpdesign
Donate link: http://kpdesign.net/wphidedash/donate/
Tags: admin, administration, dashboard, hide, multisite, buddypress
Requires at least: 3.1
Tested up to: 3.1
Stable tag: 2.1

Hide the Dashboard menu, Personal Options section and Help link on the Profile page from your subscribers when they are logged in.

== Description ==

This plugin removes the Dashboard menu, the Personal Options section and the Help link on the Profile page, hides the Dashboard links in the admin bar menu (if activated), and prevents Dashboard access to users assigned to the <em>Subscriber</em> role. Useful if you allow your subscribers to edit their own profiles, but don't want them wandering around your WordPress admin section.

Users belonging to any of the other WordPress roles will continue to see and have access to the other sections of the WordPress admin that correspond to their role's capabilities.

WP Hide Dashboard has been tested with WordPress in Single user mode, Multisite mode, and with BuddyPress, and works with all of them.

<strong>Note: Version 2.1 requires a minimum of WordPress 3.1. If you are running a version less than that, please upgrade your WordPress install before installing or upgrading.</strong>

= Works With: =

The following is a list of plugins that work well (no conflicts) with the WP Hide Dashboard plugin:

* [Members](http://wordpress.org/extend/plugins/members/ "Members") by Justin Tadlock
* [BuddyPress](http://wordpress.org/extend/plugins/buddypress/ "BuddyPress") by the BuddyPress team
* [Admin Bar Minimiser](http://wordpress.org/extend/plugins/admin-bar-minimiser/ "Admin Bar Minimiser") by David Gwyer
* [Logged Out Admin Bar](http://wordpress.org/extend/plugins/logged-out-admin-bar/ "Logged Out Admin Bar") by Peter Westwood

= Known Conflicts: =

The following is a list of plugins that are known to have conflicts with the WP Hide Dashboard plugin:

* [Role Manager](http://www.im-web-gefunden.de/wordpress-plugins/role-manager/ "Role Manager") (Use the [IWG Hide Dashboard](http://www.im-web-gefunden.de/wordpress-plugins/iwg-hide-dashboard/ "IWG Hide Dashboard") plugin to hide the dashboard link.)
* [Role Scoper](http://wordpress.org/extend/plugins/role-scoper/ "Role Scoper")
* [Flutter](http://wordpress.org/extend/plugins/fresh-page/ "Flutter")

Note: Please let me know if there are other plugins that conflict with WP Hide Dashboard, and I'll add them to the list.

= Support: =

Support is provided at: http://kpdesign.net/wphidedash/

== Installation ==

= Installation Instructions: =

This plugin works in either the `/wp-content/plugins/` or the `/wp-content/mu-plugins/` directory.

If installing the plugin in the `/wp-content/plugins/` directory:

1. Download the plugin and unzip it to a folder on your computer.
2. Upload the entire `wp-hide-dashboard` folder to that directory.
3. Activate the plugin through the Plugins section in WordPress.
4. That's it - no configuration necessary.

If installing the plugin in the `/wp-content/mu-plugins/` directory:

1. Download the plugin and unzip it to a folder on your computer.
2. Upload the wp-hide-dashboard.php file to the `/wp-content/mu-plugins/` directory.
3. The plugin will automatically run - there is no activation or configuration needed.

== Frequently Asked Questions ==

**Q. Why did you remove support for other roles from Version 2.1?**

I removed support for menu items for roles other than Subscriber. WP Hide Dashboard is a simple plugin, and was built for the Subscriber role. While I appreciate that some users need this type of functionality for other roles, most don't require it, so I don't want to bloat the plugin with unnecessary code for the majority of users.

If you need to hide admin sidebar menu links from roles other than Subscriber, I recommend using the [Admin Menu Editor plugin](http://wordpress.org/extend/plugins/admin-menu-editor/ "Admin Menu Editor plugin") to accomplish that goal. Currently it only removes links in the admin sidebar menu. The plugin author has indicated they may add support for removing admin bar menu links in a future version.

**Q. How do I change this to hide the Dashboard link, Tools menu, Personal Options, and Help options from other roles besides Subscriber?**

A. To hide these from other roles, you will need to edit the plugin in a plain text editor and make the following changes:

**Version 2.1:**

Support for removing other admin menu links has been removed from this version (see response to question above).

**Version 1.5 and 2.0:**

You will need to change the capability (line 44 in version 1.5, and line 46 in version 2.0):

`/* Plugin config - user capability for the top level you want to hide everything from */
$wphd_user_capability = 'edit_posts'; /* [default for subscriber level = edit_posts] */`

* Subscriber -> Contributor: Change `edit_posts` to `upload_files`
* Subscriber -> Author: Change `edit_posts` to `manage_categories`
* Subscriber -> Editor: Change `edit_posts` to `manage_options`

**Version 1.4 and below:**

There are 3 instances of this code in the plugin - make sure you change all of them.

* Subscriber -> Contributor: Change `!current_user_can('edit_posts')` to `!current_user_can('upload_files')`
* Subscriber -> Author: Change `!current_user_can('edit_posts')` to `!current_user_can('manage_categories')`
* Subscriber -> Editor: Change `!current_user_can('edit_posts')` to `!current_user_can('manage_options')`

== Screenshots ==

1. WordPress 3.1 default Subscribers profile page (single user - admin bar dropdown showing)
2. WordPress 3.1 Subscribers profile page with WP Hide Dashboard activated (single user - admin bar dropdown showing)
3. WordPress 3.1 default Subscribers profile page (Multisite - admin bar showing)
4. WordPress 3.1 Subscribers profile page with WP Hide Dashboard activated (Multisite - admin bar showing)
5. WordPress 3.1 website (Multisite - admin bar dropdown showing)
6. WordPress 3.1 website with WP Hide Dashboard activated (Multisite - admin bar dropdown showing)

== Changelog ==

= Version 2.1: =
* Support for WordPress version 2.9 and 3.0 removed.
* Support for roles other than Subscriber removed.
* Reworked code for removing Personal Options section on Profile page.
* Added support for removing Dashboard links in new admin bar.
* Added support for WordPress Multisite (network-activation-capable).

= Version 2.0: =
* Code reworked; support for WordPress version 2.5 - 2.8 removed.
* Updated menu removal code for compatibility with WordPress 3.0 (single user mode).
* Added code to remove Personal Options section on Profile page (props to Matthew Pollotta).

= Version 1.5: =
* Added code to make it easier to configure plugin if you want to change the role/capability level.
* Added code to remove Tools menu.
* Added code to remove Settings, Media and Comments menus for Contributors+ if needed.
* Added code to remove WordPress upgrade nag notice (admin will still see the notice).

= Version 1.4: =
* Added code to remove Tools menu in 2.8.x (menu numbering changed in core).
* Added Frequently Asked Questions and proper Changelog sections to readme.txt file.

= Version 1.3: =
* Fixed error in WordPress version checking.

= Version 1.2: =
* Added removal of Help link on Profile page.

= Version 1.1: =
* Added WordPress version checking.
* Added code for defining path to /wp-content/plugins/ if outside the WordPress directory.
* Added removal of Tools menu and collapsible arrow from the menu area in 2.7.x.

= Version 1.0: =
* Initial release

== Upgrade Notice ==

= Version 2.1: =
See changelog for full list of changes: http://wordpress.org/extend/plugins/wp-hide-dashboard/changelog/. Note: This version requires a minimum of WordPress 3.1. If you are running a version less than that, upgrade your WordPress install now, then upgrade to the latest version of the plugin.