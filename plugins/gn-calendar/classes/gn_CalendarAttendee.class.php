<?php
if(!class_exists('gn_CalendarAttendee')):

class gn_CalendarAttendee {

	var $user_id;
	var $user_login;
	var $first_name;
	var $last_name;

	function __construct ($data=array()) {
		foreach ($this as $key=>$value) {
			$this->$key = is_array($data) ? $data[$key] : $data->$key;
		}
	}

}


endif;
?>