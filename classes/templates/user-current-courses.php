<?php 
global $gnlms;
$courses = $records;
?>

<?php if(count($courses)): ?>
<ul class="gnlms-treeview">
<?php foreach($courses as $course):  ?>
	<?php if($course->expired): ?>
		<li class="gnlms-course-title"><?php echo htmlspecialchars($course->title); ?> (Expired)
	<?php else: ?>
		<li class="gnlms-course-title"><a class="gnlms-course-launch" href="#" data-course-id="<?php echo $course->id; ?>"><?php echo htmlspecialchars($course->title); ?></a>
	<?php endif; ?>
	<ul>
		<li>Status: <?php echo $course->course_status ?></li>
		<li><?php echo $course->description; ?></li>
		<li><a class="gnlms-course-launch" href="#" data-course-id="<?php echo $course->id; ?>">Launch course</a></li>
	</ul>
	</li>
<?php endforeach; ?>
</ul>
<?php else: ?>
<p>No courses found.</p>
<?php endif; ?>