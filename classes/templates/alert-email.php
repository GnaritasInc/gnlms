<h1><?php echo htmlspecialchars($alertName); ?></h1>
<table>
<tr>
<?php foreach(array_keys($events[0]) as $header): ?>
<th><?php echo htmlspecialchars($header); ?></th>
<?php endforeach; ?>
</tr>

<?php foreach($events as $record): ?>
<tr>
	<?php foreach($record as $field): ?>
		<td><?php echo htmlspecialchars($field); ?></td>
	<?php endforeach; ?>
</tr>
<?php endforeach; ?>
</table>