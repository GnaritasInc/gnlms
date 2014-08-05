<?php

class gnlms_ListWidget extends gn_ListWidgetClass {

	function __construct() {
		parent::__construct();

		$this->registerShortcodes();
		add_action('wp_enqueue_scripts', array(&$this, 'registerScripts'));
		add_filter('gn_list_filter_sql', array(&$this, 'list_filter_sql'), 10, 4);
	}

/**
 *
 * @param
 * @return
 */


function super ($methodName) {
		if ($parent = get_parent_class(__CLASS__  )) {
			if (method_exists($parent, $methodName)) {
				parent::$methodName();

			}
		}

	}


	function registerScripts () {

		$this->super("registerScripts");

		wp_enqueue_script('gn-forms', plugins_url("scripts/gn.forms.js", dirname(__FILE__)), array("jquery", "jquery-ui-datepicker"));

		// DS: jquery-treeview loaded by gnaritas-portal-user
		wp_enqueue_script('gnlms-list-widget', plugins_url("scripts/gnlms-list-widget.js", dirname(__FILE__)), array("jquery", "gn-forms", "jquery-treeview"));
	}

	function registerShortcodes () {
		add_shortcode('gnlms_course_list', array(&$this, 'listCourses'));
		add_shortcode('gnlms_course_field', array(&$this, 'getCourseField'));
		add_shortcode('gnlms_user_list', array(&$this, 'listUsers'));
		add_shortcode('gnlms_course_user_list', array(&$this, 'listCourseUsers'));
		add_shortcode('gnlms_admin_user_current_courses', array(&$this, 'listUserCurrentCourses'));
		add_shortcode('gnlms_admin_user_completed_courses', array(&$this, 'listUserCompletedCourses'));
		add_shortcode('gnlms_admin_recent_activity', array(&$this, 'listRecentActivity'));
		add_shortcode('gnlms_active_courses', array(&$this, 'listActiveCourses'));

		add_shortcode('gnlms_admin_course_list', array(&$this, 'doAdminCourseList'));
		add_shortcode('gnlms_user_current_courses', array(&$this, 'doUserCurrentCourseList'));
		add_shortcode('gnlms_user_available_courses', array(&$this, 'doUserAvailableCourseList'));

		add_shortcode('gnlms_admin_announcements', array(&$this, 'listAnnouncements'));
		add_shortcode('gnlms_user_announcements', array(&$this, 'listAnnouncements'));
		add_shortcode('gnlms_organizations', array(&$this, 'listOrganizations'));
		add_shortcode('gnlms_subscription_codes', array(&$this, 'listSubscriptionCodes'));
		add_shortcode('gnlms_subscription_code_courses', array(&$this, 'listSubscriptionCodeCourses'));
		add_shortcode('gnlms_organization_courses', array(&$this, 'listOrganizationCourses'));
		
		// add_shortcode('gnlms_shopping_cart', array(&$this, 'doShoppingCart'));
		
		add_shortcode('gnlms_button', array(&$this, 'getButtonHTML'));

	}
	
	
	function getButtonHTML ($atts, $content, $code) {
		$href = get_site_url().$atts['href'];
		$button = "<a class='gnlms-button' href='$href'>".htmlspecialchars($content)."</a>";
		
		return $button;
	}

	function doAdminCourseList ($atts, $content, $code) {
		$atts["key"] = "admin_course_list";
		return $this->gn_short_list($atts, $content, $code);
	}

	function doUserCurrentCourseList ($atts, $content, $code) {
		$atts["key"] = "user_current_courses";
		$atts["context_filter"]="context_user_id";
		return $this->gn_short_list($atts, $content, $code);
	}
	
	function doUserAvailableCourseList ($atts, $content, $code) {
		$atts["key"] = "user_available_courses";
		$atts["context_filter"]="context_user_id";
		$atts["defaultsort"]="c.title";
		return $this->gn_short_list($atts, $content, $code);
	}

	function listAnnouncements ($atts, $content, $code) {
		$atts["key"] = "announcement";
		$atts["code"] = $code;
		$atts["defaultsort"]="create_date desc";

		return $this->gn_full_list($atts, $content, $code);
	}

	function listCourses ($atts, $content, $code) {
		$atts["key"]  = "course";
		return $this->gn_full_list($atts, $content, $code);
	}

	function listOrganizations ($atts, $content, $code) {
		$atts["key"] = "organization";
		$atts["defaultsort"] = "name";

		return $this->gn_full_list($atts, $content, $code);
	}

	function listActiveCourses ($atts, $content, $code) {
		$atts["key"]  = "active_courses";
		return $this->gn_full_list($atts, $content, $code);
	}

	function listUsers ($atts, $content, $code) {
		$atts["key"]  = "admin_user_list";
		return $this->gn_full_list($atts, $content, $code);
	}

	function getContextKey ($atts) {
		return $atts["context_key"] ? $atts["context_key"] : "id";
	}

	function listSubscriptionCodes ($atts, $content, $code) {
		$atts["key"]="subscription_code";
		$atts["context_key"] = $this->getContextKey($atts);
		$atts["context_filter"]="context_organization_id";
		$atts["edit"] = "#";
		return $this->gn_full_list($atts, $content, $code);

	}

	function listSubscriptionCodeCourses ($atts, $content, $code) {
		$atts["key"]="subscription_code_course_list";
		$atts["context_key"] = $this->getContextKey($atts);
		$atts["context_filter"]="context_subscription_code_id";
		$atts["edit"] = "#";
		return $this->gn_full_list($atts, $content, $code);
	}

	function listOrganizationCourses ($atts, $content, $code) {
		$atts["key"]="organization_course_list";
		$atts["context_key"] = $this->getContextKey($atts);
		$atts["context_filter"]="context_organization_id";
		return $this->gn_full_list($atts, $content, $code);
	}


	function listCourseUsers ($atts, $content, $code) {
		$atts["key"]="course_users";
		$atts["context_key"] = $this->getContextKey($atts);
		$atts["context_filter"]="context_course_id";
		$atts["defaultsort"] = "u.last_name";
		return $this->gn_full_list($atts, $content, $code);
	}

	function listUserCurrentCourses ($atts, $content, $code) {
		$atts["key"]="admin_user_current_courses";
		$atts["context_key"] = $this->getContextKey($atts);
		$atts["context_filter"]="context_user_id";
		$atts["defaultsort"] = "c.title";
		return $this->gn_full_list($atts, $content, $code);

	}

	function listUserCompletedCourses ($atts, $content, $code) {
		$atts["key"]="admin_user_completed_courses";
		$atts["context_key"] = $this->getContextKey($atts);
		$atts["context_filter"]="context_user_id";
		$atts["defaultsort"] = "date_completed desc";
		return $this->gn_full_list($atts, $content, $code);
	}

	function listRecentActivity ($atts, $content, $code) {
		$atts["key"]="admin_recent_activity";
		$atts["defaultsort"] = "uce.event_date desc";
		return $this->gn_full_list($atts, $content, $code);
	}
	
	function doShoppingCart ($atts, $content, $code) {
		$atts["key"]="shopping_cart";
		return $this->gn_full_list($atts, $content, $code);
	}


	function getCourseField ($atts) {
		$course = $this->data->getCurrentCourse();
		$name = $atts['name'];

		return $course[$name];

	}


	function addFilter ($filtername) {
		$str = "";
		switch($filtername) {
			case 'last_name':
				$str="<label>Last name starts with: <input type='text' name='last_name' /></label>";
				break;
			case 'first_name':
				$str="<label>First name starts with: <input type='text' name='first_name' /></label>";
				break;
			case 'email':
				$str="<label>Email contains: <input type='text' name='email' /></label>";
				break;
			case 'company':
				$str="<label>Company: <input type='text' name='o__name' /></label>";
				break;
			case 'last_activity':
				$str = "<div>Last activity between <input type='date' name='t1__event_date_start' /> and <input type='date' name='t1__event_date_end' /></div>";
				break;

			case 'course_status':
				ob_start();
				include(dirname(__FILE__)."/templates/filter-course-status.php");
				$str = ob_get_clean();
				break;

			case 'user-sort':
				ob_start();
				include(dirname(__FILE__)."/templates/user-sort.php");
				$str = ob_get_clean();
				break;

			case 'course-user-sort':
				ob_start();
				include(dirname(__FILE__)."/templates/course-user-sort.php");
				$str = ob_get_clean();
				break;

			case 'code':
				$str = "<label>Registration code: <input type='text' name='code' /></label>";
				break;
			default:
				$str="";
				break;

		}

		return $str;
	}

	function isSort ($name) {
		$sortKeys = array("user-sort", "course-user-sort");

		return in_array($name, $sortKeys);
	}

	function isFilter ($name) {
		$str = "";
		switch($name) {
			case 'last_name':
			case 'first_name':
				$str = "like-front";
				break;

			case 'email':
				$str = "like";
				break;

			case 'o.name':
			case 'company':
			case 'code':
			case 'course_status':
				$str = "equal";
				break;

			case 't1.event_date_start':
				$str = "date-range";
				break;

			default:
				$str = "";
				break;
		}

		return $str;
	}

	function list_filter_sql ($val, $type, $filterKey, $filterValue) {
		if($type=="date-range") {
			return $this->dateRangeFilter($filterKey);
		}
		else return "";
	}

	function dateRangeFilter ($filterKey) {
		$columnName = preg_replace('/_(start|end)$/i', '', $filterKey);
		$requestKey = str_replace('.', '__', $columnName);
		$start = trim($_REQUEST[$requestKey."_start"]);
		$end = trim($_REQUEST[$requestKey."_end"]);

		if ($start && $end) {
			$columnName = preg_replace('/[^a-z0-9_.]+/i', '', $columnName);
			$filter = "date($columnName) between ".$this->data->quoteString($start)." and ".$this->data->quoteString($end);
			return " and $filter ";
		}
	}

	function generateDataTable ($atts, $inputRecords=false) {
		$output = "";
		global $gnlms;
		switch($atts["key"]) {
			case "admin_course_list":
				$output = $this->outputTreeViewList($atts, "admin-course-list.php");
				break;
			case "user_current_courses":
				$output = $this->outputTreeViewList($atts, "user-current-courses.php");
				break;
			case "user_available_courses":
				$output = $this->doCustomList("user-available-courses", $atts);
				break;
			
			case "announcement":
				$output = $this->doAnnouncementsList($atts);
				break;
			case "subscription_code":
				$output = $this->doCustomList("subscription-code-list", $atts);
				break;
			case "subscription_code_course_list":
				$output = $this->doCustomList("subscription-code-course-list", $atts);
				break;
			case "admin_user_current_courses":
				$output = $this->doCustomList("admin-user-courses", $atts);
				break;
			case "shopping_cart":
				$output = $this->doCustomList("shopping-cart", $atts, $this->data->fetchCourses($gnlms->getSelectedCourses()));
				break;
			default:
				$output = parent::generateDataTable($atts, $inputRecords);
				break;
		}

		return $output;
	}

	function doCustomList ($template, $atts, $inputRecords=array()) {
		if ($inputRecords) {
			$records = $inputRecords;
		}
		else {
			$sql = $this->generateSQL($atts);
			$countSQL = $this->generateCountSQL($atts);
			$records = $this->retrieveData($sql, $countSQL, $atts);
		}

		ob_start();
		include(dirname(__FILE__)."/templates/$template.php");
		return ob_get_clean();

	}

	function doAnnouncementsList ($atts) {
		$sql = $this->generateSQL($atts);
		$countSQL = $this->generateCountSQL($atts);
		$announcements = $this->retrieveData($sql, $countSQL, $atts);
		$isAdmin = $atts["code"] == "gnlms_admin_announcements" ? true : false;

		ob_start();
		include(dirname(__FILE__)."/templates/announcements-list.php");
		return ob_get_clean();
	}

	function outputTreeViewList ($atts, $templateFile) {
		$sql = $this->generateSQL($atts);
		$countSQL = $this->generateCountSQL($atts);
		$courses = $this->retrieveData($sql, $countSQL, $atts);
		if(!$courses) {
			return "<p>No data found.</p>";
		}

		ob_start();
		include(dirname(__FILE__)."/templates/$templateFile");
		return ob_get_clean();

	}
}

?>