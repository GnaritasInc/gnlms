<?php

class gnlms_Data extends gn_PluginDB {

	var $currentCourse = null;
	var $localizeNames = true;

	var $objectCache = array();

	var $courseStatusOptions = array("Registered", "In Progress", "Completed", "Expired");

	var $subscriptionPeriodIntervals = array(
		"day"=>"Days",
		"week"=>"Weeks",
		"month"=>"Months",
		"year"=>"Years"
	);


	function __construct()  {


		parent::__construct("gnlms");

		$this->initAlerts();

		$this->tableDefinition = array(
			"course" => array(
				"table"=>"course",
				"columns"=>array("id", "course_number", "record_status", "last_update", "title", "description", "url"),
				"listcolumns"=>array("id", "title", "case record_status when 1 then 'Yes' else 'No' end as 'active'"),
				"defaults"=>array(
					"record_status"=>0,
					"last_update"=>null
				)
			),
			"user" =>array (
				"table"=>"user",
				"columns"=>array(
					"id",
					"organization_id",
					"subscription_code_id",
					"user_name",
					"email",
					"first_name",
					"last_name",
					"middle_initial",
					"address_1",
					"address_2",
					"city",
					"state",
					"zip",
					"country",
					"title",
					"role",
					"phone"
				),
				"validationFunction"=>"validateUser"
			),
			
			"ecommerce"=>array(
				"table"=>"ecommerce",
				"columns"=>array(
					"id",
					"user_id",
					"transaction_date",
					"transaction_id",
					"transaction_amount"				
				)
			),
			
			"ecommerce_item"=>array(
				"table"=>"ecommerce_item",
				"columns"=>array(
					"id",
					"ecommerce_id",
					"course_id",
					"course_price"
				)
			),

			"user_course_registration"=>array(
				"table"=>"user_course_registration",
				"columns"=>array(
					"id",
					"course_id",
					"user_id",
					"registration_date",
					"registration_type",
					"ec_item_id",
					"expiration_date",
					"course_status",
					"course_completion_date",
					"score",
					"scormdata",
					"record_status"
				),
				"defaults"=>array(
					"record_status"=>0,
					"expiration_date"=>null,
					"course_completion_date"=>null,
					"ec_item_id"=>null
				)
			),

			"organization"=>array(
				"table"=>"organization",
				"columns"=>array("id", "name", "record_status"),
				"listcolumns"=>array("id", "name", "case record_status when 1 then 'Active' else 'Inactive' end as 'status'"),
				"defaults"=>array(
					"record_status"=>0
				)
			),

			"subscription_code"=>array(
				"table"=>"subscription_code",
				"columns"=>array('id','code','organization_id','expiration_date','user_limit','record_status'),
				"listcolumns"=>array("id", "code", "expiration_date", "user_limit", "record_status", "'Edit' as 'edit'"),
				"context_filters"=>array(
					"context_organization_id"=>"organization_id=#current_id#"
				),
				"defaults"=>array(
					"record_status"=>0
				)
			),

			"subscription_code_course"=>array(
				"table"=>"subscription_code_course",
				"columns"=>array("id", "subscription_code_id", "course_id", "subscription_period_number", "subscription_period_interval"),
				/*
				"list_select_table"=>"#subscription_code# sc inner join #subscription_code_course# scc on sc.id=scc.subscription_code_id inner join #course# c on c.id=scc.course_id",
				"listcolumns"=>array("scc.id", "sc.code as 'subscription_code'", "c.title as 'course'", "concat_ws(' ', scc.subscription_period_number, case when scc.subscription_period_number > 1 then concat(scc.subscription_period_interval, 's') else scc.subscription_period_interval end) as 'subscription_period'"),
				"context_filters"=>array(
					"context_subscription_code_id"=>"subscription_code_id=#current_id#"
				),
				*/
				"defaults"=>array(
					"subscription_period_number"=>null,
					"subscription_period_interval"=>null
				)
			),

			"subscription_code_course_list"=>array(
				"list_select_table"=>"#subscription_code# sc inner join #subscription_code_course# scc on sc.id=scc.subscription_code_id inner join #course# c on c.id=scc.course_id",
				"listcolumns"=>array("scc.id", "sc.code as 'subscription_code'", "c.title as 'course'", "concat_ws(' ', scc.subscription_period_number, case when scc.subscription_period_number > 1 then concat(scc.subscription_period_interval, 's') else scc.subscription_period_interval end) as 'subscription_period'"),
				"context_filters"=>array(
					"context_subscription_code_id"=>"subscription_code_id=#current_id#"
				)
			),

			"organization_course_list"=>array(
				"list_select_table"=>"#organization# o inner join #subscription_code# sc on o.id=sc.organization_id inner join #subscription_code_course# scc on scc.subscription_code_id=sc.id inner join #course# c on c.id=scc.course_id",
				"listcolumns"=>array("c.title as 'Course'",  "sc.code as 'Subscription Code'"),
				"context_filters"=>array(
					"context_organization_id"=>"o.id=#current_id#"
				)
			),

			"announcement"=>array(
				"table"=>"announcement",
				"columns"=>array("id", "create_date", "created_by", "title", "text"),
				"listcolumns"=>array("id", "create_date", "title", "text")
			),

			"admin_user_list"=>array(
				"listcolumns"=>array("u.id", "u.user_name as 'User Name'", "u.last_name as 'Last Name'", "u.first_name as 'First Name'", "u.email", "o.name as 'company'", "t1.event_date as 'Last Activity'"),
				"list_select_table"=>"#user# u left join #user_course_event# uce on u.id=uce.user_id left join #organization# o on o.id=u.organization_id left join #subscription_code# sc on sc.id=u.subscription_code_id left join (select user_id, max(event_date) as 'event_date' from #user_course_event# group by user_id) t1 on t1.user_id = u.id",
				"groupby"=>"group by u.id"

			),

			"active_courses"=>array(
				"list_select_table"=>"(select * from #course# where record_status=1) c",
				"listcolumns"=>array("c.id", "c.title")
			),

			"admin_course_list"=>array(
				"list_select_table"=>"#course# c",
				"listcolumns"=>array("c.id", "c.title", "c.description", "c.record_status as 'active'")
			),

			"user_current_courses"=>array(
				// DS: Modifying to include expired courses
				// "list_select_table"=>"#course# c inner join #user_course_registration# ucr on c.id=ucr.course_id and ucr.course_completion_date is null and (ucr.expiration_date > current_date() or ucr.expiration_date is null)",
				
				"list_select_table"=>"#course# c inner join #user_course_registration# ucr on c.id=ucr.course_id and ucr.course_status != 'Completed' and ucr.course_status != 'Failed' and ucr.record_status=1",
				"listcolumns"=>array("c.id", "c.title", "c.description", "c.course_number", "ucr.course_status", "c.url", "if(ucr.expiration_date < current_date(), 1, 0) as 'expired'"),
				"context_filters"=>array(
					"context_user_id"=>"ucr.user_id=#current_user_id#"
				)
			),
			
			"user_available_courses"=>array(
				"list_select_table"=>"#course# c left join #user_course_registration# ucr on c.id=ucr.course_id and ucr.record_status=1 and ucr.user_id=%d",
				"listcolumns"=>array("c.*, ucr.course_status, ucr.registration_date, ucr.course_completion_date, ucr.expiration_date, ucr.score")
			),

			"course_users"=>array(
				"list_select_table"=>"#user# u inner join #user_course_registration# ucr on u.id=ucr.user_id left join #organization# o on o.id=u.organization_id",
				"listcolumns"=>array("u.id", "concat(u.last_name, ', ', u.first_name) as 'name'", "u.email", "o.name as 'company'", "ucr.course_status", "ucr.registration_date", "ucr.expiration_date"),
				"context_filters"=>array(
					"context_course_id"=>"ucr.course_id=#current_id#"
				)

			),
			"user_course_event"=>array(
				"table"=>"user_course_event",
				"columns"=>array("id", "user_id", "course_id", "event_date", "event_type")
			),
			"admin_user_current_courses"=>array(
				"list_select_table"=>"#course# c inner join #user_course_registration# ucr on c.id=ucr.course_id and ucr.course_completion_date is null",
				"listcolumns"=>array("ucr.id", "c.id as 'course_id'", "c.title", "c.course_number", "ucr.course_status", "ucr.registration_date", "ucr.expiration_date"),
				"context_filters"=>array(
					"context_user_id"=>"ucr.user_id=#current_id#"
				)
			),
			"admin_user_completed_courses"=>array(
				"list_select_table"=>"#course# c inner join #user_course_registration# ucr on c.id=ucr.course_id and ucr.course_completion_date is not null",
				"listcolumns"=>array("c.id", "c.title", "c.course_number", "ucr.score", "date_format(ucr.course_completion_date, '%M %e, %Y') as 'date_completed'"),
				"context_filters"=>array(
					"context_user_id"=>"ucr.user_id=#current_id#"
				)

			),
			"admin_recent_activity"=>array(
				"list_select_table"=>"#user_course_event# uce inner join #user# u on uce.user_id=u.id inner join #course# c on c.id=uce.course_id and uce.event_date >= date_add(curdate(), interval -2 week)",
				"listcolumns"=>array("u.id as 'id', date_format(uce.event_date, '%Y-%m-%d') as 'Date', concat(u.last_name, ', ', u.first_name) as 'User', uce.event_type as 'Status', c.title as 'course'")
			)
		);


	}
	
	function tableName ($internalName) {
		return $this->prefixTableName($internalName);
	}

	function listSelectTableName($name) {
		$tableExpr = parent::listSelectTableName($name);
		if ($name == "user_available_courses") {		
			$tableExpr = $this->db->prepare($tableExpr, get_current_user_id());
		}
		
		return $tableExpr;
	}


	function initTableDefinitions () {
		require_once("includes/gnlms-data-table-defs.php");
	}

	function initAlerts () {
		$this->adminAlerts = array(
			"user_registration"=>array(
				"name"=>"User Registration",
				"eventType"=>"System Registration"
			),
			"course_start"=>array(
				"name"=>"Course Start",
				"eventType"=>"Started"
			),
			"course_completion"=>array(
				"name"=>"Course Completion",
				"eventType"=>"Completed"
			),
			"course_failure"=>array(
				"name"=>"Course Failure",
				"eventType"=>"Failed"
			)
		);

	}

	function contextFilter ($name, $atts) {
		$contextKey = $atts["context_key"] ? $atts["context_key"] : "id";
		$sql = parent::contextFilter($name, $atts);
		$sql = preg_replace('/#current_id#/i', intval($_GET[$contextKey]), $sql);

		global $user_ID;
		get_currentuserinfo();

		$sql = preg_replace('/#current_user_id#/i', intval($user_ID), $sql);

		return $sql;
	}


	function fetchCourse ($courseID) {
		$sql = $this->db->prepare($this->replaceTableRefs("select * from #course# where id=%d"), $courseID);
		return $this->db->get_row($sql);
	}
	
	function fetchCourses ($courseIDs) {
		if(count($courseIDs)) {
			$sql = $this->db->prepare($this->replaceTableRefs("select * from #course# where id in(". implode(', ', array_fill(0, count($courseIDs), '%d')) .") order by title"), $courseIDs);
			return $this->db->get_results($sql);
		}
		else return array();
		
	}

	function getCurrentCourse () {


		return $this->fetchObject("course", $_GET['id']);
	}

	function fetchObject($name, $id) {
		if(!$this->objectCache[$name]) {
			$this->objectCache[$name] = array();
		}

		if(!$this->objectCache[$name][$id]) {
			$sql = $this->getRecordSQL ($id, $name);
			$record = $sql ? $this->getRecordArray($sql) : null;
			$this->objectCache[$name][$id] = $record;
		}


		return $this->objectCache[$name][$id];

	}

	function addLMSUser ($user) {
		$sql = $this->replaceTableRefs("insert into #user# (id, user_name, email, first_name, last_name) values (%d, %s, %s, %s, %s)");
		$sql = $this->db->prepare($sql, $user->ID, $user->user_login, $user->user_email, $user->first_name, $user->last_name);

		$this->db->query($sql);
	}

	function updateLMSUser ($user) {
		$sql = $this->replaceTableRefs("update #user# set email=%s, first_name=%s, last_name=%s where id=%d");
		$sql = $this->db->prepare($sql, $user->user_email, $user->first_name, $user->last_name, $user->ID);

		$this->db->query($sql);
	}

	function updateWPUserData ($data) {

		foreach($data as $key=>$value){
			if($key=="email") {
				$sql = $this->db->prepare("update ".$this->db->users." set user_email=%s where id=%d", $value, $data["id"]);
			}
			else {
				$sql = $this->db->prepare("update ".$this->db->usermeta." set meta_value=%s where meta_key=%s and user_id=%d", $value, $key, $data["id"]);
			}

			$this->db->query($sql);
		}
	}

	function emailAvailable ($email, $user_id=0) {

		$sql = $this->db->prepare("select id from ".$this->db->users." where user_email=%s and id != %d", $email, $user_id);
		return $this->db->get_var($sql) ? false : true;

	}

	function getAvailableUsersForCourse ($course_id, $query, $search_field="last_name") {

		$searchData = array(
			"last_name"=>array(
				"cols"=>"concat_ws('; ', concat(u.last_name, ', ', u.first_name), o.name) as 'name'",
				"searchCol"=>"u.last_name",
				"orderBy"=>"u.last_name, u.first_name"
			),
			"company"=>array(
				"cols"=>"concat_ws(': ', o.name, concat(u.last_name, ', ', u.first_name)) as 'name'",
				"searchCol"=>"o.name",
				"orderBy"=>"o.name, u.last_name, u.first_name"
			)
		);

		if(!array_key_exists($search_field, $searchData)) $search_field="last_name";

		// $search_field = preg_replace('/[^a-z_.]/i', '', $search_field);

		$query = preg_replace('/[%_]/', "\\$0", $query)."%";

		// $sql = "select u.id, concat(u.last_name, ', ', u.first_name) as 'name', o.name as 'company' from #user# u left join #organization# o on o.id=u.organization_id where u.id not in (select user_id from #user_course_registration# where course_id=%d) and $search_field like %s order by last_name";

		$sql =  "select u.id, ".$searchData[$search_field]['cols'];
		$sql .= " from #user# u left join #organization# o on o.id=u.organization_id";
		$sql .= " where u.id not in (select user_id from #user_course_registration# where course_id=%d) and ".$searchData[$search_field]["searchCol"]." like %s";
		$sql .= " order by ".$searchData[$search_field]['orderBy'];

		$sql = $this->replaceTableRefs($sql);

		$sql = $this->db->prepare($sql, $course_id, $query);
		error_log("User search sql: $sql");
		return $this->db->get_results($sql);
	}

	function getAvailableCoursesForUser ($user_id) {
		$sql = "select * from #course# where id not in (select course_id from #user_course_registration# where user_id=%d) order by title";
		$sql = $this->replaceTableRefs($sql);
		return $this->db->get_results($this->db->prepare($sql, $user_id));
	}

	function getAvailableCoursesForSubscriptionCode ($code_id, $current_course) {
		$sql = "select id, title from #course# where id not in (select course_id from #subscription_code_course# where subscription_code_id=%d)";
		if($current_course) $sql .= " or id=%d";
		$sql .= " order by title";

		$sql = $this->replaceTableRefs($sql);

		$sql =  $current_course ? $this->db->prepare($sql, $code_id, $current_course) : $this->db->prepare($sql, $code_id);

		return $this->db->get_results($sql);
	}


	function assignCourseUsers ($courseID, $userIDs) {
		foreach($userIDs as $userID) {
			$this->addUserCourseRegistration($userID, $courseID);
		}
	}
	function assignUserCourses ($userID, $courseIDs) {
		$this->db->query("start transaction");
		try {
			foreach($courseIDs as $courseID) {
				$expiration_date = apply_filters("gnlms_course_expiration", null, $courseID, $userID);
				$this->addUserCourseRegistration($userID, $courseID, $expiration_date);
			}
		}
		catch (Exception $e) {
			$this->db->query("rollback");
			throw $e;
		}
		$this->db->query("commit");
	}

	function addUserCourseRegistration ($userID, $courseID, $expiration_date=null) {
		/*
		$sql = $this->db->prepare("insert into #user_course_registration# (user_id, course_id, registration_date, course_status) values (%d, %d, now(), 'Registered')", $userID, $courseID);
		$this->dbSafeExecute($sql);
		*/

		$values = array(
			"user_id"=>$userID,
			"course_id"=>$courseID,
			"registration_date"=>date('Y-m-d H:i:s'),
			"course_status"=>"Registered"
		);

		if($expiration_date) $values["expiration_date"] = $expiration_date;

		$sql = $this->getUpdateEditSQL("user_course_registration", $values, true);
		$this->dbSafeExecute($sql);
		$this->logUserCourseEvent($userID, $courseID, "Registered");
	}

	function logUserCourseEvent ($user_id, $course_id, $event_type) {
		$sql = "insert into #user_course_event# (user_id, course_id, event_type) values (%d, %d, %s)";
		$sql = $this->replaceTableRefs($sql);
		$sql = $this->db->prepare($sql, $user_id, $course_id, $event_type);
		$this->dbSafeExecute($sql);
	}

	function setCourseComplete ($user_id, $course_id, $score) {
		/*
		$sql = "update #user_course_registration# set course_status='Completed', course_completion_date=current_date(), score=%d where user_id=%d and course_id=%d";
		$sql = $this->db->prepare($sql, $score, $user_id, $course_id);

		$this->dbSafeExecute($sql);
		*/
		$this->logUserCourseEvent($user_id, $course_id, "Completed");
		$this->setUserCourseStatus($user_id, $course_id, "Completed", $score);
	}

	function setCourseFailed ($user_id, $course_id, $score) {
		$currentStatus = $this->getUserCourseStatus($user_id, $course_id);
		if($currentStatus != "Failed") {
			error_log("gnlms: User $user_id failed course $course_id with score $score");
			$this->logUserCourseEvent ($user_id, $course_id, "Failed");
			$this->setUserCourseStatus($user_id, $course_id, "Failed", $score);
		}
	}

	function logCourseLaunch ($user_id, $course_id) {

		$currentStatus = $this->getUserCourseStatus($user_id, $course_id);
		$event_type = "Accessed";
		$new_status = "";

		if($currentStatus == "Registered" || $currentStatus==null) {
			$event_type = "Started";
			$new_status = "In Progress";
		}

		$this->logUserCourseEvent ($user_id, $course_id, $event_type);

		if($new_status) {
			$this->setUserCourseStatus($user_id, $course_id, $new_status);
		}
	}

	function setUserCourseStatus ($user_id, $course_id, $status, $score=null) {
		if($score !== null) {
			$sql = "update #user_course_registration# set course_status=%s, score=%d, course_completion_date=current_date() where user_id=%d and course_id=%d";
			$sql = $this->replaceTableRefs($sql);
			$sql = $this->db->prepare($sql, $status, $score, $user_id, $course_id);

		}
		else {
			$sql = "update #user_course_registration# set course_status=%s where user_id=%d and course_id=%d";
			$sql = $this->replaceTableRefs($sql);
			$sql = $this->db->prepare($sql, $status, $user_id, $course_id);
		}
		$this->dbSafeExecute($sql);
	}

	function getUserCourseStatus ($user_id, $course_id) {
		$sql = "select course_status from #user_course_registration# where user_id=%d and course_id=%d";
		$sql = $this->replaceTableRefs($sql);
		$sql = $this->db->prepare($sql, $user_id, $course_id);

		return $this->db->get_var($sql);
	}

	function deleteAnnouncement ($id) {
		$sql = $this->db->prepare("delete from #announcement# where id=%d", $id);
		$sql = $this->replaceTableRefs($sql);
		$this->dbSafeExecute($sql);
	}

	function retrieveSubscriptionCode ($code) {
		$sql = $this->db->prepare("select * from #subscription_code# where code =%s", $code);
		$sql = $this->replaceTableRefs($sql);
		return $this->db->get_row($sql);
	}

	function retrieveSubscriptionCodeCourses ($code) {
		$sql = "select scc.* from #subscription_code_course# scc inner join #subscription_code# sc on sc.id=scc.subscription_code_id where sc.code=%s";
		$sql = $this->replaceTableRefs($sql);
		return $this->db->get_results($this->db->prepare($sql, $code));
	}

	function retrieveSubscriptionCount($subscription_id) {
		$sql = $this->db->prepare("select count(*) from #user# where subscription_code_id =%d", $subscription_id);
		$sql = $this->replaceTableRefs($sql);
		return $this->db->get_var($sql);
	}

	function retrieveExpiringRegistrations () {
		$sql = "select id, course_id, user_id from #user_course_registration# where course_completion_date is null and expiration_date <= current_date() and course_status != 'Expired'";
		$sql = $this->replaceTableRefs($sql);
		return $this->get_results($sql);
	}

	function updateCourseRegistrationStatus () {
		error_log("Updating course registration status...");

		$expired = $this->retrieveExpiringRegistrations();

		foreach($expired as $registration) {

			$this->setUserCourseStatus($registration->user_id, $registration->course_id, "Expired");
			$this->logUserCourseEvent ($registration->user_id, $registration->course_id, "Expired");

		}

		error_log("Course registration status updated.");
	}

	function getAssessmentFailureCount($uid, $cid, $assessmentName) {
		$sql = "select count(*) as 'total'";
		$sql .= " from #user_course_assessment_response#";
		$sql .= " where name = %s and result=0";
		$sql .= " and user_id = %d and course_id = %d";
		$sql .= " group by user_id, course_id, name";

		$sql = $this->replaceTableRefs($sql);
		$sql = $this->db->prepare($sql, $assessmentName, $uid, $cid);

		return $this->db->get_var($sql);
	}

	function getMaxAllowedAttempts($cid, $assessmentName) {
		// DS: hard-coded for now
		return 3;
	}

	function getAdminAlertPreferences ($userID) {
		$columns = array();
		$joins = array();

		foreach(array_keys($this->adminAlerts) as $i=>$alert) {
			$columns[] = "um$i.meta_value as '$alert'";
			$joins[] = "left join ".$this->db->usermeta." um$i on u.id=um$i.user_id and um$i.meta_key='".$this->replaceTableRefs('#alert_#')."$alert'";
		}

		$sql = "select ".implode(", ", $columns)." from ".$this->db->users." u ".implode(" ", $joins)." where u.id=%d";

		return $this->getRecordArray($this->db->prepare($sql, $userID));
	}

	function updateAdminAlertPreferences ($userID, $newPrefs) {
		$defaults = array();
		foreach(array_keys($this->adminAlerts) as $alert) {
			$defaults[$alert] = 0;
		}

		$prefs = shortcode_atts($defaults, $newPrefs);

		$result = true;
		foreach($prefs as $key=>$value) {
			$meta_key = $this->replaceTableRefs("#alert_#$key");
			error_log("Updating preference: $meta_key=>$value");
			delete_user_meta($userID, $meta_key);
			$result = $result && update_user_meta($userID, $meta_key, $value);
			error_log("Result: $result");
		}

		return $result;

	}

	function getAlertSubscribers ($alert) {
		$params = array(
			"meta_key"=>$this->replaceTableRefs("#alert_#$alert"),
			"meta_value"=>1
		);

		return get_users($params);
	}

	function getAlertEvents ($eventType, $date) {
		if($eventType == "System Registration") {
			return $this->getUserRegistrationEvents($date);
		}
		else {
			return $this->getUserCourseEvents ($eventType, $date);
		}
	}

	function getUserRegistrationEvents ($date) {

		$dateStr = date('Y-m-d H:i:s', $date);

		$sql = "SELECT u.last_name as 'Last Name', ";
		$sql .= " u.first_name as 'First Name',  u.email as 'Email', convert_tz(wp.user_registered, '".date('e')."', '".get_option('timezone_string')."') as 'Registration Date'";
		$sql .= " FROM #user# u";
		$sql .= " inner join wp_users wp on wp.id=u.id";


		$sql .= " where wp.user_registered >= %s";
		$sql .= " order by wp.user_registered desc";

		$sql = $this->replaceTableRefs($sql);

		//error_log("User registation SQL: ".$this->db->prepare($sql, $date));

		// return $this->get_results($this->db->prepare($sql, $date), ARRAY_A);

		// DS: Query on date string
		$dateStr = date('Y-m-d H:i:s', $date);
		return $this->get_results($this->db->prepare($sql, $dateStr), ARRAY_A);
	}




	function getUserCourseEvents ($eventType, $date) {
		$sql = "SELECT u.last_name as 'Last Name', ";
		$sql .= " u.first_name as 'First Name',  u.email as 'Email',";
		$sql .= " c.title as 'Course', uce.event_date as 'Date'";
		$sql .= " FROM #user_course_event# uce";
		$sql .= " inner join #user# u on u.id=uce.user_id";
		$sql .= " inner join #course# c on c.id=uce.course_id";
		$sql .= " where uce.event_type=%s";

		// DS: Changing to unix timestamp
		// $sql .= " and uce.event_date >= %s";
		$sql .= " and unix_timestamp(uce.event_date) >= %d";

		$sql .= " order by uce.event_date desc";

		$sql = $this->replaceTableRefs($sql);

		//error_log("Course event SQL: ".$this->db->prepare($sql, $eventType, $date));
		// return $this->get_results($this->db->prepare($sql, $eventType, $date), ARRAY_A);


		return $this->getLocalTimeResults($this->db->prepare($sql, $eventType, $date), ARRAY_A);


	}

	function quoteString ($str) {
		if($str === null) {
			return 'null';
		}
		else return parent::quoteString($str);
	}


}

?>