<?php if($records):

$today = strtotime("now")

?>
<table>
<tr><th>ID</th><th>CODE</th><th>EXPIRATION DATE</th><th>USER LIMIT</th><th>RECORD STATUS</th><th>EDIT</th></tr>

<?php foreach($records as $record): ?>
<?php $id=$record->id; ?>
<tr>
	<td><?php echo htmlspecialchars($id); ?></td>
	<td><?php echo htmlspecialchars($record->code); ?></td>
	<td><?php echo htmlspecialchars($record->expiration_date); ?></td>
	<td><?php echo htmlspecialchars($record->user_limit); ?></td>
	<td><?php echo ($record->expiration_date && strtotime($record->expiration_date) < strtotime("now")) ? "Expired":($record->record_status ? "Active" : "Inactive");?></td>
	<td><a href="registration-code/?id=<?php echo $id; ?>">Edit</a></td>
</tr>
<?php endforeach; ?>

</table>
<?php else: ?>
<p>No registration codes.</p>
<?php endif; ?>

<?php if($_GET['id']): ?><p><a class="gnlms-button" href="registration-code/?context_id=<?php echo $_GET['id']; ?>">Add registration code</a></p> <?php endif; ?>
