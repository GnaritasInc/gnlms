<tr>
	<th scope="row">LMS Alert Preferences</th>
	<td>
		<?php foreach($this->data->adminAlerts as $key=>$alert):  $metaKey= $this->data->replaceTableRefs("#alert_#$key"); ?>
			<label><input type="checkbox" name="<?php echo $metaKey; ?>" value="1" <?php checked(intval($user->$metaKey), 1); ?>/> <?php echo htmlspecialchars($alert['name']); ?></label><br/>
		<?php endforeach; ?>
	</td>
</tr>