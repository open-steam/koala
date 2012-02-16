var eoPath = document.URL.slice(0,19 + document.URL.search("/exam_organization/"));

function emCheckbox(box){
	boxIdString = box.id;
	examTerm = boxIdString.charAt(4);
	workCase = boxIdString.slice(5);
	
	if(box.checked===true && workCase=="showdatetime"){
		ajaxRequest(examTerm,"visibleStatusDateTime",1);
	} 
	
	if(box.checked===false && workCase=="showdatetime"){
		ajaxRequest(examTerm,"visibleStatusDateTime",0);
	}

	if(box.checked===true && workCase=="showroom"){
		ajaxRequest(examTerm,"visibleStatusRooms",1);
	} 
	
	if(box.checked===false && workCase=="showroom"){
		ajaxRequest(examTerm,"visibleStatusRooms",0);
	}
}

function ajaxRequest(term,method,value){
	apath = eoPath+"directaccess/?case="+ method +"&value="+ value +"&examterm="+term;
	new Ajax.Request(apath,
			  {
			    method:'get',
			    onFailure: function(){ alert('Error while saving status') }
			  });
}

function ajaxConfirmDeleteExamData(term,link)
{
	apath = eoPath+"directaccess/?case=message_delete_exam_data&value=1&examterm="+term;
	var result=false;
	new Ajax.Request(apath,
	  {
	    method:'get',
	    options: { asynchronous: false },
	    onFailure: function(){ alert('Error while deleting data'); return false; },
	    onComplete: function(response){result=confirm(response.responseText); if(result){location.href=link;}}
	  });
	  return false;
	}

function doConfirm(term)
{
	return confirm("Wollen Sie die Pr√ºfungsdaten f√ºr den " + term + ". Termin wirklich l√∂schen?");
}