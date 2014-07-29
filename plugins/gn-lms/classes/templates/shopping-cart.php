<?php if(count($records)): ?>
	<ul>
	<?php foreach($records as $course): ?>
		<li class="gnlms-course-title"><a href="/course-detail/?id=<?php echo $course->id; ?>"><?php echo htmlspecialchars($course->title); ?></a></li>
	<?php endforeach; ?>
	</ul>
<?php else: ?>
<p>No courses selected.</p>
<?php endif; ?>
</div>