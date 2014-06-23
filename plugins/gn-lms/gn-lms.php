<?php
/*
Plugin Name: Gnaritas LMS
Plugin URI: http://nowhere.com/
Description: Library code to implement Gnaritas LMS functionality
Version: 1.0
Author: Gnaritas, Inc.
Author URI: http://www.gnaritas.com/

*/

require_once(WP_PLUGIN_DIR . "/gnaritas_wordpress_library/gnaritas_library.php");
require_once("classes/gnlms_LMS.php");
require_once("classes/gnlms_Data.php");
require_once("classes/gnlms_ListWidget.php");
require_once("classes/gnlms_Report.php");
require_once("classes/gnlms_ReportData.php");

function gnlms_update_status () {
	$data = new gnlms_Data();
	$data->updateCourseRegistrationStatus();
}

function gnlms_register_cron ($name, $interval='hourly') {
	if(!wp_next_scheduled($name)) {
		wp_schedule_event(time(), $interval, $name);
	}
}

function gnlms_register_status_update () {
	/*
	if(!wp_next_scheduled('gnlms_status_update')) {
		wp_schedule_event(time(), 'hourly', 'gnlms_status_update');
	}
	*/
	gnlms_register_cron('gnlms_status_update');
}

function gnlms_register_admin_alerts () {
	gnlms_register_cron('gnlms_admin_alerts');
}

add_action('gnlms_status_update', 'gnlms_update_status');
add_action('wp', 'gnlms_register_status_update');

// wp_clear_scheduled_hook('gnlms_status_update');

$gnlms = new gnlms_LMS();
add_action('wp', 'gnlms_register_admin_alerts');
add_action('gnlms_admin_alerts', array(&$gnlms, 'doAdminAlerts'));

register_activation_hook( __FILE__, array(&$gnlms->data, 'insureTables'));



?>