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
	
	if(!handleChanges) {
		
		//display dialog if at least the files have changed
		showDialog = (uploaderarray.dirtyFlag) ? true : false;
	}
	
	if(showDialog) {
		if(confirm(cfmsg)) {
			
	        // DISCARD changes:
			//delete unsaved files
			sendSjaxCommand('Delete', uploaderarray.baseroom, 'unsaved');   //delete all files with ISNEW attribute
			//restore delete-marked files
			sendSjaxCommand('Restore', uploaderarray.baseroom, 'deleteFlagged');  //restore all files with a delete Flag
			//if mode==CREATE -> delete container
			if (document.getElementById('OPERATION').value == 'CREATE') {
				
				sendSjaxCommand('Delete', uploaderarray.baseroom);  //delete container
			}
		} 
		else {
			// ABORT:
			// keep unsaved data by rePOSTing it to the page
	        document.getElementById('CMD_ABORT').value = 'TRUE';
	        document.exerciseFormular.submit();
		}
	}
}


/**
 * sendSjaxCommand()
 * 
 * Sends a XHR to the server but does this synchronously, which will prevent the
 * page from unloading until the response is received.
 * 
 * @param String command The desired koaLA command.
 * @param String steamid The sTeam ID of the object on which the action should be inflicted.
 * @param String mode A mode keyword that selects a specific behaviour.
 */
function sendSjaxCommand(command, steamid, mode) {
	
	var requestObject = new XMLHttpRequest();
	requestObject.onreadystatechange = function(){
    		if (requestObject.readyState == 4){}
    };
	var params = {};
	params['command'] = command;
	params['steamid'] = steamid;
	params['mode'] = mode;
	var query = qq.obj2url(params, 'exercise/');
    requestObject.open("GET", query, false);
    requestObject.setRequestHeader("X-Requested-With", "XMLHttpRequest");
    requestObject.setRequestHeader("Content-Type", "text/xml");
    requestObject.send(null);
}


/**
 * dynamicList Object
 * 
 * Stores Attributes and Functions for the handling of a dynamicList.
 * used for selecting the allowed files in an exercise
 */
var dynamicList = dynamicList || {};

dynamicList.entries = 1;	// number of entries currently in the dynamicList (must be >= 1)
dynamicList.newId = 2;		// id of the next entry
dynamicList.type = 1;

/**
 * addEntry function
 * 
 * Adds a new line to the dynamicList
 */
dynamicList.addEntry = function() {
	
	var newEntryId = "dynamicEntry_" + this.newId;
	var newEntry = document.createElement('tr');
	newEntry.setAttribute( 'id', newEntryId );
	
	if ( this.type == 1 ) {
		newEntry.innerHTML = 	'<td style="width:60%;">'  +
								'<input type="text" class="dynamicCell" name="sf_names[]" value="" />'  +
								'</td>'  +
								'<td style="width:30%;">'  +
								'<input type="text" class="dynamicCell" name="sf_types[]" value="" />'  +
								'</td>'  +
								'<td style="width:10%;">'  +
								'<button type="button" class="dynamicAction" onClick="dynamicList.removeEntry(\'' + newEntryId + '\')">X</button>'  +
								'</td>';
	}
	else {
		newEntry.innerHTML = 	'<td style="width:40%;">'  +
								'<input type="text" class="dynamicCell" name="pp_names[]" value="" />'  +
								'</td>'  +
								'<td style="width:50%;">'  +
								'<input type="text" class="dynamicCell" readonly="readonly" value="" />'  +
								'</td>'  +
								'<td style="width:10%;">'  +
								'<button type="button" class="dynamicAction" onClick="dynamicList.removeEntry(\'' + newEntryId + '\')">X</button>'  +
								'</td>';
	}
	
	document.getElementById('dynamicList').appendChild(newEntry);
	
	this.entries++;
	this.newId++;
};

/**
 * removeEntry
 * 
 * Removes the line identified by its (DOM) id from the dynamicList
 */
dynamicList.removeEntry = function(entryId) {
	
	if ( this.entries <= 1 ) {
		
		if ( this.type == 1 ) {
			window.alert('Es muss mindestens eine Datei angegeben werden.');
		}
		else {
			window.alert('Es muss mindestens ein Autor angegeben werden!.');
		}
		
		return;
	}
	
	var entry = document.getElementById(entryId);
	document.getElementById('dynamicList').removeChild(entry);
	
	this.entries--;
};


/**
 * exerciseForm Class
 * 
 * Handles an Array of document uploaders
 * 
 * @param STRING b   	Url of extension
 * @param STRING i	 	sTeam ID of the container for this exercise
 * @param int	 size	Maximum size of a file upload
 * @param STRING ua	 	ID of DOM element that should contain all uploaders
 * @param STRING fu	 	ID of DOM element for the first uploader
 * @param STRING ab	 	ID of DOM element for the addDocument Button
 * @param array fixeduploaders  If you want to set the number of fileuploaders and not allow changes
 * @param array	preload			Array with [name, id] pairs of sTeam documents already in the exercise (for editing)
 */
exerciseForm = function(b,i,size,ua,fu,ab,fixeduploaders,preload) {
	
	//* process params
	this.backend = b;
	this.baseroom = i;
	this.dom_element = document.getElementById(ua);       //dom element containing all uploaders
	this.dom_firstuploader = document.getElementById(fu); //dom element for first uploader
	this.dom_addbutton = document.getElementById(ab);	  //dom element container for add Button
	
	this.documentUploaders = new Array();	//array containing all uploader objects
	this.createduploaders = 0;				//number of uploaders on the page
	this.uploaderCSS = 'document-uploader';
	
	this.dirtyFlag = false;		//keeps track of file changes
	
	//* settings for one uploader
	this.sizeLimit = size;				//max size for one file upload
	this.allowedExtensions = [];		//can be overridden, if fixeduploaders are used
	this.debug = true;					//true allows browser console output
	this.trackfiles = true;
	if (fixeduploaders[0]==false) {
		this.trackfiles = false;		//add attribute to sTeam docs containing an additional id for preloading	
	}
	
	//* create addButton for new document Uploaders
	if (fixeduploaders[0]==false) 
		this._addButton = this._createAddButton();
	
	//* use user-creatable uploaders, or fixed uploaders set in the exercise
	if ( fixeduploaders[0] == false ) {
		
		//* initialize with first Uploader or preload array of Uploaders with existing objects
		if ( preload[0] == false ) { // create new Exercise
			
			//initialize with first document-Uploader
			var id = this.createduploaders;
			var u1	= new qq.FileUploader({
				
					//element for first document-Uploader is already in template.html
					element: this.dom_firstuploader,
			        action: this.backend,
					baseroom: this.baseroom,
					sizeLimit:  this.sizeLimit,
					trackfiles: this.trackfiles,
					allowedExtensions: this.allowedExtensions,
			        debug: this.debug,
			        ID: id,
			        parentForm: this
			});
			
			//create deleteButton for documentUploader
			u1._deluploaderbutton = this._createDeleteButton(id, u1);
			
			//add uploader to array
			this.documentUploaders[id] = u1;
			this.createduploaders++;
		}
		else { // edit existing Exercise
			
			for ( var i = 0 ; i < preload.length ; i++ ) {
				
				// [ [name, id], [name, id] ]
				this._createDocumentUploader(true, false, preload[i][0], preload[i][1], null);
			}	
		}
	}
	else {
		
		//introduce id to the solution_files attribute that can be attached to the uploaded documents (in solution) in order to preload these files into the 
		//correct fileuploader, eh=?
		
		//allowedExtensions: ['txt'],
		
		//bei den uploadern in diesem ELSE müssen immer die "solutionid" attribute auf die tracker ID gesetzt werden, rest in diesem JS programm ist fertig dementsprechend
		
		if ( preload[0] == false ) {
			
			for ( var i = 0 ; i < fixeduploaders.length ; i++ ) {
				
				// [ [name, type, ID], [name, type, ID] ]  ID being the id of the file attribute set in exercise
				this._createDocumentUploader(false, true, null, null, fixeduploaders[i]);
			}
		}
		else {
			
			var match = false;
			var arrid;
			//load uploaders with existing files first
			for ( var i = 0 ; i < preload.length ; i++ ) {
				
				match = false;
				for ( var j = 0 ; j < fixeduploaders.length ; j++ ) {
					
					if ( preload[i][2] == fixeduploaders[j][2] ) {
						
						match = true;
						arrid = j;
						fixeduploaders[j][3] = '[ISSET]';
					}
				}
				if (match) {
					this._createDocumentUploader(true, true, preload[i][0], preload[i][1], fixeduploaders[arrid]);
				}
			}
			//load uploaders that are still empty
			for ( var k = 0 ; k < fixeduploaders.length ; k++ ) {
				
				if (!(fixeduploaders[k][3] == '[ISSET]')) {
					
					this._createDocumentUploader(false, true, null, null, fixeduploaders[k]);
				}
			}
		}
	}
};

exerciseForm.prototype = {
	
	/**
	 * _createDocumentUploader()
	 * 
	 * @param boolean loadexisting (optional) TRUE if the uploader list should contain an existing sTeam document
	 * @param boolean fixedloaders (optional) TRUE if you want to use the uploaders defined in the exercise
	 * @param String filename (optional) name of the steam document
	 * @param int steamid (optional) sTeam Id of the document
	 * @param Array solutionfile (optional) [name, type, id] of the solution file set in exercise
	 */
	_createDocumentUploader: function(loadexisting,fixedloaders,filename,steamid,solutionfile) {
		
		if ( loadexisting == null ) 
			 loadexisting = false;
		
		if ( fixedloaders == null )
			 fixedloaders = false;
		
		//create dom for new uploader
		var uploaderelement = document.createElement('div');
		uploaderelement.setAttribute("class", this.uploaderCSS);
		
		this.dom_element.appendChild(uploaderelement);

		//create uploader
		var id = this.createduploaders;
		
		if (fixedloaders) {
			
			var ext;
			if (solutionfile[1]=='*')
				ext = [];
			else
				ext = [solutionfile[1]];
			
			var u = new qq.FileUploader({
				element: uploaderelement,
	            action: this.backend,
	    		baseroom: this.baseroom,
	    		sizeLimit:  this.sizeLimit,
				trackfiles: this.trackfiles,
				solutionid: solutionfile[2],
				solutionname: solutionfile[0],
				solutiontype: solutionfile[1],
	    		allowedExtensions: ext,
	            debug: this.debug,
	            ID: id,
	            steamDoc: steamid,
	            parentForm: this
			});
		}
		else {
			
			var u = new qq.FileUploader({
				element: uploaderelement,
	            action: this.backend,
	    		baseroom: this.baseroom,
	    		sizeLimit:  this.sizeLimit,
				trackfiles: this.trackfiles,
	    		allowedExtensions: this.allowedExtensions,
	            debug: this.debug,
	            ID: id,
	            steamDoc: steamid,  //will be null if parameter not given
	            parentForm: this
			});
			
			//create deleteButton for documentUploader
			u._deluploaderbutton = this._createDeleteButton(id, u);
		}
		
		//adjust documentUploader if an existing upload is chosen
		if ( loadexisting ) {
			
			u._button._disable();
			u._delbutton._enable();
			u._options.uploadallowed = 0;
			u._addToList(0, filename);
			qq.remove(u._find(u._getItemByFileId(0), 'cancel'));
			qq.remove(u._find(u._getItemByFileId(0), 'size'));
		}
	
		//add uploader to array
		this.documentUploaders[id] = u;
		this.createduploaders++;
	},
	
	/**
	 * _deleteDocumentUploader()
	 * 
	 * @param int id ID of the documentUploader in the exerciseForm.documentUploaders Array
	 */
	_deleteDocumentUploader: function(id) {
		
		//delete file of the uploader first, false will set the complete fnc. to this._onDeleteComplete()
		this.documentUploaders[id]._onDelete(false);
	},
	
	/**
	 * _onDeleteComplete()
	 * 
	 * called when XHR completed the delete-command on the server
	 *
	 * @param XMLHttpRequest requestObject
	 * @param int id ID of the documentUploader in the exerciseForm.documentUploaders Array
	 */
	_onDeleteComplete: function(requestObject, id) {
		
		var response = {};
    	
    	if (requestObject.status == 200){
    		
    		if (window.console) console.log('[deleteCommand] ' + requestObject.responseText);/////////////// LOG ACTION (DELETE)
              
            try {
                response = eval("(" + requestObject.responseText + ")");
            } catch(err){
                response = {};
            }              
        }
    	
    	if (response.success){
    		
    		//remove documentUploader from DOM
    		documentUploader = this.documentUploaders[id]._element;
    		this.dom_element.removeChild(documentUploader);
    		
    		//remove documentUploader from exerciseForm attributes
    		this.documentUploaders[id] = null;
    		
    		//register the action for history
            this.dirtyFlag = true;
        }
    	else {
    		
    		if (response.error) {
    			
    			this._options.showMessage(response.error);
    		}
    	}
	},
	
	/**
	 * _createAddButton()
	 */
	_createAddButton: function() {
		
		var self = this;  //saves reference to this object
		
		return new exerciseFormButton(
			{
				element: this.dom_addbutton,
				name: 'addDocumentUploader',
				hoverClass: 'document-add-button-hover',
				focusClass: 'document-add-button-focus',
				disableCls: 'document-add-button-disabled',
				type: 		'submit',
				eventtype:  'click',
				onChange:   function(input){  // input ist der <input> tag, dieser wird fuer den eventhandler benutzt
							self._createDocumentUploader();
				}
			}
		);
	},
	
	/**
	 * _createDeleteButton()
	 * 
	 * @param int id ID of the documentUploader in the exerciseForm.documentUploaders Array
	 * @param qq.FileUploader u The Uploader Object
	 */
	_createDeleteButton: function(id, u) {
		
		var self = this;  //saves reference to this object
		
		//create dom container for Button and attach to qq.Fileuploader element
		var button = document.createElement('div');
		button.setAttribute("class", "document-delete-button");
		button.innerHTML = "<strong>X</strong>";
		
		/*//insert button before uploader list
		uploaderelem = u._element.firstChild;	
		uploaderlist = uploaderelem.lastChild;
		uploaderelem.insertBefore(button, uploaderlist);*/
		
		uploaderelem = u._element;	
		uploaderelem.parentNode.appendChild(button);
		
		//create and return button
		return new exerciseFormButton(
			{
				element: button,
				name: 'deleteDocumentUploader',
				hoverClass: 'document-delete-button-hover',
				focusClass: 'document-delete-button-focus',
				disableCls: 'document-delete-button-disabled',
				type: 		'submit',
				eventtype:  'click',
				
				siblingId: id,		// save the id of the uploader object for the deleteDocumentUploader function
				
				onChange:   function(input){  // input ist der <input> tag, dieser wird fuer den eventhandler benutzt
							self._deleteDocumentUploader(this.siblingId);
				}
			}
		);
	}
};



/**
 * exerciseFormButton Class
 */
exerciseFormButton = function(o){
    this._options = {
    	
        element:  null,  				// dom element containing the <input> tag
        name: 		'', 			// name attribute of file input
        onChange: function(input){},	// function for the eventhandler
        type: 		'',					// type of <input> tag
        eventtype:	'',					// type of action for eventhandler (e.g. (on)click)
        hoverClass: '',					// css 
        focusClass: '',
        disableCls: ''
    };
    
    qq.extend(this._options, o);
        
    this._element = this._options.element;
    
    // make button suitable container for input
    qq.css(this._element, {
        position: 'relative',
        overflow: 'hidden',
        // Make sure browse button is in the right side
        // in Internet Explorer
        direction: 'ltr'
    });   
    
    this._input = this._createInput();
};

exerciseFormButton.prototype = {
    /* returns file input element */    
    getInput: function(){
        return this._input;
    },
    /* cleans/recreates the file input */
    reset: function(){
        if (this._input.parentNode){
            qq.remove(this._input);    
        }                
        
        qq.removeClass(this._element, this._options.focusClass);
        this._input = this._createInput();
    },
    _disable: function(){
    	
    	// removes/adds CSS classes
    	qq.removeClass(this._element, this._options.hoverClass);
    	qq.removeClass(this._element, this._options.focusClass);
    	qq.addClass(this._element, this._options.disableCls);
    	
    	// removes the <input> tag
    	this._element.removeChild(this._input);
    },
    _enable: function(){
    	
    	// resets the CSS style
    	qq.removeClass(this._element, this._options.disableCls);
    	
    	// recreates <input> tag
    	this._input = this._createInput();
    },
    _createInput: function(){                
        var input = document.createElement("input");
        
        input.setAttribute("type", this._options.type);
        input.setAttribute("name", this._options.name);
        
        qq.css(input, {
            position: 'absolute',
            // in Opera only 'browse' button
            // is clickable and it is located at
            // the right side of the input
            right: 0,
            top: 0,
            fontFamily: 'Arial',
            // 4 persons reported this, the max values that worked for them were 243, 236, 236, 118
            fontSize: '118px',
            margin: 0,
            padding: 0,
            cursor: 'pointer',
            opacity: 0
        });
        
        this._element.appendChild(input);

        var self = this;
        /**
         * Adds an EventHandler to the Button for the (on)Click / (on)... event
         */
        qq.attach(input, this._options.eventtype, function(){ 
            self._options.onChange(input);
        });
                
        qq.attach(input, 'mouseover', function(){
            qq.addClass(self._element, self._options.hoverClass);
        });
        qq.attach(input, 'mouseout', function(){
            qq.removeClass(self._element, self._options.hoverClass);
        });
        qq.attach(input, 'focus', function(){
            qq.addClass(self._element, self._options.focusClass);
        });
        qq.attach(input, 'blur', function(){
            qq.removeClass(self._element, self._options.focusClass);
        });

        // IE and Opera, unfortunately have 2 tab stops on file input
        // which is unacceptable in our case, disable keyboard access
        if (window.attachEvent){
            // it is IE or Opera
            input.setAttribute('tabIndex', "-1");
        }

        return input;            
    }        
};














/**
 * http://github.com/valums/file-uploader
 * 
 * Multiple file upload component with progress-bar, drag-and-drop. 
 * © 2010 Andrew Valums ( andrew(at)valums.com ) 
 * 
 * Licensed under GNU GPL 2 or later and GNU LGPL 2 or later, see license.txt.
 */    

//
// Helper functions
//

var qq = qq || {};

/**
 * Adds all missing properties from second obj to first obj
 */ 
qq.extend = function(first, second){
    for (var prop in second){
        first[prop] = second[prop];
    }
};  

/**
 * Searches for a given element in the array, returns -1 if it is not present.
 * @param {Number} [from] The index at which to begin the search
 */
qq.indexOf = function(arr, elt, from){
    if (arr.indexOf) return arr.indexOf(elt, from);
    
    from = from || 0;
    var len = arr.length;    
    
    if (from < 0) from += len;  

    for (; from < len; from++){  
        if (from in arr && arr[from] === elt){  
            return from;
        }
    }  
    return -1;  
}; 
    
qq.getUniqueId = (function(){
    var id = 0;
    return function(){ return id++; };
})();

//
// Events

qq.attach = function(element, type, fn){
    if (element.addEventListener){
        element.addEventListener(type, fn, false);
    } else if (element.attachEvent){
        element.attachEvent('on' + type, fn);
    }
};
qq.detach = function(element, type, fn){
    if (element.removeEventListener){
        element.removeEventListener(type, fn, false);
    } else if (element.attachEvent){
        element.detachEvent('on' + type, fn);
    }
};

qq.preventDefault = function(e){
    if (e.preventDefault){
        e.preventDefault();
    } else{
        e.returnValue = false;
    }
};

//
// Node manipulations

/**
 * Insert node a before node b.
 */
qq.insertBefore = function(a, b){
    b.parentNode.insertBefore(a, b);
};
qq.remove = function(element){
    element.parentNode.removeChild(element);
};

qq.contains = function(parent, descendant){       
    // compareposition returns false in this case
    if (parent == descendant) return true;
    
    if (parent.contains){
        return parent.contains(descendant);
    } else {
        return !!(descendant.compareDocumentPosition(parent) & 8);
    }
};

/**
 * Creates and returns element from html string
 * Uses innerHTML to create an element
 */
qq.toElement = (function(){
    var div = document.createElement('div');
    return function(html){
        div.innerHTML = html;
        var element = div.firstChild;
        div.removeChild(element);
        return element;
    };
})();

//
// Node properties and attributes

/**
 * Sets styles for an element.
 * Fixes opacity in IE6-8.
 */
qq.css = function(element, styles){
    if (styles.opacity != null){
        if (typeof element.style.opacity != 'string' && typeof(element.filters) != 'undefined'){
            styles.filter = 'alpha(opacity=' + Math.round(100 * styles.opacity) + ')';
        }
    }
    qq.extend(element.style, styles);
};
qq.hasClass = function(element, name){
    var re = new RegExp('(^| )' + name + '( |$)');
    return re.test(element.className);
};
qq.addClass = function(element, name){
    if (!qq.hasClass(element, name)){
        element.className += ' ' + name;
    }
};
qq.removeClass = function(element, name){
    var re = new RegExp('(^| )' + name + '( |$)');
    element.className = element.className.replace(re, ' ').replace(/^\s+|\s+$/g, "");
};
qq.setText = function(element, text){
    element.innerText = text;
    element.textContent = text;
};

//
// Selecting elements

qq.children = function(element){
    var children = [],
    child = element.firstChild;

    while (child){
        if (child.nodeType == 1){
            children.push(child);
        }
        child = child.nextSibling;
    }

    return children;
};

qq.getByClass = function(element, className){
    if (element.querySelectorAll){
        return element.querySelectorAll('.' + className);
    }

    var result = [];
    var candidates = element.getElementsByTagName("*");
    var len = candidates.length;

    for (var i = 0; i < len; i++){
        if (qq.hasClass(candidates[i], className)){
            result.push(candidates[i]);
        }
    }
    return result;
};

/**
 * obj2url() takes a json-object as argument and generates
 * a querystring. pretty much like jQuery.param()
 * 
 * how to use:
 *
 *    `qq.obj2url({a:'b',c:'d'},'http://any.url/upload?otherParam=value');`
 *
 * will result in:
 *
 *    `http://any.url/upload?otherParam=value&a=b&c=d`
 *
 * @param  Object JSON-Object
 * @param  String current querystring-part
 * @return String encoded querystring
 */
qq.obj2url = function(obj, temp, prefixDone){
    var uristrings = [],
        prefix = '&',
        add = function(nextObj, i){
            var nextTemp = temp 
                ? (/\[\]$/.test(temp)) // prevent double-encoding
                   ? temp
                   : temp+'['+i+']'
                : i;
            if ((nextTemp != 'undefined') && (i != 'undefined')) {  
                uristrings.push(
                    (typeof nextObj === 'object') 
                        ? qq.obj2url(nextObj, nextTemp, true)
                        : (Object.prototype.toString.call(nextObj) === '[object Function]')
                            ? encodeURIComponent(nextTemp) + '=' + encodeURIComponent(nextObj())
                            : encodeURIComponent(nextTemp) + '=' + encodeURIComponent(nextObj)                                                          
                );
            }
        }; 

    if (!prefixDone && temp) {
      prefix = (/\?/.test(temp)) ? (/\?$/.test(temp)) ? '' : '&' : '?';
      uristrings.push(temp);
      uristrings.push(qq.obj2url(obj));
    } else if ((Object.prototype.toString.call(obj) === '[object Array]') && (typeof obj != 'undefined') ) {
        // we wont use a for-in-loop on an array (performance)
        for (var i = 0, len = obj.length; i < len; ++i){
            add(obj[i], i);
        }
    } else if ((typeof obj != 'undefined') && (obj !== null) && (typeof obj === "object")){
        // for anything else but a scalar, we will use for-in-loop
        for (var i in obj){
            add(obj[i], i);
        }
    } else {
        uristrings.push(encodeURIComponent(temp) + '=' + encodeURIComponent(obj));
    }

    return uristrings.join(prefix)
                     .replace(/^&/, '')
                     .replace(/%20/g, '+'); 
};

//
//
// Uploader Classes
//
//

var qq = qq || {};
    
/**
 * Creates upload button, validates upload, but doesn't create file list or dd. 
 * 
 * --> KLASSENDEKLARATION qq.FileUploaderBasic
 */
qq.FileUploaderBasic = function(o){
    this._options = {
    	
    	// only upload when upload allow (set to 0 if a file was already uploaded)
    	uploadallowed: 1,
    		
        // set to true to see the server response
        debug: false,
        action: '/server/upload',
        baseroom: 0,
        params: {},
        button: null,
        multiple: false,
        maxConnections: 1,
        trackfiles: null,
        solutionid: null,
        // validation        
        allowedExtensions: [],               
        sizeLimit: 0,   
        minSizeLimit: 0,                             
        // events
        // return false to cancel submit
        onSubmit: function(id, fileName){},
        onProgress: function(id, fileName, loaded, total){},
        onComplete: function(id, fileName, responseJSON){},
        onCancel: function(id, fileName){}              
    };
    qq.extend(this._options, o);
        
    // number of files being uploaded
    this._filesInProgress = 0;
    this._handler = this._createUploadHandler(); 
    
    if (this._options.button){ 
        this._button = this._createUploadButton(this._options.button);
    }
                        
    this._preventLeaveInProgress();         
};
  

/**
 * --> (KLASSENDEKLARATION) weitere Funktionen
 */
qq.FileUploaderBasic.prototype = {
    setParams: function(params){
        this._options.params = params;
    },
    getInProgress: function(){
        return this._filesInProgress;         
    },
    _createUploadButton: function(element){
        var self = this;
        
        return new qq.UploadButton({
            element: element,
            multiple: this._options.multiple && qq.UploadHandlerXhr.isSupported(),
            onChange: function(input){ ////////////////////////////////////////////////////////////////////////7
                self._onInputChange(input);
            }        
        });           
    },    
    _createUploadHandler: function(){
        var self = this,
            handlerClass;        
        
        if(qq.UploadHandlerXhr.isSupported()){           
            handlerClass = 'UploadHandlerXhr';                        
        } else {
            handlerClass = 'UploadHandlerForm';
        }

        var handler = new qq[handlerClass]({
            debug: this._options.debug,
            action: this._options.action,
            baseroom: this._options.baseroom,
            trackfiles: this._options.trackfiles,
            solutionid: this._options.solutionid,
            maxConnections: this._options.maxConnections,   
            onProgress: function(id, fileName, loaded, total){                
                self._onProgress(id, fileName, loaded, total);
                self._options.onProgress(id, fileName, loaded, total);                    
            },            
            onComplete: function(id, fileName, result){
                self._onComplete(id, fileName, result);
                self._options.onComplete(id, fileName, result);
            },
            onCancel: function(id, fileName){
                self._onCancel(id, fileName);
                self._options.onCancel(id, fileName);
            }
        });

        return handler;
    },    
    _preventLeaveInProgress: function(){
        var self = this;
        
        qq.attach(window, 'beforeunload', function(e){
            if (!self._filesInProgress){return;}
            
            var e = e || window.event;
            // for ie, ff
            e.returnValue = self._options.messages.onLeave;
            // for webkit
            return self._options.messages.onLeave;             
        });        
    },    
    _onSubmit: function(id, fileName){
        this._filesInProgress++;  
        
    },
    _onProgress: function(id, fileName, loaded, total){        
    },
    _onComplete: function(id, fileName, result){
        this._filesInProgress--;                 
        if (result.error){
            this._options.showMessage(result.error);
        }             
    },
    _onCancel: function(id, fileName){
        this._filesInProgress--;        
    },
    _onInputChange: function(input){ /////////////////////////////////////////////////////////////////
    	
    	if (this._options.uploadallowed == 1) {
	        if (this._handler instanceof qq.UploadHandlerXhr){                
	            this._uploadFileList(input.files);                   
	        } else {             
	            if (this._validateFile(input)){                
	                this._uploadFile(input);                                    
	            }                      
	        }               
	        this._button.reset(); 
    	}
    	else {
    		alert("Please wait for the current Upload to complete.");
    	}
    },  
    _uploadFileList: function(files){
        for (var i=0; i<files.length; i++){
            if ( !this._validateFile(files[i])){
                return;
            }            
        }
        
        for (var i=0; i<files.length; i++){
            this._uploadFile(files[i]);        
        }        
    },       
    _uploadFile: function(fileContainer){      
        var id = this._handler.add(fileContainer);
        var fileName = this._handler.getName(id);
        
        if (this._options.onSubmit(id, fileName) !== false){
            this._onSubmit(id, fileName);
            this._handler.upload(id, this._options.params); //////////////////////////////////////////////////////////
        }
    },      
    _formatFileName: function(name){
        if (name.length > 33){
            name = name.slice(0, 19) + '...' + name.slice(-13);    
        }
        return name;
    },
    _isAllowedExtension: function(fileName){
        var ext = (-1 !== fileName.indexOf('.')) ? fileName.replace(/.*[.]/, '').toLowerCase() : '';
        var allowed = this._options.allowedExtensions;
        
        if (!allowed.length){return true;}        
        
        for (var i=0; i<allowed.length; i++){
            if (allowed[i].toLowerCase() == ext){ return true;}    
        }
        
        return false;
    }
};
    
       
/**
 * Class that creates upload widget with drag-and-drop and file list
 * @inherits qq.FileUploaderBasic
 * 
 * --> KLASSENDEKLARATION qq.FileUploader EXTENDS qq.FileUploaderBasic
 */
qq.FileUploader = function(o){
    // call parent constructor
    qq.FileUploaderBasic.apply(this, arguments);
    
    // additional options    
    qq.extend(this._options, {
        element: null,
        // if set, will be used instead of qq-upload-list in template
        listElement: null,
        
        // sTeam ID of the uploaded file
        steamDoc: null,
        
        // sTeam ID of the room for the uploaded file(s)
        baseroom: null,
        
        // ID of this FileUploader in the parent exerciseForm Array
        ID: null,
        
        // the exerciseForm containing this FileUploader
        parentForm: null,
        
        // delete file button
        delbutton: null,
        
        // delete uploader button
        deluploaderbutton: null,
        
        // add additional id to file after upload in order to preload it again into same uploader
        trackfiles: null,
        
        // additional id
        solutionid: null,
        
        solutionname: null,
        
		solutiontype: null,
        
        // template for one uploader (set later)
        template: null,

        // template for one item in file list
        fileTemplate: '<li>' +
                '<span class="qq-upload-file"></span>' +
                '<span class="qq-upload-spinner"></span>' +
                '<span class="qq-upload-size"></span>' +
                '<span class="qq-upload-ok"><img src="{PATH_URL}widgets/asset/icon_ok.png"></span>' +
                '<a class="qq-upload-cancel" href="#">abbrechen</a>' +
                '<span class="qq-upload-failed-text">Fehler</span>' +
            '</li>',        
        
        classes: {
            // used to get elements from templates
            button: 'qq-upload-button',
            delbutton: 'qq-delete-button',
            drop: 'qq-upload-drop-area',
            dropActive: 'qq-upload-drop-area-active',
            list: 'qq-upload-list',
                        
            file: 'qq-upload-file',
            spinner: 'qq-upload-spinner',
            size: 'qq-upload-size',
            ok: 'qq-upload-ok',
            cancel: 'qq-upload-cancel',
            failtext: 'qq-upload-failed-text',

            // added to list item when upload completes
            // used in css to hide progress spinner
            success: 'qq-upload-success',
            fail: 'qq-upload-fail'
        },
        // messages                
        messages: {
            typeError: "FILE hat das falsche Format. Nur EXTENSIONS ist erlaubt.",
            sizeError: "FILE ist zu groß, maximale Größe ist SIZELIMIT.",
            minSizeError: "FILE ist zu klein, minimale Größe ist MINSIZELIMIT.",
            emptyError: "FILE ist leer.",
            onLeave: "Der Dateiupload muss zuerst beendet werden."            
        },
        showMessage: function(message){
            alert(message);
        } 
    });
    // overwrite options with user supplied    
    qq.extend(this._options, o);  
    
    // write template for file uploader
    		//template: '<div class="qq-uploader">' + 
    //        '<div class="qq-upload-drop-area"><img src="{PATH_URL}widgets/asset/drop.png" style="float:left"><span>Dateien hier fallen lassen</span></div>' +
    //        '<div class="qq-upload-button"><img src="{PATH_URL}widgets/asset/drop.png" style="float:left">um eine Datei hochzuladen ziehen sie eine Datei auf dieses Feld oder doppelklicken sie hier.</div>' +
    //        '<div><ul class="qq-upload-list"></ul></div>' + 
    //    '</div>',
    
    if (this._options.trackfiles) {
    	var inf;
    	if (this._options.solutiontype == '*') {
    		inf = 'Diese Datei kann in einem beliebigen Format';
    	}
    	else {
    		inf = 'Diese Datei muss im Format </span><span class="fixedloader_type">*.' + this._options.solutiontype + '</span><span class="fixedloader_text">';
    	}
    	this._options.template = 	'<div class="qq-uploader">' + 
									'<div class="qq-fileinfo" style="padding-bottom:3px;"><span class="fixedloader_text">Datei: </span><span class="fixedloader_filename">' +
									this._options.solutionname +
    								'</span><br /><span class="fixedloader_text">' + inf +
    								' abgegeben werden.</span></div>' +
									'<div class="qq-upload-button">Datei hochladen</div>' +
									'<div class="qq-delete-button">L&ouml;schen</div>' +
									'<ul class="qq-upload-list"></ul>' + 
									'</div>';
    }
    else {
    	this._options.template = 	'<div class="qq-uploader">' + 
									'<div class="qq-upload-button">Datei hochladen</div>' +
									'<div class="qq-delete-button">L&ouml;schen</div>' +
									'<ul class="qq-upload-list"></ul>' + 
									'</div>';
    }
    

    this._element = this._options.element;
    this._element.innerHTML = this._options.template;        
    this._listElement = this._options.listElement || this._find(this._element, 'list');
    
    this._ID = this._options.ID;
    this._parentForm = this._options.parentForm;
    this._steamDoc = this._options.steamDoc;
    this._trackfiles = this._options.trackfiles;
    this._solutionid = this._options.solutionid;
    
    this._classes = this._options.classes;
    
    //create UploadButton
    this._button = this._createUploadButton(this._find(this._element, 'button'));
    
    //create DeleteButton and disable it
    this._delbutton = this._createDeleteButton(this._find(this._element, 'delbutton'));
    this._delbutton._disable();
    
    this._bindCancelEvent();
};


/**
 * --> (KLASSENDEKLARATION) weitere Funktionen
 */
// inherit from Basic Uploader
qq.extend(qq.FileUploader.prototype, qq.FileUploaderBasic.prototype);

qq.extend(qq.FileUploader.prototype, {
    /**
     * Gets one of the elements listed in this._options.classes
     **/
    _find: function(parent, type){                                
        var element = qq.getByClass(parent, this._options.classes[type])[0];        
        if (!element){
            throw new Error('element not found ' + type);
        }
        
        return element;
    },
    /**
     * _createDeleteButton()
     * 
     * creates button and sets the onChange function to call the FileUploaders _onDelete() function
     */
    _createDeleteButton: function(element){
    	
    	var self = this;
    	
        return new qq.DeleteButton({
            element: element,
            onChange: function(input){   // input ist der <input> tag, dieser wird fuer den eventhandler benutzt
                self._onDelete(true); 
            }        
        });           
    },
    /**
     * _onDelete()
     * 
     * sends the Delete Action via XHR to the Delete-Command
     * 
     * @param boolean deletefileonly set if you only want to delete the file, whole documentUploader otherwise
     */
    _onDelete: function(deletefileonly){
    	
    	var requestObject = new XMLHttpRequest();
    	var self = this;
    	
    	if(deletefileonly) {
	    	requestObject.onreadystatechange = function(){
	    		if (requestObject.readyState == 4){
	    			self._onDeleteComplete(requestObject); 
	    		}
	    	};
    	}
		else {
			requestObject.onreadystatechange = function(){
	    		if (requestObject.readyState == 4){
	    			self._parentForm._onDeleteComplete(requestObject, self._ID); 
	    		}
	    	};
		}
    	
    	var params = {};
    	params['command'] = 'MarkDeletable';
    	
    	if ( this._steamDoc == null ) {
    		params['steamid'] = "noop"; //tell the Delete Command to do nothing
    	}
    	else {
    		params['steamid'] = this._steamDoc;
    	}
    	
    	var query = qq.obj2url(params, this._options.action);
    	
        requestObject.open("GET", query, true);
        requestObject.setRequestHeader("X-Requested-With", "XMLHttpRequest");
        requestObject.setRequestHeader("Content-Type", "application/octet-stream");
        
        requestObject.send(null);
    },
    /**
     * _onDeleteComplete()
     * 
     * eventhandler for when the XMLhttprequest is complete
     */
    _onDeleteComplete: function(requestObject){
    	
    	var response = {};
    	
    	if (requestObject.status == 200){
    		
    		//if (window.console) console.log('[deleteCommand] ' + requestObject.responseText);/////////////// LOG ACTION (DELETE)
              
            try {
                response = eval("(" + requestObject.responseText + ")");
            } catch(err){
                response = {};
            }              
        }
    	
    	if (response.success){
    		
    		//since file is deleted, remove entry from list
    		this._removeFromList();
    		
    		//allow further uploads after file was deleted
	    	this._button._enable();
	    	this._delbutton._disable();
	    	
	    	//remove sTeam ID of deleted file
	    	this._steamDoc = null;
	    	
	    	//allow uploads again since last upload was deleted successfully
            this._options.uploadallowed = 1;
            
            //register the action for history
            this._parentForm.dirtyFlag = true;
        }
    	else {
    		
    		if (response.error) {
    			
    			this._options.showMessage(response.error);
    		}
    	}
    },
    _formatSize: function(bytes){
        var i = -1;                                    
        do {
            bytes = bytes / 1024;
            i++;  
        } while (bytes > 99);
        
        return Math.max(bytes, 0.1).toFixed(1) + ['kB', 'MB', 'GB', 'TB', 'PB', 'EB'][i];
    },
    _error: function(code, fileName){
        var message = this._options.messages[code];        
        function r(name, replacement){ message = message.replace(name, replacement); }
         
        message = message.replace(/FILE/, this._formatFileName(fileName));
        message = message.replace(/EXTENSIONS/, this._options.allowedExtensions.join(', '));
        message = message.replace(/SIZELIMIT/, this._formatSize(this._options.sizeLimit));
        message = message.replace(/MINSIZELIMIT/, this._formatSize(this._options.minSizeLimit));
        
        this._options.showMessage(message);                
    },
    _validateFile: function(file){
        var name, size;
        
        if (file.value){
            // it is a file input            
            // get input value and remove path to normalize
            name = file.value.replace(/.*(\/|\\\)/, "");
        } else {
            // fix missing properties in Safari
            name = file.fileName != null ? file.fileName : file.name;
            size = file.fileSize != null ? file.fileSize : file.size;
        }
                    
        if (! this._isAllowedExtension(name)){            
            this._error('typeError', name);
            return false;
            
        } else if (size === 0){            
            this._error('emptyError', name);
            return false;
                                                     
        } else if (size && this._options.sizeLimit && size > this._options.sizeLimit){            
            this._error('sizeError', name);
            return false;
                        
        } else if (size && size < this._options.minSizeLimit){
            this._error('minSizeError', name);
            return false;            
        }
        
        return true;                
    },
    _setupDragDrop: function(){
        var self = this,
            dropArea = this._find(this._element, 'drop');                        

        var dz = new qq.UploadDropZone({
            element: dropArea,
            onEnter: function(e){
                qq.addClass(dropArea, self._classes.dropActive);
                e.stopPropagation();
            },
            onLeave: function(e){
                e.stopPropagation();
            },
            onLeaveNotDescendants: function(e){
                qq.removeClass(dropArea, self._classes.dropActive);  
            },
            onDrop: function(e){
                dropArea.style.display = 'none';
                qq.removeClass(dropArea, self._classes.dropActive);
                self._uploadFileList(e.dataTransfer.files);    
            }
        });
        qq.attach(document, 'dragenter', function(e){     
            if (!dz._isValidFileDrag(e)) return; 
            
            dropArea.style.display = 'none';            
        });                 
        qq.attach(document, 'dragleave', function(e){
            if (!dz._isValidFileDrag(e)) return;            
            
            var relatedTarget = document.elementFromPoint(e.clientX, e.clientY);
            // only fire when leaving document out
            if ( ! relatedTarget || relatedTarget.nodeName == "HTML"){               
                dropArea.style.display = 'none';                                            
            }
        });                
    },
    _onSubmit: function(id, fileName){
        qq.FileUploaderBasic.prototype._onSubmit.apply(this, arguments);
        
        //Do not allow uploads until file failed or deleted again
        this._options.uploadallowed = 0;
        
        this._addToList(id, fileName);
    },
    _onProgress: function(id, fileName, loaded, total){
        qq.FileUploaderBasic.prototype._onProgress.apply(this, arguments);
        
        var item = this._getItemByFileId(id);
        var size = this._find(item, 'size');
        size.style.display = 'inline';
        
        var text; 
        if (loaded != total){
            text = Math.round(loaded / total * 100) + '% from ' + this._formatSize(total);
        } else {                                   
            text = this._formatSize(total);
        }          
        
        qq.setText(size, text);         
    },
    _onComplete: function(id, fileName, result){
        qq.FileUploaderBasic.prototype._onComplete.apply(this, arguments);

        // mark completed
        var item = this._getItemByFileId(id);                
        qq.remove(this._find(item, 'cancel'));
        qq.remove(this._find(item, 'spinner'));
        this._find(item, 'ok').style.display = 'inline'; 
        
        if (result.success){
            qq.addClass(item, this._classes.success);
            
            //retrieve sTeam Id of new file from response and save
            this._steamDoc = result.steamid;
 
            //enables delete button after upload is completed successfully
            this._button._disable();
            this._delbutton._enable();
            this._options.uploadallowed = 0;
            
            //register the action for history
            this._parentForm.dirtyFlag = true;
        } 
        else {
            qq.addClass(item, this._classes.fail);

            //allow uploads again since last upload failed
            this._options.uploadallowed = 1;
        }         
    },
    /**
     * _removeFormList()
     * 
     * removes the first File entry from the upload list which is enough
     * since we limit the uploads to max. one at a time
     */
    _removeFromList: function(){
    	
    	if (this._listElement.hasChildNodes()) {
    		
    		this._listElement.removeChild(this._listElement.firstChild);
    	}
    },
    _addToList: function(id, fileName){
        var item = qq.toElement(this._options.fileTemplate);                
        item.qqFileId = id;

        var fileElement = this._find(item, 'file');        
        qq.setText(fileElement, this._formatFileName(fileName));
        this._find(item, 'size').style.display = 'none';        

        this._listElement.appendChild(item);
    },
    _getItemByFileId: function(id){
        var item = this._listElement.firstChild;        
        
        // there can't be txt nodes in dynamically created list
        // and we can  use nextSibling
        while (item){            
            if (item.qqFileId == id) return item;            
            item = item.nextSibling;
        }          
    },
    /**
     * delegate click event for cancel link 
     **/
    _bindCancelEvent: function(){
        var self = this,
            list = this._listElement;            
        
        qq.attach(list, 'click', function(e){            
            e = e || window.event;
            var target = e.target || e.srcElement;
            
            if (qq.hasClass(target, self._classes.cancel)){                
                qq.preventDefault(e);
               
                var item = target.parentNode;
                self._handler.cancel(item.qqFileId);
                qq.remove(item);
            }
        });
    }    
});


/**
 * --> KLASSENDEKLARATION qq.UploadDropZone
 */
qq.UploadDropZone = function(o){
    this._options = {
        element: null,  
        onEnter: function(e){},
        onLeave: function(e){},  
        // is not fired when leaving element by hovering descendants   
        onLeaveNotDescendants: function(e){},   
        onDrop: function(e){}                       
    };
    qq.extend(this._options, o); 
    
    this._element = this._options.element;
    
    this._disableDropOutside();
    this._attachEvents();   
};

/**
 * --> (KLASSENDEKLARATION) prototype, weitere Funktionen
 */
qq.UploadDropZone.prototype = {
    _disableDropOutside: function(e){
        // run only once for all instances
        if (!qq.UploadDropZone.dropOutsideDisabled ){

            qq.attach(document, 'dragover', function(e){
                if (e.dataTransfer){
                    e.dataTransfer.dropEffect = 'none';
                    e.preventDefault(); 
                }           
            });
            
            qq.UploadDropZone.dropOutsideDisabled = true; 
        }        
    },
    _attachEvents: function(){
        var self = this;              
                  
        qq.attach(self._element, 'dragover', function(e){
            if (!self._isValidFileDrag(e)) return;
            
            var effect = e.dataTransfer.effectAllowed;
            if (effect == 'move' || effect == 'linkMove'){
                e.dataTransfer.dropEffect = 'move'; // for FF (only move allowed)    
            } else {                    
                e.dataTransfer.dropEffect = 'copy'; // for Chrome
            }
                                                     
            e.stopPropagation();
            e.preventDefault();                                                                    
        });
        
        qq.attach(self._element, 'dragenter', function(e){
            if (!self._isValidFileDrag(e)) return;
                        
            self._options.onEnter(e);
        });
        
        qq.attach(self._element, 'dragleave', function(e){
            if (!self._isValidFileDrag(e)) return;
            
            self._options.onLeave(e);
            
            var relatedTarget = document.elementFromPoint(e.clientX, e.clientY);                      
            // do not fire when moving a mouse over a descendant
            if (qq.contains(this, relatedTarget)) return;
                        
            self._options.onLeaveNotDescendants(e); 
        });
                
        qq.attach(self._element, 'drop', function(e){
            if (!self._isValidFileDrag(e)) return;
            
            e.preventDefault();
            self._options.onDrop(e);
        });          
    },
    _isValidFileDrag: function(e){
        var dt = e.dataTransfer,
            // do not check dt.types.contains in webkit, because it crashes safari 4            
            isWebkit = navigator.userAgent.indexOf("AppleWebKit") > -1;                        

        // dt.effectAllowed is none in Safari 5
        // dt.types.contains check is for firefox            
        return dt && dt.effectAllowed != 'none' && 
            (dt.files || (!isWebkit && dt.types.contains && dt.types.contains('Files')));
        
    }        
}; 






/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/**
 * @TODO::  Merge Buttons into one class, the existing exerciseFormButton should be suitable
 */      
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////





/**
 * --> KLASSENDEKLARATION qq.UploadButton
 */
qq.UploadButton = function(o){
    this._options = {
        element: null,  
        // if set to true adds multiple attribute to file input      
        multiple: false,
        // name attribute of file input
        name: 'file',
        onChange: function(input){},
        hoverClass: 'qq-upload-button-hover',
        focusClass: 'qq-upload-button-focus',
        disableCls: 'qq-upload-button-disabled'
    };
    
    qq.extend(this._options, o);
        
    this._element = this._options.element;
    
    // make button suitable container for input
    qq.css(this._element, {
        position: 'relative',
        overflow: 'hidden',
        // Make sure browse button is in the right side
        // in Internet Explorer
        direction: 'ltr'
    });   
    
    this._input = this._createInput();
};

/**
 * --> (KLASSENDEKLARATION) prototype, weitere Funktionen
 */
qq.UploadButton.prototype = {
    /* returns file input element */    
    getInput: function(){
        return this._input;
    },
    /* cleans/recreates the file input */
    reset: function(){
        if (this._input.parentNode){
            qq.remove(this._input);    
        }                
        
        qq.removeClass(this._element, this._options.focusClass);
        this._input = this._createInput();
    }, 
    /**
     * _disable() function
     * 
     * Removes the <input> tag and its handlers so that no further
     * uploads can be made
     */
    _disable: function(){
    	
    	// removes/adds CSS classes
    	qq.removeClass(this._element, this._options.hoverClass);
    	qq.removeClass(this._element, this._options.focusClass);
    	qq.addClass(this._element, this._options.disableCls);
    	
    	// removes the <input> tag
    	this._element.removeChild(this._input);
    },
    _enable: function(){
    	
    	// resets the CSS style
    	qq.removeClass(this._element, this._options.disableCls);
    	
    	// recreates <input> tag
    	this._input = this._createInput();
    },
    _createInput: function(){                
        var input = document.createElement("input");
        
        if (this._options.multiple){
            input.setAttribute("multiple", "multiple");
        }
                
        input.setAttribute("type", "file");
        input.setAttribute("name", this._options.name);
        
        qq.css(input, {
            position: 'absolute',
            // in Opera only 'browse' button
            // is clickable and it is located at
            // the right side of the input
            right: 0,
            top: 0,
            fontFamily: 'Arial',
            // 4 persons reported this, the max values that worked for them were 243, 236, 236, 118
            fontSize: '118px',
            margin: 0,
            padding: 0,
            cursor: 'pointer',
            opacity: 0
        });
        
        this._element.appendChild(input);

        var self = this;
        qq.attach(input, 'change', function(){ ///////////////////////////////////////////////////////
            self._options.onChange(input);
        });
                
        qq.attach(input, 'mouseover', function(){
            qq.addClass(self._element, self._options.hoverClass);
        });
        qq.attach(input, 'mouseout', function(){
            qq.removeClass(self._element, self._options.hoverClass);
        });
        qq.attach(input, 'focus', function(){
            qq.addClass(self._element, self._options.focusClass);
        });
        qq.attach(input, 'blur', function(){
            qq.removeClass(self._element, self._options.focusClass);
        });

        // IE and Opera, unfortunately have 2 tab stops on file input
        // which is unacceptable in our case, disable keyboard access
        if (window.attachEvent){
            // it is IE or Opera
            input.setAttribute('tabIndex', "-1");
        }

        return input;            
    }        
};



/**
 * --> KLASSENDEKLARATION qq.DeleteButton
 */
qq.DeleteButton = function(o){
    this._options = {
        element: null,  
        // name attribute of file input
        name: 'delete',
        onChange: function(input){},
        hoverClass: 'qq-delete-button-hover',
        focusClass: 'qq-delete-button-focus',
        disableCls: 'qq-delete-button-disabled'
    };
    
    qq.extend(this._options, o);
        
    this._element = this._options.element;
    
    // make button suitable container for input
    qq.css(this._element, {
        position: 'relative',
        overflow: 'hidden',
        // Make sure browse button is in the right side
        // in Internet Explorer
        direction: 'ltr'
    });   
    
    this._input = this._createInput();
};

/**
 * --> (KLASSENDEKLARATION) prototype, weitere Funktionen
 */
qq.DeleteButton.prototype = {
    /* returns file input element */    
    getInput: function(){
        return this._input;
    },
    /* cleans/recreates the file input */
    reset: function(){
        if (this._input.parentNode){
            qq.remove(this._input);    
        }                
        
        qq.removeClass(this._element, this._options.focusClass);
        this._input = this._createInput();
    }, 
    /**
     * _disable() function
     * 
     * Removes the <input> tag and its handlers so that no further
     * uploads can be made
     */
    _disable: function(){
    	
    	// removes/adds CSS classes
    	qq.removeClass(this._element, this._options.hoverClass);
    	qq.removeClass(this._element, this._options.focusClass);
    	qq.addClass(this._element, this._options.disableCls);
    	
    	// removes the <input> tag
    	this._element.removeChild(this._input);
    },
    _enable: function(){
    	
    	// resets the CSS style
    	qq.removeClass(this._element, this._options.disableCls);
    	
    	// recreates <input> tag
    	this._input = this._createInput();
    },
    _createInput: function(){                
        var input = document.createElement("input");
        
        input.setAttribute("type", "submit");
        input.setAttribute("name", this._options.name);
        
        qq.css(input, {
            position: 'absolute',
            // in Opera only 'browse' button
            // is clickable and it is located at
            // the right side of the input
            right: 0,
            top: 0,
            fontFamily: 'Arial',
            // 4 persons reported this, the max values that worked for them were 243, 236, 236, 118
            fontSize: '118px',
            margin: 0,
            padding: 0,
            cursor: 'pointer',
            opacity: 0
        });
        
        this._element.appendChild(input);

        var self = this;
        /**
         * Adds an EventHandler to the Button for the (on)Click event
         */
        qq.attach(input, 'click', function(){ ///////////////////////////////////////////////////////################################
            self._options.onChange(input);
        });
                
        qq.attach(input, 'mouseover', function(){
            qq.addClass(self._element, self._options.hoverClass);
        });
        qq.attach(input, 'mouseout', function(){
            qq.removeClass(self._element, self._options.hoverClass);
        });
        qq.attach(input, 'focus', function(){
            qq.addClass(self._element, self._options.focusClass);
        });
        qq.attach(input, 'blur', function(){
            qq.removeClass(self._element, self._options.focusClass);
        });

        // IE and Opera, unfortunately have 2 tab stops on file input
        // which is unacceptable in our case, disable keyboard access
        if (window.attachEvent){
            // it is IE or Opera
            input.setAttribute('tabIndex', "-1");
        }

        return input;            
    }        
};


/**
 * Class for uploading files, uploading itself is handled by child classes
 * 
 * --> KLASSENDEKLARATION qq.UploadHandlerAbstract
 */
qq.UploadHandlerAbstract = function(o){
    this._options = {
        debug: false,
        action: '/upload.php',
        baseroom: 42,
        trackfiles: null,
        solutionid: null,
        // maximum number of concurrent uploads        
        maxConnections: 999,
        onProgress: function(id, fileName, loaded, total){},
        onComplete: function(id, fileName, response){},
        onCancel: function(id, fileName){}
    };
    qq.extend(this._options, o);    
    
    this._queue = [];
    // params for files in queue
    this._params = [];
};


/**
 * --> (KLASSENDEKLARATION) prototype, weitere FUnktionen
 */
qq.UploadHandlerAbstract.prototype = {
    log: function(str){
        if (this._options.debug && window.console) console.log('[uploader] ' + str);        
    },
    /**
     * Adds file or file input to the queue
     * @returns id
     **/    
    add: function(file){},
    /**
     * Sends the file identified by id and additional query params to the server
     */
    upload: function(id, params){
        var len = this._queue.push(id);

        var copy = {};        
        qq.extend(copy, params);
        this._params[id] = copy;        
                
        // if too many active uploads, wait...
        if (len <= this._options.maxConnections){               
            this._upload(id, this._params[id]);
        }
    },
    /**
     * Cancels file upload by id
     */
    cancel: function(id){
        this._cancel(id);
        this._dequeue(id);
    },
    /**
     * Cancells all uploads
     */
    cancelAll: function(){
        for (var i=0; i<this._queue.length; i++){
            this._cancel(this._queue[i]);
        }
        this._queue = [];
    },
    /**
     * Returns name of the file identified by id
     */
    getName: function(id){},
    /**
     * Returns size of the file identified by id
     */          
    getSize: function(id){},
    /**
     * Returns id of files being uploaded or
     * waiting for their turn
     */
    getQueue: function(){
        return this._queue;
    },
    /**
     * Actual upload method
     */
    _upload: function(id){},
    /**
     * Actual cancel method
     */
    _cancel: function(id){},     
    /**
     * Removes element from queue, starts upload of next
     */
    _dequeue: function(id){
        var i = qq.indexOf(this._queue, id);
        this._queue.splice(i, 1);
                
        var max = this._options.maxConnections;
        
        if (this._queue.length >= max && i < max){
            var nextId = this._queue[max-1];
            this._upload(nextId, this._params[nextId]);
        }
    }        
};


/**
 * Class for uploading files using form and iframe
 * @inherits qq.UploadHandlerAbstract
 * 
 * --> KLASSENDEKLARATION qq.UploadHandlerForm
 */
qq.UploadHandlerForm = function(o){
    qq.UploadHandlerAbstract.apply(this, arguments);
       
    this._inputs = {};
};

/**
 * --> (KLASSENDEKLARATION) prototype, weitere Funktionen
 */
// @inherits qq.UploadHandlerAbstract
qq.extend(qq.UploadHandlerForm.prototype, qq.UploadHandlerAbstract.prototype);

qq.extend(qq.UploadHandlerForm.prototype, {
    add: function(fileInput){
        fileInput.setAttribute('name', 'qqfile');
        var id = 'qq-upload-handler-iframe' + qq.getUniqueId();       
        
        this._inputs[id] = fileInput;
        
        // remove file input from DOM
        if (fileInput.parentNode){
            qq.remove(fileInput);
        }
                
        return id;
    },
    getName: function(id){
        // get input value and remove path to normalize
        return this._inputs[id].value.replace(/.*(\/|\\\)/, "");
    },    
    _cancel: function(id){
        this._options.onCancel(id, this.getName(id));
        
        delete this._inputs[id];        

        var iframe = document.getElementById(id);
        if (iframe){
            // to cancel request set src to something else
            // we use src="javascript:false;" because it doesn't
            // trigger ie6 prompt on https
            iframe.setAttribute('src', 'javascript:false;');

            qq.remove(iframe);
        }
    },     
    _upload: function(id, params){                        
        var input = this._inputs[id];
        
        if (!input){
            throw new Error('file with passed id was not added, or already uploaded or cancelled');
        }                

        var fileName = this.getName(id);
                
        var iframe = this._createIframe(id);
        var form = this._createForm(iframe, params);
        form.appendChild(input);

        var self = this;
        this._attachLoadEvent(iframe, function(){                                 
            self.log('iframe loaded');
            
            var response = self._getIframeContentJSON(iframe);

            self._options.onComplete(id, fileName, response);
            self._dequeue(id);
            
            delete self._inputs[id];
            // timeout added to fix busy state in FF3.6
            setTimeout(function(){
                qq.remove(iframe);
            }, 1);
        });

        form.submit();        
        qq.remove(form);        
        
        return id;
    }, 
    _attachLoadEvent: function(iframe, callback){
        qq.attach(iframe, 'load', function(){
            // when we remove iframe from dom
            // the request stops, but in IE load
            // event fires
            if (!iframe.parentNode){
                return;
            }

            // fixing Opera 10.53
            if (iframe.contentDocument &&
                iframe.contentDocument.body &&
                iframe.contentDocument.body.innerHTML == "false"){
                // In Opera event is fired second time
                // when body.innerHTML changed from false
                // to server response approx. after 1 sec
                // when we upload file with iframe
                return;
            }

            callback();
        });
    },
    /**
     * Returns json object received by iframe from server.
     */
    _getIframeContentJSON: function(iframe){
        // iframe.contentWindow.document - for IE<7
        var doc = iframe.contentDocument ? iframe.contentDocument: iframe.contentWindow.document,
            response;
        
        this.log("converting iframe's innerHTML to JSON");
        this.log("innerHTML = " + doc.body.innerHTML);
                        
        try {
            response = eval("(" + doc.body.innerHTML + ")");
        } catch(err){
            response = {};
        }        

        return response;
    },
    /**
     * Creates iframe with unique name
     */
    _createIframe: function(id){
        // We can't use following code as the name attribute
        // won't be properly registered in IE6, and new window
        // on form submit will open
        // var iframe = document.createElement('iframe');
        // iframe.setAttribute('name', id);

        var iframe = qq.toElement('<iframe src="javascript:false;" name="' + id + '" />');
        // src="javascript:false;" removes ie6 prompt on https

        iframe.setAttribute('id', id);

        iframe.style.display = 'none';
        document.body.appendChild(iframe);

        return iframe;
    },
    /**
     * Creates form, that will be submitted to iframe
     */
    _createForm: function(iframe, params){
        // We can't use the following code in IE6
        // var form = document.createElement('form');
        // form.setAttribute('method', 'post');
        // form.setAttribute('enctype', 'multipart/form-data');
        // Because in this case file won't be attached to request
        var form = qq.toElement('<form method="post" enctype="multipart/form-data"></form>');

        var queryString = qq.obj2url(params, this._options.action);

        form.setAttribute('action', queryString);
        form.setAttribute('target', iframe.name);
        form.style.display = 'none';
        document.body.appendChild(form);

        return form;
    }
});


/**
 * Class for uploading files using xhr
 * @inherits qq.UploadHandlerAbstract
 * 
 * --> KLASSENDEKLARATION qq.UploadHandlerXhr
 */
qq.UploadHandlerXhr = function(o){
    qq.UploadHandlerAbstract.apply(this, arguments);

    this._files = [];
    this._xhrs = [];
    
    // current loaded size in bytes for each file 
    this._loaded = [];
};

// static method
qq.UploadHandlerXhr.isSupported = function(){
    var input = document.createElement('input');
    input.type = 'file';        
    
    return (
        'multiple' in input &&
        typeof File != "undefined" &&
        typeof (new XMLHttpRequest()).upload != "undefined" );       
};



/**
 * --> (KLASSENDEKLARATION) weitere Funktionen
 */
// @inherits qq.UploadHandlerAbstract
qq.extend(qq.UploadHandlerXhr.prototype, qq.UploadHandlerAbstract.prototype)

qq.extend(qq.UploadHandlerXhr.prototype, {
    /**
     * Adds file to the queue
     * Returns id to use with upload, cancel
     **/    
    add: function(file){
        if (!(file instanceof File)){
            throw new Error('Passed obj in not a File (in qq.UploadHandlerXhr)');
        }
                
        return this._files.push(file) - 1;        
    },
    getName: function(id){        
        var file = this._files[id];
        // fix missing name in Safari 4
        return file.fileName != null ? file.fileName : file.name;       
    },
    getSize: function(id){
        var file = this._files[id];
        return file.fileSize != null ? file.fileSize : file.size;
    },    
    /**
     * Returns uploaded bytes for file identified by id 
     */    
    getLoaded: function(id){
        return this._loaded[id] || 0; 
    },
    /**
     * Sends the file identified by id and additional query params to the server
     * @param {Object} params name-value string pairs
     */    
    _upload: function(id, params){
        var file = this._files[id],
            name = this.getName(id),
            size = this.getSize(id);
                
        this._loaded[id] = 0;
                                
        var xhr = this._xhrs[id] = new XMLHttpRequest();
        var self = this;
                                        
        xhr.upload.onprogress = function(e){
            if (e.lengthComputable){
                self._loaded[id] = e.loaded;
                self._options.onProgress(id, name, e.loaded, e.total);
            }
        };

        xhr.onreadystatechange = function(){            
            if (xhr.readyState == 4){
                self._onComplete(id, xhr);                    
            }
        };

        // build query string
        params = params || {};
        params['qqfile'] = name;
        params['command'] = 'Upload';
        params['baseroom'] = this._options.baseroom;
        if(this._options.trackfiles) {
        	params['trackmyfile'] = this._options.solutionid;
        }
        console.log(this._options.trackfiles);
        console.log(this._options.solutionid);
        var queryString = qq.obj2url(params, this._options.action);

        xhr.open("POST", queryString, true);
        xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
        xhr.setRequestHeader("X-File-Name", encodeURIComponent(name));
        xhr.setRequestHeader("Content-Type", "application/octet-stream");
        xhr.send(file);
    },
    _onComplete: function(id, xhr){
        // the request was aborted/cancelled
        if (!this._files[id]) return;
        
        var name = this.getName(id);
        var size = this.getSize(id);
        
        this._options.onProgress(id, name, size, size);
         
        if (xhr.status == 200){
            this.log("xhr - server response received");
            this.log("responseText = " + xhr.responseText);
                        
            var response;
                    
            try {
                response = eval("(" + xhr.responseText + ")");
            } catch(err){
                response = {};
            }
            
            this._options.onComplete(id, name, response);  // bei erstellung des obj. auf die onComplete fkt. in qq.FileUploader gesetzt worden
                        
        } else {                   
            this._options.onComplete(id, name, {});
        }
                
        this._files[id] = null;
        this._xhrs[id] = null;    
        this._dequeue(id);                    
    },
    _cancel: function(id){
        this._options.onCancel(id, this.getName(id));
        
        this._files[id] = null;
        
        if (this._xhrs[id]){
            this._xhrs[id].abort();
            this._xhrs[id] = null;                                   
        }
    }
});