<?php if($atts['code']=="gnlms_course_detail"): 
	
	
	$id = $context['id'];
	$userID = get_current_user_id();
	$registration = $this->retrieveRegistration($userID, $id);
	$dateFormat = get_option("date_format");
	
	$msg = "";
	if (strlen(trim($_GET['msg']))) {
		$msg = trim($_GET['msg']);
	}
	else if ($this->err) {
		$msg = $this->err;
	}
	
	if ($registration) {
		switch($registration->course_status) {
			case "Registered":
			case "In Progress":
				$statusText = "You registered for this course on ".date($dateFormat, strtotime($registration->registration_date)).'. <a class="gnlms-course-launch gnlms-button" data-course-id="'. $id .'" href="#">Launch course</a>';
				break;
			case "Expired":
				$statusText = "Your registration for this course expired on ".date($dateFormat, strtotime($registration->expiration_date)).".";
				break;
			case "Completed":
				$statusText = "You completed this course on ".date($dateFormat, strtotime($registration->course_completion_date)).".";
				break;
		}
	}
	else if ($userID) {
		$statusText = "This course is currently available.";
		$courseID = $id;
		ob_start();
		include("user-self-registration.php");
		$actionButton = apply_filters("gnlms_available_course_action_button", ob_get_clean(), $id, $userID);
	}
?>

<h2><?php echo $atts['title']; ?></h2>
<div class="gnlms-course-detail full_span">
<h3><?php echo htmlspecialchars($context['title']); ?></h3>
<div class="gnlms-course-description"><?php echo trim($context['description']); ?></div>
<div class="gnlms-course-status">
<?php if($msg): ?><p class="gnlms-msg"><?php echo htmlspecialchars($msg); ?></p><?php endif; ?>
<?php if($userID): ?>
	<?php echo $statusText; ?> <?php if(!$registration) echo $actionButton; ?>
<?php endif; ?>
</div>
</div>

<?php else: ?>

<form method="POST" class="gnlms_data_form">
	<input type="hidden" name="gnlms_data_form" value="course"/>
	<input type="hidden" name="_redirect" value="/" />
	<?php if($context['id']): ?>  
		<input type="hidden" name="id" value="<?php echo($context['id']); ?>" />
	<?php endif; ?>
	
	<label>Title<input type="text" name="title" value="<?php echo(htmlspecialchars($context['title'])); ?>"/></label>
	<label>Number/ID<input type="text" name="course_number" value="<?php echo(htmlspecialchars($context['course_number'])); ?>"/></label>
	<label><input type="checkbox" name="record_status" value="1" <?php $this->writeChecked($context['record_status']==1);?>/> Active</label>
	<label>URL<input disabled='disabled' type="text" name="url" size="50" value="<?php echo(htmlspecialchars($context['url'])); ?>"/></label>
	<label>Credit<input type="text" name="credit" value="<?php echo $context['credit']; ?>"/></label>
	<label>Certificate<input type="text" name="certificate" size="50" value="<?php echo $context['certificate']; ?>"/></label>
	<label>Image<input type="text" name="image" size="50" value="<?php echo $context['image']; ?>"/></label>
	<label for="description">Description</label>
	<textarea name="description" id="description"><?php echo(htmlspecialchars(trim($context['description']))); ?></textarea>
	<label>Version/Update <input type="date" name="last_update" value="<?php echo(htmlspecialchars($context['last_update'])); ?>"/></label>
	<input type="submit" value="<?php echo $context['id'] ? "Update" : "Add" ?> Course"/>
</form>

<?php endif; ?>