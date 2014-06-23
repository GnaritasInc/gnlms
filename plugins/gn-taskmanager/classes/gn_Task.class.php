<?php

if(!class_exists('gn_Task')):

class gn_Task {

	var $id=0;
	var $title="New Task";
	var $description;
	var $start_date;
	var $due_date;
	var $completion_date = null;
	var $status;
	var $active = 1;
	var $type;
	var $owner;
	var $owner_id;
	var $activity;
	var $activity_id;
	var $url;
	var $subtype;
	var $category = "general";

	public static $dbFields = array(
		"id",
		"title",
		"description",
		"start_date",
		"due_date",
		"completion_date",
		"status",
		"active",
		"type",
		"owner",
		"owner_id",
		"activity_id",
		"url",
		"subtype",
		"category"
	);

	protected $deleted = 0;

	protected $metaData = array();

	public static $metaFields = array();

	public static $editFields = array("title", "description", "start_date", "due_date", "status", "active", "completion_date", "category");

	public static $states = array("Not started", "In progress", "Waiting on someone else", "Completed", "Deferred");

	public static $completeState = "Completed";

	public static $inactiveState = "Deferred";

	public static $initialState = "Not started";

	public static $defaultDuration = "1 weeks";

	function __construct ($data=array()) {
		if ($data) {
			foreach ($this as $key=>$value) {
				$this->$key = is_array($data) ? $data[$key] : $data->$key;
			}
		}

		$this->type = get_class($this);

		if(!$this->status) {
			$this->status = self::$initialState;
		}

		$this->setStatus($this->status);
	}

	function setStatus ($status) {
		$this->status = $status;

		if($this->status == self::$completeState && !$this->completion_date) {
			$this->completion_date = date("Y-m-d");
		}
		else {
			$this->completion_date = null;
		}

		if ($this->status == self::$inactiveState) {
			$this->active = 0;
		}
		else {
			$this->active = 1;
		}

	}

	function isDeleted () {
			return $this->deleted;
	}

	function isComplete () {
		return ($this->status == self::$completeState) ? true : false;
	}

	function getMetaValue ($key) {
			return array_key_exists($key, $this->metaData) ?  $this->metaData[$key] : null;
	}

	function setMetaValue ($key, $value) {
		$this->metaData[$key] = $value;
	}

	function getMetaData () {
		return $this->metaData;
	}

	function markComplete() {
		$this->setStatus(self::$completeState);
	}

}

endif;

?>