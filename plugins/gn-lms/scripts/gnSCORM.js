var _gnlms_COURSE_ID;
var _gnlms_USER_ID;
var _gnlms_URL;


APS_API = new Object();
Session= new Object();




gnSCORMAPI = function  () {

	var lastError=0,lastErrorString="",lastDiagnostic=""
	
	var internalStorage;
	
	this.localData = function () {
		return (internalStorage);
	}
	
	function setData(data) {
		if (data) {
			internalStorage = data;
		}
	}


	function gnLoadData() {
		var success
		
		lastError=0,lastErrorString="",lastDiagnostic=""
		
		internalStorage = new Object();
		
		var data = new Object();
		
		data["cmd"] = "load";
		data["cid"] = _gnlms_COURSE_ID;
		data["uid"] = _gnlms_USER_ID
		
		var url =_gnlms_URL;
		
		var callback_s = function (responseData, textStatus, jqXHR) {
			if (responseData) {
				setData(responseData);
			}
			else {
				responseData= new Object();
				setData(responseData);

			}
			success = true		
		}
		var callback_f = function (data, textStatus, jqXHR) {
			success = false	
			alert ("f:" + textStatus +":" + jQuery.stringify(data));
			lastErrorString =textStatus
			lastError=22
		}
		
		jQuery.ajax({
		  url: url,
		  async: false,
		  dataType: 'json',
		  data: data,
		  type:"POST",
		  success: callback_s,
		  error: callback_f
		});
		
		return (success)
	}


	function gnSaveData() {
		
		var success
		
		var data = new Object();
		
		data["cmd"] = "save";
		data["cid"] = _gnlms_COURSE_ID;
		data["uid"] = _gnlms_USER_ID


		data["data"] = jQuery.stringify(internalStorage);
		
		var url =_gnlms_URL;
		
		var callback_s = function (data, textStatus, jqXHR) {
			setData(data);
			
			
			success = true		
		}
		var callback_f = function (data, textStatus, jqXHR) {
			success = false	
			alert ("f:" + textStatus +":" + jQuery.stringify(data));

		}
		
		jQuery.ajax({
		  url: url,
		  async: false,
		  dataType: 'json',
		  data: data,
		  type:"POST",
		  success: callback_s,
		  error: callback_f
		});
		
		return (success)	
	}


	function gnDiscardData() {
		internalStorage = null;
		return (true);
	
	
	}
	
	function gnSetValue (name,value) {
		var nameArray = name.split(".");
		var lastname = nameArray.pop()
		var propertyLocation = internalStorage
		
		for (var i=0; i<nameArray.length; i++) {
			propertyName = nameArray[i];
			
			if (propertyLocation && (typeof(propertyLocation)=="object")) {
				nextLocation =propertyLocation[propertyName]?propertyLocation[propertyName]:(new Object());
			
				propertyLocation[propertyName] = nextLocation;
				propertyLocation =nextLocation;
			}
		}
		
		if (propertyLocation && (typeof(propertyLocation)=="object")) {
			propertyLocation[lastname] =value;
			return (true);
		}
		else {
			return (false);
		}
		
	}
	
	// CTW 8/12/2013 patch for IE8
	
	Object.keys = Object.keys || function(o) {  
	    var result = [];  
	    for(var name in o) {  
	        if (o.hasOwnProperty(name))  
	          result.push(name);  
	    }  
	    return result;  
	};
	
	// End CTW 8/12/2013 patch
	

	function gnPropertyCount(propertyLocation) {
		if (propertyLocation && (typeof(propertyLocation)=="object")) {
			return (Object.keys(propertyLocation).length );
					
		}
		else {
			return(0);
		}
	}
	
	function gnGetValue (name) {
		var nameArray = name.split(".");
		var lastname = nameArray.pop()
		var propertyLocation = internalStorage;
		value="";
		
		
		for (var i=0; i<nameArray.length; i++) {
			propertyName = nameArray[i];
			
			if (propertyLocation && (typeof(propertyLocation)=="object")) {
				propertyLocation =propertyLocation[propertyName]
			}

		}
		if (lastname=="_count") {
			value =gnPropertyCount(propertyLocation);
		

		
		}
		else if (propertyLocation && (typeof(propertyLocation)=="object")) {
			// DS: propertyLocation[lastname] could be zero
			// value=propertyLocation[lastname] ? propertyLocation[lastname]: "" ;
			
			value= (lastname in propertyLocation) ? propertyLocation[lastname]: "" ;
			
		}
		
		if(window.console) window.console.log ("gnGetValue " + name +": " + value);
		return(value);

	}





	//************************************
	// Initialize
	this.Initialize = function (s) {
		return (gnLoadData());
	
	}

	//************************************
	// Terminate
	this.Terminate = function (s) {
		return (gnDiscardData());
	
	}
	
	//************************************
	// GetValue
	
	this.GetValue = function (parameter) {
		return (gnGetValue(parameter));
	
	}
	
	//************************************
	//SetValue
	
	this.SetValue = function (name, value) {
		return (gnSetValue (name,value));
	
	}
	
	this.Commit = function (s) {
	
		return (gnSaveData());
	
	
	}
	
	var lastError=0,lastErrorString="",lastDiagnostic=""
	
	this.GetLastError = function(){ return (lastError);}
	this.GetErrorString = function (parameter) { return (lastErrorString)}
	this.GetDiagnostic = function (parameter) {return (lastDiagnostic);}

	this.Terminate = function (parameter) { 
		
		jQuery(window).trigger('gn_SCORM_terminate',{});
		if (GLOBAL_COURSE_WINDOW) {
			GLOBAL_COURSE_WINDOW.close();
			GLOBAL_COURSE_WINDOW = null;
		}
		
		GLOBAL_ALLOW_CLOSE=true;
		jQuery("#gnlms-course-monitor").dialog("close");
	}

	return (this)
}


API_1484_11 = new gnSCORMAPI();
API = API_1484_11;

API.LMSInitialize=API.Initialize
API.LMSGetValue=API.GetValue
API.LMSSetValue=API.SetValue
API.LMSGetLastError=API.GetLastError
API.LMSCommit=API.Commit

API.LMSGetErrorString =API.GetErrorString 
API.LMSGetDiagnostic =API.GetDiagnostic 



window.API=API;
window.API_1484_11 =API;

//API_1484_11.Terminate = function (s) {}
API_1484_11.GetErrorString = function (parameter) {}
API_1484_11.GetDiagnostic = function (parameter) {}


APS_API = new Object();


var GLOBAL_COURSE_WINDOW
var GLOBAL_ALLOW_CLOSE = false;

function launchCourse (url) {
	
	window.gnSession= new Object(); // hack

	GLOBAL_COURSE_WINDOW = window.open(url,"Course","fullscreen=no,status=no,toolbar=no,menubar=no,location=no");

	if (GLOBAL_COURSE_WINDOW) {
		jQuery(".gnScormFailedLaunch").hide();
		jQuery(".gnScormSuccessfulLaunch").show();
	
		GLOBAL_COURSE_WINDOW.moveTo(0, 0);
		GLOBAL_COURSE_WINDOW.resizeTo(screen.availWidth, screen.availHeight);
		GLOBAL_COURSE_WINDOW.focus();
	
	
	} else {
		jQuery(".gnScormSuccessfulLaunch").hide();
		jQuery(".gnScormFailedLaunch").show();
	
	}
	

}

function gnLMSCourseMonitorClose () {

	if (!GLOBAL_ALLOW_CLOSE && GLOBAL_COURSE_WINDOW && !GLOBAL_COURSE_WINDOW.closed && GLOBAL_COURSE_WINDOW.$ && GLOBAL_COURSE_WINDOW.$("#menu-logout")) {
		GLOBAL_COURSE_WINDOW.focus();
		GLOBAL_COURSE_WINDOW.moveTo(0, 0);
		GLOBAL_COURSE_WINDOW.resizeTo(screen.availWidth, screen.availHeight);

		GLOBAL_COURSE_WINDOW.$("#menu-logout").click();
		return (false);
	}
	else {
		GLOBAL_ALLOW_CLOSE = false;
		return(true);
		
	}

}


/*
API_1484_11.Initialize = function (s) {}
API_1484_11.Terminate = function (s) {}
API_1484_11.GetValue = function (parameter) {}
API_1484_11.SetValue = function (name, value) {}
API_1484_11.Commit = function (s) {}
API_1484_11.GetLastError = function(){}
API_1484_11.GetErrorString = function (parameter) {}
API_1484_11.GetDiagnostic = function (parameter) {}

*/



/*

cmi._version (characterstring, RO) Represents the version of the data model
cmi.comments_from_learner._children (comment,location,timestamp, RO) Listing of supported data model elements
cmi.comments_from_learner._count (non-negative integer, RO) Current number of learner comments
cmi.comments_from_learner.n.comment (localized_string_type (SPM: 4000), RW) Textual input
cmi.comments_from_learner.n.location (characterstring (SPM: 250), RW) Point in the SCO to which the comment applies
cmi.comments_from_learner.n.timestamp (time (second,10,0), RW) Point in time at which the comment was created or most recently changed
cmi.comments_from_lms._children (comment,location,timestamp, RO) Listing of supported data model elements
cmi.comments_from_lms._count (non-negative integer, RO) Current number of comments from the LMS
cmi.comments_from_lms.n.comment (localized_string_type (SPM: 4000), RO) Comments or annotations associated with a SCO
cmi.comments_from_lms.n.location (characterstring (SPM: 250), RO) Point in the SCO to which the comment applies
cmi.comments_from_lms.n.timestamp (time(second,10,0), RO) Point in time at which the comment was created or most recently changed
cmi.completion_status ("completed", "incomplete", "not attempted", "unknown", RW) Indicates whether the learner has completed the SCO
cmi.completion_threshold (real(10,7) range (0..1), RO) Used to determine whether the SCO should be considered complete
cmi.credit ("credit", "no-credit", RO) Indicates whether the learner will be credited for performance in the SCO
cmi.entry (ab_initio, resume, "", RO) Asserts whether the learner has previously accessed the SCO
cmi.exit (timeout, suspend, logout, normal, "", WO) Indicates how or why the learner left the SCO
cmi.interactions._children (id,type,objectives,timestamp,correct_responses,weighting,learner_response,result,latency,description, RO) Listing of supported data model elements
cmi.interactions._count (non-negative integer, RO) Current number of interactions being stored by the LMS
cmi.interactions.n.id (long_identifier_type (SPM: 4000), RW) Unique label for the interaction
cmi.interactions.n.type ("true-false", "choice", "fill-in", "long-fill-in", "matching", "performance", "sequencing", "likert", "numeric" or "other", RW) Which type of interaction is recorded
cmi.interactions.n.objectives._count (non-negative integer, RO) Current number of objectives (i.e., objective identifiers) being stored by the LMS for this interaction
cmi.interactions.n.objectives.n.id (long_identifier_type (SPM: 4000), RW) Label for objectives associated with the interaction
cmi.interactions.n.timestamp (time(second,10,0), RW) Point in time at which the interaction was first made available to the learner for learner interaction and response
cmi.interactions.n.correct_responses._count (non-negative integer, RO) Current number of correct responses being stored by the LMS for this interaction
cmi.interactions.n.correct_responses.n.pattern (format depends on interaction type, RW) One correct response pattern for the interaction
cmi.interactions.n.weighting (real (10,7), RW) Weight given to the interaction relative to other interactions
cmi.interactions.n.learner_response (format depends on interaction type, RW) Data generated when a learner responds to an interaction
cmi.interactions.n.result ("correct", "incorrect", "unanticipated", "neutral") or a real number with values that is accurate to seven significant decimal figures real. , RW) Judgment of the correctness of the learner response
cmi.interactions.n.latency (timeinterval (second,10,2), RW) Time elapsed between the time the interaction was made available to the learner for response and the time of the first response
cmi.interactions.n.description (localized_string_type (SPM: 250), RW) Brief informative description of the interaction
cmi.launch_data (characterstring (SPM: 4000), RO) Data provided to a SCO after launch, initialized from the dataFromLMS manifest element
cmi.learner_id (long_identifier_type (SPM: 4000), RO) Identifies the learner on behalf of whom the SCO was launched
cmi.learner_name (localized_string_type (SPM: 250), RO) Name provided for the learner by the LMS
cmi.learner_preference._children (audio_level,language,delivery_speed,audio_captioning, RO) Listing of supported data model elements
cmi.learner_preference.audio_level (real(10,7), range (0..*), RW) Specifies an intended change in perceived audio level
cmi.learner_preference.language (language_type (SPM 250), RW) The learner’s preferred language for SCOs with multilingual capability
cmi.learner_preference.delivery_speed (real(10,7), range (0..*), RW) The learner’s preferred relative speed of content delivery
cmi.learner_preference.audio_captioning ("-1?, "0?, "1?, RW) Specifies whether captioning text corresponding to audio is displayed
cmi.location (characterstring (SPM: 1000), RW) The learner’s current location in the SCO
cmi.max_time_allowed (timeinterval (second,10,2), RO) Amount of accumulated time the learner is allowed to use a SCO
cmi.mode ("browse", "normal", "review", RO) Identifies one of three possible modes in which the SCO may be presented to the learner
cmi.objectives._children (id,score,success_status,completion_status,description, RO) Listing of supported data model elements
cmi.objectives._count (non-negative integer, RO) Current number of objectives being stored by the LMS
cmi.objectives.n.id (long_identifier_type (SPM: 4000), RW) Unique label for the objective
cmi.objectives.n.score._children (scaled,raw,min,max, RO) Listing of supported data model elements
cmi.objectives.n.score.scaled (real (10,7) range (-1..1), RW) Number that reflects the performance of the learner for the objective
cmi.objectives.n.score.raw (real (10,7), RW) Number that reflects the performance of the learner, for the objective, relative to the range bounded by the values of min and max
cmi.objectives.n.score.min (real (10,7), RW) Minimum value, for the objective, in the range for the raw score
cmi.objectives.n.score.max (real (10,7), RW) Maximum value, for the objective, in the range for the raw score
cmi.objectives.n.success_status ("passed", "failed", "unknown", RW) Indicates whether the learner has mastered the objective
cmi.objectives.n.completion_status ("completed", "incomplete", "not attempted", "unknown", RW) Indicates whether the learner has completed the associated objective
cmi.objectives.n.progress_measure (real (10,7) range (0..1), RW) Measure of the progress the learner has made toward completing the objective
cmi.objectives.n.description (localized_string_type (SPM: 250), RW) Provides a brief informative description of the objective
cmi.progress_measure (real (10,7) range (0..1), RW) Measure of the progress the learner has made toward completing the SCO
cmi.scaled_passing_score (real(10,7) range (-1 .. 1), RO) Scaled passing score required to master the SCO
cmi.score._children (scaled,raw,min,max, RO) Listing of supported data model elements
cmi.score.scaled (real (10,7) range (-1..1), RW) Number that reflects the performance of the learner
cmi.score.raw (real (10,7), RW) Number that reflects the performance of the learner relative to the range bounded by the values of min and max
cmi.score.min (real (10,7), RW) Minimum value in the range for the raw score
cmi.score.max (real (10,7), RW) Maximum value in the range for the raw score
cmi.session_time (timeinterval (second,10,2), WO) Amount of time that the learner has spent in the current learner session for this SCO
cmi.success_status ("passed", "failed", "unknown", RW) Indicates whether the learner has mastered the SCO
cmi.suspend_data (characterstring (SPM: 64000), RW) Provides space to store and retrieve data between learner sessions
cmi.time_limit_action ("exit,message", "continue,message", "exit,no message", "continue,no message", RO) Indicates what the SCO should do when cmi.max_time_allowed is exceeded
cmi.total_time (timeinterval (second,10,2), RO) Sum of all of the learner’s session times accumulated in the current learner attempt
adl.nav.request (request(continue, previous, choice, jump, exit, exitAll, abandon, abandonAll, suspendAll _none_), RW) Navigation request to be processed immediately following Terminate()
adl.nav.request_valid.continue (state (true, false, unknown), RO) Used by a SCO to determine if a Continue navigation request will succeed.
adl.nav.request_valid.previous (state (true, false, unknown), RO) Used by a SCO to determine if a Previous navigation request will succeed.
adl.nav.request_valid.choice.{target=} (state (true, false, unknown), RO) Used by a SCO to determine if a Choice navigation request for the target activity will succeed.
adl.nav.request_valid.jump.{target=} (state (true, false, unknown), RO) Used by a SCO to determine if a Jump navigation request for the target activity will succeed.
*/