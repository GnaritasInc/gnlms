jQuery(document).ready (function (){

	jQuery("#registerform p:first").hide();
	jQuery("<hr width='50%' align='center'/>").insertBefore("#registerform p:first");
	jQuery("#registerform p:eq(1)").css("margin-top","20px");
	jQuery("#user_email").change ( function () {
		jQuery("#user_login").val(jQuery("#user_email").val());
		
	});

jQuery("#registerform p:eq(1) label").prepend("*");
	
});
