
<div <?php if(isset($metaKey)) echo("data-type='$metaKey' "); ?><?php if(isset($metaValue)) echo("data-itemid='$metaValue' "); ?>class="gn-dialog" id="gntm-input-form" title="Add Task">
<form>
<input type="hidden" name="action" value="gntm-addtask"/>
<input type="hidden" name="nonce" value="<?php $this->writeEncodedValue($this->nonce) ?>" />
<input type="hidden" name="type" value="gn_Activity" />

<?php if(isset($metaKey) && isset($metaValue)): ?>
<input type="hidden" name="<?php echo($metaKey);?>" value="<?php echo($metaValue); ?>" />
<?php endif; ?>

<p class="msg">Enter Task Information</p>
<table>

<tr>
<th>Title:</th>
<td><input type="text" size="50" name="title" value="New Task" /></td>
</tr>


<tr>
<th>Start date:</th>
<td><input type="text" class="date-picker" name="start_date" value="" /></td>
</tr>

<tr>
<th>Due date:</th>
<td><input type="text" class="date-picker" name="due_date" value="" /></td>
</tr>

<tr>
<th>Description:</th>
<td>
<textarea cols="49" rows="4" name="description"></textarea>
</td>
</tr>
</table>
</form>
</div>

<div <?php if(isset($metaKey)) echo("data-type='$metaKey' "); ?><?php if(isset($metaValue)) echo("data-itemid='$metaValue' "); ?>class="gn-dialog" id="gntm-edit-form" title="Edit Task"></div>
