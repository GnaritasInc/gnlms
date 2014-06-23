<?php

$cmd = $this->formAction;
$evt = $this->formData;

if(!is_user_logged_in()){
	die("Login required");
}
$title = ($cmd == "add" ? "Add" : "Edit")." Event";
?>

<html>
<head>
<title><?php echo($title) ?></title>
<?php
wp_head();
?>
</head>
<body>
<h1><?php echo($title) ?></h1>
<form id="event_input" method="POST">
<input type="hidden" name="cmd" value="<?php echo($cmd) ?>"/>
<input type="hidden" name="start" id="start" value="<?php echo($this->htmlEncode($evt->start)) ?>" />
<input type="hidden" name="end" id="end" value="<?php echo($this->htmlEncode($evt->end)) ?>" />
<?php if($cmd=="update"): ?>
<input type="hidden" name="id" value="<?php echo($this->htmlEncode($evt->id)) ?>"/>
<?php endif; ?>
<table>
<tr>
	<th>Title:</th>
	<td><input type="text" name="title" value="<?php echo($this->htmlEncode($evt->title)) ?>"/></td>
</tr>

<tr>
	<th>Time:</th>
	<td>
		<input id="start_date" type="text" class="datepicker" name="start_date" /> <input type="text" size="8" class="time-entry" name="start_time" id="start_time" /> to
		<input id="end_date" type="text" class="datepicker" name="end_date" "/> <input type="text" size="8" class="time-entry" name="end_time" id="end_time" /> <br/>
		<input id="all_day" type="checkbox" name="all_day" value="1"<?php $this->writeConditionalAttribute($evt->all_day || $cmd == "add", "checked"); ?> /> All day
	</td>
</tr>
<tr>
	<th>Location:</th>
	<td><input type="text" name="location" value="<?php echo($this->htmlEncode($evt->location)) ?>"/></td>
</tr>
<tr>
	<th>Description:</th>
	<td>
		<textarea name="description"><?php echo($this->htmlEncode($evt->description)); ?></textarea>
	</td>
</tr>
<tr>
	<th>Visibility:</th>
	<td>
		<select name="visibility">
			<option value="private"<?php $this->writeConditionalAttribute($evt->visibility=="private", "selected"); ?>>Private</option>
			<option value="public"<?php $this->writeConditionalAttribute($evt->visibility=="public", "selected"); ?>>Public</option>
		</select>
	</td>
</tr>
<tr>
	<th>&#160;</th>
	<td><input type="submit" value="Submit"/> <input type="reset" value="Clear"/></td>
</tr>
</table>
</form>

</body>
</html>