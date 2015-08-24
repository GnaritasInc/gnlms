<?php


class gnlms_LMS  {
	
	
	function __construct () {

		$this->data = true;
		$this->data = new gnlms_Data();

		$this->listWidget = new gnlms_ListWidget();
		$this->listWidget->data = $this->data;

		$this->reportInterface = new gnlms_Report();		

		$this->ajaxMethods = array (
			"gnlms-user-course-assignment"=>"doUserCourseAssignment",
			"gnlms-course-user-assignment"=>"doCourseUserAssignment",
			"gnlms-edit-user-course-registration"=>"editUserCourseRegistration",			
			"gnlms_single_course_registration"=>"ajaxCourseRegistration"
		);

		$this->emailHeaders = array(
			"MIME-Version: 1.0",
			"Content-Type: text/html; charset=utf-8"
		);

		add_shortcode("gnlms_data_form", array(&$this, "gnlms_data_form"));
		add_shortcode("gnlms_data_support_form", array(&$this, "gnlms_data_support_form"));
		add_shortcode("gnlms_launch_course", array(&$this, "gnlms_launch_course"));
		add_shortcode("gnlms_course_detail", array(&$this, "gnlms_course_detail"));
		

		add_action("init", array(&$this, "controller"), 2);
		add_action("init", array(&$this, "createNonce"));
		add_action('wp_enqueue_scripts', array(&$this, 'register_scripts'));		
		add_action('admin_enqueue_scripts', array(&$this, 'register_scripts'));
		add_action("admin_init", array(&$this, 'initSettings'));



		$this->adminCapability = "manage_options";
		add_action('admin_menu', array(&$this, 'adminInit'));

		add_action('wp_ajax_gnlms-user-course-assignment', array(&$this, "doAjaxPost"));
		add_action('wp_ajax_gnlms-course-user-assignment', array(&$this, "doAjaxPost"));

		add_action('wp_ajax_gnlms-edit-user-course-registration', array(&$this, 'doAjaxPost'));
		
		add_action('wp_ajax_gnlms_single_course_registration', array(&$this, 'doAjaxPost'));

		add_action('wp_ajax_gnlms-course-user-selection', array(&$this, "ajaxFetchContent"));
		add_action('wp_ajax_gnlms-fetch-form', array(&$this, "ajaxFetchContent"));
		
		add_action('wp_ajax_gnlms-launch-course', array(&$this, "ajaxLaunchCourse"));


		add_action("gnlms_data_form_post", array(&$this, "handleFormSubmission"));
		
		add_filter("user_row_actions", array(&$this, "userActionLinks"), 15, 2);
		
		add_action('edit_user_profile', array(&$this, 'writeCourseRegistrationLink'));
		add_action('show_user_profile', array(&$this, 'writeCourseRegistrationLink'));
		
		add_action('personal_options', array(&$this, 'writeAdminAlertOptions'));
		add_action('personal_options_update', array(&$this, 'saveAdminAlertOptions'));
		
		add_filter("admin_title", array(&$this, 'adminPageTitle'), 10,  2);
		
		add_action("gnlms_admin_action", array(&$this, "handleAdminPost"));
		
		add_action("gnlms_ajax_post", array(&$this, "gnlmsAjaxPost"));		
		
		// Page access
		add_action("template_redirect", array(&$this, "checkPageAccess"));

	}
	
	function initSettings () {
		$settingsSection = 'gnlms_settings';
		$settingsPage = 'general';
		
		$settings = array(
			array(
				"name"=>gnlms_course_path,
				"title"=>"Course Path",
				"description"=>"Physical path to the directory where course files will be uploaded (e.g. /var/www/html/courses, C:\\inetpub\\wwwroot\\courses).<br/>This directory should be writable."
			),
			array(
				"name"=>gnlms_course_url,
				"title"=>"Course URL",
				"description"=>"Request URL for the courses directory (e.g. /courses, http://example.com/courses)."
			),
			array(
				"name"=>gnlms_course_image_url,
				"title"=>"Course Image URL",
				"description"=>"Request URL for the course screenshot images directory (e.g. /courses/_images, http://example.com/courses/_images)."
			),
			array(
				"name"=>"gnlms_course_page",
				"title"=>"Course Detail Page",
				"description"=>"URL of a page with the [gnlms_course_detail] shortcode to display course information and registration links."
			)
		);
		
		add_settings_section(
			$settingsSection,
			'LMS Settings',
			array(&$this, "settingsSection"),
			$settingsPage
		);
		
		foreach($settings as $setting) {
			add_settings_field(
				$setting['name'],
				$setting['title'],
				array(&$this, "writeSettingsInput"),
				$settingsPage,
				$settingsSection,
				array_merge(array("label_for"=>$setting['name']), $setting)
			);
			
			register_setting($settingsPage, $setting['name']);
		}		

	}
	
	function settingsSection () {
		echo "<p>LMS Configuration options.</p>";
	}
	
	
	function writeSettingsInput ($args) {
		extract(array_merge(array("class"=>"large-text", "id"=>$args['name']), $args));
		
		echo "<input type='text' class='$class' name='$name' id='$id' value=\"".htmlspecialchars($this->getOption($name))."\" />";
		if ($description) echo "<br/><span class='description'>$description</span>";
	}
	
	
	function getOption ($name) {
		$defaults = array(
			"gnlms_course_path"=>trailingslashit(ABSPATH)."courses",
			"gnlms_course_url"=>home_url("courses", "relative"),
			"gnlms_course_image_url"=>home_url("courses/_images", "relative")
		);
		if (array_key_exists($name, $defaults)) {
			return get_option($name, $defaults[$name]);
		}
		else return get_option($name);
	}

	function createNonce () {
		$this->nonce = wp_create_nonce("gnlms");
	}

	function verifyNonce ($nonce) {
		return wp_verify_nonce($nonce, "gnlms");
	}


	function adminInit () {
		
		$capability = $this->adminCapability;
		
		$menuPage = "gnlms-courses";		
		$callback = array(&$this, 'adminController');

		add_menu_page("LMS Courses", " LMS Courses", $capability, $menuPage, $callback);
		$coursePage = add_submenu_page($menuPage, "Add/Edit Course", "Add New", $capability, "gnlms-course", $callback);
		
		add_submenu_page($menuPage, "Upload Course", "Upload", $capability, "gnlms-course-upload", $callback);

		add_submenu_page(null, "Edit Course Regisrations", "Edit Course Regisrations", $capability, "gnlms-user-course-registration", $callback);
		
		$this->doReportsMenu();
		
		do_action("gnlms_admin_menu", $menuPage, $callback);
		
	}

	function writeAdminAlertOptions ($user) {
		if($user->has_cap($this->adminCapability)) {
			include("templates/admin-alert-preferences.php");
		}
	}
	
	function saveAdminAlertOptions ($userID) {
		if(user_can($userID, $this->adminCapability)) {
			foreach($this->data->adminAlerts as $key=>$alert) {
				$metaKey = $this->data->replaceTableRefs("#alert_#$key");
				update_user_meta($userID, $metaKey, intval($_POST[$metaKey]));
			}
		}
	}
	 

	
	function doReportsMenu () {
		$capability = $this->adminCapability;
		$menuPage = "gnlms-reports";		
		$callback = array(&$this, 'adminReport');
		
		add_menu_page("LMS Reports", "LMS Reports", $capability, $menuPage, $callback);
		
		foreach($this->reportInterface->getReportList() as $key=>$title) {
			add_submenu_page($menuPage, "LMS Report: $title", $title, $capability, "gnlms-report-{$key}", $callback);
		}		
	}
	
	function adminReport () {
		$page = $_GET["page"];
		$match = array();
		if ($page=="gnlms-reports") {
			include("templates/admin-reports.php");
		}
		else if(preg_match('/^gnlms-report-([a-z0-9_-]+)$/', $page, $match)) {
			$report = $match[1];
			include("templates/admin-report.php");
		}
	}
	
	function adminPageTitle ($admin_title, $title) {
		$slug = $_GET['page'];
		if($slug == "gnlms-user-course-registration") {			
			$admin_title = "User Course Registration $admin_title";
		}
		
		return $admin_title;
	}
	
	function writeCourseRegistrationLink ($user) {
		echo "<p><a href='".admin_url("users.php?page=gnlms-user-course-registration&user_id=".$user->ID)."'>Manage Course Registrations</a></p>";
	}
	
	function userActionLinks ($actions, $user) {
		$actions["gnlms_course_registrations"] = '<a href="'.admin_url("users.php?page=gnlms-user-course-registration&user_id=".$user->ID).'">Courses</a>';
		return $actions;
	}

	function adminController () {
		if($action = $_POST['gnlms_admin_action']) {
			do_action("gnlms_admin_action", $action);
			return;
		}

		$page = $_GET['page'];
		$slug = preg_replace('/^gnlms-/', "admin-", $page);
		$templateFile = "templates/$slug.php";
		$context = array();

		if($page=="gnlms-course" && $_GET["id"] && $course = $this->data->fetchObject("course", $_GET["id"])) {
			$context = $course;
		}
		else if ($page=="gnlms-user-course-registration" && $_GET["user_id"]) {
			$context = get_userdata($_GET["user_id"]);
		}

		$this->displayTemplate(apply_filters("gnlms_admin_template", $templateFile, $page), apply_filters("gnlms_admin_data_context", $context, $page));

	}

	function handleAdminPost ($action) {
		if($action == "add_edit_course") {
			$this->defaultUpdateEdit("course", false);
			$_POST["_msg"] = "Course ".($_POST["id"] ? "updated." : "added.");
			$this->displayTemplate("templates/admin-course.php", $_POST);
		}
		else if ($action == "course_upload") {
			try {
				$this->doCourseUpload();
			}
			catch (Exception $e) {
				$_POST['_error'] = $e->getMessage();
			}
			if (!$_POST['_error']) {
				$_POST["_msg"] = "Course uploaded and extracted to ".trailingslashit($this->getOption("gnlms_course_path")) . trim($_POST["course_dir"]);
			}
			$this->displayTemplate("templates/admin-course-upload.php", $_POST);
		}
	}
	
	function doCourseUpload () {
		if(!$this->verifyNonce($_POST['gnlms_nonce'])) {
			wp_die("Unauthorized");
		}
		$uploadOptions = array(
			"test_form"=>false,
			"mimes"=>array('zip' => 'application/zip'),
			"ext"=>array('zip'),
			"type"=>true
		);
		
		$file = wp_handle_upload($_FILES['gnlms_course_file'], $uploadOptions);
		if ($file['error']) {
			throw new Exception($file['error']);
			return;
		}
		
		$zipFile = $file['file'];
		$courseDir = trim($_POST["course_dir"]);
		
		if (!strlen($courseDir)) {
			throw new Exception("Please specify a course directory.");
			return;
		}		
		
		if (intval($_POST['use_manifest'])) {
			$courseData = $this->getCourseDataFromManifest($zipFile);			
			$courseData['url'] = trailingslashit($this->getOption("gnlms_course_url")). trailingslashit($courseDir) . $courseData['url'];
			$_POST = array_merge($_POST, $courseData);
		}
		
		if (!strlen($_POST['title']) || !strlen($_POST['url'])) {
			throw new Exception("Please enter both a course title and url.");
			return;
		}
		
		$this->extractCourseFiles($zipFile, $courseDir);
		$this->defaultUpdateEdit("course", false);		
		
	}
	
	function extractCourseFiles ($zipFile, $courseDir) {
		WP_Filesystem();
		$extracted = unzip_file($zipFile, trailingslashit($this->getOption("gnlms_course_path")).$courseDir);
		
		if (is_wp_error($extracted)) {
			throw new Exception($extracted->get_error_message());
		}		
	}
	
	function getCourseDataFromManifest ($filePath) {
		$courseData = array();
		$manifestPath = "imsmanifest.xml";
		
		$zip = new ZipArchive();
		$openResult = $zip->open($filePath);
		
		if ($openResult !== true) {
			throw new Exception("Error reading ZIP archive: ".$this->getZIPError($openResult));
			return;
		}		
		
		$manifestXML = $zip->getFromName($manifestPath);
		
		if ($manifestXML === false) {
			throw new Exception("Unable to read manifest file.");
			return;
		}		
		
		$doc = $this->loadXML($manifestXML);
		
		$xpath = new DOMXpath($doc);
		$xpath->registerNamespace("scormdefault", "http://www.imsproject.org/xsd/imscp_rootv1p1p2");
		
		$organizations = $xpath->query("//scormdefault:organization");
		if (!$organizations->length) {
			throw new Exception("The course title and launch URL can't be determined since the manifest contains no organizations.");
			return;
		}
		else if ($organizations->length > 1) {
			throw new Exception("The course title and launch URL can't be determined since the manifest contains multiple organizations.");
			return;			
		}
		
		$titleNode = $xpath->query("scormdefault:title", $organizations->item(0));
		
		if (!$titleNode->length) {
			throw new Exception("Course title not found in manifest.");
			return;
		}
		
		$courseData["title"] = $titleNode->item(0)->textContent;
		
		$itemNode = $xpath->query("scormdefault:item", $organizations->item(0));
		if (!$itemNode->length) {
			throw new Exception("The launch URL can't be determined from the manifest since its organization element has no item child.");
			return;
		}
		else if ($itemNode->length > 1) {
			throw new Exception("The launch URL can't be determined from the manifest since its organization element has multiple item children.");
			return;
		}
		
		$resourceID = $itemNode->item(0)->getAttribute("identifierref");
		$resourceNode = $xpath->query("//scormdefault:resource[@identifier='$resourceID']");
		
		if (!$resourceNode->length) {
			throw new Exception("The launch URL can't be determined from the manifest since the resource specified by its organization element can't be found.");
			return;
		}
		
		$url = $resourceNode->item(0)->getAttribute("href");
		if (!$url) {
			throw new Exception("Unable to find course launch URL in manifest.");
			return;
		}
		
		$fileIndex = $zip->locateName($url);
		if ($fileIndex === false) {
			throw new Exception("Course launch file '".htmlspecialchars($url)."' not found in ZIP archive.");
			return;
		}
		
		$courseData['url'] = $url;
		
		
		return $courseData;
	}
	
	function loadXML ($xml) {
		libxml_use_internal_errors(true);
		$doc = new DOMDocument();
		if(!$doc->loadXML($xml)) {
			$error = libxml_get_last_error();
			throw new Exception("XML parse error: ".$error->message);
		}
		
		return $doc;		
	}
	
	function getZIPError ($code) {
                switch ($code)
                    {
                        case 0:
                        return 'No error';
                        
                        case 1:
                        return 'Multi-disk zip archives not supported';
                        
                        case 2:
                        return 'Renaming temporary file failed';
                        
                        case 3:
                        return 'Closing zip archive failed';
                        
                        case 4:
                        return 'Seek error';
                        
                        case 5:
                        return 'Read error';
                        
                        case 6:
                        return 'Write error';
                        
                        case 7:
                        return 'CRC error';
                        
                        case 8:
                        return 'Containing zip archive was closed';
                        
                        case 9:
                        return 'No such file';
                        
                        case 10:
                        return 'File already exists';
                        
                        case 11:
                        return 'Can\'t open file';
                        
                        case 12:
                        return 'Failure to create temporary file';
                        
                        case 13:
                        return 'Zlib error';
                        
                        case 14:
                        return 'Malloc failure';
                        
                        case 15:
                        return 'Entry has been changed';
                        
                        case 16:
                        return 'Compression method not supported';
                        
                        case 17:
                        return 'Premature EOF';
                        
                        case 18:
                        return 'Invalid argument';
                        
                        case 19:
                        return 'Not a zip archive';
                        
                        case 20:
                        return 'Internal error';
                        
                        case 21:
                        return 'Zip archive inconsistent';
                        
                        case 22:
                        return 'Can\'t remove file';
                        
                        case 23:
                        return 'Entry has been deleted';
                        
                        default:
                        return 'An unknown error has occurred('.intval($code).')';
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

		wp_localize_script('gnlms', 'gnlms', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), "nonce"=>$this->nonce));

		$this->register_styles();
	}

	function register_styles () {
		$this->register_form_styles();
		wp_enqueue_style('jquery-ui-custom', plugins_url("css/jquery-ui.min.css", dirname(__FILE__)));
	}



	function register_form_styles () {
		wp_enqueue_style( "gnlms-forms", plugins_url('css/gnlms-forms.css', dirname(__FILE__)));
	}

	function controller () {
		$form = trim($_POST["gnlms_data_form"]);
		if($form) {
			do_action("gnlms_data_form_post", $form);
		}
	}


	function handleFormSubmission ($name) {
		if (!$this->data->tableDefExists($name)) {
			return;
		}
		
		if(!$this->verifyUser($name)) {
			die("Unauthorized");
			return;
		}

		switch ($name) {
			case "alert_preferences":
				$this->updateAlertPreferences();
				break;

			default:				
				$this->defaultUpdateEdit($name);				
		}


	}	

	function getRolesForAction ($action) {
		$allowedRoles = array("administrator", "lms_admin");

		$userActions = array(
			"gnlms_single_course_registration"
		);

		if (in_array($action, $userActions)) {
			$allowedRoles = array_merge($allowedRoles, array("lms_user", "subscriber"));
		}

		return  $allowedRoles;
	}

	function verifyUser ($action="") {
		$roles = $this->getRolesForAction($action);
		foreach ($roles as $role) {
			if ($this->user_in_role($role)) {
				return true;
			}
		}

		return false;
	}

		function verifyUserRole ($role="lms_admin") {
			return ($this->user_in_role("administrator") || $this->user_in_role($role));
		}

		function check_auth_blog_member()
		{
		global $post;
			if (  (!is_front_page()
				  && ( !is_user_logged_in() || !is_user_member_of_blog() )
				  &&(strpos($_SERVER['REQUEST_URI'], '/user-account/')!==0)
				  &&(strpos($_SERVER['REQUEST_URI'], '/register/')!==0)
				  &&(strpos($_SERVER['REQUEST_URI'], '/login/')!==0)
				  && (strpos($_SERVER['REQUEST_URI'], '/lostpassword/')!==0)
				  && (strpos($_SERVER['REQUEST_URI'], '/resetpass/')!==0)
				  && $_SERVER['PHP_SELF'] != '/wp-login.php')
				  && !get_post_meta( $post->ID, "gnlms_allow_anonymous", true )

				  ) {

			auth_redirect();
			}

		}


		function checkPageAccess () {
			global $post;
			// $role = get_post_meta($post->ID, "gnlms_role", true);

			$role = $this->getPageRole($post->ID);

			$this->check_auth_blog_member();

			 if($role && !$this->verifyUserRole($role)) {
				error_log("User attempted access of denied page:");
				header("HTTP/1.0 404 Not Found");
				include( get_404_template() );
				exit();
			}
		}

		function getPageRole ($pageID) {
			//error_log("gnlms_LMS->getPageRole($pageID)");
			$role = get_post_meta($pageID, "gnlms_role", true);

			if($role) {
				//error_log("Role found: '$role'");
				return $role;
			}
			else {
				$post = get_post($pageID);
				//error_log("Parent page is ".$post->post_parent);
				if($post->post_parent) {
					//error_log("Checking parent...");
					return $this->getPageRole($post->post_parent);
				}
				else {
					//error_log("Top-level page.");
					return null;
				}
			}
	}

	function ajaxSuccess ($data=array()) {
		echo(json_encode(array_merge(array("status"=>"OK"), $data)));
		exit();
	}

	function ajaxError ($msg) {
		echo(json_encode(array("status"=>"error", "msg"=>$msg)));
		exit();
	}

	function ajaxVerify () {
		$nonce = $_REQUEST['gnlms_nonce'];
		$action = trim($_REQUEST["action"]);
		return $this->verifyNonce($nonce) && $this->verifyUser($action);
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

	function gnlmsAjaxPost ($action) {
		if($method = $this->ajaxMethods[$action]) {
			$this->$method();
		}		
	}
	
	function doAjaxPost () {

		if(!$this->ajaxVerify()){
			$this->ajaxError("Unauthorized");
			return;
		}
		
		try {
			do_action("gnlms_ajax_post", $_POST['action']);
		}
		catch (Exception $e) {
			$this->ajaxError($e->getMessage());
			return;
		}

		$this->ajaxSuccess();
	}

	function ajaxCourseRegistration () {

		$userID = get_current_user_id();
		$courseID = intval($_POST['course_id']);
		$course = $this->data->fetchCourse($courseID);
		$registration = $this->retrieveRegistration($userID, $courseID);
		$err = "";
		
		if(!$userID) {
			$err = "Error: Unknown user.";
		}
		else if (!$course) {
			$err = "Error: Unknown course.";
		}
		else if ($price = $this->getCoursePrice(0, $courseID, $userID)) {
			$err = "Error: Free registration not allowed for this course.";
		}
		else if ($registration) {
			$err = "Error: User already registered for this course.";
		}
		else {
			$expirationDate = apply_filters("gnlms_course_registration_expiration_date", null, $userID, $courseID);
			try {
				$this->data->addUserCourseRegistration($userID, $courseID, $expirationDate);
			}
			catch (Exception $e) {
				$err = "Database error: ".$e->getMessage();
			}
		}

		if ($err) {
			throw new Exception($err);
		}
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
		if ($errors = apply_filters("gnlms_validation_errors", $this->validateFormData($name), $name, $_POST)) {
			$_POST['_errors'] = $errors;
			// print_r($errors);
			error_log("defaultUpdateEdit Errors: ". implode("|",$errors));
			return;
		}

		$sql = apply_filters("gnlms_update_edit_sql", $this->data->getUpdateEditSQL($name, $_POST), $name, $_POST);

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


	function validateFormData ($name) {
		$errors = array();
		$tableDef = $this->data->tableDefinition[$name];
		if($tableDef && $tableDef["validationFunction"]){
			$callback = $tableDef["validationFunction"];
			$errors = $this->$callback();
		}

		return $errors;
	}


	function gnlms_data_form ($atts, $content="", $code="") {
		$name = $atts["name"];
		$formFile = "forms/".$name.".php";

		if($name == trim($_POST["gnlms_data_form"]) || trim($_POST["gnlms_admin_action"])) {
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
		$this->displayTemplate(apply_filters("gnlms_data_form", $formFile, $atts), $contextRecord, $atts);
		return ob_get_clean();

	}

	function gnlms_course_detail ($atts) {
		$atts["name"] = "course";
		$atts["code"] = "gnlms_course_detail";

		return $this->gnlms_data_form($atts);
	}
	

	function getCoursePrice ($courseID, $userID) {
		return apply_filters("gnlms_course_price", 0, $courseID, $userID);
	}

	function getClientScript($uid,$cid,$scormInterfaceURL, $courseURL) {
		ob_start();
		include("templates/_course_launch.php");
		return ob_get_clean();
		
	}

	function getCourseLaunchURL ($cid) {
		return "/course-monitor/?id=$cid";
	}
	
	function getCoursePageLink ($cid) {
		if ($url = get_option("gnlms_course_page")) {
			$params = array();
			if (strpos($url, '?') !== false) {
				$query = strstr($url, '?');
				parse_str(substr($query, 1), $params);
				$url = strstr($url, '?', true);
			}
			
			return $url . '?' . http_build_query(array_merge($params, array("id"=>$cid)));
		}
		else return "";
	}

	function getCourseURL ($cid) {
			// $sql ="select url from gnlms_course where id=$cid";

			$sql = $this->data->db->prepare("select url from ".$this->data->tableName('course')." where id=%d", $cid);
			return ($this->data->db->get_var($sql));
	}
	function retrieveRegistration($uid, $cid) {
		// $sql ="select record_status from gnlms_user_course_registration where user_id=$uid and course_id=$cid";

		$sql ="select * from ".$this->data->tableName('user_course_registration')." where user_id=%d and course_id=%d";
		$sql = $this->data->db->prepare($sql, $uid, $cid);
		//return ($this->data->db->get_var($sql));

		return $this->data->db->get_row($sql);
	}

	
	function ajaxLaunchCourse () {
		echo $this->gnlms_launch_course();
		exit();
	}
	
	function gnlms_launch_course ($atts=array()) {
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
				// $context["user"] = $this->data->fetchObject("user", $contextID);
				$user = get_userdata($contextID);
				$context["user"] = $user ? array("ID"=>$user->ID, "first_name"=>$user->first_name, "last_name"=>$user->last_name) : array();				
				$context["courses"] = $this->data->getAvailableCoursesForUser($contextID);
				break;
		}

		ob_start();
		$this->displayTemplate($formFile, $context);
		return ob_get_clean();

	}


	function displayTemplate ($templateFile, $context=array(), $atts=array()) {
		if(file_exists($templateFile) || file_exists(dirname(__FILE__)."/$templateFile")) {
			// DS: maybe should check $templateFile is under WP install dir
			include($templateFile);
		}
		else {
			echo( "Error: $templateFile not found.");
		}		
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

		$this->writeLogMessage($msg, "alerts");
	}

	function doAdminAlerts () {

		$this->alertLog("Checking for admin alerts...");

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



	function var_error_log( $object=null ){
		ob_start();                    // start buffer capture
		var_dump( $object );           // dump the values
		$contents = ob_get_contents(); // put the buffer into a variable
		ob_end_clean();                // end capture
		error_log( $contents );        // log contents of the result of var_dump( $object )
	}






}




?>