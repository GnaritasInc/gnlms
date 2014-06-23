<?php
/**
 * Class Name: GN List Widget
 * @author: Gnaritas
 * @version: 1.0
 * @package: Gnaritas Wordpress Library
 */



if(!class_exists("gn_ListWidgetClass")):


class gn_ListWidgetClass  extends gn_WebInterface{
   protected $filename = __FILE__;



/**
 *
 *
 *
 */


function __construct () {

		$this->initDirLocations(__FILE__);

		// Data Initialization

		global $gn_DataInterface;

		$this->data=$gn_DataInterface->data;
		$this->dataInterface=$gn_DataInterface;

		$this->dtaKeyField ="id";
		$this->dtaNameField ="name";


		$this->registerScripts();

		$this->setAjaxHooks();

		$this->addShortCodes();

	}


/**
 *
 * @param
 * @return
 */


function super ($methodName) {
		if ($parent = get_parent_class($this)) {
			if (method_exists($parent, $methodName)) {
				parent::$methodName();

			}
		}

	}

/**
 *
 * @param
 * @return
 */

function initDirLocations($filename) {

			$baseDir = trailingslashit(dirname(dirname(plugin_basename($filename))));
			$this->homeDir = WP_PLUGIN_DIR."/$baseDir";
			$this->htmlDir = $this->homeDir."html/";
			$this->homeURL = WP_PLUGIN_URL."/$baseDir";
			$this->jsURL = $this->homeURL."js/";
			$this->cssURL = $this->homeURL."css/";

	}

/**
 *
 * @param
 * @return
 */

function registerScripts() {
			$this->super("registerScripts");

			wp_enqueue_script('gn_list_select', $this->jsURL."gn_list_select.js");
	}

/**
 *
 * @param
 * @return
 */

function addShortCodes() {
				add_shortcode('gn_full_list', array(&$this, 'gn_full_list'));
				add_shortcode('gn_short_list', array(&$this, 'gn_short_list'));
				add_shortcode('gn_running_list',array(&$this, 'gn_running_list'));
				add_shortcode('gn_edit_list',array(&$this, 'gn_edit_list'));
}

/**
 *
 * @param
 * @return
 */

function isFilter ($name) {
	switch ($name) {


		case "record_status": return ("record_status");
			break;


		case "student-identifier": return ("student-identifier");
			break;

		case "wp_users.user_login": return ("like");
			break;

		case "firstname": return ("like-front");
			break;

		case "lastname": return ("like-front");
			break;

		case "student_firstname": return ("like-front");
			break;

		case "student_lastname": return ("like-front");
			break;

		case "is_flagged": return ("notZero");
			break;


		case "teacher_lastname": return ("like-front");
			break;

		case "name": return ("like");  // DS: Changing this to "like"
			break;

		case "classroom_id": return ("equal");
			break;

		case "district_id": return ("equal");
			break;

		case "gn_partner.district_id": return ("equal");
			break;


		case "status": return ("equal");
			break;


		case "group_id": return ("equal");
			break;

		case "grade_id": return ("equal");
			break;

		case "grade": return ("equal");
			break;

		/** Not tested
		case "ca__grade_id": return ("equal");
			break;
		*/
		// *This* is correct:

		case "ca.grade_id": return ("equal");
			break;

		case "role": return ("equal");
			break;

		case "um2__meta_value": return ("equal");
			break;
		case "um2.meta_value": return ("equal");
			break;


		case "classroom_id": return ("equal");
			break;

		case "gn_partner_service.service_id": return ("equal");
			break;

		case "referral_count": return ("notZero");



		case "school_id": return ("equal");
			break;

		case "ca.school_id": return ("equal");
			break;


		case "student-classroom-my": return ("equal");
			break;

		case "firstname": return ("like");
			break;

		case "wcr-student": return ("wcr-student");
			break;

		case "wcrs-wcr": return ("wcrs-wcr");
			break;

		default: return (false);
			break;
		}

}

/**
 *
 * @param
 * @return
 */

function addFilterSQL ($atts, $g_defaultTable="") {
	$sql="";

	$key=$atts["key"];


	$qString =""; // $_SERVER['QUERY_STRING'];

	if ($_POST["activeForm"]==$key) {

		foreach($_POST as $key => $val)
			{
			    $qString .= $key . "=" . $val . "&";
			}

	}
	else {
		foreach($this->defaultFormValues as $key => $val)
			{
				$qString .= $key . "=" . $val . "&";
			}

		}



		if ($g_defaultTable) {
			$g_defaultTable= $g_defaultTable.".";
		}


		$filters = explode("&",$qString);


		foreach ($filters as $filter) {
			$filterArray = explode("=",$filter);
			$filterKey=$filterArray[0];


			$filterKey = preg_replace("/^_/","",$filterKey);
			$filterKey = preg_replace("/__/",".",$filterKey);

			$filterValue=$filterArray[1];


			if (strpos($filterKey,".")>0 || $filterKey=="classroom_id"  || $filterKey=="teacher") {
				$defaultTable="";
			}
			else {
				$defaultTable=$g_defaultTable;
			}


			if ($filterValue && $type=$this->isFilter($filterKey)) {
				switch ($type) {
					case "record_status":
						if ($filterValue=="no") {
							$sql.=" and ". ($defaultTable?"$defaultTable":"")."record_status=1 ";
						}
						break;

					case "equal":
						$sql.=" and $defaultTable".mysql_real_escape_string($filterKey)."=". $this->data->quoteString($filterValue)." ";
						break;

					case "like":
						 $sql.=" and $defaultTable".mysql_real_escape_string($filterKey)." like ". $this->data->quoteString("%".$filterValue."%")." ";
						 break;
					case "like-front":
						 $sql.=" and $defaultTable".mysql_real_escape_string($filterKey)." like ". $this->data->quoteString("".$filterValue."%")." ";
						 break;

					case "notZero":
						 $sql.=" and $filterValue > 0 ";
						 break;

					case "wcr-student":
							switch ($filterValue) {
								case "not-started":
									$sql.=" and ( isnull( started) or started=0 )";
									break;
								case "not-complete":
									$sql.=" and ( isnull(complete) or complete=0)  ";
									break;
								case "complete":
										$sql.=" and ( complete=1)  ";
									break;
								}
							break;


					case "student-identifier":
							$sql.=" and ( gn_student.id='$filterValue' or school_identifier='$filterValue')";
							break;

					case "wcrs-wcr":
							switch ($filterValue) {
								case "incomplete":
									$sql.=" and ( Incomplete=1)  ";
									break;
								case "missing-fields":
									$sql.=" and ( IncompleteFields=1)  ";
									break;
								case "missing-tier":
									$sql.=" and (IncompleteTier=1)  ";
									break;
								case "missing-retier":
									$sql.=" and (IncompleteRetier=1)  ";
									break;
								}

						 break;

				}
			}


	}


	error_log("SQL is here".$sql);
	return($sql);

}


/**
 *
 * @param
 * @return
 */

function isSort ($name) {
	switch ($name) {

		case "student-sort": return (true);
			break;

		case "classroom-sort": return (true);
			break;

		case "wcr-sort": return (true);
			break;


		case "isr-sort": return (true);
			break;

		case "referral-sort": return (true);
			break;

		default: return (false);
			break;

	}
}


/**
 *
 * @param
 * @return
 */

function addSortSQL ($atts) {
	$sql="";

	//$qString = $_SERVER['QUERY_STRING'];

	$qString = $_SERVER['QUERY_STRING'];

	foreach($_POST as $key => $val)
		{
			    $qString .= $key . "=" . $val . "&";
		}

	$sorts = explode("&",$qString);



	foreach ($sorts as $sort) {
		$sortArray = explode("=",$sort);
		$sortKey=$sortArray[0];
		$sortValue=$sortArray[1];

		error_log("Sort:".$sort.": ".$this->issort($sortKey));

		if ($this->issort($sortKey)) {
			$sql.=" order by ". mysql_real_escape_string($sortValue)." ";
		}

		if ( $sql!=""  && $atts["defaultsort"])
				$sql.=", ". $atts["defaultsort"];

	}

	if ($sql=="" && $atts["defaultsort"])
		$sql=" order by ". $atts["defaultsort"];

	return($sql);

}




/**
 *
 * @param
 * @return
 */

function addFilter ($filtername) {

		$str="";
		switch ($filtername) {

			case "record-status":
				$str.='<div class="inline"><label for="record_status">Show inactive: </label>';
				$str.='<input type="hidden"  id="record_status_null" value="no" name="record_status" />';
				$str.='<input id="record_status" value="yes" name="record_status" type="checkbox" /></div>';
				break;

			case "student-is-flagged":
				$str.='<label for="is_flagged">Show Students: </label>';
				$str.='<select id="is_flagged" name="is_flagged">';
				$str.='<option value="">All </option>';
				$str.='<option value="is_flagged">Flagged for September</option>';
				$str.='</select>';
				break;



			case "student-first-name":
				$str.='<div class="inline"><label for="firstname">First Name starts with: </label>';
				$str.='<input id="firstname" name="firstname" type="text"/></div>';
				break;


			case "user-name":
				$str.='<div class="inline"><label for="wp_users__user_login">User Login like: </label>';
				$str.='<input id="wp_users__user_login" name="wp_users__user_login" type="text"/></div>';
				break;


			case "student-last-name":
				$str.='<div class="inline"><label for="lastname">Last Name starts with: </label>';
				$str.='<input id="lastname" name="lastname" type="text"/></div>';
				break;


			case "record-student-first-name":
				$str.='<label for="student_firstname">First Name like: </label>';
				$str.='<input id="student_firstname" name="student_firstname" type="text"/>';
				break;


			case "record-student-last-name":
				$str.='<label for="student_lastname">Last Name like: </label>';
				$str.='<input id="student_lastname" name="student_lastname" type="text"/>';
				break;



			case "teacher-name":
				$str.='<label for="teacher">Teacher Last Name like: </label>';
				$str.='<input id="teacher_lastname" name="teacher_lastname" type="text"/>';
				break;

			case "name":
				$str.='<label for="_name">Name like: </label>';
				$str.='<input id="_name" name="_name" type="text"/>';
				break;


			case "student-classroom":
				$str.='<label for="classroom_id">Classroom: </label>';
				$str.='<select id="classroom_id" name="classroom_id">';
				$str.='<option value=""> </option>';
				$str.=$this->dataInterface->listOptions("classroom", true);
				$str.='</select>';
				break;


			case "student-grade":
				$str.='<div class="inline"><label for="grade">Grade: </label>';
				$str.='<select id="ca__grade_id" name="ca__grade_id">';
				$str.='<option value=""> </option>';
				$str.=$this->dataInterface->listOptions("grade", true);
				$str.='</select></div>';
				break;

			case "district":
				$str.='<label for="district">District: </label>';
				$str.='<select id="district_id" name="district_id">';
				$str.='<option value=""> </option>';
				$str.=$this->dataInterface->listOptions("district", true);
				$str.='</select>';
				break;

			case "user_district":
				$str.='<label for="district">District: </label>';
				$str.='<select id="district_id" name="district_id">';
				$str.='<option value=""> </option>';
				$str.=$this->dataInterface->listOptions("user_district", true);
				$str.='</select>';
				break;


			case "partner-district":
				$str.='<label for="district">District: </label>';
				$str.='<select id="gn_partner__district_id" name="gn_partner__district_id">';
				$str.='<option value=""> </option>';
				$str.=$this->dataInterface->listOptions("district", true);
				$str.='</select>';
				break;


			case "school":
				$str.='<label for="school">School: </label>';
				$str.='<select id="school_id" name="school_id">';
				$str.='<option value=""> </option>';
				$str.=$this->dataInterface->listOptions("school", true);
				$str.='</select>';
				break;

			case "ca-school":
				$str.='<label for="ca__school_id">School: </label>';
				$str.='<select id="ca__school_id" name="ca__school_id">';
				$str.='<option value=""> </option>';
				$str.=$this->dataInterface->listOptions("school", true);
				$str.='</select>';
				break;


			case "student-classroom-my":
				$str.='<label for="classroom">Classroom: </label>';
				$str.='<select id="classroom_id" name="classroom_id">';
				$str.='<option value=""> </option>';
				$str.=$this->dataInterface->listOptions("my_classroom", true);
				$str.='</select>';
				break;

			case "wcr-student":
				$str.='<label for="wcr-student">Show Student WCRs: </label>';
				$str.='<select id="wcr-student" name="wcr-student">';
				$str.='<option value="all">All </option>';
				$str.='<option value="not-started">Not Started </option>';
				$str.='<option value="not-complete">Fields Incomplete </option>';
				$str.='<option value="complete">Fields Complete</option>';
				$str.='</select>';
				break;


			case "ccnx-role":
				$str.='<label for="um2__meta_value">Show User Role: </label>';
				$str.='<select id="um2__meta_value" name="um2__meta_value">';
				$str.='<option value="">All </option>';
				$str.='<option value="gn_ssc">CCNX SSC</option>';
				$str.='<option value="gn_pm">CCNX PM</option>';
				$str.='<option value="gn_district_administrator">CCNX District Administrator</option>';
				$str.='<option value="gn_tech_support_district">CCNX Tech Support District</option>';
				//$str.='<option value="gn_tech_support">CCNX Tech Support</option>';
				$str.='</select>';
				break;


			case "student-identifier":
				$str.='<div class="inline"><label for="student-identifier">Student Id: </label>';
				$str.='<input id="student-identifier" name="student-identifier" type="text"/></div>';
				break;



			case "partner-referrals":
				$str.='<label for="referral_count">Show Partner: </label>';
				$str.='<select id="referral_count" name="referral_count">';
				$str.='<option value="">All </option>';
				$str.='<option value="referral_count">With current referrals</option>';
				$str.='</select>';
				break;

			case "partner-service":

			$str.='<div class="inline"><label for="service">Providing service: </label>';
			$str.='<select name="gn_partner_service__service_id" id="gn_partner_service__service_id">';
			$str.='<option value="">All</option>';
			$str.=$this->dataInterface->listOptions("service", true);
			$str.='</select>';
			$str.='</div>';
			break;

			case "service-referrals":
				$str.='<label for="referral_count">Show service: </label>';
				$str.='<select id="referral_count" name="referral-coun">';
				$str.='<option value="">All </option>';
				$str.='<option value="referral_count">With current referrals</option>';
				$str.='</select>';
				break;


			case "wcrs-wcr":
				$str.='<label for="wcrs-wcr">Show WCRs: </label>';
				$str.='<select id="wcrs-wcr" name="wcrs-wcr">';
				$str.='<option value="">All </option>';
				$str.='<option value="incomplete">Incomplete </option>';
				$str.='<option value="missing-fields">Missing Fields </option>';
				$str.='<option value="missing-tier">Requiring Tiering </option>';
				$str.='<option value="missing-retier">Requiring Re-Tiering </option>';

				$str.='</select>';
				break;

				case "referral-status":
					$str.='<label for="referral-status">Show Referrals: </label>';
					$str.='<select id="status" name="status">';
					$str.='<option value="">All</option>';
					$str.='<option value="referred">Referred</option>';
					$str.='<option value="on-hold">On-hold</option>';
					$str.='<option value="wait-list">Wait-list</option>';
					$str.='<option value="denied">Denied</option>';
					$str.='<option value="delivered">Delivered</option>';
					$str.='</select>';
				break;




			case "student-sort":
				$str.='<div class="inline"><label for="student-sort">Sort by: </label>';
				$str.='<select id="student-sort" name="student-sort">';
				$str.='<option value="lastname">Last Name</option>';
				$str.='<option value="firstname">First name</option>';
				$str.='<option value="gn_grade.id">Grade</option>';
				$str.='<option value="classroom">Classroom</option>';
				$str.='</select></div>';
				break;

			case "classroom-sort":
				$str.='<div class="inline"><label for="classroom-sort">Sort by: </label>';
				$str.='<select id="classroom-sort" name="classroom-sort">';
				$str.='<option value="classroom">Classroom</option>';
				$str.='<option value="teacher">Teacher</option>';
				$str.='<option value="gn_grade.id">Grade</option>';
				$str.='</select></div>';
				break;

			case "wcr-sort":
							$str.='<div class="inline"><label for="wcr-sort">Sort by: </label>';
							$str.='<select id="wcr-sort" name="wcr-sort">';
							$str.='<option value="classroom">Classroom</option>';
							$str.='<option value="teacher">Teacher</option>';
							$str.='<option value="gn_grade.id">Grade</option>';
							$str.='</select></div>';
				break;

			case "isr-sort":
							$str.='<div class="inline"><label for="isr-sort">Sort by: </label>';
							$str.='<select id="isr-sort" name="isr-sort">';
							$str.='<option value="lastname">Last Name</option>';
							$str.='<option value="firstname">First name</option>';

							$str.='<option value="classroom">Classroom</option>';
							$str.='<option value="teacher">Teacher</option>';
							$str.='<option value="grade_id">Grade</option>';
							$str.='</select></div>';
				break;

			case "referral-sort":
							$str.='<div class="inline"><label for="referral-sort">Sort by: </label>';
							$str.='<select id="referral-sort" name="referral-sort">';
				$str.='<option value="lastname">Last name</option>';
				$str.='<option value="firstname">First name</option>';
							$str.='<option value="service">Service</option>';
							$str.='<option value="partner">Partner</option>';
							$str.='<option value="status">Status</option>';
							$str.='</select></div>';
				break;


			default:
				$str="";
			}

			return ($str);



	}


	/**
 *
 * @param
 * @return
 */

function selectedItemHTML($id, $name) {

			$html="";

			$html="<div class='list-selected-form-item' id='list-selected-form-item-" .$id ."'>";

			$html.="<input type='hidden' class='gn_list_select_name'  name='list-selected-form-item-name[]' value='".$name ."'/>";
			$html.="<input type='hidden' class='gn_list_select_id' name='list-selected-form-item-id[]' value='".$id ."'/>";

			$html.="</div>";

			return ($html);

		}

		/**
 *
 * @param
 * @return
 */

function currentPostedItem($n) {

				$id=$_POST["list-selected-form-item-id"][$n];
				$name=$_POST["list-selected-form-item-name"][$n];

				return ($this->selectedItemHTML($id, $name));


			}

		/**
 *
 * @param
 * @return
 */

function addCurrentPostedItems () {
				$str="";
				$n=0;

				while ($_POST["list-selected-form-item-id"][$n]) {

					$str.=$this->currentPostedItem($n);
					$n++;
				}

				return ($str);
			}

		/**
 *
 * @param
 * @return
 */

function addSelectedItems($records, $id_field, $name_field) {
				$str="";
				foreach ($records as $record) {

						if (! is_array($record)) {
							$record=get_object_vars($record);
						}

					$str.=$this->selectedItemHTML($record[$id_field], $record[$name_field]);

				}

				//print ("Selected:".$str);
				return ($str);

			}


		/**
 *
 * @param
 * @return
 */

function addCurrentSelectedItems($atts) {

				$selected_atts = $atts;

				$selected_atts["key"] = $atts["view_key"];
				$selected_atts["context_filter"] = $atts["view_context_filter"];


				$sql =$this->generateSQL($selected_atts);

				$records =  $this->data->get_results($sql);


				return ($this->addSelectedItems($records, "id", "name"));

			}


/**
 *
 * @param
 * @return
 */

function formID($atts) {
	$form_id = $atts["form_id"]?$atts["form_id"]:"gn-list-form";

	return ($form_id);

	}

/**
 *
 * @param
 * @return
 */

function addForm ($atts, $code) {
		$str="";

		$defAtts=array( "gn_page_id","page_student_id",
						"key", "title", "context_filter",
						"view_key", "view_context_filter",
						"select_key", "select_context_filter",
						"save_action",  "filters", "mode",
						"link", "linkclass", "hide","hideid");

		if ($atts["filters"] )
			$str.='<div class="filters">';

			$form_id = $this->formID($atts);

			$str.="<form id='$form_id' class='gn-list-form' action='' method='post'>";

			$key=$atts["key"];
			$str.="<input type='hidden' name='activeForm' value='$key'/>";
			$str.="<input type='hidden' name='offset' value=''/>";

			$str.="<input type='hidden' name='action' value='gn-get-list-widget'/>";
			$str.="<input type='hidden' name='type' value='". ( $code?$code:$atts["type"]) ."'/>";


			// This should just be definitional
			foreach ($defAtts as $akey) {

				if ($value= $atts[$akey]) {
					$str.="<input type='hidden' name='$akey' value='$value'/>";
				}

			}

			if (!$atts["mode"]) {
				$str.="<input type='hidden' name='mode' value='view'/>";
			}



			foreach (explode(" ",$atts["filters"]) as $filter) {
				$str.= $this->addFilter ($filter);
			}

			$id = $this->getQueryStringValue("id")?$this->getQueryStringValue("id"):$this->getPostValue("id");

			if (!$atts["gn_page_id"] && ($id= $_GET["id"]))
				$str.="<input type='hidden' name='gn_page_id' value='$id'/>";

			if (!$atts["page_student_id"] && ($this->dataInterface->contextStudent)) {
				$page_student_id = $this->dataInterface->contextStudent;
				$str.="<input type='hidden' name='page_student_id' value='$page_student_id'/>";

			}


			if ($atts["name"] && !($atts["title"]))
				$str.="<input type='hidden' name='title' value='". $atts["name"]."'/>";



			if ($atts["select"]) {
				$str.="<div id='list-select-form-items'>";

				$str.=$this->addCurrentPostedItems();

				$str.=$this->addCurrentSelectedItems($atts);

				$str.="</div>";
			}

			if ($atts["filters"])
				$str.="<div class='container'><input style='float:left; margin-top:5px' type='submit' value='Update'/></div>";

			$str.="</form>";

			if ($atts["filters"] )
				$str.="</div>";


		return ($str);
	}


/**
 *
 * @param
 * @return
 */

function dataOptions($atts) {
		$str="";

			$str.='<div class="widget_links">';

			$context ="?contextName=".$this->dataInterface->contextType."&contextId=".$this->dataInterface->contextId;

			$str = apply_filters("gn_list_dataOptions", $str);


			/* TO BE MOVED */

			if ($atts["assignservice"])
				$str.='<a class="bulk-referral-link" href="'.$atts["assignservice"] .'">Assign Service</a>';

			if ($atts["assignwcrservice"])
				$str.='<a class="bulk-wcr-referral-link" href="'.$atts["assignwcrservice"] .'">Assign Service</a>';

			/* TO BE MOVED */

			if ($atts["buttons"]) {

				$buttonArray = explode(" ", $atts["buttons"]);

				foreach ($buttonArray as $button) {

					$str.= "<a class='$button carry-context' href='#'>Button</a>";

				}
			}

			if ($atts["modify"])
				$str.='<a class="gn-list-modify-mode" href="#">Edit</a>';


			if ($atts["mode"]=="edit") {
				$str.='<a class="gn-list-modify-cancel" href="#">Cancel</a>';
				$str.='<a class="gn-list-modify-save" href="#">Save</a>';
			}


			if ($atts["addnew"])
				$str.='<a class="gn-addnew" href="'.$atts["addnew"] .$context.'">Add New</a>';




			$str.='</div>';

		return($str);
	}



// ***************
// Pagination

/**
 *
 * @param
 * @return
 */

function writeResultScroller ($totalRows, $recordsPerPage, $currentOffset, $offsetParamName){

	// Called by writePagedResult() to write links to previous and/or next result
	// pages, if required.

	ob_start();


	$url =  $_SERVER["REQUEST_URI"]; //$this->getServerVariable('URL');


	$totalRows=  $totalRows ? $totalRows : $this->foundRows;

	$numPages = $this->roundUp($totalRows / $recordsPerPage);
	$lastOffset = ($numPages-1)*$recordsPerPage;
	$onFirstPage = ($currentOffset == 0) ? true : false;
	$onLastPage = ($currentOffset == $lastOffset) ? true : false;
	$currentPage = ($currentOffset/$recordsPerPage + 1);


	 $this->writeOutput("<div class='resultscroller'>");
	$this->writeScrollerItem("<<",  $this->appendParam($url, $offsetParamName, 0), !$onFirstPage, "First page..."); // "
	$this->writeOutput("&#160;");
	$this->writeScrollerItem("<",  $this->appendParam($url, $offsetParamName, $currentOffset-$recordsPerPage), !$onFirstPage, "Previous page...");
	$this->writeOutput("&#160;");

	$min = max(0, $currentPage-10);
	$max = min($numPages, $currentPage+10);

	//for($i=0; $i<$numPages; ++$i){
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


/**
 *
 * @param
 * @return
 */

function writeScrollerItem($text, $url, $active, $titleText=""){

	// Called by writeResultScroller(). Writes $text as a link or
	// as plain text depending on value of $active.

	if($active)
		$this->writeScrollerLink($text, $url, $titleText);
	else
		$this->writeOutput("<span class='inactive'>".$this->htmlEncode($text)."</span>");
}

/**
 *
 * @param
 * @return
 */

function writeScrollerLink($text, $url, $titleText){

	// Called by writeResultScroller(). Writes hyperlink to $url
	// with "title" attribute of $titleText.

	$this->writeOutput("<a class='list-scroller-page' ");
	if($titleText) $this->writeOutput("title='".$this->htmlEncode($titleText)."' ");
	$this->writeOutput("href='".$url."'>".$this->htmlEncode($text)."</a>");
}


// End pagination
// *********

/**
 *
 * @param
 * @return
 */

function retrieveData ($sql, $countSQL) {


		 if ($this->paginate) {
		 	$offset = $this->getPostValue("offset")?$this->getPostValue("offset"):0;
			$sql.=sprintf(" limit %d,%d", $offset,$this->recordLimit);
			$this->foundRows = $this->data->db->get_var($countSQL);
		 }


		$records= $this->data->get_results($sql);


		 return($records);
	}


/**
 *
 * @param
 * @return
 */

function addDataHeaders($data, $caption='', $atts = array()) {
		$str='';
		$record = $data[0];

		$str.="<thead>";
		if ($caption)
			$str.="<caption>".$caption."</caption>";

		$str.="<tr>";

		if ($atts["select"]) {
					$str.="<th width='10' class='select-list-header'><input type='checkbox' class='list-select-all'/></th>";
		}

			foreach ($record as $key => $value) {
				$key = preg_replace('/^gn_/', '', $key);

				if ($key == "id") {
					$width=" width='10' ";
				}

				if ($value=="Edit") {
					$width="width='10%' ";
				}


				if ($this->showColumnp($key,$atts)) {
					$str.="<th fieldname='$key' $width $class>";
					$str.=strtoupper($key);
					$str.='</th>';
				}

				$width="";
				$class="class='set-sort' ";
			}

		$str.='</tr></thead>';


		return($str);
	}



/**
 *
 * @param
 * @return
 */

function getRowKey($record, $atts=array()) {
		$array = get_object_vars($record);
		$key = $atts["rowkey"]?$atts["rowkey"]:$this->dtaKeyField;

		return ($array[$key]);
	}

/**
 *
 * @param
 * @return
 */

function getNameKey($record, $atts=array()) {
		$array = get_object_vars($record);
		$key = $atts["rowname"]?$atts["rowname"]:$this->dtaNameField;

		return ($array[$key]);
	}





/**
 *
 * @param
 * @return
 */

function showColumnp($key, $atts) {

	$hide = $atts["hide"]?explode(" ",$atts["hide"]):array();

	if ($key =="id") {

		if ($atts["hideid"] || in_array ( strtolower($key) , $hide)) {
			return ( false);
		}
		else {
				return (true);
			}
	}

	else {
		return (! in_array (  strtolower($key) , $hide));

	}

}


/**
 *
 * @param
 * @return
 */

function addDbData($records, $atts) {
		$str='';

		$class = $atts["linkclass"]?$atts["linkclass"]:$atts["key"];

		$str.="<tbody>";
		foreach ($records as $record) {


			$rowkey = $this->getRowKey($record);
			$str.="<tr id='id-$rowkey'>";

			if ($atts["select"]) {
									$str.="<td class='select-list-item'><input type='checkbox' class='select-list-list-item' name='select-list-list-item-$rowkey' value='$rowkey'/></td>";
			}

			$col=0;
			$limit=2;


				foreach ($record as $key => $value) {
				$col++;

					if ($this->showColumnp($key,$atts)) {

						$str.="<td fieldname='$key'  class='$key'>";
							if ($value=="Edit" && $atts["edit"]) {
								$str.='<a href="'.$this->addAttribute($atts["edit"], "id", $rowkey) .'" class="edit_link">Edit</a>';
							}
							else if ($atts["link"] && ($col<=$limit)) {
								$str.='<a '. "class='$class listwidget_anchor edit_link' ".' href="'.$this->addAttribute($atts["link"], "id", $rowkey) .'">'.$value.'</a>';
							}

							else if ($atts["edit"] && $key=="name") {
								$str.='<a href="'.$this->addAttribute($atts["edit"], "id", $rowkey) .'" class="edit_link">'.$value.'</a>';
							}


							else {
								$str.=$value;
							}
						$str.='</td>';
					}
					else {
						$limit++;
					}
				}

			$str.='</tr>';
		}
		$str.="</tbody>";

		return($str);
	}

/**
 *
 * @param
 * @return
 */

function generateSQL($atts) {
	$sql="";

	$sql= $this->data->getListSQL($atts["key"], $atts);

	$defaultTable= $this->data->tablename($atts["key"]);

	$sql.= $this->addFilterSQL($atts, $defaultTable);

	$sql.= $this->data->getGroupBy($atts["key"], $atts);

	$sql.= $this->addSortSQL($atts);


	return ($sql);
}

/**
 *
 * @param
 * @return
 */

function generateCountSQL($atts) {
	$sql="";


	if ($this->data->getGroupBy($atts["key"], $atts)) {
			$ssql =	$this->data->getListSQL($atts["key"], $atts);

			$defaultTable= $this->data->tablename($atts["key"]);

			$ssql.= $this->addFilterSQL($atts, $defaultTable);

			$ssql.= $this->data->getGroupBy($atts["key"], $atts);

			$sql = "select count(*) from ($ssql) derived";

	}
	else {

		$sql= $this->data->getCountSQL($atts["key"], $atts);

		$defaultTable= $this->data->tablename($atts["key"]);


		$sql.=	 $this->addFilterSQL($atts, $defaultTable);

		$sql.= $this->data->getGroupBy($atts["key"], $atts);

	}


	return ($sql);
}


/**
 *
 * @param
 * @return
 */

function generateDataTable ($atts, $inputRecords=false) {
		$sql =$this->generateSQL($atts);
		$countSQL =$this->generateCountSQL($atts);
		$class = $atts["tableclass"]?$atts["tableclass"]:$atts["key"];

		if ($sql ||$inputRecords) {
			$data= $inputRecords?$inputRecords:$this->retrieveData($sql, $countSQL);
			$table="";

			if ($data) {


			$table.="<table class='$class'>";
			$table.=$this->addDataHeaders($data,'', $atts);
			$table.= $this->addDbData($data, $atts);
			$table.='</table>';
			}
			else {
				$table="<table><tr><td><i>No data found</td></tr></table>";

			}


		}
		else {
			$table="<table><tr><td><i>No data definition found for:".$atts["key"]."</td></tr></table>";
		}
		return ($table);
	}

	/**
 *
 * @param
 * @return
 */

function generateDataRunning ($atts) {
			$sql =$this->generateSQL($atts);
			$countSQL =$this->generateCountSQL($atts);

			$list="";

			if ($sql) {
				$records= $this->retrieveData($sql, $countSQL);
				$table="";

				if ($records) {
					foreach ($records as $record) {
						$rowkey = $this->getRowKey($record, $atts);
						$name = $this->getNameKey($record, $atts);

						$list.=$list?", ":"";
						if ($atts["link"])
							$list.='<a href="'.$this->addAttribute($atts["link"], "id", $rowkey) .'" class="edit_link">'.$name.'</a>';
						else
							$list.=$name;

					}
				}
				else {
					$list="<i>No records found</i>";
				}

			}
			else {
				$list="<i>No data definition found for:".$atts["key"]."</i>";
			}

			return ("<p>".$list."</p>");
	}


/**
 *
 * @param
 * @return
 */

function addDefaults($atts) {

	$this->defaultFormValues = array();

	$filters =explode(" ",$atts["filters"]);

	if (in_array("record-status", $filters)) {

		if (!$_POST["record_status"] ) {
			$_POST["record_status"]="no";
		}

		$this->defaultFormValues["record_status"]="no";
	}

}


/**
 *
 * @param
 * @return
 */

function listWidgetHeader($key,$atts,$type, $code="") {

	$this->addDefaults($atts);

	$title = $atts["title"]?$atts["title"]:($atts["name"]?$atts["name"]:"Data Display");

	$class = $atts["key"];

	$class.= $atts["postout"]?" listwidget_postout":"";

		$str="";
		if (!$atts["supress_container"])
			$str.="<div class='widget_container'>";
		$str.="<div class='widget listwidget $class'>
			   <h2 class='widget_title'>$title</h2>";
		$str.=$this->dataOptions($atts);


		$str.= $this->addForm($atts, $code);


		if ($_POST["activeForm"]==$key) {

			$str.= $this->addFormLoader($_POST,$this->formID($atts), true);
		} else {

			$str.= $this->addFormLoader($this->defaultFormValues,"gn-list-form", true);

		}

		if ($atts["fullspan"]) {
			$str.='			<div class="full_span">';
		}

		return($str);

}

/**
 *
 * @param
 * @return
 */

function listWidgetFooter($key,$atts,$type) {

		$str='';

		if ($atts["fullspan"]) {
			$str.='			</div>';
		}


		$str.='	</div>';

		if ($atts["select"]) {

			$str.="<input type='button' class='list-select-reset' value='Clear Selections'/>";
			$str.="<div id='list-select-selected-items'></div>";

		}

		if (($this->paginate) && ($this->foundRows > $this->recordLimit)) {

			$offset = $this->getPostValue("offset")?$this->getPostValue("offset"):0;

			$str.= $this->writeResultScroller ($this->foundRows, $this->recordLimit, $offset, "offset");
		}


		if (!$atts["supress_container"]) {
			$str.="</div>";
		}

		return ($str);

}


/**
 *
 * @param
 * @return
 */

function generateRunningList ($key, $atts, $code,$records=false) {
		$this->paginate=false;
		$str=$this->listWidgetHeader($key,$atts,"running", $code);
					$str.= $this->generateDataRunning($atts);
		$str.=$this->listWidgetFooter($key,$atts,"running");

		return ($str);
	}



/**
 *
 * @param
 * @return
 */

function generateShortList ($key, $atts, $code,$records=false) {

		$this->paginate=false;
		$str=$this->listWidgetHeader($key,$atts,"short", $code);
					$str.= $this->generateDataTable($atts, $records);
		$str.=$this->listWidgetFooter($key,$atts,"short");

		return ($str);
	}


/**
 *
 * @param
 * @return
 */

function generateFullList ($key, $atts, $code, $records=false) {
		$this->recordLimit=$atts["limit"]?$atts["limit"]:15;
		$this->paginate = true;

		$str=$this->listWidgetHeader($key,$atts,"full", $code);
					$str.= $this->generateDataTable($atts, $records);
		$str.=$this->listWidgetFooter($key,$atts,"full");

		return ($str);
	}

/**
 *
 * @param
 * @return
 */

function generateEditList ($key, $atts, $code, $records=false) {

			$atts["form"]=1;
			if ($atts["mode"]=="edit") {
				$atts["fullspan"]=true;
				$atts["running"]=false;
				$atts["modify"]=false;
				$key = $atts["key"]=$atts["select_key"];
				$atts["context_filter"] = $atts["select_context_filter"];
				$this->recordLimit=$atts["limit"]?$atts["limit"]:15;
				$this->paginate = true;
				$type="full";
				$atts["select"] = 1;

			}
			else {
				$key = $atts["key"]=$atts["view_key"];
				$atts["context_filter"] = $atts["view_context_filter"];

				$atts["fullspan"]=false;
				$atts["running"]=true;
				$atts["modify"]=true;
				$this->paginate= false;
				$atts["select"] = 0;
				$type="running";


			}

			$str=$this->listWidgetHeader($key,$atts,$type, $code);

			if ($type=="running") {
				$str.= $this->generateDataRunning($atts);
			}

			else {
				$str.= $this->generateDataTable($atts, $records);
			}

			$str.=$this->listWidgetFooter($key,$atts,$type);

			return ($str);
	}

	/**
 *
 * @param
 * @return
 */

function gn_edit_list($atts, $content, $code) {


			return $this->generateEditList ($atts["key"], $atts, $code);

		}


/**
 *
 * @param
 * @return
 */

function gn_full_list($atts, $content, $code) {
		$atts["fullspan"]=true;
		return $this->generateFullList ($atts["key"], $atts, $code);

	}


/**
 *
 * @param
 * @return
 */

function gn_short_list($atts, $content, $code) {
		$atts["fullspan"]=true;

		return $this->generateShortList ($atts["key"], $atts, $code);

	}



/**
 *
 * @param
 * @return
 */

function gn_running_list($atts, $content, $code) {

		$atts["fullspan"] = false;
		$atts["running"] = true;

		return $this->generateRunningList ($atts["key"], $atts, $code);

	}



/**
 *
 * @param
 * @return
 */

function gn_list ($atts) {

		if ($atts["mode"]=="save") {

			do_action( $atts["save_action"], $atts);

			$atts["mode"] ="view";
		}

		$atts["supress_container"] = true;


		switch ($atts["type"]) {

		case "gn_edit_list":
			return ($this->gn_edit_list($atts, null, "gn_edit_list"));
			break;

		case "gn_running_list":
			return ($this->gn_running_list($atts, null, "gn_running_list"));
			break;



		case "gn_short_list":
			return ($this->gn_short_list($atts, null, "gn_short_list"));
			break;


		case "gn_full_list":
			return ($this->gn_full_list($atts, null, "gn_full_list"));
			break;


		defaut:

			error_log("gn_list: Unknown List Type");
			break;
		}




	}



/**
 *
 * @param
 * @return
 */

function setAjaxHooks() {

		add_action('wp_ajax_gn-get-list-widget', array(&$this, 'ajaxGetListWidget'));


}


/**
 *
 * @param
 * @return
 */

function ajaxGetListWidget () {

		$str= $this->gn_list($_GET);

		echo ($str);

		exit();

}




}


endif;

if(class_exists('gn_ListWidgetClass')) {

	$gn_ListWidget = new gn_ListWidgetClass();

}




?>