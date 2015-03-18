<form method="POST" class="gnlms_data_form" autocomplete="off">
	<div class="err">
		<?php if($errors = $context['_errors']): ?>
			<p>The following errors have occurred:</p>
			<ul>
			<li><?php echo(implode("</li><li>", $errors)); ?></li>
			</ul>
		<?php endif; ?>
	</div>
	<input type="hidden" name="gnlms_data_form" value="user"/>
	<input type="hidden" name="_redirect" value="/users/" />
	<?php if($context['id']): ?>
		<input type="hidden" name="id" value="<?php echo($context['id']); ?>" />
	<?php endif; ?>

	<?php include("_user_fields.php");?>

	<input type="submit" value="Submit"/>
</form>