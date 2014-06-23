<fieldset>
<legend>Available Users</legend>
<label class="gn-select-all"><input type="checkbox" class="gn-select-all" data-name="user_ids[]"/> Select All</label>
<?php foreach($context as $user): 
	$id = $user->id;
	// $name = htmlspecialchars($user->name);
	// $company = htmlspecialchars($user->company);
	// $labelText = "$name; $company";
	
	$labelText = htmlspecialchars($user->name);
	
	
?>
<label><input type="checkbox" name="user_ids[]" value="<?php echo $id; ?>"/> <?php echo $labelText; ?></label>
<?php endforeach; ?>

</fieldset>