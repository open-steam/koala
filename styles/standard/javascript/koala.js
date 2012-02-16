//js for menu
function mark_parent(element,yesOrNo) {
	  var parent = element.parentNode.parentNode;
	  if (parent.tagName.toLowerCase()!=='li') return;
	  //parent=parent.parentNode.parentNode;
	  if (yesOrNo){
	     parent.className='activemenu';
	  } else {
		  parent.className='';
	  }
  }
var alreadyClicked=false;
function menu_clicked(element) {
	if (alreadyClicked) return;

	var links=element.getElementsByTagName('a');

	if (links.length==0) return;

	alreadyClicked=true;
	location.href=links[0].href;
}

//Effect.Appear('infoBar');

/*to fade in the "infoBar" with jquery js lib
$(document).ready(function(){
	$("div.infoBar").fadeIn("slow");
});*/