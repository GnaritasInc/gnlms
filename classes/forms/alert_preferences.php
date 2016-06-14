
<form id="gnlms_alert_preferences" class="gnlms_data_form" method="POST" action="/">
<p>Check to receive email notification for each event.</p>
<?php if(strlen(trim($_GET['alert_preferences_update']))): ?><p>Preferences updated.</p><?php endif; ?>
<?php if($_POST["_errors"]): ?> <p><?php echo $_POST["_errors"]; ?> </p> <?php endif; ?>
<input type="hidden" name="gnlms_data_form" value="alert_preferences"/>
<label><input type="checkbox" name="user_registration" value="1" <?php $this->writeChecked($context['user_registration']==1);?>/> User Registration</label>
<label><input type="checkbox" name="course_start" value="1" <?php $this->writeChecked($context['course_start']==1);?>/> Course Start</label>
<label><input type="checkbox" name="course_completion" value="1" <?php $this->writeChecked($context['course_completion']==1);?>/> Course Completion</label>
<label><input type="checkbox" name="course_failure" value="1" <?php $this->writeChecked($context['course_failure']==1);?>/> Course Failure</label>
<input type="submit" value="Update"/> <input type="reset" value="Cancel"/>
</form>