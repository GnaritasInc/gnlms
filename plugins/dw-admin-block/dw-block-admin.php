<?php
/*
Plugin Name: 		DW Admin Block
Plugin URI: 		http://www.danielwoolnough.com/product/dw-admin-block/
Tags:				wordpress, hide, block, admin, wp-admin, admin interface, admin, access,
Description: 		DW Admin Block, blocks access to the admin interface based on their user capabilities. 
Version: 			1.0
Requires at least: 	3.0
Tested up to: 		3.2.1
Author: 			Daniel Woolnough
Author URI:			http://www.danielwoolnough.com/
*/

/* This plugin has been released under the GNU GPL License V3.0 at: http://www.gnu.org/licenses/gpl.txt */

/* ===Configuration=== */

/*	
	If you want to change the level that users need to be before they are allowed access
	to the admin interface, you can change the following to allow this. Please make sure 
	you read http://codex.wordpress.org/Roles_and_Capabilities#Capabilities before making
	any changes at all to the following line.
*/
$dwab_required_capability = 'edit_others_posts';

/* 
	Here is where tiy can change where users are redirected to.
	If you leave this blank, users will be sent to the homepage but i reccomend making your
	own access denied webpage.
*/
$dwab_redirect_to = '';

/* 
	If you wish to make upgrades easier, add the two lines below to your wp-config.php.
	Make sure you edit them first and do not post them as-is.
*/
/*
	define('DWAB_REQUIRED_CAPABILITY', 'edit_others_posts');
	define('dwab_REDIRECT_TO' , 'http://enter-url-here');
*/

/* ===End of Configuration=== */

/* ===EDIT BELOW AT YOUR OWN RISK!=== */

/* Override these values from the constants if they are defined and not empty */
if (defined('DWAB_REQUIRED_CAPABILITY'))
	$dwab_required_capability = DWAB_REQUIRED_CAPABILITY;
if (defined('DWAB_REDIRECT_TO'))
	$dwab_redirect_to = DWAB_REDIRECT_TO;

if (!function_exists('dwab_init')) {
	
	function dwab_init() {
		
		/* We need the config vars inside the function */
		global $dwab_required_capability, $dwab_redirect_to;
		
		/* Is this the admin interface? */
		if (
			/* Look for the presence of /wp-admin/ in the url */
			stripos($_SERVER['REQUEST_URI'],'/wp-admin/') !== false
			&&
			/* Allow calls to async-upload.php */
			stripos($_SERVER['REQUEST_URI'],'async-upload.php') == false
			&&
			/* Allow calls to admin-ajax.php */
			stripos($_SERVER['REQUEST_URI'],'admin-ajax.php') == false
		) {
			
			/* Does the current user fail the required capability level? */
			/* Comment out line 74 if you wish to use this on WPMU and uncomment out line 76. */
			/* Single Blog Installation Control  */
			if (!current_user_can($dwab_required_capability)) {
			/* WPMU Installation Control */
//			if (!is_site_admin()) {  
				
				/* Do we need to default to the site homepage? */
				if ($dwab_redirect_to == '') { $dwab_redirect_to = get_option('siteurl'); }
				
				/* Send a temporary redirect */
				wp_redirect($dwab_redirect_to,302);
			}
		}
	}
}
/* Add the action with maximum priority */
add_action('init','dwab_init',0);
?>