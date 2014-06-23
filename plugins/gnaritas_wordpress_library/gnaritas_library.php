<?php
/*
Plugin Name: Gnaritas Library Functions
Plugin URI: http://nowhere.com/
Description: Library code to support Gnaritas plugins.
Version: 1.0
Author: Gnaritas, Inc.
Author URI: http://www.gnaritas.com/

---------------------------------------------------------------------
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You can see a copy of GPL at <http://www.gnu.org/licenses/>
---------------------------------------------------------------------
*/

// URL for shared client-side code
define('GN_SCRIPT_URL', WP_PLUGIN_URL.'/'.trailingslashit(dirname(plugin_basename(__FILE__))).'js/');

/*
require_once('classes/gn_XMLUtils.class.php');
require_once('classes/gn_DataDisplay.class.php');
require_once('classes/gn_Date.class.php');
require_once('classes/gn_DateDisplay.class.php');
*/


require_once('classes/gn_CSVWriter.class.php');
require_once('classes/gn_WebInterface.class.php');
require_once('classes/gn_ListWidget.class.php');
require_once('classes/gn_PluginDB.class.php');




?>