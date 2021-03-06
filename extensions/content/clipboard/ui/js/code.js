jQuery(document).ready(function(){
	jQuery(document).keypress(function(e){
		if (e.keyCode == 113) { //F2
			var element = jQuery(".listviewer-item-hover");
			if (element.length > 0) {
				item = jQuery("#" + element.attr('id') + "_1");

				if (!item.hasClass("directEditor")) {
					removeAllDirectEditors();

					item.addClass("directEditor").html("");

					var obj = new Object;
					obj.id = element.attr('id');

					sendRequest("GetDirectEditor", obj, item.attr('id'), "nonModalUpdater", null, null, "explorer");
				} else {
					removeAllDirectEditors();
				}
			}
		}
	});	
});

function removeAllDirectEditors(save) {
	if(save){
		//define the dataSaveFunctionCallback to make the contentProvider happy
		jQuery.globalEval("function dataSaveFunctionCallback(response){return true;}");
		$('.changed').each(function(number, obj) {
			eval($(obj).attr('data-saveFunction'));
			$(obj).removeClass("changed");
		});
	}

	jQuery(document).keyup(function(e) {});

	var elements = jQuery(".directEditor");
	if (elements) {
		for(i=0; i<elements.length; i++) {
			var item = elements[i];
			var parent = jQuery(item).parent()[0];
			removeDirectEditor(parent.id, item.id);
		}
	}
}

function removeDirectEditor(objectId, elementId) {
	jQuery("#" + elementId).removeClass("directEditor").html("");

  //get the new name
	var obj = new Object;
	obj.id = objectId;
	sendRequest("GetLabel", obj, elementId, "nonModalUpdater",null,null,"explorer");
	sendRequest("GetChangeDate", obj, obj.id + "_5", "nonModalUpdater",null,null,"explorer");
	if($('.listviewer').length != 0) resetListViewerHeadItem();
}

function getSelectionAsArray() {
	var result = new Array();
	$(".listviewer-item-selected").each(function() {result.push(this.id);});
	return result;
}

function getGallerySelectionAsArray() {
	var result = new Array();
	$(".galleryEntry.selected").each(function() {result.push(this.id);});
	return result;
}

function getSelectionAsJSON() {
	return $.toJSON(getSelectionAsArray());
}

function getGallerySelectionAsJSON() {
	return $.toJSON(getGallerySelectionAsArray());
}

function getParamsArray(paramsObject) {
	if (!paramsObject) {
		paramsObject = {};
	}
	var ids = getSelectionAsArray();
	var paramsArray = new Array();
	for (i = 0; i < ids.length; i++) {
		var po = clone(paramsObject);
		po.id = ids[i];
		paramsArray.push(po);
	}
	return paramsArray;
}

function getGalleryParamsArray(paramsObject) {
	if (!paramsObject) {
		paramsObject = {};
	}
	var ids = getGallerySelectionAsArray();
	var paramsArray = new Array();
	for (i = 0; i < ids.length; i++) {
		var po = clone(paramsObject);
		po.id = ids[i];
		paramsArray.push(po);
	}
	return paramsArray;
}

function getElementIdArray(elementId) {
	var elementIdArray = new Array();
	var ids = getSelectionAsArray();
	for (i = 0; i < ids.length; i++) {
		elementIdArray.push(elementId);
	}
	return elementIdArray;
}

function getGalleryElementIdArray(elementId) {
	var elementIdArray = new Array();
	var ids = getGallerySelectionAsArray();
	for (i = 0; i < ids.length; i++) {
		elementIdArray.push(elementId);
	}
	return elementIdArray;
}

function clone(obj) {
    // Handle the 3 simple types, and null or undefined
    if (null == obj || "object" != typeof obj) return obj;

    // Handle Date
    if (obj instanceof Date) {
        var copy = new Date();
        copy.setTime(obj.getTime());
        return copy;
    }

    // Handle Array
    if (obj instanceof Array) {
        var copy = [];
        for (var i = 0; i < obj.length; i++) {
            copy[i] = clone(obj[i]);
        }
        return copy;
    }

    // Handle Object
    if (obj instanceof Object) {
        var copy = {};
        for (var attr in obj) {
            if (obj.hasOwnProperty(attr)) copy[attr] = clone(obj[attr]);
        }
        return copy;
    }

    throw new Error("Unable to copy obj! Its type isn't supported.");
}
