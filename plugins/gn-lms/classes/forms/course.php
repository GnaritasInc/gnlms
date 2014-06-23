<form method="POST" class="gnlms_data_form">
	<input type="hidden" name="gnlms_data_form" value="course"/>
	<input type="hidden" name="_redirect" value="<?php echo $this->getSiteURL(); ?>" />
	<?php if($context['id']): ?>  
		<input type="hidden" name="id" value="<?php echo($context['id']); ?>" />
	<?php endif; ?>
	
	<label>Title<input type="text" name="title" value="<?php echo(htmlspecialchars($context['title'])); ?>"/></label>
	<label>Number/ID<input type="text" name="course_number" value="<?php echo(htmlspecialchars($context['course_number'])); ?>"/></label>
	<label><input type="checkbox" name="record_status" value="1" <?php $this->writeChecked($context['record_status']==1);?>/> Active</label>
	<label>URL<input disabled='disabled' type="text" name="url" size="50" value="<?php echo(htmlspecialchars($context['url'])); ?>"/></label>
	<label for="description">Description</label>
	<textarea name="description" id="description"><?php echo(htmlspecialchars(trim($context['description']))); ?></textarea>
	<label>Version/Update <input type="date" name="last_update" value="<?php echo(htmlspecialchars($context['last_update'])); ?>"/></label>
	<input type="submit" value="<?php echo $context['id'] ? "Update" : "Add" ?> Course"/>
</form>