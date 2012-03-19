/**
 * displayConfirmation()
 * 
 * Displays a confimation dialog box if the user tries to leave the page, 
 * after unsaved changes were made to the object.
 */
function displayConfirmation() {
	
	var showDialog = handleChanges;
	var cfmsg = "Sie verlassen die Seite! Wenn Sie auf 'OK' klicken, werden alle Änderungen verworfen. " +
				"Mit 'Abbrechen' können Sie die Bearbeitung fortsetzen.";
	
	if(showDialog) {
		if(confirm(cfmsg)) {
			
	        // DISCARD changes
		} 
		else {
			// ABORT
	        document.getElementById('CMD_ABORT').value = 'TRUE';
	        document.exerciseFormular.submit();
		}
	}
};


/**
 * dynamicList Class
 * 
 * Stores Attributes and Functions for the handling of a dynamicList.
 * used for selecting the allowed files in an exercise
 */
dynamicList = function(pElement,pEntries,pNewId,pType) {

	this.entries = pEntries;	// number of entries currently in the dynamicList (must be >= 1)
	this.newId = pNewId;		// id of the next entry
	this.element = pElement;
	this.type = pType;
};

dynamicList.prototype = {
		
	/**
	 * addEntry function
	 * 
	 * Adds a new line to the dynamicList
	 */
	addEntry: function() {
		
		if ( this.type == 1 ) {
			var newEntryId = "dynamicEntry_" + this.newId;
		}
		else {
			var newEntryId = "dynamicEntry_GRP_" + this.newId;
		}
		
		var newEntry = document.createElement('tr');
		newEntry.setAttribute( 'id', newEntryId );
		
		if ( this.type == 1 ) {
			newEntry.innerHTML = 	'<td style="width:40%;">'  +
									'<input type="text" class="dynamicCell" name="tt_names[]" value="" />'  +
									'</td>'  +
									'<td style="width:50%;">'  +
									'<input type="text" class="dynamicCell" readonly="readonly" name="tt_fnames[]" value="" />'  +
									'</td>'  +
									'<td style="width:10%;">'  +
									'<button type="button" class="dynamicAction" onClick="reviewers.removeEntry(\'' + newEntryId + '\')">X</button>'  +
									'</td>';
		}
		else {
			newEntry.innerHTML = 	'<td style="width:50%;">'  +
									'<input type="text" class="dynamicCell" name="gr_names[]" value="" />'  +
									'</td>'  +
									'<td style="width:40%;">'  +
									'<input type="text" class="dynamicCell" name="gr_tts[]" value="" />'  +
									'</td>'  +
									'<td style="width:10%;">'  +
									'<button type="button" class="dynamicAction" onClick="tutorials.removeEntry(\'' + newEntryId + '\')">X</button>'  +
									'</td>';
		}
		
		document.getElementById(this.element).appendChild(newEntry);
		
		this.entries++;
		this.newId++;
	},
	
	/**
	 * removeEntry
	 * 
	 * Removes the line identified by its (DOM) id from the dynamicList
	 */
	removeEntry: function(entryId) {
		
		if ( this.entries <= 1 ) {
			
			if ( this.type == 1 ) {
				window.alert('Es muss mindestens ein Tutor angegeben werden.');
			}
			else {
				window.alert('Es muss mindestens eine Übungsgruppe angegeben werden.');
			}
			
			return;
		}
		
		var entry = document.getElementById(entryId);
		document.getElementById(this.element).removeChild(entry);
		
		this.entries--;
	}
};