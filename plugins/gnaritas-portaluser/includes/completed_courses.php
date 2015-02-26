<?php
global $records;
$records = gn_current_user_completed_courses ();
$empty = (!$records || count($records)==0) ? true : false;
?>

<h2>Completed Courses</h2>
<div class="full_span<?php echo $empty ? " gn-empty" : ""?>">
<?php
if ($empty) {
	echo ("<i>No courses found</i>");
}
else {

?>
<ul id="gn-completed-courses">

<?php

foreach ($records as $key => $obj) {
echo ("<li>". $obj->title);
echo ("<ul>");
echo ("<li>Registration date: $obj->registration_date</li>");
echo ("<li>Completion date: $obj->course_completion_date</li>");
echo ("<li>Score: $obj->score</li>");
echo ("</ul>");
echo ("</li>");

}


?>


</ul>

<script type="text/javascript">

jQuery(document).ready (function ($) { $("#gn-completed-courses").treeview({	animated: "fast",
		persist: "location",
		collapsed: true,
		unique: true
	});
});

</script>

<?php
}
?>
</div>
