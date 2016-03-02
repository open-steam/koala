var visible_gallery_id = 0;

function activate_gallery(gallery_id)
{
    if (visible_gallery_id == gallery_id)
        return

    if (visible_gallery_id != 0)
    {
        document.getElementById(visible_gallery_id).className = 'gallery';
    }

    gallery = document.getElementById(gallery_id)
    if (gallery) {
        visible_gallery_id = gallery_id;
        gallery.className = 'gallery_visible';
    }
}

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

                    sendRequest("GetDirectEditor", obj, item.attr('id'), "updater", null, null, "explorer");
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
	sendRequest("GetLabel", obj, elementId, "updater",null,null,"explorer");
}

function getSelectionAsArray() {
    var result = new Array();
    $(".listviewer-item-selected").each(function() {
        result.push(this.id);
    });
    return result;
}

function getSelectionAsJSON() {
    return $.toJSON(getSelectionAsArray());
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
    console.log(paramsArray);
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
