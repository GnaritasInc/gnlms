<?php
/*
Plugin Name: Gnaritas Content Editor
Plugin URI: http://nowhere.com/
Description: Library code to support Gnaritas content editing.
Version: 1.0
Author: Gnaritas, Inc.
Author URI: http://www.gnaritas.com/


*/


function gnContentEditorInclude($file) {
	error_log("Checking". dirname(__FILE__). "/includes/". $file );

	if (file_exists (dirname(__FILE__). "/includes/". $file )) {
		ob_start();
		include (dirname(__FILE__). "/includes/". $file);
		return ob_get_clean();
	}
}


// *************************************************************

function gn_content_editor($atts) {
	$model ="form.php";

	if ($atts["model"]) {
		$model=$atts["model"].".php";
	}

	return(gnContentEditorInclude($model));
}

add_shortcode("gn-edit-content", 'gn_content_editor');


?>
