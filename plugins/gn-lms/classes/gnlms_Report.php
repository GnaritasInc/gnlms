<?php

class gnlms_Report {
	
	var $courseEventTypes = array("Registered", "Started", "Accessed", "Completed");
	
	function __construct () {
		$this->data = true;
		$this->data = new gnlms_ReportData();
		
		add_shortcode("gnlms_report", array(&$this, 'doReport'));
		add_action('init', array(&$this, 'controller'));
	}
	
	function controller () {
		if($_GET['reportname'] && $_GET['gnlms_csv_output']) {
			$this->doCSVReport();
		}
	}
	
	function writeReportTitle ($report) {
		$titles = array(
			"user-registration"=>"User Registration",
			"user-activity"=>"User Activity",
			"assessment-responses"=>"Course Post-Test: User Responses",
			"assessment-summary"=>"Course Post-Test: Course Summary",
			"course-completion"=>"Course Completion"
		);
		$title = $titles[$report];
		echo("<h2>$title</h2>");
	}
	
	function doReport ($atts) {
		// $report = $_GET['reportname'];
		$report = $atts['name'];
		ob_start();
		$this->writeReportTitle($report);
		include(dirname(__FILE__)."/templates/report-filters.php");
		$content = ob_get_clean();
		
		foreach($_GET as $key=>$value) {
			$content = str_replace("{".$key."}", htmlspecialchars($value), $content);
		}
		
		$content = preg_replace('/{[a-z_]+}/i', "", $content);
		
		if ($_GET['cmd']) {			
			$errors = $this->checkInput($report);
			if(!$errors) $records = $this->data->fetchReportData($report);
			ob_start();
			include(dirname(__FILE__)."/templates/report-data.php");
			$content .= ob_get_clean();
		}
		
		return $content;
	}
	
	function doCSVReport () {
		$report = $_GET['reportname'];
		$csvWriter = new gn_CSVWriter();
		$records = $this->data->fetchReportData($report);
		
		$csvWriter->doCSVResponse($report, $records);
	}
	
	function checkInput ($report) {
		if($report=='assessment-responses' || $report=='assessment-summary') {
			if(!strlen(trim($_GET['course_id']))) {
				return array("Please select a course.");
			}
			else return array();
		}

		else return array();
	}
	
	function writeOrderByOptions ($report, $formKey="sort") {
		foreach($this->data->dataDefinitions[$report]["orderBy"] as $key=>$value) {
			$selected = ($_GET[$formKey]==$key) ? " selected='selected'" : "";
			$text = $value[1];
			
			echo("<option value='$key' $selected>$text</option>");
		}
	}	

}


?>