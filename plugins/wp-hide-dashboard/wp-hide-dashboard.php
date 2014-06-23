<?php
/*
Plugin Name: WP Hide Dashboard
Plugin URI: http://kpdesign.net/wphidedash/
Description: A simple plugin that removes the Dashboard menu, the Personal Options section and the Help link on the Profile page, hides the Dashboard links in the admin bar menu (if activated), and prevents Dashboard access to users assigned to the <em>Subscriber</em> role. Useful if you allow your subscribers to edit their own profiles, but don't want them wandering around your WordPress admin section. <strong>Note: This version requires a minimum of WordPress 3.1. If you are running a version less than that, please upgrade your WordPress install now.</strong>
Author: Kim Parsell
Author URI: http://kpdesign.net/
Version: 2.1
License: MIT License - http://www.opensource.org/licenses/mit-license.php

Copyright (c) 2008-2011 Kim Parsell
Personal Options removal code: Copyright (c) 2010 Large Interactive, LLC, Author: Matthew Pollotta
Originally based on IWG Hide Dashboard plugin by Thomas Schneider, Copyright (c) 2008 (http://www.im-web-gefunden.de/wordpress-plugins/iwg-hide-dashboard/)

Permission is hereby granted, free of charge, to any person obtaining a copy of this
software and associated documentation files (the "Software"), to deal in the Software
without restriction, including without limitation the rights to use, copy, modify, merge,
publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons
to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or
substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/

/* Disallow direct access to the plugin file */
if (basename($_SERVER['PHP_SELF']) == basename (__FILE__)) {
	die('Sorry, but you cannot access this page directly.');
}

/* Plugin config - user capability for the top level you want to hide everything from */
$wphd_user_capability = 'edit_posts'; /* [default for Subscriber role = edit_posts] */

/* WordPress 3.1 introduces the admin bar in both the admin area and the public-facing site. For subscribers, there's also now
	a pesky link to the Dashboard and a redundant link to the user's profile in the My Account menu. Let's remove the Dashboard
	link and only show the Profile link on the site. */

function wphd_remove_admin_bar_links() {
	global $blog, $current_user, $id, $wp_admin_bar, $wphd_user_capability;

	if ((!current_user_can(''.$wphd_user_capability.'') || !current_user_can_for_blog($blog->userblog_id, ''.$wphd_user_capability.'')) && is_admin_bar_showing()) {

		/* If Multisite, check whether they are assigned to any network sites before removing links */
		$user_id = get_current_user_id();
		$blogs = get_blogs_of_user($user_id);
		if (is_multisite() && empty($blogs)) {
			return;
		} else if (is_admin() || is_blog_admin()) {
			$wp_admin_bar->remove_menu('edit-my-profile');
		}
		$wp_admin_bar->remove_menu('dashboard');

	}

}

add_action('admin_bar_menu', 'wphd_remove_admin_bar_links', 100);

/* There's also an admin bar menu in WordPress Multisite that lists all of the network sites that a user belongs to, which includes links
	to each site's Dashboard. Let's remove the default menu and create one of our own that only links to the sites a user belongs to. */

function wphd_custom_my_sites_menu() {
	global $blog, $current_user, $wpdb, $wp_admin_bar, $wphd_user_capability;

	if (is_multisite()) {
		if (!current_user_can_for_blog($blog->userblog_id, ''.$wphd_user_capability.'') && is_admin_bar_showing() && is_user_logged_in()) {
			/* Add custom My Sites menu if the user is assigned to one or more sites. */
			if (count($wp_admin_bar->user->blogs) < 1)
				return;

			$wp_admin_bar->add_menu(array('id' => 'my-blogs', 'title' => __('My Sites'), 'href' => $null));

			$default = includes_url('images/wpmini-blue.png');

			foreach ((array) $wp_admin_bar->user->blogs as $blog) {
				$blavatar = '<img src="'.esc_url($default).'" alt="'.esc_attr__('Blavatar').'" width="16" height="16" class="blavatar" />';
				$blogname = empty( $blog->blogname ) ? $blog->domain : $blog->blogname;
				$wp_admin_bar->add_menu(array('parent' => 'my-blogs', 'id' => 'blog-'.$blog->userblog_id, 'title' => $blavatar.$blogname, 'href' => get_home_url($blog->userblog_id)));
			}
		}

	}

}

remove_action('admin_bar_menu', 'wp_admin_bar_my_sites_menu', 90);
add_action('admin_bar_menu', 'wphd_custom_my_sites_menu', 100);

/* Now for the admin sidebar menu and the profile page. Let's hide the Dashboard link, Help menu, Favorites menu, Upgrade notice, and
	the Personal Options section. */

function wphd_hide_dashboard() {
	global $blog, $current_user, $id, $parent_file, $wphd_user_capability, $wp_db_version;

	if ($wp_db_version < 17056) {
		return;

	} else if ((!current_user_can(''.$wphd_user_capability.'') || !current_user_can_for_blog($blog->userblog_id, ''.$wphd_user_capability.'')) && $wp_db_version >= 17056) {

		/* First, let's get rid of the Help menu, update nag, Personal Options section */
		echo "\n" . '<style type="text/css" media="screen">#your-profile { display: none; } .update-nag, #screen-meta, .color-option, .show-admin-bar { display: none !important; }</style>';
		echo "\n" . '<script type="text/javascript">jQuery(document).ready(function($) { $(\'form#your-profile > h3:first\').hide(); $(\'form#your-profile > table:first\').hide(); $(\'form#your-profile\').show(); });</script>' . "\n";

		/* Now, let's fix the sidebar admin menu - go away, Dashboard link. */
		/* If Multisite, check whether they are in the User Dashboard before removing links */
		$user_id = get_current_user_id();
		$blogs = get_blogs_of_user($user_id);
		if (is_multisite() && empty($blogs)) {
			return;
		} else if (function_exists('remove_menu_page')) {
			remove_menu_page('index.php');							/* Hides Dashboard menu in 3.1 */
			remove_menu_page('separator1');							/* Hides arrow separator under Dashboard link in 3.1*/
			remove_submenu_page('profile.php', 'profile.php');		/* Hides Profile submenu link in 3.1. Really don't need to see it twice, do we? */
		}

		/* Last, but not least, let's redirect folks to their profile when they login or if they try to access the Dashboard via direct URL */
		if (is_multisite() && empty($blogs)) {
			return;
		} else if ($parent_file == 'index.php') {
			if (headers_sent()) {
				echo '<meta http-equiv="refresh" content="0;url='.admin_url('profile.php').'">';
				echo '<script type="text/javascript">document.location.href="'.admin_url('profile.php').'"</script>';
			} else {
				wp_redirect(admin_url('profile.php'));
				exit();
			}
		}

	}

}

add_action('admin_head', 'wphd_hide_dashboard', 0);

/* That's all folks. You were expecting more? */

?>