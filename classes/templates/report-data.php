<div class="gnlms_report_data">

<?php if($errors): ?>
<div class="gnlms_error">
<p>The following errors occurred:</p>
<ul>
<li><?php echo implode("</li><li>", $errors); ?></li>
</ul>
</div>
<?php endif; ?>

<?php if($records): ?>

<table>
<tr>
<?php include("report-data-header.php"); ?>
</tr>

<?php include("report-data-records.php"); ?>


</table>

<?php else: ?>

<p>No data.</p>

<?php endif; ?>
</div>