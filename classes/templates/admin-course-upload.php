<h1>Upload Course</h1>

<?php if(!wp_is_writable($this->getOption("gnlms_course_path"))): ?>
	<div class="error">
	<p>Your course directory is not writable. Please make sure the directory below is writable before uploading course files.</p>
	<p>Current course directory: <?php echo htmlspecialchars($this->getOption("gnlms_course_path")); ?></p>
	</div>
<?php endif; ?>
<?php if($context["_error"]): ?>
	<div class="error">
	<p>Error uploading course files: <?php echo ($context["_error"]); ?></p>
	</div>
<?php elseif($context["_msg"]): ?>
	<div class="updated">
	<p><?php echo ($context["_msg"]); ?></p>
	</div>
<?php endif; ?>

<?php $useManifest = strlen(trim($context["use_manifest"])) ? $context["use_manifest"] : 0; ?>

<form method="POST" class="gnlms_data_form" enctype="multipart/form-data" id="gnlms_course_upload">
<input type="hidden" name="gnlms_admin_action" value="course_upload"/>
<input type="hidden" name="gnlms_nonce" value="<?php echo $this->nonce; ?>"/>
<input type="hidden" name="record_status" value="1"/>

<p>Upload a ZIP archive containing the course's files to create a new course record. The course files will be extracted into the directory you specify below.</p>
<p>The course's title and launch URL may be entered below or read from the course's manifest file.</p>
<label><input style="display: block" type="file" name="gnlms_course_file"/> ZIP File</label>

<p><?php echo trailingslashit($this->getOption("gnlms_course_path")); ?><input id="course_dir" style="display: inline" type="text" name="course_dir" value="<?php echo htmlspecialchars($context["course_dir"]); ?>" /><br/>
<label for="course_dir">Course directory</label></p>


<label><input type="radio" name="use_manifest" value="0" <?php checked($useManifest , 0); ?>/> Use title and launch URL entered below:</label>
<fieldset>
<legend>Course Information</legend>
<label><input type="text" name="title" value="<?php echo htmlspecialchars($context["title"]); ?>"/> Title</label>
<label><input type="text" name="url" size="100" value="<?php echo htmlspecialchars($context["url"]); ?>"/> URL</label>
</fieldset>

<label><input type="radio" name="use_manifest" value="1" <?php checked($useManifest , 1); ?>/> Get title and launch URL from manifest.</label>


<input type="submit" value="Upload Course File"/>
</form>