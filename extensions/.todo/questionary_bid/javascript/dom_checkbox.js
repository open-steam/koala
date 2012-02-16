// JavaScript Document
edit_multiple_check=0;

//insert a new row and deletes the "norow" row
function insertElement(name)
{
	if(name!="")
	{
		id=getnewID();
		
		elementtable = document.getElementById("options");
		tr = document.createElement("tr");
		tr.setAttribute("id", "tr_"+id);
		tr.style.backgroundColor="#D8D8D8";
		
		td = document.createElement("td");
		td.setAttribute("id", "td1_"+id);
		td.appendChild(document.createTextNode(name));
		input = document.createElement("INPUT")
		input.setAttribute("type", "hidden");
		input.setAttribute("value", name);
		input.setAttribute("name", "options_"+id);
		input.setAttribute("id", "options_"+id);
		td.appendChild(input);
		tr.appendChild(td);
		
		td = document.createElement("td");
		td.style.textAlign="center";
		input = document.createElement("INPUT")
		input.setAttribute("type", "checkbox");
		input.setAttribute("value", 1);
		input.setAttribute("name", "checked_"+id);
		td.appendChild(input);
		tr.appendChild(td);
		
		td = document.createElement("td");
		td.style.textAlign="center";
		img = document.createElement("IMG");
		img.setAttribute("src", "icons/element_edit.gif");
		img.onclick = new Function("edit_possibility("+id+");");
		img.style.cursor="pointer";
		td.appendChild(img);
		img = document.createElement("IMG");
		img.setAttribute("src", "icons/delete.gif");
		img.setAttribute("id", "img_"+id);
		img.onclick = new Function("confirm_delete("+id+");");
		img.style.cursor="pointer";
		td.appendChild(img);
		tr.appendChild(td);
		
		elementtable.appendChild(tr);	
		
		//delete norow
		if(document.getElementById("norow"))
		{	
			document.getElementById("options").deleteRow(2);
		}
		
	}
	document.getElementById('option').value="";
}

//creates a new unique ID for the next answer
function getnewID()
{
	found=false;
	while(found==false)
	{
		randomID=Math.round(Math.random()*1000);
		if(document.getElementById("options_"+randomID))	found=false;	
		else found=true;
	}
	return randomID;
}


//delete node and insert a new row with label "no answers" available
function deleteNode(optionid)
{	
	//delete node
	node=document.getElementById("tr_"+optionid);	
	document.getElementById("options").removeChild(node);
	
	//insert norow
	if(document.getElementById("options").getElementsByTagName("tr").length == 3)
	{
		//insert no row
		elementtable = document.getElementById("options");
		tr = document.createElement("tr");
		tr.setAttribute("id", "norow");
		tr.style.textAlign="center";
		
		td = document.createElement("td");
		td.setAttribute("id", "td_norow");
		td.style.color="#808080";
		td.setAttribute("colSpan",3);
		td.appendChild(document.createTextNode(noanswers));
		tr.appendChild(td);
		
		elementtable.appendChild(tr);
	}
}

//edit existing possibility
function edit_possibility(optionid)
{	
	if(edit_multiple_check==0)
	{
		edit_multiple_check=1;
		
		content=document.getElementById("td1_"+optionid).firstChild.data;
		
		//delete content
		document.getElementById("td1_"+optionid).firstChild.data="";
		
		//insert textfield
		td = document.getElementById("td1_"+optionid);
		input = document.createElement("INPUT")
		input.setAttribute("type", "text");
		input.setAttribute("value", content);
		input.setAttribute("name", "new_name_"+optionid);
		input.setAttribute("id", "new_name_"+optionid);
		td.appendChild(input);
		
		//insert OK button
		button = document.createElement("INPUT")
		button.setAttribute("type", "button");
		button.setAttribute("value", "OK");
		button.setAttribute("name", "ok_button_"+optionid);
		button.setAttribute("id", "ok_button_"+optionid);
		button.onclick = new Function("edit_confirm("+optionid+");");
		td.appendChild(button);
	}
}

//save new possibility value
function edit_confirm(optionid)
{	
	content=document.getElementById("new_name_"+optionid).value;
	
	regAus=/[a-z]|[A-Z]+/;
	if(content.match(regAus))
	{	
		//save changes
		document.getElementById("td1_"+optionid).firstChild.data=content;
		document.getElementById("options_"+optionid).value=content;
		
		//delete textfield
		node=document.getElementById("new_name_"+optionid);	
		document.getElementById("td1_"+optionid).removeChild(node);
		
		//delete ok button
		node=document.getElementById("ok_button_"+optionid);	
		document.getElementById("td1_"+optionid).removeChild(node);
		
		edit_multiple_check=0;
	}
}