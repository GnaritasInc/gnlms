<form method="POST" class="gnlms_data_form">
<input type="hidden" name="gnlms_data_form" value="checkout"/>
<?php foreach($selectedCourses as $course): ?>
	<input type="hidden" name="course_id[]" value="<?php echo $course->id; ?>"/>
<?php endforeach; ?>
<input type="submit" value="Register"/>
</form>