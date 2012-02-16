<?php

  /****************************************************************************
  cluster.php - displays all documents of a container one after another
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
          Bastian Schröder <bastian@upb.de>

  ****************************************************************************/

  //include stuff
  require_once("./config/config.php");
  require_once("$steamapi_doc_root/steam_connector.class.php");
  require_once("$config_doc_root/classes/template.inc");
  require_once("$config_doc_root/classes/doc_content.php");
//  require_once("$config_doc_root/classes/debugHelper.php");

  require_once("$config_doc_root/includes/sessiondata.php");
  require_once("$config_doc_root/includes/derive_icon.php");
  require_once("$config_doc_root/includes/derive_menu.php");
  require_once("$config_doc_root/includes/derive_url.php");


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
  $current_room = ($object != 0)?steam_factory::get_object( $steam, $object ):$steam->get_login_user()->get_workroom();

  //get inventory and inventorys attributes if allowed to
  $allowed = $current_room->check_access_read( $steam->get_login_user() );
  if($allowed)
    $inventory = $current_room->get_inventory();
  else
    $inventory = array();

    
  //******************************************************
  //** Display Stuff
  //******************************************************

  //template stuff
  $tpl = new Template("./templates/$language", "keep");
  $tpl->set_file("content", "cluster.ihtml");
  $tpl->set_block("content", "document", "DUMMY");
  $tpl->set_block("content", "link", "DUMMY");
  $tpl->set_block("content", "folder", "DUMMY");
  $tpl->set_var(array(
    "DUMMY" => "",
    "MENU" => derive_menu("contentframe", $current_room)
  ));


  //display directory
  $content = false;
  foreach($inventory as $item)
  {
    //get values in handsome format
    $attributes = $item->get_attributes( array(OBJ_NAME, DOC_MIME_TYPE, DOC_EXTERN_URL, "bid:doctype", "bid:collectiontype", "bid:presentation", "bid:hidden") );

    //skip hidden
    if(  $item->get_attribute("bid:hidden") != 0 )
      continue;


    //parse out doc content or appropriate link
    if( $item instanceof steam_document )
    {
      $content = new doc_content($steam, $item);
      $tpl->set_var("DOCUMENT_CONTENT", $content->get_content($config_webserver_ip) );
      $tpl->parse("DOCUMENTS", "document", true);
    }

    //parse out all non content things
    else
    {
      $attributes["object"] = $item;

      $tpl->set_var(array(
        "OBJECT_ID" => $item->get_id(),
        "OBJECT_NAME" => stripslashes( $item->get_attribute(OBJ_NAME) )
      ));


      //parse out link
      if( $item instanceof steam_docextern )
      {
        $url = derive_url( $item->get_attribute(DOC_EXTERN_URL) );

        $tpl->set_var("OBJECT_LINK", $url);
        $tpl->parse("DOCUMENTS", "link", true);
      }

      //parse out everything else
      else
      {
        $tpl->set_var(array(
          "OBJECT_NAME" => $item->get_attribute(OBJ_NAME),
          "OBJECT_ICON" => derive_icon($attributes)
        ));
        $tpl->parse("DOCUMENTS", "folder", true);
      }

    }
  }

  //Logout & Disconnect
  $steam->disconnect();

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