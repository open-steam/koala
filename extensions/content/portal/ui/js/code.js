function portalLockButton(id){
        hideAllMenus(); //if the page viewed on a narrow screen then the burger menu is displayed. Hide this to use the Bearbeiten function
	editButtons = document.getElementsByClassName("editbutton");
	for (var i = 0; i < editButtons.length; i++) {
		if (editButtons[i].style.display=="block"){
			editButtons[i].style.display = "none";
			jQuery("#edit_icon").parent().css("background-color", "");
			createCookie("portalEditMode", "0", 1);
		}else{
			editButtons[i].style.display = "block";
			jQuery("#edit_icon").parent().css("background-color", "#ff8300");
			createCookie("portalEditMode", id, 1/24);
		}
	}
}
