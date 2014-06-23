<form class="gnlms_report_filter gnlms_data_form" method="GET">
<fieldset>
<legend>Filters</legend>

<?php if($report=='user-registration'): ?>

	<div class="gnlms_range"><label>Registration date between <input type="date" name="start_date" value="{start_date}" /></label> <label>and <input type="date" name="end_date" value="{end_date}"/></label></div>
	<label>
		<select name="organization_id">
			<option value="">All</option>
			<option value="null"<?php echo $_GET['organization_id']=='null' ? " selected='selected'" : ""; ?>>No organization</option>
			<?php foreach($this->data->fetchUserOrganizations() as $org): ?>
			<option value="<?php echo $org->id; ?>"<?php echo $_GET['organization_id']==$org->id ? " selected='selected'" : ""; ?>><?php echo htmlspecialchars($org->name); ?></option>
			<?php endforeach; ?>
		</select>
		<br/>Organization
	</label>
	<label><input type="text" name="regcode" value="{regcode}"/>Registration Code</label>
	<label>
		<select name="sort">
			<?php $this->writeOrderByOptions("user-registration"); ?>	
		</select>
		<br/>Order by
	</label>


<?php elseif($report=="user-activity"): ?>

	<div class="gnlms_range"><label>Date between <input type="date" name="start_date" value="{start_date}" /></label> <label>and <input type="date" name="end_date" value="{end_date}"/></label></div>
	<label><input type="text" name="email" value="{email}" />Email contains</label>
	<label>
		<select name="organization_id">
			<option value="">All</option>
			<option value="null"<?php echo $_GET['organization_id']=='null' ? " selected='selected'" : ""; ?>>No organization</option>
			<?php foreach($this->data->fetchUserActivityOrganizations() as $org): ?>
			<option value="<?php echo $org->id; ?>"<?php echo $_GET['organization_id']==$org->id ? " selected='selected'" : ""; ?>><?php echo htmlspecialchars($org->name); ?></option>
			<?php endforeach; ?>
		</select>
		<br/>Organization
	</label>
	<label>
		<select name="event_type">
			<option value="">All</option>
			<?php foreach($this->courseEventTypes as $type): ?>
			<option<?php echo($_GET[event_type]==$type ? " selected='selected'" : ""); ?>><?php echo($type); ?></option>
			<?php endforeach; ?>
		</select>
		<br/>Activity Type
	</label>
	<label>
		<select name="sort">
			<?php $this->writeOrderByOptions("user-activity"); ?>	
		</select>
		<br/>Order by
	</label>
	
<?php elseif($report=="assessment-responses"): ?>
	<div class="gnlms_range"><label>Date between <input type="date" name="start_date" value="{start_date}" /></label> <label>and <input type="date" name="end_date" value="{end_date}"/></label></div>
	<label><input type="text" name="email" value="{email}" />Email contains</label>
	<label>
		<select name="course_id">
			<?php foreach($this->data->fetchAssessmentCourses() as $course): ?>
			<option value="<?php echo $course->id; ?>"<?php echo($course->id==$_GET['course_id'] ? ' selected="selected"' : "");  ?>><?php echo htmlspecialchars($course->title); ?></option>
			<?php endforeach; ?>
		</select>
		<br/>Course
	</label>
	<label>
		<select name="sort">
			<?php $this->writeOrderByOptions($report); ?>	
		</select>
		<br/>Order by
	</label>	
<?php elseif($report=="assessment-summary"): ?>
	<div class="gnlms_range"><label>Date between <input type="date" name="start_date" value="{start_date}" /></label> <label>and <input type="date" name="end_date" value="{end_date}"/></label></div>
	<label>
		<select name="course_id">
			<?php foreach($this->data->fetchAssessmentCourses() as $course): ?>
			<option value="<?php echo $course->id; ?>"<?php echo($course->id==$_GET['course_id'] ? ' selected="selected"' : "");  ?>><?php echo htmlspecialchars($course->title); ?></option>
			<?php endforeach; ?>
		</select>
		<br/>Course
	</label>
	
<?php elseif($report=="course-completion"): ?>
	<div class="gnlms_range"><label>Date between <input type="date" name="start_date" value="{start_date}" /></label> <label>and <input type="date" name="end_date" value="{end_date}"/></label></div>
	<label><input type="text" name="email" value="{email}" />Email contains</label>
	<label>
		<select name="organization_id">
			<option value="">All</option>
			<option value="null"<?php echo $_GET['organization_id']=='null' ? " selected='selected'" : ""; ?>>No organization</option>
			<?php foreach($this->data->fetchCourseCompletionOrganizations() as $org): ?>
			<option value="<?php echo $org->id; ?>"<?php echo $_GET['organization_id']==$org->id ? " selected='selected'" : ""; ?>><?php echo htmlspecialchars($org->name); ?></option>
			<?php endforeach; ?>
		</select>
		<br/>Organization
	</label>
	<label>
		<select name="course_id">
			<option value="">All</option>
			<?php foreach($this->data->fetchCompletedCourses() as $course): ?>
			<option value="<?php echo $course->id; ?>"<?php echo($course->id==$_GET['course_id'] ? ' selected="selected"' : "");  ?>><?php echo htmlspecialchars($course->title); ?></option>
			<?php endforeach; ?>
		</select>
		<br/>Course
	</label>			
	<label>
		<select name="sort">
			<?php $this->writeOrderByOptions("course-completion"); ?>	
		</select>
		<br/>Order by
	</label>

<?php endif; ?>


<input type="submit" name="cmd" value="Submit" /> <input type="submit" name="gnlms_csv_output" value="Export CSV"/>
</fieldset>
</form>