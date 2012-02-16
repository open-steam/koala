<?php

  /****************************************************************************
  gallery.php - display content of container and rooms as image gallery.
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

  Author: Moritz Boedicker
  EMail: docmo@upb.de

  ****************************************************************************/

  //include stuff
  require_once("../../config/config.php");
  require_once("$steamapi_doc_root/steam_connector.class.php");
  require_once("$config_doc_root/classes/template.inc");
  require_once("$config_doc_root/classes/doc_content.php");
  #require_once("$config_doc_root/classes/debugHelper.php");

  require_once("$config_doc_root/includes/sessiondata.php");
  require_once("$config_doc_root/includes/derive_icon.php");
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

  $current_room_path = $current_room->get_path(1);
  $current_room_data = $current_room->get_attributes(array(OBJ_NAME, OBJ_DESC), 1);

  //check if user may write in this folder
  $write_allowed = $current_room->check_access_write($steam->get_login_user(), 1);

  //get inventory and inventorys attributes if allowed to
  $allowed = $current_room->check_access_read($steam->get_login_user(), 1);
  $result = $steam->buffer_flush();
  $write_allowed = $result[$write_allowed];
  $allowed = $result[$allowed];
  $current_room_path = $result[$current_room_path];
  $current_room_data = $result[$current_room_data];

  $current_room_display_name = str_replace("'s workarea", "", stripslashes($current_room_data[OBJ_NAME]));
  if (isset($current_room_data[OBJ_DESC]) && $current_room_data[OBJ_DESC] != "")
  {
    $current_room_display_name = $current_room_data[OBJ_DESC];
  }
  $current_room_display_name = str_replace("s workroom.", "", $current_room_display_name);

  //Code for the paged inventory
  $number_of_thumbs = 16;
  
  if(isset($_GET['from']) && $_GET['from'] != '') $from = (int) $_GET['from'];
  else $from = 0;
  if(isset($_GET['to']) && $_GET['to'] != '') $to = (int) $_GET['to'];
  else $to = $number_of_thumbs - 1;
  
  $pic_count = sizeof($current_room->get_inventory());
  
  if($allowed && $current_room instanceof steam_container)
    if($from >= 0 && $to >= $number_of_thumbs - 1)
      $inventory = $current_room->get_inventory_paged($from, $to);
    else
      $inventory = $current_room->get_inventory_paged(0, $number_of_thumbs - 1);
  else
    $inventory = array();

  //******************************************************
  //** Display Stuff
  //******************************************************

  //template stuff
  $tpl = new Template("./templates/$language", "keep");
  $tpl->set_file("content", "gallery.ihtml");
  $tpl->set_block("content", "folder", "DUMMY");
  $tpl->set_block("content", "item", "DUMMY");
  $tpl->set_block("content", "remove_image_button", "DUMMY");
  $tpl->set_var(array(
    "DUMMY" => "",
    "DOCUMENTS" => "",
    "GALLERY_NAME" => $current_room_display_name,
    "GALLERY_DESC" => $gallery_desc,
    "REMOVE_IMAGE_BUTTON" => "",
  ));
  
  //set bounds for pages viewing of pictures
  $prev_from = $from - $number_of_thumbs;
  $prev_to = $to - $number_of_thumbs;
  $next_from = $from + $number_of_thumbs;
  $next_to = $to + $number_of_thumbs;

  $tpl->set_var(array(
    "FROM" => min ($from+1, $pic_count),
    "TO" => min($to+1, $pic_count),
    "PIC_COUNT" => ($pic_count)
  ));
  
  if($from == 0)
    $tpl->set_var(array(
      "BACKLINK" => "<a href=\"\" class=\"pagingleft\"><img alt=\"Zur&uuml;ck\" title=\"Zur&uuml;ck\" src=\"$config_webserver_ip/icons/top_seq_prev_off.gif\"></a>"
    ));
  else
    $tpl->set_var(array(
      "BACKLINK" => "<a href=\"$config_webserver_ip/modules/gallery/gallery.php?from=$prev_from&to=$prev_to\" class=\"pagingleft\"><img alt=\"Zur&uuml;ck\" title=\"Zur&uuml;ck\" src=\"$config_webserver_ip/icons/top_seq_prev_on.gif\"></a>"
    ));

  if($to >= $pic_count-1)
    $tpl->set_var(array(
      "FORWARDLINK" => "<a href=\"\" class=\"pagingright\"><img alt=\"Vor\" title=\"Vor\" src=\"$config_webserver_ip/icons/top_seq_next_off.gif\"></a>" 
	  ));
  else
    $tpl->set_var(array(
	  "FORWARDLINK" => "<a href=\"$config_webserver_ip/modules/gallery/gallery.php?from=$next_from&to=$next_to\" class=\"pagingright\"><img alt=\"Vor\" title=\"Vor\" src=\"$config_webserver_ip/icons/top_seq_next_on.gif\"></a>"
	  ));

  //Caching Code
  $tnr = array();
  foreach($inventory as $item) {
    $tnr[$item->get_id()] = array();
    $tnr[$item->get_id()]["creator"] = $item->get_creator(1);
    $tnr[$item->get_id()]["item_write_access"] = $item->check_access_write($steam->get_login_user(), 1);
    $tnr[$item->get_id()]["item_read_access"] = $item->check_access_read($steam->get_login_user(), 1);
  }
  $result = $steam->buffer_flush();
  $creators = array();
  $item_write_access = array();
  $item_read_access = array();
  foreach($inventory as $item) {
    $creators[$item->get_id()] = $result[$tnr[$item->get_id()]["creator"]];
    $item_write_access[$item->get_id()] = $result[$tnr[$item->get_id()]["item_write_access"]];
    $item_read_access[$item->get_id()] = $result[$tnr[$item->get_id()]["item_read_access"]];
  }
  steam_factory::load_attributes($steam, $inventory, array(OBJ_NAME, OBJ_DESC, OBJ_KEYWORDS, DOC_MIME_TYPE, "bid:description"));

  // If you want to use further Methods of caching e.g. PHP PEARs Cache_Lite
  // insert caching mechanisms in here...
  // below this, the steam connector is no longer used...

  $undisplayed_pic_count = 0;

  for ($i = 0; $i < count($inventory); $i += 1) {
    $item = $inventory[$i];

    // Skip image if rights are insufficient
    if (!$item_read_access[$item->get_id()]) {
      $undisplayed_pic_count++;
      continue;
    }

    $itemname = $item->get_attribute(OBJ_NAME);
    $itemdescription = $item->get_attribute(OBJ_DESC);
    $itemkeywords = implode (", ", $item->get_attribute(OBJ_KEYWORDS));
    $itemmimetype = $item->get_attribute(DOC_MIME_TYPE);
//    $itembiddescription = $item->get_attribute("bid:description");
//    if (!$itembiddescription) $itembiddescription = "";

    // set display name
    if ($itemdescription != "")
      $object_display_name = $itemdescription;
    else
      $object_display_name = stripslashes($itemname);

    $tpl->set_var(array(
      "OBJECT_ID" => $item->get_id(),
      "OBJECT_NAME" => $object_display_name,
      "OBJECT_DESC" => "",
      "OBJECT_KEYWORDS" => $itemkeywords
    ));

    // render a steam_document
    if($item instanceof steam_document)
    {
      //care for documents not to be displayed in the browser
      if($itemmimetype === "image/gif" || $itemmimetype === "image/jpg"
         || $itemmimetype === "image/jpeg" || $itemmimetype === "image/png") {
        $tpl->set_var(array("ITEM_THUMBNAIL_ID" => $item->get_id(), "ITEM_BIGTHUMB_ID" => $item->get_id()));

        if ($i-$undisplayed_pic_count == 0) {
          $tpl->set_var("FIRST_GALLERY_ID", $item->get_id());
        }

        if ($item_write_access[$item->get_id()]) {
          $tpl->parse("REMOVE_IMAGE_BUTTON", "remove_image_button");
        }

        $tpl->parse("ITEM_LINK", "document");
        $tpl->parse("DOCUMENTS", "item", true);
      }
    }
    $tpl->set_var(array("REQUESTCOUNT" => $steam->get_request_count()));
  }

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
