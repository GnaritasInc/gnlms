<?php
ob_start();
define ('LOC', str_replace("\\","/",dirname( __FILE__)));
define('WP_ROOT',substr(LOC,0,strpos(LOC,"wp-content")));

require_once(WP_ROOT.'/wp-config.php');

require_once("classes/gnlms_LMS.php");
require_once("classes/gnlms_Data.php");

ob_end_clean();

$gnlms = new gnlms_LMS();



function loadData($uid, $cid) {
	global $wpdb;
	global $gnlms;


	$sql ="select scormdata from ".$gnlms->data->tableName('user_course_registration')." where course_id=%d and user_id=%d";
	
	$sql = $wpdb->prepare($sql, $cid, $uid);
	
	$data = $wpdb->get_var($sql);
	
	echo strlen(trim($data)) ?  $data : "{}";

}


function checkCompletion ($uid, $cid, $data) {
	global $gnlms;
	$dataObj = json_decode(stripslashes($data));

	if(!$dataObj) {
		error_log("JSON parse error: ".json_last_error());
		error_log("JSON string: $data");
		return;
	}


	if(isComplete($dataObj) && $gnlms->data->getUserCourseStatus ($uid, $cid) != "Completed") {
		$gnlms->data->setCourseComplete($uid, $cid, getScore($dataObj));
	}
	else if (isFailed($dataObj) && $gnlms->data->getUserCourseStatus ($uid, $cid) != "Failed") {
		$gnlms->data->setCourseFailed($uid, $cid, getScore($dataObj));
	}

}


function storeEvaluationResult($uid, $cid, $data) {
	global $gnlms;
	$dataObj = json_decode(stripslashes($data), true);
	
	if (!$dataObj) {
		error_log("gnlms: JSON parse error: ".json_last_error());
		error_log("gnlms: JSON string: ".stripslashes($data));	
		return;
	}
	
	if (isset($dataObj["cmi"]["comments_from_learner"])) {
		$commentData = array();
		foreach ($dataObj["cmi"]["comments_from_learner"] as $i=>$comment) {
			$n = intval($i)+1;
			if ($n<=20 && $commentText=trim($comment["comment"])) {
				$commentData["q$n"] = $commentText;
			}
			
		}
		if (count($commentData)) {
			$commentData["user_id"] = $uid;
			$commentData["course_id"] = $cid;
			
			$sql = "insert into ".$gnlms->data->tableName("evaluation_response")." (". implode(", ", array_keys($commentData)) .")";
			$sql .= " values (". implode(", ", array_fill(0, count($commentData), "%s")) .")";
			$sql .= " on duplicate key update id=id";
			
			$sql = $gnlms->data->db->prepare($sql, array_values($commentData));
			
			$gnlms->data->dbSafeExecute($sql);
			
		}
	}


}


function storeAssessmentResult($uid, $cid, $data) {
	global $gnlms;

	$assessmentData=extractAssessmentResults($uid, $cid, $data);

	foreach ($assessmentData->assessments as $name=>$assessments) {
		foreach ($assessments as $id=> $assessment) {

			error_log("Storing for $name");

			$assessment["user_id"]=  $gnlms->data->quoteString($uid);
			$assessment["course_id"]=  $gnlms->data->quoteString($cid);

			$sql= "insert into ".$gnlms->data->tableName('user_course_assessment_response')."(" .implode(",",array_keys($assessment)) .") values (" . implode(",",array_values($assessment)) .") ON DUPLICATE KEY UPDATE id=id";

			$gnlms->data->dbSafeExecute($sql);


		}
	}
}

function debugInteraction ($interaction) {
	// Debugging error log

				ob_start();
				echo ($interaction->id);

				print_r($result);
				echo ($interaction->student_response);

				$val=ob_get_clean();
				error_log("Extracting assessment data for: ". $val);

	// End debugging log

}

function extractInteractionAssessmentData($assessmentData,$interaction) {

		global $gnlms;

		list($type, $name, $attempt, $n) = explode("_",$interaction->id);

		$result = $interaction->result;
		$student_response = $interaction->student_response;

		if ($name && $attempt && $n) {

			$attempt =intval($attempt);
			$attempt--;

			if (!in_array($name, $assessmentData->assessmentNames)) {
				array_push($assessmentData->assessmentNames,$name);
				$assessmentData->assessments[$name] = array();
			}
			if (!($assessmentData->assessments[$name][$attempt])) {
				$assessmentData->assessments[$name][$attempt] = array();
				$assessmentData->assessments[$name][$attempt]["attempt"]=  $gnlms->data->quoteString($attempt+1);
				$assessmentData->assessments[$name][$attempt]["name"]=  $gnlms->data->quoteString($name);

			}
			if ($n=="score") {
				$assessmentData->assessments[$name][$attempt]["score"] = $gnlms->data->quoteString( $student_response);

			}
			else if ($n=="date") {
				// DS: Changing this to standardize time zone
				// $assessmentData->assessments[$name][$attempt]["response_date"] = $gnlms->data->quoteString($student_response);

				$assessmentData->assessments[$name][$attempt]["response_date"] = "from_unixtime(".time().")";

			}
			else if ($n=="result") {
				$assessmentData->assessments[$name][$attempt]["result"] = $gnlms->data->quoteString($result);
			}
			else {
				$assessmentData->assessments[$name][$attempt]["q".$n."_response"]= $gnlms->data->quoteString($student_response);
				$assessmentData->assessments[$name][$attempt]["q".$n."_result"]= $gnlms->data->quoteString($result);
			}
		}

		return ($assessmentData);
}

function interactionType($interaction) {

	$idArray = explode("_",$interaction->id);

	return ($idArray[0]);


}
function extractAssessmentData($assessmentData, $interaction) {
	global $gnlms;

	//{"student_response":"3","result":1,"id":"assessment_posttest_1_4"}

	if (interactionType($interaction)=="assessment") {

		$assessmentData = extractInteractionAssessmentData($assessmentData, $interaction);


	}
	return ($assessmentData);
}

function extractAssessmentResults($uid, $cid, $data) {

	$dataObj = json_decode(stripslashes($data));

	if(!$dataObj) {
		error_log("gnlms: JSON parse error: ".json_last_error());
		error_log("gnlms: JSON string: ".stripslashes($data));

	}

	$assessmentData = new stdClass;

	$assessmentData->assessments = array();
	$assessmentData->assessmentNames = array();

	if ($dataObj->cmi && $dataObj->cmi->interactions) {


		$interactions = get_object_vars($dataObj->cmi->interactions);

		foreach ($interactions as $key => $value) {

			$assessmentData= extractAssessmentData($assessmentData, $value);

		}

	}
	return ($assessmentData);
}

function getScore ($courseData) {
	$score = ($courseData->cmi && $courseData->cmi->core && $courseData->cmi->core->score) ? $courseData->cmi->core->score->raw : null;
	return $score;
}

function getCourseStatus ($courseData) {
	$courseStatus = ($courseData->cmi &&  $courseData->cmi->core) ? $courseData->cmi->core->lesson_status : "";
	return 	$courseStatus;
}

function isComplete ($courseData) {

	$courseStatus = getCourseStatus ($courseData);

	return $courseStatus == "completed" ? true : false;
}

function isFailed ($courseData) {
	$courseStatus = getCourseStatus ($courseData);
	return $courseStatus == "failed" ? true : false;
}

function saveData($uid,$cid, $data) {
	global $wpdb;
	global $gnlms;

	checkCompletion($uid, $cid, $data);
	storeAssessmentResult($uid, $cid, $data);
	storeEvaluationResult($uid, $cid, $data);
	
	$gnlms->scormLog("Saving data for user $uid, course $cid: $data");

	$sql = "update ".$gnlms->data->tableName('user_course_registration')." set scormdata=%s where user_id=%d and course_id=%d";
	
	$sql = $wpdb->prepare($sql, stripslashes($data), $uid, $cid);
	
	return ($wpdb->get_var($sql));
}

function matchCredentials($uid,$cid) {

	return ($uid && $cid && $uid==get_current_user_id() && (true || $cid == $_SESSION ["cid"]));


}

function dispatch() {
	$cmd = $_POST["cmd"];
	$cid = $_POST["cid"];
	$uid = $_POST["uid"];
	$data = $_POST["data"];


	if (!matchCredentials($uid,$cid)) {
		echo("Not allowed");
		//exit();
	}
	else {

		switch($cmd) {

		case "load":
			return (loadData($uid,$cid));

		break;


		case "save":
			$result = saveData($uid,$cid, $data);
			do_action("gnlms_user_course_data_saved", $uid, $cid, $data);
			return $result;

		break;

		default:
			echo("42");

		break;
		}


	}

}


dispatch();


?>