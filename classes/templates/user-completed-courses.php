<?php 
global $gnlms;
$dateFormat = get_option("date_format");
?>

<?php if(count($records)): ?>

<ul id="gnlms-completed-courses">
	<?php foreach($records as $course): ?>
		<li><?php echo htmlspecialchars($course->title); ?>
			<ul>
				<li>Registration date: <?php echo date($dateFormat, strtotime($course->registration_date)); ?></li>
				<li>Completion date: <?php echo date($dateFormat, strtotime($course->course_completion_date)); ?></li>
				<li>Score: <?php echo $course->score; ?></li>
			</ul>
		</li>
	<?php endforeach; ?>	
</ul>

<?php else: ?>
<p>No completed courses.</p>
<?php endif;?>