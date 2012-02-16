<?php

/****************************************************************************
 properties.php - display and modify the properties dialog of an object
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

 Author: Henrik Beige <hebeige@gmx.de>
 Harald Selke <hase@upb.de>

 ****************************************************************************/

//include stuff
require_once("./config/config.php");
require_once("$steamapi_doc_root/steam_connector.class.php");
require_once("$config_doc_root/classes/template.inc");
//require_once("$config_doc_root/classes/doc_content.php");
//require_once("$config_doc_root/classes/debugHelper.php");

require_once("$config_doc_root/includes/sessiondata.php");
require_once("$config_doc_root/includes/derive_menu.php");
require_once("$config_doc_root/includes/derive_url.php");


//******************************************************
//** Precondition
//******************************************************

$properties = (isset($_GET["properties"]))?$_GET["properties"]:((isset($_POST["properties"]))?$_POST["properties"]:0);

$page = (int) (isset($_GET["page"]))?$_GET["page"]:1;
$last_page = (int) (isset($_POST["last_page"]))?$_POST["last_page"]:0;

$attrib = array(OBJ_NAME, OBJ_DESC, OBJ_KEYWORDS, OBJ_TYPE, "bid:doctype", DOC_MIME_TYPE, DOC_EXTERN_URL, "bid:collectiontype", "bid:presentation", "bid:frameset", "bid:fullscreen", "bid:hidden", "bid:description", "bid:tags");

//******************************************************
//** sTeam Stuff
//******************************************************

$steam = new steam_connector(
$config_server_ip,
$config_server_port,
$login_name,
$login_pwd);

if( !$steam || !$steam->get_login_status() )
{
	header("Location: $config_webserver_ip/index.html");
	exit();
}

//get old/saved form values and merge them with the recently selected
if(isset($_POST["changed"]))
{
	if(!($changed = @unserialize(rawurldecode($_POST["changed"]))))
	$changed = "";
	$post = $_POST;
	unset($post["changed"]);
	unset($post["orderfiles"]);

	//catch unset checkboxes
	switch ($last_page)
	{
		//clean general values
		case 1:
			if(!isset($post["bid:hidden"]) && isset($changed["bid:hidden"]) && $changed["bid:hidden"] == "1")
			$post["bid:hidden"] = "0";
			if(!isset($post["bid:frameset"]) && isset($changed["bid:frameset"]) && $changed["bid:frameset"] == "1")
			$post["bid:frameset"] = "0";
			if(!isset($post["bid:fullscreen"]) && isset($changed["bid:fullscreen"]) && $changed["bid:fullscreen"] == "1")
			$post["bid:fullscreen"] = "0";
			break;

			//clean metadata values
		case 2:
			$post[OBJ_KEYWORDS] = explode ("\n", $post[OBJ_KEYWORDS]);
			break;

			//clean sorting post
		case 3:
			$tmp = trim($post["bid:sorting"]);
			$tmp_sorting = explode(" ", $tmp);
			$post["bid:sorting"] = array();
			foreach($tmp_sorting as $id)
			$post["bid:sorting"][] = steam_factory::get_object($steam, $id);
			break;
	}

}
else
{
	$changed = array();
	$post = (isset($_POST))?$_POST:array();
}

//get current object
$steamUser = $steam->get_login_user();
if( $properties != 0 ) $current_object = steam_factory::get_object($steam, $properties);
else $current_object = $steamUser->get_workroom();

//get requested attribute values
$current_object_data = $current_object->get_attributes ($attrib, 1);
$editable = $current_object->check_access_write($steamUser, 1);
$owner = $current_object->get_creator(1);

$result = $steam->buffer_flush();
$current_object_data = $result[$current_object_data];
$editable = $result[$editable];
$owner = $result[$owner];

$owner_name = $owner->get_name();

if (!($current_object_data["bid:description"]))
  $current_object_data["bid:description"]="";
if (!($current_object_data["bid:tags"]))
  $current_object_data["bid:tags"]="";

//general data for each page
$saved[OBJ_NAME] = $current_object_data[OBJ_NAME];
$saved[OBJ_DESC] = $current_object_data[OBJ_DESC];
$saved[OBJ_TYPE] = $current_object_data[OBJ_TYPE];
$saved["bid:doctype"] = $current_object_data["bid:doctype"];

//page specific data
switch($page)
{
	case 1:
		$saved = $current_object_data;
		break;
	case 2:
		$saved = $current_object_data;
		$saved[OBJ_KEYWORDS] = $current_object_data[OBJ_KEYWORDS];
		$saved["bid:description"] = $current_object_data["bid:description"];
		$saved["bid:tags"] = $current_object_data["bid:tags"];
		break;
	case 3:
		$saved = $current_object_data;
		if(isset($changed["bid:sorting"]))
		$inventory = $changed["bid:sorting"];
		else
		$inventory = $current_object->get_inventory("", array(OBJ_NAME, OBJ_DESC, OBJ_TYPE, "bid:doctype"));
		$saved["bid:sorting"] = $inventory;
		break;
}
$values = array_merge($saved, $changed, $post);

//Save everything
if(isset($_GET["action"]) && $_GET["action"] == "save")
{
	//build arrays, NOT bid:sorting
	foreach($values as $key => $value)
	{
		if ($key == OBJ_KEYWORDS)
			$tmp_values[$key] = $values[OBJ_KEYWORDS];
		else if($key == "bid:sorting")
			continue;
		else if ($key == DOC_EXTERN_URL && !($current_object instanceof steam_docextern))
			continue;
		else if ($key == OBJ_DESC)
			$tmp_values[$key] = strip_tags(stripslashes($value), "<b><i><em><font><strong><small><big>");
		else
			$tmp_values[$key] = $value;
	}
	$current_object->set_attributes($tmp_values, 1);

	//do sorting stuff on directory
	if($current_object instanceof steam_container && isset($values["bid:sorting"]))
	foreach($values["bid:sorting"] as $key => $value) {
		$current_object->swap_inventory($value, $key, 1);
	}

	//save all
	$steam->buffer_flush();
}

//Logout & Disconnect
$steam->disconnect();

//after save action and disconnect close window and update the opener
if(isset($_GET["action"]) && $_GET["action"] == "save")
{
	echo("<html>\n<body onload='window.opener.top.location.reload();");
	echo("window.close();'>\n</body>\n</html>");
	exit;
}

//******************************************************
//** Display Stuff
//******************************************************

//template stuff
$tpl = new Template("./templates/$language", "keep");
$tpl->set_file(array(
    "content" => "properties.ihtml"
    ));
    $tpl->set_block("content", "checked", "DUMMY");
    $tpl->set_block("content", "unchecked", "DUMMY");
    $tpl->set_block("content", "tab", "DUMMY");
    $tpl->set_block("content", "general", "DUMMY");
    $tpl->set_block("content", "metadata", "DUMMY");
    $tpl->set_block("content", "sort", "DUMMY");

    $tpl->set_var(array(
    "DUMMY" => "",
    "CHANGED" => rawurlencode(serialize($values)),
    "OBJ_NAME" => htmlentities(stripslashes($values[OBJ_NAME]), ENT_COMPAT, "UTF-8"),
    "OBJ_DESC" => htmlentities(stripslashes($values[OBJ_DESC]), ENT_COMPAT, "UTF-8"),
    "bid:description" => htmlentities(stripslashes($values["bid:description"]), ENT_COMPAT, "UTF-8"),
    "bid:tags" => htmlentities(stripslashes($values["bid:tags"]), ENT_COMPAT, "UTF-8"),
    "OBJ_KEYWORDS" => htmlentities(stripslashes(implode ("\n", $values[OBJ_KEYWORDS])), ENT_COMPAT, "UTF-8"),
    "OBJ_OWNER" => $owner_name,
    "OBJECT_ID" => $current_object->get_id()
    ));

    // Add extra edit field for links to change their URL
    if ($current_object instanceof steam_docextern)
    {
    	$tpl->set_var(array(
    		"DOC_EXTERN_URL" => $values[DOC_EXTERN_URL]
    	));
    }

    //output tab
    $tpl->set_block("tab", "tab_label_1", "DUMMY");
    $tpl->set_block("tab", "tab_label_2", "DUMMY");
    $tpl->set_block("tab", "tab_label_3", "DUMMY");
    $tpl->set_block("tab", "tab_spacer", "DUMMY");
    $tpl->set_block("tab", "tab_active", "DUMMY");
    $tpl->set_block("tab", "tab_inactive", "DUMMY");
    $tpl->set_block("tab_inactive", "tab_inactive_left", "TAB_INACTIVE_LEFT");
    $tpl->set_block("tab_inactive", "tab_inactive_right", "TAB_INACTIVE_RIGHT");

    //if no directory no 3rd tab
    $tabnumber = ($current_object instanceof steam_container &&
    $saved["bid:doctype"] !== "portal" &&
    $saved["bid:doctype"] !== "portlet" &&
    $saved["OBJ_TYPE"] !== "container_portal_bid" &&
    $saved["OBJ_TYPE"] !== "container_portlet_bid" &&
    $saved["bid:doctype"] !== "questionary")?3:2;

    //display 2px spacer if the first one is inactive
    if($page > 1)
    $tpl->parse("TAB", "tab_spacer", true);


    //display the tabs on top
    for($i = 1; $i <= $tabnumber; $i++)
    {
    	//page specific data, name and page number
    	$tpl->parse("TAB_NAME", "tab_label_$i");
    	$tpl->set_var("TAB_PAGE", $i);

    	//parse correct border to inactive tabs
    	if($i - 1 != $page)
    	$tpl->parse("TAB_INACTIVE_LEFT", "tab_inactive_left");
    	else
    	$tpl->set_var("TAB_INACTIVE_LEFT", "");

    	if($i + 1 != $page)
    	$tpl->parse("TAB_INACTIVE_RIGHT", "tab_inactive_right");
    	else
    	$tpl->set_var("TAB_INACTIVE_RIGHT", "");

    	//parse active/inactive tabs
    	$tpl->parse("TAB", (($i == $page)?"tab_active":"tab_inactive"), true);
    }

    $tpl->set_var(array(
        "TAGS" => $_SESSION["tags"]
    ));


    //output page content
    switch ($page)
    {
    	default:
    	case 1:
      		$tpl->set_block("general", "general_view", "DUMMY");
      		$tpl->set_block("general", "general_edit", "DUMMY");
    		
      		$tpl->set_var(array(
        		"bid:hidden" => ((isset($values["bid:hidden"]) && $values["bid:hidden"] == 1)?"checked":""),
        		"bid:frameset" => ((isset($values["bid:frameset"]) && $values["bid:frameset"] == 1)?"checked":""),
        		"bid:fullscreen" => ((isset($values["bid:fullscreen"]) && $values["bid:fullscreen"] == 1)?"checked":"")
      		));
      		
      		if($editable) 
      		{      			
				$tpl->set_block("general_edit", "doc_extern_url_edit", "DOC_EXTERN_URL_EDIT");
      			
      			// Add extra edit field for links to change their URL
      			if ($current_object instanceof steam_docextern)
      			{
      				$tpl->parse("DOC_EXTERN_URL_EDIT", "doc_extern_url_edit");	
      			}
      			else
      			{
      				$tpl->parse("DOC_EXTERN_URL_EDIT", "DUMMY");	      				      			
      			}
      			
      			$tpl->parse("GENERAL_CONTENT", "general_edit");
      		}
      		else
      		{
				$tpl->set_block("general_view", "doc_extern_url_view", "DOC_EXTERN_URL_VIEW");
      			
				// Add extra edit field for links to change their URL
      			if ($current_object instanceof steam_link)
      			{
      				print "TEST";
      				$tpl->parse("DOC_EXTERN_URL_VIEW", "doc_extern_url_view");	
      			}
      			else
      			{
      				$tpl->parse("DOC_EXTERN_URL_VIEW", "DUMMY");
      			}
      			
      			$tpl->parse("GENERAL_CONTENT", "general_view");
      		}
      			
      		$tpl->parse("TAB_PAGE", "general");
      	break;
    	
      	case 2:
      		$tpl->set_block("metadata", "metadata_view", "DUMMY");
      		$tpl->set_block("metadata", "metadata_edit", "DUMMY");
      		
      		if($editable)
      			$tpl->parse("METADATA_CONTENT", "metadata_edit");
      		else
      			$tpl->parse("METADATA_CONTENT", "metadata_view");
      			$tpl->parse("TAB_PAGE", "metadata");
      		break;
      		
    	case 3:
      		$tpl->set_block("sort", "sort_view", "DUMMY");
      		$tpl->set_block("sort", "sort_edit", "DUMMY");
      		$tpl->set_block("sort_view", "sort_view_row", "SORT_VIEW_ROW");
      		$tpl->set_block("sort_edit", "sort_edit_row", "SORT_EDIT_ROW");
      		
     		foreach($inventory as $item)
      		{
      			if(!($item instanceof steam_document ||
      			$item instanceof steam_docextern ||
      			$item instanceof steam_messageboard ||
      			$item instanceof steam_container ||
      			$item instanceof steam_room ||
      			$item instanceof steam_exit ||
      			$item instanceof steam_link) ||
      			$item instanceof steam_trashbin ||
      			$item instanceof steam_user ||
      			$item->get_attribute("OBJ_TYPE") === "LARS_DESKTOP"
      			)
      				continue;

      			$dir_entry_display_name = $item->get_attribute(OBJ_NAME);
      			if ($item->get_attribute(OBJ_DESC) != "" && !($item instanceof steam_messageboard))
      			{
      				$dir_entry_display_name = $item->get_attribute(OBJ_DESC);
      			}
      			
      			$tpl->set_var(array(
          			"DIR_ENTRY_ID" => $item->get_id(),
          			"DIR_ENTRY_NAME" => $dir_entry_display_name
      			));
      			
      			if($editable)
      				$tpl->parse("SORT_EDIT_ROW", "sort_edit_row", true);
      			else
      				$tpl->parse("SORT_VIEW_ROW", "sort_view_row", true);
      		}
      		
      		if($editable)
      		{
      			$tpl->set_var(array(
          			"bid:presentation:normal" => (($values["bid:presentation"] === "normal")?"selected":""),
          			"bid:presentation:index" => (($values["bid:presentation"] === "index")?"selected":""),
          			"bid:presentation:head" => (($values["bid:presentation"] === "head")?"selected":""),
          			"bid:collectiontype:normal" => (($values["bid:collectiontype"] === "normal" || $values["bid:collectiontype"] == "")?"selected":""),
          			"bid:collectiontype:cluster" => (($values["bid:collectiontype"] === "cluster")?"selected":""),
          			"bid:collectiontype:sequence" => (($values["bid:collectiontype"] === "sequence")?"selected":""),
		  			"bid:collectiontype:gallery" => (($values["bid:collectiontype"] === "gallery")?"selected":""),
		  			"bid:collectiontype:taggedFolder" => (($values["bid:collectiontype"] === "taggedFolder")?"selected":"")
      			));
      			$tpl->parse("SORT_CONTENT", "sort_edit");
      		}
      		else
      		{
      				$tpl->set_block("sort_view", "bid:presentation:normal", "DUMMY");
      				$tpl->set_block("sort_view", "bid:presentation:index", "DUMMY");
      				$tpl->set_block("sort_view", "bid:presentation:head", "DUMMY");
      				$tpl->set_block("sort_view", "bid:collectiontype:normal", "DUMMY");
      				$tpl->set_block("sort_view", "bid:collectiontype:cluster", "DUMMY");
      				$tpl->set_block("sort_view", "bid:collectiontype:sequence", "DUMMY");
      				$tpl->set_block("sort_view", "bid:collectiontype:gallery", "DUMMY");
      				$tpl->set_block("sort_view", "bid:collectiontype:taggedFolder", "DUMMY");
      				$tpl->parse("bid:presentation", "bid:presentation:" . $values["bid:presentation"]);
      				$tpl->parse("bid:collectiontype", "bid:collectiontype:" . $values["bid:collectiontype"]);
      				$tpl->parse("SORT_CONTENT", "sort_view");
      		}
      		
      		$tpl->parse("TAB_PAGE", "sort");
      	break;
    }

    out();

    function out()
    {
    	//parse all out
    	global $tpl;
    	$tpl->parse("OUT", "content");
    	$tpl->p("OUT");

    	exit;
    }
    ?>