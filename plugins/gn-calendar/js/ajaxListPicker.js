(function ($) {
	
	/**
	
	Wrapper for jQuery UI autocomplete using an ajax callback as data source
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
	
	(See http://jqueryui.com/demos/autocomplete/ for more info.)
	
	**/
	
	
	$.fn.ajaxListPicker = function (arg) {
		
		var settings = {
			minLength:2,
			source:function (request, response) {
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
	
})(jQuery);