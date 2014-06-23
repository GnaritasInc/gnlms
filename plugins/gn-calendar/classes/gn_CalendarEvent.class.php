<?php
if(!class_exists('gn_CalendarEvent')):


class gn_CalendarEvent {

	var $id;
	var $start;
	var $end;
	var $title;
	var $location;
	var $description;
	var $visibility;
	var $all_day = 0;
	var $owner;
	var $creator;
	var $owner_id;
	var $creator_id;
	var $attendees = array();
	var $editable = 1;

	private $deleted = 0;
	private $created = null;
	private $updated = null;

	static $intFields = array("id", "start", "end", "all_day", "deleted", "owner_id", "creator_id");
	static $updateFields = array("start", "end", "title", "location", "description", "visibility", "all_day");


	function __construct ($data=array()) {
		foreach ($this as $key=>$value) {
			$this->$key = is_array($data) ? $data[$key] : $data->$key;
		}
	}

	function getCreateDate () {
		return $this->created;
	}

	function getModifiedDate () {
		return $this->updated;
	}

	function isDeleted () {
		return $this-> deleted ? true : false;
	}

	function addAttendee (&$attendee) {
		if(!$this->attendees) {
			$this->attendees = array();
		}
		$this->attendees[$attendee->id] = $attendee;
	}

	function removeAttendee ($attendeeID) {
		if($this->attendees) {
			$attendee = $this->attendees[$attendeeID];
			unset($this->attendees[$attendeeID]);
			return $attendee;
		}
		else return null;
	}


}

endif;

?>