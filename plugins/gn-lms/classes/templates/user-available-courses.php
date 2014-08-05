<?php if($records): global $gnlms; $selectedCourses = $gnlms->getSelectedCourses(); ?>
<table>
<tr><th>Title</th><th>Status</th></tr>
<?php foreach($records as $course): 
	if($course->course_status) {
		$status = $course->course_status;
	}
	else if (in_array($course->id, $selectedCourses)) {
		$status = "Selected";
	}
	else {
		$status = "Available";
	}
?>
		<tr>
			<td class="gnlms-course-title"><a href="/course-detail/?id=<?php echo $course->id; ?>"><?php echo htmlspecialchars($course->title); ?></a></td>
			<td><?php echo $status; ?></td>
		</tr>
<?php endforeach; ?>
</table>
<?php else: ?>
	<p>No available courses.</p>
<?php endif; ?>
