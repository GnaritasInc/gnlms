<?php


class gnlms_LMS extends gn_WebInterface {


	function __construct () {
		include("templates/_states.php");

		$this->data = true;
		$this->data = new gnlms_Data();

		$this->listWidget = new gnlms_ListWidget();
		$this->listWidget->data = $this->data;

		$this->reportInterface = new gnlms_Report();

		$this->ajaxMethods = array (
			"gnlms-user-course-assignment"=>"doUserCourseAssignment",
			"gnlms-course-user-assignment"=>"doCourseUserAssignment",
			"gnlms-add-edit-announcement"=>"addEditAnnouncement",
			"gnlms-delete-announcement"=>"deleteAnnouncement",
			"gnlms-add-edit-subscription-code"=>"addEditSubscriptionCode",
			"gnlms-add-edit-subscription-code-course"=>"addEditSubscriptionCodeCourse",
			"gnlms-edit-user-course-registration"=>"editUserCourseRegistration"
		);

		$this->emailHeaders = array(
			"MIME-Version: 1.0",
			"Content-Type: text/html; charset=utf-8"
		);
		
		$this->enableSessions();

		add_shortcode("gnlms_data_form", array(&$this, "gnlms_data_form"));
		add_shortcode("gnlms_data_support_form", array(&$this, "gnlms_data_support_form"));
		add_shortcode("gnlms_launch_course", array(&$this, "gnlms_launch_course"));
		add_shortcode("gnlms_course_detail", array(&$this, "gnlms_course_detail"));

		
		
		
		add_action("init", array(&$this, "controller"));
		add_action("init", array(&$this, "createNonce"));

		add_action('wp_enqueue_scripts', array(&$this, 'register_scripts'));

		// add_action('user_register', array(&$this, 'addLMSUser'));

		add_action('profile_update', array(&$this, 'updateLMSUser'));

		add_action('gnlms_data_update', array(&$this, 'dataUpdateHandler'));


		add_action('admin_menu', array(&$this, 'adminInit'));

		add_action('wp_ajax_gnlms-user-course-assignment', array(&$this, "doAjaxPost"));
		add_action('wp_ajax_gnlms-course-user-assignment', array(&$this, "doAjaxPost"));
		add_action('wp_ajax_gnlms-add-edit-announcement', array(&$this, "doAjaxPost"));
		add_action('wp_ajax_gnlms-delete-announcement', array(&$this, 'doAjaxPost'));

		add_action('wp_ajax_gnlms-add-edit-subscription-code', array(&$this, 'doAjaxPost'));
		add_action('wp_ajax_gnlms-add-edit-subscription-code-course', array(&$this, 'doAjaxPost'));
		add_action('wp_ajax_gnlms-edit-user-course-registration', array(&$this, 'doAjaxPost'));

		add_action('wp_ajax_gnlms-course-user-selection', array(&$this, "ajaxFetchContent"));
		add_action('wp_ajax_gnlms-fetch-announcement-form', array(&$this, "ajaxFetchContent"));
		add_action('wp_ajax_gnlms-fetch-form', array(&$this, "ajaxFetchContent"));


		add_filter('gn_db_update_edit_value_defaults_filter', array(&$this, 'filterFormData'));



		// Registration

		add_action('register_form',array(&$this, 'registrationAddFields'));
		add_action('register_post',array(&$this, 'registrationCheckFields'),10,3);
		add_action('user_register', array(&$this, 'registrationDbInsertFields'));



	}
	
	function enableSessions () {
		add_action('init', array(&$this, "initSession"), 1);
		add_action('wp_logout', array(&$this, "destroySession"));
		add_action('wp_login', array(&$this, "destroySession"));
		add_filter('wp_redirect', array(&$this, "beforeRedirect"), 20, 2);
		
	}
	
	function beforeRedirect ($location, $status) {
		session_write_close();
		return $location;
	}
	
	function initSession () {		
		error_log("Starting PHP session");
		session_start(); 		
  		$this->setSessionValue("_gnlms_session_started", true);
	}
	
	function destroySession () {
		if (session_id()) {
			error_log("Destroying PHP session");
			session_destroy();
		}
	}

	function createNonce () {
		$this->nonce = wp_create_nonce("gnlms");
	}

	function verifyNonce ($nonce) {
		return wp_verify_nonce($nonce, "gnlms");
	}


	function adminInit () {
		// DS: We might want to define a custom capability for this, if possible
		$capability = "publish_posts";

		add_menu_page("Courses", "Courses", $capability, "gnlms-courses", array(&$this, 'adminController'));
		$coursePage = add_submenu_page("gnlms-courses", "Add New", "Add New", $capability, "gnlms-course", array(&$this, 'adminController'));

		add_action("admin_print_styles-$coursePage", array(&$this, 'register_form_styles'));
	}

	function adminController () {
		if($action = $_POST['gnlms_admin_action']) {
			$this->handleAdminPost($action);
			return;
		}


		$slug = preg_replace('/^gnlms-/', "admin-", $_GET['page']);
		$templateFile = "templates/$slug.php";
		$context = array();

		if($slug=="admin-course" && $_GET["id"] && $course = $this->data->fetchObject("course", $_GET["id"])) {
			$context = $course;
		}

		$this->displayTemplate($templateFile, $context);

	}

	function handleAdminPost ($action) {
		if($action == "add_edit_course") {
			$this->defaultUpdateEdit("course", false);
			$_POST["_msg"] = "Course ".($_POST["id"] ? "updated." : "added.");
			$this->displayTemplate("templates/admin-course.php", $_POST);
		}
		else {
			echo("Unknown action: $action");
		}
	}

	function addEditLMSUser () {
		if($_POST['id']) {
			if(strlen(trim($_POST['password']))) {
				wp_update_user(array("ID"=>$_POST['id'], "user_pass"=>$_POST['password']));
			}
			$this->defaultUpdateEdit("user");
		}
		else {
			$userData = array(
				"user_pass"=>$_POST['password'],
				"user_login"=>$_POST['email'],
				"user_email"=>$_POST['email'],
				"role"=>"lms_user"
			);
			$result = wp_insert_user($userData);

			if(is_wp_error( $result )) {
				$_POST['_errors'] = array($result->get_error_message());
				return;
			}
		}
	}

	function addLMSUser ($user_id) {
		error_log("addLMSUser($user_id)");
		$user = get_userdata($user_id);
		if($this->user_in_role("lms_user", $user)) {
			error_log("User in lms_user role. Roles: ".implode(", ", $user->roles));
			$this->data->addLMSUser($user);
		}


	}

	function updateLMSUser ($user_id, $old_data) {
		$user = get_userdata($user_id);
		if($this->user_in_role("lms_user", $user)) {
			$this->data->updateLMSUser($user);
		}
	}

	function user_in_role ($role, $user=null) {
		if(!$user) {

			$user = wp_get_current_user();
		}

		return ($user && in_array($role, $user->roles));
	}

	function register_scripts () {

		wp_enqueue_script('gnlms', plugins_url('scripts/gnlms.js', dirname(__FILE__)), array('jquery-ui-dialog'));

		wp_enqueue_script('jquery.stringify', plugins_url('scripts/jquery.stringify.js', dirname(__FILE__)), array('jquery'));
		wp_enqueue_script('gnSCORM', plugins_url('scripts/gnSCORM.js', dirname(__FILE__)), array('jquery.stringify'), "20130318");

		wp_enqueue_script ('my_tiny_mce', plugins_url('scripts/tiny_mce/tiny_mce.js', dirname(__FILE__)));

		wp_localize_script('gnlms', 'gnlms', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), "nonce"=>$this->nonce));

		$this->register_styles();
	}

	function register_styles () {
		$this->register_form_styles();
		// wp_enqueue_style ('wp-jquery-ui-dialog');

		// wp_enqueue_style('jquery-ui-custom', plugins_url("css/jquery-ui-1.8.23.custom.css", dirname(__FILE__)));

		wp_enqueue_style('jquery-ui-custom', plugins_url("css/jquery-ui-1.9.2.custom.css", dirname(__FILE__)));
	}

	function register_form_styles () {
		wp_enqueue_style( "gnlms-forms", plugins_url('css/gnlms-forms.css', dirname(__FILE__)));
	}

	function controller () {
		$form = trim($_POST["gnlms_data_form"]);
		if($form) {
			$this->handleFormSubmission($form);
		}
	}


	function handleFormSubmission ($name) {
		if(!$this->verifyUser()) {
			die("Unauthorized");
			return;
		}

		switch ($name) {
			case "user":
				$this->addEditLMSUser();
				break;
			case "subscription_code":
				$this->addEditSubscriptionCode();
				break;
			case "alert_preferences":
				$this->updateAlertPreferences();
				break;

			default:
				$this->defaultUpdateEdit($name);
		}


	}

	function filterFormData ($data) {
		$form = $data["gnlms_data_form"];
		if($form=="announcement") {
			unset($data["create_date"]);
			if($data["id"]) {

				unset($data["created_by"]);
			}
			else {
				$user = wp_get_current_user();
				$data["created_by"]=$user->user_login;
			}
		}

		return $data;
	}

	function verifyUser () {
		return ($this->user_in_role("administrator") || $this->user_in_role("lms_admin"));
	}

	function ajaxSuccess () {
		echo(json_encode(array("status"=>"OK")));
		exit();
	}

	function ajaxError ($msg) {
		echo(json_encode(array("status"=>"error", "msg"=>$msg)));
		exit();
	}

	function ajaxVerify () {
		$nonce = $_REQUEST['gnlms_nonce'];
		return $this->verifyNonce($nonce) && $this->verifyUser();
	}

	function ajaxFetchContent () {
		if(!$this->ajaxVerify()) {
			echo("<p>Unauthorized</p>");
			exit();
		}

		$action = $_GET['action'];
		$records = array();

		$method = "";

		switch($action) {
			case "gnlms-course-user-selection":
				$method = "writeCourseUserSearchResults";
				break;
			case "gnlms-fetch-announcement-form":
				$method = "displayAnnouncementForm";
				break;
			case "gnlms-fetch-form":
				$method = "ajaxDisplayForm";
				break;

		}

		if(!$method) {
			echo("<p>Unknown action: $action</p>");
			exit();
		}

		$this->$method();

		exit();

	}

	function ajaxDisplayForm () {
		$atts["name"] = $_GET["type"];
		echo($this->gnlms_data_form($atts, null, null));
	}

	function displayAnnouncementForm () {
		$id = trim($_GET["id"]);
		$context = array();
		if(intval($id)) {
			$context = $this->data->fetchObject("announcement", $id);
			if(!$context) {
				echo("<p>Announcement not found.</p>");
				exit();
			}
		}

		$this->displayTemplate("forms/announcement.php", $context);
		exit();
	}

	function writeCourseUserSearchResults () {
		$course_id = $_GET["course_id"];
		$query = trim($_GET["q"]);
		$search_field = $_GET["search_field"];

		$records = $this->data->getAvailableUsersForCourse($course_id, $query, $search_field);

		if(!$records) {
			echo("<p>No users found.</p>");
			return;
		}

		$this->displayTemplate("templates/course-available-users.php", $records);
	}

	function doAjaxPost () {

		if(!$this->ajaxVerify()){
			$this->ajaxError("Unauthorized");
			return;
		}

		$method = $this->ajaxMethods[$_POST['action']];

		if(!$method) {
			$this->ajaxError("Unknown action: ".$_POST['action']);
			return;
		}

		try {
			$this->$method();
		}
		catch (Exception $e) {
			$this->ajaxError($e->getMessage());
			return;
		}


		$this->ajaxSuccess();
	}

	function addEditAnnouncement () {
		$title = trim($_POST["title"]);
		$text = trim($_POST["text"]);
		if(!$title || !$text) {
			$this->ajaxError("Please enter a title and text.");
			return;
		}
		// DS: Adding created_by for new announcements

		if(!intval($_POST["id"])) {

			$currentUser = wp_get_current_user();
			$_POST["created_by"] = $currentUser->user_login;
		}


		$sql = $this->data->getUpdateEditSQL("announcement", $_POST);
		$this->data->dbSafeExecute($sql);
	}

	function deleteAnnouncement () {
		$id = intval($_POST["id"]);
		if(!$id) {
			$this->ajaxError("Invalid ID.");
			return;
		}

		$this->data->deleteAnnouncement($id);
	}

	function addEditSubscriptionCode () {
		$code = trim($_POST['code']);
		$errors = array();
		if(!$code) {
			//$this->ajaxError("Please enter a code.");
			// return;

			$errors[] = "Please enter a code.";
		}

		if(!$_POST['id'] && $dbCode = $this->data->retrieveSubscriptionCode($code)) {
			// $this->ajaxError("Code $code is already in use. Please enter a different code.");
			// return;

			$errors[] = "Code $code is already in use. Please enter a different code.";
		}

		if($errors) {
			$_POST['_errors'] = $errors;
			error_log("addEditSubscriptionCode Errors: ". implode("|",$errors));
			return;
		}


		if($id = $_POST['id']) {
			$sql = $this->data->getUpdateEditSQL("subscription_code", $_POST);
			$this->data->dbSafeExecute($sql);
			$redirect = $_SERVER['REQUEST_URI'];
		}
		else {
			$id = $this->data->doInsert("subscription_code", $_POST);
			$redirect = $_SERVER['PATH_INFO']."?id=$id";
		}

		wp_safe_redirect($redirect);
		exit();

	}

	function addEditSubscriptionCodeCourse () {
		$codeID = trim($_POST['subscription_code_id']);

		if(!$codeID) {
			$this->ajaxError("No subscription code ID");
			return;
		}
		if(!$code = $this->data->fetchObject('subscription_code', $codeID)) {
			$this->ajaxError("Subscription code $codeID not found.");
			return;
		}

		$sql = $this->data->getUpdateEditSQL("subscription_code_course", $_POST);
		$this->data->dbSafeExecute($sql);


	}

	function updateAlertPreferences () {
		$result = $this->data->updateAdminAlertPreferences(get_current_user_id(), $_POST);
		if(!$result) {
			$_POST['_errors'] = "Error updating preferences.";
			return;
		}

		$redirect = $_SERVER['PATH_INFO']."?".http_build_query(array_merge(array("alert_preferences_update"=>1), $_GET));

		wp_safe_redirect($redirect);
		exit();
	}

	function editUserCourseRegistration () {
		$sql = $this->data->getUpdateEditSQL("user_course_registration", $_POST);
		$this->data->dbSafeExecute($sql);

	}

	function doCourseUserAssignment () {
		$this->data->assignCourseUsers($_POST["course_id"], $_POST["user_ids"]);
	}

	function doUserCourseAssignment () {
		$this->data->assignUserCourses($_POST["user_id"], $_POST["course_ids"]);
	}

	function defaultUpdateEdit ($name, $doRedirect=true) {
		if ($errors = $this->validateFormData($name)) {
			$_POST['_errors'] = $errors;
			// print_r($errors);
			error_log("defaultUpdateEdit Errors: ". implode("|",$errors));
			return;
		}

		$sql = $this->data->getUpdateEditSQL($name, $_POST);

		if(!$sql) {
			$_POST['_errors'] = array("Error: No data definition for $name");
			error_log("Error: No data definition for $name");
			return;
		}

		try {
			$this->data->dbSafeExecute($sql);


			do_action('gnlms_data_update', $name);

			if($doRedirect) {
				$redirect = trim($_POST["_redirect"]) ?  $_POST["_redirect"] : $_SERVER['REQUEST_URI'];
				wp_safe_redirect($redirect);
				exit();
			}
		}
		catch (Exception $e) {
			$_POST['_errors'] = array("Database error: ".$e->getMessage());
			error_log("defaultUpdateEdit: ". $e->getMessage());
		}
	}

	function dataUpdateHandler ($name) {
		if($name=="user" && is_numeric($_POST["id"])){
			$data = array(
				"id"=>$_POST["id"],
				"email"=>$_POST['email'],
				"first_name"=>$_POST['first_name'],
				"last_name"=>$_POST['last_name']
			);

			$this->data->updateWPUserData($data);
		}
	}

	function validateFormData ($name) {
		$errors = array();
		$tableDef = $this->data->tableDefinition[$name];
		if($tableDef && $tableDef["validationFunction"]){
			$callback = $tableDef["validationFunction"];
			$errors = $this->$callback();
		}

		return $errors;
	}

	function validateUser () {
		$errors = array();
		$email = $_POST["email"];
		$user_id = $_POST["id"] ? $_POST["id"] : 0;
		if(!$this->data->emailAvailable($email, $user_id)) {
			$errors[]= "Email address is already in use.";
		}

		return $errors;
	}

	function gnlms_data_form ($atts, $content="", $code="") {
		$name = $atts["name"];
		$formFile = "forms/".$name.".php";

		if($name == trim($_POST["gnlms_data_form"])) {
			$contextRecord = $_POST;
		}
		else if($name == "alert_preferences" && $this->user_in_role("lms_admin")) {
			$contextRecord = $this->data->getAdminAlertPreferences(get_current_user_id());
		}
		else {
			$contextParam = $atts["context"] ? $atts["context"] : "id";

			$contextID = trim($_GET[$contextParam]);

			$contextRecord = strlen($contextID) ? $this->data->fetchObject($name, $contextID) : array();

			if($contextRecord === null) {
				return "Error: Record $contextID doesn't exist";
			}
		}


		ob_start();
		$this->displayTemplate($formFile, $contextRecord, $atts);
		return ob_get_clean();

	}
	
	function gnlms_course_detail ($atts) {
		$atts["name"] = "course";
		$atts["code"] = "gnlms_course_detail";
		
		return $this->gnlms_data_form($atts);
	}

	function getClientScript($uid,$cid,$scormInterfaceURL, $courseURL) {
		$str="";

		//$str.="<script type='text/javascript' src='". plugins_url('scripts/jquery.js', dirname(__FILE__)) ."'> </script>\n";
		//$str.="<script type='text/javascript' src='". plugins_url('scripts/jquery.stringify.js', dirname(__FILE__)) ."'> </script>\n";
		//$str.="<script type='text/javascript' src='". plugins_url('scripts/gnSCORM.js', dirname(__FILE__)) ."'> </script>\n";
		$str.="<script type='text/javascript'>\n";
		$str.="_gnlms_COURSE_ID =$cid;\n";
		$str.="_gnlms_USER_ID=$uid;\n";
		$str.="_gnlms_URL='$scormInterfaceURL';\n";
		$str.="courseURL='$courseURL';\n";
		//$str.="launchCourse(courseURL);\n";
		$str.="</script>\n";

		$str.="<p class='gnScormSuccessfulLaunch'>Course access in progress.</p>";
		$str.="<p class='gnScormFailedLaunch'>The course window has not opened. Please disable pop-up blockers for this site. You may use the link below to launch the course.</p>";
		$str.="<a class='gnScormFailedLaunch' href='#' onclick='launchCourse(courseURL)'>Click to view course</a>";
		$str.="<p class='class='gnScormSuccessfulLaunch'>Do not close this window while the course is in progress.</p>";
		$str.="<p class='class='gnScormSuccessfulLaunch'>When you have finished your session with the course, this dialog may be closed.</p>";






		return ($str);
}

function getCourseURL ($cid) {
		// $sql ="select url from gnlms_course where id=$cid";

		$sql = $this->data->db->prepare("select url from ".$this->data->tableName('course')." where id=%d", $cid);
		return ($this->data->db->get_var($sql));
}
	function retrieveRegistration($uid, $cid) {
		// $sql ="select record_status from gnlms_user_course_registration where user_id=$uid and course_id=$cid";

		$sql ="select record_status, expiration_date from ".$this->data->tableName('user_course_registration')." where user_id=%d and course_id=%d";
		$sql = $this->data->db->prepare($sql, $uid, $cid);
		//return ($this->data->db->get_var($sql));

		return $this->data->db->get_row($sql);
	}

	function gnlms_launch_course ($atts) {
				$name = $atts["name"];
				$contextParam = $atts["context"] ? $atts["context"] : "id";
				$contextID = trim($_GET[$contextParam]);

				$context = array();

				$uid =get_current_user_id();
				$cid = $contextID;

				if (!$uid ||!$cid) {
					echo("Invalid Context");
					exit();
				}

				$scormListener = plugins_url('scormListener.php', dirname(__FILE__));
				$courseURL = $this->getCourseURL ($cid);

				session_start();
				$_SESSION['cid'] = $cid;

				/*
				if ($this->retrieveRegistration ($uid, $cid)) {
					$this->data->logCourseLaunch($uid, $cid);
					return ($this->getClientScript($uid,$cid, $scormListener, $courseURL));
				}
				else {
					return("<strong>No valid course registration.</strong>");
				}
				*/

				$registration = $this->retrieveRegistration ($uid, $cid);


				if(!$registration || !$registration->record_status) {
					return("<strong>No valid course registration.</strong>");
				}
				else if ($registration->expiration_date && strtotime($registration->expiration_date) <= strtotime(date("Y-m-d"))) {
					return "<strong>Registration expired.</strong>";
				}
				else {
					$this->data->logCourseLaunch($uid, $cid);
					return ($this->getClientScript($uid,$cid, $scormListener, $courseURL));

				}

	}

	function gnlms_data_support_form ($atts) {
		$name = $atts["name"];
		$formFile = "forms/".$name.".php";
		$contextParam = $atts["context"] ? $atts["context"] : "id";
		$contextID = trim($_GET[$contextParam]);

		$context = array();

		switch ($name) {
			case "course-user-assignment":
				$context["course_id"] = $contextID;
				$context["course"] = $this->data->fetchObject("course", $contextID);
				// $context["users"] = $this->data->getAvailableUsersForCourse($contextID);
				break;
			case "user-course-assignment":
				$context["user_id"] = $contextID;
				$context["user"] = $this->data->fetchObject("user", $contextID);
				$context["courses"] = $this->data->getAvailableCoursesForUser($contextID);
				break;
		}

		ob_start();
		$this->displayTemplate($formFile, $context);
		return ob_get_clean();

	}


	function displayTemplate ($templateFile, $context=array(), $atts=array()) {
		if(!file_exists(dirname(__FILE__)."/$templateFile")) {
			echo( "Error: $templateFile not found.");
		}
		include($templateFile);
	}

	function writeChecked ($condition) {
		 $this->writeConditionalAttribute($condition, "checked");
	}

	function writeConditionalAttribute ($condition, $attribute) {
		if($condition) {
			echo(" $attribute='$attribute'");
		}
	}
	
	function writeLogMessage ($msg, $logPrefix) {
		$now = time();
		$logfile = dirname(__FILE__)."/log/{$logPrefix}_".date('Ymd', $now);
		$timestamp = date('Y-m-d H:i:s T', $now);

		error_log("[$timestamp] $msg\n", 3, $logfile);
		
	}
	
	function scormLog ($msg) {
		$this->writeLogMessage($msg, "scorm");
	}

	function alertLog ($msg) {
		/*
		$now = time();
		$logfile = dirname(__FILE__)."/log/alerts_".date('Ymd', $now);
		$timestamp = date('Y-m-d H:i:s T', $now);

		error_log("[$timestamp] $msg\n", 3, $logfile);
		*/
		
		$this->writeLogMessage($msg, "alerts");
	}

	function doAdminAlerts () {

		$this->alertLog("Checking for admin alerts...");

		/* DS: Changing to unix timestamp
		$lastRun = get_option("gnlms_last_alert", date('Y-m-d H:i:s', strtotime("-10 days")));
		$now = date('Y-m-d H:i:s');
		*/

		$lastRun = get_option("gnlms_last_alert", strtotime("-10 days"));
		$now = time();


		foreach($this->data->adminAlerts as $key=>$alert) {
			$alertEvents = $this->data->getAlertEvents($alert['eventType'], $lastRun);
			$this->alertLog("Querying events for $key");
			if($alertEvents) {
				$this->alertLog("Events found. Sending alerts...");
				$this->sendAlerts($key, $alertEvents);
			}
			else {
				$this->alertLog("No events found.");
			}
		}

		update_option("gnlms_last_alert", $now);
	}

	function sendAlerts ($alert, $events) {
		$subscribers = $this->data->getAlertSubscribers($alert);
		$alertName = $this->data->adminAlerts[$alert]["name"];
		$subject = get_bloginfo('name')." Alert: $alertName";
		$message = $this->getAlertEmailMessage($alertName, $events);

		if($subscribers) {
			foreach($subscribers as $subsciber) {
				$to = $subsciber->user_email;
				$this->alertLog("Sending $alert alert to $to");
				$result = wp_mail($to, $subject, $message, $this->emailHeaders);
				$this->alertLog($result ? "Success":"Failure");
			}
		}
		else {
			$this->alertLog("No subscribers for event '$alert'.");
		}
	}

	function getAlertEmailMessage ($alertName, $events) {
		ob_start();
		include("templates/alert-email.php");
		return ob_get_clean();
	}



	// *********************************
	// Registration

	function findRegistrationCode($code) {
		return ($this->data->retrieveSubscriptionCode($code));

	}


	function validateRegistrationCode($codeData) {
		// "columns"=>array('id','code','organization_id','expiration_date','user_limit','record_status')


		$todays_date = date("Y-m-d");
		$today = strtotime($todays_date);

		$expiration_date = strtotime($codeData->expiration_date);


		if ($codeData->record_status!=1) {
			return (false);
		}
		else if ($expiration_date<$today) {
			return (false);
		}

		else if ($this->data->retrieveSubscriptionCount($codeData->id) >= $codeData->user_limit) {
			return (false);
		}
		else {
			return (true);

		}


	}


	function registrationAddFields () {
		$context = $_POST;
		$context["_is_registration"] = true;

		ob_start();
		$this->displayTemplate("forms/_user_fields.php", $context);
		echo( ob_get_clean());

	}

	function registrationCheckFields ($login, $email, $errors) {

		$regCode =$_POST['registration_code'];

		if (!$regCode) {
			$errors->add('empty_regCode', "<strong>ERROR</strong>: Please enter a registration code.");
		} else if (!$regCodeData = $this->findRegistrationCode($regCode)) {
			$errors->add('invalid_regCode', "<strong>ERROR</strong>: Registration code not found.");
		} else if (!$this->validateRegistrationCode ($regCodeData)) {
			$errors->add('overlimit_regCode', "<strong>ERROR</strong>: Registration code is expired or over the limit.");
		}
		else {

			$_POST["organization_id"] = $regCodeData->organization_id;
			$_POST["subscription_code_id"] = $regCodeData->id;
			$_POST["email"] = $_POST["user_email"];
		}




		if ($_POST['first_name'] == '') {
			$errors->add('empty_realname', "<strong>ERROR</strong>: Please enter a First Name");
		} else {
			$firstname = $_POST['first_name'];
		}
		if ($_POST['last_name'] == '') {
			$errors->add('empty_realname', "<strong>ERROR</strong>: Please enter a Last Name");
		} else {
			$lastname = $_POST['last_name'];
		}

	}

function registrationDbInsertFields($user_id) {
		error_log("Doing DB Update");

		$this->addLMSUser($user_id);

		// Auto-register for courses

		$this->assignUserCourses($user_id);

		$_POST["id"] = $user_id;
		$_POST["gnlms_data_form"] = "user";

		$doRedirect = trim($_POST['_redirect']) ? true : false;

		$this->defaultUpdateEdit ("user", $doRedirect);
		error_log("Finished DB Update");

	}


	function assignUserCourses ($user_id) {
		if($code = $_POST['registration_code']) {
			foreach($this->data->retrieveSubscriptionCodeCourses($code) as $course) {
				$number =  $course->subscription_period_number;
				$interval = $course->subscription_period_interval;
				$subscription_period = ($number && $interval) ?  "+$number $interval" : "";

				$expiration_date = $subscription_period ? date('Y-m-d', strtotime($subscription_period)) : null;

				$this->data->addUserCourseRegistration($user_id, $course->course_id, $expiration_date);
			}
		}
	}




	// End Registration
	// **********************************************************************


}




?>