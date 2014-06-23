<?php 
$orgID = array_key_exists('organization_id', $context) ? $context['organization_id'] : $_GET['context_id'];
$organization = $this->data->fetchObject("organization", $orgID);
$context["organization"] = $organization['name'];
?>
<form method="POST" id="gnlms-add-edit-subscription-code" class="gnlms_data_form">
<input type="hidden" name="gnlms_data_form" value="subscription_code"/>

<input type="hidden" name="organization_id" value="<?php echo htmlspecialchars($orgID); ?>"/>

<?php if($context['id']): ?>  
	<input type="hidden" name="id" value="<?php echo($context['id']); ?>" />
<?php endif; ?>
<p>Codes can be any combination alpha-numeric characters, underscores, and hyphens, up to 20 characters long; start the code with a reference for the organization such as abbreviated name e.g. "Cambridge_1234".</p>
<?php if($context['_errors']): ?>
	<div class="error">
	<p>The following errors occurred:</p>
	<ul>
		<?php foreach($context['_errors'] as $error): ?>
			<li><?php echo htmlspecialchars($error); ?></li>
		<?php endforeach; ?>
	</ul>
	</div>
<?php endif; ?>
<label><input type="text" disabled="disabled" value="<?php echo htmlspecialchars($context['organization']); ?>"/> Organization</label>
<label><input type="text" maxlength="20" name="code" value="<?php echo htmlspecialchars($context['code']); ?>"/> Code</label>
<label><input type="date" name="expiration_date" value="<?php echo htmlspecialchars($context['expiration_date']); ?>"/> Expiration Date</label>
<label><input type="number" name="user_limit" min="1" value="<?php echo htmlspecialchars($context['user_limit']); ?>"/> User Limit</label>
<label><input type="checkbox" name="record_status" value="1" <?php $this->writeChecked($context['record_status']==1 || !$context['id']);?>/> Active</label>
<input type="submit" value="<?php echo $context['id'] ? 'Update ' : 'Add ' ?> Registration Code"/>
</form>