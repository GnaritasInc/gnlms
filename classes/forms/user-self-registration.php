<form method="POST" style="display: inline">
<input type="hidden" name="action" value="gnlms_single_course_registration"/>
<input type="hidden" name="course_id" value="<?php echo $courseID; ?>"/>
<input type="hidden" name="gnlms_nonce" value="<?php echo $this->nonce; ?>"/>
<a class="gnlms-form-submit  gnlms-button" href="#">Register</a>
</form>