<?php if($records): ?>
<ul class="gnlms-treeview">
<?php foreach($records as $course): ?>
	<li class="gnlms-course-title"><a href="/course-detail/?id=<?php echo $course->id; ?>"><?php echo htmlspecialchars($course->title); ?></a></li>
<?php endforeach; ?>
</ul>
<?php else: ?>
	<p>No available courses.</p>
<?php endif; ?>
