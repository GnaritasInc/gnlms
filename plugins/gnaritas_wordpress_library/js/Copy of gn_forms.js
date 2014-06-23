

//DS: Client side form population script.


function addLabelProperties( f )
{
    //    Collect all label elements in form, init vars
    if ( typeof f.getElementsByTagName == 'undefined' ) return;
    var labels = f.getElementsByTagName( "label" ),
        label,
        elem,
        i = j = 0;

    //    Loop through labels retrieved
    while ( label = labels[i++] )
    {            
        //    For Opera 6
        if ( typeof label.htmlFor == 'undefined' ) return;
        
        //    Retrieve element
        elem = f.elements[label.htmlFor];

        if ( typeof elem == 'undefined' )
        {    //    No element found for label
            //alert( "No element found for label: " + label.htmlFor );
        }
        else if ( typeof elem.label != 'undefined' )
        {    //    label property already added
            continue;
        }
        else if ( typeof elem.length != 'undefined' && elem.length > 1 && elem.nodeName != 'SELECT' && elem.nodeName )
        {    //    For checkbox arrays and radio-button groups
            for ( j = 0; j < elem.length; j++ )
            {
             try {
              elem.item( j ).label = label;
              }
              catch (e) {
              
              	if (elem.innerHTML)
              		alert (elem.innerHTML);
              	
              	}
            }
        }
        //    Regular label
        try {
        	elem.label = label;   
        }
        catch (e) {
        }
    }
}


function populateForm(form,dataObject) {
	//var form =form;
	
	if (!form) {
		return;
	}

	addLabelProperties(form)
	
	if (dataObject) {
		for (var i=0;i<form.elements.length;i++) {
			element =form.elements[i]

			name = form.elements[i].name
			name2=name?name.replace(/\[\]/gi,""):"";
			

			value = dataObject[name]?dataObject[name]:dataObject[name2] 
			
			// DS: Value is undefined, not null, when name or name2 not found in dataObject
			
			if (value!=undefined) {
			
				// DS: converting value to string
				
				value += "";
				
				//Text
				if (form.elements[i].type=="text") {
					form.elements[i].value = value;
				}

				if (form.elements[i].type=="textarea") {
					form.elements[i].value = value;
				}


				//Hidden
				if (form.elements[i].type=="hidden") {
					form.elements[i].value = value;
				}
				//select-one
				if (form.elements[i].type=="select-one") {
					for (var j=0; j<element.options.length;j++) {
						if (element.options[j].value ==value) {
							element.selectedIndex =j
						}
					}

				}

				if (form.elements[i].type=="checkbox") {
										
					// DS: This is -1 (i.e. true) when the comma isn't found, so adding ">=0".
					
					if (value.indexOf(",")>=0) {
					   valueArray = value.split(",");
					   for (index in valueArray) {
					   	ivalue = valueArray[index];
					   	if (form.elements[i].value==ivalue) {
					   		form.elements[i].checked=true;
					   	}
					   }				
					}
					else {
						if (value && form.elements[i].value==value) {
							form.elements[i].checked=true;
						}
						else {
							form.elements[i].checked=false;
						}
					
					
					
					}

				}
				if (form.elements[i].type=="radio") {
					
					if (value!=null) {
						if (form.elements[i].value==value) {
							form.elements[i].checked=true;
						}
					}
					

				}

				// need for others (checkbox etc)
			}





		}
	}
}

function getLabel(name) {
	if (element= document.getElementById(name)) {
		return (element.label)
	}
}

// DS: Generic form validator:
   

var gn_FormValidator = {
	
	
	// Validation function.
	validate: function (form, validationFields, errorCallback) {
		errorCallback = errorCallback || function (errors) {alert("Invalid input in "+errors.length+" fields.");};
		var errors = [];
		for(var i=0; i<validationFields.length; ++i) {
			var field = validationFields[i];
			var elem = form.elements[field.name];
			if(elem && field.validFunc && !field.validFunc(elem)) {
				errors.push(field);
			}
		}
		
		if(errors.length) {
			errorCallback(errors);
			return false;
		}
		else return true;
	},
	
	// Helper functions for common validation tasks:
	
	hasInput: function (elem) {
		var re = /\S+/;
		return re.test(elem.value);
	},
	
	hasOneChecked: function (arr) {
		for(var i=0; i<arr.length; ++i) {
			if(arr[i].checked) {
				return true;
			}
		}
		
		return false;
	}
};



//DS: Legacy client-side validation code below.

function checkForMatchedInput(item1,item2, errorMsg, label) {
	var label = document.getElementById(item2 +"Label")
	
	label=label?label:getLabel(item2);
	/*
	
	if (!label)
		alert ("Can't find label:" +item2)
	*/
	


	if (document.forms["registration"].elements[item1].value != document.forms["registration"].elements[item2].value) {
		errors+=errorMsg +".<br/>";
		markError(document.getElementById(item2 +"Label"));
	}
	else {
		markNormal(document.getElementById(item2 +"Label"));

	}

}


function checkForCreditCardInput(form, item1, errorMsg,label) {
	label=label?label:getLabel(item1);
	/*
	
	if (!label)
		alert ("Can't find label:" +item2)
	*/
	

	if (!isCreditCard(form.elements[item1].value)) {
		errors+=errorMsg +".<br/>";
		markError(document.getElementById(item1 +"Label"));
	}
	else {
		markNormal(document.getElementById(item1 +"Label"));

	}

}


function isCreditCard(strInput)
{
	// Encoding only works on cards with less than 19 digits
	strInput+="";
	if (strInput.length > 19) return (false);

	var sum = 0;
	var mul = 1;
	var l = strInput.length;

	for (i = 0; i < l; i++)
	{
		var digit    = strInput.substring(l-i-1,l-i);
    		var tproduct = parseInt(digit ,10) * mul;

	    	if (tproduct >= 10)
		{ sum += (tproduct % 10) + 1; }
    		else
		{ sum += tproduct; }

    		if (mul == 1)
		{ mul++; }
	    	else
		{ mul--; }
  	}

  	if ((sum % 10) == 0)  return (true);
  	else                  return (false);
}





var errors ="";

function markError(label) {
	try {
	if (label) {
		if (label.className && (label.className.indexOf("error")<0)) {
			label.origClass = label.className;
			label.className = label.origClass +" error"
		}
		else {
		if(!label.className)
			label.className = "error"

		}
	}
	}
	catch (e) {
	
	alert(e.description + label);
	}


}

function markNormal(label) {
	if (label) {
		if (label.origClass) {
			label.className = label.origClass
		}
		else if (label.className=="error") {
			label.className = "";
		}
	}

}

function selectInputValue(form, itemName) {
	var label = document.getElementById(itemName +"Label")
	var formElement = form.elements[itemName];

	if (!label)
		alert ("Can't find label:" +itemName)


	return(formElement.options[formElement.selectedIndex].value)
}


function checkForSomeInput(form, itemName, errorMsg,label) {
	label=label?label:getLabel(itemName);
	/*
	
	if (!label)
		alert ("Can't find label:" +item2)
	*/
	
	try {
	if (form.elements[itemName].value == "") {
		errors+=errorMsg +".<br/>";
		markError(label);
	}
	else {
		markNormal(label);
		return (true);

	}
	}
	catch(e) {
	
	alert ("Error finding element for" + itemName);
	}
	
}


function checkForCheckboxInput(form, itemName, errorMsg,label) {
	label=label?label:getLabel(itemName);
	/*
	
	if (!label)
		alert ("Can't find label:" +item2)
	*/

	var checked= false;
		

	if (form.elements[itemName].length) {
		 for (counter = 0; counter < form.elements[itemName].length; counter++)
			{
			if (form.elements[itemName][counter].checked)
				checked = true; 
			
			}
	}
	
	
	if (!checked) {

		errors+=errorMsg +".<br/>";
		markError(label);
	}
	else {
		markNormal(label);
		return (true);

	}
	
}

function checkForRadioInput(form, itemName, errorMsg,label) {
	label=label?label:getLabel(itemName);
	/*
	
	if (!label)
		alert ("Can't find label:" +item2)
	*/

	checked=false

	if (form.elements[itemName]) {

	if (form.elements[itemName].length) {
	 for (counter = 0; counter < form.elements[itemName].length; counter++)
		{
		if (form.elements[itemName][counter].checked)
			checked = true; 
		
		}
	}
	else {
		checked=form.elements[itemName].checked
	}
	
	}
	

	if (!checked) {

		errors+=errorMsg +".<br/>";
		markError(label);
	}
	else {
		markNormal(label);
		return (true);

	}
	
}


function checkForSpecialInput(form, itemName,pattern, errorMsg) {
	var label = document.getElementById(itemName +"Label")

	label=getLabel(itemName);


	var re = new RegExp(pattern,"i" )
	if (!label)
		alert ("Can't find label:" +itemName)
	

		if (!re.test(form.elements[itemName].value)) {
			errors+=errorMsg +".<br/>";
			markError(document.getElementById(itemName +"Label"));
		}
		else {
			markNormal(document.getElementById(itemName +"Label"));

		}
}

// This does not work
function checkForSelectInput(form, itemName, errorMsg,label) {
	var formElement = form.elements[itemName];

	label=label?label:getLabel(itemName);

	if (formElement.options[formElement.selectedIndex].value == "") {
		errors+=errorMsg +".<br/>";
		markError(label);
	}
	else {
		//alert ("Normal:" + formElement.options[formElement.selectedIndex].value);
		markNormal(label);

	}
	
}

function checkForMatchedInput(form,  item1,item2, errorMsg) {
	var label = document.getElementById(item2 +"Label")

	label=getLabel(item2);


	if (!label)
		alert ("Can't find label:" +item2)

	else {
		if (form.elements[item1].value != form.elements[item2].value) {
			errors+=errorMsg +".<br/>";
			markError(label);
		}
		else {
			markNormal(label);

		}
	}
}


var BrowserDetect = {
	init: function () {
		this.browser = this.searchString(this.dataBrowser) || "An unknown browser";
		this.version = this.searchVersion(navigator.userAgent)
			|| this.searchVersion(navigator.appVersion)
			|| "an unknown version";
		this.OS = this.searchString(this.dataOS) || "an unknown OS";
	},
	searchString: function (data) {
		for (var i=0;i<data.length;i++)	{
			var dataString = data[i].string;
			var dataProp = data[i].prop;
			this.versionSearchString = data[i].versionSearch || data[i].identity;
			if (dataString) {
				if (dataString.indexOf(data[i].subString) != -1)
					return data[i].identity;
			}
			else if (dataProp)
				return data[i].identity;
		}
	},
	searchVersion: function (dataString) {
		var index = dataString.indexOf(this.versionSearchString);
		if (index == -1) return;
		return parseFloat(dataString.substring(index+this.versionSearchString.length+1));
	},
	dataBrowser: [
		{
			string: navigator.userAgent,
			subString: "Chrome",
			identity: "Chrome"
		},
		{ 	string: navigator.userAgent,
			subString: "OmniWeb",
			versionSearch: "OmniWeb/",
			identity: "OmniWeb"
		},
		{
			string: navigator.vendor,
			subString: "Apple",
			identity: "Safari"
		},
		{
			prop: window.opera,
			identity: "Opera"
		},
		{
			string: navigator.vendor,
			subString: "iCab",
			identity: "iCab"
		},
		{
			string: navigator.vendor,
			subString: "KDE",
			identity: "Konqueror"
		},
		{
			string: navigator.userAgent,
			subString: "Firefox",
			identity: "Firefox"
		},
		{
			string: navigator.vendor,
			subString: "Camino",
			identity: "Camino"
		},
		{		// for newer Netscapes (6+)
			string: navigator.userAgent,
			subString: "Netscape",
			identity: "Netscape"
		},
		{
			string: navigator.userAgent,
			subString: "MSIE",
			identity: "Explorer",
			versionSearch: "MSIE"
		},
		{
			string: navigator.userAgent,
			subString: "Gecko",
			identity: "Mozilla",
			versionSearch: "rv"
		},
		{ 		// for older Netscapes (4-)
			string: navigator.userAgent,
			subString: "Mozilla",
			identity: "Netscape",
			versionSearch: "Mozilla"
		}
	],
	dataOS : [
		{
			string: navigator.platform,
			subString: "Win",
			identity: "Windows"
		},
		{
			string: navigator.platform,
			subString: "Mac",
			identity: "Mac"
		},
		{
			string: navigator.platform,
			subString: "Linux",
			identity: "Linux"
		}
	]

};
BrowserDetect.init();
