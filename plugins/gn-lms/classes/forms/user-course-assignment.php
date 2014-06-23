<div class="gnlms-form-dialog" title="Register User for Courses" id="gnlms-user-course-assignment">
<h2>Available courses for <?php echo htmlspecialchars($context["user"]["first_name"]." ".$context["user"]["last_name"]); ?></h2>
	<div class="err">
		<?php if($errors = $context['_errors']): ?>
			<p>The following errors have occurred:</p>
			<ul>
			<li><?php echo(implode("</li><li>", $errors)); ?></li>
			</ul>
		<?php endif; ?>
	</div>

<form method="POST" class="gnlms_data_form">
	<input type="hidden" name="action" value="gnlms-user-course-assignment"/>
	<input type="hidden" name="user_id" value="<?php echo $context["user_id"]; ?>"/>
	<input type="hidden" name="gnlms_nonce" value="<?php echo $this->nonce; ?>"/>
	<label for="course_ids">Choose Courses</label>
	<select multiple="multiple" id="course_ids" name="course_ids[]">
		<?php foreach($context["courses"] as $course): ?>
		<option value="<?php echo $course->id; ?>"><?php echo htmlspecialchars($course->title); ?></option>
		<?php endforeach; ?>
	</select>
</form>
</div>
<?php if($_GET['id']): ?><p><a href="#" class="gnlms-open-dialog" data-dialogid="gnlms-user-course-assignment">Register this user for new courses</a></p> <?php endif; ?>
