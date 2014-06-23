<?php

if(!class_exists("gn_TaskManager")):

class gn_TaskManager extends gn_WebInterface {
	var $db;
	var $GlobalMSG = "";

	private $homeDir;
	private $homeURL;
	private $htmlDir;
	public $jsURL;
	private $cssURL;
	private $nonceString = "gntm-nonce";
	private $nonce;

	private $sortKeys = array("due"=>"t.due_date", "title"=>"t.title", "status"=>"t.status", "start"=>"t.start_date", owner=>"t.owner", creator=>"creator", subtype=>"t.subtype");

	var $taskCategories = array(
		"general"=>"General Tasks",
	);

	function __construct() {
		$this->db = new gn_TaskManagerDB();
		$this->db->parent = $this;


		$baseDir = trailingslashit(dirname(dirname(plugin_basename(__FILE__))));
		$this->homeDir = WP_PLUGIN_DIR."/$baseDir";
		$this->htmlDir = $this->homeDir."html/";
		$this->homeURL = WP_PLUGIN_URL."/$baseDir";
		$this->jsURL = $this->homeURL."js/";
		$this->cssURL = $this->homeURL."css/";


		$this->setHooks();

		$this->recordLimit=10;


		add_shortcode('gntasks', array(&$this, 'dispatch'));


	}



	private function setHooks() {
		add_action('init', array(&$this, 'registerScripts'));
		add_action('init', array(&$this, 'registerCSS'));
		add_action('wp_ajax_gntm-gettasks', array(&$this, 'ajaxGetCurrentTasks'));
		add_action('wp_ajax_gntm-addtask', array(&$this, 'ajaxCreateActivity'));
		add_action('wp_ajax_gntm-settaskcompletion', array(&$this, 'ajaxSetTaskCompletion'));
		add_action('wp_ajax_gntm-gettaskinputform', array(&$this, 'ajaxGetInputForm'));
		add_action('wp_ajax_gntm-updatetask', array(&$this, 'ajaxUpdateTask'));
		add_action('wp_ajax_gntm-deletetask', array(&$this, 'ajaxDeleteTask'));

		add_filter('gncalendar_event_result', array(&$this, 'addTaskDueDates'), 10, 3);

	}

	function registerScripts () {

		$this->nonce = wp_create_nonce($this->nonceString);


		if (!is_admin()) {

			// wp_enqueue_script('jquery', $this->jsURL."jquery.js");

			// wp_enqueue_script('jquery-ui-custom', $this->jsURL."jquery-ui-1.8.7.custom.min.js");

			wp_enqueue_script('gn-taskmanager', $this->jsURL."gn-taskmanager.js", array("jquery", "jquery-ui-dialog"));


			global $current_user;
			get_currentuserinfo();

			$jsVars = array(
				"ajaxURL" => admin_url('admin-ajax.php'),
				"nonce" => $this->nonce,
				"userID" => $current_user->ID,
				"userName" => $current_user->user_login
			);

			wp_localize_script('gn-taskmanager', "gn_TaskManager", $jsVars);

		}

	}

	function registerCSS () {

		// wp_enqueue_style('jquery-ui-custom', $this->cssURL."jquery-ui-1.8.7.custom.css");
		wp_enqueue_style('gn-taskmanager', $this->cssURL."gn-taskmanager.css");
	}

	function install () {
		$this->db->insureTables();
	}

	function wp_user_role() {
		global $current_user, $wp_roles;
		if( $current_user->id )  {
			foreach($wp_roles->role_names as $role => $Role) {
				if (array_key_exists($role, $current_user->caps)) {
					return ($role);
					break;

				}
			}
		}
	}

	function get_wp_user_role($user_id) {
		global  $wp_roles;
		$userInfo = get_userdata($user_id);


		if( $user_id )  {
			foreach($wp_roles->role_names as $role => $Role) {

				// if (array_key_exists($role, $userInfo->wp_capabilities)) {
				if (array_key_exists($role, $userInfo->roles)) {
					return ($role);
					break;
				}
			}
		}
	}



	function dispatch($atts) {
		$content = "";
		$filters = $atts["filteroptions"] ? preg_split("/[\s,]+/", $atts["filteroptions"]) : array();
		ob_start();
		switch(trim($atts['type'])){
			case "":
				include($this->htmlDir."display.php");
				break;

			default:
				include($this->htmlDir."context_tasks.php");
				break;
		}
		$content = ob_get_clean();


		return $content;
	}

	function writeTasks ($type) {


		switch (trim($type)) {
			case "":
				$this->writeCurrentTasks();
				break;
			default:
				$this->writeMetaFilteredTasks("${type}_id", $_GET['id']);
				break;
		}
	}

	function addTaskDueDates ($result, $start, $end) {
		global $current_user;
		get_currentuserinfo();

		$taskUserID = $current_user->ID;

		/*
		if(intval($_GET['userid']) && $this->wp_user_role()=="ccnx_pm") {
			global $ccnx_DataInterface;
			$sscs = $ccnx_DataInterface->data->getSSC_WP_IDs($current_user->ID);

			if(in_array($_GET['userid'], $sscs)) {
				$taskUserID = $_GET['userid'];
			}
		}
		*/

		$dueDates = $this->db->getTaskDueDates ($start, $end, $taskUserID);

		foreach($dueDates as $row) {
			$result[] = $row;
		}

		return $result;
	}

	function ajaxGetCurrentTasks () {

		/*
		if ($_GET["tasktype"]=="support") {
			$this->writeSupportTasks();
			}
		else {
			$this->writeTasks($_GET["tasktype"]);
		}
		*/
		$this->writeTasks($_GET["tasktype"]);
		exit();
	}
	

	function writeFooter() {
		$str="";



		if($this->foundRows > $this->recordLimit) {

			$offset = $this->getQuerystringValue("offset")?$this->getQuerystringValue("offset"):0;

			$str.= $this->writeResultScroller ($this->foundRows, $this->recordLimit, $offset, "offset");
		}

		return ($str);
	}


	function writeMetaFilteredTasks ($metaKey, $metaValue=false) {
		if(value!==false) {
			if(!$tasks = $this->db->getActivityMetaFilteredTasks($metaKey, $metaValue)){
				echo("<table><tr><td><i>[No current tasks]</i></td></tr></table>");
				return;
			}

			$html = "<tr><th>Title</th><th>Status</th><th>Due Date</th></tr>";
			foreach($tasks as $task) {
				$checked = $task->completion_date ? "checked='checked' " : "";
				$url = "#";
				$linkClass= "class='task-edit' ";



				$html .= "<tr>";

				$html.="<td><a $linkClass href='$url' id='t".$this->htmlEncode($task->id)."'>".$this->htmlEncode($task->title)."</a></td>";
				$html .= "<td>".$this->htmlEncode($task->status)."</td>";
				$html .= "<td>".$this->htmlEncode($task->due_date)."</td></tr>";
			}

			echo("<table>$html</table>");

		}
	}


	private function writeCurrentTasks ($type="") {

		$header = "<table>";
		$header.= "<tr><th>Complete</th><th>Title</th><th>Status</th><th>Start Date</th><th>Due date</th><td>&#160;</td></tr>";

		$html = "";

		$sortBy = $this->sortKeys[$_GET['sortby']];
		$typeFilter = $type ? $type : $_GET['type'];
		$showComplete = $_GET["showcomplete"] ? true : false;
		$offset = intval($_GET['offset']) ? $_GET['offset'] : 0;
		$tasks = $this->db->getCurrentTasks($typeFilter, $sortBy, $showComplete, null, $offset, $this->recordLimit);
		$this->foundRows = $this->db->getFoundRows();

		foreach($tasks as $task) {
			$url = $task->url ? $task->url  : "#";
			$class = $this->getTaskCSSClass($task);
			$linkClass = $this->getTaskLinkClass($task);
			$checked = $task->completion_date ? "checked='checked' " : "";
			$html .= "<tr$class>";
			$html .= "<td><input type='checkbox' title='Mark this task complete...' class='task-checkbox' $checked value='$task->id' /></td>";
			$html .= "<td><a $linkClass href='$url' id='t".$this->htmlEncode($task->id)."'>".$this->htmlEncode($task->title)."</a></td>";
			$html .= "<td>".$this->htmlEncode($task->status)."</td>";
			$html .= "<td>".$this->htmlEncode($task->start_date)."</td>";
			$html .= "<td>".$this->htmlEncode($task->due_date)."</td>";
			$html .= "<td><a class='task-delete' title='Delete this task...' href='#' id='t".$this->htmlEncode($task->id)."'>Delete</a></td>";

			$html .= "</tr>";
		}

		$html.="</table>";

		$html .= $this->writeFooter();

		$html = $html ? $header.$html : "<tr><td>[No tasks]</td></tr>";

		echo($html);
	}

	function getTaskLinkClass ($task) {
		$class = "class='task-edit' ";

		return $class;
	}



	private function getTaskCSSClass ($task) {
		$class = "";

		if($task->completion_date) {
			$class = " class='task-complete'";
		}
		else if (!$task->active) {
			$class = " class='task-inactive'";
		}

		return $class;
	}

	function ajaxGetInputForm () {

		$taskID = trim($_GET['taskid']);

		$task = $taskID ? $this->db->getTaskByID($taskID) : new gn_Task();

		$activityMeta = $task->activity_id ? $this->db->getActivityMetaObject($task->activity_id) : new stdClass();

		$metaKey = "";
		$metaValue = "";

		if($metaValue = $activityMeta->isr_id) {
			$metaKey = "isr_id";
		}
		else if ($metaValue = $activityMeta->wcr_id) {
			$metaKey = "wcr_id";
		}

		if($taskID && $err = $this->verifyTask($task)) {
			$this->doAjaxError($err, "html");
		}

		switch ($task->type) {
			case 'gn_Task':
				include($this->htmlDir."_frm.gnTask.php");
				exit();
				break;

			case 'gn_SupportRequestTask':
				include($this->htmlDir."_frm.gnSupportRequest.php");
				exit();
				break;

			default:
				include($this->htmlDir."_frm.gnTask.php");
				exit();

		}

	}

	private function verifyTask ($task) {
		$err = "";
		if(!$task) {
			$err = "Task not found.";
		}
		else if(!$this->userCanViewTask($task)) {
			$err = "You don't have permission to view this task.";
		}

		else if ($task->isDeleted()) {
			$err = "Task is deleted.";
		}

		return $err;
	}

	function ajaxGetStatusOptions () {
		$this->writeStatusOptions($_GET['type']);
		exit();
	}

	private function writeStatusOptions ($taskType, $selectedValue = "") {
		if(class_exists($taskType)) {
			foreach ($taskType::$states as $str) {
				$text = $this->htmlEncode($str);
				$selected = ($str == $selectedValue) ? " selected='selected'" : "";
				echo('<option value="'.$text.'"'.$selected.'>'.$text.'</option>');
			}
		}
	}

	function ajaxCreateActivity () {
		$this->doAuthorityCheck();

		$activityType = $_POST['type'];

		switch($activityType) {
			case "gn_Activity":
				$this->createGenericActivity();
				break;

			default:
				$this->doAjaxError("Unknown activity.");
		}
	}

	function writeCategoryOptions ($task) {
		foreach($this->taskCategories as $key=>$value) {
			if($key == "support") continue;
			$selected = $task->category == $key ? " selected='selected'" : "";
			echo("<option value='$key'$selected>$value</option>");
		}
	}



	function createCurrentUserTask () {
		global $current_user;
		get_currentuserinfo();

		$task = new gn_Task();
		$task->owner = $current_user->user_login;
		$task->owner_id = $current_user->ID;

		return $task;

	}

	function createCurrentUserActivity () {
		global $current_user;
		get_currentuserinfo();

		$activity = new gn_Activity();
		$activity->creator = $current_user->user_login;
		$activity->creaor_id = $current_user->ID;

		return $activity;

	}

	function addCurrentUserTask ($task) {
		$activity = new gn_Activity();

		$activity->creator = $task->owner;
		$activity->creator_id = $task->owner_id;

		$activity->addTask($task);

		$activity->title = $task->title;

		$this->db->addActivity($activity);

	}



	function createWorkflow ($class, $activityID, $title) {
		global $current_user;
		get_currentuserinfo();

		$wf = new $class(
			$activityID,
			array("title"=>$title, "creator"=>$current_user->user_login, "creator_id"=>$current_user->ID)
		);

		$this->db->addActivity($wf);
	}



	function delegateTarget($id) {
		$role =$this->get_wp_user_role($id);

		return (($role == "ccnx_administrator") || ($role == "ccnx_tech_support") || ($role == "ccnx_tech_cuspport_district"));


	}

		function getAdminUserInfo ($ccnx_id) {

			$user_id = $this->dataInterface->data->getDBID("user_assignment", "id", $ccnx_id, "user_id");

			$user = get_userdata($user_id);

			$role =$this->get_wp_user_role($user_id);

			if ($this->delegateTarget($user_id)) {
				return ($user);
			}
			else {
				return(false);
			}

	}
	function getAdminInfo ($id) {

		$user = get_userdata($id);

		$role =$this->get_wp_user_role($id);

		if ($this->delegateTarget($id)) {
			return ($user);
		}
		else {
			return(false);
		}

	}


	private function createGenericActivity () {
		global $current_user;
		get_currentuserinfo();

		$activity = new gn_Activity();
		$task = $this->createTaskFromPostData (gn_Activity::$initialTask);

		$activity->creator = $current_user->user_login;
		$activity->creator_id = $current_user->ID;

		if(strlen(trim($_POST['meta_key'])) && strlen(trim($_POST['meta_value']))) {
			$activity->setMetaValue($_POST['meta_key'], $_POST['meta_value']);
		}

		$task->owner = $current_user->user_login;
		$task->owner_id = $current_user->ID;

		$activity->addTask($task);

		$activity->title = $task->title;

		try {
			$this->db->addActivity($activity);
		}
		catch (Exception $e) {
			$this->doAjaxError($e->getMessage());
		}

		$this->doAjaxSuccess();

	}

	private function createTaskFromPostData ($taskType) {
		foreach($taskType::$editFields as $key) {
			$data[$key] = trim($_POST[$key]);
		}
		$task = new $taskType($data);
		return $task;
	}

	function ajaxSetTaskCompletion () {
		$this->doAuthorityCheck();

		$dbTask = $this->db->getTaskByID($_POST["taskID"]);

		if($err = $this->verifyTask($dbTask)) {
			$this->doAjaxError($err);
		}

		$newStatus = intval($_POST['state']) ? $dbTask::$completeState : $dbTask::$initialState;

		try {
			$this->db->updateTaskStatus($dbTask, $newStatus);
		}
		catch (Exception $e) {
			$this->doAjaxError($e->getMessage());
		}

		$this->doAjaxSuccess();
	}

	function ajaxUpdateTask () {
		$this->doAuthorityCheck();

		$dbTask = $this->db->getTaskByID($_POST["id"]);

		if($err = $this->verifyTask($dbTask)) {
			$this->doAjaxError($err);
		}

		$ccnxAction = $dbTask->getMetaValue("ccnx_data_action");

		if($ccnxAction == "support-request") {
			$this->updateSupportTask($dbTask);
			return;
		}

		$newTask = $this->createTaskFromPostData($dbTask->type);

		try {
			$this->db->updateTask($dbTask, $newTask);
		}
		catch (Exception $e) {
			$this->doAjaxError($e->getMessage());
		}

		$this->doAjaxSuccess();
	}




	function ajaxDeleteTask () {
		$this->doAuthorityCheck();

		$dbTask = $this->db->getTaskByID($_POST["taskID"]);

		if($err = $this->verifyTask($dbTask)) {
			$this->doAjaxError($err);
		}

		try {
			$this->db->deleteTask($dbTask);
		}
		catch (Exception $e) {
			$this->doAjaxError($e->getMessage());
		}

		$this->doAjaxSuccess();

	}

	function doJSONResponse ($data) {
		header("Content-Type: application/json");
		echo(json_encode($data));
		exit();
	}


	function doAuthorityCheck () {
		$authorized = wp_verify_nonce($_POST['nonce'], $this->nonceString);

		if(!$authorized) {
			$this->doAjaxError("Permission denied.");
		}
	}

	function userCanEditTask ($task) {
		global $current_user;
		get_currentuserinfo();

		$role=$this->wp_user_role();

		return (($current_user->ID == $task->owner_id) || ($role =="ccnx_administrator") || ($role =="ccnx_tech_support") || ($role =="ccnx_tech_support_district") ) ? true : false;
	}

	function userCanViewTask ($task) {
		$role=$this->wp_user_role();

		if ($this->userCanEditTask ($task))
			return(true);
		else if (($role =="ccnx_pm")) {
			return (true);
		}

	}

	function doAjaxError ($msg, $format="json") {
		if($format == "json") {
			$this->doJSONResponse(
				array(
					"status"=>"Error",
					"message"=>"Error: $msg",
					"nonce"=>$this->nonce
				)
			);
		}
		else if($format == "html") {
			die("<p>$msg</p>");
		}
		else {
			die($msg);
		}
	}

	function doAjaxSuccess () {
		$this->doJSONResponse(
			array(
				"status"=>"OK",
				"nonce"=>$this->nonce
			)
		);
	}

	private function writeEncodedValue ($value) {
		echo($this->htmlEncode($value));
	}

	function writeBoolean ($int) {
		$value = $int == 1 ? "Yes" : "No";
		echo($value);
	}

	function writeTriState ($int) {
		if ($int==0) {
			echo("Unknown");
		}
		else {
			$this->writeBoolean($int);
		}
	}


	function writeResultScroller ($totalRows, $recordsPerPage, $currentOffset, $offsetParamName){


		ob_start();

		$url ="scroll/";

		$totalRows=  $totalRows ? $totalRows : $this->foundRows;

		$numPages = $this->roundUp($totalRows / $recordsPerPage);
		$lastOffset = ($numPages-1)*$recordsPerPage;
		$onFirstPage = ($currentOffset == 0) ? true : false;
		$onLastPage = ($currentOffset == $lastOffset) ? true : false;
		$currentPage = ($currentOffset/$recordsPerPage + 1);


		$this->writeOutput("<div class='taskscroller'>");
		$this->writeScrollerItem("<<",  $this->appendParam($url, $offsetParamName, 0), !$onFirstPage, "First page...");
		$this->writeOutput("&#160;");
		$this->writeScrollerItem("<",  $this->appendParam($url, $offsetParamName, $currentOffset-$recordsPerPage), !$onFirstPage, "Previous page...");
		$this->writeOutput("&#160;");

		$min = max(0, $currentPage-10);
		$max = min($numPages, $currentPage+10);

		for($i=$min; $i<$max; ++$i){
			$this->writeScrollerItem($i+1, $this->appendParam($url, $offsetParamName, $i*$recordsPerPage), !($i+1==$currentPage));
			$this->writeOutput("&#160;");
		}

		$this->writeScrollerItem(">", $this->appendParam($url, $offsetParamName, $currentOffset+$recordsPerPage), !$onLastPage, "Next page...");
		$this->writeOutput("&#160;");
		$this->writeScrollerItem(">>", $this->appendParam($url, $offsetParamName, $lastOffset), !$onLastPage, "Last page...");

		$this->writeOutput("</div>\n");

		return ob_get_clean();
	}


	function writeScrollerItem($text, $url, $active, $titleText=""){

		if($active) $this->writeScrollerLink($text, $url, $titleText);
		else $this->writeOutput("<span class='inactive'>".$this->htmlEncode($text)."</span>");
	}

	function writeScrollerLink($text, $url, $titleText){

		$this->writeOutput("<a class='list-scroller-page' ");
		if($titleText) $this->writeOutput("title='".$this->htmlEncode($titleText)."' ");
		$this->writeOutput("href='".$url."'>".$this->htmlEncode($text)."</a>");
	}

	/*** CCNX-specific
	
	function writeSupportTasks () {

		$offset = $_GET['offset']? $_GET['offset']:0;

		$sortBy = $this->sortKeys[$_GET['sortby']];
		$typeFilter = $type ? $type : $_GET['type'];
		$districtFilter =  $_GET['district_id'];
		$subTypeFilter =  $_GET['subtype'];
		$showComplete = $_GET["showcomplete"] ? true : false;

		$anchorClass="task-edit";

		if ($this->wp_user_role()=="ccnx_pm") {
			$anchorClass="task-view";
		}


		$tasks = $this->db->getSupportTasks($districtFilter,$subTypeFilter, $sortBy, $showComplete, $ownerId, $offset, $this->recordLimit );



		$this->foundRows = $this->db->foundRows; //$this->db->getSupportTaskCount($districtFilter,$subTypeFilter, $sortBy, $showComplete);


		$header="<table>";

		$header.= "<tr><th>Request</th><th>Date</th><th>Type</th><th>Owner</th><th>District</th><th>From</th><th>Status</th></tr>";

		foreach($tasks as $task) {
			$url = $task->url ? $task->url."?task_id=".$task->id : "#";
			$class = $this->getTaskCSSClass($task);
			$checked = $task->completion_date ? "checked='checked' " : "";

			$title =$task->title;

			$title=preg_replace('/Support request: /i',"",$title);

			$html .= "<tr$class>";
			$html .= "<td><a class='$anchorClass' href='$url' id='t".$this->htmlEncode($task->id)."'>".$this->htmlEncode($title)."</a></td>";

			$html .= "<td>".$this->htmlEncode($task->start_date)."</td>";
			$html .= "<td>$task->subtype</td>";
			$html .= "<td>$task->owner</td>";
			$html .= "<td>$task->district</td>";

			$html .= "<td>$task->creator</td>";
			$html .= "<td>".$this->htmlEncode($task->status)."</td>";
			$html .= "</tr>";
		}

		$html.="</table>";



		$html.=$this->writeFooter();

		echo($html ? $header.$html : "<tr><td>[No requests]</td></tr>");
	}

	function writeISRTasks ($isrID) {
		$this->writeMetaFilteredTasks("isr_id", $isrID);
	}

	function writeWCRTasks ($wcrID) {
		$this->writeMetaFilteredTasks("wcr_id", $wcrID);
	}

	function writeReviewTasks () {
			$tasks = $this->db->getCurrentReviewTasks();
			ob_start();
			if(!$tasks) {
				echo("No current tasks.");
			}
			else {
				foreach($tasks as $task) {
					echo("<tr><td>".$this->htmlEncode($task->title)."</td><td>".$this->htmlEncode($task->due_date)."</td></tr>");
				}
			}
			return ob_get_clean();
	}

	function getSupportTaskData($taskID) {
		$taskMeta = $this->db->getSupportTaskData($taskID);
		$data = array();

		foreach($taskMeta as $metaField) {
			$data[$metaField->meta_key] = $metaField->meta_value;
			$data['creator_id'] = $metaField->creator_id;
			$data['creator'] = $metaField->creator;
		}

		return $data;
	}
	function createISRWorkflow ($isrData) { // throws Exception

		global $current_user;
		get_currentuserinfo();

		$isrID = $isrData['id'];
		$isrName = $isrData['name'];

		$isr = new gn_ISRWorkflow(
			$isrID,
			array("title"=>"ISR Workflow", "creator"=>$current_user->user_login, "creator_id"=>$current_user->ID)
		);

		foreach($isr->getTasks() as $task) {
			$task->title = "ISR $isrName:".$task->title;
			$task->url = "/reviews/isrs/isr/?id=$isrID";
		}

		$this->db->addActivity($isr);


	}

	function ccnxObjectUpdate ($name, $object, $postData) {

		switch ($name) {

			case "service_referral":
				$this->serviceReferralUpdate($object, $postData);
				break;

			case "wcr":
				$this->ccnxWCRUpdate($object, $postData);
				break;

			case "wcr_student_review":
				$this->ccnxWCRStudentReviewUpdate($object, $postData);
				break;

			// isr forms:
			case "isr_referral":
			case "isr_previous_interventions":
			case "isr_ist_meeting":

				$this->ccnxISRUpdate($name, $object, $postData);
				break;

			case "isr_isp":
				$this->ccnxISRISPUPdate($object, $postData);
				break;
		}

	}

	function ccnxObjectCreate ($name, $object, $postData) {
		if($name=="service_referral") {
			$this->serviceReferralUpdate ($object, $postData);
		}
	}

	function serviceReferralUpdate ($object, $postData) {
		$task = $this->db->getServiceReferralReminder($object["id"]);
		$flag = $object["followup_flag"] ? true : false;

		if($flag && !$task) {
			$this->createServiceReferralReminder($postData);
		}
		else if (!$flag && $task) {
			$this->db->deleteTask($task);
		}
	}

	function claimByAction ($task) {

		global $current_user;
		$user_id = $current_user->id;

		if(!$owner = $this->getAdminInfo ($user_id)){
					$this->doAjaxError("Administrator not found.");
					return;
		}
				$task->owner = $owner->user_login;
				$task->owner_id = $owner->ID;
		try {
					$this->db->saveTask($task);
				}
				catch (Exception $e) {
					$this->doAjaxError($e->getMessage());
		}

		return ($task);

	}

	function updateSupportTask ($task) {
		$adminAction = $_POST['admin-action'];

		if($adminAction == "delegate") {
			if(!$owner = $this->getAdminUserInfo ($_POST['admin_id'])){
				$this->doAjaxError("Administrator not found.");
				return;
			}

			$task->owner = $owner->user_login;
			$task->owner_id = $owner->ID;
		}

		else if ($adminAction == "deny") {
			$task = $this->claimByAction($task);
			$task->setStatus("Denied");
			$task->completion_date = date("Y-m-d");
			$task->setMetaValue("deny-reason", $_POST['deny-reason']);
		}
		else if ($adminAction == "set-status") {
			$task = $this->claimByAction($task);
			$task->setStatus($_POST['status']);
			$task->setMetaValue("resolution", $_POST['resolution']);
		}
		else if ($adminAction == "complete") {
			$task->markComplete();
			$task->completion_date = date("Y-m-d");
			$this->db->saveTask($task);

		}

		try {
			$this->db->saveTask($task);
		}
		catch (Exception $e) {
			$this->doAjaxError($e->getMessage());
		}

		$this->doJSONResponse(
			array(
				"status"=>"Confirm",
				"message"=>"Task updated",
				"nonce"=>$this->nonce
			)
		);
	}

	function supportRequestProcessed ($taskID) {
		$task = $this->db->getTaskByID($taskID);

		if($err = $this->verifyTask($task)) {
			throw new Exception($err);
		}

		$this->claimByAction ($task);



		$task->markComplete();
		$task->completion_date = date("Y-m-d");

		$this->db->saveTask($task);
	}
	function createServiceReferralReminder ($postData) {
		global $ccnx_DataInterface;
		$student = $ccnx_DataInterface->data->getCCNXObject($postData["student_id"], "student");
		$partner = $ccnx_DataInterface->data->getCCNXObject($postData["partner_id"], "partner");
		$service = $ccnx_DataInterface->data->getCCNXObject($postData["service_id"], "service");

		$studentName = $student["firstname"]. " " . $student["lastname"];
		$partnerName = $partner["name"];
		$serviceName = $service["name"];

		$title = "Service Referral Followup: $studentName, ".($partnerName ? $partnerName : $serviceName);
		$today = date('Y-m-d');
		$dueDate = date_format(date_create('+2 weeks'), 'Y-m-d');

		$task = $this->createCurrentUserTask();

		$task->title = $title;
		$task->start_date = $today;
		$task->due_date = $dueDate;

		$task->setMetaValue("service_referral_id", $postData["object_id"]);

		$activity = $this->createCurrentUserActivity();
		$activity->title = $task->title;

		$activity->setMetaValue("service_referral_id", $postData["object_id"]);

		$activity->addTask($task);

		$this->db->addActivity($activity);
	}

	function ccnxWCRUpdate($object, $postData) {
		global $ccnx_DataInterface;


		$taskComplete = false;
		$nextState = "";

		switch ($object["wf_status"]) {

			case "start":
				if ($object["meeting_complete"]) {

					$taskComplete = true;
					$nextState = "meeting-complete";
				}
				break;

			case "meeting-complete":

				break;
		}

		if ($taskComplete) {

			$task = $this->db->getWCRTask($object["id"], $object["wf_status"]);

			$this->completeCCNXWorkflowTask("wcr", $object["id"], $object["wf_status"], $nextState);

		}
	}

	function ccnxWCRStudentReviewUpdate ($object, $postData) {

		global $ccnx_DataInterface;

		// $object is a wcr student review. we need its parent wcr.
		$wcr = $ccnx_DataInterface->data->getCCNXObject($object["wcr_id"], "wcr");

		if(!wcr) {
			$ccnx_DataInterface->GlobalMSG.="<br/>Error: WCR for student review not found.";
			return;
		}

		if($wcr["wf_status"]=="meeting-complete") {

			if(!$wcrStatus = $ccnx_DataInterface->data->getCCNXObject($wcr['id'],"wcr_status")){
				$ccnx_DataInterface->GlobalMSG.="<br/>Error: unable to determine wcr status.";
				return;
			}

			if($wcrStatus && !intval($wcrStatus["Incomplete"])) {
				$this->completeCCNXWorkflowTask("wcr", $wcr["id"], $wcr["wf_status"], "data-entered");
			}
			else {
				$task = $this->db->getWorkflowTask ("wcr", $wcr["id"], $wcr["wf_status"]);
				$task->setStatus("In progress");
				try {

					$this->db->saveTask($task);
				}
				catch (Exception $e) {
					$ccnx_DataInterface->GlobalMSG.="<br/>Error updating worklow: ".$e->getMessage();
					return;
				}
			}
		}
	}

	function ccnxISRUpdate($form, $object, $postData) {

		global $ccnx_DataInterface;


		if($form == "isr_referral") {
			$isr = $ccnx_DataInterface->data->getCCNXObject($object["id"], "isr");
		}
		else {
		

			$isr = $ccnx_DataInterface->data->getCCNXObject($object["isr_id"], "isr");
		//}

		if(!$isr) {
			$ccnx_DataInterface->GlobalMSG.="<br/>Error: ISR  not found.";
			return;
		}

		$doUpdate = false;
		$newState = "";
		$taskWfKey = $isr["wf_status"];

		switch($form) {
				case "isr_referral":

					$taskWfKey = "start";
					if($isr["wf_status"]==$taskWfKey) {
						$newState = $isr["isr_type"] == 2 ? "isr-meeting-complete" : "referral-information-entered";
					}
					$doUpdate = true;
					break;

				case "isr_previous_interventions":
					$taskWfKey = "referral-information-entered";
					if($isr["wf_status"]==$taskWfKey) {
						$newState = "previous-interventions-entered";
					}
					$doUpdate = true;
					break;

				case "isr_ist_meeting":
					$taskWfKey = "previous-interventions-entered";
					if (intval($object["meeting_occurred"])) {
						$doUpdate = true;
					}
					if($isr["wf_status"]==$taskWfKey && $doUpdate) {
						$newState = "isr-meeting-complete";
					}

					break;

		}

		if($doUpdate) {
			$this->completeCCNXWorkflowTask("isr", $isr['id'], $taskWfKey, $newState);
		}
	}

	function ccnxISRISPUPdate ($object, $postData) {
		global $ccnx_DataInterface;


		$isr = $ccnx_DataInterface->data->getCCNXObject($object["isr_id"], "isr");

		if(!$isr) {
			$ccnx_DataInterface->GlobalMSG.="<br/>Error: ISR  not found.";
			return;
		}
		$ispStatus = $ccnx_DataInterface->data->getCCNXObject($object["id"], "isr_isp_status");

		if(!$ispStatus) {
			$ccnx_DataInterface->GlobalMSG.="<br/>Error: Unable to determine ISP status.";
			return;
		}

		$nextState = "isp-data-entered";

		if(intval($ispStatus["complete"])) {
			$this->completeCCNXWorkflowTask ("isr", $isr['id'], "isp-data-entered");
			$nextState = "outcomes-documented";
		}

		$this->completeCCNXWorkflowTask ("isr", $isr['id'], "isr-meeting-complete", $nextState);

	}



	function completeCCNXWorkflowTask ($type, $id, $wfKey, $nextState="") {
		global $ccnx_DataInterface;

		$task = $this->db->getWorkflowTask ($type, $id, $wfKey);
		if(!$task) {
			$ccnx_DataInterface->GlobalMSG.="<br/>Error: Workflow task not found.";
			return;
		}

		try {
			$task->markComplete();
			$this->db->saveTask($task);
		}
		catch (Exception $e) {
			$ccnx_DataInterface->GlobalMSG.="<br/>Error updating worklow: ".$e->getMessage();
			return;
		}

		if($nextState) {
			$this->updateCCNXWorkflowStatus ($id, $type, $nextState);
		}
	}

	function updateCCNXWorkflowStatus ($id, $type, $status) {

		global $ccnx_DataInterface;
		$wf_update= array (id=>$id, "wf_status" => $status);
		$ccnx_DataInterface->data->updateEdit($type, $wf_update, true);
		$ccnx_DataInterface->GlobalMSG.="<br/>Workflow updated.";

	}

	function createWCRWorkflow ($wcrData) { // throws Exception
		global $current_user;
		get_currentuserinfo();

		$wcrID = $wcrData['id'];
		$wcrName = $wcrData['name'];


		$wf = new gn_WCRWorkflow($wcrID, array("title"=>"WCR Workflow", "creator"=>$current_user->user_login, "creator_id"=>$current_user->ID));

		foreach($wf->getTasks() as $task) {
			$task->title = "WCR $wcrName:".$task->title;
			$task->url = "/reviews/wcrs/wcr/?id=$wcrID";
		}
		$this->db->addActivity($wf);
	}

	function createSupportRequestActivity ($userInfo) {

		global $current_user;
		get_currentuserinfo();

		$activity = new gn_SupportRequestActivity();
		$activity->creator = $userInfo['user_login'] ? $userInfo['user_login'] : $current_user->user_login;
		$activity->creator_id = $userInfo['user_id'] ? $userInfo['user_id'] : $current_user->ID;

		$subtype = $_POST['ccnx_form'];

		if($subtype=="partner" || $subtype=="service") {
			$subtype .= (intval($_POST['is_existing'])) ? "_edit" : "_add";
		}

		if(!$task= $this->createSupportRequestTask($userInfo, $subtype)) {
			return;
		}

		$activity->addTask($task);

		$this->db->addActivity($activity);


	}

	function createSupportRequestTask ($userInfo, $subtype) {

		if(!$requestInfo = $this->supportRequestInfo[$subtype]){
			return null;
		}


		$today = date("Y-m-d");

		$objectName = $_POST['object_name'] ? " '".$_POST['object_name']."'" : "";

		$taskData = array(
			"title"=>$requestInfo["title"].$objectName,
			"subtype"=>$requestInfo["subtype"],
			"start_date"=>$today,
			"due_date"=>$today,
			"status"=>"Pending"
		);

		if($requestInfo["url"]) $taskData["url"] = $requestInfo["url"];

		$task =  new gn_SupportRequestTask($taskData);

		foreach ($_POST as $key=>$value) {
			if($key == "service_ids" && is_array($_POST["service_ids"]) && count($_POST["service_ids"])) {
				$sql = "select name from ccnx_service where id in(". $this->dataInterface->data->quoteNumericList(implode(",", $_POST["service_ids"])) .")";
				$services = $this->dataInterface->data->get_results($sql);

				$serviceNames = array();

				foreach($services as $service) {
					$serviceNames[] = $service->name;
				}

				$task->setMetaValue ("services", implode("; ", $serviceNames));

			}
			else {
				$task->setMetaValue ($key, $value);
			}
		}

		$district_id = $userInfo['district_id']?$userInfo['district_id']:-1;
		$task->setMetaValue ("district_id", $district_id);

		return $task;

	}

	
	********/



}

endif;

?>