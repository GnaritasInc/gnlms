<?php

class gnlms_ReportData extends gn_PluginDB {

	function __construct() {
		parent::__construct("gnlms");

		$this->dataDefinitions = array(


			"course-activity" => array(
				"title"=>"Course Activity",
				"columns"=>"u.email, u.last_name as 'Last Name', u.first_name as 'First Name', o.name as 'Organization', c.title as 'Course', uce.event_date as 'Date', uce.event_type as 'Activity Type'",
				"tableExpr"=>"#user_course_event# uce inner join #user# u on u.id=uce.user_id inner join #course# c on c.id=uce.course_id left join #organization# o on o.id=u.organization_id",
				"filters"=>array(
					"_between"=>array("date(uce.event_date)", "start_date", "end_date"),
					"_contains"=>array("u.email", "email"),
					"o.id"=>"organization_id",
					"uce.event_type"=>"event_type",
					"u.test"=>"test"
				),
				"orderBy"=>array(
					"event_date"=>array("uce.event_date desc", "Event Date (desc)"),
					"last_name"=>array("u.last_name", "Last Name"),
					"org"=>array("o.name", "Organization"),
					"course"=>array("c.title", "Course"),
					"event_type"=>array('uce.event_type', 'Activity Type')
				)

			),

			"assessment-responses" => array (
				"title"=>"Course Post-Test: User Responses",
				"columns" => "u.email as 'Email', u.last_name as 'Last Name', u.first_name as 'First Name', ucar.response_date as 'Date', ucar.score as 'Score', case ucar.result when 1 then 'P' else 'F' end as 'Pass/Fail'",
				"tableExpr" => "#user# u inner join #user_course_assessment_response# ucar on u.id=ucar.user_id inner join #course_assessment# ca on ca.course_id=ucar.course_id and ca.name=ucar.name",
				"filters" => array(
					"_contains"=>array("u.email", "email"),
					"_between"=>array("ucar.response_date", "start_date", "end_date"),
					"ca.course_id"=>"course_id",
					"u.test"=>"test"
				),
				"orderBy"=>array(
					"response_date"=>array("ucar.response_date desc", "Date (desc)"),
					"last_name"=>array("u.last_name", "Last Name"),
					"score"=>array('ucar.score', 'Score'),
					"result"=>array("ucar.result", "Pass/Fail")
				)
			),
			"assessment-summary" => array(
				"title"=>"Course Post-Test: Course Summary",
				"columns"=>"caq.sequence, caq.text, caq.correct_answer",
				"tableExpr"=>"#course_assessment_question# caq inner join #course_assessment# ca on ca.id=caq.course_assessment_id",
				"filters" => array (
					"ca.course_id"=>"course_id"
				),
				"orderBy"=>"caq.sequence"
			),
			"course-completion"=>array(
				"title"=>"Course Completion",
				"columns"=>"u.email as 'Email', u.last_name as 'Last Name', u.first_name as 'First Name', o.name as 'Organization', c.title as 'Course', ucr.course_completion_date as 'Date', case when ucr.course_status='Completed' then 'Pass' else 'Fail' end as 'Result'",
				"tableExpr"=>"#user# u inner join #user_course_registration# ucr on u.id=ucr.user_id and (ucr.course_status='Completed' or ucr.course_status='Failed') inner join #course# c on c.id=ucr.course_id left join #organization# o on o.id=u.organization_id",
				"filters"=>array(
					"_between"=>array("ucr.course_completion_date", "start_date", "end_date"),
					"_contains"=>array("u.email", "email"),
					"o.id"=>"organization_id",
					"ucr.course_id"=>"course_id",
					"u.test"=>"test"
				),
				"orderBy"=>array(
					"date"=>array("ucr.course_completion_date desc", "Date (desc)"),
					"last_name"=>array("u.last_name", "Last Name"),
					"org"=>array("o.name", "Organization"),
					"course"=>array("c.title", "Course"),
					"result"=>array("case when ucr.score > 70 then 1 else 0 end", "Result")
				)
			),
			"evaluation-data"=>array(
				"title"=>"Evaluation Data",
				"columns"=>"u.first_name as 'First Name', u.last_name as 'Last Name', u.title as 'Title', c.title as 'course', er.response_date as 'Comment Date', er.q1 as 'Q1', er.q2 as 'Q2', er.q3 as 'Q3', er.q4 as 'Q4', er.q5 as 'Q5', er.q6 as 'Q6', er.q7 as 'Q7', er.q8 as 'Q8', er.q9 as 'Q9', er.q10 as 'Q10', er.q11 as 'Q11', er.q12 as 'Q12', er.q13 as 'Q13', er.q14 as 'Q14', er.q15 as 'Q15', er.q16 as 'Q16', er.q17 as 'Q17', er.q18 as 'Q18', er.q19 as 'Q19', er.q20 as 'Q20'",
				"tableExpr"=>"#evaluation_response# er inner join #user# u on u.id=er.user_id inner join #course# c on c.id=er.course_id",
				"filters"=>array(
					"_between"=>array("date(er.response_date)", "start_date", "end_date"),
					"_contains"=>array("u.last_name", "last_name"),
					"c.id"=>"course_id"
				),
				"orderBy"=>"er.response_date desc"
			)
		);
	}
	
	function getReportDefinition ($name) {
		return apply_filters('gnlms_report_definition', $this->dataDefinitions[$name], $name);
	}

	function getReportSQL ($name) {
		if(!$reportSpec = $this->getReportDefinition($name)) {
			return "";
		}

		$sql = "select ".$reportSpec["columns"];
		$sql .= " from ".$reportSpec["tableExpr"];
		$sql .= " where ".$this->getFilterSQL($reportSpec["filters"]);

		if($reportSpec['groupBy']) {
			$sql .= " group by ".$reportSpec['groupBy'];
		}

		if ($reportSpec['having']) {
			$sql .= " having ". $this->getFilterSQL($reportSpec["having"]);
		}

		if(is_array($reportSpec['orderBy'])) {
			if(array_key_exists($_GET['sort'], $reportSpec['orderBy'])) {
				$sql .= " order by ".$reportSpec['orderBy'][$_GET['sort']][0];
			}
		}
		else {
			$sql .= " order by ".$reportSpec['orderBy'];
		}

		$sql = apply_filters('gnlms_report_sql', $this->replaceTableRefs($sql), $name);

		error_log("Report SQL: $sql");
		return $sql;
	}

	function escapePattern ($str) {
		$str = preg_replace('/[%_]/', "\\$0", $str);
		return $str;
	}

	function getFilterSQL ($filters) {
		$conditions = array("1=1");

		foreach($filters as $key=>$value) {

			if($key == "_between") {
				if(strlen($_GET[$value[1]]) && strlen($_GET[$value[2]])) {
					$conditions[] = $value[0]." between ".$this->quoteString($_GET[$value[1]])." and ".$this->quoteString($_GET[$value[2]]);
				}
			}
			else if ($key == "_contains") {
				if(strlen($_GET[$value[1]])) {
					$conditions[] = $value[0]." like ".$this->quoteString("%".$this->escapePattern($_GET[$value[1]])."%");
				}
			}
			else if($_GET[$value] == "null") {
				$conditions[] = "$key is null";
			}
			else if (strlen($_GET[$value])) {
				$conditions[] = $key."=".$this->quoteString($_GET[$value]);
			}
		}

		return implode(" and ", $conditions);
	}

	function fetchReportData ($name) {
		if($name == "assessment-responses") {
			return $this->fetchAssessmentResponseData();
		}
		else if($sql = $this->getReportSQL($name)) {
			// return $this->db->get_results($sql, ARRAY_A);
			return $this->getLocalTimeResults($sql, ARRAY_A);
		}
		else error_log("No report definition for ".htmlspecialchars($name));
	}

	function fetchAssessmentResponseData () {
		$questions = $this->fetchAssessmentQuestions($_GET['course_id']);
		$questionCols = array();
		foreach($questions as $question) {
			$sequence = $question->sequence;
			$questionCols[] = "q".$sequence."_response as 'Q$sequence'";
			$questionCols[] = "q".$sequence."_result";
		}

		
		// $this->dataDefinitions["assessment-responses"]["columns"] .= ", ".implode(", ", $questionCols);
		
		$reportDef = $this->getReportDefinition("assessment-responses");
		$reportDef["columns"] .= ", ".implode(", ", $questionCols);

		$sql = $this->getReportSQL("assessment-responses");

		// return $this->db->get_results($sql, ARRAY_A);

		$sql = $this->replaceTableRefs($sql);

		return $this->getLocalTimeResults($sql, ARRAY_A);
	}

	function fetchAssessmentQuestions ($courseID) {
		$sql = "select caq.* from #course_assessment# ca inner join #course_assessment_question# caq on ca.id=caq.course_assessment_id where ca.course_id=%d order by caq.course_assessment_id, caq.sequence";
		$sql = $this->replaceTableRefs($sql);
		$sql = $this->db->prepare($sql, $courseID);
		error_log("Question sql: $sql");
		return $this->db->get_results($sql);
	}

	function fetchAssessmentCourses () {
		//$sql = "select c.id, c.title from #course# c inner join #course_assessment# ca on c.id=ca.course_id order by c.title";
		$sql = "select id, title from #course#  where id in (select course_id from #course_assessment#) order by title";
		$sql = $this->replaceTableRefs($sql);
		return $this->db->get_results($sql);
	}
	
	function fetchEvaluationCourses () {
		$sql = "select id, title from #course#  where id in (select course_id from #evaluation_response#) order by title";
		$sql = $this->replaceTableRefs($sql);
		return $this->db->get_results($sql);
	}

	function fetchCompletedCourses () {

		// DS: Changing to return all courses
		// $sql = "select id, title from #course#";
		// $sql .= " where id in (select course_id from #user_course_registration# where course_status='Completed')";

		$sql = "select id, title from #course# where record_status=1";
		$sql = $this->replaceTableRefs($sql);
		return $this->db->get_results($sql);
	}
	


	function fetchMaxAssessmentAnswers ($courseID) {
		$sql = "select max(a.sequence)";
		$sql .= " from #course_assessment_answer# a inner join #course_assessment_question# q on q.id=a.question_id inner join #course_assessment# ca on ca.id=q.course_assessment_id";
		$sql .= " where ca.course_id=%d";

		$sql = $this->replaceTableRefs($sql);
		return $this->db->get_var($this->db->prepare($sql, $courseID));
	}

	function fetchQuestionAssessmentQuestionData ($question, $maxAnswers) {
		/* Example:
			select avg(a1), avg(a2), avg(a3), avg(a4)
			from
			(SELECT 1 as 'sequence', ur.course_id, ur.name,
			case ur.q1_response when '1' then 1 else 0 end as 'a1',
			case ur.q1_response when '2' then 1 else 0 end as 'a2',
			case ur.q1_response when '3' then 1 else 0 end as 'a3',
			case ur.q1_response when '4' then 1 else 0 end as 'a4'

			FROM #user_course_assessment_response# ur
			inner join #course_assessment# ca on ca.course_id=ur.course_id and ca.name=ur.name
			where ca.course_id=14
			) t1
		*/

		$aggregateCols = array();
		$caseCols = array();
		$filters = array (
			"ca.course_id"=>"course_id",
			"_between"=>array("ur.response_date", "start_date", "end_date")
		);


		for($i=1; $i<=$maxAnswers; ++$i) {
			$aggregateCols[] = "avg(a$i)";
			$caseCols[] = "case ur.q%1\$d_response when '$i' then 1 else 0 end as 'a$i'";
		}

		$sql = " select ".implode(", ", $aggregateCols);
		$sql .= " from";
		$sql .= " (SELECT %1\$d as 'sequence', ur.course_id, ur.name, ";

		$sql .= implode(", ", $caseCols);

		$sql .= " FROM #user_course_assessment_response# ur";
		$sql .= " inner join #course_assessment# ca on ca.course_id=ur.course_id and ca.name=ur.name";
		// $sql .= " where ca.course_id=%2\$d";

		$sql .= " where ".$this->getFilterSQL($filters);

		$sql .= " ) t1";

		$sql = $this->replaceTableRefs($sql);
		error_log("Question data sql (before prepare): $sql");

		$sql = $this->db->prepare($sql, $question);

		error_log("Question data sql: $sql");

		return $this->db->get_row($sql, ARRAY_N);
	}




}

?>
