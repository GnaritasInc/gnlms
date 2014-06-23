
jQuery(document).ready(function ($){
	
	var datepickerOptions = {
		autoSize:true,
		dateFormat:"yy-mm-dd"
	};
	
	function getParams () {
		var params = {};
		var arr = location.search ? location.search.split("&") : "";
		for(var i=0; i<arr.length; ++i) {
			var parts = arr[i].split("=");

			params[decodeURIComponent(parts[0])] = decodeURIComponent(parts[1]);
		}

		return params;
	}
	
	function refreshTasks (params) {
		
		
		/*
		taskType = $("form.gn-taskfilter").find('input[name=tasklist-type]').val();
		
		
		params = params || "action=gntm-gettasks";
		params+="&tasktype="+taskType;
		
		*/
		
	
		params = $("form.gn-taskfilter").serialize(); // "#gntm-current-tasks form")
		
		loadPageComponent (
			"#gntm-current-tasks", 
			params, 
			setHandlers,
			handleTaskLoadError
		);		
	}
	
	
	function setScrollHandler() {
	
		$('div.taskscroller a.list-scroller-page').click(function() {
			offset= getUrlVar ($(this).attr("href"),"offset");

			$("form.gn-taskfilter input[name=offset]").val(offset);

			refreshTasks();

			return false;
		});
	}
	
	function loadInputForm (taskID) {
		var idRef = taskID ? "&taskid="+encodeURIComponent(taskID) : "";
		var dialogTitle = (idRef ? "Edit" : "Add") + " Task";
		$("#gntm-edit-form").dialog({title:dialogTitle});
		loadPageComponent(
			"#gntm-edit-form", 
			"action=gntm-gettaskinputform"+idRef, 
			function (responseText, status) {
				if(status != "error"){
					$("#gntm-edit-form .date-picker").datepicker(datepickerOptions);
					setDefaultDates();
					$("#gntm-edit-form").ajaxDialogForm("open");
				}
			}
		);		
	}
	
	
	function loadInputFormRO (taskID) {
			var idRef = taskID ? "&taskid="+encodeURIComponent(taskID) : "";
			var dialogTitle = (idRef ? "View" : "") + " Task";
			$("#gntm-edit-form").dialog({title:dialogTitle, buttons: [
					    {
						text: "Close",
						click: function() { $(this).dialog("close"); }
					    }
					]
					});
			loadPageComponent(
				"#gntm-edit-form", 
				"action=gntm-gettaskinputform"+idRef, 
				function (responseText, status) {
					if(status != "error"){
						$("#gntm-edit-form .date-picker").datepicker(datepickerOptions);
						$("#gntm-edit-form").ajaxDialogForm("open");
					}
				}
			);		
	}
	
	function setDefaultDates () {
		$("#gntm-edit-form .date-picker").each(function () {
			if(!$(this).val()) {
				$(this).datepicker("setDate", new Date());
			}
		});

	}
	
	function deleteTask (taskID) {
		if(confirm("Delete this task?")) {
			doInlinePost({action:"gntm-deletetask", taskID:taskID});
		}
	}
	
	function setCheckboxClickHandler () {
		$("#gntm-current-tasks input[type='checkbox']").click(function () {
			setTaskCompletion (this.value, this.checked);
		});
	}
	
	function setTaskCompletion (taskID, state) {
		doInlinePost({
			action:"gntm-settaskcompletion",
			taskID:taskID,
			state:(state ? 1 : 0)
		});
	}
	
	function doInlinePost (data) {
		setAjaxErrorHandler(handlePostError);
		$.post(
			gn_TaskManager.ajaxURL, $.extend({nonce:gn_TaskManager.nonce}, data),
			inlinePostComplete,
			"json"
		);		
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
	
	function setWaitingState (isWaiting) {
		// DS: Can do something fancier, but this works.
		// $("body").css("cursor", (isWaiting ? "wait":"auto"));
	}
	
	function inlinePostComplete (response) {
		if(response.status != "OK") {
			alert("Error: "+response.message);
		}
		updateNonce(response);
		refreshTasks();			
	}
	
	function handleTaskLoadError (e, xhr) {
		alert("Unable to load tasks: "+xhr.status+" "+xhr.statusText);
	}
	
	function setAjaxErrorHandler (func) {		
		$(document).unbind("ajaxError");
		$(document).ajaxError(func);
	}
		
	function validateInput (frm) {
		return true;
	}
	
	
	function handlePostError (e, xhr) {
		alert("Server error: "+xhr.status+" "+xhr.statusText);
	}
	
	function updateNonce (data) {
		gn_TaskManager.nonce = data.nonce || gn_TaskManager.nonce;
		$("#gntm-input-form input[name='nonce']").val(gn_TaskManager.nonce);
	}
	
	function showInputError (dlg, errMsg) {
		var msg = $(dlg).find(".msg");
		if(msg.length) {
			msg.addClass("ui-state-error");
			msg.text(errMsg);
		}
		else {
			alert(errMsg);
		}
	}
	
	function clearInputError (dlg) {
		var msg = $(dlg).find(".msg");
		if(msg.length) {
			msg.removeClass("ui-state-error");
			msg.text("Enter Task Information.");
		}
	}
	
	function setHandlers () {
		
		setCheckboxClickHandler();
		$("a.task-edit").click(function () {
		
			
			loadInputForm(this.id.substr(1));
			return false;
			
		});

		$("a.task-view").click(function () {
		
			
			loadInputFormRO(this.id.substr(1));
			return false;
			
		});
		
		$("a.task-delete").click(function () {
			deleteTask(this.id.substr(1));
			return false;
		});
		
		setScrollHandler();
	}
	
	

	
	$("#gntm-edit-form").ajaxDialogForm({
		zIndex:1,
		close: function () {
			clearInputError(this);
			$(".date-picker").datepicker("hide");
		},
		onAjaxError:handlePostError,
		onSuccess: function (response) {
			if(response.status=="Confirm") {
				alert(response.message);
				$(this).ajaxDialogForm("close");
				refreshTasks();
			}
			else if(response.status != "OK") {
				showInputError(this, response.message);
			}
			else {
				$(this).ajaxDialogForm("close");
				refreshTasks();
			}

			updateNonce(response);
		},
		onSubmit: function () {
			if($(this).hasClass("gn-support-request")) {
				return doSupportRequestAction(this);
			}
			else {
				if(!validateInput(this)){
					return false;
				}
				
				var metaKey = $("form.gn-taskfilter input[name='tasktype']").val();
				var metaValue = $("form.gn-taskfilter input[name='id']").val();
				
				if(metaKey && metaValue) {
					this.meta_key.value = metaKey+"_id";
					this.meta_value.value = metaValue;
				}
				return true;
			}
		},
		action:gn_TaskManager.ajaxURL
	});
	
	function checkAdminAction (frm) {
		var approve = $("#admin-action-approve")[0];
		var delegate = $("#admin-action-delegate")[0];
		var deny = $("#admin-action-deny")[0];
		var denyReason = $("#deny-reason");
		
		if(approve && approve.checked) {
			location.href = frm.url.value + "?task_id="+frm.id.value;
			return false;
		}
		
		else if (delegate && delegate.checked) {
			var val = $(frm.admin_id).val();
			if(!val) {
				alert("Please select an administrator.");
			}
			else {
				
				return true;
			}			
		}
		
		else if (deny && deny.checked) {
			if(denyReason && !$.trim(denyReason.val())) {
				alert("Please indicate the reason for denying  the request.");
				return false;
			}
			else return confirm("Deny this request?");
		}
		
		else if (!$("input[name='admin-action']:checked").length) {
			alert("Please choose an action.");
			return false;
		}
		else return true;
	}
	
	function doSupportRequestAction (frm) {
		
		var setStatus = $("#admin-action-status")[0];
		
		if(setStatus && setStatus.checked) {
			if($("#status").val() != "Pending" && !$.trim($("#resolution").val())) {
				alert("Please indicate what action was taken.");
				return false;
			}
			else return true;
		}
		else return checkAdminAction(frm);
		
	}
	
	

	
	$(".date-picker").datepicker(datepickerOptions);
	

	
	
	$(".gn-taskwidget a#add-new").click(function () {
		$("#gntm-input-form form").get(0).reset();
		$("#gntm-input-form").dialog("open");
		return false;
	});
	

	
	$("form.gn-taskfilter input, form.gn-taskfilter select").change(function () {	
		if(this.form.offset) this.form.offset.value = 0;
		refreshTasks($(this.form).serialize());
		
	});
	
	
	setHandlers();		

});

