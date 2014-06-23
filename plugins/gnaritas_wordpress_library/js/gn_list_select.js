jQuery(document).ready (function ($) {

	listSelectInit();
	

	setEditHandlers();

	function listSelectInit(container) {
		container = container?container:document;
		
		

		
		$(container).find(".select-list-item input:checkbox").unbind("change");  
		$(container).find("input.list-select-all").unbind("change");
		$(container).find("input.list-select-reset").unbind("click");
		

		$(container).find(".select-list-item input:checkbox").change(function() { list_select_change(this) });
		$(container).find("input.list-select-all").change (function() {list_select_change_all(this);});
		$(container).find("input.list-select-reset").click(function(e) {list_select_reset(e)});

		
		$(container).find(".list-selected-form-item").each (function(pos,item) { create_select_list_display_item(item);});

		$(container).find("input.select-list-list-item").each (function(pos,item) {list_select_check(this)} );
		
		var widget = $(container).find("div.listwidget").get(0);
		if (widget) {
			widget.refreshWidget = function () {refreshWidget(widget)}
		}

	}
	
	
	function refreshContainedWidget (widgetContainer) {

		params = $(widgetContainer).find("form").serialize();
		
		loadPageComponent (
				widgetContainer, 
				params, 
				function () {setEditHandlers(widgetContainer)},
				handleListLoadError
			);		
	}
	
		
	function refreshWidget (widget) {
	
	 refreshContainedWidget ($(widget).parent().get(0))
			
	}
	
	$("div.listwidget").each (function (pos, item) {item.refreshWidget = function () {refreshWidget(item)}
		} );
	
	
	function setAjaxErrorHandler (func) {		
			jQuery(document).unbind("ajaxError");
			jQuery(document).ajaxError(func);
	}
	
	
	function setWaitingState (isWaiting) {
			// DS: Can do something fancier, but this works.
			// $("body").css("cursor", (isWaiting ? "wait":"auto"));
	}
	
	function handleListLoadError (e, xhr) {
		alert("Unable to refresh list: "+xhr.status+" "+xhr.statusText);
	}

	function setEditHandlers (container) {
		container = container?container:document;
	
		$(container).find("a.gn-list-modify-mode").click (function () {list_modify_mode(this); });
		$(container).find("a.gn-list-modify-cancel").click (function () {list_modify_cancel(this); });
		$(container).find("a.gn-list-modify-save").click (function () {list_modify_save(this); });
		
		
		listSelectInit(container);
		
	}
	
	function handlePostError (e, xhr) {
			alert("Server error: "+xhr.status+" "+xhr.statusText);
	}
	
	
	function loadPageComponent (selector, params, onComplete, onAjaxError) {
		setAjaxErrorHandler(onAjaxError || handlePostError);
		setWaitingState(true);
		$(selector).load(
			gn_TaskManager.ajaxURL,
			params,
			function (responseText, statusText, xhr) {
				if(onComplete) onComplete(responseText, statusText, xhr);
				setWaitingState(false);
			}
		);
	}
	
	
	function list_modify_mode(item) {
	
		var widgetContainer= $(item).parents("div.widget_container").get(0);
		$(widgetContainer).find("form input[name='mode']").val("edit");	
	
		refreshContainedWidget(widgetContainer);
		
		
	}


	function list_modify_cancel(item) {
	
		var widgetContainer= $(item).parents("div.widget_container").get(0);
		var widget= $(item).parents("div.widget").get(0);
		$(widgetContainer).find("form input[name='mode']").val("view");
	
		widget= $(item).parents("div.widget").get(0);
		widget.refreshWidget();
		
		
	}
	
	function list_modify_save(item) {
		
		var widgetContainer= $(item).parents("div.widget_container").get(0);
		$(widgetContainer).find("form input[name='mode']").val("save");	

		var widget= $(item).parents("div.widget").get(0);
		
		
		
		widget.refreshWidget();
			
			
	}
	

});


function list_select_reset(event) {
	$("#list-select-selected-items a").trigger("click", event.target);
	
	$(event.target).prev("div.widget").find ("input:checkbox").attr("checked",false);
}

function list_select_change_all(item) {
	newCheck = $(item).attr("checked");
	

	$(item).parents("table").find (".select-list-item input:checkbox").each(function (pos,item) {
										if ($(item).attr("checked")!=newCheck) {
											$(item).attr("checked",newCheck);
											$(item).trigger("change");
										}
										});
}

function list_select_check(item) {
	id = $(item).attr("name").substr(22);
	if ($("#list-selected-form-item-"+id).length>0) {
		$(item).attr("checked","checked");
	}
}


function create_select_list_display_item(item) {
	name = $(item).find("input.gn_list_select_name").val();
	id = $(item).find("input.gn_list_select_id").val();
	
	addSelectDisplayItem(item,id,name);
	

	
}
	
function list_item_get_name (item) {
	nameField = "name";
	
	nameString= $(item).parent().nextAll("[fieldname="+nameField+"]").text();
	
	if (!nameString) {
		nameString = $(item).parent().next("td:not(.id)").text();
	}
	
	if (!nameString) {
		nameString = $(item).parent().next().next().text();
	}
	
	if (!nameString) {
		nameString="Unknown-" + $(item).val()
	}
	return (nameString)
}

function list_select_change(item) {
	var nameField = "name"
	if ($(item).attr("checked"))  {
		//addSelectItem($(item).val(),$(item).parent().nextAll("[class=name]").text());
		addSelectItem(item, $(item).val(),list_item_get_name(item));
	}
	else
		removeSelectItem(item, $(item).val());

}




function addSelectItem(item, id,name) {
	
	addSelectFormItem(item, id,name);
	addSelectDisplayItem(item, id,name) 
}

function removeSelectItem(item, id) {

	removeSelectFormItem(item, id) 
	removeSelectDisplayItem(item, id);
	
}
	



function addSelectFormItem(item,id,name) {
	var html="<div class='list-selected-form-item' id='list-selected-form-item-" +id +"'>";
	
	html+="<input type='hidden' class='gn_list_select_name'  name='list-selected-form-item-name[]' value='"+name +"'/>";
	html+="<input type='hidden' class='gn_list_select_id' name='list-selected-form-item-id[]' value='"+id +"'/>";
	
	html+="</div>";
	
	var container = $(item).parents("div.widget_container");
	if ($(container).find("#list-selected-form-item-"+id).length==0)
		$(container).find("#list-select-form-items").append(html);
}

function removeSelectFormItem(item, id) {
	var container = $(item).parents("div.widget_container");

	
	
	$(container).find("#list-selected-form-item-"+id).remove();
}


function list_select_item_remove (item) {
	var id = $(item).parent().attr("id").substr(19);
	
	var container = $(item).parents("div.widget_container");
	
	$(container).find("input[name='select-list-list-item-" +id+"']").attr("checked",false);
	
	
	removeSelectItem(item,id);

	return(false);



}	
	
	
function addSelectDisplayItem(item, id,name) {
	var html="<div class='list-selected-item' id='list-selected-item-" +id +"'><a href='#' onclick='return list_select_item_remove(this);' class='list-selected-item'>" + name +"</a></div>";
	
	var container = $(item).parents("div.widget_container");
	

	
	if ($(container).find("#list-selected-item-"+id).length==0) {
		$(container).find("#list-select-selected-items").append(html);
		
	}
}

function removeSelectDisplayItem(item, id) {
	var container = $(item).parents("div.widget_container");
	$(container).find("#list-selected-item-"+id).remove();
}
	
	
// *************************************************


jQuery(document).ready(function ($) {
	$("div.listwidget_postout a.listwidget_anchor").click (function () {

		 form = $(this).parents("div.listwidget_postout").find("form.gn-list-form").get(0);
		 form.setAttribute("action", $(this).attr("href"));
		
		$("form input[name='action']").remove();
		$("form input[name='page_id']").remove();
		
		
		 
		 
		 $(this).parents("div.listwidget_postout").find("form.gn-list-form").submit();
		 return (false);


		});

});


// **** 
function getUrlVars (href){
    var vars = [], hash;
    var hashes = href.slice(href.indexOf('?') + 1).split('&');
    for(var i = 0; i < hashes.length; i++)
    {
      hash = hashes[i].split('=');
      vars.push(hash[0]);
      vars[hash[0]] = hash[1];
    }
    return vars;
  }

 function getUrlVar (href, name) {
     return getUrlVars(href)[name];
  }


jQuery(document).ready(function ($) {

		$('div.resultscroller a.list-scroller-page').each(function () {
			$(this).attr("href", $(this).attr("data-href"));
		});

		$('div.resultscroller a.list-scroller-page').click(function() {

					offset= getUrlVar ($(this).attr("href"),"offset");


					// DS: Changing to class selector
					// $("#gn-list-form input[name=offset]").val(offset);

					// $("#gn-list-form").submit();

					var frm = $(this).closest(".resultscroller").prevAll(":has(.gn-list-form)").first().find(".gn-list-form").get(0);

					if(frm) {
						frm.offset.value = offset;
						frm.submit();
						return false;
					}
		});
});



	