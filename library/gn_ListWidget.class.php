<?php


if(!class_exists("gn_ListWidgetClass")):


class gn_ListWidgetClass  {
   protected $filename = __FILE__;
   protected static $__CLASS__ = __CLASS__; 



/**
 *
 *
 *
 */


function __construct () {
		$this->initialize();
		
	}

function initialize () {


		$this->initDirLocations(__FILE__);


		// $this->registerScripts();

		$this->initVars();
		// $this->setAjaxHooks();

		
		// DS: Defining shortcodes in subclass
		// $this->addShortCodes();


}

/**
 *
 * @param
 * @return
 */


function initVars () {
		$this->dtaKeyField ="id";
		$this->dtaNameField ="name";
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
			wp_enqueue_script('jquery');
			wp_enqueue_script('gn_list_widget', $this->jsURL."gn_list_widget.js");
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

		default: return (false);
			break;
		}

}

/**
 *
 * @param
 * @return
 */

function addFilterSQL ($atts, $inputDefaultTable="") {
	$sql="";

	$key=$atts["key"];
	$qString ="";

	if ($_POST["activeForm"]==$key) {
	//if ($_POST["activeForm"]==$this->formID($atts)) {
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

		if ($inputDefaultTable) {
			$inputDefaultTable= $inputDefaultTable.".";
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
				$defaultTable=$inputDefaultTable;
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

					default:

						$sql.= apply_filters("gn_list_filter_sql","", $type, $filterKey, $filterValue);
						break;



				}
			}


	}


	return($sql);

}


/**
 *
 * @param
 * @return
 */

function isSort ($name) {

	return (apply_filters("gn_list_is_sort", $name));

}


/**
 *
 * @param
 * @return
 */

function addSortSQL ($atts) {
	$sql="";

	if ($_POST["activeForm"]==$atts["key"]) {

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

			if ($this->issort($sortKey)) {
				$sql.=" order by ". mysql_real_escape_string($sortValue)." ";
			}

			if ( $sql!=""  && $atts["defaultsort"])
					$sql.=", ". $atts["defaultsort"];

		}

		if ($sql=="" && $atts["defaultsort"])
			$sql=" order by ". $atts["defaultsort"];
	}
	else {

		if ($sql=="" && $atts["defaultsort"])
			$sql=" order by ". $atts["defaultsort"];


	}

	return($sql);

}




/**
 *
 * @param
 * @return
 */

function addFilter ($filtername) {

		$str="";

		$str= apply_filters("gn_list_add_filter", $str, $filtername);


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
				if ($records) {
					foreach ($records as $record) {

							if (! is_array($record)) {
								$record=get_object_vars($record);
							}

						$str.=$this->selectedItemHTML($record[$id_field], $record[$name_field]);

					}
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
						"link", "linkclass", "hide","hideid", "buttons", "buttonnames");

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

			$id = strlen(trim($_GET["id"])) ? $_GET["id"] : $_POST['id'];

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
				$str.="<div class='container gnlist-filter-update'><input style='float:left; margin-top:5px;' type='submit' value='Update Filters'/></div>";

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

			$str = apply_filters("gn_list_dataOptions", $str, $atts);



			if ($atts["buttons"]) {

				$buttonArray = explode(" ", $atts["buttons"]);
				$buttonNameArray = explode(" ", $atts["buttonnames"]);
				$i=0;

				foreach ($buttonArray as $button) {
					$buttonname=$buttonNameArray[$i]?$buttonNameArray[$i]:"Button";
					$i++;
					$buttonname = preg_replace('/_/'," ", $buttonname);
					$str.= "<a class='$button carry-context' href='#'>$buttonname</a>";

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
	$this->writeOutput("data-href='".$url."' href='#'>".$this->htmlEncode($text)."</a>");
}


// End pagination
// *********

/**
 *
 * @param
 * @return
 */

function retrieveData ($sql, $countSQL, $atts) {

		// CTW 12-13-2012 added activeForm filter
		 if ($this->paginate ){
		 	$offset = (strlen(trim($_POST["offset"])) && ($_POST["activeForm"]==$atts["key"])) ? $_POST["offset"]:0;
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
		
		$linkKey = $atts["link_key"] ? $atts["link_key"] : "id";

		$str.="<tbody>";
		foreach ($records as $record) {


			$rowkey = $this->getRowKey($record);
			$str.="<tr id='id-$rowkey'>";

			if ($atts["select"]) {
									$str.="<td class='select-list-item'><input type='checkbox' class='select-list-list-item' name='select-list-list-item-$rowkey' value='$rowkey'/></td>";
			}

			$col=0;
			// DS: adding check of 'limit' attribute
			// $limit=2;
			$limit= array_key_exists('limit', $atts) ? $atts['limit'] : 2;
			
			// DS: Adding 'link_fields' attribute to speicfy fields to be linked
			
			$linkFields = $atts['link_fields'] ?  explode(',', $atts['link_fields']) : null;

				foreach ($record as $key => $value) {
				$col++;

					if ($this->showColumnp($key,$atts)) {

						$str.="<td fieldname='$key'  class='$key'>";
							if ($value=="Edit" && $atts["edit"]) {
								$str.='<a href="'.$this->addAttribute($atts["edit"], $linkKey, $rowkey) .'" class="edit_link">Edit</a>';
							}
							// DS: Adding check of link fields
							else if (($linkFields && in_array($key, $linkFields) || !$linkFields) && $atts["link"] && ($col<=$limit)) {
								$str.='<a '. "class='$class listwidget_anchor edit_link' ".' href="'.$this->addAttribute($atts["link"], $linkKey, $rowkey) .'">'.$value.'</a>';
							}

							else if ($atts["edit"] && $key=="name") {
								$str.='<a href="'.$this->addAttribute($atts["edit"], $linkKey, $rowkey) .'" class="edit_link">'.$value.'</a>';
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

	if ($sql) {
		$sql.= $this->addFilterSQL($atts, $defaultTable);

		$sql.= $this->data->getGroupBy($atts["key"], $atts);

		$sql.= $this->addSortSQL($atts);
	}

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

		if ($sql || $inputRecords) {
			// CTW 12-13-2012 added atts to call
			$data= $inputRecords?$inputRecords:$this->retrieveData($sql, $countSQL, $atts);
						
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
			$table="<table><tr><td><i>No data definition found or context query error for:".$atts["key"]."</td></tr></table>";
			error_log("gn_ListWidget caught error creating data query for definition:".$atts["key"]. " at: ". $_SERVER['REQUEST_URI']);
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
				// CTW 12-13-2012 added activeForm filter
				$records= $this->retrieveData($sql, $countSQL, $atts);
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


       function insertJScriptObject($object) {

		$str = $object ? json_encode($object) : "{};";
		echo($str);
	}


function addFormLoader($activeRecord,$formname, $buffer=false) {

	if ($buffer) {
		ob_start();
	}

	if ($activeRecord["id"]) {
		$activeRecord["object_id"] = $activeRecord["id"];

	}

	if ($activeRecord["name"]) {
		$activeRecord["object_name"] = $activeRecord["name"];
	}

	// DS modifications

	echo('<script type="text/javascript">');
	// echo('var formData =');

	echo("if(!window.gn_FormData) var gn_FormData = {};\n");

	echo("gn_FormData['$formname']=");
	echo($this->insertJScriptObject($activeRecord).";");



	echo("</script>");

	if ($buffer) {
		return ( ob_get_clean());
	}

}


function addAttribute($url, $key, $value) {
	if (strpos($url,"?")) {
		return($url."&$key=$value");
	}
	else {
		return($url."?$key=$value");

	}
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

			$str.="<form><input type='button' class='list-select-reset' value='Clear Selections'/></form>";
			$str.="<div id='list-select-selected-items'></div>";

		}

		if (($this->paginate) && ($this->foundRows > $this->recordLimit)) {

			$offset = strlen(trim($_POST["offset"]))?$_POST["offset"]:0;

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

		if (!$atts["type"]) $atts["type"]="gn_running_list";
		$this->paginate=false;
		$str=$this->listWidgetHeader($key,$atts,"running");
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

		if (!$atts["type"]) $atts["type"]="gn_short_list";

		if (!$atts["checkid"] || $_GET["id"]) {

			$this->paginate=false;
			$str=$this->listWidgetHeader($key,$atts,"short");
						$str.= $this->generateDataTable($atts, $records);
			$str.=$this->listWidgetFooter($key,$atts,"short");

			return ($str);
		}
	}


/**
 *
 * @param
 * @return
 */

function generateFullList ($key, $atts, $code, $records=false) {

		if (!$atts["type"]) $atts["type"]="gn_full_list";

		$this->recordLimit=$atts["limit"]?$atts["limit"]:15;
		$this->paginate = true;

		$str=$this->listWidgetHeader($key,$atts,"full");
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

			if (!$atts["type"]) $atts["type"]="gn_edit_list";

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

			$str=$this->listWidgetHeader($key,$atts,$type);

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

			error_log("gnError:gn_list: Unknown List Type");
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



?>