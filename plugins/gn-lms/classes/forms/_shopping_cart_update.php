
<form method="POST" class="gnlms_data_form shopping_cart_update">
	<?php if(!$atts['ajax']): ?> <input type="hidden" name="gnlms_data_form" value="shopping_cart_update"/> <?php endif; ?>
	<input type="hidden" name="course_id" value="<?php echo $id; ?>" />
	<input type="hidden" name="action" value="<?php echo $action; ?>" />
	<input type="hidden" name="gnlms_nonce" value="<?php echo $this->nonce; ?>"/>
	<input type="submit" value="<?php echo $actionText; ?>" />
</form>
