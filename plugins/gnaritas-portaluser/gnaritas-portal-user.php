<?php
/*
Plugin Name: Gnaritas Portal User
Plugin URI: http://nowhere.com/
Description: Library code to support Gnaritas portal.
Version: 1.0
Author: Gnaritas, Inc.
Author URI: http://www.gnaritas.com/


*/

// *************************************************************

function gnlms_user_in_role ($role, $user=null) {
	if (current_user_can('level_0')){
	if(!$user) {
		$user = wp_get_current_user();
	}

	//print_r($user->roles);
	return ($user && in_array($role, $user->roles));
}
else {
	return (false);
}
}

// *************************************************************

function gnInclude($file) {
	error_log('Including:'.$file);
	ob_start();
	include ($file);
	return ob_get_clean();
}



// *************************************************************
function redirect_to_front_page() {
	global $redirect_to;
	if (!isset($_GET['redirect_to'])) {
		$redirect_to = get_option('siteurl');
	}

	$redirect_to = get_option('siteurl');
}

add_action('login_form', 'redirect_to_front_page');


// ******************************

function gn_go_home () {

wp_redirect( home_url() );
  exit;
}

add_action( 'wp_logout', 'gn_go_home');


// *************************************************************

function gn_login_form() {
	return(gnInclude("includes/login_form.php"));
}

add_shortcode("gn_loginform", 'gn_login_form');


// *************************************************************
// Home page  

function gn_front_page ($value) {
	$page = null;
	$title = "";
	if(gnlms_user_in_role('lms_admin')) {
		// $value=71;		
		// $page = get_page_by_title("Admin Dashboard");
		$title = get_option('gnlms_front_page_admin');
	}
	else if (gnlms_user_in_role('lms_user')) {
 		// $value=23;
 		// $page = get_page_by_title("User Dashboard");
 		$title = get_option('gnlms_front_page_user');
	}
	
	if($title) {
		$page = get_page_by_title($title);
	}
	
	
	return $page ? $page->ID : $value;
	

}

add_filter( "option_page_on_front", 'gn_front_page' , 10, 1);


function gn_set_default_front_pages () {
	$defaults = array(
		"gnlms_front_page_admin"=>"Admin Dashboard",
		"gnlms_front_page_user"=>"User Dashboard"
	);
	
	foreach ($defaults as $key=>$value) {
		add_option($key, $value);
	}
}

register_activation_hook( __FILE__, 'gn_set_default_front_pages');

function gn_front_page_section_output () {
	echo "<p>Enter page titles for LMS admin and user home pages.</p>";
}

function gn_text_field ($args) {
	$id=$args[0];
	echo "<input type='text' name='$id' id='$id' value=\"".htmlspecialchars(get_option($id))."\"/>";
}

function gn_settings_init () {
	
	add_settings_section('gn_front_page_section','Role-based home page settings', 'gn_front_page_section_output','reading');
	
	add_settings_field('gnlms_front_page_admin', 'Admin home page', 'gn_text_field', 'reading', 'gn_front_page_section', array("gnlms_front_page_admin"));

	add_settings_field('gnlms_front_page_user', 'User home page', 'gn_text_field', 'reading', 'gn_front_page_section', array("gnlms_front_page_user"));
	
	register_setting('reading','gnlms_front_page_admin');
	register_setting('reading','gnlms_front_page_user');
}

add_action('admin_init', 'gn_settings_init');


// *************************************************************


function my_function_admin_bar(){ return false; }
add_filter( 'show_admin_bar' , 'my_function_admin_bar');

// *************************************************************

function gn_completed_courses() {
	return(gnInclude("includes/completed_courses.php"));
}

function gn_current_user_completed_courses () {
	global $wpdb;
	global $gnlms;
	
	$uid = get_current_user_id();
	// $sql = "select * from gnlms_user_course_registration ucr left join gnlms_course c on ucr.course_id = c.id where user_id=$uid and course_status='Completed' order by course_completion_date desc";
	
	$sql = "select * from #user_course_registration# ucr left join #course# c on ucr.course_id = c.id where user_id=$uid and course_status='Completed' order by course_completion_date desc";
	$sql = $gnlms->data->replaceTableRefs($sql);
	
	error_log("User completed courses sql: $sql");
	
	$records = $wpdb->get_results($sql);

	return ($records);


}

add_shortcode("gn_completed_courses", 'gn_completed_courses');


function  gn_portal_register_scripts () {
	wp_enqueue_style( "gnportal-css", plugins_url('gnaritas-portaluser/css/portal.css', dirname(__FILE__)));

	 wp_enqueue_script( "jquery-treeview", plugins_url('gnaritas-portaluser/scripts/jquery.treeview/jquery.treeview.js', dirname(__FILE__)));
	 wp_enqueue_style( "jquery-treeview", plugins_url('gnaritas-portaluser/scripts/jquery.treeview/jquery.treeview.css', dirname(__FILE__)));

	 wp_enqueue_script( "gnportal-js", plugins_url('gnaritas-portaluser/scripts/gnaritas-portal-user.js', dirname(__FILE__)));


	// wp_enqueue_style( "jquery-treeview-screen", plugins_url('gnaritas-portaluser/scripts/jquery.treeview/screen.css', dirname(__FILE__)));
	// wp_enqueue_style( "jquery-treeview-red", plugins_url('gnaritas-portaluser/scripts/jquery.treeview/red-treeview.css', dirname(__FILE__)));
}
add_action('wp_enqueue_scripts', 'gn_portal_register_scripts');




?>