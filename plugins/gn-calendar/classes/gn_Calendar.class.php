<?php

if(!class_exists('gn_Calendar')):


class gn_Calendar extends gn_WebInterface {


	var $db;
	var $GlobalMSG = "";
	var $homeDir;
	var $homeURL;
	var $jsURL;
	var $cssURL;
	var $jsVars;
	var $nonceString = "gncalendar-nonce";
	var $nonce;

	function __construct () {
		$this->db = new gn_CalendarDB();
		$this->db->parent = $this;
		$baseDir = trailingslashit(dirname(dirname(plugin_basename(__FILE__))));
		$this->homeDir = WP_PLUGIN_DIR."/$baseDir";
		$this->homeURL = WP_PLUGIN_URL."/$baseDir";
		$this->jsURL = $this->homeURL."js/";
		$this->cssURL = $this->homeURL."css/";

		$this->setHooks();

		add_shortcode('gncalendar', array(&$this, 'displayCalendar'));
		add_shortcode('gncalendarwidget', array(&$this, 'displayCalendarWidget'));
	}

	function install () {
		$this->db->insureTables();
	}




	function displayCalendar () {
		$content = "";

		if(is_user_logged_in()) {
			ob_start();
			$this->displayPage("calendar.php");
			$content = ob_get_clean();
		}

		else {
			$content = "<p><a href='/wp-login.php?redirect_to=".$_SERVER["REQUEST_URI"]."'>Log in</a> to see the calendar.</p>";
		}

		return $content;
	}

	function displayCalendarWidget ($atts) {
		shortcode_atts(array("fullcalendarurl"=>"/calendar/"), $atts);
		return "<div class='gn-calendar-widget' data-fullcalendarurl='".$atts['fullcalendarurl']."'></div>";
	}



	function doUpdateEvent() {
		$evt = $this->getEventFromPostData();

		$this->db->updateEvent($evt);

		header("Location: ".$this->appendParam($_SERVER["REQUEST_URI"], "view", "calendar"));
	}

	function doAuthorityCheck ($evt, $capability) {
		$authorized = wp_verify_nonce($_POST['nonce'], $this->nonceString);
		global $current_user;
		get_currentuserinfo();

		$authorized = $authorized && ($evt->creator_id==$current_user->ID ? true : false);

		if(!$authorized) {
			$this->doAjaxError("Permission denied.");
		}

	}

	function ajaxUpdateEvent () {

		if(!$dbEvent = $this->db->getEventByID($_POST['id'])) {
			$this->doAjaxError("Event not found.");
		}

		if($dbEvent->isDeleted()) {
			$this->doAjaxError("Event was deleted by another user.");
		}

		$this->doAuthorityCheck($dbEvent, "update_event");


		$updatedEvent = $this->getEventFromPostData();

		foreach (gn_CalendarEvent::$updateFields as $key) {
			$dbEvent->$key = $updatedEvent->$key;
		}

		$dbEvent->attendees = $updatedEvent->attendees;

		try {
			$this->db->updateEvent($dbEvent);
		}

		catch (Exception $e) {
			$this->doAjaxError($e->getMessage());
		}

		$this->doAjaxSuccess();

	}

	function ajaxDeleteEvent () {
		if(!$dbEvent = $this->db->getEventByID($_POST['id'])) {
			$this->doAjaxError("Event not found.");
		}

		$this->doAuthorityCheck($dbEvent, "delete_event");

		try {
			$this->db->deleteEvent($dbEvent->id);
		}
		catch (Exception $e) {
			$this->doAjaxError($e->getMessage());
		}

		$this->doAjaxSuccess();

	}

	function ajaxEventDragResize () {
		if(!$dbEvent = $this->db->getEventByID($_POST['id'])) {
			$this->doAjaxError("Event not found.");
		}

		if($dbEvent->isDeleted()) {
			$this->doAjaxError("Event was deleted by another user.");
		}

		$this->doAuthorityCheck($dbEvent, "update_event");

		try {
			$this->db->updateEventTime($_POST['id'], $_POST['start'], $_POST['end'], $_POST['allDay']);
		}
		catch (Exception $e) {
			$this->doAjaxError($e->getMessage());
		}

		$this->doAjaxSuccess();
	}

	function doAddEvent() {

		$evt = $this->getEventFromPostData();

		$this->db->addEvent($evt);

		header("Location: ".$this->appendParam($_SERVER["REQUEST_URI"], "view", "calendar"));
	}

	function ajaxAddEvent () {
		$evt = $this->getEventFromPostData();
		try {
			$this->db->addEvent($evt);
		}
		catch (Exception $e){
			$this->doAjaxError($e->getMessage());
		}

		$this->doAjaxSuccess();
	}

	function doAjaxError ($msg) {
		$this->doAjaxResponse(
			array(
				"status"=>"Error",
				"message"=>"Error: $msg",
				"nonce"=>$this->nonce
			)
		);
	}

	function doAjaxSuccess () {
		$this->doAjaxResponse(
			array(
				"status"=>"OK",
				"nonce"=>$this->nonce
			)
		);
	}

	function doAjaxResponse ($data) {
		header( "Content-Type: application/json" );
		echo(json_encode($data));
		exit();
	}

	private function getEventFromPostData () {
		$evt = new gn_CalendarEvent($_POST);
		$evt->all_day = $_POST['all_day'] ? 1 : 0;

		if(!$evt->creator) {
			global $current_user;
			get_currentuserinfo();
			$evt->creator = $current_user->user_login;
			$evt->owner = $current_user->user_login;
			$evt->creator_id = $current_user->ID;
			$evt->owner_id = $current_user->ID;
		}

		$evt->attendees = array();

		foreach($_POST['attendees'] as $item) {
			$attendeeData = explode(',',$item);
			$evt->attendees[] = new gn_CalendarAttendee(array("user_id"=>$attendeeData[0], "user_login"=>$attendeeData[1]));
		}

		return $evt;
	}

	private function displayPage ($filename) {
		include($this->homeDir."html/$filename");
	}

	private function setHooks () {
		add_action('init', array(&$this, 'registerScripts'));
		add_action('init', array(&$this, 'registerCSS'));
		add_action('wp_ajax_gncalendar-getevents', array(&$this, 'ajaxGetEvents'));
		add_action('wp_ajax_gncalendar-eventdragresize', array(&$this, 'ajaxEventDragResize'));
		add_action('wp_ajax_gncalendar-addevent', array(&$this, 'ajaxAddEvent'));
		add_action('wp_ajax_gncalendar-updateevent', array(&$this, 'ajaxUpdateEvent'));
		add_action('wp_ajax_gncalendar-deleteevent', array(&$this, 'ajaxDeleteEvent'));
		add_action('wp_ajax_gncalendar-getattendeeoptions', array(&$this, 'ajaxGetAttendeeOptions'));
		add_action('wp_ajax_gncalendar-getfilteredattendees', array(&$this, 'ajaxGetFilteredAttendees'));
	}

	function ajaxGetAttendeeOptions () {
		$attendees = $this->db->getAvailableAttendees($_GET['filter']);


		foreach($attendees as $attendee) {
			$val = "$attendee->user_id,$attendee->user_login";
			$name = $this->htmlEncode("$attendee->last_name, $attendee->first_name");
			echo("<option value='$val'>$name</option>");
		}

		exit();
	}

	function ajaxGetFilteredAttendees () {
		$attendees = $this->db->getFilteredAttendees($_GET['filter'], $_GET['term']);
		$this->doAjaxResponse($attendees);
	}

	function ajaxGetEvents () {

		$from = intval($_GET['start']);
		$to = intval($_GET['end']);

		$events = $this->db->getEvents($from, $to, false, $_GET['userid']);

		if(function_exists('apply_filters')) {
			$events = apply_filters('gncalendar_event_result', $events, $from, $to);
		}

		$this->doAjaxResponse($events);

	}

	function writeUserSelect () {
		global $current_user;
		get_currentuserinfo();

		$userRole = $this->get_wp_user_role($current_user->ID);
		if($userRole == "ccnx_pm") {
			$sscs = $this->db->getSSCData($current_user->ID);
			include($this->homeDir."html/_user-select.php");
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


function upgradeJquery ($our_version) {

      global $wp_scripts;

        if ( ( version_compare($our_version, $wp_scripts->registered['jquery']->ver) == 1) ) {
                wp_deregister_script('jquery');
                wp_register_script('jquery', $this->jsURL."jquery.js",false, $our_version);
        }

}




	function registerScripts () {

		$this->nonce = wp_create_nonce($this->nonceString);


		if (!is_admin()) {

			/*
			$this->upgradeJquery ("1.5.2") ;
			wp_enqueue_script('jquery', $this->jsURL."jquery.js");


			wp_enqueue_script('jquery-ui-custom', $this->jsURL."jquery-ui-1.8.7.custom.min.js");
			
			*/
			
			wp_enqueue_script('jquery-ui-gn', $this->jsURL."jquery-ui-gn.js", array("jquery", "jquery-ui-dialog", "jquery-ui-autocomplete"));

			wp_enqueue_script('fullcalendar', $this->jsURL."fullcalendar.min.js", array("jquery", "jquery-ui-draggable", "jquery-ui-resizable"));
			wp_enqueue_script('time-picker', $this->jsURL."jquery.timePicker.min.js");
			wp_enqueue_script('gncalendar-display', $this->jsURL."calendar.js");
			wp_enqueue_script('gncalendar-form', $this->jsURL."form.js");


			global $current_user;
			get_currentuserinfo();

			$this->jsVars = array(
				"ajaxURL" => admin_url('admin-ajax.php'),
				"nonce" => $this->nonce,
				"fullCalendarURL" => "/calendar/"
			);

			$userData = array(
				"user_id"=>$current_user->ID,
				"user_login"=>$current_user->user_login,
				"first_name"=>$current_user->first_name,
				"last_name"=>$current_user->last_name
			);

			wp_localize_script('fullcalendar', "gn_Calendar", $this->jsVars);
			wp_localize_script('gncalendar-form', "gn_CalendarUser", $userData);

			}

	}

	function registerCSS () {
		wp_enqueue_style('fullcalendar', $this->cssURL."fullcalendar.css");

		// wp_enqueue_style('jquery-ui-custom', $this->cssURL."jquery-ui-1.8.7.custom.css");

		wp_enqueue_style('time-picker', $this->cssURL."timePicker.css");
		wp_enqueue_style('gn-calendar', $this->cssURL."gn-calendar.css");
	}

	private function writeConditionalAttribute ($condition, $name, $value='') {
		if(!$value) $value = $name;
		if($condition) {
			echo(" $name=\"$value\"");
		}
	}
}



endif;

?>