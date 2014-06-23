<?php

if(!class_exists('gn_TaskManagerDB')):

class gn_TaskManagerDB extends gn_PluginDB {

	var $foundRows = 0;

	/*
	private $activityFilters = array (
		"general"=>"gn_Activity",		
		"wcr"=>"gn_WCRWorkflow",
		"isr"=>"gn_ISRWorkflow",
		"support"=>"gn_SupportRequestActivity"
	);
	*/
	private $activityFilters = array (
		"general"=>"gn_Activity"
	);

	function __construct () {
		parent::__construct("gntm");
		$this->debug = false;
	}

	function initTableDefinitions () {
		$this->tableDefinitions = array (
			"activities" => "(
			  id int(10) unsigned not null auto_increment,
			  title text not null,
			  type varchar(45) not null,
			  active tinyint(3) unsigned not null default '1',
			  completion_date date default null,
			  creator varchar(60) not null,
			  creator_id int(10) unsigned not null,
			  created timestamp not null default '0000-00-00 00:00:00',
			  updated timestamp not null default current_timestamp on update current_timestamp,
			  deleted tinyint(3) unsigned not null default '0',
			  url varchar(100) DEFAULT NULL,
			  primary key (id)
			) engine=innodb;",


			"tasks" => "(
				id int(10) unsigned not null auto_increment,
				title text not null,
				description text,
				start_date date default null,
				due_date date default null,
				completion_date date default null,
				status varchar(45) not null default 'not started',
				active tinyint(3) unsigned not null default '1',
				type varchar(45) not null,
				subtype varchar(45) not null default '',
				owner varchar(60) default 'unassigned',
				owner_id int(10) unsigned not null default '0',
				activity_id int(10) unsigned not null,
				created timestamp not null default '0000-00-00 00:00:00',
				updated timestamp not null default current_timestamp on update current_timestamp,
				deleted tinyint(3) unsigned not null default '0',
				url varchar(100) default null,
				category varchar(45) default null,
				primary key (id),
				constraint #task_activity# foreign key (activity_id) references #activities# (id) on delete cascade on update cascade
			) engine=innodb;",

			"task_meta" => "(
			  id int(10) unsigned not null auto_increment,
			  task_id int(10) unsigned not null,
			  meta_key varchar(50) not null,
			  meta_value text,
			  primary key (id),
			  unique key #meta_key# (task_id,meta_key),
			  constraint #task_meta_task# foreign key (task_id) references #tasks# (id) on delete cascade on update cascade
			) engine=innodb;",

			"task_update" => "(
			  id integer unsigned not null auto_increment,
			  task_id integer unsigned not null,
			  field varchar(60) not null,
			  old_value text not null,
			  new_value text not null,
			  user_name varchar(60) not null,
			  user_id integer unsigned not null,
			  change_date timestamp not null default 0,
			  primary key (id),
			  constraint #task_update_task# foreign key (task_id) references #tasks# (id) on delete cascade on update cascade
			) engine = innodb;",


			"activity_meta" => "(
			  id integer unsigned not null auto_increment,
			  activity_id integer unsigned not null,
			  meta_key varchar(50) not null,
			  meta_value text,
			  primary key (id),
			  unique key #meta_key# (activity_id,meta_key),
			  constraint #activity_meta_activity# foreign key (activity_id) references #activities# (id) on delete cascade on update cascade
			) engine = innodb;"

		);
	}

	function getCurrentTasks ($typeFilter="", $orderBy='t.due_date', $showComplete=false, $ownerID=null, $offset=0, $limit=null) {

		$orderBy= $orderBy ? $this->escapeColumnName($orderBy) : 't.due_date';
		$tasks = $this->tableName("tasks");
		$activities = $this->tableName("activities");
		$activityMeta = $this->tableName("activity_meta");


		if(!$ownerID) {
			global $current_user;
			get_currentuserinfo();
			$ownerID = $this->quoteInteger($current_user->ID);

		}


		$sql = "select sql_calc_found_rows t.* from $tasks t inner join $activities a on t.activity_id = a.id ";
		$sql .= "where ";
		$sql .= " t.deleted = 0";
		$sql .= " and t.active=1";
		$sql .= " and t.owner_id = $ownerID";
		$sql .= $typeFilter ? " and t.category = ".$this->quoteString($typeFilter) : "";
		$sql .= $showComplete ? "" : " and t.completion_date is null";


		$sql .= " order by  $orderBy";

		if($limit) {
			$sql .= " limit ".$this->quoteInteger($offset).", ".$this->quoteInteger($limit);
		}


		return $this->get_results($sql);
	}
	
	

	function getFoundRows () {
		return $this->db->get_var("select found_rows()");
	}



	function getFilterConditions ($filters) {
		$conditions = array();

		foreach($filters as $key=>$value) {
			$conditions[] = "$key=$value";
		}

		return implode (" and ", $conditions);
	}



	function getActivityMetaFilteredTasks ($key, $value) {
		$tasks = $this->tableName("tasks");
		$activityMeta = $this->tableName("activity_meta");

		$sql = "select t.* from $tasks t";
		$sql .= " inner join $activityMeta am on am.meta_key='$key' and am.meta_value='$value' and t.activity_id=am.activity_id";
		$sql .= " where t.deleted=0 and t.active=1 and t.completion_date is null";

		return $this->get_results($sql);

	}



	function getWorkflowTask ($type, $id, $workFlowKey) {

		$activityMetaKey = $type."_id";
		$activityMetaValue = $id;
		$taskMetaKey = $type."_wf";
		$taskMetaValue = $workFlowKey;


		$activities = $this->tableName("activities");
		$tasks = $this->tableName("tasks");

		$activityMeta = $this->tableName("activity_meta");
		$taskMeta = $this->tableName("task_meta");


		$sql = "select t.*";

		$sql .= " from $tasks t ";
		$sql .= " inner join $activities a on t.activity_id=a.id";
		$sql .= " inner join $activityMeta am on a.id=am.activity_id";
		$sql .= " inner join $taskMeta tm on t.id=tm.task_id";
		$sql .= " where am.meta_key=".$this->quoteString($activityMetaKey);
		$sql .= " and am.meta_value=".$this->quoteString($activityMetaValue);
		$sql .= " and tm.meta_key=".$this->quoteString($taskMetaKey);
		$sql .= " and tm.meta_value=".$this->quoteString($taskMetaValue);

		$result = $this->get_results($sql);

		if($result) {
			$taskType = $result[0]->type;
			return new $taskType($result[0]);
		}
		else return null;
	}

	function escapeColumnName ($str) {
		return preg_replace('/[^a-z_\.]/', "", $str);
	}



	function getTaskDueDates ($start, $end, $userID) {
		$activities = $this->tableName("activities");
		$tasks = $this->tableName("tasks");

		$sql = "select a.id as 'activity_id', t.title, max(t.due_date) as 'start', 1 as 'all_day', 'gn-task' as 'className', 0 as 'editable'";
		$sql .= " from $activities a inner join $tasks t on a.id = t.activity_id";
		$sql .= " where a.deleted =0 and a.active = 1 and a.completion_date is null";
		$sql .= " and t.deleted = 0";
		$sql .= " and t.owner_id = ".$this->quoteInteger($userID);
		$sql .= " group by a.id, a.title";
		$sql .= " having start between from_unixtime(".$this->quoteInteger($start).") and from_unixtime(".$this->quoteInteger($end).")";

		return $this->get_results($sql);
	}

	function addActivity ($activity) {
		$activities = $this->tableName("activities");

		$sql = "insert into $activities (title, type, creator, creator_id, created, updated) values (%s, %s, %s, %d, null, null)";

		$values = array($activity->title, $activity->type, $activity->creator, $activity->creator_id);

		$this->dbSafeExecute("start transaction");

		try {
			$this->dbSafeExecute($this->db->prepare($sql, $values));
			$activity->id = $this->db->insert_id;
			foreach($activity->tasks as $task) {
				$task->activity_id = $activity->id;
				$this->addTask($task);
			}


			$this->saveActivityMeta($activity);
		}

		catch (Exception $e) {
			$this->dbSafeExecute("rollback");
			throw($e);
			return;
		}

		$this->dbSafeExecute("commit");

	}

	function saveActivityMeta ($activity) {
		$this->saveMetaData($activity, $this->tableName("activity_meta"), "activity_id");
	}

	function addActivityMeta ($activityID, $key, $value) {
		$this->addMetaData($this->tableName("activity_meta"), "activity_id", $activityID, $key, $value);
	}


	function addTask($task) {
		$tasks = $this->tableName("tasks");


		$data = array();

		foreach($task::$dbFields as $key) {
			$value = $task->$key;
			if($key=="id") {
				continue;
			}
			else if(strlen(trim($value))) {

				$data[$key] = $this->quoteString($value);
			}
		}


		$data['created'] = 'null';
		$data['updated'] = 'null';
		$data['start_date']  = $data['start_date'] ? $data['start_date'] : $this->quoteString(date('Y-m-d'));
		$data['due_date']  = $data['due_date'] ? $data['due_date'] : $this->quoteString(date('Y-m-d'));


		$sql = "insert into $tasks (".implode(", ", array_keys($data)).") values (".implode(", ", $data).")";

		$this->dbSafeExecute($sql);

		$taskID = $this->db->insert_id;

		$task->id = $taskID;
		$this->saveTaskMeta($task);

	}

	function addTaskMeta ($taskID, $key, $value) { // stores an individual meta field
		$this->addMetaData($this->tableName("task_meta"), "task_id", $taskID, $key, $value);
	}

	function saveTaskMeta ($task) { // stores all meta fields
		$this->saveMetaData($task, $this->tableName("task_meta"), "task_id");
	}

	function saveMetaData ($obj, $table, $idCol) {
		$rows = array();

		if($meta = $obj->getMetaData()) {
			foreach($meta as $key=>$value){
				$vals = array($this->quoteInteger($obj->id), $this->quoteString($key), $this->quoteString($value));
				$rows[] = "(". implode(", ", $vals) .")";
			}
		}

		if(count($rows)) {
			$sql = "insert into $table ($idCol, meta_key, meta_value)";
			$sql .= " values ".implode(", ", $rows);
			$sql .= " on duplicate key update meta_value = values(meta_value)";

			$this->dbSafeExecute($sql);
		}
	}

	function addMetaData ($table, $idCol, $itemID, $key, $value) {
		$values = array($this->quoteInteger($itemID), $this->quoteString($key), $this->quoteString($value));

		$sql = "insert into $table ($idCol, meta_key, meta_value)";
		$sql .= " values(".implode(", ", $values).")";
		$sql .= " on duplicate key update meta_value = values(meta_value)";

		$this->dbSafeExecute($sql);

	}

	function getMetaData ($table, $idCol, $itemID) {
		$sql = "select * from $table where $idCol = ".$this->quoteInteger($itemID);
		$result = $this->get_results($sql);
		return $result ? $result : null;
	}

	function getActivityByID ($activityID) {
		$activities = $this->tableName("activities");
		$sql = "select * from $activities where id = ".$this->quoteInteger($activityID);

		$result = $this->get_results($sql);

		if ($result) {
			$activityType = $result[0]->type;
			$activity = new $activityType($result[0]);
			$tasks = $this->getActivityTasks($activityID);

			foreach($tasks as $task){
				$activity->addTask($task);
			}

			if($meta = $this->getActivityMeta($activityID)) {
				foreach($meta as $field) {
					 $key = $field->meta_key;
					 $value = $field->meta_value;

					$activity->setMetaValue ($key, $value);

				}
			}

			return $activity;
		}
		else return null;
	}

	function getActivityMeta ($activityID) {
		return $this->getMetaData($this->tableName("activity_meta"), "activity_id", $activityID);
	}

	function getActivityMetaObject ($activityID) {
		$data = array();
		if($meta = $this->getActivityMeta($activityID)){
			foreach($meta as $field) {
				$data[$field->meta_key] = $field->meta_value;
			}
		}

		return $data ? (object) $data : null;
	}

	function getActivityTasks ($activityID) {
		$tasks = array();
		$tasksTable = $this->tableName("tasks");

		$sql = "select * from $tasksTable where activity_id=".$this->quoteInteger($activityID);
		$sql .= " order by id";

		$result = $this->get_results($sql);

		if ($result) {
			foreach($result as $row) {
				$tasks[] =  new $row->type($row);
			}
		}

		return $tasks;
	}

	function getTaskByID ($taskID) {
		$tasks = $this->tableName("tasks");
		$sql = "select * from $tasks where id=".$this->quoteInteger($taskID);

		$result = $this->get_results($sql);

		if($result) {
			$taskType = $result[0]->type;
			$task = new $taskType($result[0]);

			if($meta = $this->getTaskMeta($taskID)){
				foreach($meta as $field) {
					 $key = $field->meta_key;
					 $value = $field->meta_value;

					$task->setMetaValue ($key, $value);
				}
			}


			return $task;
		}

		else return null;
	}

	function getTaskMeta ($taskID) {
		return $this->getMetaData($this->tableName("task_meta"), "task_id", $taskID);
	}

	function getSupportTaskData ($taskID) {
		$tasks = $this->tableName("tasks");
		$activities = $this->tableName("activities");
		$taskMeta = $this->tableName("task_meta");

		$sql = "select tm.*, a.creator, a.creator_id";
		$sql .= " from $tasks t inner join $activities a on t.activity_id=a.id";
		$sql .= " inner join $taskMeta tm on tm.task_id=t.id";
		$sql .= " where tm.task_id=".$this->quoteInteger($taskID);

		return $this->get_results($sql);
	}

	function getNextTask ($task) {
		$tasks = $this->tableName("tasks");

		$sql = "select * from $tasks";
		$sql .= " where activity_id = ".$this->quoteInteger($task->activity_id);
		$sql .= " and completion_date is null";
		$sql .= " and deleted=0";
		$sql .= " order by id limit 1";

		$result = $this->get_results($sql);

		return $result ? new $result[0]->type($result[0]) : null;

	}

	function activateNextTask ($task) {

		if($nextTask = $this->getNextTask ($task)) {
			$nextID = $nextTask->id;
			$taskType = $nextTask->type;
			$duration = $taskType::$defaultDuration;
			$start = $this->quoteString(date_format(date_create(), 'Y-m-d'));
			$due = $this->quoteString(date_format(date_create("+$duration"), 'Y-m-d'));
			$status = $this->quoteString($taskType::$initialState);


			$tasks = $this->tableName("tasks");
			$sql = "update $tasks set active=1, status=$status, start_date=$start, due_date=$due where id = $nextID";
			$this->dbSafeExecute($sql);
		}

	}

	function updateTaskStatus ($task, $newStatus) {

		$this->dbSafeExecute("start transaction");

		try {
			if($task->status != $newStatus) {
				$this->logTaskUpdate($task, "status", $newStatus);
			}
			$task->setStatus($newStatus);
			$this->saveTask($task);

		}
		catch (Exception $e) {
			$this->dbSafeExecute("rollback");
			throw($e);
			return;
		}

		$this->dbSafeExecute("commit");

	}

	function updateTask ($dbTask, $newTask) {

		$this->dbSafeExecute("start transaction");

		try {

			foreach($dbTask::$editFields as $key){
				if($newTask->$key != $dbTask->$key) {
					$this->logTaskUpdate($dbTask, $key, $newTask->$key);
				}

				if ($key == "status") {
					$dbTask->setStatus($newTask->$key);
				}
				else {
					$dbTask->$key = $newTask->$key;
				}
			}

			$this->saveTask($dbTask);

		}

		catch (Exception $e) {
			$this->dbSafeExecute("rollback");
			throw($e);
			return;
		}

		$this->dbSafeExecute("commit");

	}

	function saveTask ($task) {
		$tasks = $this->tableName("tasks");
		foreach($task::$editFields as $key){
			$func = ($key == "active") ? 'quoteInteger' : 'quoteString';
			$value = $task->$key === null ? "null" : $this->$func($task->$key);
			$updates[] = "$key=$value";
		}

		$sql = "update $tasks set ".implode(', ', $updates)." where id=".$this->quoteInteger($task->id);


		try {
			$this->dbSafeExecute($sql);
			if($task->isComplete()) {
				$this->activateNextTask($task);
			}
			$this->saveTaskMeta($task);
			$this->updateTaskActivity($task->activity_id);
		}
		catch (Exception $e) {
			throw($e);
			return;
		}


	}

	function deleteTask ($task) {
		$tasks = $this->tableName("tasks");

		$sql = "update $tasks set deleted=1 where id=".$this->quoteInteger($task->id);


		$this->dbSafeExecute("start transaction");

		try {
			$this->logTaskUpdate($task, "deleted", "1");
			$this->dbSafeExecute($sql);
			$this->activateNextTask($task);
			$this->updateActivityDeletionState($task->activity_id);
		}
		catch (Exception $e) {
			$this->dbSafeExecute("rollback");
			throw($e);
			return;
		}

		$this->dbSafeExecute("commit");

	}

	private function updateActivityDeletionState ($activityID) {
		$activities = $this->tableName("activities");
		$tasks = $this->tableName("tasks");

		$sql = "update $activities";
		$sql .= " set deleted = (select min(t.deleted) from $tasks t where t.activity_id=".$this->quoteInteger($activityID).")";
		$sql .= " where id = ".$this->quoteInteger($activityID);


		$this->dbSafeExecute($sql);

	}

	private function updateTaskActivity($activityID) {
		$activities = $this->tableName("activities");
		$tasks = $this->tableName("tasks");


		$sql = "update $activities";
		$sql .= " set completion_date = case (select count(*) from $tasks t where t.activity_id=".$this->quoteInteger($activityID)." and t.completion_date is null) when 0 then curdate() else null end, ";
		$sql .= " active = (select max(t.active) from $tasks t where t.activity_id=".$this->quoteInteger($activityID).")";
		$sql .= " where id = ".$this->quoteInteger($activityID);

		$this->dbSafeExecute($sql);
	}

	function logTaskUpdate ($task, $field, $newValue) {
		global $current_user;
		get_currentuserinfo();

		$taskUpdate = $this->tableName("task_update");

		$data = array (
			"task_id" => $this->quoteInteger($task->id),
			"field" => $this->quoteString($field),
			"old_value" => ($field == "deleted") ? $task->isDeleted() : $this->quoteString($task->$field),
			"new_value" => $this->quoteString($newValue),
			"user_name" => $this->quoteString($current_user->user_login),
			"user_id" => $this->quoteInteger($current_user->ID),
			"change_date" => "null"
		);

		$sql = "insert into $taskUpdate (".implode(', ', array_keys($data)).")";
		$sql .= " values (".implode(', ', $data).")";

		$this->dbSafeExecute($sql);

	}

	/*** CCNX-specific

	function getSupportTasks ($districtFilter, $subTypeFilter="", $orderBy='t.due_date', $showComplete=false, $ownerID=null, $offset= false, $recordLimit = false) {
		$sql = $this->getSupportTaskSQL ($districtFilter, $subTypeFilter, $orderBy, $showComplete, $ownerID, $offset, $recordLimit);
		$result = $this->get_results($sql);
		$this->foundRows = $this->getFoundRows();

		return $result;

	}

	function getCurrentReviewTasks () {
		global $current_user;
		get_currentuserinfo();
		$ownerID = $this->quoteInteger($current_user->ID);
		$tasks = $this->tableName("tasks");
		$activties = $this->tableName("activities");

		$sql = "select t.* from $tasks t inner join $activties a on t.activity_id = a.id";
		$sql .= " where ";
		$sql .= " t.deleted = 0";
		$sql .= " and t.active=1";
		$sql .= " and t.owner_id = $ownerID";
		$sql .= " and a.type in ('gn_ISRWorkflow', 'gn_WCRWorkflow')";
		$sql .= " order by t.completion_date, t.due_date";

		return $this->get_results($sql);


	}
	function getSupportTaskSQL ($districtFilter, $subTypeFilter="", $orderBy='t.due_date', $showComplete=false, $ownerID=null, $offset= false, $recordLimit = false) {

		$orderBy= $orderBy ? $this->escapeColumnName($orderBy) : 't.due_date';
		$tasks = $this->tableName("tasks");

		global $current_user;
		get_currentuserinfo();
		$ownerID = $this->quoteInteger($current_user->ID);
		$tasks = $this->tableName("tasks");
		$activities = $this->tableName("activities");

		$sql = "select sql_calc_found_rows t.*, a.creator, tm.meta_value as district_id, d.name as district";
		$sql .= " from $tasks t inner join $activities a on t.activity_id=a.id";
		$sql.=" left join ".$this->tableName("task_meta"). " tm on tm.task_id = t.id and tm.meta_key='district_id' ";
		$sql.=" left join ccnx_district d on d.id = tm.meta_value";


		$sql .= " where a.type = 'gn_SupportRequestActivity'";
		$sql .= $subTypeFilter ? " and t.subtype = ".$this->quoteString($subTypeFilter) : "";
		$sql .= ($districtFilter || $districtFilter=="0") ? " and tm.meta_value = ".$this->quoteString($districtFilter) : "";

		$sql .= $showComplete ? "" : " and t.completion_date is null";


		global $ccnx_DataInterface;

		if ($user_districts = $ccnx_DataInterface->data->userDistricts()) {

			$sql.=" and tm.meta_value in (".implode(",",$user_districts).") ";

		}

		if ($this->parent->get_wp_user_role($current_user->ID) == "ccnx_pm" ) {

			$creators = $ccnx_DataInterface->data->getSSC_WP_IDs($current_user->ID);
			$creators[] = $current_user->ID;

			$sql.= " and a.creator_id in (" . implode(",",$creators). ") ";

		}



		$sql .= " order by $orderBy";


		if ($recordLimit)
			$sql .= " limit ".$this->quoteInteger($offset).", ".$this->quoteInteger($recordLimit);

		return $sql;

	}


		function getSupportTaskCount($districtFilter, $subTypeFilter="", $orderBy='t.due_date', $showComplete=false, $ownerID=null) {

			$tasks = $this->tableName("tasks");

			global $current_user;
			get_currentuserinfo();
			$ownerID = $this->quoteInteger($current_user->ID);
			$tasks = $this->tableName("tasks");
			$activities = $this->tableName("activities");
			$sql = "select count(t.id) from $tasks t inner join $activities a on t.activity_id=a.id";

					$sql.=" left join ".$this->tableName("task_meta"). " tm on tm.task_id = t.id and tm.meta_key='district_id' ";
					$sql.=" left join ccnx_district d on d.id = tm.meta_value";

			$sql .= " where a.type = 'gn_SupportRequestActivity'";

		$sql .= $subTypeFilter ? " and t.subtype = ".$this->quoteString($subTypeFilter) : "";
		$sql .= ($districtFilter || $districtFilter=="0") ? " and tm.meta_value = ".$this->quoteString($districtFilter) : "";

			$sql .= $showComplete ? "" : " and t.completion_date is null";


			global $ccnx_DataInterface;

			if ($user_districts = $ccnx_DataInterface->data->userDistricts()) {

				$sql.=" and tm.meta_value in (".implode(",",$user_districts).") ";

			}


			if ($this->parent->get_wp_user_role($current_user->ID) == "ccnx_pm" ) {

				$creators = $ccnx_DataInterface->data->getSSC_WP_IDs($current_user->ID);
				$creators[] = $current_user->ID;

				$sql.= " and a.creator_id in (" . implode(",",$creators). ") ";

			}

			return $this->db->get_var($sql);

	}

	function getServiceReferralReminder ($srID) {
		global $current_user;
		get_currentuserinfo();
		$tasks = $this->tableName("tasks");
		$taskMeta = $this->tableName("task_meta");

		$sql = "select t.* from ";
		$sql .= " $tasks t inner join $taskMeta tm";
		$sql .= " on t.id=tm.task_id and tm.meta_key='service_referral_id' and tm.meta_value=".$this->quoteString($srID);
		$sql .= " where t.deleted = 0 and t.owner_id=".$this->quoteString($current_user->ID);

		$result = $this->get_results($sql);

		return $result ? new gn_Task($result[0]) : null;

	}

	function getISRTasks ($isrID) {
		return $this->getActivityMetaFilteredTasks ("isr_id", $isrID);
	}

	function getWCRTasks ($wcrID) {
		return $this->getActivityMetaFilteredTasks ("wcr_id", $wcrID);
	}	
	function getWCRTask ($wcrID, $wfKey) {
		return $this->getWorkflowTask("wcr", $wcrID, $wfKey);
	}

	function getISRTask ($isrID, $wfKey) {
		return $this->getWorkflowTask("isr", $isrID, $wfKey);
	}	
	***/



}

endif;

?>