<?php
/*
Plugin Name: Gnaritas Task Manager
Plugin URI: http://nowhere.com/
Description: TBD
Version: 1.0
Author: Gnaritas, Inc.
Author URI: http://www.gnaritas.com/


Note: This requires the "Gnaritas Library Functions" and "Gnaritas Calendar" plugins to be activated.

*/

require_once(WP_PLUGIN_DIR.'/gnaritas_wordpress_library/gnaritas_library.php');
require_once(WP_PLUGIN_DIR.'/gn-calendar/_classloader.php');



require_once("classes/gn_TaskManager.class.php");
require_once("classes/gn_TaskManagerDB.class.php");

require_once("classes/gn_Task.class.php");

require_once("classes/gn_Activity.class.php");




if(class_exists('gn_TaskManager')) {

	$gnTaskManager = new gn_TaskManager();
	register_activation_hook(__FILE__, array(&$gnTaskManager, 'install'));

}




?>