<label>Course Status: 
<select name="course_status">
<option value="">Any</option>
<?php foreach($this->data->courseStatusOptions as $status): ?>
<option><?php echo $status; ?></option>
<?php endforeach; ?>
</select>
</label>