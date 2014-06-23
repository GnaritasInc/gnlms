<div id="calendar-content">
<?php $this->writeUserSelect(); ?>
<div id="calendar"></div>
<div id="dialog-form" title="Edit event">
	<p id="msg">Enter event details.</p>
	<form id="event_input" name="event_input">
	<input type="hidden" name="start" id="start" value="" />
	<input type="hidden" name="end" id="end" value="" />
	<input type="hidden" name="id" id="id" value="" />
	<input type="hidden" name="creator" id="creator" value="" />
	<input type="hidden" name="owner" id="owner" value="" />
	<input type="hidden" name="creator_id" id="creator_id" value="" />
	<input type="hidden" name="owner_id" id="owner_id" value="" />
	<fieldset>
	<legend>Event details</legend>
	<table>
	<tr>
		<th>Title:</th>
		<td><input class="" type="text" size="50" name="title" value=""/></td>
	</tr>

	<tr>
		<th>Time:</th>
		<td>
			<input id="start_date" type="text" class="datepicker"  /> <input type="text" size="8" class="time-entry" id="start_time" /> to
			<input id="end_date" type="text" class="datepicker" /> <input type="text" size="8" class="time-entry" id="end_time" /> <br/>
			<input id="all_day" type="checkbox" name="all_day" value="1" /> All day
		</td>
	</tr>
	<tr>
		<th>Location:</th>
		<td><input type="text" size="50" name="location" value=""/></td>
	</tr>
	<tr>
		<th>Description:</th>
		<td>
			<textarea class="" cols="49" rows="4" name="description"></textarea>
		</td>
	</tr>
	<tr>
		<th>Visibility:</th>
		<td>
			<select name="visibility">
				<option value="private">Private</option>
				<option value="public">Public</option>
			</select>
		</td>
	</tr>
</table>
</fieldset>
<fieldset>
<legend>Attendees</legend>
<label>Attendee lookup: <input type="text" id="attendee-input" title="Enter name to search for attendees..." /></label></p>
<div id="attendeelist">[None]</div>
</fieldset>
</form>

</div>
</div>
