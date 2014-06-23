<?php if($context["_msg"]): ?>
<div id="message">
<p><?php echo($context["_msg"]); ?></p>
</div>
<?php endif; ?>
<h1><?php echo $context['id'] ? "Edit" : "Add" ?> Course</h1>

<form method="POST" class="gnlms_data_form">
	<input type="hidden" name="gnlms_admin_action" value="add_edit_course"/>
	<?php if($context['id']): ?>  
		<input type="hidden" name="id" value="<?php echo($context['id']); ?>" />
	<?php endif; ?>
	
	<label><input type="text" name="title" value="<?php echo(htmlspecialchars($context['title'])); ?>"/>Title</label>
	<label><input type="text" name="course_number" value="<?php echo(htmlspecialchars($context['course_number'])); ?>"/>Number/ID</label>
	<label><input type="checkbox" name="record_status" value="1" <?php $this->writeChecked($context['record_status']==1);?>/> Active</label>
	<label><input type="text" name="url" size="100" value="<?php echo(htmlspecialchars($context['url'])); ?>"/> URL</label>
	<label for="description">Description</label>
	<textarea name="description" id="description"><?php echo(htmlspecialchars(trim($context['description']))); ?></textarea>
	<label>Version/Update date: <input type="date" name="last_update" value="<?php echo(htmlspecialchars($context['last_update'])); ?>"/></label>
	<input type="submit" value="Submit"/>
</form>