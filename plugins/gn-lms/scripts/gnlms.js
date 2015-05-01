
function gnlms_css_hacks($) {

	$("#setupform label[for='user_name']").hide();
	$("#setupform input[name='user_name']").attr("type","hidden");
	$("#setupform input[name='user_name']").nextAll("span.hint").hide(); //attr("type","hidden");

	$("#setupform input[name='user_email']").on("input",function() {
			$("#setupform input[name='user_name']").val($(this).val());
			});

	$("#theme-my-login.profile table tr#password").remove();

}


jQuery(document).ready(function ($) {
	gnlms_css_hacks($);
	
	$("a.gnlms-certificate").click(function () {
		window.open(this.href, '', 'resizable=yes,scrollbars=yes,width=800,height=600');
		return false;
	});

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

	$("#gnlms-sc-dialog").dialog("option", {
		"buttons":{
			"Close": function () { $(this).dialog("close"); },
			"Checkout": function () { location.href="/checkout/"; }
		}
	});

	function setCheckoutButtonState () {
		$("button:contains('Checkout')", $("#gnlms-sc-dialog").parents(".ui-dialog")).button("option", "disabled", $("#gnlms-sc-dialog .gnlms-shopping-cart").length ? false : true);
	}

	setCheckoutButtonState();

	$("#gnlms-sc-dialog").on("submit",  "form.shopping_cart_update", function (e) {
		e.preventDefault();
		ajaxFormSubmit.call(this, function (data, textStatus, xhr) {
			if(data.status != "OK") {
				reportAjaxError(data.msg);
			}
			else {
				$(".gnlms-shopping-cart-content", $("#gnlms-sc-dialog")).replaceWith(data.html);
				setCheckoutButtonState();
				$("#gnlms-sc-dialog").on("dialogclose", function () {
					var params = getParams();
					if ("msg" in params) {
						delete params.msg;
						location.replace(location.pathname + "?" + $.param(params));
					}
					else location.reload(true);
				});
			}
		});

		return false;
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

	function ajaxFormSubmit(complete) {
		complete = complete || ajaxPostComplete;
		$.post(gnlms.ajaxurl, $(this).serialize(), complete, "json");
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

	$(".gnlms-button:not('#login-button')").button();

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

						jQuery("#gnlms-course-monitor").dialog({
							"modal":true,
							"beforeClose": function( event, ui ) {
								return (gnLMSCourseMonitorClose());
							},
							"close":function () {
								location.reload(true);
							}
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
	
	initToggleText();

});

function initToggleText () {
	var $ = jQuery;
	
	$(".gn-toggle-text").hide();
	$(".gn-toggle")
	.attr("title", "Click to expand")
	.click(function () {
		$(this).next(".gn-toggle-text").toggle();
		var expanded = $(this).next(".gn-toggle-text").is(":visible") ? true : false;
		$(this).attr("title", "Click to "+(expanded ? "hide" : "expand"));
		$(this).toggleClass("expanded", expanded);
	});
}

function getParams () {
	var params = {};
	var arr = location.search ? location.search.replace(/^\?/, "").split("&") : "";
	for(var i=0; i<arr.length; ++i) {
		var parts = arr[i].split("=");

		params[decodeURIComponent(parts[0])] = decodeURIComponent(parts[1]);

	}
	return params;
}

