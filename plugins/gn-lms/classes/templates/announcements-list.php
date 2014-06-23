<div class="gn-announcements-list">

<?php if(count($announcements)): ?>
<dl>
	<?php foreach($announcements as $announcement): ?>
	<dt><?php echo date('n/j/y', strtotime($announcement->create_date)); ?>: <?php echo htmlspecialchars($announcement->title); ?></dt>
	<dd>
	<?php echo $announcement->text; ?>
	<?php if($isAdmin): ?>
		<p><a href="#" class="gnlms-button gnlms-open-dialog-form" data-type="announcement" data-dialogid="gnlms-announcement-dialog" data-id="<?php echo $announcement->id; ?>">Edit</a> <a href="#" class="gnlms-button gnlms-announcement-delete" data-id="<?php echo $announcement->id; ?>">Delete</a></p>
	<?php endif; ?>
	</dd>
	<?php endforeach; ?>
</dl>
<?php else: ?>
<p>No announcements.</p>
<?php endif; ?>
<?php if($isAdmin): ?> <p><a href="#" class="gnlms-button gnlms-open-dialog-form" data-type="announcement" data-dialogid="gnlms-announcement-dialog">Add new</a></p> <?php endif; ?>
</div>
<?php if($isAdmin): ?>
<div data-height="425" class="gnlms-form-dialog" title="Create Announcement" id="gnlms-announcement-dialog"></div>

<?php endif; ?>