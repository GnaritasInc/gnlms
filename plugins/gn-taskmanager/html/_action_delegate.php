	<div>
	<input type="radio" id="admin-action-delegate" name="admin-action" value="delegate" /><label for="admin-action-delegate">Claim Task or Delegate to another administrator:</label>
	<select name="admin_id">
	<option value="">Choose administrator</option>
	<?php $ccnx_DataInterface->listOptions("administrator_user"); ?>
	</select>
	</div>
