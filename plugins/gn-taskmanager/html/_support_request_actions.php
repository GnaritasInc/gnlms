
<?php if ($this->userCanEditTask($task)): ?>

<h3>Actions</h3>

<?php switch($subtype): ?>
<?php case "Community Partner Add": ?>
<?php case "Community Partner Edit": ?>
<?php case "Service Label Add": ?>
<?php case "Service Label Edit": ?>

	<?php include("_action_delegate.php"); ?>
	<?php include("_action_approve.php"); ?>
	<?php include("_action_deny.php"); ?>
	<div>
	<input type="radio" id="admin-action-status" name="admin-action" value="set-status" /><label for="admin-action-status">Update Task Status:</label>
	</div>
	<?php include("_action_set_status.php"); ?>

<?php break; ?>

<?php case "Student Record Add": ?>
<?php case "Student Record Update": ?>

	<?php
		if($subtype=="Student Record Add") {
			$studentAction = "added";
		}
		else {
			$studentAction = "updated";
		}
	?>

	<?php include("_action_delegate.php"); ?>

	<?php include("_action_approve.php"); ?>

	<div><input type="radio" id="admin-action-complete" name="admin-action" value="complete" /><label for="admin-action-complete">Complete Task (after record <?php echo $studentAction; ?>).</label></div>

	<?php include("_action_deny.php"); ?>

<?php break; ?>

<?php default: ?>

	<?php include("_action_delegate.php"); ?>

	<div>
	<input type="radio" id="admin-action-status" name="admin-action" value="set-status" /><label for="admin-action-status">Handle the request directly:</label>
	</div>
	<?php include("_action_set_status.php"); ?>

<?php endswitch; ?>
<?php endif; ?>