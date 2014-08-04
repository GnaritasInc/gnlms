
jQuery(document).ready(function ($) {
	$("div.gnlms-form-dialog").each(function () {
		var width = $(this).attr("data-width") ? parseInt($(this).attr("data-width")) : 500;
		var height = $(this).attr("data-height") ? parseInt($(this).attr("data-height")) : 300;
		$(this).dialog({
			"dialogClass":"wp-dialog",
			"modal":true,
			"autoOpen": false,
			// DS: Changing to array format to allow for easier post-rendering manipulation
			/*
			"buttons":{
				"OK":function () {
					var formID = $(this).attr("data-form-id");
					var frm = formID ? $("#"+formID).get(0) : $(this).find("form").get(0);
					if(window.tinyMCE) {
						tinyMCE.triggerSave();
					}
					if(frm && validateForm(frm)) ajaxFormSubmit.apply(frm);
				},
				"Cancel":function () {
					$(this).dialog("close");
				}
			},
			*/
			"buttons":[
				{
					"text":"OK",
					"click":function () {
						var formID = $(this).attr("data-form-id");
						var frm = formID ? $("#"+formID).get(0) : $(this).find("form").get(0);
						if(window.tinyMCE) {
							tinyMCE.triggerSave();
						}
						if(frm && validateForm(frm)) ajaxFormSubmit.apply(frm);
					}
				},
				{
					"text":"Cancel",
					"click":function () {
						$(this).dialog("close");
					}
				}
			],
			"width": width,
			"height": height
		});
	});
	

	
	$("a.gnlms-open-dialog").click(function (event) {
		var myDialog = $("#"+$(this).attr("data-dialogid"));
		if(myDialog.length) {
			myDialog.dialog("open");
			event.preventDefault();
			return false;
		}
	});
	
	$("a.gnlms-open-dialog:not(#menu a)").button();
	
	function ajaxFormSubmit() {
		$.post(gnlms.ajaxurl, $(this).serialize(), ajaxPostComplete, "json");	
	}
	
	
	
	function ajaxPostComplete (data, textStatus, xhr) {
		if(data.status != "OK") {
			reportAjaxError(data.msg);
		}
		else {
			$(this).parent("div.gnlms-form-dialog").dialog("close");
			location.reload(true);
		}		
	}
	
	function reportAjaxError (msg) {
		displayError(msg);
	}
	
	
	$("form#gnlms-course-user-selection").submit(function () {		
		$("#gnlms-found-users").load(gnlms.ajaxurl, $(this).serialize());		
		return false;		
	});
	
	$("#gnlms-found-users").on("click", "input:checkbox.gn-select-all", function () {
		var checked = this.checked;
		var name = $(this).attr("data-name");
		$("input:checkbox[name='"+name+"']").each(function () {
			this.checked = checked;
		});
	});
	
	$("a.gnlms-button").button();
	
	$("a.gnlms-open-dialog-form").click(function (event) {
		var dialogSelector = "#"+$(this).attr("data-dialogid");
		var queryParams = getParams();
		var data = {
			"action":"gnlms-fetch-form",
			"type":$(this).attr("data-type"),
			"gnlms_nonce":gnlms.nonce
		};
		if(queryParams.id) data.context_id = queryParams.id;
		var id = $(this).attr("data-id");
		var dialogTitle = $(dialogSelector).dialog("option", "title");
		if (id) {
			data.id = id;
			dialogTitle = dialogTitle.replace(/^Create |^Add /i, "Edit ");
			$(dialogSelector).dialog("option", "title", dialogTitle);
		}
		else {
			dialogTitle = dialogTitle.replace(/^Edit /i, "Create ");
			$(dialogSelector).dialog("option", "title", dialogTitle);
		}
		
		$(dialogSelector).load(gnlms.ajaxurl, $.param(data), function () {
			setDatePickers();
			$(dialogSelector).dialog("open");
			
		});
		
		event.preventDefault();
		return false;
		
	});
	
	$("a.gnlms-course-launch").click (function(event) {
	
		if ($("#gnlms-course-monitor").length) {
		
			$("#gnlms-course-monitor").remove();
		}
		
		if (!$("#gnlms-course-monitor").length) {
				$("<div title='Course Monitor' id='gnlms-course-monitor'></div>").appendTo("body")
				.load($(this).attr("href"), 
				
					function (responseText, textStatus, XMLHttpRequest) {
						
						jQuery("#gnlms-course-monitor").dialog(
						{
						"modal":true,
						"beforeClose": function( event, ui ) {return (gnLMSCourseMonitorClose());}
						});
						launchCourse(courseURL);
					}
						
					);		
		}
		/*
		IE no like re-use
		else {
			var myDialog = $("#gnlms-course-monitor");

			$(myDialog).load($(this).attr("href"), function () {

				jQuery("#gnlms-course-monitor").dialog("open");
				launchCourse(courseURL);
				

			});
		}
		*/
		
		
		//$(myDialog).dialog("open");
		
		event.preventDefault();
		return false;
		
	});
	
	
	
	$("a.gnlms-announcement-delete").click(function (event) {
		if(!confirm("Delete this announcement?")) {
			return false;
		}
		
		var data = {
			"id":$(this).attr("data-id"),
			"action":"gnlms-delete-announcement",
			"gnlms_nonce":gnlms.nonce
		};
		$.post(gnlms.ajaxurl, data, ajaxPostComplete, "json");
		
		event.preventDefault();
		return false;
	});
	
	$("#gnlms-course-user-assignment").on("dialogopen", function () {
		$("#gnlms-course-user-selection").get(0).reset();
		$("#gnlms-found-users").empty();
		var buttons = $(this).dialog("option", "buttons");
		buttons[0].text="Register Users";
		buttons[0].disabled = true;
		
		$(this).dialog("option", "buttons", buttons);
		
	});
	
	$("#gnlms-course-user-assignment").on("click", function () {		
		var buttons = $(this).dialog("option", "buttons");
		var checked = $(this).find("input:checked").length ? true : false;
		
		buttons[0].disabled = !checked;
		
		$(this).dialog("option", "buttons", buttons);
	});
	
});


function getParams () {
	var params = {};
	var arr = location.search ? location.search.replace(/^\?/, "").split("&") : "";
	for(var i=0; i<arr.length; ++i) {
		var parts = arr[i].split("=");

		params[decodeURIComponent(parts[0])] = decodeURIComponent(parts[1]);

	}
	return params;
}

