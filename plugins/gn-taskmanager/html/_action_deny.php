	<div><input type="radio" id="admin-action-deny" name="admin-action" value="deny" /><label for="admin-action-deny">Deny this request.</label></div>
	<div style="margin-left: 21px">
		<label style="display: block">Reason for denial:</label>
		<textarea cols="49" rows="4" id="deny-reason" name="deny-reason"><?php $this->writeEncodedValue($taskmeta['deny-reason']); ?></textarea>
	</div>
