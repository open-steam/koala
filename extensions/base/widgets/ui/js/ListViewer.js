function widgets_listViewer_hover_toggle(elementId, isHovered) {
	if (isHovered) {
		jQuery('#' + elementId).addClass('listviewer-item-hover').removeClass('listviewer-item-unhover');
	} else {
		jQuery('#' + elementId).removeClass('listviewer-item-hover').addClass('listviewer-item-unhover');
	}
}

function widgets_listViewer_selection_toggle(elementId, isSelected) {
	if (isSelected) {
		jQuery('#' + elementId).addClass('listviewer-item-selected');
	} else {
		jQuery('#' + elementId).removeClass('listviewer-item-selected');
	}
}