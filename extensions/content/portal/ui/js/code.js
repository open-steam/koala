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


function bid2PathFix() {
    input = 'https://steam.lspb.de';
    output='http://bid.lspb.de';
    
    while(document.body.innerHTML.search(input)!=-1){
        document.body.innerHTML = document.body.innerHTML.replace(input,output);
    }
    
    input = 'http://steam.lspb.de';
    output='http://bid.lspb.de';
    
    while(document.body.innerHTML.search(input)!=-1){
        document.body.innerHTML = document.body.innerHTML.replace(input,output);
    }
    
    
    return true;
}

function proofLoaded() {
   if (document.readyState == "complete") {
      bid2PathFix();
   } else {
      setTimeout('proofLoaded()',500);
   }
}

setTimeout('proofLoaded()',500);