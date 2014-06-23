<?php 
$codeID = array_key_exists('subscription_code_id', $context) ? $context['subscription_code_id'] : $_GET['context_id'];
?>
<form method="POST" id="gnlms-add-edit-subscription-code-course" class="gnlms_data_form">
<input type="hidden" name="action" value="gnlms-add-edit-subscription-code-course"/>
<input type="hidden" name="gnlms_nonce" value="<?php echo $this->nonce; ?>"/>

<input type="hidden" name="subscription_code_id" value="<?php echo htmlspecialchars($codeID); ?>"/>

<?php if($context['id']): ?>  
	<input type="hidden" name="id" value="<?php echo($context['id']); ?>" />
<?php endif; ?>

<label>
<select name="course_id">
<option value="">Choose course</option>
	<?php foreach($this->data->getAvailableCoursesForSubscriptionCode($codeID, $context['course_id']) as $course): ?>
		<option value="<?php echo $course->id; ?>"<?php echo ($course->id==$context['course_id']) ? ' selected="selected"':'';?>><?php echo htmlspecialchars($course->title); ?></option>
	<?php endforeach; ?>
</select>
<br />Course
</label>
<div class="gnlms_range">
<input type="number" name="subscription_period_number" value="<?php echo $context['subscription_period_number'];?>"/> 
<select name="subscription_period_interval">
	<option value=""></option>
	<?php foreach($this->data->subscriptionPeriodIntervals as $key=>$value): ?>	
		<option value="<?php echo $key; ?>"<?php echo ($key==$context['subscription_period_interval']) ? ' selected="selected"':'';?>><?php echo $value; ?></option>
	<?php endforeach; ?>
</select>
<br/>Subscription Period
</div>
</form>