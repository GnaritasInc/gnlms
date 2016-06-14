<?php if($records): ?>
<table>
<tr><th>TITLE</th><th>COURSE NUMBER</th><th>COURSE STATUS</th><th>EXPIRATION DATE</th><th>EDIT</th></tr>

<?php foreach($records as $record): ?>
<?php $id=$record->id; $course_id=$record->course_id; ?>
<tr>
	<td><a href="<?php echo $atts["link"].(strpos($atts["link"], '?')===false ? '?' : '&'); ?>id=<?php echo($course_id); ?>"><?php echo htmlspecialchars($record->title); ?></a></td>
	<td><?php echo htmlspecialchars($record->course_number); ?></td>
	<td><?php echo htmlspecialchars($record->course_status); ?></td>
	<td><?php echo htmlspecialchars($record->expiration_date); ?></td>
	<td><a href="#" class="gnlms-open-dialog-form" data-dialogid="gnlms-edit-course-registration" data-type="user_course_registration" data-id="<?php echo $id; ?>">Edit</a></td>
</tr>
<?php endforeach; ?>

</table>
<?php else: ?>
<p>No courses.</p>
<?php endif; ?>

<div id="gnlms-edit-course-registration" data-width="690" data-height="590" class="gnlms-form-dialog" title="Edit Course Registration"> </div>