<?php if($records): 
	global $gnlms; 
	// $selectedCourses = $gnlms->getSelectedCourses(); 
	$userID = get_current_user_id();
?>
<table>
<tr><th>Title</th><th>Status/Action</th></tr>
<?php foreach($records as $course): 
	if($course->course_status) {
		$status = $course->course_status;
		if (!in_array($status, array('Inactive', 'Expired'))) {
			$status = "<a class='gnlms-course-launch' data-course-id='{$course->id}' href='#'>Launch</a>";
		}
	}
	/*
	else if (in_array($course->id, $selectedCourses)) {
		$status = apply_filters("gnlms_selected_course_action_text", "Selected", $course->id, $userID);
	}
	*/
	else {
		$status = apply_filters("gnlms_available_course_action_text", "Available", $course->id, $userID);
	}
?>
		<tr>
			<td class="gnlms-course-title"><a href="<?php echo $gnlms->getCoursePageLink($course->id); ?>"><?php echo htmlspecialchars($course->title); ?></a></td>
			<td><?php echo apply_filters("gnlms_user_course_status_text", $status, $userID, $course); ?></td>
		</tr>
<?php endforeach; ?>
</table>
<?php include("_course_monitor.php"); ?>
<?php else: ?>
	<p>No available courses.</p>
<?php endif; ?>
