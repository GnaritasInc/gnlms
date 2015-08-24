<?php if($report=="assessment-responses"): ?>
	<?php foreach($records as $record): ?>
	<tr>
		<?php foreach($record as $key=>$value): ?>
			<?php if(strpos($key, "_result")) continue; ?>
			<?php if(preg_match('/^Q[0-9]+/', $key)): ?>
				<td class="answer numeric <?php echo $record[strtolower($key).'_result']==1 ? "correct" : "incorrect" ?>"><?php echo(htmlspecialchars($value)); ?></td>
			<?php else: ?>
				<td><?php echo(htmlspecialchars($value)); ?></td>
			<?php endif; ?>
		<?php endforeach; ?>
		
	</tr>
	<?php endforeach; ?>

<?php elseif($report=="assessment-summary"): ?>
	<?php $maxAnswers = $this->data->fetchMaxAssessmentAnswers($_GET['course_id']); ?>
	<?php foreach($records as $record): ?>
		<?php $answerData = $this->data->fetchQuestionAssessmentQuestionData($record["sequence"], $maxAnswers); ?>
		<tr>
			<td><?php echo $record["sequence"]; ?></td>
			<td><?php echo htmlspecialchars($record["text"]); ?></td>
			<td><?php echo $record["correct_answer"]; ?></td>
			<?php foreach($answerData as $col): ?>
			<td><?php echo(number_format($col*100)); ?>%</td>
			<?php endforeach; ?>
		</tr>
	<?php endforeach; ?>

<?php else: ?>
	<?php foreach($records as $record): ?>

	<tr>
	<?php foreach($record as $key=>$value): ?>
	<td><?php echo($this->formatValue($key, $value)); ?></td>
	<?php endforeach; ?>
	</tr>

	<?php endforeach; ?>
<?php endif; ?>