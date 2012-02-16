<?php

  /****************************************************************************
  trashbin.php - trashbin of the frameset
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

  Author: Harald Selke <hase@upb.de>

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
  //** Precondition
  //******************************************************

  $workroom_id = (isset($_GET["workroom"]))?$_GET["workroom"]:"";
  $environment_id = (isset($_GET["environment"]))?$_GET["environment"]:"";

  $steam = new steam_connector(	$config_server_ip,
                                $config_server_port,
                                $login_name,
                                $login_pwd);
  
  if( !$steam || !$steam->get_login_status() )
  {
    header("Location: $config_webserver_ip/index.html");
    exit();
  }

  //current room steam object should be the trashbin
  if( (int) $object != 0 ) $trashbin = steam_factory::get_object( $steam, $object );
  $trashbin_path = $trashbin->get_path(1);
  
    //check if user may write in this folder
  $write_allowed = $trashbin->check_access_write($steam->get_login_user(), 1);
  //get inventory and inventorys attributes if allowed to
  $allowed = $trashbin->check_access_read($steam->get_login_user(), 1);
  $result = $steam->buffer_flush();
  $write_allowed = $result[$write_allowed];
  $allowed = $result[$allowed];

  if($allowed && $trashbin instanceof steam_container)
    $inventory = $trashbin->get_inventory("", array("DOC_MIME_TYPE", "DOC_LAST_MODIFIED", "CONT_LAST_MODIFIED", "OBJ_LAST_CHANGED", "bid:collectiontype", "bid:doctype", "OBJ_CREATION_TIME"));
  else
    $inventory = array();

  if((sizeof($inventory))>0) $hascontent = true;
	else $hascontent = false;
  
  
  //******************************************************
  //** Display Stuff
  //******************************************************

  //template stuff
  $tpl = new Template("./templates/$language", "keep");
  $tpl->set_file("content", "trashbin.ihtml");
  $tpl->set_block("content", "trashbin_name", "DUMMY");
  $tpl->set_block("content", "no_content", "DUMMY");
  $tpl->set_block("content", "item", "DUMMY");
  $tpl->set_block("content", "item_list", "DUMMY");
  $tpl->set_block("content", "mark", "MARK");
  $tpl->set_block("content", "unmark", "UNMARK");

  $tpl->parse("TRASHBIN_NAME", "trashbin_name");

  $tpl->set_var(array(
    "DUMMY" => "",
    "MARK" => "",
    "UNMARK" => "",
    "TRASHBIN_ID" => $trashbin->get_id(),
    "TRASHBIN_ICON" => "$config_webserver_ip/icons/mimetype/trashbin.gif",
    "MENU" => derive_menu("trashbin", $trashbin, $trashbin_path, 1)
  ));

  //set menu to write mode, if the user's access rights allow so
  if ($write_allowed) {
    $tpl->set_var("MENU",derive_menu("trashbin", $trashbin, $trashbin_path, 2));
  }

  //display directory
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

      $tpl->set_var(array(
        "OBJECT_ID" => $item->get_id(),
        "OBJECT_NAME" => $object_display_name,
        "OBJECT_LAST_CHANGED" => date("d.m.Y H:i", $lastchanged),
        "OBJECT_OWNER" => $owner
      ));

      if($item instanceof steam_document)
      {
        //derive mimetype
        $mimetype = derive_icon(array(
          "object" => $item,
          "name" => $itemname,
          "bid:collectiontype" => $item->get_attribute("bid:collectiontype"),
          "bid:doctype" => $item->get_attribute("bid:doctype"),
          "mimetype" => $itemmimetype,
	  "obj_type" => $item->get_attribute("OBJ_TYPE")
        ));

        //derive size
        $size = $sizeresult[$item->get_id()];
        $tpl->set_var(array(
          "OBJECT_SIZE" => (($size > 1048576)? round($size / 1048576 + 0.05, 1) . " MB" : round($size / 1024 + 0.5) . " kB"),
          "OBJECT_ICON" => $mimetype
        ));
        $tpl->parse("ITEM", "item");
        $tpl->parse("DOCUMENTS", "item_list", true);
      }

      else if($item instanceof steam_link || $item instanceof steam_exit)
      {
        $linked_object = $linktargets[ $item->get_id() ];

        if ($item instanceof steam_link) {
          if( $itemdescription != "" )
            $name = $itemdescription;
          else $name = $itemname;
          $tpl->set_var(array("OBJECT_SIZE" => ""));
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
          "OBJECT_NAME" => $name,
          "OBJECT_ICON" => $mimetype
        ));
        $tpl->parse("ITEM", "item");
        $tpl->parse("DOCUMENTS", "item_list", true);
      }

      else if($item instanceof steam_docextern) {
        //derive mimetype
        $mimetype = derive_icon(array(
          "object" => $item,
          "name" => $itemname,
          "bid:collectiontype" => $item->get_attribute("bid:collectiontype"),
          "bid:doctype" => $item->get_attribute("bid:doctype"),
          "mimetype" => $itemmimetype
        ));

        $tpl->set_var(array(
          "OBJECT_NAME" => $name,
          "OBJECT_ICON" => $mimetype,
          "OBJECT_SIZE" => ""
        ));
        $tpl->parse("ITEM", "item");
        $tpl->parse("DOCUMENTS", "item_list", true);
      }

      else if($item->get_attribute("bid:doctype") != "")
      {
        $icon = array("bid:doctype" => $item->get_attribute("bid:doctype"));
        $tpl->set_var(array(
          "OBJECT_SIZE" => "",
          "OBJECT_ICON" => derive_icon($icon)
        ));
        $tpl->parse("ITEM", "item");
        $tpl->parse("DOCUMENTS", "item_list", true);
      }

      else if($item instanceof steam_messageboard)
      {
        $icon = array("object" => $item);
        $tpl->set_var(array(
          "OBJECT_NAME" => stripslashes($itemname),
          "OBJECT_SIZE" => "",
          "OBJECT_ICON" => derive_icon($icon)
        ));
        $tpl->parse("ITEM", "item");
        $tpl->parse("DOCUMENTS", "item_list", true);
      }

      else if($item instanceof steam_container && !$item instanceof steam_user)
      {
        $icon = array(
          "object" => $item,
          "bid:collectiontype" => $item->get_attribute("bid:collectiontype"),
          "bid:presentation" => $item->get_attribute("bid:presentation"),
	  "obj_type" => $item->get_attribute("OBJ_TYPE")
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
        $tpl->parse("ITEM", "item");
        $tpl->parse("DOCUMENTS", "item_list", true);
      }

      //parse javascript mark/unmark
      $tpl->parse("MARK", "mark", 1);
      $tpl->parse("UNMARK", "unmark", 1);
    }
  }

  if(!$hascontent) $tpl->parse("DOCUMENTS", "no_content");


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
