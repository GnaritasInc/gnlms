// Calendar widget scripts

// Calendar widget definition

(function ($) {

	var today = new Date();
	var month = today.getMonth()+1;
	var year = today.getFullYear();

	$.widget("ui.calendarWidget", {
		options: {
			source: function (year, month, callback) { callback([]); },
			onMonthClick: function (year, month) {},
			onDayClick: function (str) {},
			_data: {
				events: {},
				currMonth: month,
				currYear: year,
				monthKey: "m"+year + "_" + month
			}
		},

		clearEventCache: function () {
			this.options._data.events = {};
		},

		_create: function () {
			var self = this;
			
			

			this.datepicker = $("<div display='block'/>").datepicker({
				inline:true,
				showButtonPanel: true,

				onSelect: function () {
					self.options.onDayClick.apply(self.element[0], arguments);
				},

				onChangeMonthYear: function (year, month) {

					var currData =  self.options._data;

					var newData = {
						currMonth: month,
						currYear: year,
						monthKey: "m"+year + "_" + month
					};

					self.options._data = $.extend(currData, newData);

					self._refresh();

				},

				beforeShowDay: function (d) {

					var data = self.options._data;
					var events = data.events[data.monthKey] || [];
					var css = "";
					for(var i=0; i<events.length; ++i) {
						var start = events[i].start;
						if(d.getMonth()==start.getMonth() && d.getDate()==start.getDate()) {
							css = "ui-state-active";
						}
					}
					return [true, css];
				}
			  
			})
			.click(function (e) {
				var className = e.target.className;
				if(className=="ui-datepicker-title" || className=="ui-datepicker-month" || className=="ui-datepicker-year") {
					var data = self.options._data;
					self.options.onMonthClick.call(self.element[0], data.currYear, data.currMonth);
				}
			});

			this.element.append(this.datepicker);
			
			

			this._refresh();
		},

		_refresh: function () {
			var self = this;
			var data = this.options._data;
			this.element.find('.ui-datepicker-inline').show();
			if(!data.events[data.monthKey]) {
				this.options.source.call(
					this.element[0],
					data.currYear,
					data.currMonth,
					function (events) {
						data.events[data.monthKey] = events;
						self.datepicker.datepicker("refresh");
					}
				);
			}
			else {
				this.datepicker.datepicker("refresh");
			}
		},

		destroy: function () {

			$.Widget.prototype.destroy.apply(this, arguments);
		}
	});

})(jQuery);

// Calendar widget instantiation 

jQuery(document).ready(function ($) {
	$(".gn-calendar-widget").calendarWidget({
		source: function (year, month, callback) {
			var start = new Date(year, month-1, 1);
			var end = new Date(year, month, 1);
			
			$.get(
				gn_Calendar.ajaxURL, 
				{
					action:"gncalendar-getevents",
					start:Math.round(start.valueOf()/1000),
					end:Math.round(end.valueOf()/1000)
				}, 
				function (data, status) {
					data = data || [];
					var events = [];
					for(var i=0; i<data.length; ++i) {
						var item = data[i];
						var event = {};
						if(item.className=="gn-task") {
							var parts = item.start.split("-");
							event.start = new Date(parseInt(parts[0]), parseInt(parts[1], 10)-1, parseInt(parts[2], 10));
						}
						else {
							event.start = new Date(parseInt(item.start) * 1000);
						}
						
						events.push(event);
					}
					
					callback(events);
				},
				"json"
			);
		},
		onMonthClick: function (year, month) {
			// location.href = gn_Calendar.fullCalendarURL + "?y="+year+"&m="+(month-1);
			
			location.href = $(this).attr("data-fullcalendarurl") + "?gny="+year+"&gnm="+(month-1);
		},

		onDayClick: function (str) {
			var date = new Date(str);
			var y = date.getFullYear();
			var m = date.getMonth();
			var d = date.getDate();
			
			// location.href = gn_Calendar.fullCalendarURL + "&y="+y+"&m="+m+"&d="+d;
			
			location.href = $(this).attr("data-fullcalendarurl") + "?gny="+y+"&gnm="+m+"&gnd="+d;
		}
	
	});
});


// full-calendar scripts

jQuery(document).ready(function ($) {
	
	function getParams () {
		var params = {};
		var arr = location.search ? location.search.split("&") : "";
		for(var i=0; i<arr.length; ++i) {
			var parts = arr[i].split("=");
			
			params[decodeURIComponent(parts[0])] = decodeURIComponent(parts[1]);
		}
		
		return params;
	}
	
	function getUserID () {
		if(document.forms["ccnx_calendar_user_select"]) {
			var userID = $(document.forms["ccnx_calendar_user_select"]["userid"]).val();
			
			return userID;
		}
	}
	
	var params = getParams();
	
	var today = new Date();
	
	if(!$("#calendar").length) {
		return;
	}
	
	var frm = $("#event_input").get(0);
	
	$("#calendar").fullCalendar({
		selectable:true,
		selectHelper:true,
		unselectCancel:"#dialog-form",
		
		year: params.gny || today.getFullYear(),
		month: params.gnm || today.getMonth(),
		date: params.gnd || today.getDate(),
		defaultView: params.gnd ? "agendaDay" : "month",
		
		// day/time block selection callback
		select: function (startDate, endDate, allDay) {
			
			populateForm(frm, getNewEvent(startDate, endDate, allDay));
			
			
			
			$("#dialog-form").dialog("option", "buttons", { "Cancel":closeDialog, "OK":addEvent});
			$("#dialog-form").dialog("option", "title", "Add event");
			$("#dialog-form").dialog("open");
		},
		
		// event click callback
		eventClick: function (evt) {
			
			if(!evt.editable) {
				return false;
			}
			
			populateForm(frm, evt);
			
			$("#dialog-form").dialog("option", "buttons", {"Cancel":closeDialog, "Delete this event":deleteEvent, "OK":updateEvent});
			$("#dialog-form").dialog("option", "title", "Edit event");			
			$("#dialog-form").dialog("open");
			
			return false;			
		},
		
		eventDrop:eventDragResize,
		eventResize:eventDragResize,
		slotMinutes:15,
		firstHour:9,
		editable: true,
		header:{
			left:'month,agendaWeek,agendaDay',
			center:'title'
		},
		
		
		// event data source function 
		events: function (start, end, callback) {
			setAjaxErrorHandler(function (e, xhr) {
				showAjaxError(xhr);
			});
			
			$.ajax({
				cache: false,
				url: gn_Calendar.ajaxURL,
				dataType:'json',
				data: {
					start:Math.round(start.getTime()/1000),
					end:Math.round(end.getTime()/1000),
					action:"gncalendar-getevents",
					userid:getUserID()
				},
				success: function (events) {
					events = events || [];
					for(var i=0; i<events.length; ++i) {
						var evt = events[i];
						evt.allDay = parseInt(evt.all_day) ? true : false;
						
						// This will ultimately be set on the server side!
						evt.editable = (evt.editable == undefined) ? true : parseInt(evt.editable);
					}	
					callback(events);
				}
			});
		}
		// end function
	});
	
	$("#calendar_user_select").change(function () {
		$("#calendar").fullCalendar("refetchEvents");
	});
	
	$("#dialog-form").dialog({
		zIndex:10,
		width:"auto",		
		autoOpen: false,
		modal:true,
		resizable:true,
		open:function () {
			clearInputError();
			// loadAttendeeOptions(frm.attendees);
		},
		close: function () {
			$("#calendar").fullCalendar("unselect");
			$(".datepicker").datepicker("hide");
			$("#attendee-input").val("");
		}
	});
	
	$("#attendee-input").ajaxListPicker({
		ajaxURL:gn_Calendar.ajaxURL,
		getParams : function (request) {
			return {
				action:"gncalendar-getfilteredattendees",
				filter:getAttendeeIDs(frm.attendees||[]),
				term:request.term
			};
		},
		onSelect : function (e, ui) {			
			if(!frm.attendees.length) {
				frm.attendees.push(gn_CalendarUser);
			}
			frm.attendees.push(ui.item);
			writeAttendeeList(frm);			
		}
	});
		

	function writeAttendeeList (frm) {
		var $ = jQuery;
		var attendees = frm.attendees;

		if(attendees.length) {
			$("#attendeelist").empty();
			for(var i=0; i<attendees.length; ++i) {
				var attendee = attendees[i];
				$("#attendeelist").append("<div>"+attendee.first_name+" "+attendee.last_name+" (<a id='u"+attendee.user_id+"' href='#'>Remove</a>)</div>")

			}

			$("#attendeelist a").click(function () {
				for(var i=0; i<attendees.length; ++i) {

					if("u"+attendees[i].user_id==this.id) {
						attendees.splice(i, 1);
						break;
					}
				}

				// loadAttendeeOptions(attendees);
				writeAttendeeList(frm);
				return false;
			});
		}
		else {
			$("#attendeelist").html("<p>[None]</p>");
		}

	}

	function getAttendeeIDs (attendees) {
		var arr = [gn_CalendarUser.user_id];
		for(var i=0; i<attendees.length; ++i) {
			arr.push(attendees[i].user_id);
		}

		return arr.join(",");
	}

	function populateTimeFields (startDate, endDate, allDay) {
		var $ = jQuery;
		endDate = endDate || new Date(startDate.valueOf());
		$("#start_date").datepicker("setDate", new Date(startDate.valueOf()));
		$("#end_date").datepicker("setDate", new Date(endDate.valueOf()));
		$.timePicker("#start_time").setTime(new Date(startDate.valueOf()));
		$.timePicker("#end_time").setTime(new Date(endDate.valueOf()));
		$("#all_day").get(0).checked = allDay;
		$("#all_day").change();
	}

	function eventDragResize (evt,dayDelta,minuteDelta,allDay,revertFunc) {
		var $ = jQuery;

		setAjaxErrorHandler(function (e, xhr) {
			showAjaxError(xhr, revertFunc);
		});

		evt.end = evt.end || new Date(evt.start.valueOf() + 1000 * 60 * 60);

		var eventData = {
			action:"gncalendar-eventdragresize",
			id:evt.id,
			start:Math.round(evt.start.valueOf()/1000),
			end:Math.round(evt.end.valueOf()/1000),
			all_day: evt.allDay ? 1 : 0,
			nonce:gn_Calendar.nonce
		};	

		$.post(
			gn_Calendar.ajaxURL, eventData,
			function (response) {
				if(response.status != "OK") {
					alert(response.message);
					revertFunc();
				}
				updateNonce(response);
				$("#calendar").fullCalendar("refetchEvents");
			},
			"json"
		);
	}

	function copyEventData (evt) {
		var obj = {};
		for(var key in evt) {
			if(key == "start" || key == "end") {
				obj[key] = Math.round(evt[key].valueOf()/1000);
			}
			else {
				obj[key] = evt[key];
			}
		}

		return obj;
	}

	function doFormSubmission (action) {
		if(validate(document.forms['event_input'])) {
			var $ = jQuery;
			setAjaxErrorHandler(function (e, xhr) {
				showAjaxError(xhr);
			});

			var frm = $("#event_input");
			var attendees = frm.get(0).attendees;
			for(var i=0; i<attendees.length; ++i) {
				var attendee = attendees[i];
				frm.append("<input type='hidden' name='attendees[]' value='"+attendee.user_id+","+attendee.user_login+"' />");
			}

			$.post(
				gn_Calendar.ajaxURL,
				frm.serialize() + "&action="+action + "&nonce="+gn_Calendar.nonce,
				function (data) {				
					if(data.status != "OK") {
						showInputError(data.message);
					}
					else {
						frm.eventData = null;
						closeDialog();
						$("#calendar").fullCalendar("refetchEvents");
					}
					updateNonce(data);
				},
				"json"
			);
		}
	}

	function addEvent () {	
		doFormSubmission("gncalendar-addevent");	
	}

	function updateEvent () {
		doFormSubmission("gncalendar-updateevent");
	}

	function deleteEvent () {
		if(confirm("You are about to delete this event. Continue?")) {
			doFormSubmission("gncalendar-deleteevent");
		}
	}

	function closeDialog () {
		jQuery("#dialog-form").dialog("close");
	}

	function showAjaxError (xhr, revertFunc) {
		alert("Server error: "+xhr.status+" "+xhr.statusText);
		if(revertFunc) {
			revertFunc();
		}
	}

	function setAjaxErrorHandler (func) {
		var $ = jQuery;
		$(document).unbind("ajaxError");
		$(document).ajaxError(func);
	}

	function validate (frm) {
		var $ = jQuery;
		clearInputError();

		if(!hasInput(frm.title)) {
			showInputError("Please enter an event title.");	
			return false;
		}

		if(parseInt(frm.start.value) > parseInt(frm.end.value)) {
			showInputError("End time is before start time.");	
			return false;
		}

		return true;

	}

	function getNewEvent (startDate, endDate, allDay) {
		return {
			id:"",
			start:startDate,
			end:endDate,
			allDay:allDay,
			all_day:allDay,
			title:"New Event",
			description:"",
			location:"",
			visibility:"public",
			attendees:[]

		};
	}

	function populateForm (frm, evt) {

		frm.eventData = evt;
		populateTimeFields (evt.start, evt.end, evt.allDay);

		frm.id.value = evt.id;
		frm.title.value = evt.title;
		frm.location.value = evt.location;
		frm.description.value = evt.description;

		frm.attendees = [];

		for(var i=0; i<evt.attendees.length; ++i) {
			frm.attendees.push(evt.attendees[i]);
		}

		jQuery("#event_input select[name='visibility'] option[value='"+evt.visibility+"']").attr("selected", "selected");

		jQuery("#event_input input[name='attendees[]']").remove();

		writeAttendeeList(frm);
	}

	function updateNonce (data) {
		gn_Calendar.nonce = data.nonce;
	}

	function showInputError (errMsg) {
		var $ = jQuery;
		var msg = $("#msg");
		msg.addClass("ui-state-error");
		msg.text(errMsg);
	}

	function clearInputError () {
		var $ = jQuery;
		var msg = $("#msg");
		msg.removeClass("ui-state-error");
		msg.text("Enter event data.");
	}

	function hasInput (elem) {
		var val = elem.value.replace(/^\s+|\s+$/, "");

		return val.length ? true : false;	
	}

});

