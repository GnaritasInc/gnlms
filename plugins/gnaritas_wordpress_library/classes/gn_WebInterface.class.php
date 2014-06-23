<?php

if(!class_exists('gn_WebInterface')):

class gn_WebInterface {
	   protected $filename = __FILE__;

		var $jsURL;
		//var $cssURL;
		//var $jsVars;

	var $fileTypes = array();

 function gn_WebInterface () {
	$this->init();

 }

 function init() {

 			$baseDir = trailingslashit(dirname(dirname(plugin_basename(__FILE__))));
			$this->jsURL = WP_PLUGIN_URL."/$baseDir"."js/";

			$this->GlobalMSG="";


 	$optionskey="gnWebOptions";
 			/* List of File Types */
			    $this->fileTypes['swf'] = 'application/x-shockwave-flash';
			    $this->fileTypes['pdf'] = 'application/pdf';
			    $this->fileTypes['exe'] = 'application/octet-stream';
			    $this->fileTypes['zip'] = 'application/zip';
			    $this->fileTypes['doc'] = 'application/msword';
			    $this->fileTypes['xls'] = 'application/vnd.ms-excel';
			    $this->fileTypes['ppt'] = 'application/vnd.ms-powerpoint';
			    $this->fileTypes['gif'] = 'image/gif';
			    $this->fileTypes['png'] = 'image/png';
			    $this->fileTypes['jpeg'] = 'image/jpg';
			    $this->fileTypes['jpg'] = 'image/jpg';
			    $this->fileTypes['rar'] = 'application/rar';

			    $this->fileTypes['ra'] = 'audio/x-pn-realaudio';
			    $this->fileTypes['ram'] = 'audio/x-pn-realaudio';
			    $this->fileTypes['ogg'] = 'audio/x-pn-realaudio';

			    $this->fileTypes['wav'] = 'video/x-msvideo';
			    $this->fileTypes['wmv'] = 'video/x-msvideo';
			    $this->fileTypes['avi'] = 'video/x-msvideo';
			    $this->fileTypes['asf'] = 'video/x-msvideo';
			    $this->fileTypes['divx'] = 'video/x-msvideo';

			    $this->fileTypes['mp3'] = 'audio/mpeg3';
			    $this->fileTypes['mp4'] = 'video/mp4';
			    $this->fileTypes['mpeg'] = 'video/mpeg';
			    $this->fileTypes['mpg'] = 'video/mpeg';
			    $this->fileTypes['mpe'] = 'video/mpeg';
			    $this->fileTypes['mov'] = 'video/quicktime';
			    $this->fileTypes['swf'] = 'video/quicktime';
			    $this->fileTypes['3gp'] = 'video/quicktime';
			    $this->fileTypes['m4a'] = 'video/quicktime';
			    $this->fileTypes['aac'] = 'video/quicktime';
			    $this->fileTypes['m3u'] = 'video/quicktime';

			    $this->streamableExtensions =array('mp3','m3u','m4a','mid','ogg','ra','ram','wm', 'wav','wma','aac','3gp','avi','mov','mp4','mpeg','mpg','swf','wmv','divx','asf');
			    //$this->streamableExtensions =array('m3u','m4a','mid','ogg','ra','ram','wm', 'wav','wma','aac','3gp','avi','mov','mp4','mpeg','mpg','swf','wmv','divx','asf');


	// DS: Doing this in ccnx-data plugin instead.
	// wp_enqueue_script('gn_forms', $this->jsURL."gn_forms.js");
 }

 function baseDir () {
 	return (trailingslashit(dirname(dirname(plugin_basename($this->filename)))));
 }




 function getHost() {

	 return ($_SERVER['HTTP_HOST']);


 }

 function wpManagementInit() {
  	$optionskey="gnWebOptions";


 }


function displayMessages() {
	if ($this->GlobalMSG!="") {
		print ($this->GlobalMSG);
	}

}

/******** DS: original implementaion re-factored into setOptionValue

function updateOption($key) {
	$options = $newoptions = get_option($this->optionsKey);

		if ( $_POST[$key] ) {
			$newoptions[$key] = strip_tags(stripslashes($_POST[$key]));

			if ( $options != $newoptions ) {
				$options = $newoptions;
				update_option($this->optionsKey, $options);
				$this->optionValues =$options;
			}
		}
}

********/


function is_assoc($array) {
    return (is_array($array) && 0 !== count(array_diff_key($array, array_keys(array_keys($array)))));
}

function gnStringRepresentation($object) {
	$str="";
	$started =false;

		if (is_string($object)) {


			$str='"' .preg_replace('/"/i','\"',$object) .'"';
			$str= str_replace("\r\n", '', $str);

			// Fix the overload
			$str=str_replace('\\\\\\\"','\"',$str) ;


			}

		else if (is_int($object)) {


					$str=$object."" ;


			}
		else if (is_array($object) & !($this->is_assoc($object))) {

				$array = $object;
					$started = false;
					$str.="[";
					foreach ($array as $key) {

						$str.=(($started)? "," :"");

						$str.=$this->gnStringRepresentation($key);
						$started = true;
					}
					$str.="]";

		}
		else if (is_array($object) && $this->is_assoc($object)) {
				if (!object) {
					$str.="null";
				}
				else {
				// Do object

				$array = $object;
					$started = false;
					$str.="{";
					foreach ($array as $key =>$value) {

						$str.=(($started)? "," :"");

						if (is_string($key)) {
							$str.= '"' . $key .'" : ';
							$str.=$this->gnStringRepresentation($value);
						}
						else {
							$str.= '"' . "unknown-array-key" .'" : ';
							$str.=$this->gnStringRepresentation($value);
						}
						$started = true;
					}
					$str.="}";
				}
		}
		else if (is_object($object)) {
				if (!object) {
					$str.="null";
				}
				else {
				// Do object

				$array = get_object_vars($object);
					$started = false;
					$str.="{";
					foreach ($array as $key =>$value) {

						$str.=(($started)? "," :"");

						if (is_string($key)) {
							$str.= '"' . $key .'" : ';
							$str.=$this->gnStringRepresentation($value);
						}
						else {
							$str.= '"' . "unknown-key" .'" : ';
							$str.=$this->gnStringRepresentation($value);
						}
						$started = true;
					}
					$str.="}";
				}
		}

		else {
			try {
				//$str.="0";
				$str.=($object."");
				//				$str.=('{"unknown":"'.$object.'"}');

				}
				catch(Exception $e) {
					echo "error";
					print_r ($object);
				}

	}
	return($str);
}



   function insertJScriptObject($object) {
	 //$str= $this->gnStringRepresentation($object);

	$str = $object ? json_encode($object) : "{};";

	// Needs escape at BU ?
	$str= str_replace('\\\\"', '\\"',$str);
		echo($str);
	}


function addFormLoader($activeRecord,$formname, $buffer=false) {

	if ($buffer) {
		ob_start();
	}

	if ($activeRecord["id"]) {
		$activeRecord["object_id"] = $activeRecord["id"];

	}

	if ($activeRecord["name"]) {
		$activeRecord["object_name"] = $activeRecord["name"];
	}

	// DS modifications

	echo('<script type="text/javascript">');
	// echo('var formData =');

	echo("if(!window.gn_FormData) var gn_FormData = {};\n");

	echo("gn_FormData['$formname']=");
	echo($this->insertJScriptObject($activeRecord).";");


	/*
	echo('function gn_form_init() {
		"populateForm(document.forms[\''.$formname.'\'],formData);")
	}
	*/
	//echo("\njQuery(document).ready(function() {populateForm(document.forms['$formname'],formData);});

	echo("</script>");

	if ($buffer) {
		return ( ob_get_clean());
	}

}

function displayOptions ($results, $current=false, $prop = "id") {
	foreach ($results as $row) {
		$disabled="";

		if ($row->id==$current) {
			$selected="selected='selected'";
		}
		else {
			$selected="";
		}
		$values = get_object_vars($row);


		if (array_key_exists("record_status", $values)) {
			$disabled=$values["record_status"]?"":" disabled='disabled' ";
		}


		$value=$values[$prop];
		echo("<option value='". $value."' $disabled $selected>".htmlspecialchars($row->name)."</option>");
	}

}

function displayChecks ($results, $inputName, $current=false, $prop = "id", $nameprop="name") {
	foreach ($results as $row) {
		if ($row->id==$current) {
			$selected="selected='selected'";
		}
		else {
			$selected="";
		}
		$values = get_object_vars($row);
		$value=$values[$prop];
		$name = $values[$nameprop];
		echo("<li class='gfield'><label for  value='$inputName-$value' class='xgfield_label'><input id='$inputName-$value' type='checkbox' name='$inputName"."[]"."' value='$value'/>&#160;".htmlspecialchars($name)."</label></li>");
	}

}

function displayChecksRaw ($results, $inputName, $current=false, $prop = "id", $nameprop="name") {
	foreach ($results as $row) {
		if ($row->id==$current) {
			$selected="selected='selected'";
		}
		else {
			$selected="";
		}
		$values = get_object_vars($row);
		$value=$values[$prop];
		$name = $values[$nameprop];
		echo("<label for  value='$inputName-$value' class='xgfield_label'><input id='$inputName-$value' type='checkbox' name='$inputName"."[]"."' value='$value'/>&#160;".htmlspecialchars($name)."</label><br/>");
	}

}


function updateOption($key) {

		if ( $_POST[$key] ) {

			$newvalue = strip_tags(stripslashes($_POST[$key]));
			$this->setOptionValue($key, $newvalue);

		}
}

function setOptionValue ($key, $value) {

	$options = $newoptions = get_option($this->optionsKey);

	$newoptions[$key] = $value;

	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_option($this->optionsKey, $options);
		$this->optionValues =$options;
	}

}

function contentType($fileName) {
    $extension = strtolower(end(explode('.',$fileName)));

    $contentType = $this->fileTypes[$extension];

	return($contentType);
}

function getOptionValue($key) {

	if (!$this->optionValues) {
		$this->optionValues = get_option($this->optionsKey);
	}
	return($this->optionValues[$key]);
}




function get_working_directory() {
	$options = get_option($this->optionskey);

	$file_location =$options["file_location"];
	return ($file_location);
}


function displayAdminPage ($page) {
	include("html/$page");
}



function htmlEncode($str) {

	// does &amp;, &lt;, &gt; and &quot; only

	return htmlspecialchars($str);
}

function friendlyTime ($timeStr) {
 	$time = new gn_Date($timeStr);
  	$formattedTime = $time->format("g:iA");

  	return($formattedTime);
 }

 function friendlyDate($dateStr) {
  	$date = new gn_Date($dateStr);
    $formattedDate = $date->format("M d, Y");

   	return($formattedDate);
 }


function encrypt ($pwd, $data, $ispwdHex = 0) {
	if ($ispwdHex)
		$pwd = @pack('H*', $pwd); // valid input, please!

	$key[] = '';
	$box[] = '';
	$cipher = '';

	$pwd_length = strlen($pwd);
	$data_length = strlen($data);

	for ($i = 0; $i < 256; $i++)
	{
		$key[$i] = ord($pwd[$i % $pwd_length]);
		$box[$i] = $i;
	}
	for ($j = $i = 0; $i < 256; $i++)
	{
		$j = ($j + $box[$i] + $key[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}
	for ($a = $j = $i = 0; $i < $data_length; $i++)
	{
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$k = $box[(($box[$a] + $box[$j]) % 256)];
		$cipher .= chr(ord($data[$i]) ^ $k);
	}
	return $cipher;
}


function rc4Encrypt($key, $pt) {
	return bin2hex($this->encrypt($key, $pt));
}

function getVerificationKey ($data, $keystring) {
	return $this->rc4Encrypt ($keystring, $data);
}


function appendParam($url, $paramName, $paramValue){
	//echo ("AppendParam start".$url."<br>");

	//$url =  $_SERVER["REQUEST_URI"]; //	    return(preg_replace($pattern,"",$_SERVER["REQUEST_URI"]));

	$url = preg_replace("/&amp;/", "&", $url);

	if($this->paramExists($url, $paramName)){
		$url = $this->setParam($url, $paramName, $paramValue);
	}
	else {
		if(strpos($url,"?")===false){
				$url.="?";
			}
			else {
				$url.="&";
			}

		$url.=urlencode($paramName)."=".urlencode($paramValue);
	}

	//$url = preg_replace("/&/", "&amp;", $url);

	//echo ("AppendParam end".$url."<br>");

	$url = str_replace("&amp;", "&", $url);

	return $url;
}

function paramExists($url, $name){
	return preg_match("/[?&]$name/", $url);
}

function setParam($url, $name, $value) {
	return preg_replace("/(?<=[?&])$name=[^&]*(?=&?)/", "$name=$value", $url);
}

function roundUp ($float) {
	return ceil($float);
}

function roundDown($float){
	return floor($float);
}

function getCurrentDir () {
	$path = $_SERVER['PATH_TRANSLATED'];
	return substr($path, 0, strrpos($path, "\\"));
}

function getQuerystringValue ($key) {
	$val =  $this->getArrayValue($key, $_GET);
	return $val ? $val : "";
}


function getArrayValue ($key, $arr) {
	return (array_key_exists ($key, $arr) ? $arr[$key] : null);
}

function getPostValue ($key, $default="") {
	$val = $this->getArrayValue($key, $_POST);
	if (is_array($val) && count($val)==1) {
		$val=$val[0];
	}
	return ($val!=null) ? $val : $default;
}

function getCookieValue ($key) {
	$val =  $this->getArrayValue($key, $_COOKIE);
	return $val ? $val : "";
}

function getSessionValue ($key) {
	startSession();
	$val =  $this->getArrayValue($key, $_SESSION);
	return $val;
}

function setSessionValue ($key, $value){
	startSession();
	$_SESSION[$key] = $value;
}

function getServerVariable($key){
	$val = $this->getArrayValue($key, $_SERVER);
	return $val;
}


function getPostValues($fields) {
	$postValues=array();

	foreach($fields as $fieldName){
		if(($value = $this->getPostValue($fieldName)) !==NULL)
			$postValues[$fieldName] = $value;
	}

	return $postValues;

}

/**
 *
 * @param
 * @return
 */

function addAttribute($url, $key, $value) {
	if (strpos($url,"?")) {
		return($url."&$key=$value");
	}
	else {
		return($url."?$key=$value");

	}
}

/**
 *
 * @param
 * @return
 */

function getDataParam($name) {

		return mysql_real_escape_string($_POST[$name]);

	}


function writeOutput ($str)  {
	echo ($str);
}

// AJAX

function doAjaxResponse ($data) {
		header( "Content-Type: application/json" );
		echo(json_encode($data));
		exit();
	}

/**
 *
 * @param
 * @return
 */

function currentPageURL() {
		 $pageURL = 'http';
			 if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
			 $pageURL .= "://";
			 if ($_SERVER["SERVER_PORT"] != "80") {
			  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
			 } else {
			  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
			 }
		 return $pageURL;
		}



/**
 *
 * @param
 * @return
 */

function gn_user_role() {
			global $current_user, $wp_roles;
			if( $current_user->id )  {
				foreach($wp_roles->role_names as $role => $Role) {
					if (array_key_exists($role, $current_user->caps)) {
						//print_r($current_user->caps);
						return ($role);
						break;

					}
				}
			}
		}

/**
 *
 * @param
 * @return
 */

function get_user_role($user_id) {
			global  $wp_roles;
			$userInfo = get_userdata($user_id);


			if( $user_id )  {
				foreach($wp_roles->role_names as $role => $Role) {

					if (array_key_exists($role, $userInfo->wp_capabilities)) {
						return ($role);
						break;
					}
				}
			}
		}


}
endif;
?>
