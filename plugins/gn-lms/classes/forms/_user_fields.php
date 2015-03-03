<?php if($context["_is_profile"]): ?><h2>My Profile</h2><?php endif; ?>

<div id="gnLMSUser">
<?php


	if ($context["_is_registration"]) {
?>
<p><label id="registration_code"><span>*Registration Code: </span><input type="text" name="registration_code" value="<?php echo(htmlspecialchars($context['registration_code'])); ?>" /></label></p>

<?php
}
?>

<p><label><span>*Last Name:</span> <input type="text" name="last_name" value="<?php echo(htmlspecialchars($context['last_name'])); ?>" /></label></p>
<p><label><span>*First Name:</span> <input type="text" name="first_name" value="<?php echo(htmlspecialchars($context['first_name'])); ?>" /></label></p>

<?php if (!$context["_is_registration"]) { ?>
<p><label><span>*Email:</span> <input autocomplete="off" type="text" name="email" value="<?php echo(htmlspecialchars($context['email'])); ?>" /></label></p>

<p><label><span>Password:</span><input autocomplete="off" name="password" type="password" value=""/></label></p>
<p><label><span>Confirm Password:</span> <input autocomplete="off" name="password" type="password" value=""/></label></p>


<p><label><span>Organization:</span> <br/>
	<select name="organization_id">
	<option value="">N/A</option>
	<?php foreach($this->data->retrieveSelectOptions('organization') as $organization): ?>
		<?php $id = $organization->id; $name = $organization->name; $selected = ($id==$context['organization_id']) ? " selected='selected'" : ""; ?>
		<option value="<?php echo $id ?>"<?php echo $selected; ?>><?php echo(htmlspecialchars($name)); ?></option>
	<?php endforeach; ?>
	</select></label></p>
<?php } ?>

<p><label><span>Title:</span> <input type="text" name="title" value="<?php echo(htmlspecialchars($context['title'])); ?>"/></label></p>
<!-- <p><label><span>Role:</span> <input type="text" name="role" value="<?php echo(htmlspecialchars($context['role'])); ?>"/></label></p> -->
<p><label><span>Phone:</span> <input type="text" name="phone" value="<?php echo(htmlspecialchars($context['phone'])); ?>"/></label></p>
<p><label><span>Address 1:</span> <input type="text" name="address_1" value="<?php echo(htmlspecialchars($context['address_1'])); ?>"/></label></p>
<p><label><span>Address 2:</span> <input type="text" name="address_2" value="<?php echo(htmlspecialchars($context['address_2'])); ?>"/></label></p>
<p><label><span>City:</span> <input type="text" name="city" value="<?php echo(htmlspecialchars($context['city'])); ?>"/></label></p>
<!-- <p><label><span>State:</span> <input type="text" name="state" value="<?php echo(htmlspecialchars($context['state'])); ?>"/></label></p> -->
<p><label><span>State:</span> <?php if(!$context["_is_registration"]): ?><br/><?php endif; ?>
<select name="state">
<option value=""></option>
<?php foreach($this->states as $state): ?>
	<option value="<?php echo $state[0]; ?>"<?php echo ($context['state']==$state[0]) ? " selected='selected'" : ""?>><?php echo $state[1]; ?></option>
<?php endforeach; ?>
</select>
</label></p>
<p><label><span>Zip:</span><input type="text" name="zip" value="<?php echo(htmlspecialchars($context['zip'])); ?>"/></label></p>
<p><label><span>Country:</span> <input type="text" name="country" value="<?php echo(htmlspecialchars($context['country'])); ?>"/></label></p>

</div>