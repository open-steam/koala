function portalLockButton(id){
	editButtons = document.getElementsByClassName("editbutton");
	for (var i = 0; i < editButtons.length; i++) {
		if (editButtons[i].style.display=="block"){
			editButtons[i].style.display = "none";
			jQuery("#edit_icon").parent().css("background-color", "#3a6e9f");
			createCookie("portalEditMode", "0", 1);
		}else{
			editButtons[i].style.display = "block";
			jQuery("#edit_icon").parent().css("background-color", "#ff8300");
			createCookie("portalEditMode", id, 1/24);
		}
	}
}
