<ul class="gnlms-treeview">
<?php foreach($records as $course): ?>
		<li class="gnlms-course-title"><?php echo htmlspecialchars($course->title); ?>
	<ul>
		<li>Status: <?php echo $course->course_status ?></li>
		<li><?php echo htmlspecialchars($course->description); ?></li>
		<li><a class="gnlms-course-launch" href="/course-monitor/?id=<?php echo $course->id; ?>">Launch course</a></li>
	</ul>
	</li>
<?php endforeach; ?>
</ul>
