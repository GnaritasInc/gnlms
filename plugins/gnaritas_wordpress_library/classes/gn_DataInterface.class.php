<?php
if(!class_exists("gn_DataInterfaceClass")):

class gn_DataInterfaceClass extends gn_WebInterface{

		var $importErrors = array();
		public static $MYSQL_DUPLICATE_KEY_ERROR = 1062;


/**
 *
 * @param
 * @return
 */

function __construct () {
			parent::init();


 			$baseDir = trailingslashit(dirname(dirname(plugin_basename(__FILE__))));
			$this->jsURL = WP_PLUGIN_URL."/$baseDir"."js/";

			$this->inheritedRecord = false;
			$this->adminActionKey ="gn_data_action";

			/*
			$this->gnData = new gn_DataClass();
			$this->data = $this->gnData;

			$this->gnData->container = $this;
			$this->gnData->dataInterface = $this;


			$this->dataImport = new gn_DataImportClass();
			$this->dataImport->parent = $this;
			$this->dataImport->data = $this->data;

			$this->dataExport = new gn_DataExportClass();
			$this->dataExport->parent = $this;
			$this->dataExport->data = $this->data;


			add_shortcode('gn_data_context', array(&$this, 'gn_data_context'));
			add_shortcode('gn_user_context', array(&$this, 'gn_user_context'));
			add_shortcode('gn_data_form', array(&$this, 'gn_data_form'));
			add_shortcode('gn_data_support_form', array(&$this, 'gn_data_support_form'));
			add_shortcode('gn_data_import', array(&$this, 'gn_data_import'));
			add_shortcode('gn_support_form', array(&$this, 'gn_support_form'));


			add_action ("init",  array(&$this, 'registerCCNXUserContext'));



			*/

						$this->registerScripts();
						$this->registerActions();


						$this->setAjaxHandlers();

						$this->initMessageAliases();

						$this->initWorkflows();


		}





/**  CCNX
 *
 * @param
 * @return
 */

function initMessageAliases() {

			$this->messageAliasArray = array();

		}


/** CCNX
 *
 * @param
 * @return
 */

function setAjaxHandlers() {

		add_action('wp_ajax_gn-ajax-update-edit', array(&$this, 'ajaxUpdateEdit'));
		add_action('wp_ajax_gn-ajax-update-edit-json', array(&$this, 'ajaxUpdateEditJSON'));
		add_action('wp_ajax_gn-ajax-autosave', array(&$this, 'ajaxAutoSave'));
		add_action('wp_ajax_gn-ajax-heartbeat', array(&$this, 'ajaxHeartBeat'));
		add_action('wp_ajax_gn-ajax-get-object-record', array(&$this, 'ajaxGetObjectRecord'));

}

/**
 *
 * @param
 * @return
 */

 function ajaxHeartBeat () {
 	echo("OK");
 	exit();
 }

/**
 *
 * @param
 * @return
 */

function registerActions() {

		/*
		add_action('wp_ajax_gn-getOptions', array(&$this, 'ajaxGetSelectOptions'));
		add_action('wp_ajax_gn-updateEdit', array(&$this, 'ajaxUpdateEdit'));
		add_action('wp_ajax_gn-deleteNote', array(&$this, 'ajaxDeleteNote'));

			add_action('gn_save_user_district_assignment', array(&$this, 'bulkUserDistrictAssign'));
			add_action('gn_save_user_school_assignment', array(&$this, 'bulkUserSchoolAssign'));

		*/

	}



/**
 *
 * @param
 * @return
 */

function registerScripts() {

		if (!is_admin()) {
			wp_enqueue_script('jquery');
			wp_enqueue_script('jquery-ui-custom', $this->jsURL."jquery-ui-1.8.7.custom.min.js");

			wp_enqueue_script('jquery.form.wizard', $this->jsURL."jquery.form.wizard.js");
			wp_enqueue_script('jquery.validate', $this->jsURL."jquery.validate.min.js");
			wp_enqueue_script('jquery.validate.additional', $this->jsURL."additional-methods.min.js");
			wp_enqueue_script('gn.forms', $this->jsURL."gn.forms.js");

		}
	}



/**
 *
 * @param
 * @return
 */

function controller () {

			$this->init_user_context();

			if (($action=$this->getPostValue($this->adminActionKey))!="") {
				$this->handlePost($action);

			}

		}






/**
 *
 * @param
 * @return
 */

function init_user_context() {




}




/**
 *
 * @param
 * @return
 */

function checkAccess () {


}




/**
 *
 * @param
 * @return
 */

function doExport () {
			$type = trim($_GET['gn-export-type']);

			$this->dataExport->doExport($type);
		}




/**
 *
 * @param
 * @return
 */

function gn_data_form ($atts, $content="", $code="")  {
			$isDataForm = true;
			ob_start();


			switch  ($name=$atts["name"]) {

				case ("form"):
					include ("forms/gn_data_form.php");
					break;

			}




			return ob_get_clean();

}







/**
 *
 * @param
 * @return
 */

function gn_data_support_form ($atts, $content, $code)  {

					ob_start();


					switch  ($name=$atts["name"]) {


						case ("partner_service"):
							include ("forms/gn_data_partner_service.php");
							break;

						case ("student_classroom_assignment"):
							include ("forms/gn_data_student_classroom_assignment.php");
							break;



						default:
							echo("Not implemented:".$name);
							break;
					}


					return ob_get_clean();

}


/**
 *
 * @param
 * @return
 */

function gn_data_import ($atts) {
			ob_start();

			include("forms/gn_data_import.php");


			return ob_get_clean();

}

// DS:

/**
 *
 * @param
 * @return
 */

function getObjectNotes ($tableKey, $recordID) {
			return $this->gnData->retrieveRecordNotes($tableKey, $recordID);
		}


/**
 *
 * @param
 * @return
 */

function getObjectRecord($id,$name) {
			$sql = $this->gnData->getRecordSQL($id,$name);

			$activeRecord = $this->gnData->getRecordArray($sql);

			$this->activeRecord = $activeRecord;

			return ($activeRecord);
		}

/**
 *
 * @param
 * @return
 */


 function ajaxGetObjectRecord () {
 	$id = $_GET['id'];
 	$name = $_GET['name'];

 	$record = $this->getObjectRecord($id, $name);

 	if($record['id']) {
 		$record['object_id'] = $record['id'];
 	}

	if($record['name']) {
 		$record['object_name'] = $record['name'];
 	}

 	$this->doAjaxResponse($record);

 }

/**
 *
 * @param
 * @return
 */

function gn_user_context() {
		$this->init_user_context();

				echo ("\n<script type='text/javascript'>");

				echo ("context_type='user';\n context={'user':".json_encode($this->context) ."};\n");

				if ($_GET["id"])
					echo ("\n$(document).ready(function () {gnInsureId('". $_GET["id"] ."');});");



				echo ("\n</script>");
}

function identify_gn_role() {
			$role = $this->gn_user_role();

			echo ("\n<script type='text/javascript'>");
			echo ("gn_role='$role';\n");
			echo ("\n</script>");

}



function gn_data_context ($atts) {

			ob_start();

			$name = $atts["name"];
			$id=$this->getQueryStringValue("id");
			if (!($id && $name)) {

			    if ($this->getQueryStringValue("contextName")) {

					if($name==$this->getQueryStringValue("contextName") ) {
						$id= $this->getQueryStringValue("contextId");
						$_GET["id"]=$id;
						$activeRecord = $this->getObjectRecord($id,$name);

					}
					else {
						$this->inheritedRecord = true;
						$id=$this->getQueryStringValue("contextId");
						$name=$this->getQueryStringValue("contextName");

						$activeRecord = $this->getObjectRecord($id,$name);
					}


			    }
			    else {
			    	//wp_redirect("../");
			    }
			}

			else {
				$activeRecord = $this->getObjectRecord($id,$name);
			}

				$this->activeRecord = $activeRecord;
				$this->gnData->contextRecord = $activeRecord;
				$this->gnData->contextType = $name;
				$this->contextType = $name;
				$this->contextId = $id;

				echo ("\n<script type='text/javascript'>");

				echo ("context_type='$name';\n context={".$name.":".json_encode($activeRecord) ."};");



				if ($_GET["id"])
					echo ("\n$(document).ready(function () {gnInsureId('". $_GET["id"] ."');});");



				echo ("\n</script>");



			//}

			$this->checkAccess ();

			$this->fixBreadCrumbs();

			$data=ob_get_clean();

			return ($data);

		}


/**
 *
 * @param
 * @return
 */

function fixBreadCrumbs() {
			$breadCrumbContext = array();

			$student_id = $this->contextStudent;
			$district_id = $this->contextDistrict;
			$classroom_id = $this->contextClassroom;
			$school_id = $this->contextSchool;

			$service_id = $this->contextService;
			$partner_id = $this->contextPartner;

			$group_id = $this->contextGroup;




				$breadCrumbContext["Student"] = $student_id;
				$breadCrumbContext["District"] = $district_id;
				$breadCrumbContext["Classroom"] = $classroom_id;

				$breadCrumbContext["Partner"] = $partner_id;
				$breadCrumbContext["Service"] = $service_id;

				$breadCrumbContext["Group"] = $group_id;

				$breadCrumbContext["School"] = $school_id;
				$breadCrumbContext["WCR"] = $this->getContextValue("wcr_id");



			 if ($student_id && $district_id) {
			 	$sca = $this->data->getCurrentStudentClassroomAssignment($student_id, $district_id);

			 	//print_r($sca);
					if ($sca["school_id"])
						$breadCrumbContext["School"] = $sca["school_id"];
					if ($sca["classroom_id"])
						$breadCrumbContext["Classroom"] = $sca["classroom_id"];
			}


		echo ("<script type='text/javascript'>appendProperBreadcrumbId(".json_encode($breadCrumbContext).");</script>");




		}





/**
 *
 * @param
 * @return
 */

function genericFormLoader($id, $name, $recordname) {

			//error_log("Form loader for: $id $name $recordname");

			if($name=='isr_isp') {
				$activeRecord = $this->data->retrieveISRISPGoals($id);
			}

			else {
				$activeRecord = $this->getObjectRecord($id,$recordname);
			}

			$this->addFormLoader($activeRecord,$name."_form");

		}


/**
 *
 * @param
 * @return
 */

function taskFormLoader ($taskID, $name) {
			global $gnTaskManager;
			$activeRecord = $gnTaskManager->getSupportTaskData($taskID);
			$activeRecord["gn_data_action"] = "update-edit";
			$activeRecord["support_request_task"] = $taskID;

			//$this->activeRecord = $activeRecord;
			$this->addFormLoader($activeRecord,$name."_form");
		}


/**
 *
 * @param
 * @return
 */

function genericUpdateEdit($name) {

			global $_POST;

			if ($this->getPostValue ("object_id")) {
				$_POST["id"] = $this->getPostValue ("object_id");
			}

			if ($this->getPostValue ("object_name")) {
				$_POST["name"] = $this->getPostValue ("object_name");
			}

			$sql = $this->gnData->getUpdateEditSQL($name, $_POST);

			// error_log("******* Update/edit sql: $sql");

			// DS: dbExecute (wrapper for wpdb->query) returns the
			// number of rows affected by the last operation, so could be zero.

			try {
				$return= $this->gnData->dbSafeExecute($sql);
			}
			catch (Exception $e) {
				$msg = $e->getMessage();

				if($e->getCode() == self::$MYSQL_DUPLICATE_KEY_ERROR){
					$msg = "Duplicate record. ($msg)";
				}

				$this->GlobalMSG.="Error: $msg";
				return;
			}

			if (!$_POST["id"] && !$_GET["id"]) {
				$id= $this->gnData->db->insert_id;

				//error_log("@@@@@ Setting id to: $id");
				$_GET["id"] = $id;
			}


			if ($return !== false) {
				if ($_POST["student_lastname"]) {

					$dataname = strtoupper($name). ($_POST["student_lastname"]?": ".$_POST["student_lastname"]:"");

				}
				else if ($_POST["gn_form"] == "isr_ist_meeting") {
					$dataname = "ISR MEETING";
				}
				else {
					$dataname = strtoupper($name). ($_POST["object_name"]?": ".$_POST["object_name"]:"");
				}

				if ($_POST["object_id"]) {
					$ccnxAction = "generic_update";
					$msg = $dataname." updated.";
				}
				else {
					$_POST["object_id"] = $this->gnData->getInsertID();

					$ccnxAction = "generic_create";
					$msg = $dataname." added.";

				}

				try {
					do_action("gn_data_event", $ccnxAction, $_POST);
				}
				catch (Exception $e) {
					$this->GlobalMSG.= "Error: ".$e->getMessage();
					return;
				}

				$this->GlobalMSG.= stripslashes($msg);
			}
			else {

				$this->GlobalMSG.="Update error/ or No Change";
			}
		}





/**
 *
 * @param
 * @return
 */

function list_wcr_meeting_options($wcr_id) {

		$wcr_fields = $this->data->getCCNXObject($wcr_id, "wcr");


		for ($i=1; $i<=3; $i++) {

			if ($wcr_fields["meeting_date".$i]) {

				echo ("<option>". $wcr_fields["meeting_date".$i]."</option>");
			}
		}


	}






/**
 *
 * @param
 * @return
 */

function genericRelationEdit($name) {

			global $_POST;


			if ($this->getPostValue ("object_id")) {

				$_POST["id"] = $this->getPostValue ("object_id");
			}
			if ($this->getPostValue ("object_name")) {
					$_POST["name"] = $this->getPostValue ("object_name");
			}


				$sqlArray = $this->gnData->getRelationEditSQL($name, $_POST);


				$this->data->dbExecute("start transaction");

				$return = true;

				foreach ($sqlArray as $sql) {
					 $this->gnData->dbExecute($sql);
				}

				if ($return) {
					$this->data->dbExecute("commit");
					$this->GlobalMSG.=strtoupper($name)." updated.";


				}
				else {
					$this->data->dbExecute("rollback");
					$this->GlobalMSG.="Update error/ No Change";
				}




}



/**
 *
 * @param
 * @return
 */

function retrieveListOptions ($type) {

			switch ($type) {
						case "grade":
							$sql = $this->data->getListSQL("grade")." order by id";
							$records = $this->data->get_results($sql);
							break;

						case "ssc_user":
							$sql = $this->data->getListSQL("user_assignment", array(context_filter=>"context_ssc"))." order by name";
							$records = $this->data->get_results($sql);
							break;

						case "district_ssc_user":
							$sql = $this->data->getListSQL("user_assignment", array(context_filter=>"context_district_ssc"))." order by name";
							$records = $this->data->get_results($sql);
							break;


						case "pm_user":
							$sql = $this->data->getListSQL("user_assignment", array(context_filter=>"context_pm")) ." order by name";
							$records = $this->data->get_results($sql);
							break;


						case "district_group":
							$sql = $this->data->getListSQL("group", array(context_filter=>"context_current_district")) ." order by name";
							$records = $this->data->get_results($sql);
							break;


						case "user_district":
							$sql = $this->data->getListSQL("user_district_assignment", array(context_filter=>"current_gn_user_id")) ." order by name";
							$records = $this->data->get_results($sql);
							break;


						case "administrator_user":
							$sql = $this->data->getListSQL("user_assignment", array(context_filter=>"context_administrator")) ." order by name";


							$records = $this->data->get_results($sql);
							break;

						case "partner_kind":
							$sql = "select id, name from ".$this->data->tableName("partner_kind")." order by case name when 'Other' then 1 else 0 end, name";
							$records = $this->data->get_results($sql);
							break;

						default:
							$records = $this->data->retrieveSelectOptions($type);
							break;
			}

			return ($records);
		}


/**
 *
 * @param
 * @return
 */

function listOptions($type, $buffer=false) {

			$records=$this->retrieveListOptions($type);

			if ($buffer)  ob_start();

			if ($records)
				$this->displayOptions($records);
			else {
				echo("<option><i>No options for:$type</i></option>");
			}

			if ($buffer)  return ob_get_clean();

		}






/**
 *
 * @param
 * @return
 */

function listCheckOptions($type, $name, $buffer=false) {

			$records=$this->retrieveListOptions($type);

			if ($buffer)  ob_start();

			if ($records)
				$this->displayChecks($records, $name, $buffer);
			else {
				echo("<option><i>No options for:$type</i></option");
			}

			if ($buffer)  return ob_get_clean();

		}

/**
 *
 * @param
 * @return
 */

function listCheckOptionsRaw($type, $name, $buffer=false) {

			$records=$this->retrieveListOptions($type);

			if ($buffer)  ob_start();

			if ($records)
				$this->displayChecksRaw($records, $name, $buffer);
			else {
				echo("<option><i>No options for:$type</i></option");
			}

			if ($buffer)  return ob_get_clean();

		}



/**
 *
 * @param
 * @return
 */

function displayDistrictServices ($district_id,  $buffer=false) {

				$records=$this->gnData->retrieveDistrictServices($district_id);

				if ($buffer)  ob_start();

				if ($records)
					$this->displayChecks($records, "service_id");
				else {
					echo("<option><i>No options for:$type</i></option");
				}

				if ($buffer)  return ob_get_clean();


	}



/**
 *
 * @param
 * @return
 */

function ajaxGetSelectOptions() {

			$type = $_GET["type"];
			$id = $_GET["id"];
			$district_id=$_GET["district"];
			$service_type_id = $_GET["service_type_id"];

			$this->data->contextRecord = array ("district_id"=>$district_id);

			global $user_ID;


			$role = $this->gn_user_role();




			//if ($id) {

				switch ($type) {

					/*
					case "partner-services":
						$records = $id?$this->data->retrievePartnerServices($id):$this->data->retrieveSelectOptions("service", true, array(context_filter=>"context_district_service"));
						break;
					case "service-partners":
						$records = $id?$this->data->retrieveDistrictServicePartners($id):$this->data->retrieveSelectOptions("partner", true, array(context_filter=>"context_district_partner") );
						break;
					*/

					case "partner-services":
						//if ($id && $district_id && $service_type_id)
							if ($service_type_id)
								$records = $this->data->retrievePartnerServices($id, $district_id, $service_type_id);
						//:$this->data->retrieveSelectOptions("service", true, array(context_filter=>"context_district_service"));
						break;
					case "service-partners":
						//if ($id && $district_id && $service_type_id)
							if ($service_type_id)
								$records = $this->data->retrieveDistrictServicePartners($id, $district_id, $service_type_id);
						//:$this->data->retrieveSelectOptions("partner", true, array(context_filter=>"context_district_partner") );
						break;



					case "service":
						$records = $this->data->retrievePartnerServices(0);
						break;

					case "ssc_user":
						$records = $id?$this->data->retrieveDistrictSSC($id):$this->data->retrieveSelectOptions("ssc_user");
						break;

					case "group":
						$records = $id?$this->data->retrieveDistrictGroups($id):$this->data->retrieveSelectOptions("group");
						break;

					case "school":
	//					$records = $id?$this->data->retrieveDistrictSchools($id):$this->data->retrieveSelectOptions("school");
						if ($role=="gn_ssc") {
						$records = $this->data->retrieveSSCSchool($user_ID);
						}
						else {
						$records = $id?$this->data->retrieveDistrictSchools($id):array ();
						}
						break;

					case "classroom":
	//					$records = $id?$this->data->retrieveSchoolClassrooms($id):$this->data->retrieveSelectOptions("classroom");
						if ($role=="gn_ssc") {
						$records = $this->data->retrieveSSCClassrooms($user_ID);

						}
						else {
						$records = $id?$this->data->retrieveSchoolClassrooms($id):array ();
						}
							break;





					default:
						error_log ("Unknown select options:".$type);
						break;
				}
		//	}


			$this->doAjaxResponse ($records);

		}




/*



function genericMultipleEdit($form, $elements) {

	global $_POST;

	if ($this->getPostValue ("object_id")) {
		$_POST["id"] = $this->getPostValue ("object_id");
	}

	if ($this->getPostValue ("object_name")) {
		$_POST["name"] = $this->getPostValue ("object_name");
	}


	$baseData=$_POST;


	if ($this->data->doMultipleInsertUpdate($form, $elements, $baseData)) {
			if ($_POST["object_id"])
						$this->GlobalMSG.=strtoupper($name)." updated.";
					else
						$this->GlobalMSG.=strtoupper($name)." added.";
				}
		else {

			$this->GlobalMSG.="Update error/ or No Change";

		}
}




function classroomServiceReferral($form) {
	$classroomID = $_POST["classroom_id"];

	$studentObjects =$this->data->retrieveClassroomStudentObjects($classroomID);
	$students = array();

	foreach ($studentObjects as $student) {
		$students[] = array("student_id"=>$student->id);

	}

	$this->genericMultipleEdit("service_referral", $students);

}
*/

/**
 *
 * @param
 * @return
 */

function genericMultipleInsertUpdate($form, $elements) {



	$baseData=$_POST;


	if ($this->data->doMultipleInsertUpdate($form, $elements, $baseData)) {

				if ($elements[0]["id"]) {
					$this->GlobalMSG.=strtoupper($form)." updated.";
				}
				else {
					$this->GlobalMSG.=strtoupper($form)." added.";


				}
		}
		else {

			$this->GlobalMSG.="Update error/ or No Change";

		}


}

/**
 *
 * @param
 * @return
 */

function bulkCCNXUpdate($type) {
	$ids = $_POST["list-selected-form-item-id"];

	$objects = array();

	foreach ($ids as $id) {
		$object = $this->data->getCCNXObject($id,$type );

		$objects[] = $object;

	}

	$this->genericMultipleInsertUpdate($type, $objects);

}


/**
 *
 * @param
 * @return
 */

function bulkServiceStatusUpdate($form) {


	$this->bulkCCNXUpdate("service_referral");

	/*
	$ids = $_POST["list-selected-form-item-id"];

	$referrals = array();

	foreach ($ids as $studentReferralID) {
		$referral = $this->data->getCCNXObject($studentReferralID,"service_referral" );



		$referrals[] = $referral;

	}

	$this->genericMultipleInsertUpdate("service_referral", $referrals);
	*/

}






/**
 *
 * @param
 * @return
 */

function bulkSSCAssign($form) {

	$this->bulkCCNXUpdate("classroom");

}


/**
 *
 * @param
 * @return
 */

function bulkServiceGroup($form) {
	$this->bulkCCNXUpdate("service");

}




/**
 *
 * @param
 * @return
 */

function bulkServiceReferral($form) {
	if ($_POST["bulk_option"]=="wcr") {
		$this->bulkWCRServiceReferral($form);

	}
	else {

		$ids = $_POST["list-selected-form-item-id"];


		/*
		$studentObjects =$this->data->retrieveClassroomStudentObjects($classroomID);
		*/
		$students = array();

		foreach ($ids as $studentID) {
			$students[] = array("student_id"=>$studentID);

		}

		$this->genericMultipleInsertUpdate("service_referral", $students);
	}
}


/**
 *
 * @param
 * @return
 */

function bulkWCRServiceReferral($form) {
	$ids = $_POST["list-selected-form-item-id"];

	$students = array();

	foreach ($ids as $studentwcrID) {
		$wcr = $this->data->getCCNXObject($studentwcrID,"wcr_student_review" );

		$students[] = array("student_id"=>$wcr["student_id"]);

	}

	$this->genericMultipleInsertUpdate("service_referral", $students);

}







/**
 *
 * @param
 * @return
 */

function bulkUserDistrictAssign($form) {

	$ids = $form["list-selected-form-item-id"];
	$base_id = $form["gn_page_id"];

	$objects = array();

	foreach ($ids as $id) {


		$object =  array(user_id=>$base_id, district_id=>$id);

		$objects[] = $object;

	}

	$this->data->genericAssignReplace("user_district_assignment", "user_id", $base_id,  $objects);

}



/**
 *
 * @param
 * @return
 */

function bulkUserSchoolAssign($form) {

	$ids = $form["list-selected-form-item-id"];
	$base_id = $form["gn_page_id"];

	$objects = array();

	foreach ($ids as $id) {
		$object =  array(user_id=>$base_id, school_id=>$id);

		$objects[] = $object;

	}

	$this->data->genericAssignReplace("user_school_assignment", "user_id", $base_id,  $objects);

}




//retrievePartnerServices
//retrieveDistrictServicePartners




/**
 *
 * @param
 * @return
 */

function contextValue($key) {
		echo($this->data->contextRecord[$key]);


	}


/**
 *
 * @param
 * @return
 */

function getContextValue($key) {
			return($this->data->contextRecord[$key]);


	}



/**
 *
 * @param
 * @return
 */

function addGeoLocation ($data) {
		$where ="";


		$where.= $data["location_address"]." ";
		$where.= $data["location_address2"]." ";
		$where.= $data["location_city"]." ";
		$where.= $data["location_state"]." ";
		$where.= $data["location_zip"]." ";


		$loc = $this->geoLocate($where);

		$data["locationnorth"] = $loc->locationnorth;

		$data["locationeast"] = $loc->locationeast;

		return ($data);
	}



/**
 *
 * @param
 * @return
 */

function ajaxUpdateEdit () {
		$form=$this->getPostValue("gn_form");

		switch($form) {
			case "note":
				$this->addEditNote();
				break;
			default:
				$this->genericUpdateEdit($form);
				break;
		}
	}

/**
 *
 * @param
 * @return
 */

 function ajaxUpdateEditJSON (){

 	$this->ajaxUpdateEdit();
 	$this->doAjaxResponse(array(
 		"status"=>"OK",
 		"message"=>$this->GlobalMSG
 	));

 }


/**
 *
 * @param
 * @return
 */

 function ajaxAutoSave () {
 	$form=$this->getPostValue("gn_form");

 	// DS: using this for now
 	try {
 		$this->genericUpdateEdit($form);
 	}
 	catch (Exception $e) {
 		$this->doAjaxResponse (
 			array(
 				"status"=>"Error",
 				"message"=>"Autosave error: ".$e->getMessage()
 			)
 		);
 	}

 	$this->doAjaxResponse (array("status"=>"OK"));

 }


/**
 *
 * @param
 * @return
 */

function ajaxDeleteNote () {
		$id = $_POST['id'];

		if($this->checkNoteAccess($id)) {
			$sql = "update gn_note set deleted=1 where id=".$this->gnData->quoteString($id);
			$result = $this->gnData->dbExecute($sql);
			if($result === false) {
				$this->doAjaxResponse (array(
					"status"=>"Error",
					"message"=>"Error deleting note."
				));
			}
			else {
				$this->doAjaxResponse (array("status"=>"OK"));
			}
		}
		else {
				$this->doAjaxResponse (array(
					"status"=>"Error",
					"message"=>"Permission denied."
				));
		}

	}


/**
 *
 * @param
 * @return
 */

function checkNoteAccess ($noteID) {
		global $current_user;
		get_currentuserinfo();
		$userRole = gn_user_role();

		$dbNote = $this->gnData->getCCNXObject($noteID, "note");

		return ($userRole == "gn_administrator" || $dbNote['creator_user_id']==$current_user->ID) ? true : false;

	}


/**
 *
 * @param
 * @return
 */

function addEditNote() {
		global $current_user;
		get_currentuserinfo();

		$data = $_POST;

		if($data['object_id']) {
			$data['id'] = $data['object_id'];
			unset($data['creator_user_id']);

			if(!$this->checkNoteAccess($data['object_id'])) {
				$this->doAjaxResponse (array(
					"status"=>"Error",
					"message"=>"Permission denied."
				));
				return;
			}
		}
		else {
			$data['creator_user_id'] = $current_user->ID;
		}

		$sql = $this->gnData->getUpdateEditSQL("note", $data);
		$return = $this->gnData->dbExecute($sql);

		if($return === false) {
			$this->doAjaxResponse (array(
				"status"=>"Error",
				"message"=>"Error editing note."
			));
		}
		else {
			$this->doAjaxResponse (array(
				"status"=>"OK"
			));
		}

	}

	function updateEditPartner ($form) {

		if(!trim($_POST['object_id']) && !trim($_POST['date_joined'])) {
			// set date joined to current date, if not provided, for new partners
			$_POST['date_joined'] = date('Y-m-d');
		}

		$this->genericUpdateEdit($form);
	}


/**
 *
 * @param
 * @return
 */

function doUpdateEdit() {


			if ($this->getPostValue("location_address") && !$this->getPostValue("locationnorth")) {

				$_POST=$this->addGeolocation($_POST);
			}

			switch ($form=$this->getPostValue("gn_form")) {


			case ("district"):
				$this->genericUpdateEdit($form);
				break;


			case ("classroom_service_referral"):
				$this->classroomServiceReferral($form);
				break;

			case ("bulk_service_referral"):

				$this->bulkServiceReferral($form);
				break;


			case ("bulk_service_group"):

				$this->bulkServiceGroup($form);
				break;


			case ("bulk_ssc_assign"):

				$this->bulkSSCAssign($form);
				break;


			case ("bulk_wcr_service_referral"):

				$this->bulkWCRServiceReferral($form);
				break;

			case ("bulk_service_status_update"):

				$this->bulkServiceStatusUpdate($form);
				break;

			case "isr_isp":
				$this->doISPUpdate();
				break;

			case "partner":
				$this->updateEditPartner($form);
				break;

			default:
				$this->genericUpdateEdit($form);
				break;

			}


			// DS: Task manager now introspects on generic ccnx update/create event
			// to determine if a support request has been processed.
			/**

			if($taskID = $this->getPostValue("support_request_task")) {
				try {
					do_action('support_request_processed', $taskID);
				}
				catch (Exception $e) {
					$this->GlobalMSG.= "Error updating support request: ".$e->getMessage();
				}
			}

			**/

		}





/**
 *
 * @param
 * @return
 */

function doDataRelationUpdate() {
			switch ($form=$this->getPostValue("gn_form")) {


			case ("partner_service"):
	;
				$this->genericRelationEdit($form);
				break;


			default:
				$this->genericRelationEdit($form);
				break;

		}
}





/**
 *
 * @param
 * @return
 */

function handlePost($action) {

			switch ($action) {

			case "update-edit":
				$this->doUpdateEdit();
				break;

			case "update-relation":
				$this->doDataRelationUpdate();
				break;

			case "data-import":
				$this->dataImport->doDataImport();
				break;

			case "support-request":
				$this->doSupportRequest();
				break;
			case "class-wcr-create":
				$this->createWCR();
				break;
			case ("student-isr-create"):

				$this->createISR();

				/** DS: this is now in the data class
				try {
					// should be in data routines after actually (successfuly) created
					do_action('gn_isr_created', 1);
				}
				catch (Exception $e) {
					$this->GlobalMSG.= "Error creating ISR workflow: ".$e->getMessage();
				}
				**/
				break;

			default:
				//echo ("Unknown Post: $action");
				//Possibly updated list display
				break;
			}

	}


/**
 *
 * @param
 * @return
 */

function createISR () {
		$studentId= $this->getPostValue("studentId");

		if (!$studentId) {
			$this->GlobalMSG.= "Error creating ISR : missing student ID ";
		}
		else {

			if ($id= $this->data->createStudentISR($studentId)) {

				wp_redirect("/reviews/isrs/isr/?id=". $id);
			}
			else {
			$this->GlobalMSG.= "Data Error creating ISR ";

			}
		}
	}


/**
 *
 * @param
 * @return
 */

function doISPUpdate () {

			if (!$ispID = $this->getPostValue ("object_id")) {
				$this->GlobalMSG .= "Error: No ISP id provided.";
				return;
			}

			$goals = array();

			for($goalSeq = 1; $this->getPostValue("goal-$goalSeq-record"); $goalSeq++){

				$goalCols = $this->data->tableColumns("isr_isp_goal");
				$goalPrefix = "goal-$goalSeq";

				$goal = $this->getISPItemData($goalCols, $goalPrefix);

				if(!goal) {
					continue;
				}

				$goal['sequence'] = $goalSeq;

				for($interventionSeq=1;
					$this->getPostValue("$goalPrefix-intervention-$interventionSeq-record");
					$interventionSeq++) {

					$interventionCols = $this->data->tableColumns("isr_isp_intervention");
					$interventionPrefix = "$goalPrefix-intervention-$interventionSeq";

					$intervention = $this->getISPItemData($interventionCols, $interventionPrefix);

					if($intervention) {
						if(!$goal['interventions']) {
							$goal['interventions'] = array();
						}
						$intervention['sequence'] = $interventionSeq;
						$goal['interventions'][] = $intervention;
					}
				}

				$goals[] = $goal;

			}

			$this->data->saveISPGoals($ispID, $goals);
	}



/**
 *
 * @param
 * @return
 */

function getISPItemData ($dataColumns, $prefix) {
		$data = array();
		foreach($dataColumns as $col){
			if($val = $this->getPostValue("$prefix-$col")) {
				$data[$col] = $val;
			}
		}

		return $data;
	}


/**
 *
 * @param
 * @return
 */

function createWCR() {
		$classId= $this->getPostValue("classId");

		if ($id= $this->data->createClassWCR($classId)) {

			wp_redirect("/reviews/wcrs/wcr/?id=". $id);
		}
	}


/**
 *
 * @param
 * @return
 */


 			// DS:  *****  Need to do this via action vs. having this have task manager code



function doSupportRequest() {
			global $current_user;
			get_currentuserinfo();

			try {
				//$gnTaskManager->createSupportRequestActivity();
				do_action('gn_data_event', 'support_request_created', $this->data->getCCNXUserData($current_user->ID));
			}
			catch (Exception $e) {
				$this->GlobalMSG.= $e->getMessage();
				return;
			}

			// DS: Should re-direct to confirmation page here.
			// (Just append 'mode=confirm' to the current url).
			echo("<script type='text/javascript'> location.search = 'mode=confirm'</script>");

		}

// DS: File data import methods removed



/**
 *
 * @param
 * @return
 */

function simpleChartTable ($records, $caption) {

		$str="<table  class='gn_dash_view'><thead><caption>$caption</caption><tr><th></th><th></th></tr></thead><tbody>";

		foreach($records as $record) {
			$value=$record->value+.0;

			$str.="<tr><th>$record->heading</th><td>$value</td></tr>";

		}
		$str.="</tbody></table>";

		return ($str);

	}



/**
 *
 * @param
 * @return
 */

function nextStudentWCRList ($student_wcr_id, $wcr_id) {
		$records = $this->data->retrieveStudentWCRs($wcr_id);
		$next = array();
		$collect=false;

			foreach ($records as $record) {
				if ($collect) {
					$next[] = $record["id"];
				}
				if ($record["id"] ==$student_wcr_id) {
					$collect= true;
				}
			}

		return (implode(",", $next));

	}




/**
 *
 * @param
 * @return
 */

function doQuickChart ($chart, $caption) {
		$id= $this->getQueryStringValue("id");

		if ($id) {

			switch ($chart) {

			case "wcr-progress":
				$records = $this->data->wcrReportRecordEntry($id);
				break;


			case "records-complete":
				$records = $this->data->wcrReportRecordCompletion($id);
				break;


			case "tier-complete":
				$records = $this->data->wcrReportRecordTierCompletion($id);
				break;

	// :-}
			case "retier-complete":
				$records = $this->data->wcrReportRecordReTierCompletion($id);
				break;

			case "classroom-wcr-progress":
				$classroom_id= $this->getQueryStringValue("id");

				$currentWCR=$this->data->getCurrentClassroomWCR($classroom_id);

				$records = $this->data->wcrReportRecordReTierCompletion($currentWCR["id"]);
				break;


			}
		}
		else {

			error_log("CCNX Quick chart called without context at: ". $_SERVER['REQUEST_URI']);

		}

		if(($records[0]->value)>0 ) {

			return($this->simpleChartTable($records,$caption));

		}

}




/**
 *
 * @param
 * @return
 */

function record_table($objectId, $key, $fields) {

	$str="";
	$record = $this->data->getCCNXFullObject($objectId,$key);

	if ($record) {
		$str="<table class='gn_record_table'>";

		foreach($fields as $fieldKey=>$fieldTitle) {
			$value = htmlspecialchars($record[$fieldKey]);
			//$value = str_replace('gn_br', '<br />', $value);

			$value = preg_replace('/\[gn_([a-z\/]+?)\]/i', "<$1>", $value);

			$fieldTitle = preg_replace('/_/i', " ", $fieldTitle);

			switch ($value) {
				case "0":
					$value="No";
					break;

				case "1":
					$value="Yes";
					break;

				case "-1":
					$value="N/A";
					break;
			}

			$str.="<tr class='$fieldKey'><th>$fieldTitle</th><td>$value</td></tr>";
		}
		$str.="</table>";
	}

	return ($str);

}


// ****** Workflow


/**
 *
 * @param
 * @return
 */

function initWorkflows() {

		add_action ("gn_data_event",  array(&$this, 'processDataEvent'),10,2);

		add_action ("gn_data_event_generic_create_student",  array(&$this, 'gn_create_student'));

		add_action ("gn_data_event_generic_update_student_classroom_assignment", array(&$this, 'gn_update_student_classroom_assignment'));
		add_action ("gn_data_event_generic_insert_student_classroom_assignment", array(&$this, 'gn_update_student_classroom_assignment'));



		add_filter ("gn_db_update_edit_value_defaults_filter", array(&$this, 'gn_update_edit_defaults_filter'));


		add_action ("wp_head", array(&$this, 'identify_gn_role'));

	}



/**
 *
 * @param
 * @return
 */

function processDataEvent($type,$post) {
		$kind = $post["gn_form"];

		//error_log("********ACTION: gn_data_event_".$type."_$kind  Type: $typeArg");
		do_action ("gn_data_event_".$type."_$kind", $post);


	}


/**
 *
 * @param
 * @return
 */

function gn_create_student ($post) {

		if ($_GET["contextName"]=="school") { // New insert in context of a school...

			$district_id = $post["district_id"];
			$student_id = $_GET["id"];
			$school_id = $_GET["contextId"];

			$sca = $this->data->getCurrentStudentClassroomAssignment($student_id, $district_id);

			$district_school_year_id = $sca["district_school_year_id"];
			$update = array(student_id=>$student_id,school_id=>$school_id, district_id=>$district_id, district_school_year_id=>$district_school_year_id);

			// Define the district and the school for this year's classroom assignment

			$this->data->updateEdit ("student_classroom_assignment",$update , true);

		}

		else if ($_GET["contextName"]=="classroom") { // ... or classroom
			$district_id = $post["district_id"];
			$student_id = $_GET["id"];
			$classroom_id = $_GET["contextId"];

			$classroom = $this->data->getCCNXObject($classroom_id, "classroom");

			$school_id = $classroom['school_id'];

			$sca = $this->data->getCurrentStudentClassroomAssignment($student_id, $district_id);

			$district_school_year_id = $sca["district_school_year_id"];
			$update = array(
				student_id=>$student_id,
				school_id=>$school_id,
				district_id=>$district_id,
				district_school_year_id=>$district_school_year_id,
				school_id => $school_id,
				classroom_id => $classroom_id
			);


			$this->data->updateEdit ("student_classroom_assignment",$update , true);
		}

}

/**
 *
 * @param
 * @return
 */

function gn_update_student_classroom_assignment ($post) {

		$district_id = $post["district_id"];
		$student_id = $post["student_id"];
		$classroom_id = $post["classroom_id"];


		//insure district sycned


		if($student_id && $district_id) {
			$this->data->updateEdit ("student", array(id=>$student_id, district_id=>$district_id), true);
		}

		// Check existing WCR

		$this->data->insureStudentWCRonUpdate($classroom_id, $student_id);

	}



/**
 *
 * @param
 * @return
 */

function gn_update_edit_defaults_filter($valueArray) {

			$this->init_user_context();

				if(!$valueArray['last_update']) {
					$valueArray['last_update'] = date("Y-m-d H:i:s");
				}

				if(!$valueArray['last_update_gn_user_id']) {
					$valueArray['last_update_gn_user_id'] = $this->context["gn_user_id"];
				}


				if(!$valueArray['id'] && !$valueArray['record_created_gn_user_id']) {
					$valueArray['record_created_gn_user_id'] = $this->context["gn_user_id"];
				}


				if(!$valueArray['last_update_gn_user_name']) {
					$valueArray['last_update_gn_user_name'] = $this->context["gn_user_name"];
				}

				// CTW: Not sure about this one ????

				if ($this->userDistrict) {
					$valueArray["district_school_year_id"] = $valueArray["district_school_year_id"]?$valueArray["district_school_year_id"]:$this->userDistrictSchoolYearId;
					$valueArray["district_id"] = $valueArray["district_id"]?$valueArray["district_id"]:$this->userDistrict;
				}

				return ($valueArray);


	}




// *****  Options


/**
 *
 * @param
 * @return
 */

function getStateOptions () {
			// DS: can be modified to pull from db.
			include("forms/_state_options.php");
		}


/**
 *
 * @param
 * @return
 */

function getLanguageOptions () {
			include("forms/_language_options.php");
		}




/**
 *
 * @param
 * @return
 */

function getReferralReasonOptions  () {
			$arr = array("Academic", "Social/Emotional/Behavioral", "Health/Medical", "Family");
			return "<option>".implode("</option><option>", $arr)."</option>";
		}


/**
 *
 * @param
 * @return
 */

function showGlobalMessage ($name, $atts) {


				if ($this->GlobalMSG) {

					$message = preg_replace( array_keys($this->messageAliasArray),  array_values($this->messageAliasArray), $this->GlobalMSG);
					return("<div class='gnMessage'>$message</div>");

				}


			}




// ***** Google Maps




/**
 *
 * @param
 * @return
 */

function geoLocate($where) {
	$where = stripslashes($where);
    $whereurl = urlencode($where);

	$loc = new stdClass();
  	$location = file("http://maps.google.com/maps/geo?q=$whereurl&output=csv&key=$this->googleKey");

	list ($stat,$acc,$north,$east) = explode(",",$location[0]);

	$loc->locationnorth = $north;
	$loc->locationeast = $east;
	return ($loc);

}



/**
 *
 * @param
 * @return
 */

function searchPartners() {

 	$serviceType= $_POST["service"];
 	$radius= $_POST["radius"];

 	$partners = $this->data->retrievePartnerByLocation($this->location, $radius,$serviceType);

	return($partners);

 }



/**
 *
 * @param
 * @return
 */

function jsQuote($str) {
	$str= str_replace('"','\\"',$str);
	$str= str_replace("'","\\'",$str);

	$nl   = array("\r\n", "\n", "\r");

	$str= str_replace($nl,' ',$str);
	return($str);
}


/**
 *
 * @param
 * @return
 */

function insertMarkers() {
	$siteURL = get_option('siteurl');

 	if ($_POST["location"]) {
 		$partners=$this->searchPartners();
 	}
 	else {
 		$partners = array();
 	}


	echo ("var markers= new Array();\n");
	echo ("var bounds = new GLatLngBounds();\n");


 	if ($partners) {
		foreach ($partners as $partner) {
			$name =$this->jsQuote($partner->name);
			$address =$this->jsQuote($partner->location_address);
			$address2 =$this->jsQuote($partner->location_address2);
			if ($partner->location_address2) {
				$address.="<p>$address2</p>";
			}

			$address3 =$this->jsQuote($partner->location_city .", ".$partner->location_state.", ".$partner->location_zip);

			$html="<h3><a href=\"$siteURL/partnersservices/partners/partner/?id=$partner->id\">$name</a></h3><p>$address</p><p>$address3</p>";
			//$html=$this->jsQuote($this->offCampusEventDescriptionForMap ($event, $_REQUEST['where']));
			echo ("map.addOverlay(createMarker(new GLatLng($partner->locationnorth,$partner->locationeast),'$html'));
			bounds.extend(new GLatLng($partner->locationnorth,$partner->locationeast));");

		}
 	}



 	if ($partners) {
 	echo("\nmap.setZoom(map.getBoundsZoomLevel(bounds));
	  		map.setCenter(bounds.getCenter());");
	 }


 }



/**
 *
 * @param
 * @return
 */

function displayPartner($partner) {

   			$name =$this->jsQuote($partner->name);
   			$address =$this->jsQuote($partner->location_address);
   			$address2 =$this->jsQuote($partner->location_address2);
   			if ($partner->location_address2) {
   				$address.="<p>$address2</p>";
   			}

			$distance =round($partner->distance*10)/10;
   			$address3 =$this->jsQuote($partner->location_city .", ".$partner->location_state.", ".$partner->location_zip);

   			$html="<h3><a href=\"$siteURL/partnersservices/partners/partner/?id=$partner->id\">$name ($distance miles)</a></h3><p>$address<br/>$address3</p>";


   $str= "<div class='partner'>";

    $str.=$html;


 	$str.="</div>";
 	return($str);


  }


/**
 *
 * @param
 * @return
 */

function displayPartners () {
  	$currentHeader ="";
  	$displayingArchived=false;
  	$firstChild = false;

  	if ($_POST["location"] && $partners =$this->searchPartners()) {
 		foreach ($partners as $partner ) {


 			echo ( $this->displayPartner($partner));
 			$firstChild=false;
 			}


  	}
  	else if ($_GET["where"]) {
  		echo("<p>Sorry, there are currently no partners who fit the search criteria in this area. Try expanding your search area and selecting all partner types.</p>");
  	}

 }



/**
 *
 * @param
 * @return
 */

function getMapCoordinates () {
 	$html="";

   if ($_POST['location']) {
           $where = stripslashes($_POST['location']);
           $whereurl = urlencode($where);
		   // Note - Google key is domain specific!
		   $location = file("http://maps.google.com/maps/geo?q=$whereurl&output=csv&key=$this->googleKey");
		   $radius = $_POST['location'];
		   // Sample - $location[0]="200,8,51.369318,-2.133457";
           list ($stat,$acc,$north,$east) = explode(",",$location[0]);
           $html = "Information for ".htmlspecialchars($where)."<br>";
           $html .= "North: $north, East: $east<br>";
           $html .= "Accuracy: $acc, Status: $stat<br>";
   } else {
            $north=37.090240;
            $east = -95.712891;
            $acc =1;
            $radius=0;

           $north= 37.0625;
           $east=-95.677068;
           $acc =3.1;

   }
   $this->location =$location;
   $this->radius =$radius;


 			$this->html = $html;

            $this->north= $north;
            $this->east = $east;
            $this->accuracy = $acc;

}



/**
 *
 * @param
 * @return
 */

function insertMapArguments() {

 	echo("new GLatLng($this->north,$this->east),".$this->tabAccuracy[$this->accuracy]);
 }






 // ********


}




endif;


}

?>