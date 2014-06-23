<div style="margin-top: 1em; margin-left: 21px">
<label>Set Status:</label>
<select id="status" name="status">
	<?php $this->writeStatusOptions($task->type, $task->status); ?>
</select>
<label style="display: block">Description of action taken:</label>
<textarea cols="49" rows="4" id="resolution" name="resolution">
<?php $this->writeEncodedValue($taskmeta['resolution']); ?>
</textarea>
</div>