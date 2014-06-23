

jQuery(document).ready(function ($) {
	
	if(!$("form #start, form #end").length) {
		return;
	}
	
	var now = new Date();
	var start = $("#start").val() ? new Date($("#start").val() * 1000) : new Date(now.valueOf() + ((15 - now.getMinutes() % 15) * 1000 * 60));
	var end = $("#end").val() ? new Date($("#end").val() * 1000) : new Date(start.valueOf() + (1000 * 60 * 60));
	 
	
	$("#start_date").datepicker({
			onClose: function (dateText) {
				setDateTimeValue(this.form.start, dateText, "#start_time");				
			},
			autoSize: true
	});
	
	$("#end_date").datepicker({
		onClose:function (dateText) {
			setDateTimeValue(this.form.end, dateText, "#end_time");
		},
		autoSize: true
	});
	
	$("#start_date").datepicker("setDate", new Date(start.valueOf()));
	$("#end_date").datepicker("setDate", new Date(end.valueOf()));
	
	
	
	
	$(".time-entry").timePicker({
		show24Hours: false,
		step:15,
		endTime:"11:45 PM"

	});
	
	$.timePicker("#start_time").setTime(start);
	$.timePicker("#end_time").setTime(end);
	
	$("#start_time").change(function () {
		setDateTimeValue(this.form.start, $("#start_date").val(), "#start_time");
	});
	
	$("#end_time").change(function () {
		setDateTimeValue(this.form.end, $("#end_date").val(), "#end_time");
	});
	
	$("#all_day").change(function () {
		if(this.checked) {
			$(".time-entry").hide();
		}
		else $(".time-entry").show();
	});
	if($("#all_day").get(0).checked) {
		$(".time-entry").hide();
	}
	
	function setDateTimeValue (dateTimeInput, dateString, timePickerSelector) {
		var $ = jQuery;
		var dateVal = new Date(dateString);
		var startTime = new Date($.timePicker(timePickerSelector).getTime().valueOf());

		startTime.setFullYear(dateVal.getFullYear(), dateVal.getMonth(), dateVal.getDate());

		dateTimeInput.value = Math.round(startTime.valueOf()/1000);
	}
	
});

