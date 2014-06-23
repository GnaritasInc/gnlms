(function ($) {
	
	/**
	
	.ajaxListPicker()
	
	Extension of the jQuery UI autocomplete widget using an ajax callback as data source
	and clearing the user's input after selection.
	
	Usage:
	
	$("input[type='text']").ajaxListPicker({
			ajaxURL:"http://example.com/ajax.php",			
			getParams: function (request) {  
				return {queryText:request.term};
			}, 			
			onSelect: function (e, ui) { 
				alert("Selected item [Object]: "+ui.item);
			}			
	});
	
	See http://jqueryui.com/demos/autocomplete/ for more info.
	
	**/
	
	
	$.fn.ajaxListPicker = function (arg) {
		
		var settings = {
			minLength:2,
			source: function (request, response) {
				$.getJSON (
					arg.ajaxURL,
					arg.getParams ? arg.getParams(request) : {},
					response
				);
			},
			select: function (e, ui) {			
				$(this).val("");	
				if (arg.onSelect) arg.onSelect(e, ui);
				return false;
			}
		};		
		
		if(typeof arg === "object" || !arg) { // initialize the list picker
			return this.autocomplete($.extend(settings, arg));
		}
		else { // pass it through to $.autocomplete()
			return this.autocomplete.apply(this, arguments);
		}
		
	}
	
	/**
	
	.ajaxDialogForm()
	
	Extension of the jQuery UI dialog widget for form submission. Matched elements should contain an 
	html form element. Only the first contained html form is submitted. Form  submission is via ajax 
	and is triggered using jQuery UI dialog buttons (*not* a standard html form "submit" button) 
	and by default uses the http POST method.
	
	Options are the same as for the jQuery UI dialog (see http://jqueryui.com/demos/dialog/), 
	except by default the ajaxDialogForm is modal, does not open automatically, has its width 
	and height set to "auto", and has two buttons: "OK", which submits the form, and "Cancel", 
	which closes the dialog without doing anything.
	
	.ajaxDialogForm also accepts the following options for form submission:
		
		action: The URL to which form data should be submitted. The default is the 
		current url.
		
		method: The jQuery ajax function used to submit the form. Expected values 
		are "post" (default) and "get".
		
		dataType: The expected data type of the server response. (Default "json".)
		
		onSubmit: Callback function executed before form submission. Returning false
		cancels form submission and execution context is the html form element (i.e. exactly
		like the html form element's "onsubmit" handler). Can be used for input validation.
		
		onSuccess: Callback function executed when the ajax form submission completes. This
		function is called from the jQuery "get" or "post" method's "success" callback with the 
		same parameters, but executed in the context of the DOM element whose contained form was
		submitted. (Tip: Call $(this).ajaxDialogForm("close") in this function to close the 
		dialog after successful form submission.)
		
		onAjaxError: Callback function passed to jQuery.ajaxError().
			
	
	**/
	
	$.fn.ajaxDialogForm = function (arg) {
		var settings = {
			autoOpen:false,
			modal:true,
			width:"auto",
			height:"auto",
			resizable:true,
			buttons:{
				"Cancel": function () {
					$(this).ajaxDialogForm("close");
				},
				"OK": function () {
					$(this).ajaxDialogForm("submit");
				}

			},
			onSubmit: function () {return true;},
			onSuccess: function (data, status, xhr) {
				alert("Form submitted.");
			},
			onAjaxError: null,
			method:"post",
			action:location.href,
			dataType:"json"			
		};
		
		var methods = { 
			"init": function (options) {
				var data = $.extend(settings, options);
				this.data("ajaxDialogForm", data);
				return this.dialog(data);
			},
			"submit": function () {
				var settings = this.data("ajaxDialogForm");
				var $frm = this.find("form");
				var frm = $frm.get(0);
				var dlg = this.get(0);
				if(frm && settings.onSubmit.apply(frm)) {
					if(settings.onAjaxError){
						$(document).unbind("ajaxError");
						$(document).ajaxError(settings.onAjaxError);
					}
					$[settings.method.toLowerCase()](
						settings.action,
						$frm.serialize(),
						function () {
							settings.onSuccess.apply(dlg, arguments);
						},
						settings.dataType
					);
				}
			}
		};
		
		if(typeof arg === "object" || !arg) { 
			return methods.init.apply(this, arguments);
		}
		else if(methods[arg]) {
			return methods[arg].apply(this, Array.prototype.slice.call(arguments, 1));
		}
		else { 
			return this.dialog.apply(this, arguments);
		}		
		
	}
	
})(jQuery);