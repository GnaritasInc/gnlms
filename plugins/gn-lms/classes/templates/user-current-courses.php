<ul class="gnlms-treeview">
<?php foreach($courses as $course): ?>
	<?php if($course->expired): ?>
		<li class="gnlms-course-title"><?php echo htmlspecialchars($course->title); ?> (Expired)
	<?php else: ?>
		<li class="gnlms-course-title"><a class="gnlms-course-launch" href="/course-monitor/?id=<?php echo $course->id; ?>"><?php echo htmlspecialchars($course->title); ?></a>
	<?php endif; ?>
	<ul>
		<li>Status: <?php echo $course->course_status ?></li>
		<li><?php echo htmlspecialchars($course->description); ?></li>
		<li><a class="gnlms-course-launch" href="/course-monitor/?id=<?php echo $course->id; ?>">Launch course</a></li>
	</ul>
	</li>
<?php endforeach; ?>
</ul>
<div title='Course Monitor' id='gnlms-course-monitor'></div>