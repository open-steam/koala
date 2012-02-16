<?php

  /****************************************************************************
  treeframe.php - treeframe of the frameset
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
  //require_once("$config_doc_root/classes/debugHelper.php");

  require_once("$config_doc_root/includes/sessiondata.php");
//  require_once("$config_doc_root/includes/derive_icon.php");

  //width of the plus/minus in front of directory
  $image_width = 16;

  //array keys
  define ("CONST_ATTRIB", 1);
  define ("CONST_INVENTORY", 2);
  define ("CONST_CALENDAR", 3);


  //******************************************************
  //** sTeam Stuff
  //******************************************************

#$debugTime_prelogin = microtime(true);
  //login und $steam def.
  $steam = new steam_connector(	$config_server_ip,
  								$config_server_port,
  								$login_name,
  								$login_pwd);

  if( !$steam || !$steam->get_login_status() )
  {
    header("Location: $config_webserver_ip/index.html");
    exit();
  }

#$debugTime = microtime(true);

  // pre-load attributes of actual user
  $login_user = $steam->get_login_user();
  $login_user->get_attributes( array(USER_WORKROOM, USER_CALENDAR, USER_BOOKMARKROOM) );

  $workroom = $login_user->get_workroom();
  $calendar_user = $login_user->get_calendar();

  //current room steam object
  if( (int) $object != 0 ) $current_room = steam_factory::get_object( $steam, $object );
  else $current_room = $workroom;
  $current_room->get_attributes(array ("bid:doctype"));

  if( !$current_room instanceof steam_container  || $current_room instanceof steam_calendar || $current_room->get_attribute("bid:doctype") === "portal")
  {
    $tmp = $current_room->get_environment();
    if($tmp)
      $current_room = $tmp;
  }

  $rootroom = $steam->get_root_room();

  $inventorybuffer_tnr = array();
  $inventorybuffer_access = array();
  //Do the path stuff
  //get path til root node
  $backtrack = array();
  if( $current_room->get_id() != $workroom->get_id() ){
  	  $current_environment = $current_room;
      while( is_object($current_environment) ){
        array_unshift($backtrack, $current_environment);
        $inventorybuffer_tnr[ $current_environment->get_id() ] = $current_environment->get_inventory_raw(CLASS_CONTAINER|CLASS_ROOM|CLASS_EXIT, 1);
        $inventorybuffer_access[$current_environment->get_id()] = $current_environment->check_access_read( $login_user, 1);
        if( $current_environment->get_id() == $rootroom->get_id() ) break;
        else $current_environment = $current_environment->get_environment();
      }
      $path = $backtrack;
  }
  else {
    $inventorybuffer_tnr[ $current_room->get_id() ] = $current_room->get_inventory_raw(CLASS_CONTAINER|CLASS_ROOM|CLASS_EXIT, 1);
    $inventorybuffer_access[$current_room->get_id()] = $current_room->check_access_read( $login_user, 1);
    $backtrack[] = $current_room;
  }
  $path = $backtrack;

  $inventorybuffer = $steam->buffer_flush();
  $treeobjects = array();
  foreach($backtrack as $treenode) {
    $treeobjects = array_merge($treeobjects, $inventorybuffer[ $inventorybuffer_tnr[ $treenode->get_id() ] ]);
  }

  $tree_roots = array();
  $tree_roots[ $workroom->get_id() ] = $workroom;
  if ($login_user->get_attribute(USER_BOOKMARKROOM))
    $tree_roots[ $login_user->get_attribute(USER_BOOKMARKROOM)->get_id() ] = $login_user->get_attribute(USER_BOOKMARKROOM);
  if ($login_user->get_attribute(USER_TRASHBIN))
    $tree_roots[ $login_user->get_attribute(USER_TRASHBIN)->get_id() ] = $login_user->get_attribute(USER_TRASHBIN);
  $tree_roots[ $rootroom->get_id() ] = $rootroom;

  $treeobjects = array_merge($treeobjects, $tree_roots);

  $additional_tnr = array();
  $additionalinventorybuffer_tnr = array();
  $additionalinventorybuffer_access = array();
  foreach($treeobjects as $object) {
    if (is_object($object) ) {
      $object->get_attributes(array(OBJ_NAME, OBJ_DESC, OBJ_TYPE, "bid:presentation", "bid:hidden", "bid:collectiontype", "bid:doctype"), 1);
      $additional_tnr[ $object->get_id() ] = array();
      if ($object instanceof steam_container) {
        $additionalinventorybuffer_tnr[ $object->get_id() ] = $object->get_inventory_raw(CLASS_CONTAINER|CLASS_ROOM|CLASS_EXIT, 1);
        $additionalinventorybuffer_access[$object->get_id()] = $object->check_access_read( $login_user, 1);
      }
      if ($object instanceof steam_exit) {
         $additional_tnr[ $object->get_id() ]["exit"] = $object->get_exit(1);
      }
    }
  }
  $additional_data = $steam->buffer_flush();

  $exit_tnr = array();
  foreach($treeobjects as $object) {
    if ($object instanceof steam_exit) {
      $object = $additional_data[ $additional_tnr[ $object->get_id() ]["exit"] ];
      if (is_object($object)) {
        $object->get_attributes(array(OBJ_NAME, OBJ_DESC, OBJ_TYPE, "bid:presentation", "bid:hidden", "bid:collectiontype", "bid:doctype"), 1);
        $exit_tnr[ $object->get_id() ] = array();
        $exit_tnr[ $object->get_id() ]["access"] = $object->check_access_read( $login_user, 1);
        if ($object instanceof steam_container)
          $exit_tnr[ $object->get_id() ][ "inventory" ] = $object->get_inventory_raw(CLASS_CONTAINER|CLASS_ROOM|CLASS_EXIT, 1);
      }
    }
  }
  $exit_data = $steam->buffer_flush();



  //******************************************************
  //** Display Stuff
  //******************************************************

  //get root node
  $root_env = array_shift($path);

  //template stuff
  $tpl = new Template("./templates/$language", "keep");
  $tpl->set_file("tree", "treeframe.ihtml");
  $tpl->set_block("tree", "grey_name", "DUMMY");
  $tpl->set_block("tree", "openbranch", "DUMMY");
  $tpl->set_block("tree", "closedbranch", "DUMMY");
  $tpl->set_block("tree", "emptybranch", "DUMMY");
  $tpl->set_block("tree", "openfolder", "DUMMY");
  $tpl->set_block("tree", "closedfolder", "DUMMY");
  $tpl->set_block("tree", "openfolder_index", "DUMMY");
  $tpl->set_block("tree", "closedfolder_index", "DUMMY");
  $tpl->set_block("tree", "sequence", "DUMMY");
  $tpl->set_block("tree", "cluster", "DUMMY");
  $tpl->set_block("tree", "gallery", "DUMMY");
  $tpl->set_block("tree", "exit", "DUMMY");
  $tpl->set_block("tree", "trashbin", "DUMMY");
  $tpl->set_block("tree", "user_calendar", "DUMMY");
 # $tpl->set_block("tree", "group_calendar", "DUMMY");
  $tpl->set_block("tree", "folder", "DUMMY");
  $tpl->set_block("tree", "separator", "DUMMY");
  $tpl->set_var(array(
    "DUMMY" => "",
    "SPACEHOLDER" => 0,
    "FOLDER_ID" => $root_env->get_id(),
    "FOLDER_NAME" => ""
  ));


  //output drives
  $separator = true;

  foreach($tree_roots as $obj_key => $obj )
  {
    $folder_display_name = str_replace("'s workarea", "", stripslashes( $obj->get_attribute(OBJ_NAME) ));
    if( $obj->get_attribute(OBJ_DESC) != "")
    {
      $folder_display_name = $obj->get_attribute(OBJ_DESC);
    }
    $folder_display_name = str_replace("s workroom.", "", $folder_display_name);
    $folder_display_name = str_replace("s workroom", "", $folder_display_name);
    $folder_display_name = preg_replace("/.*'s bookmarks/", "Lesezeichen", $folder_display_name);
    if ($obj instanceof steam_trashbin) $folder_display_name = "Papierkorb";

    //set template vars
    $tpl->set_var(array(
      "SPACEHOLDER" => "0",
      "FOLDER_ID" => $obj_key,
      "FOLDER_NAME" => $folder_display_name
    ));
    $index = ( $obj->get_attribute("bid:presentation") === "index")?"_index":"";
    $state = ( $obj_key == $root_env->get_id() )?"open":"closed";

    //parse group calendar icon, when a group is to be parsed out
/*
    if( $login_user->get_name() != "guest" && $obj_key == $login_user->get_workroom()->get_id() )
    {
      $tpl->set_var("USER_CALENDAR_ID", $calendar_user->get_id() );
      $tpl->parse("GROUP_CALENDAR", "user_calendar");
    }
    else $tpl->parse("GROUP_CALENDAR", "");
*/

    //parse current folder
    if(( $obj->get_attribute("bid:collectiontype") === "sequence"))
      $icon = "sequence";
    else if(( $obj->get_attribute("bid:collectiontype") === "cluster"))
      $icon = "cluster";
    else if(( $obj->get_attribute("bid:collectiontype") === "gallery"))
      $icon = "gallery";
    else if ($obj instanceof steam_trashbin)
      $icon = "trashbin";
    else
      $icon = $state . "folder" . $index;

    $targetid = $obj->get_id();
    $invcount = count($additional_data[ $additionalinventorybuffer_tnr[$targetid] ]);
    $tpl->parse("OPEN_CLOSED_FOLDER", ((  $invcount > 0)?$state:"empty") . "branch");
    $tpl->parse("OPEN_CLOSED_FOLDER", $icon, true);
    $tpl->parse("FOLDERS", "folder", true);

    //output tree
    $tpl->set_var("GROUP_CALENDAR", "");
    if( $obj->get_id() == $root_env->get_id() )
    {
      parse_tree($root_env, $image_width);
    }

    //separator between user home and groups
    if($separator)
    {
      $tpl->parse("FOLDERS", "separator", true);
      $separator = false;
    }

  }

  $steam->disconnect();

  out();

  function out()
  {
    //parse all out
    global $tpl;
    $tpl->parse("OUT", "tree");
    $tpl->p("OUT");
  }

exit;

  //********************************************************
  //display tree
  //********************************************************
  function parse_tree($parent_env, $spaceholder)
  {
    global $steam;
    global $current_room;
    global $image_width;
    global $path;
    global $tpl;
    global $treeview_mini;
    global $show_hidden;
    global $inventorybuffer;
    global $inventorybuffer_tnr;
    global $treeobjects;
    global $inventorybuffer_access;
    global $additional_data;
    global $additional_tnr;
    global $exit_data;
    global $exit_tnr;
    global $additionalinventorybuffer_access;
    global $additionalinventorybuffer_tnr;


    $allowed = $inventorybuffer[ $inventorybuffer_access[ $parent_env->get_id() ] ];
    if( $allowed ) {
        $inventories = $inventorybuffer[ $inventorybuffer_tnr[ $parent_env->get_id() ] ];
    }
    else $inventories = array();

    $next_env = array_shift($path);

    //output current inventory depth
    foreach($inventories as $item)
    {
    	#$item = steam_factory::get_object( $steam, $item->get_id() );

        //dont show portals and questionaries
      	if( $item->get_attribute(OBJ_TYPE) === "container_portal_bid" || $item->get_attribute("bid:doctype") === "portal" || $item->get_attribute("bid:doctype") === "questionary")
        	continue;

		// find display name
	    if( $item->get_attribute(OBJ_DESC) != "")
	      $object_display_name = str_replace("s workroom.", "", $item->get_attribute(OBJ_DESC));
	    else $object_display_name = stripslashes( str_replace("'s workarea", "", $item->get_attribute(OBJ_NAME)) );

      	//set template vars
      	if( $item instanceof steam_exit && is_object($additional_data[ $additional_tnr[ $item->get_id() ]["exit"] ]  ) )
      		$folder_id = $additional_data[ $additional_tnr[ $item->get_id() ]["exit"] ]->get_id();
      	else
      		$folder_id = $item->get_id();

      	$tpl->set_var(array(
        	"SPACEHOLDER" => $spaceholder,
        	"FOLDER_ID" => $folder_id,
        	"FOLDER_NAME" => $object_display_name,
        	"FOLDER_NAME_GREY" => $object_display_name#,
        	#"GROUP_CALENDAR" => ""
      	));

      	$index = ( $item->get_attribute("bid:presentation") === "index")?"_index":"";
      	$state = ( is_object($next_env) && $item->get_id() == $next_env->get_id() )?"open":"closed";

      	//if item is hidden, either show it in grey color or skip
      	if( $item->get_attribute("bid:hidden") ) {
        	if($show_hidden && ($item->get_attribute("bid:hidden") != "hide_always"))
          		$tpl->parse("FOLDER_NAME", "grey_name");
        	else continue;
      	}

      	//parse current folder
      	if( $state == "open" && $treeview_mini || !$treeview_mini || $parent_env->get_id() == $current_room->get_id() )
      	{
        	if(( $item->get_attribute("bid:collectiontype") === "sequence" ))
          		$icon = "sequence";
        	else if(( $item->get_attribute("bid:collectiontype") === "cluster"))
          		$icon = "cluster";
            else if(( $item->get_attribute("bid:collectiontype") === "gallery"))
                $icon = "gallery";
            else if( $item instanceof steam_exit )
          		$icon = "exit";
            else if(( $item->get_attribute("OBJ_TYPE") === "LARS_DESKTOP"))
            	continue;
        	else
          		$icon = $state . "folder" . $index;

          if (!($item instanceof steam_exit)) {
            if( $additional_data[ $additionalinventorybuffer_access[ $item->get_id() ] ] && count($additional_data[ $additionalinventorybuffer_tnr[ $item->get_id() ] ]) > 0 )
              $fInventory = $state;
            else
              $fInventory = "empty";
          }
          else {
            if( $exit_data[ $exit_tnr[ $folder_id ]["access"] ] && count($exit_data[ $exit_tnr[ $folder_id ]["inventory"] ]) > 0 )
              $fInventory = $state;
            else
              $fInventory = "empty";
          }

        	$tpl->parse("OPEN_CLOSED_FOLDER", $fInventory . "branch");
        	$tpl->parse("OPEN_CLOSED_FOLDER", $icon, true);
        	$tpl->parse("FOLDERS", "folder", true);
      	}

      //process subfolders if folder is open
      if($state == "open")
      	parse_tree($next_env, $spaceholder + $image_width);

    }
  }

?>