<?php 
global $gnlms;
?>
<div class="gnlms-course-descriptions">
<?php if(strlen(trim($atts['title']))): ?><h2><?php echo htmlspecialchars($atts['title']); ?></h2><?php endif; ?>
<?php if(count($records)): ?>
<?php foreach($records as $course): ?>
	<div class="gnlms-course-info">
		<h3><?php echo htmlspecialchars($course->title); ?></h3>
		<div class="gnlms-course-description">
		<?php if(strlen(trim($course->image))): ?><img class="gnlms-course-image" src="<?php echo $gnlms->getOption("gnlms_course_image_url")."/".$course->image;?>" alt="<?php echo htmlspecialchars($course->title); ?>" /><?php endif; ?>
		<?php echo $course->description; ?>
		</div>
		<?php if($courseLink = $gnlms->getCoursePageLink($course->id)): ?>
		<div class="gnlms-course-action">
		<p><a href="<?php echo $courseLink; ?>">View course details...</a></p>	
		</div>
		<?php endif; ?>
	</div>
<?php endforeach; ?>


<?php else: ?>
<p>Currently there are no courses available, but check back soon!</p>
<?php endif; ?>

</div>
