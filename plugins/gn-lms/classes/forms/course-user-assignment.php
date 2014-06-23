<div class="gnlms-form-dialog" data-height="500" title="Register Users for Course" id="gnlms-course-user-assignment" data-form-id="gnlms-course-user-assignment-form">
	<div class="err">
		<?php if($errors = $context['_errors']): ?>
			<p>The following errors have occurred:</p>
			<ul>
			<li><?php echo(implode("</li><li>", $errors)); ?></li>
			</ul>
		<?php endif; ?>
	</div>

<form id="gnlms-course-user-selection" method="GET" class="gnlms_data_form">
<input type="hidden" name="course_id" value="<?php echo $context["course_id"]; ?>"/>
<input type="hidden" name="gnlms_nonce" value="<?php echo $this->nonce; ?>"/>
<input type="hidden" name="action" value="gnlms-course-user-selection"/>
<fieldset>
<legend style="font-weight: bold; margin-bottom: 1em">Search for users to register</legend>
<label style="margin-bottom: 0">Find users whose  
	<select name="search_field">
		<option value="last_name">Last Name</option>
		<option value="company">Organization</option>
	</select>
</label>
<label>starts with <input style="display: inline" type="text" name="q" /></label>

<input type="submit" value="Search"/>
</fieldset>
</form>


<form method="POST" class="gnlms_data_form" id="gnlms-course-user-assignment-form">
	<input type="hidden" name="action" value="gnlms-course-user-assignment"/>
	<input type="hidden" name="course_id" value="<?php echo $context["course_id"]; ?>"/>
	<input type="hidden" name="gnlms_nonce" value="<?php echo $this->nonce; ?>"/>
	<div id="gnlms-found-users">
	</div>
	

</form>
</div>
<p><a href="#" class="gnlms-open-dialog" data-dialogid="gnlms-course-user-assignment">Register new users for this course</a></p>