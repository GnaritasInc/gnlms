<form method="POST" class="gnlms_data_form">
	<input type="hidden" name="gnlms_data_form" value="organization"/>
	<?php if($context['id']): ?>  
		<input type="hidden" name="id" value="<?php echo($context['id']); ?>" />
	<?php endif; ?>
	<input type="hidden" name="_redirect" value="/organizations/"/>
	<label><input type="text" name="name" value="<?php echo(htmlspecialchars($context['name'])); ?>"/>Title</label>
	<label><input type="checkbox" name="record_status" value="1" <?php $this->writeChecked($context['record_status']==1 || !$context['id']);?>/> Active</label>
	<input type="submit" value="Submit"/>
</form>