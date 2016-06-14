<h1><?php echo $context['id'] ? "Edit" : "Add" ?> Course</h1>

<?php if($context["_msg"]): ?>
	<div id="message" class="updated">
	<p><?php echo($context["_msg"]); ?></p>
	</div>
<?php endif; ?>

<form method="POST" class="gnlms_data_form">
	<input type="hidden" name="gnlms_admin_action" value="add_edit_course"/>
	<?php if($context['id']): ?>  
		<input type="hidden" name="id" value="<?php echo($context['id']); ?>" />
	<?php endif; ?>
	
	<label><input type="text" name="title" value="<?php echo(htmlspecialchars($context['title'])); ?>"/>Title</label>
	<label><input type="text" name="course_number" value="<?php echo(htmlspecialchars($context['course_number'])); ?>"/>Number/ID</label>
	<label><input type="checkbox" name="record_status" value="1" <?php $this->writeChecked($context['record_status']==1);?>/> Active</label>
	<label><input type="text" name="url" size="100" value="<?php echo(htmlspecialchars($context['url'])); ?>"/> URL</label>
	<label>Credit<input type="text" name="credit" value="<?php echo $context['credit']; ?>"/></label>
	<label>Certificate<input type="text" name="certificate" size="50" value="<?php echo $context['certificate']; ?>"/></label>
	<label>Image<input type="text" name="image" size="50" value="<?php echo $context['image']; ?>"/></label>
	<label for="description">Description</label>
	<?php wp_editor(trim($context['description']), "description", array("media_buttons"=>false, "teeny"=>true)); ?>
	<label>Version/Update date: <input type="date" name="last_update" value="<?php echo(htmlspecialchars($context['last_update'])); ?>"/></label>
	<input type="submit" value="<?php echo $context['id'] ? "Update" : "Add" ?> Course"/>
</form>

<?php 

echo do_shortcode('[gnlms_course_user_list title="Registered Users" link="'.admin_url("user-edit.php").'" link_key="user_id" filters="course_status course-user-sort"]');
echo do_shortcode('[gnlms_data_support_form name="course-user-assignment"]');
?>