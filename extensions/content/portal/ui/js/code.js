function portalLockButton(id){
	editButtons = document.getElementsByClassName("editbutton");
	for (var i = 0; i < editButtons.length; i++) {
		if (editButtons[i].style.display=="block"){
			editButtons[i].style.display = "none";
			createCookie("portalEditMode", "0", 1);
		}else{
			editButtons[i].style.display = "block";
			createCookie("portalEditMode", id, 1/24);
		}
	}
}
