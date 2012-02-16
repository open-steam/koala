// functions used in Index Command
/**
 * function for deleting a given workplan
 * @param workplanid
 * @param name
 */
function deleteWorkplan(workplanid, name) {
	var retVal = confirm("Projektplan \"" +name+ "\" wirklich löschen?");
	if (retVal != null && retVal == true) {
		params = {};
		params.deleteid = workplanid;
		params.name = name;
		sendRequest("Index", params , "everything", "updater");
	}
}

// functions used in Create Command
/**
 * function for checking correct input in the create workplan dialog
 */
function checkCreateForm() {
	if (checkString(document.getElementsByName("values[name]")[0].value)) {
		if (checkDate(document.getElementsByName("values[start]")[0].value, 1)) {
			if (document.getElementsByName("values[end]")[0].value.length > 0) {
				return checkDate(document.getElementsByName("values[end]")[0].value, 1);
			} else return true;
		} else return false;
	} else return false;
}

// functions used in Overview and UpdateOverview Commands
/**
 * function sends ajax request to display editable overview of current workplan
 * @param workplanid
 */
function changeOverview(workplanid) {
	hideConfirmation();
	params = {};
	params.id = workplanid;
	params.change = 1;
	sendRequest("UpdateOverview", params , "overview", "updater");
}

/**
 * function sends ajax request to return to normal overview of current workplan
 * @param workplanid
 */
function cancelChangeOverview(workplanid) {
	params = {};
	params.id = workplanid;
	params.change = 0;
	sendRequest("UpdateOverview", params , "overview", "updater");
}

/**
 * function checks input in change overview view and then sends ajax request to save changes
 * @param workplanid
 * @returns {Boolean}
 */
function saveChangedOverview(workplanid) {
	if (!checkString(document.getElementById("name").value)) return false;
	if (!checkDate(document.getElementById("start").value, 1)) return false;
	if (document.getElementById("end").value.length > 0 && !checkDate(document.getElementById("end").value, 1)) return false;
	params = {};
	params.id = workplanid;
	params.change = 2;
	params.name = document.getElementById("name").value;
	params.start = document.getElementById("start").value;
	params.end = document.getElementById("end").value;
	params.description = document.getElementById("description").value;
	sendRequest("UpdateOverview", params , "overview", "updater");
	
	var name = document.getElementById("headline").innerHTML;
	name = name.substring(0,name.indexOf("</a>"))+"</a> / "+params.name+"</h1>";
	document.getElementById("confirmation").innerHTML = "Änderungen erfolgreich gespeichert.";
	window.setTimeout('document.getElementById("headline").innerHTML = '+name+'; appearConfirmation();',1000);
}

// functions used in ListView, UpdateListView and UpdateForms Commands
/**
 * function checks create milestone dialog for correct input and then sends ajax request to create new milestone
 */
function newMilestone() {
	if (!checkString(document.getElementById("milestonename").value)) return false;
	if (!checkDate(document.getElementById("milestonedate").value, 1)) return false;
	params = {};
	params.mot = 0;
	params.update = 0;
	params.snapshot = 0;
	params.id = document.getElementById("workplanid").value;
	params.name = document.getElementById("milestonename").value;
	params.start = document.getElementById("milestonedate").value;
	params.end = document.getElementById("milestonedate").value;
	params.duration = document.getElementById("milestoneduration").value;
	params.depends = document.getElementById("milestonedepends").value;
	
	length = document.getElementById("milestoneusers").options.length;
	users = "";
	for (i=0; i < length; i++) {
		if (document.getElementById("milestoneusers").options.item(i).selected == true) {
			users = users + document.getElementById("milestoneusers").options.item(i).value + ",";
		}
	}
	users = users.substring(0,users.length-1);
	params.users = users;
	
	Effect.SlideUp("slidedown_milestone");
	sendRequest("UpdateListView", params , "list", "updater");
	
	document.getElementById("confirmation").innerHTML = "Meilenstein "+params.name+" erfolgreich erstellt.";
	window.setTimeout("appearConfirmation();",1000);
}

/**
 * function checks create task dialog for correct input and then sends ajax request to create new task and update the update/create forms
 * @returns {Boolean}
 */
function newTask() {
	if (!checkString(document.getElementById("taskname").value)) return false;
	if (!checkDate(document.getElementById("taskstart").value, 1)) return false;
	if (!checkDate(document.getElementById("taskend").value, 1)) return false;
	if (!checkTaskStartEnd(document.getElementById("taskstart").value, document.getElementById("taskend").value)) {
		alert("Enddatum früher als Anfangsdatum.");
		return false;
	}
	
	params = {};
	params.mot = 1;
	params.snapshot = 0;
	params.update = 0;
	params.id = document.getElementById("workplanid").value;
	params.name = document.getElementById("taskname").value;
	params.start = document.getElementById("taskstart").value;
	params.end = document.getElementById("taskend").value;
	params.duration = document.getElementById("taskduration").value;
	params.depends = document.getElementById("taskdepends").value;
	
	length = document.getElementById("taskusers").options.length;
	users = "";
	for (i=0; i < length; i++) {
		if (document.getElementById("taskusers").options.item(i).selected == true) {
			users = users + document.getElementById("taskusers").options.item(i).value + ";";
		}
	}
	users = users.substring(0,users.length-1);
	params.users = users;
	
	Effect.SlideUp("slidedown_task");
	sendRequest("UpdateListView", params , "list", "updater");
	window.setTimeout('sendRequest("UpdateForms", {command:"UpdateForms",id:"'+document.getElementById("workplanid").value+'"}, "createforms", "updater");',1000);
	
	document.getElementById("confirmation").innerHTML = "Vorgang "+params.name+" erfolgreich erstellt.";
	window.setTimeout('appearConfirmation();',1000);
}

/**
 * function asks user if he really wants to delete the selected milestone/task and does as the user selects
 * @param name
 * @param workplanid
 * @param oid
 * @param mot
 * @returns {Boolean}
 */
function deleteTask(name, workplanid, oid, mot) {
	if (mot == 1) {
		var retVal = confirm("Meilenstein \"" +name+ "\" wirklich löschen?");
	} else {
		var retVal = confirm("Vorgang \"" +name+ "\" wirklich löschen?");
	}
	if (retVal == true){
		params = {};
		params.mot = mot;
		params.update = 2;
		params.snapshot = 0;
		params.id = workplanid;
		params.elementid = oid;
		sendRequest("UpdateListView", params , "list", "updater");
		
		if (mot == 1) {
			document.getElementById("confirmation").innerHTML = "Meilenstein "+name+" erfolgreich gelöscht.";
		} else {
			document.getElementById("confirmation").innerHTML = "Vorgang "+name+" erfolgreich gelöscht.";
		}
		window.setTimeout('appearConfirmation();',1000);
	} else return false;
}

/**
 * function to clear the view and call the function for displaying the update dialog
 * @param mot
 * @param oid
 * @param name
 * @param start
 * @param end
 * @param duration
 * @param depends
 * @param users
 */
function updateTask(mot, oid, name, start, end, duration, depends, users) {
	hideConfirmation();
	if (document.getElementById("slidedown_milestone").style.display != "none") {
		Effect.SlideUp("slidedown_milestone");
	}
	if (document.getElementById("slidedown_task").style.display != "none") {
		Effect.SlideUp("slidedown_task");
	}
	if (document.getElementById("updateMilestoneTable").style.display != "none") {
		Effect.Fade("updateMilestoneTable");
	}
	if (document.getElementById("updateTaskTable").style.display != "none") {
		Effect.Fade("updateTaskTable");
	}
	if (mot == 1) {
		window.setTimeout('appearUpdateMilestone("'+oid+'", "'+name+'", "'+start+'", "'+end+'", "'+duration+'", "'+depends+'", "'+users+'");',1000);
	} else {
		window.setTimeout('appearUpdateTask("'+oid+'", "'+name+'", "'+start+'", "'+end+'", "'+duration+'", "'+depends+'", "'+users+'");',1000);
	}
}

/**
 * function to let the update dialog appear if the user clicked the update icon of a certain milestone
 * @param oid
 * @param name
 * @param start
 * @param end
 * @param duration
 * @param depends
 * @param users
 */
function appearUpdateMilestone(oid, name, start, end, duration, depends, users) {
	document.getElementById("elementname").value = name.substring(0,name.lastIndexOf("(")-1);
	document.getElementById("elementdate").value = start;
	document.getElementById("milestoneid").value = oid;
	if (duration != -1) {
		document.getElementById("elementduration").value = duration;
	} else document.getElementById("elementduration").value = '';
	if (depends != -1) {
		for (i=0; i < document.getElementById("elementdepends").options.length; i++) {
			if (document.getElementById("elementdepends").options[i].value == depends) {
				document.getElementById("elementdepends").options[i].selected = true;
				break;
			}
		}
	}
	users = JSON.parse(users);
	for (i=0; i < users.length; i++) {
		for (j=0; j < document.getElementById("elementusers").options.length; j++) {
			if (document.getElementById("elementusers").options[j].value == users[i]) {
				document.getElementById("elementusers").options[j].selected = true;
			}
		}
	}
	Effect.Appear("updateMilestoneTable");
}

/**
 * function to let the update dialog appear if the user clicked the update icon of a certain task
 * @param oid
 * @param name
 * @param start
 * @param end
 * @param duration
 * @param depends
 * @param users
 */
function appearUpdateTask(oid, name, start, end, duration, depends, users) {
	document.getElementById("elementtaskname").value = name.substring(0,name.lastIndexOf("(")-1);
	document.getElementById("elementtaskstart").value = start;
	document.getElementById("elementtaskend").value = end;
	document.getElementById("taskid").value = oid;
	if (duration != -1) {
		document.getElementById("elementtaskduration").value = duration;
	} else document.getElementById("elementtaskduration").value = '';	
	if (depends != -1) {
		for (i=0; i < document.getElementById("elementtaskdepends").options.length; i++) {
			if (document.getElementById("elementtaskdepends").options[i].value == depends) {
				document.getElementById("elementtaskdepends").options[i].selected = true;
				break;
			}
		}
	}
	users = JSON.parse(users);
	for (i=0; i < users.length; i++) {
		for (j=0; j < document.getElementById("elementtaskusers").options.length; j++) {
			if (document.getElementById("elementtaskusers").options[j].value == users[i]) {
				document.getElementById("elementtaskusers").options[j].selected = true;
			}
		}
	}
	Effect.Appear("updateTaskTable");
}

/**
 * function to save the changes if user changed the information of a task
 * @param workplanid
 * @returns {Boolean}
 */
function saveUpdateTask(workplanid) {
	if (!checkDate(document.getElementById("elementtaskstart").value, 1)) return false;
	if (!checkDate(document.getElementById("elementtaskend").value, 1)) return false;
	if (!checkString(document.getElementById("elementtaskname").value)) return false;
	if (!checkTaskStartEnd(document.getElementById("elementtaskstart").value, document.getElementById("elementtaskend").value)) {
		alert("Enddatum früher als Anfangsdatum.");
		return false;
	}
	params = {};
	params.id = workplanid;
	params.update = 1;
	params.snapshot = 0;
	params.changeID = document.getElementById("taskid").value;
	params.name = document.getElementById("elementtaskname").value;
	params.start = document.getElementById("elementtaskstart").value;
	params.end = document.getElementById("elementtaskend").value;
	params.duration = document.getElementById("elementtaskduration").value;
	params.depends = document.getElementById("elementtaskdepends").value;
	
	length = document.getElementById("elementtaskusers").options.length;
	users = "";
	for (i=0; i < length; i++) {
		if (document.getElementById("elementtaskusers").options.item(i).selected == true) {
			users = users + document.getElementById("elementtaskusers").options.item(i).value + ",";
		}
	}
	users = users.substring(0,users.length-1);
	params.users = users;
	Effect.Fade("updateTaskTable");
	
	sendRequest("UpdateListView", params , "list", "updater");
	window.setTimeout('sendRequest("UpdateForms", {command:"UpdateForms",id:"'+document.getElementById("workplanid").value+'"}, "createforms", "updater");',1000);
	
	document.getElementById("confirmation").innerHTML = "Änderungen erfolgreich gespeichert.";
	window.setTimeout('appearConfirmation();',1000);
}

/**
 * function to save the changes if user changed the information of a milestone
 * @param workplanid
 * @returns {Boolean}
 */
function saveUpdateMilestone(workplanid) {
	if (!checkDate(document.getElementById("elementdate").value, 1)) return false;
	if (!checkString(document.getElementById("elementname").value)) return false;
	params = {};
	params.id = workplanid;
	params.update = 1;
	params.snapshot = 0;
	params.changeID = document.getElementById("milestoneid").value;
	params.name = document.getElementById("elementname").value;
	params.start = document.getElementById("elementdate").value;
	params.end = document.getElementById("elementdate").value;
	params.duration = document.getElementById("elementduration").value;
	params.depends = document.getElementById("elementdepends").value;
	
	length = document.getElementById("elementusers").options.length;
	users = "";
	for (i=0; i < length; i++) {
		if (document.getElementById("elementusers").options.item(i).selected == true) {
			users = users + document.getElementById("elementusers").options.item(i).value + ",";
		}
	}
	users = users.substring(0,users.length-1);
	params.users = users;
	Effect.Fade("updateMilestoneTable");
	
	sendRequest("UpdateListView", params , "list", "updater");
	
	document.getElementById("confirmation").innerHTML = "Änderungen erfolgreich gespeichert.";
	window.setTimeout("appearConfirmation();",1000);
}

// functions used in GanttView Command
/**
 * function to check user input in the create milestone dialog
 */
function checkMilestoneGanttView() {
	if (checkString(document.getElementsByName("milestonename")[0].value)) {
		return checkDate(document.getElementsByName("milestonedate")[0].value, 1);
	} else return false;
}

/**
 * function to check user input in the create task dialog
 */
function checkTaskGanttView() {
	if (checkString(document.getElementsByName("taskname")[0].value)) {
		if (checkDate(document.getElementsByName("taskstart")[0].value, 1)) {
			if (checkDate(document.getElementsByName("taskend")[0].value, 1)) {
				if (!checkTaskStartEnd(document.getElementsByName("taskstart")[0].value, document.getElementsByName("taskend")[0].value)) {
					alert("Enddatum früher als Anfangsdatum.");
					return false;
				} else return true;
			} else return false;
		} else return false;
	} else return false;
}

// functions used in Users and UpdateUsers Commands
/**
 * function to change from normal users view to editable users view
 */
function changeUsers(workplanid) {
	params = {};
	params.id = workplanid;
	params.change = 1;
	params.newChanges = 0;
	sendRequest("UpdateUsers", params , "users", "updater");
	hideConfirmation();
}

/**
 * function to change from editable users view to normal users view
 */
function cancelChangeUsers(workplanid) {
	params = {};
	params.id = workplanid;
	params.change = 0;
	params.newChanges = 0;
	sendRequest("UpdateUsers", params , "users", "updater");
}

/**
 * function to save the changed user information
 * @param workplanid
 * @returns {Boolean}
 */
function saveChangedUsers(workplanid) {
	params = {};
	params.id = workplanid;
	params.change = 0;
	params.newChanges = 1;
	params.leaderRes = document.getElementById("leaderRes").value;
	if (!checkInteger(params.leaderRes, 1)) return false;
	
	elementsRoles = document.getElementsByName("role");
	roles = "[";
	for (i=0; i < elementsRoles.length; i++) {
		roles = roles + elementsRoles[i].value + ",";
	}
	roles = roles.substring(0,roles.length-1) + "]";
	params.roles = roles;
	
	elementsRes = document.getElementsByName("ressource");
	ressources = "[";
	for (i=0; i < elementsRes.length; i++) {
		if (!checkInteger(elementsRes[i].value, 1)) return false;
		ressources = ressources + elementsRes[i].value + ",";
	}
	ressources = ressources.substring(0,ressources.length-1) + "]";
	params.ressources = ressources;
	
	elementsOIDs = document.getElementsByName("useroid");
	oids = "[";
	for (i=0; i < elementsOIDs.length; i++) {
		oids = oids + elementsOIDs[i].value + ",";
	}
	oids = oids.substring(0,oids.length-1) + "]";
	params.oids = oids;
	
	sendRequest("UpdateUsers", params , "users", "updater");
	
	document.getElementById("confirmation").innerHTML = "Änderungen erfolgreich gespeichert.";
	window.setTimeout("appearConfirmation();",1000);
}

// functions used in Snapshots and UpdateSnapshots Commands
var gantt = 0;
/**
 * function to display a gantt diagram of the selected snapshot
 */
function loadSnapshotGantt(version, workplanid, oids, tasks, start, end, depends, milestones, annotationid) {
	if (document.getElementById("confirmation") != null) {
		hideConfirmation();
	}
	// if already one gantt diagramm was displayed new tasks would just be added to old diagram; need reload to display another gantt diagram (cause of jsGantt)
	if (gantt != 0) {
		location.reload();
		return;
	}
	params = {};
	params.id = workplanid;
	params.version = version;
	params.annotationid = annotationid;
	sendRequest("UpdateSnapshots", params , "oldversioninfo", "updater");
	document.getElementById("current").style.display = "none";
	document.getElementById("currentversion").style.display = "";
	document.getElementById("hidecurrent").style.display = "none";
	document.getElementById("newsnap").style.display = "none";
	
	OID = JSON.parse(oids);
	tasks = tasks.substring(1, tasks.length-1);
	Start = JSON.parse(start);
	End = JSON.parse(end);
	Depends = JSON.parse(depends);
	Milestone = JSON.parse(milestones);
	if (tasks.indexOf(',') == -1) {
		task = tasks;
		if (task.length > 20) {
			task = task.substring(0, 20)+'(...)';
		}
	} else {
		task = tasks.substring(0, tasks.indexOf(','));
	}
	
	g.setShowRes(0);
	g.setShowDur(0); 
	g.setShowComp(0);
	g.setCaptionType('Duration');  // Set to Show Caption (None,Caption,Resource,Duration,Complete)
	g.setShowStartDate(1); 
	g.setShowEndDate(1); 
	g.setDateInputFormat('dd/mm/yyyy')  // Set format of input dates ('mm/dd/yyyy', 'dd/mm/yyyy', 'yyyy-mm-dd')
	g.setDateDisplayFormat('dd.mm.yyyy') // Set format to display dates ('mm/dd/yyyy', 'dd/mm/yyyy', 'yyyy-mm-dd')
	g.setFormatArr("day", "week", "month") // Set format options (up to 4 : "minute","hour","day","week","month","quarter")
	if( g ) {
		for (i=0; i<OID.length; i++) {
			
			var starthelp = new Date(Start[i]*1000);
			var starthelpday = starthelp.getDate();
			
			if (starthelpday < 10) {
				starthelpday = "0" + starthelpday;
			}
			var starthelpmonth = starthelp.getMonth()+1;
			if (starthelpmonth < 10) {
				starthelpmonth = "0" + starthelpmonth;
			}
			var starthelpyear = starthelp.getFullYear();
			starthelp = starthelpday + "/" + starthelpmonth + "/" + starthelpyear;

			var endhelp = new Date(End[i]*1000);
			var endhelpday = endhelp.getDate();
			if (endhelpday < 10) {
				endhelpday = "0" + endhelpday;
			}
			var endhelpmonth = endhelp.getMonth()+1;
			if (endhelpmonth < 10) {
				endhelpmonth = "0" + endhelpmonth;
			}
			var endhelpyear = endhelp.getFullYear();
			endhelp = endhelpday + "/" + endhelpmonth + "/" + endhelpyear;
			if (Depends[i] == -1) {
				Depends[i] = '';
			}
			if (Milestone[i] == 1) {
				g.AddTaskItem(new JSGantt.TaskItem(OID[i], task, starthelp, endhelp, 'ff00ff', 'http://help.com', 1, 'User', 100, 0, 1, 1, Depends[i], ""));
			} else {
				g.AddTaskItem(new JSGantt.TaskItem(OID[i], task, starthelp, endhelp, '396789', 'http://help.com', 0, 'User', 0, 0, 1, 1, Depends[i], ""));
			}
			tasks = tasks.substring(tasks.indexOf(',')+1, tasks.length+1);
			task = tasks.substring(0, tasks.indexOf(','));
			if (tasks.indexOf(',') == -1) {
				task = tasks;
			}
			if (task.length > 20) {
				task = task.substring(0, 20)+'(...)';
			}
		}
	}
	g.Draw();	
	g.DrawDependencies();
	gantt++;
}

/**
 * function to display the list view of the selected snapshot
 */
function loadSnapshotList(version, workplanid, annotationid) {
	if (document.getElementById("confirmation") != null) {
		hideConfirmation();
	}
	
	params2 = {}
	params2.id = workplanid;
	params2.version = version;
	params2.annotationid = annotationid;
	
	params = {};
	params.id = workplanid;
	params.update = 0;
	params.snapshot = version;
	
	if (version != 0) {
		sendRequest("UpdateSnapshots", params2 , "oldversioninfo", "updater");
		sendRequest("UpdateListView", params , "oldversion", "updater");
		document.getElementById("currentversion").style.display = "";
		document.getElementById("current").style.display = "none";
	} else {
		params.snapshot = -1;
		sendRequest("UpdateSnapshots", params2 , "currentversioninfo", "updater");
		sendRequest("UpdateListView", params , "currentversiontable", "updater");
		document.getElementById("current").style.display = "";
		document.getElementById("currentversion").style.display = "none";
	}
	document.getElementById("newsnap").style.display = "none";
	document.getElementById("hidecurrent").style.display = "none";
}

/**
 * function to hide a snapshot in the user view
 */
function hideSnapshot() {
	document.getElementById("oldversioninfo").innerHTML = "";
	document.getElementById("oldversion").innerHTML = "";
	document.getElementById("currentversion").style.display = "none";
	if (document.getElementById("newsnap").title != "none") {
		document.getElementById("newsnap").style.display = "";
	}
}

/**
 * function to hide the current version in the user view
 */
function hideCurrentSnapshot() {
	document.getElementById("current").style.display = "none";
	document.getElementById("hidecurrent").style.display = "none";
	document.getElementById("currentversion").style.display = "";
}

// functions used in several Commands
/**
 * function to let the confirmation bar appear
 */
function appearConfirmation() {
	if (document.getElementById("confirmation").style.display == "none") {
		Effect.Appear("confirmation");
	}
}

/**
 * function to hide the confirmation bar if it is displayed
 */
function hideConfirmation() {
	if (document.getElementById("confirmation").style.display != "none") {
		Effect.Fade("confirmation");
	}
}

/**
 * function to create a new snapshot when javascript dialog was submitted
 * @param workplanid
 */
function newSnapshot(workplanid) {
	hideConfirmation();
	var retVal = prompt("Bitte einen Namen für den Snapshot eingeben", "");
	if (retVal != null && retVal.length > 0) {
		params = {};
		params.id = workplanid;
		params.name = retVal;
		params.newsnapshot = 1;
		params.version = 0;
		sendRequest("UpdateSnapshots", params , "bla", "data");

		document.getElementById("confirmation").innerHTML = "Snapshot "+retVal+" erfolgreich erstellt.";
		window.setTimeout("appearConfirmation();",1000);
	}
}

/**
 * function to trim a string
 * @param string
 * @returns
 */
function trim (string) {
	  return string.replace (/^\s+/, '').replace (/\s+$/, '');
}

/**
 * function to check if given value is a valid string
 * @param divid
 * @returns {Boolean}
 */
function checkString(string) {
	if (trim(string).length > 0) {
		return true;
	} else {
		alert("Bitte einen gültigen Namen eingeben.");
		return false;
	}
}

/**
 * function to check if the value is a date of the form DD.MM.YYYY
 * @param date
 * @returns {Boolean}
 */
function checkDate(date, output) {
    var D, d=date.split(/\D+/);
    d[0]*=1;
    d[1]-=1;
    d[2]*=1;
    if (date.length != 10) {
    	if (output == 1) {
    		alert("Bitte ein gültiges Datum der Form TT.MM.JJJJ eingeben.");
    	}
    	return false;
    }
    D=new Date(d[2],d[1],d[0]);
    
    if(D.getFullYear()== d[2] && D.getMonth()== d[1] && D.getDate()== d[0]) {
    	return true;
    } else {
    	if (output == 1) {
    		alert("Bitte ein gültiges Datum der Form TT.MM.JJJJ eingeben.");
    	}
    	return false;
    }
}

/**
 * function to check if given value is a valid int
 * @param divid
 * @returns {Boolean}
 */
function checkInteger(int, output) {
	var test = /^\d+$/.test(int);
	if (test) {
		return true;
	} else {
		if (output == 1) {
			alert("Bitte einen gültigen Zahlenwert (ganze Zahl) eingeben.");
		}
		return false;
	}
}

/**
 * function to check that end isnt before start
 * @param start
 * @param end
 * @returns {Boolean}
 */
function checkTaskStartEnd(start, end) {
	start = start.split(/\D+/);
    start[0]*=1;
    start[1]-=1;
    start[2]*=1;
    start= new Date(start[2],start[1],start[0]);
    end = end.split(/\D+/);
    end[0]*=1;
    end[1]-=1;
    end[2]*=1;
    end = new Date(end[2],end[1],end[0]);
	if (end < start) {
		return false;
	} else return true;
}

/**
 * function to calculate an end date for a task if needed information is given
 * @param divid
 * @returns {Boolean}
 */
function enterEndDate(id) {
	if (id == 1) {
		var start = document.getElementById("taskstart").value;
		var end = document.getElementById("taskend").value;
		var duration = document.getElementById("taskduration").value;
		var worktime = 0;
		if (duration == 0 || !checkInteger(duration, 0)) return;
		if (!checkDate(start, 0)) return;
		//if (end.length > 0) return;
		
		var length = document.getElementById("taskusers").options.length;
		for (i=0; i < length; i++) {
			if (document.getElementById("taskusers").options.item(i).selected == true) {
				worktime = worktime + parseInt(document.getElementById("taskusers").options.item(i).title);
			}
		}
	} else {
		var start = document.getElementsByName("taskstart")[0].value;
		var end = document.getElementsByName("taskend")[0].value;
		var duration = document.getElementsByName("taskduration")[0].value;
		var worktime = 0;
		if (duration == 0 || !checkInteger(duration, 0)) return;
		if (!checkDate(start, 0)) return;
		//if (end.length > 0) return;
		
		var length = document.getElementsByName("taskusers[]")[0].options.length;
		for (i=0; i < length; i++) {
			if (document.getElementsByName("taskusers[]")[0].options.item(i).selected == true) {
				worktime = worktime + parseInt(document.getElementsByName("taskusers[]")[0].options.item(i).title);
			}
		}
	}
	
	if (worktime == 0) return;
	worktime = worktime / 7;
	worktime = Math.ceil(duration / worktime);
	
    var D, d=start.split(/\D+/);
    d[0]*=1;
    d[1]-=1;
    d[2]*=1;
    D=new Date(d[2],d[1],d[0]);
    D.setDate(D.getDate()+worktime); 
    day = D.getDate();
    if (day < 10) day = '0'+day;
    month = D.getMonth()+1;
    if (month < 10) month = '0'+month;
 
    if (id == 1) {
    	document.getElementById("taskend").value = day + '.' + month + '.' + D.getFullYear();
    } else {
    	document.getElementsByName("taskend")[0].value = day + '.' + month + '.' + D.getFullYear();
    }
}