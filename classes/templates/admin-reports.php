<h1>LMS Reports</h1>
<ul>
<?php foreach($this->reportInterface->getReportList() as $key=>$title): ?>
	<li><a href="admin.php?page=gnlms-report-<?php echo htmlspecialchars($key); ?>"><?php echo htmlspecialchars($title); ?></a></li>
<?php endforeach; ?>
</ul>