<?php
if(!class_exists('gn_CalendarDB')):


class gn_CalendarDB extends gn_PluginDB {

	function __construct () {
		parent::__construct("gncalendar");
		$this->debug = false;
	}

	function initTableDefinitions () {
		$this->tableDefinitions = array (

			"events" => "(
			  id int(10) unsigned not null auto_increment,
			  title text not null,
			  location text,
			  description text,
			  start bigint(20) not null,
			  end bigint(20) default null,
			  visibility varchar(45) not null,
			  all_day tinyint(3) unsigned not null default '0',
			  owner varchar(45) default null,
			  creator varchar(45) default null,
			  owner_id int(10) unsigned default null,
			  creator_id int(10) unsigned default null,
			  deleted tinyint(3) unsigned not null default '0',
			  created timestamp not null default '0000-00-00 00:00:00',
			  updated timestamp not null default current_timestamp on update current_timestamp,
			  primary key (id)
			) engine=innodb;",

			"event_attendee" => "(
			  event_id int(10) unsigned not null,
			  user_id int(10) unsigned not null,
			  user_name varchar(60) default null,
			  display_order int(10) unsigned default '0',
			  primary key (event_id,user_id),
			  constraint #fk_event_id# foreign key (event_id) references #events# (id) on delete cascade on update cascade
			) engine=innodb;"
		);
	}



	private function packageResult ($dbResult, $className='gn_CalendarEvent') {
		$myResult = array();
		foreach($dbResult as $row) {
			$myResult[] = new $className($row);
		}

		return $myResult;
	}

	private function packageEventResult ($dbResult) {
		$myResult = array();
		foreach($dbResult as $row) {
			$evt = new gn_CalendarEvent($row);
			$evt->attendees = $row->hasAttendees ? $this->getEventAttendees($evt->id) : array();
			$myResult[] = $evt;
		}

		return $myResult;
	}

	private function getEventQueryCols () {

		$eventAttendee = $this->tableName("event_attendee");
		$cols = array(
			"e.*",
			"(select max(event_id) from $eventAttendee where event_id = e.id) as 'hasAttendees'"
		);

		return $cols;
	}

	private function getAttendeeQueryCols () {
		$userMeta = $this->db->usermeta;
		$cols = array(
			"u.ID as 'user_id'",
			"u.user_login",
			"(select meta_value from $userMeta where user_id=u.ID and meta_key='first_name') as first_name",
			"(select meta_value from $userMeta where user_id=u.ID and meta_key='last_name') as last_name"
		);

		return $cols;
	}

	function getEvents ($start, $end, $includeDeleted=false, $userID=null) {
		global $current_user;
		get_currentuserinfo();


		$currentUserID = $this->quoteInteger($current_user->ID);
		$creatorFilter = " creator_id=$currentUserID";
		$userRole = $this->parent->get_wp_user_role($currentUserID);
		$publicFilter = $this->getPublicEventFilter($currentUserID, $userRole);

		if(intval($userID) && $userRole == "ccnx_pm") {
			$sscs = $this->getSSCIDs($currentUserID);

			if(in_array($userID, $sscs)) $creatorFilter = " creator_id=".$this->quoteInteger($userID);
		}


		$events = $this->tableName("events");

		$cols = $this->getEventQueryCols();

		if($userID && $userID != $currentUserID) {
			$cols[] = "0 as 'editable'";
		}

		$sql = "select ".implode(', ', $cols)." from $events e";
		$sql .= " where e.start >= ".$this->quoteInteger($start);
		$sql .= " and e.end <= ".$this->quoteInteger($end);
		if(!$includeDeleted) $sql .= " and e.deleted = 0";

		$sql .= " and ($publicFilter or $creatorFilter)";

		$dbResult = $this->get_results($sql);

		return $this->packageEventResult($dbResult);

	}

	function getPublicEventFilter ($userID, $userRole) {
		$filter = "visibility='public'";

		if($userDistricts = $this->getUserDistricts($userID, $userRole)) {
			$userDistricts = implode(", ", $userDistricts);
			$filter .= " and creator_id in (";
			$filter .= "select user_id from (". $this->getUserDistrictAssignemntSQL() .") t1 where district_id in ($userDistricts)";
			$filter .= ")";
		}

		return "($filter)";
	}

	function getUserDistricts ($userID, $userRole) {
		$userDistricts = array();
		global $ccnx_DataInterface;

		if($userRole == "ccnx_ssc" || $userRole == "ccnx_ssc_hs") {
			$userDistricts[] = $ccnx_DataInterface->userDistrict;
		}
		else if ($userRole=="ccnx_pm") {
			$ccnxUser_ID= $ccnx_DataInterface->data->getDBID("user_assignment", "user_id",$user_ID);
			$sql = "select district_id from ccnx_user_district_assignment where user_id=$ccnxUser_ID";
			$result = $this->get_results($sql);

			foreach($result as $row) {
				$userDistricts[] = $row->district_id;
			}
		}

		return $userDistricts;
	}

	function getUserDistrictAssignemntSQL () {
		$sql = " select user_id, district_id";
		$sql .= " from ccnx_user_assignment";
		$sql .= " where district_id is not null";
		$sql .= " union";
		$sql .= " select ua.user_id, uda.district_id";
		$sql .= " from ccnx_user_assignment ua";
		$sql .= " inner join ccnx_user_district_assignment uda on uda.user_id=ua.id";

		return $sql;
	}

	function getSSCIDs ($pmID) {
		global $ccnx_DataInterface;
		$sscs = $ccnx_DataInterface->data->getSSC_WP_IDs($pmID);
		return $sscs;
	}

	function getSSCData ($pmID) {
		global $ccnx_DataInterface;
		$sscs = $ccnx_DataInterface->data->getSSCSelectList($pmID);
		return $sscs;
	}

	private function getAttendeeSQL () {
		$users = $this->db->users;
		$eventAttendee = $this->tableName("event_attendee");
		$cols = $this->getAttendeeQueryCols();

		$sql = "select ".implode(', ', $cols);
		$sql .= " from $users u, $eventAttendee ea";
		$sql .= " where u.ID = ea.user_id";

		return $sql;

	}

	function getAvailableAttendees ($filterIDs) {
		$users = $this->db->users;
		$cols = $this->getAttendeeQueryCols();


		$sql =  "select ".implode(', ', $cols)." from $users u";
		$sql .= " where u.ID not in(".$this->quoteNumericList($filterIDs).")";
		$sql .= " order by last_name";



		return $this->packageResult($this->get_results($sql), 'gn_CalendarAttendee');

	}

	function getFilteredAttendees ($filterIDs, $filterName) {
		$users = $this->db->users;
		$userMeta = $this->db->usermeta;
		$namePattern = $this->quoteString("$filterName%");

		$cols = $this->getAttendeeQueryCols();

		$cols[] = "concat((select meta_value from $userMeta where user_id=u.ID and meta_key='last_name'), ', ', (select meta_value from $userMeta where user_id=u.ID and meta_key='first_name')) as 'value'";

		$sql = "select ".implode(', ', $cols);
		$sql .= " from $users u";
		$sql .= " where u.ID not in(".$this->quoteNumericList($filterIDs).")";
		$sql .= " having last_name like $namePattern or first_name like $namePattern";
		$sql .= " order by last_name limit 10";

		return $this->get_results($sql);
	}

	function getEventAttendees ($eventID) {
		$sql = $this->getAttendeeSQL();
		$sql .= " and ea.event_id = ".$this->quoteInteger($eventID);
		$sql .= " order by display_order";

		return $this->packageResult($this->get_results($sql), 'gn_CalendarAttendee');
	}

	function addEventAttendees ($eventID, $attendees) {
		$eventAttendee = $this->tableName("event_attendee");
		$quotedEventID = $this->quoteInteger($eventID);
		$this->dbSafeExecute("delete from $eventAttendee where event_id=$quotedEventID");

		foreach ($attendees as $i=>$attendee) {
			$userID = $this->quoteInteger($attendee->user_id);
			$userName = $this->quoteString($attendee->user_login);

			$sql = "insert into $eventAttendee (event_id, user_id, user_name, display_order) values ($quotedEventID, $userID, $userName, $i)";

			$this->dbSafeExecute($sql);
		}
	}

	function getEventByID ($eventID) {
		$cols = $this->getEventQueryCols();
		$sql = "select ".implode(', ', $cols)." from ".$this->tableName("events")." e where e.id = ".$this->quoteInteger($eventID);
		$result = $this->getRecord($sql);

		if($result) {
			 $evt = new gn_CalendarEvent($result);
			 $evt->attendees = $result->hasAttendees ? $this->getEventAttendees($evt->id) : array();

			 return $evt;
		}
		else return null;
	}

	function addEvent($event) {

		global $current_user;
		get_currentuserinfo();


		$cols = array("created", "updated", "creator", "creator_id", "owner", "owner_id");
		$values = array("null", "null", $this->quoteString($current_user->user_login), $this->quoteInteger($current_user->ID), $this->quoteString($current_user->user_login), $this->quoteInteger($current_user->ID));

		foreach(gn_CalendarEvent::$updateFields as $key){

			$cols[] = $key;
			$values[] = $this->quoteValue($key, $event->$key);

		}

		$sql = "insert into ". $this->tableName("events") ."(". implode(', ', $cols) .")";
		$sql .= " values (". implode(', ', $values) .")";

		$this->dbSafeExecute($sql);

		$this->addEventAttendees($this->db->insert_id, $event->attendees);
	}

	function updateEvent ($event) {
		$updates = array();

		foreach(gn_CalendarEvent::$updateFields as $key) {


			$value = $this->quoteValue($key, $event->$key);

			$updates[] = "$key=$value";
		}

		$sql = "update ". $this->tableName("events");
		$sql .= " set ".implode(", ", $updates);
		$sql .= " where id = ".$this->quoteInteger($event->id);

		$this->dbSafeExecute($sql);

		$this->addEventAttendees($event->id, $event->attendees);
	}

	function updateEventTime ($eventID, $start, $end, $allDay) {
		$tableName = $this->tableName("events");
		$sql = "update $tableName set start=%d, end=%d, all_day=%d where id=%d";

		$this->dbSafeExecute($this->db->prepare($sql, array($start, $end, $allDay, $eventID)));
	}

	function deleteEvent ($eventID) {
		$sql = "update ". $this->tableName("events");
		$sql .= " set deleted=1 where id = ".$this->quoteInteger($eventID);

		$this->dbSafeExecute($sql);
	}

	private function quoteValue ($key, $value) {
		if($value === null) {
			return "null";
		}
		else if (in_array($key, gn_CalendarEvent::$intFields)) {
			return $this->quoteInteger($value);
		}
		else {
			return $this->quoteString($value);
		}
	}

}


endif;
?>