


function gn_populateForm (frm, data) {
	var $ = jQuery;

		if(data) {
			for(var name in data){
				if ($(frm).find('input:radio[name=' +name +']').length>0) {


					$(frm).find('input:radio[name=' +name +']').each(function(element){
						if($(this).val()==data[name]){
							// DS: This prevents it from being unchecked by reset()
							// Changing this and below to set checked *property* as opposed to attribute.
							// $(this).attr("checked","checked")
							this.checked = true;
						}

					});

				}
				else if ($(frm).find('input:checkbox[name^=' +name +']').length>0) {
					$(frm).find('input:checkbox[name^=' +name +']').each(function(element){
						//alert ($(this).val() +"--"+ data[name]);

						    if($(this).val()==data[name] )
						    {
							// $(this).attr("checked","checked")
							this.checked = true;
						    }
						    else {

							// $(this).attr("checked",false)
							this.checked = false;
						    }



						    if ($.isArray(data[name])) {
						    if  ($.inArray( $(this).val(), data[name] )>=0) {

							// $(this).attr("checked","checked")
							this.checked = true;
						    }
						     else {

								//$(this).attr("checked",false)
								this.checked = false;
							}
						    }

						if (typeof(data[name])=="string") {

							dataval= data[name].split(",");

							if ($.isArray(dataval)) {
								if  ($.inArray( $(this).val(), dataval )>=0) {

									// $(this).attr("checked","checked")
									this.checked = true;
								}
								else {

									// $(this).attr("checked",false)
									this.checked = false;
								}
							}
						}

					});


				}

				else {

				/*
				if (frm.id=="student_classroom_assignment_form") {
					alert(name +"->" + data[name]);
				}
				*/

				$(frm[name]).val(data[name]);

				 /*
				 if (frm.id=="student_classroom_assignment_form"){
				 	alert("val:"+$(frm[name]).val());
				 	alert("name:"+$("#" + name).html());
				}
				*/

				 }




			}


			$(frm).trigger("formPopulated");
		}
		else { // no form data
			// DS: "record_status" should default to checked

			$(frm).find("input:checkbox[name='record_status']").attr("checked", "checked");
		}
}

var _gnValidationRules = {
	"gnlms-add-edit-subscription-code":{
		"code":{
			"msg":"",
			"validFunc": function (elem) {
				//alert("Validating "+elem.name);
				var pattern = /^[a-z0-9_-]+$/i;
				if(!hasInput(elem)) {
					this.msg = "Please enter the code."
					return false;
				}				
				else if (!pattern.test(elem.value)) {
					this.msg = "Registration code should contain only letters, numbers,  hyphens or underscores."
					return false;
				}
				
				else return true;
			}
		}
	}
};


function hasInput (elem) {
	var val = elem.value.replace(/^\s+|\s+$/g, "");
	return val.length ? true : false;
}

function validateForm (frm) {
	var validationRules = _gnValidationRules[frm.id];
	if(validationRules) {
		for(var name in validationRules) {
			var rule = validationRules[name];
			var elem = frm.elements[name];
			if(!rule.validFunc(elem)) {
				displayError(rule.msg);
				return false;
			}
		}
	}
	
	return true;
}


function displayError (msg) {
	alert(msg);
}



function gn_form_forward(formElement, targetName) {
	jQuery("#"+targetName).val (formElement.val());
}

function gn_form_other_option(formElement, targetName, containerName) {
	if (formElement.val().toLowerCase()=="other") {
		jQuery("#"+targetName).val ("");
		jQuery("#"+containerName).show ();
	}
	else {
	jQuery("#"+containerName).hide ();

	}

}

// DS: add datepicker only when HTML5 date input not supported
function setDatePickers () {
	var $ = jQuery;
	$("input[type='date']").each(function () {
		if(this.type == "text") { // will be "date" for browsers that can render the HTML5 date input
			$(this).datepicker({dateFormat:"yy-mm-dd"});
		}
	});
}
jQuery(document).ready(function ($) {
	setDatePickers();
	$("form:not(.gn-list-form)").find(":button, :submit, :reset").button();
});

