function widgets_listViewer_hover_toggle(elementId, isHovered) {
	if (isHovered) {
		jQuery('#' + elementId).addClass('listviewer-item-hovered').removeClass('listviewer-item-unhovered');
	} else {
		jQuery('#' + elementId).removeClass('listviewer-item-hovered').addClass('listviewer-item-unhovered');
	}
}

function widgets_listViewer_selection_toggle(elementId, isSelected) {
	if (isSelected) {
		jQuery('#' + elementId).addClass('listviewer-item-selected').addClass('listviewer-item-hover');
	} else {
		jQuery('#' + elementId).removeClass('listviewer-item-selected').removeClass('listviewer-item-hover');
	}
}

var nameAscending = true;
//sort listitems by name
function sortByName(headitem) {

	var items = $('.listviewer-item');
	items.sort(function(a, b){
		var name1 = jQuery(a).children().eq(1).text();
		var name2 = jQuery(b).children().eq(1).text();
		if(nameAscending){
			return name1.localeCompare(name2);
		}
		else{
			return name2.localeCompare(name1);
		}
	})
        
	var indices = new Array();
	for(var i = 0; i<items.length; i++){
		indices.push(items[i].id);
	}
        
	var obj = {
		id: indices[0],
		direction: "",
		indices: indices
	}
        
	resetListViewerHeadItem();

	if(nameAscending){
		nameAscending = false;
		jQuery(headitem).text("Name ▲");
	}
	else{
		nameAscending = true;
		jQuery(headitem).text("Name ▼");
	}

	jQuery(".listviewer-item").remove();
	jQuery(items).insertAfter(".listviewer-head");

	sendRequest("Order", obj, "", "nonModalUpdater", null, null, "explorer");
}

var dateAscending = true;
//sort listitems by change date
function sortByDate(headitem) {

	var items = $('.listviewer-item');
	items.sort(function(a, b){
		var dateString1 = jQuery(a).children().eq(5).text();
		var dateString2 = jQuery(b).children().eq(5).text();
		var date1 = stringToDate(dateString1);
		var date2 = stringToDate(dateString2);

		if(dateAscending){
			return date2.getTime() - date1.getTime();
		}
		else{
			return date1.getTime() - date2.getTime();
		}
	})

	var indices = new Array();
	for(var i = 0; i<items.length; i++){
		indices.push(items[i].id);
	}

	var obj = {
		id: indices[0],
		direction: "",
		indices: indices
	}

	resetListViewerHeadItem();

	if(dateAscending){
		dateAscending = false;
		jQuery(headitem).text("Änderungsdatum ▼");
	}
	else{
		dateAscending = true;
		jQuery(headitem).text("Änderungsdatum ▲");
	}

	jQuery(".listviewer-item").remove();
	jQuery(items).insertAfter(".listviewer-head");

	sendRequest("Order", obj, "", "nonModalUpdater", null, null, "explorer");
}

//converts datestring into date
//dateString must be something like 12.07.2016, 16:29 Uhr or heute um 14:15 Uhr
function stringToDate(dateString){
	var short = dateString.substring(0, dateString.length - 4);
	var minutes = short.substring(short.length - 2, short.length);
	var hours = short.substring(short.length - 5, short.length-3);
	if(short.startsWith("heute")){
		var year = new Date().getFullYear();
		var month = ("0" + new Date().getMonth()).slice(-2);
		var day = new Date().getDate();
	}
	else{
		var year = short.substring(short.length - 11, short.length-7);
		var month = parseInt(short.substring(short.length - 14, short.length-12))-1;
		var day = short.substring(0, short.length-15);
	}

	return new Date(year, month, day, hours, minutes, "00", "00");
}

//removes the triangles from the listviewer-head-items
function resetListViewerHeadItem(){
	jQuery(".listviewer-head").children().eq(1).text("Name");
	jQuery(".listviewer-head").children().eq(5).text("Änderungsdatum");
}
