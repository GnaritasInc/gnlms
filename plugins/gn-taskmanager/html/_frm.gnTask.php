
<form>
<?php if($task->id): ?>
	<input type="hidden" name="id" value="<?php $this->writeEncodedValue($task->id); ?>"/>
	<input type="hidden" name="action" value="gntm-updatetask" />
<?php else: ?>
	<input type="hidden" name="action" value="gntm-addtask"/>
	<input type="hidden" name="type" value="gn_Activity" />
<?php endif; ?>
<input type="hidden" name="nonce" value="<?php $this->writeEncodedValue($this->nonce) ?>" />

<?php if($metaKey && $metaValue): ?>
	<input type="hidden" name="<?php echo($metaKey);?>" value="<?php echo($metaValue); ?>" />
<?php endif; ?>

<input type="hidden" name="meta_key" value="" />
<input type="hidden" name="meta_value" value="" />


<p class="msg">Enter Task Information</p>
<table>

<tr>
<th>Title:</th>
<td><input type="text" size="50" name="title" value="<?php $this->writeEncodedValue($task->title); ?>" /></td>
</tr>
<tr>
<th>Category:</th>
<td>
<select name="category" id="category">
	<?php $this->writeCategoryOptions($task); ?>
</select>
</td>
</tr>
<tr>
<th>Status:</th>
<td>
	<select id="status" name="status">
		<?php $this->writeStatusOptions($task->type, $task->status); ?>
	</select>
</td>
</tr>

<tr>
<th>Start date:</th>
<td><input type="text" class="date-picker" name="start_date" value="<?php $this->writeEncodedValue($task->start_date); ?>" /></td>
</tr>

<tr>
<th>Due date:</th>
<td><input type="text" class="date-picker" name="due_date" value="<?php $this->writeEncodedValue($task->due_date); ?>" /></td>
</tr>

<tr>
<th>Description:</th>
<td>
<textarea cols="49" rows="4" name="description">
<?php $this->writeEncodedValue($task->description); ?>
</textarea>
</td>
</tr>
</table>

</form>