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

//jquery sortable code
/*$(function() {
        $(".column").sortable({
                connectWith: ".column",
                items:".portlet",
                placeholder: "ui-state-highlight",
                update: function(event, ui) { alert("jetzt sollte persistiert werden..."); }
                //idee: 
                //per javascript nach der reihenfolge der portlets suchten
                //start bei div mit class=portal
                //darunter folgen die 3 spalten
                //und in jeder spalte sind die portlets
                //die div-id eines portlets in der oberfläche ist die objekt-id des objektes
                //aus der mit der ermittelten reihenfolge einen (noch fehlenden) php-command aufrufen, der die sortierung durchführt
                
        });
        
        $(".column").disableSelection();
});*/

