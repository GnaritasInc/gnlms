<?php if($records):?>
	<table>
	<tr><th>CODE</th><th>COURSE</th><th>SUBSCRIPTION PERIOD</th><th>EDIT</th></tr>

	<?php foreach($records as $record): ?>
	<?php $id=$record->id; ?>
	<tr>
		<td><?php echo htmlspecialchars($record->subscription_code); ?></td>
		<td><?php echo htmlspecialchars($record->course); ?></td>
		<td><?php echo htmlspecialchars($record->subscription_period); ?></td>
		<td><a href="#" class="gnlms-open-dialog-form" data-dialogid="gnlms-edit-subscription-code-course" data-type="subscription_code_course" data-id="<?php echo $id; ?>">Edit</a></td>
	</tr>
	<?php endforeach; ?>

	</table>
<?php elseif($_GET['id']): ?>
	<p>No courses.</p>
<?php else: ?>
	<p>Courses can be added after the registration code is created.</p>
<?php endif; ?>

<?php if($_GET['id']): ?><p><a class="gnlms-button gnlms-open-dialog-form" href="#" data-dialogid="gnlms-edit-subscription-code-course" data-type="subscription_code_course">Add Course</a></p> <?php endif; ?>
<div id="gnlms-edit-subscription-code-course" class="gnlms-form-dialog" title="Add Course"> </div>