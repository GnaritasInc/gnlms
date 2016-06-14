<div id='gnlms-course-dialog'>

<script type='text/javascript'>
_gnlms_COURSE_ID =<?php echo $cid; ?>;
_gnlms_USER_ID=<?php echo $uid; ?>;
_gnlms_URL="<?php echo $scormInterfaceURL; ?>";
courseURL="<?php echo $courseURL; ?>";
</script>

<p class='gnScormSuccessfulLaunch gnlms-scorm-message'>Course access in progress.</p>
<p class='gnScormFailedLaunch gnlms-scorm-message'>The course window has not opened. Please disable pop-up blockers for this site. You may use the link below to launch the course.</p>
<p class='gnScormFailedLaunch gnlms-scorm-message'><a href='#' onclick='launchCourse(courseURL)'>Click to view course</a></p>
<p class='gnScormSuccessfulLaunch gnlms-scorm-message'>Do not close this window while the course is in progress.</p>
<p class='gnScormSuccessfulLaunch gnlms-scorm-message'>When you have finished your session with the course, this dialog may be closed.</p>

</div>

