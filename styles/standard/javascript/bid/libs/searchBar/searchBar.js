  /****************************************************************************
  searchBar.js - tagging searchBar
  Copyright (C)

  This program is free software; you can redistribute it and/or modify it
  under the terms of the GNU General Public License as published by the
  Free Software Foundation; either version 2 of the License,
  or (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
  See the GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software Foundation,
  Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA

  Author: Benedikt Redmer
  EMail: ben@redmer.net

  ****************************************************************************/
  
  
  
var divLayer;
var tagArray = new Array();
var onSearchCallback = new Function();
var imageURL;
var sb_tagstring;
var sb_boolstring;


function sb_setImageURL(url) {
        imageURL=url;
}



function sb_init(layerName, tagstring, boolstring) {
	
	divLayer = layerName;
        sb_tagstring = tagstring;
        sb_boolstring = boolstring;
	
	var innerHTML = "";
	
	innerHTML += "<div style=\"text-align:right;padding-bottom:10px\">";
	//innerHTML += "  <form name=\"searchBar\" action=\"\" target=\"_self\">";
	innerHTML += "    <input type=\"text\" id=\"searchString\" name=\"searchString\" onBlur=\"sb_onSearch()\" size=\"64\">";
	innerHTML += "    <img src=\"" + imageURL + "/searchbutton.png\" onClick=\"sb_onSearch()\" style=\"vertical-align:middle; padding:0px\">";
	innerHTML += "    <img src=\"" + imageURL + "/clearbutton.png\" style=\"vertical-align:middle; padding:0px\" onBlur=\"sb_onSearch()\" onClick=\"sb_clearSearchString()\">";
	innerHTML += "    <img src=\"" + imageURL + "/up.png\" onBlur=\"sb_onSearch()\" style=\"vertical-align:middle; padding:0px\" onClick=\"sb_swapTagButton(this)\">";
	//innerHTML += "  </form>";
	innerHTML += "</div>";
	
	innerHTML += "<div id=\"tagLayer\" style=\"visibility:hidden; position:absolute; z-index:999; text-align:justify;\">";
	innerHTML += "  <div style=\"font-weight:bold; padding-bottom:4px; padding-top:0px;\">" + tagstring + ":</div>";
	
	for (i=0; i<tagArray.length; i++)
		innerHTML += "  <span onClick=\"sb_changeTagState(this)\" onBlur=\"sb_onSearch()\" id=\"" + tagArray[i] + "\" style=\"background-color:orange\">" + tagArray[i] + "</span>";
	
	innerHTML += "  <div style=\"font-weight:bold; padding-bottom:4px; padding-top:4px;\">" + boolstring + ":</div> ";
	innerHTML += "  <span onClick=\"sb_insertTagOperation('OR')\" onBlur=\"sb_onSearch()\" style=\"background-color:orange\">ODER</span>";
	innerHTML += "  <span onClick=\"sb_insertTagOperation('AN')\" onBlur=\"sb_onSearch()\" style=\"background-color:orange\">\"UND NICHT\"</span>";
	innerHTML += "</div>";		
				
	document.getElementById(divLayer).innerHTML=innerHTML;
	
	//document.getElementById("searchString").value=getSearchStringFromURL();
	
}



function getSearchStringFromURL()
{
	var searchString = String(document.URL);
	
	searchString = searchString.match(/searchString=.+/g);
	
	searchString = String(searchString).substring(13);
	
	searchString = searchString.replace(/\+/g, " ");
	searchString = searchString.replace(/%C3%84/g, "Ä");
	searchString = searchString.replace(/%C3%96/g, "Ö");
	searchString = searchString.replace(/%C3%9C/g, "Ü");
	searchString = searchString.replace(/%C3%A4/g, "ä");
	searchString = searchString.replace(/%C3%B6/g, "ö");
	searchString = searchString.replace(/%C3%BC/g, "ü");
	searchString = searchString.replace(/%C3%9F/g, "ß");

	return searchString;

}



function sb_setTags(tagList) {	
	
  tagArray = tagArray.concat( tagList.split(" ") );

}



function sb_onSearchCallback (func) {	
	
	onSearchCallback = func;

}



function sb_getSearchString() {	
	
	return document.getElementById("searchString").value;

}



function sb_setSearchString(str) {	
	
	document.getElementById("searchString").value = str;

}



function sb_isValidSearchString() {	
	
	var searchString = trimString(document.getElementById("searchString").value);
		document.getElementById("searchString").value = searchString;
			
	if ((searchString.search(/^OR/) != -1) ||
			(searchString.search(/AN$/) != -1) ||
			(searchString.search(/OR$/) != -1) ||
			(searchString.search(/OR OR/) != -1) ||
			(searchString.search(/OR AN/) != -1) ||
			(searchString.search(/AN OR/) != -1) ||
	   	(searchString.search(/AN AN/) != -1) )
		return false;
	else
		return true;

}



function sb_onSearch() {

	var searchString = trimString(document.getElementById("searchString").value);
			document.getElementById("searchString").value = searchString;
	
	if ( !sb_isValidSearchString() )
		document.getElementById("searchString").style.backgroundColor = "#ffcccc";
	else
		document.getElementById("searchString").style.backgroundColor = "white";  	   		
					
	for (var i=0; i<tagArray.length; i++)
          if ( !searchString.split(" ").search( tagArray[i] ) )
            document.getElementById(tagArray[i]).style.backgroundColor = "orange";

        var tags = searchString.split(" ");
        for (var i=0; i<tags.length; i++)
          if ( document.getElementById(tags[i]) )
            document.getElementById(tags[i]).style.backgroundColor="yellow";
		
	onSearchCallback();
	
}



function sb_changeTagState(caller){
					
	var tag = caller.firstChild.nodeValue;
	var searchString = document.getElementById("searchString").value;
			
	if ( caller.style.backgroundColor=="yellow" ) {
			caller.style.backgroundColor="orange"
			searchString = searchString.replace(new RegExp(tag, "g"), " ");
	} 
	else {
			caller.style.backgroundColor="yellow"
			searchString = searchString + " " + tag;
	}

	document.getElementById("searchString").value = trimString(searchString);
	
	sb_onSearch()
	
}
	
	
	
function sb_showTags(caller){
			
	var tagButton = caller;
	
	var tagLayerWidth = 200;   
	var tagButtonWidth = tagButton.offsetWidth;
	var tagButtonHeight = tagButton.offsetHeight;   
			
	var tagButtonPosition = { left:Math.ceil(tagButtonWidth/2), 
														top:Math.ceil(tagButtonHeight) };   
	while (tagButton) {
		tagButtonPosition.left += tagButton.offsetLeft;
		tagButtonPosition.top += tagButton.offsetTop;
		tagButton = tagButton.offsetParent;
	} 
			
	var tagLayer = document.getElementById("tagLayer");
		
	if (tagButtonPosition.left>document.getElementById(divLayer).offsetWidth/2)
		tagButtonPosition.left -= tagLayerWidth;
				
	if (tagLayer.style.visibility == "visible" )
		tagLayer.style.visibility = "hidden"
	else {
		tagLayer.style.backgroundColor = "orange";
		tagLayer.style.padding = "5px";
		tagLayer.style.fontFamily = "Arial";
		tagLayer.style.fontSize = "14";
		tagLayer.style.textAlign = "justify";
		tagLayer.style.outline = "1px solid black";
		tagLayer.style.left = tagButtonPosition.left;
		tagLayer.style.top = tagButtonPosition.top;
		tagLayer.style.width = tagLayerWidth + "px";
		tagLayer.style.position = "absolute";
		tagLayer.style.visibility = "visible"
	}
	
	sb_onSearch();
				
}
	
	
	
function sb_swapTagButton(caller){
			
	if (caller.src.search(/up.png/) != -1)
		caller.src=imageURL +"/down.png";
	else
		caller.src=imageURL +"/up.png";
	
	sb_showTags(caller);
		
}
			
			
			
function sb_insertTagOperation(operation) {
			
	var searchString = document.getElementById("searchString").value;
				
	if (searchString.length == 0 && operation == "OR") {
		sb_onSearch();
		return;
	}
	
	if ((operation == "AN") &&
			(searchString.search(/AN$/) != -1) ) {
		searchString = searchString.replace(/AN$/, " ");
		document.getElementById("searchString").value = trimString(searchString);
		sb_onSearch();
		return;
	}
				
	if ((operation == "OR") &&
			(searchString.search(/OR$/) != -1) ) {
		searchString = searchString.replace(/OR$/, " "); 
		document.getElementById("searchString").value = trimString(searchString);
		sb_onSearch();
		return;
	}
				
	if ((operation == "AN") &&
			(searchString.search(/OR$/) != -1) ) {
		sb_onSearch();
		return;
	}
	
	if ((operation == "OR") &&
		(searchString.search(/AN$/) != -1) ) {
		sb_onSearch();
		return;
	}
	
	searchString += " " + operation;
	document.getElementById("searchString").value = trimString(searchString);
	
	sb_onSearch();

}
			
			
			
function sb_clearSearchString() {
			
	document.getElementById("searchString").value = "";
	sb_onSearch()
	
}



function sb_parse(tagList) {
		
	var result = true;
	var searchArray = splitString(document.getElementById("searchString").value, " ");
	var tagArr = splitString(trimString(tagList), " ");
	
	if (searchArray.length>0) {
		if (searchArray[0]=="AN") {
			var currentOperator = "AN"
			var currentOperand = searchArray[1];
			var i = 1;
		}
		else {
			var currentOperator = "AND"
			var currentOperand = searchArray[0];
			var i = 0;
		}
	}
	else {
		var currentOperator = "AND";
		var currentOperand = "";
		var i = 0;
	}
	
		
	while (i<searchArray.length){
		
		var tmpResult = tagArr.search(currentOperand);
		
		if (currentOperator=="AND")
			result = result && tmpResult;
			
		if (currentOperator=="OR")
			result = result || tmpResult;
		
		if (currentOperator=="AN")
			result = result && !tmpResult;
		
		i++;
		
		
		if (i<searchArray.length) {
			
			if ( (searchArray[i] != "OR") && (searchArray[i] != "AN") ) {
				currentOperand = searchArray[i];
				currentOperator = "AND";
			}
			else {
				currentOperator = searchArray[i];
				i++;			
				currentOperand = searchArray[i];
			}
		
		}
		
	}
	
	return result;

}
