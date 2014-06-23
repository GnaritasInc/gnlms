=== DW Admin Block ===

Contributors: DanielWoolnough
Donate link: http://www.danielwoolnough.com/contact/coffee/
Tags: wordpress, hide, block, admin, wp-admin, admin interface, admin, access,
Requires at least: 3.0
Tested up to: 3.2.1
Stable tag: 1.0

DW Admin Block, blocks access to the admin interface based on their user capabilities. 

== Description ==

DW Admin Block is a small plugin that denies access to the WordPress admin interface based on a users capability. The default is only to allow access to admins and editors but the capability can be set manualy by the user by editing the config file.

Block access to the admin interface based on user capability. The default is only editors and admins are allowed access, but the required capability can be set by editing the plugin file.

You can change the configuration of the plugin by modifying the file itself. The two config variables are on lines 24 and 31.

== Installation ==

1. Download
2. Upload to your `/wp-contents/plugins/` directory.
3. Activate the plugin through the 'Plugins' menu in WordPress.

== Configuration ==

= Edit who can access =

To edit who can access the WordPress admin interface, go to line 24 in the configuration file and change the varible there. You will need to have read <http://codex.wordpress.org/Roles_and_Capabilities#Capabilities> first before even trying to change what's there.

= Where to redirect to? =

This is up to you. By default, the plugin will redirect users to the homepage of the site/blog. By adding something on line 31, you can choose where to send users to Personally, I reccomend creating an access denied page and sending them there.

= Single site or WPMU? =

This plugin is capable of use on single site installations and on WPMU sites. On line 74 is where the single site installation config is. This is not commented out by default for use on single site installations. If you wish to use this plugin on WPMU sites, comment out line 74 and remove the comment out on line 76.

== Frequently Asked Questions ==

= Can I use this on my Single site WP Installation? =

Yes, please take a look at the Configuration section for more information.

= Can I use this on my WPMU site? =

Yes, please take a look at the Configuration section for more information.

= Can I change the redirection url? =

Yes, you need to edit the plugin file and change the variable on line 21.

= Can I change the required capability? =

Yes, please take a look at the Configuration section for more information.

= I have another question... =

If you have an questions that haven't been answered hear already, please contact me. Please note, I do not actively support this plugin, so I'm not likely to make any changes to meet any specific user requirements. Thanks <http://www.danielwoolnough.com/contact/>

== Changelog ==

= 1.0 =
* Initial Release

== Screenshots ==

*This is not really a plugin you can give a screen shot to. You will have to try it out to find out.

== Support Questions ==

If you have an questions that haven't been answered hear already, please contact me. Please note, I do not actively support this plugin, so I'm not likely to make any changes to meet any specific user requirements. Thanks <http://www.danielwoolnough.com/contact/>