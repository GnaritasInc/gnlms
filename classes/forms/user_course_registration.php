<form method="POST" class="gnlms_data_form">
<input type="hidden" name="action" value="gnlms-edit-user-course-registration"/>
<input type="hidden" name="gnlms_nonce" value="<?php echo $this->nonce; ?>"/>
<input type="hidden" name="id" value="<?php echo($context['id']); ?>" />


<label><input type="checkbox" name="record_status" value="1" <?php $this->writeChecked($context['record_status']==1);?>/> Active</label>
<label><input type="date" name="expiration_date" value="<?php echo($context['expiration_date']); ?>"/> Expiration Date</label>
<label><input type="date" name="course_completion_date" value="<?php echo($context['course_completion_date']); ?>"/> Completion Date</label>
<label>
<select name="course_status">
<?php foreach($this->data->courseStatusOptions as $status): ?>
<option<?php echo(($status==$context['course_status']) ? " selected='selected'" : ""); ?>><?php echo($status); ?></option>
<?php endforeach; ?>
</select>
</label>
<label>SCORM Data<br/>
<textarea name="scormdata">
<?php echo htmlspecialchars($context['scormdata']); ?>
</textarea>
</label>

</form>