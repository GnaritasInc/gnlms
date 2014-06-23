
<?php global $ccnx_DataInterface; ?>
<?php $taskmeta = $task->getMetaData(); ?>
<?php $subtype = $task->subtype; ?>

<form class="gn-support-request">
<input type="hidden" name="id" value="<?php $this->writeEncodedValue($task->id); ?>"/>
<input type="hidden" name="action" value="gntm-updatetask" />
<input type="hidden" name="nonce" value="<?php $this->writeEncodedValue($this->nonce); ?>" />
<input type="hidden" name="url" value="<?php $this->writeEncodedValue($task->url); ?>" />

<h2><?php $this->writeEncodedValue($task->title); ?></h2>
<p class="msg"></p>

<?php switch($subtype): ?>
<?php case "Community Partner Add":?>
<?php case "Community Partner Edit":?>
<?php case "Community Partner": ?>

<h3>Partner Information</h3>
<table>
<tr>
<th>Name:</th><td><?php $this->writeEncodedValue($taskmeta['object_name']);  ?></td>
</tr>
<tr>
<th>Website:</th><td><?php $this->writeEncodedValue($taskmeta['website']);  ?></td>
</tr>
<tr>
<th>Contact:</th>
<td>
<?php $this->writeEncodedValue($taskmeta['contact_firstname']." ".$taskmeta['contact_lastname']);  ?><br/>
<?php $this->writeEncodedValue($taskmeta['contact_email']);  ?><br/>
<?php $this->writeEncodedValue($taskmeta['contact_phone']);  ?>
</td>
</tr>
<tr>
<th>Services provided:</th>
<td><?php $this->writeEncodedValue($taskmeta['services']);  ?></td>
</tr>
<tr>
<th>How this partner's services are provided:</th>
<td><?php $this->writeEncodedValue($taskmeta['how_provided']);  ?></td>
</tr>

</table>

<?php break; ?>
<?php case "Service Label Add": ?>
<?php case "Service Label Edit": ?>
<?php case "Service Label": ?>

<?php
$serviceType = $ccnx_DataInterface->data->getCCNXObject($taskmeta['service_type_id'], "service_type");

?>

<h3>Service Label Information</h3>
<table>
<tr>
<th>Service Label:</th><td><?php $this->writeEncodedValue($taskmeta['object_name']);  ?></td>
</tr>
<tr>
<th>Service Type:</th><td><?php $this->writeEncodedValue($serviceType['name']);  ?></td>
</tr>
<tr>
<th>Providing Partners:</th><td><?php $this->writeEncodedValue($taskmeta['partners']);  ?></td>
</tr>

<tr>
<th>Definition:</th><td><?php $this->writeEncodedValue($taskmeta['definition']); ?></td>
</tr>
</table>

<?php break; ?>


<?php case "Student Record Update": ?>
<h3>Details</h3>
<table>
<tr>
<th>First Name:</th><td><?php $this->writeEncodedValue($taskmeta['firstname']);  ?></td>
</tr>
<tr>
<th>Last Name:</th><td><?php $this->writeEncodedValue($taskmeta['lastname']);  ?></td>
</tr>

<tr>
<th>Student I.D.:</th><td><?php $this->writeEncodedValue($taskmeta['school_identifier']);  ?></td>
</tr>

	<?php if($taskmeta['support_action']=="Transfer"): ?>
	<tr>
	<th>Classroom:</th><td><?php $this->writeEncodedValue($taskmeta['school']);  ?></td>
	</tr>
	<tr>
	<th>Grade:</th><td><?php $this->writeEncodedValue($taskmeta['classroom']);  ?></td>
	</tr>
	<?php endif; ?>
<tr>
<th>Action Requested:</th><td><?php $this->writeEncodedValue($taskmeta['support_action']);  ?></td>
</tr>

<tr>
<th>Notes:</th><td><?php $this->writeEncodedValue($taskmeta['notes']);  ?></td>
</tr>
</table>

<?php break; ?>

<?php case "Student Record Add": ?>

<h3>Details</h3>
<table>
<tr>
<th>First Name:</th><td><?php $this->writeEncodedValue($taskmeta['firstname']);  ?></td>
</tr>
<tr>
<th>Last Name:</th><td><?php $this->writeEncodedValue($taskmeta['lastname']);  ?></td>
</tr>
<tr>
<th>Classroom:</th><td><?php $this->writeEncodedValue($taskmeta['classroom']);  ?></td>
</tr>
<tr>
<th>Grade:</th><td><?php $this->writeEncodedValue($taskmeta['grade']);  ?></td>
</tr>
<tr>
<th>Gender:</th><td><?php echo($taskmeta['gender']=="F" ? "Female" : "Male");  ?></td>
</tr>
<tr>
<th>Ethnicity:</th><td><?php $this->writeEncodedValue($taskmeta['ethnicity']);  ?></td>
</tr>
<tr>
<tr>
<th>ELL:</th><td><?php $this->writeBoolean($taskmeta['status_ell']);  ?></td>
</tr>
<tr>
<th>IEP:</th><td><?php $this->writeBoolean($taskmeta['status_iep']);  ?></td>
</tr>
<tr>
<th>Flag for September:</th><td><?php $this->writeBoolean($taskmeta['is_flagged']);  ?></td>
</tr>
<tr>
<th>Special Education:</th><td><?php $this->writeTriState($taskmeta['status_sped']);  ?></td>
</tr>
<tr>
<th>Retained:</th><td><?php $this->writeTriState($taskmeta['is_retained']);  ?></td>
</tr>
<tr>
<th>Notes:</th><td><?php $this->writeEncodedValue($taskmeta['notes']);  ?></td>
</tr>
</table>

<?php break; ?>

<?php case "Technical Issue": ?>

<h3>Details</h3>
<table>
<tr>
<th>Operating system:</th><td><?php $this->writeEncodedValue($taskmeta['os']);  ?></td>
</tr>
<tr>
<th>Browser and version:</th><td><?php $this->writeEncodedValue($taskmeta['browser']);  ?></td>
</tr>
<tr>
<th>Description of problem:</th><td><?php $this->writeEncodedValue($taskmeta['description']); ?></td>
</tr>
</table>


<?php break; ?>

<?php default: ?>

<h3>Details</h3>
<table>
<tr>
<th>Description:</th><td><?php $this->writeEncodedValue($taskmeta['description']); ?></td>
</tr>
</table>

<?php break; ?>
<?php endswitch; ?>

<?php include("_support_request_actions.php"); ?>


</form>