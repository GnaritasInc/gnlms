
<?php

$approveText = array(
	"Community Partner Add"=>"Add partner to system.",
	"Community Partner Edit"=>"Edit partner.",
	"Service Label Add"=>"Add service label to system.",
	"Service Label Edit"=>"Edit service label.",
	"Student Record Add" => "Add student record.",
	"Student Record Update" => "Update student record."
);

?>


<div><input type="radio" id="admin-action-approve" name="admin-action" value="approve" /><label for="admin-action-approve"><?php echo $approveText[$subtype]; ?></label></div>
