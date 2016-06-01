<?php

class gnlms_Data extends gn_PluginDB {

	var $currentCourse = null;
	var $localizeNames = false;

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
				"columns"=>array(
					"id",
					"course_number",
					"last_update",
					"title",
					"description",
					"url",
					"image",
					"certificate",
					"credit",
					"record_status"
				),
				"listcolumns"=>array("id", "title", "case record_status when 1 then 'Yes' else 'No' end as 'active'"),
				"defaults"=>array(
					"record_status"=>0,
					"last_update"=>null
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
					"test_attempts",
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


			"admin_user_list"=>array(
				"listcolumns"=>array("u.id", "u.user_name as 'User Name'", "u.last_name as 'Last Name'", "u.first_name as 'First Name'", "u.email", "t1.event_date as 'Last Activity'"),
				"list_select_table"=>"(".$this->getUserSelect().") u left join #user_course_event# uce on u.id=uce.user_id  left join (select user_id, max(event_date) as 'event_date' from #user_course_event# group by user_id) t1 on t1.user_id = u.id",
				"groupby"=>"group by u.id"
			),


			"active_courses"=>array(
				"list_select_table"=>"(select * from #course# where record_status=1) c",
				"listcolumns"=>array("c.id", "c.title")
			),

			"admin_course_list"=>array(
				"table"=>"course",
				// "list_select_table"=>"#course# c",
				"listcolumns"=>array("id", "title", "description", "record_status as 'active'")
			),

			"user_current_courses"=>array(
				// DS: Modifying to include expired courses
				// "list_select_table"=>"#course# c inner join #user_course_registration# ucr on c.id=ucr.course_id and ucr.course_completion_date is null and (ucr.expiration_date > current_date() or ucr.expiration_date is null)",

				"list_select_table"=>"#course# c inner join #user_course_registration# ucr on c.id=ucr.course_id and ucr.course_status != 'Completed' and ucr.course_status != 'Failed' and ucr.record_status=1",
				"listcolumns"=>array("c.id", "c.title", "c.description", "c.course_number", "ucr.course_status", "c.url", "if(ucr.expiration_date < current_date(), 1, 0) as 'expired'"),
				"context_filters"=>array(
					"context_user_id"=>"ucr.user_id=#current_user_id#"
				),
				"filter"=>"c.record_status=1"
			),
			
			"user_completed_courses"=>array(
				"list_select_table"=>"#user_course_registration# ucr left join #course# c on ucr.course_id = c.id",
				"listcolumns"=>array("c.*", "ucr.*"),
				"context_filters"=>array(
					"context_user_id"=>"ucr.user_id=#current_user_id#"
				),
				"filter"=>"ucr.course_status='Completed'"
			),

			"user_available_courses"=>array(

				"list_select_table"=>"#course# c left join #user_course_registration# ucr on c.id=ucr.course_id and ucr.user_id=%d",
				"listcolumns"=>array("c.*, case when ucr.record_status=0 then 'Inactive' else ucr.course_status end as 'course_status', ucr.registration_date, ucr.course_completion_date, ucr.expiration_date, ucr.score"),
				"filter"=>"c.record_status=1"
			),
			
			"available_courses"=>array(
				"list_select_table"=>"#course# c",
				"listcolumns"=>array("c.id", "c.title", "c.description", "c.image", "c.credit"),
				"filter"=>"record_status=1"
			),
			
			"course_users"=>array(
				"list_select_table"=>"(".$this->getUserSelect().") u inner join #user_course_registration# ucr on u.id=ucr.user_id",
				"listcolumns"=>array("u.id", "concat(u.last_name, ', ', u.first_name) as 'name'", "u.email", "ucr.course_status", "ucr.registration_date", "ucr.expiration_date"),
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
				"listcolumns"=>array("c.id","ucr.id as 'ur_id'", "ucr.course_id", "c.title", "c.course_number", "ucr.score", "date_format(ucr.course_completion_date, '%M %e, %Y') as 'date_completed'"),
				"context_filters"=>array(
					"context_user_id"=>"ucr.user_id=#current_id#"
				)

			),
			
			"admin_recent_activity"=>array(
				"list_select_table"=>"#user_course_event# uce inner join (".$this->getUserSelect().") u on uce.user_id=u.id inner join #course# c on c.id=uce.course_id and uce.event_date >= date_add(curdate(), interval -2 week)",
				"listcolumns"=>array("u.id as 'id', date_format(uce.event_date, '%Y-%m-%d') as 'Date', concat(u.last_name, ', ', u.first_name) as 'User', uce.event_type as 'Status', c.title as 'course'")
			),
			
			
			"evaluation_response"=>array(
				"table"=>"evaluation_response",
				"columns"=>array("id", "user_id", "course_id", "response_date", "q1", "q2", "q3", "q4", "q5", "q6", "q7", "q8", "q9", "q10", "q11", "q12", "q13", "q14", "q15", "q16", "q17", "q18", "q19", "q20")				
			)
			
			
		);


	}

	function getUserSelect () { 
		$users = $this->db->users;
		$usermeta = $this->db->usermeta;
		
		$sql = "select u.id, u.user_login as 'user_name', u.user_email as 'email', um1.meta_value as 'first_name', um2.meta_value as 'last_name'";
		$sql .= " from $users u";
		$sql .= " left join $usermeta um1 on u.id=um1.user_id and um1.meta_key='first_name'";
		$sql .= " left join $usermeta um2 on u.id=um2.user_id and um2.meta_key='last_name'";
		
		return $sql;
	}
	
	
	function getListSQL ($name, $atts=array()) { // override of super class method to allow filtering
		$sql = parent::getListSQL($name, $atts);
		
		return apply_filters("gnlms_list_sql", $sql, $name, $atts);
	}

	function tableName ($internalName) {
		$key = $internalName;
		$internalName = $this->tableDefinition[$key]['table'];

		$result = $internalName?$this->prefixTableName($internalName):"";

		return ($result);

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

	
	// DS: adding override for filtering
	function fetchObject ($name, $id, $output=ARRAY_A) {
		
		$obj = (array_key_exists($name, $this->tableDefinition)) ?  parent::fetchObject($name, $id, $output) : null;
		return apply_filters("gnlms_data_object", $obj, $name, $id, $output);
	}

	function var_error_log( $object=null ){
		ob_start();                    // start buffer capture
		var_dump( $object );           // dump the values
		$contents = ob_get_contents(); // put the buffer into a variable
		ob_end_clean();                // end capture
		error_log( $contents );        // log contents of the result of var_dump( $object )
	}




	function getAvailableUsersForCourse ($course_id, $query, $search_field="last_name") {

		
		$users = $this->db->users;
		$usermeta = $this->db->usermeta;
		
		$sql = "select u.id, concat(um2.meta_value, ', ', um1.meta_value) as 'name'";
		$sql .= " from $users u";
		$sql .= " left join $usermeta um1 on u.id=um1.user_id and um1.meta_key='first_name'";
		$sql .= " left join $usermeta um2 on u.id=um2.user_id and um2.meta_key='last_name'";
		$sql .= " where u.id not in (select user_id from #user_course_registration# where course_id=%d) and um2.meta_value like %s";
		
		$sql = $this->replaceTableRefs($sql);
		$sql = $this->db->prepare($sql, $course_id,  preg_replace('/[%_]/', "\\$0", $query)."%");
		
		$sql = apply_filters("gnlms_course_available_users_sql", $sql, $course_id, $query, $search_field);		
		
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

	function getUserSubscriptionCodeCourse ($userID, $courseID) {
		$sql = "select scc.*";
		$sql .= " from #subscription_code_course# scc inner join #user# u on u.subscription_code_id=scc.subscription_code_id";
		$sql .= " where u.id=%d and scc.course_id=%d";

		$sql = $this->replaceTableRefs($sql);
		$sql = $this->db->prepare($sql, $userID, $courseID);

		return $this->db->get_row($sql);
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

		$values = array(
			"user_id"=>$userID,
			"course_id"=>$courseID,
			"registration_date"=>date('Y-m-d H:i:s'),
			"course_status"=>"Registered"
		);

		if($expiration_date) $values["expiration_date"] = $expiration_date;

		$values = apply_filters("gnlms_ucr_data", $values);

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
		$users = $this->db->users;
		$usermeta = $this->db->usermeta;	
				
		$sql = "select um2.meta_value as 'Last Name', um1.meta_value as 'First Name', u.user_email as 'Email', convert_tz(u.user_registered, '".date('P')."', '". $this->formatTimezoneOffset(get_option('gmt_offset')) ."') as 'Registration Date'";
		$sql .= " from $users u";
		$sql .= " left join $usermeta um1 on u.id=um1.user_id and um1.meta_key='first_name'";
		$sql .= " left join $usermeta um2 on u.id=um2.user_id and um2.meta_key='last_name'";
		$sql .= " where u.user_registered >= %s";
		$sql .= " order by u.user_registered desc";

		
		return $this->get_results($this->db->prepare($sql, $dateStr), ARRAY_A);
	}




	function getUserCourseEvents ($eventType, $date) {
		
		$users = $this->db->users;
		$usermeta = $this->db->usermeta;	

		$sql = "select um2.meta_value as 'Last Name', um1.meta_value as 'First Name', u.user_email as 'Email',";
		$sql .= " c.title as 'Course', uce.event_date as 'Date'";
		$sql .= " from #user_course_event# uce";
		$sql .= " inner join $users u on u.id=uce.user_id";
		$sql .= " left join $usermeta um1 on u.id=um1.user_id and um1.meta_key='first_name'";
		$sql .= " left join $usermeta um2 on u.id=um2.user_id and um2.meta_key='last_name'";
		$sql .= " inner join #course# c on c.id=uce.course_id";
		$sql .= " where uce.event_type=%s";
		$sql .= " and unix_timestamp(uce.event_date) >= %d";
		$sql .= " order by uce.event_date desc";
		
		$sql = $this->replaceTableRefs($sql);

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