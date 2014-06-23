
<div class="widget">
<h2 class="widget_title">Tasks</h2>


<?php if ($filters): ?>

	<div class="filters">
	<form class="gn-taskfilter">
	<input type="hidden" name="action" value="gntm-gettasks" />
	<input type="hidden" name="tasklist-type" value="task" />
	<input type="hidden" name="offset" value="0" />



	<?php if (in_array("sortby", $filters)): ?>

		<label for="filter_options2">Sort by:</label>
		<select name="sortby" id="filter_options2">
		<option value="due">Due Date</option>
		<option value="title">Title</option>
		<option value="status">Status</option>
		<option value="start">Start Date</option>
		</select>

	<?php endif; ?>

	<?php if (in_array("togglecomplete", $filters)): ?>
		<label><input type="checkbox" value="1" name="showcomplete" /> Show completed tasks</label>
	<?php endif; ?>

	</form>
	</div>
<?php endif; ?>

<div class="full_span gn-taskwidget" style="max-height: 300px; overflow-y: auto">
<div id="gntm-current-tasks">
<?php $this->writeCurrentTasks($atts['type']) ?>
</div>
<div class="widget_links"><a href="#" class="task-edit gnlms-button">Add task</a></div>
</div>
<div id="gntm-edit-form" class="gn-dialog" title="Edit Task"></div>

</div>

