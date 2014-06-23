<?php

if(!class_exists('gn_Activity')):

class gn_Activity {

	var $id=0;
	var $title="Generic activity";
	var $type;
	var $active = 1;
	var $completion_date = null;
	var $creator;
	var $creator_id;
	var $tasks = array();
	var $url;

	protected $deleted = 0;

	protected $metaData = array();

	public static $initialTask = "gn_Task";

	public static $metaFields = array();


	function __construct ($data=array()) {
		if($data) {
			foreach ($this as $key=>$value) {
				$this->$key = is_array($data) ? $data[$key] : $data->$key;
			}
		}

		$this->type = get_class($this);

	}

	function addTask(&$task) {
		$this->tasks[] = $task;
		$task->activity =& $this;
	}

	function isDeleted () {
		return $this->deleted ? true : false;
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

	function getTasks() {
		return $this->tasks;
	}
}

endif;

?>