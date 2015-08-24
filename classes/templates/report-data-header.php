<?php if($report=="assessment-responses"): ?>

	<?php foreach(array_keys($records[0]) as $header): ?>
		<?php if(strpos($header, "_result")) continue; ?>
		<th><?php echo(htmlspecialchars($header)); ?></th>
	<?php endforeach; ?>

<?php elseif($report=="assessment-summary"): ?>
	
	<th>Question</th>
	<th>Question Text</th>
	<th>Correct Response</th>
	<?php for($i=1; $i<=$this->data->fetchMaxAssessmentAnswers($_GET['course_id']); ++$i): ?>
		<th>Response <?php echo $i; ?></th>
	<?php endfor; ?>


<?php else: ?>

	<?php foreach(array_keys($records[0]) as $header): ?>
	<th><?php echo(htmlspecialchars(ucfirst($header))); ?></th>
	<?php endforeach; ?>
	
<?php endif; ?>
