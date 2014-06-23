<ul class="gnlms-treeview">
<?php foreach($courses as $course): ?>
	<?php 
		$active = $course->active;
		$class = "gnlms-course-title";
		if(!$active) $class .= " inactive"; 
	?>
	<li class="<?php echo $class; ?>"><a href="/course/?id=<?php echo $course->id; ?>"><?php echo htmlspecialchars($course->title); ?></a> <?php if(!$active):?> (Inactive) <?php endif; ?>
	<ul>
		<li><p><?php echo htmlspecialchars($course->description); ?></p></li>
	</ul>
	</li>
<?php endforeach; ?>
</ul>