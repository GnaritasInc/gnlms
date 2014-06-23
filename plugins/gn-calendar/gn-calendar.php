<?php
/*
Plugin Name: Gnaritas Calendar
Plugin URI: http://nowhere.com/
Description: TBD
Version: 1.0
Author: Gnaritas, Inc.
Author URI: http://www.gnaritas.com/


Note: This requires the "Gnaritas Library Functions" plugin to be activated.

*/
require_once(WP_PLUGIN_DIR.'/gnaritas_wordpress_library/gnaritas_library.php');

require_once("_classloader.php");

if(class_exists('gn_Calendar')) {

	$gnCalendar = new gn_Calendar();
	register_activation_hook(__FILE__, array(&$gnCalendar, 'install'));

}

?>