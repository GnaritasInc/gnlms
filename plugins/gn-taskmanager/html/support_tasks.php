<?php global $ccnx_ListWidget; global $ccnx_DataInterface; ?>
<?php $role = gn_user_role(); ?>

<div class="widget">
<h2 class="widget_title">Support Requests</h2>

<div class="gn_taskwidget_container">
	<form class="gn-taskfilter">

<?php if ($filters): ?>

	<div class="filters">
	<input type="hidden" name="tasktype" value="support" />

	<input type="hidden" name="action" value="gntm-gettasks" />

	<?php if (in_array("district", $filters)): ?>
		<label for="district_id">District:</label>
		<select name="district_id" id="district_id">
		<option value="">All</option>
		<?php $ccnx_DataInterface->listOptions ("district"); ?>

		</select>
	<?php endif; ?>

	<?php if (in_array("user_district", $filters)): ?>
		<label for="district_id">District:</label>
		<select name="district_id" id="district_id">
		<option value="">All</option>
		<?php $ccnx_DataInterface->listOptions ("user_district"); ?>

		</select>
	<?php endif; ?>


	<?php if (in_array("type", $filters)): ?>
		<label for="subtype">Showing:</label>
		<select name="subtype" id="subtype">
		<option value="">All</option>
		<?php include ("_support_options.php"); ?>

		</select>
	<?php endif; ?>

<?php if (in_array("sortby", $filters)): ?>

	<label for="filter_options2">Sort by:</label>
	<select name="sortby" id="filter_options2">
	<option value="subtype">Type</option>
	<option value="owner">Owner</option>
	<option value="creator">From</option>
	<option value="title">Title</option>
	<option value="status">Status</option>
	<option value="start">Start Date</option>
	<!--<option value="due" selected="selected">Due Date</option>-->
	</select>

<?php endif; ?>

<?php if (in_array("togglecomplete", $filters)): ?>
<label><input type="checkbox" value="1" name="showcomplete" /> Show completed tasks</label>
<?php endif; ?>
</div>
<?php endif; ?>

<div class="full_span gn-taskwidget" xstyle="max-height: 300px; overflow-y: auto">
<!--<table id="current-tasks">-->
<div id="gntm-current-tasks">
<?php $this->writeSupportTasks(); ?>

</div>


<div class="gn-dialog" title="Support Request" id="gntm-edit-form"></div>
</div>

<input type="hidden" name="offset"/>
</form>
</div>
</div>



