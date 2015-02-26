<?php 
global $gnlms;
$courses = $records;
?>

<?php if(count($courses)): ?>
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
<?php include("_course_monitor.php"); ?>
<?php else: ?>
<p>No courses found. <a href="<?php echo get_permalink(get_page_by_title('Courses')); ?>">Browse available courses.</a></p>
<?php endif; ?>