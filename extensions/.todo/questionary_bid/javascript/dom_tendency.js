// JavaScript Document
edit_multiple_check=0;

//insert a new row and deletes the "norow" row
function insertElement(name1, name2)
{
	if(name1!="" && name2!="")
	{
		name = name1 + " - " + name2;
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
		input.setAttribute("value", name1);
		input.setAttribute("name", "tendency_element_a_"+id);
		input.setAttribute("id", "tendency_element_a_"+id);
		td.appendChild(input);
		input = document.createElement("INPUT")
		input.setAttribute("type", "hidden");
		input.setAttribute("value", name2);
		input.setAttribute("name", "tendency_element_b_"+id);
		input.setAttribute("id", "tendency_element_b_"+id);
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
		img.onclick = new Function("confirm_delete("+id+",'"+name+"');");
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
	document.getElementById('tendency_element_a').value="";
	document.getElementById('tendency_element_b').value="";
}


//creates a new unique ID ofor the next answer
function getnewID()
{
	found=false;
	while(found==false)
	{
		randomID=Math.round(Math.random()*1000);
		if(document.getElementById("tendency_element_a_"+randomID))	found=false;	
		else found=true;
	}
	return randomID;
}


//delete node and insert a new row with label "no questions available"
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
		td.appendChild(document.createTextNode(nooption));
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
		
		content_a=document.getElementById("tendency_element_a_"+optionid).value;
		content_b=document.getElementById("tendency_element_b_"+optionid).value;
		
		//delete content
		document.getElementById("td1_"+optionid).firstChild.data="";
		
		//insert textfield a
		td = document.getElementById("td1_"+optionid);
		div=document.createElement("DIV");
		div.setAttribute("id", "div_"+optionid);
		input = document.createElement("INPUT")
		input.setAttribute("type", "text");
		input.setAttribute("value", content_a);
		input.setAttribute("name", "new_name_a_"+optionid);
		input.setAttribute("id", "new_name_a_"+optionid);
		div.appendChild(input);
		
		//hyphen
		hyphen=document.createTextNode(" - ");
		div.appendChild(hyphen);
		
		//insert textfield b
		input = document.createElement("INPUT");
		input.setAttribute("type", "text");
		input.setAttribute("value", content_b);
		input.setAttribute("name", "new_name_b_"+optionid);
		input.setAttribute("id", "new_name_b_"+optionid);
		div.appendChild(input);
		
		//insert OK button
		button = document.createElement("INPUT")
		button.setAttribute("type", "button");
		button.setAttribute("value", "OK");
		button.setAttribute("name", "ok_button_"+optionid);
		button.setAttribute("id", "ok_button_"+optionid);
		button.onclick = new Function("edit_confirm("+optionid+");");
		div.appendChild(button);
		
		td.appendChild(div);
	}
}

//save new possibility value
function edit_confirm(optionid)
{	
	content_a=document.getElementById("new_name_a_"+optionid).value;
	content_b=document.getElementById("new_name_b_"+optionid).value;
	
	regAus=/[a-z]|[A-Z]+/;
	if(content_a.match(regAus) && content_b.match(regAus))
	{	
		//save changes
		document.getElementById("td1_"+optionid).firstChild.data=content_a+" - "+content_b;
		document.getElementById("tendency_element_a_"+optionid).value=content_a;
		document.getElementById("tendency_element_b_"+optionid).value=content_b;
		
		//delete div form
		node=document.getElementById("div_"+optionid);	
		document.getElementById("td1_"+optionid).removeChild(node);
		
		edit_multiple_check=0;
	}
}