<?php 
global $gnlms; 
?>
<ul class="gnlms-treeview">
<?php foreach($courses as $course): $courseLaunchURL = $gnlms->getCourseLaunchURL($course->id); ?>
	<?php if($course->expired): ?>
		<li class="gnlms-course-title"><?php echo htmlspecialchars($course->title); ?> (Expired)
	<?php else: ?>
		<li class="gnlms-course-title"><a class="gnlms-course-launch" href="<?php echo $courseLaunchURL; ?>"><?php echo htmlspecialchars($course->title); ?></a>
	<?php endif; ?>
	<ul>
		<li>Status: <?php echo $course->course_status ?></li>
		<li><?php echo htmlspecialchars($course->description); ?></li>
		<li><a class="gnlms-course-launch" href="<?php echo $courseLaunchURL; ?>">Launch course</a></li>
	</ul>
	</li>
<?php endforeach; ?>
</ul>
<div title='Course Monitor' id='gnlms-course-monitor'></div>