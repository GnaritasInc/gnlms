<?php if($atts['code']=="gnlms_course_detail"): 
	
	$isSelected = in_array($context['id'], $this->getSelectedCourses());
	$action = $isSelected ? "gnlms_shopping_cart_remove" : "gnlms_shopping_cart_add";
	$actionText = $isSelected ? "Remove from Shopping Cart" : "Add to Shopping Cart";
	$id = $context['id'];
?>

<h2><?php echo $atts['title']; ?></h2>
<div class="gnlms-course-detail full_span">
<?php if($msg=trim($_GET['msg'])): ?><p class="gnlms-msg"><?php echo htmlspecialchars($msg); ?></p><?php endif; ?>
<h3><?php echo htmlspecialchars($context['title']); ?></h3>
<p><?php echo htmlspecialchars(trim($context['description'])); ?></p>

<?php include("_shopping_cart_update.php"); ?>

</div>

<?php else: ?>

<form method="POST" class="gnlms_data_form">
	<input type="hidden" name="gnlms_data_form" value="course"/>
	<input type="hidden" name="_redirect" value="/" />
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

<?php endif; ?>