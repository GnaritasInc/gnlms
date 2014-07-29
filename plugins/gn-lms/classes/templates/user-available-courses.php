<?php if($records): global $gnlms; $selectedCourses = $gnlms->getSelectedCourses(); ?>
<ul class="gnlms-treeview">
<?php foreach($records as $course): ?>
	<?php if(!in_array($course->id, $selectedCourses)): ?>
		<li class="gnlms-course-title"><a href="/course-detail/?id=<?php echo $course->id; ?>"><?php echo htmlspecialchars($course->title); ?></a></li>
	<?php endif; ?>
<?php endforeach; ?>
</ul>
<?php else: ?>
	<p>No available courses.</p>
<?php endif; ?>
