<form class="ccnx_calendar_user_select" name="ccnx_calendar_user_select">
<label for="calendar_user_select">Display events for:</label>
<select name="userid" id="calendar_user_select">
<option value="">Me</option>
<?php foreach($sscs as $ssc) { ?>
<option value="<?php echo $ssc->ID; ?>"><?php echo $ssc->name; ?></option>
<?php } ?>
</select>
</form>