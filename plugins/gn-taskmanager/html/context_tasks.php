

<div class="widget">
<h2 class="widget_title">Tasks</h2>
<div class="widget_links"><a href="#" class="task-edit">Add task</a></div>



	<form class="gn-taskfilter">
	<input type="hidden" name="action" value="gntm-gettasks" />
	<input type="hidden" name="tasktype" value="<?php echo($atts['type']); ?>" />

	<input type="hidden" name="id" value="<?php echo(htmlspecialchars($_GET['id'])); ?>" />

	</form>

<div class="full_span gn-taskwidget" style="max-height: 300px; overflow-y: auto">
<div id="gntm-current-tasks">
<?php $this->writeTasks($atts['type']) ?>
</div>

</div>
<div id="gntm-edit-form" class="gn-dialog" title="Edit Task"></div>

</div>

