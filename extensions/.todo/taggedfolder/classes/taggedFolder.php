<?php

  /****************************************************************************
  contentframe.php - display content of container and rooms as filelist
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

  Author: Henrik Beige
  EMail: hebeige@gmx.de

  Ported: Carsten Buettemeier
  ****************************************************************************/

  //include stuff
  require_once("./config/config.php");
  require_once("$steamapi_doc_root/steam_connector.class.php");
  require_once("$config_doc_root/classes/template.inc");
  require_once("$config_doc_root/classes/doc_content.php");
  #require_once("$config_doc_root/classes/debugHelper.php");

  require_once("$config_doc_root/includes/sessiondata.php");
  require_once("$config_doc_root/includes/derive_icon.php");
  require_once("$config_doc_root/includes/derive_menu.php");
  require_once("$config_doc_root/includes/derive_url.php");
  require_once("$config_doc_root/config/mimetype_map.php");

  //******************************************************
  //** sTeam Stuff
  //******************************************************

  //login und $steam def. in "./includes/login.php"


  $steam = new steam_connector(	$config_server_ip,
  								$config_server_port,
  								$login_name,
  								$login_pwd);

  if( !$steam || !$steam->get_login_status() )
  {
    header("Location: $config_webserver_ip/index.html");
    exit();
  }

  //current room steam object
  if( (int) $object != 0 ) $current_room = steam_factory::get_object( $steam, $object );
  else $current_room = $steam->get_login_user()->get_workroom();
  //current room steam object
  // $current_room = ($object != 0)?new steam_object($object):$steam->get_workroom_user($steam->login_user);
  $current_room_path = $current_room->get_path(1);
  $current_room_data = $current_room->get_attributes(array(OBJ_NAME, OBJ_DESC, OBJ_LAST_CHANGED, "bid:tags", "bid:presentation", "bid:collectiontype", "bid:description"), 1);
  $current_room_creator = $current_room->get_creator(1);

  //check if user may write in this folder
  $write_allowed = $current_room->check_access_write($steam->get_login_user(), 1);
  //get inventory and inventorys attributes if allowed to
  $allowed = $current_room->check_access_read($steam->get_login_user(), 1);
  $result = $steam->buffer_flush();
  $write_allowed = $result[$write_allowed];
  $allowed = $result[$allowed];
  $current_room_path = $result[$current_room_path];
  $current_room_data = $result[$current_room_data];
  $current_room_creator = $result[$current_room_creator];

  $current_room_creator_name = $current_room_creator->get_name();

  $current_room_display_name = str_replace("'s workarea", "", stripslashes($current_room_data[OBJ_NAME]));
  if (isset($current_room_data[OBJ_DESC]) && $current_room_data[OBJ_DESC] != "")
  {
    $current_room_display_name = $current_room_data[OBJ_DESC];
  }
  $current_room_display_name = str_replace("s workroom.", "", $current_room_display_name);
  $current_room_display_name = str_replace("s workroom", "", $current_room_display_name);
  $current_room_display_name = preg_replace("/.*'s bookmarks/", "Lesezeichen", $current_room_display_name);

  if($allowed && $current_room instanceof steam_container)
    $inventory = $current_room->get_inventory("", array("DOC_MIME_TYPE", "DOC_LAST_MODIFIED", "CONT_LAST_MODIFIED", "OBJ_LAST_CHANGED", "bid:tags", "bid:presentation", "bid:collectiontype", "bid:hidden", "bid:doctype", "bid:description", "DOC_EXTERN_URL", "OBJ_CREATION_TIME"));
  else
    $inventory = array();

  if((sizeof($inventory))>0) $hascontent = true;
	else $hascontent = false;

  //get head mounted content if needed
  $head_mounted = ($current_room_data["bid:presentation"] === "head" &&
                  is_array($inventory) &&
                  isset($inventory[0]) &&
                  $inventory[0] instanceof steam_document);
  if($head_mounted)
  {
    $tmp_content = new doc_content($steam, $inventory[0]);
    $head_mounted_content = $tmp_content->get_content($config_webserver_ip);
  }


  //******************************************************
  //** Display Stuff
  //******************************************************

  //template stuff
  $tpl = new Template("./templates/$language", "keep");
  $tpl->set_file("content", "taggedFolder.ihtml");
  $tpl->set_block("content", "no_content", "DUMMY");
  $tpl->set_block("content", "grey_name", "DUMMY");
  $tpl->set_block("content", "head_mounted_name", "DUMMY");
  $tpl->set_block("content", "document", "DUMMY");
  $tpl->set_block("content", "nb_document", "DUMMY");
  $tpl->set_block("content", "link", "DUMMY");
  $tpl->set_block("content", "broken_link", "DUMMY");
  $tpl->set_block("content", "www-link", "DUMMY");
  $tpl->set_block("content", "folder", "DUMMY");
  $tpl->set_block("content", "item", "DUMMY");
  $tpl->set_block("item", "item_properties_on", "ITEM_PROPERTIES");
  $tpl->set_block("item", "item_properties_off", "DUMMY");
  $tpl->set_block("content", "mark", "MARK");
  $tpl->set_block("content", "unmark", "UNMARK");
  $tpl->set_var(array(
    "DUMMY" => "",
    "MARK" => "",
    "UNMARK" => "",
    "FOLDER_ID" => $current_room->get_id(),
    "FOLDER_NAME" => $current_room_display_name,
    "FOLDER_PATH" => $config_webserver_ip . $current_room_path . '/',
    "FOLDER_ICON" => (($current_room_data["bid:presentation"] === "index")?"$config_webserver_ip/icons/mimetype/folder_closed_index.gif":"$config_webserver_ip/icons/mimetype/folder_closed.gif"),
    "FOLDER_CREATOR" => $current_room_creator_name,
    "FOLDER_LAST_CHANGED" => date("d.m.Y H:i", $current_room_data[OBJ_LAST_CHANGED]),
    "MENU" => derive_menu("contentframe", $current_room, $current_room_path, 1),
    "HEAD_MOUNTED" => (($head_mounted)?"$head_mounted_content<hr>":"")
    ));

  if (sizeof($inventory)==0) {
    $tpl->set_block("content", "folderview", "no_content");
    $tpl->parse("DOCUMENTS", "");
  }

  //set menu to write mode, if the user's access rights allow so and the user is not the guest user
  if ($write_allowed && $steam->get_login_user()->get_name() != "guest") {
    $tpl->set_var("MENU",derive_menu("contentframe", $current_room, $current_room_path, 2));
  }
  //display directory
  $content = false;

  $tnr = array();
  foreach($inventory as $item) {
    $tnr[$item->get_id()] = array();
    $tnr[$item->get_id()]["creator"] = $item->get_creator(1);
    $tnr[$item->get_id()]["writeaccess"] = $item->check_access_write($steam->get_login_user(), 1);
    if ($item instanceof steam_document) $tnr[$item->get_id()]["contentsize"] = $item->get_content_size(1);
    if ($item instanceof steam_link) $tnr[$item->get_id()]["link_object"] = $item->get_link_object(1);
  }
  $result = $steam->buffer_flush();
  $creators = array();
  $linktargets = array();
  $accessresult = array();
  $sizeresult = array();
  foreach($inventory as $item) {
    $creators[$item->get_id()] = $result[$tnr[$item->get_id()]["creator"]];
    $accessresult[$item->get_id()] = $result[$tnr[$item->get_id()]["writeaccess"]];
    if ($item instanceof steam_document) $sizeresult[$item->get_id()] = $result[$tnr[$item->get_id()]["contentsize"]];
    if ($item instanceof steam_link)  $linktargets[$item->get_id()] = $result[$tnr[$item->get_id()]["link_object"]];
  }
  steam_factory::load_attributes($steam, $creators, array(OBJ_NAME));

  // If you want to use further Methods of caching e.g. PHP PEARs Cache_Lite
  // insert caching mechanisms in here...
  // below this, the steam connector is no longer used...

  $tags = array();

  foreach($inventory as $item) {
   if (!$item instanceof steam_trashbin) {

    $itemname = $item->get_attribute(OBJ_NAME);
    $itemdescription = $item->get_attribute(OBJ_DESC);
    $itemmimetype = $item->get_attribute(DOC_MIME_TYPE);
    $lastchanged = $item->get_attribute(DOC_LAST_MODIFIED);
    if ($lastchanged === 0) {
      $lastchanged = $item->get_attribute(OBJ_CREATION_TIME);
    }

    // set display name
    if ($itemdescription != "")
      $object_display_name = $itemdescription;
    else $object_display_name = stripslashes($itemname);

    $owner = $creators[ $item->get_id() ]->get_name();

    $bidDescription = $item->get_attribute("bid:description");
    $bidTags = $item->get_attribute("bid:tags");
    
    if (!$bidDescription) $bidDescription="";
    if (!$bidTags) $bidTags="";
    
    foreach(explode(" ", $bidTags) as $tag) {
      $tag = trim($tag);
      if (strlen($tag)>0)
        if (!in_array($tag, $tags))
          array_push($tags, $tag);
    }
    
    $tpl->set_var(array(
      "OBJECT_ID" => $item->get_id(),
      "OBJECT_NAME" => $object_display_name,
      "OBJECT_NAME_GREY" => $object_display_name,
      "OBJECT_FILENAME" => stripslashes($itemname),
      "OBJECT_LAST_CHANGED" => date("d.m.Y H:i", $lastchanged),
      "bid:description" =>  $bidDescription,
      "bid:tags" =>  $bidTags,
      "OBJECT_OWNER" => $owner
    ));

    if($show_hidden) {
      if($content == false && $head_mounted)
        $tpl->parse("OBJECT_NAME", "head_mounted_name");
      else if($item->get_attribute("bid:hidden"))
        $tpl->parse("OBJECT_NAME", "grey_name");
    }
    else if($item->get_attribute("bid:hidden"))
      continue;

   //parse correct Properties symbol
    $access = $accessresult[ $item->get_id() ];
    $tpl->parse("ITEM_PROPERTIES", (($access)?"item_properties_on":"item_properties_off"));

    $visible = false;

	// render a steam_document

    if($item instanceof steam_document)
    {
      //derive mimetype
      $mimetype = derive_icon(array(
        "object" => $item,
        "name" => $itemname,
        "bid:collectiontype" => $item->get_attribute("bid:collectiontype"),
        "bid:doctype" => $item->get_attribute("bid:doctype"),
        "mimetype" => $itemmimetype
      ));

      //derive size
      $size = $sizeresult[$item->get_id()];
      $tpl->set_var(array(
        "OBJECT_SIZE" => (($size > 1048576)? round($size / 1048576 + 0.05, 1) . " MB" : round($size / 1024 + 0.5) . " kB"),
        "OBJECT_ICON" => $mimetype
      ));

      //care for documents not to be displayed in the browser
      if($itemmimetype === "text/html" || $itemmimetype === "text/plain"
         || $itemmimetype === "text/css" || $itemmimetype === "text/xml"
         || $itemmimetype === "application/vnd.google-earth.kml+xml"
         || $itemmimetype === "image/gif" || $itemmimetype === "image/jpg"
         || $itemmimetype === "image/jpeg" || $itemmimetype === "image/png")
      {
        $tpl->parse("ITEM_LINK", "document");
        $tpl->parse("DOCUMENTS", "item", true);
      }
      else
      {
        $tpl->parse("ITEM_LINK", "nb_document");
        $tpl->parse("DOCUMENTS", "item", true);
      }
      $visible = true;
    }

	// set vars and parse for all STEAM_LINKs and STEAM_EXITs

    else if($item instanceof steam_link || $item instanceof steam_exit)
    {
      $linked_object = $linktargets[ $item->get_id() ];

	  if ($item instanceof steam_link) {
		if( $itemdescription != "" )
		  $name = $itemdescription;
		else $name = $itemname;
		if ($linked_object instanceof steam_document) {
		  $size = $linked_object->get_content_size();
		  $tpl->set_var(array("OBJECT_SIZE" => (($size > 1048576)? round($size / 1048576 + 0.05, 1) . " MB" : round($size / 1024 + 0.5) . " kB"),));
		}
		else {
		  $tpl->set_var(array("OBJECT_SIZE" => ""));
		}
	  }

	  if ($item instanceof steam_exit) {
		if($itemdescription != "" )
		  $name = str_replace("s workroom.", "", stripslashes($itemdescription));
		else $name = str_replace("'s workarea", "", stripslashes($itemname));
	  }

      //derive mimetype
      $mimetype = derive_icon(array(
        "object" => $item,
        "name" => $itemname,
        "bid:collectiontype" => $item->get_attribute("bid:collectiontype"),
        "bid:doctype" => $item->get_attribute("bid:doctype"),
        "mimetype" => $itemmimetype
      ));


      $tpl->set_var(array(
        "LINK_OBJECT_ID" => (is_object($linked_object)?$linked_object->get_id():-1),
        "OBJECT_NAME" => $name,
        "OBJECT_ICON" => $mimetype
      ));

      $tpl->parse("ITEM_LINK", "link");
      $tpl->parse("DOCUMENTS", "item", true);
      $visible = true;
    }

	else if($item instanceof steam_docextern)
    {
      $url = derive_url($item->get_attribute(DOC_EXTERN_URL));

      $tpl->set_var(array(
        "OBJECT_SIZE" => "",
        "OBJECT_LINK" => $url,
        "OBJECT_ICON" => "./icons/mimetype/www.gif"
      ));
      $tpl->parse("ITEM_LINK", "www-link");
      $tpl->parse("DOCUMENTS", "item", true);
      $visible = true;
    }
    else if($item->get_attribute("bid:doctype") != "")
    {
      $icon = array("bid:doctype" => $item->get_attribute("bid:doctype"));
      $tpl->set_var(array(
        "OBJECT_SIZE" => "",
        "OBJECT_ICON" => derive_icon($icon)
      ));
      $tpl->parse("ITEM_LINK", "folder");
      $tpl->parse("DOCUMENTS", "item", true);
      $visible = true;
    }

    else if($item instanceof steam_messageboard)
    {
      $icon = array("object" => $item);
      $tpl->set_var(array(
        "OBJECT_NAME" => stripslashes($itemname),
        "OBJECT_NAME_GREY" => stripslashes($itemname),
        "OBJECT_SIZE" => "",
        "OBJECT_ICON" => derive_icon($icon)
      ));
      $tpl->parse("ITEM_LINK", "folder");
      $tpl->parse("DOCUMENTS", "item", true);
      $visible = true;
    }

    else if($item instanceof steam_container && !$item instanceof steam_user)
    {
      $icon = array(
        "object" => $item,
        "bid:collectiontype" => $item->get_attribute("bid:collectiontype"),
        "bid:presentation" => $item->get_attribute("bid:presentation")
      );
      $icon = derive_icon($icon);
      $lastchanged = $item->get_attribute("CONT_LAST_MODIFIED");
      if ($lastchanged === 0) {
        $lastchanged = $item->get_attribute(OBJ_CREATION_TIME);
    }

      $tpl->set_var(array(
        "OBJECT_SIZE" => "",
        "OBJECT_ICON" => $icon,
        "OBJECT_LAST_CHANGED" => date("d.m.Y H:i", $lastchanged)
      ));
      $tpl->parse("ITEM_LINK", "folder");
      $tpl->parse("DOCUMENTS", "item", true);
      $visible = true;
    }

    //parse javascript mark/unmark
    if($visible)
    {
      $tpl->parse("MARK", "mark", 1);
      $tpl->parse("UNMARK", "unmark", 1);
    }

	$content = $content || $visible;
   }
  }

//  if(!$content) $tpl->parse("DOCUMENTS", "no_content");

  natsort($tags);

  $tagString = implode(" ", $tags);
  $tpl->set_var(array(
        "TAGS" => $tagString
      ));
  $_SESSION["tags"] = $tagString;

  out();

  //Logout & Disconnect
  $steam->disconnect();


function out()
  {
    //parse all out
    global $tpl;
    $tpl->parse("OUT", "content");
    $tpl->p("OUT");
  }

?>
