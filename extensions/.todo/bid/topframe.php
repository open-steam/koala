<?php

/****************************************************************************
 topframe.php - topframe of the frameset
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
 bastian Schröder <bastian@upb.de>

 ****************************************************************************/

//include stuff
require_once("./config/config.php");
require_once("$steamapi_doc_root/steam_connector.class.php");
require_once("$config_doc_root/classes/template.inc");
//require_once("$config_doc_root/classes/debugHelper.php");

require_once("$config_doc_root/includes/sessiondata.php");
require_once("$config_doc_root/includes/derive_menu.php");
require_once("$config_doc_root/includes/derive_logoname.php");

//******************************************************
//** Precondition
//******************************************************

$workroom_id = (isset($_GET["workroom"]))?$_GET["workroom"]:"";
$environment_id = (isset($_GET["environment"]))?$_GET["environment"]:"";

$sequence_first = (isset($_GET["seq_first"]))?$_GET["seq_first"]:"";
$sequence_prev = (isset($_GET["seq_prev"]))?$_GET["seq_prev"]:"";
$sequence_next = (isset($_GET["seq_next"]))?$_GET["seq_next"]:"";
$sequence_last = (isset($_GET["seq_last"]))?$_GET["seq_last"]:"";
$sequence = ($sequence_first != "")?$sequence_first + $sequence_prev + $sequence_next + $sequence_last:"";

 
//login und $steam def. in "./includes/login.php"
$steam = new steam_connector($config_server_ip, $config_server_port, $login_name, $login_pwd);

$current_object = steam_factory::get_object( $steam, $_GET["object"] );
$path = $current_object->get_path();
$user = steam_factory::get_user($steam, $login_name);
$user_data = $user->get_attributes(array(USER_FIRSTNAME, USER_FULLNAME, "LARS_DESKTOP"));
$attributes = $current_object->get_attributes(array(
  OBJ_NAME, 
  OBJ_DESC, 
  "OBJ_TYPE",
  "bid:collectiontype"
  ),1);
$write_allowed = $current_object->check_access_write($user, 1);
$result = $steam->buffer_flush();
$attributes = $result[$attributes];
$write_allowed = $result[$write_allowed];

$current_object_is_new_portal = strcmp($attributes["OBJ_TYPE"],
  "container_portal_bid") == 0;
$current_object_is_gallery = ($current_object instanceof steam_container) &&
  (strcmp($attributes["bid:collectiontype"], "gallery") == 0);


if ($user_data[USER_FIRSTNAME] !== 0 && $user_data[USER_FULLNAME] !== 0)
  $username = $user_data[USER_FIRSTNAME] . ' ' . $user_data[USER_FULLNAME];
else
  $username = $login_name;

if ($user_data["LARS_DESKTOP"] !== 0)
  $desktop = $user_data["LARS_DESKTOP"];
else
  $desktop = "";

//******************************************************
//** Display Stuff
//******************************************************

//template stuff
$tpl = new Template("./templates/$language", "keep");
$tpl->set_file("content", "topframe.ihtml");
$tpl->set_block("content", "sequence", "DUMMY");
$tpl->set_block("content", "print_logo", "DUMMY");
$tpl->set_block("content", "print_menu", "DUMMY");
$tpl->set_block("content", "print_document_menu_edit", "DUMMY");
$tpl->set_block("content", "print_document_menu", "DUMMY");
$tpl->set_block("content", "print_image_menu", "DUMMY");
$tpl->set_block("content", "print_gallery_menu_upload", "DUMMY");
$tpl->set_block("content", "print_gallery_menu", "DUMMY");
$tpl->set_block("content", "button_logout", "DUMMY");
$tpl->set_block("content", "button_login", "DUMMY");
$tpl->set_block("content", "button_desktop", "DUMMY");
$tpl->set_block("sequence", "sequence_entry_on", "DUMMY");
$tpl->set_block("sequence", "sequence_entry_off", "DUMMY");
$tpl->set_block("sequence", "sequence_first_on", "DUMMY");
$tpl->set_block("sequence", "sequence_first_off", "DUMMY");
$tpl->set_block("sequence", "sequence_prev_on", "DUMMY");
$tpl->set_block("sequence", "sequence_prev_off", "DUMMY");
$tpl->set_block("sequence", "sequence_next_on", "DUMMY");
$tpl->set_block("sequence", "sequence_next_off", "DUMMY");
$tpl->set_block("sequence", "sequence_last_on", "DUMMY");
$tpl->set_block("sequence", "sequence_last_off", "DUMMY");
$tpl->set_var(array(
    "DUMMY" => "",
    "SEQUENCE" => "",
    "TOP_FRAME_MENU"=> "",
));

$titles = derive_menu_titles("contentframe", $_GET["doctype"]);
$current_url = $_SERVER['HTTP_REFERER'];

$tpl->set_var(array(
     "OBJECT_ID" => $object,
     "FOLDER_HOME_ID" => $workroom_id,
     "FOLDER_PARENT_ID" => $environment_id,
     "MENU_TITLE_NEW" => $titles[0],
     "MENU_TITLE_EDIT" => $titles[1],
     "LOGIN_NAME" => $username,
     "CURRENT_URL" => $current_url,
     "LOGOFILENAME" => derive_logoname($path),
     "LOGO" => "",
     "USER_ADMINISTRATION" => $config_useradmin_ip,
     "USER_MANUAL" => $config_usermanual_ip,
     "DUMMY" => "",
     "LOGINBUTTON" => "",
     "LOGOUTBUTTON" => "",
     "DESKTOP" => ""
     ));

     if ($current_object instanceof steam_messageboard ||
       $current_object_is_new_portal) 
     {
       // don't display menus in forum and new portal, but only
       // the icon to edit the properties
       $tpl->parse("TOP_FRAME_MENU", "print_image_menu");
     }
     else if ($current_object_is_gallery) {
       if ($write_allowed) {
         $tpl->parse("TOP_FRAME_MENU", "print_gallery_menu_upload");
       }
       $tpl->parse("TOP_FRAME_MENU", "print_gallery_menu", true);
     } 
     //set menu for document
     else if($_GET["doctype"] == "document")
     {
       //login und $steam def. in "./includes/login.php"
       $steam = new steam_connector( $config_server_ip,
       $config_server_port,
       $login_name,
       $login_pwd);
       if($steam) {
         $myroom = steam_factory::get_object( $steam, $_GET["object"] );
         $mime_type =  $myroom->get_attribute( DOC_MIME_TYPE );
         if($mime_type === "text/html" || $mime_type === "text/plain" || $mime_type === "text/css") {
           if ($write_allowed) {
             $tpl->parse("TOP_FRAME_MENU", "print_document_menu_edit");
           }
           $tpl->parse("TOP_FRAME_MENU", "print_document_menu", true);
         }
         if($mime_type === "image/gif" || $mime_type === "image/jpg" || $mime_type === "image/jpeg" || $mime_type === "image/png") {
           $tpl->parse("TOP_FRAME_MENU", "print_image_menu");
         }
       }
     }
     else {
      // print menu
      $tpl->parse("TOP_FRAME_MENU", "print_menu");
     }
     $tpl->parse("LOGO", "print_logo");
      
     if ( $steam->get_login_user()->get_name() == "guest" )
     $tpl->parse("LOGINBUTTON", "button_login");
     else $tpl->parse("LOGOUTBUTTON", "button_logout");

     if ( $desktop !== "" )
     $tpl->parse("DESKTOP", "button_desktop");

     //output sequence images
     if(gettype($sequence) == "integer")
     {
      $tpl->set_var(array(
      "SEQUENCE_ENTRY_ID" => $sequence_first,
      "SEQUENCE_FIRST_ID" => $sequence_first,
      "SEQUENCE_PREV_ID" => $sequence_prev,
      "SEQUENCE_NEXT_ID" => $sequence_next,
      "SEQUENCE_LAST_ID" => $sequence_last
      ));
      if($sequence_first > $sequence_prev)
      $tpl->parse("SEQUENCE_ENTRY", "sequence_entry_on");
      else
      $tpl->set_var("SEQUENCE_ENTRY", "");
      $tpl->parse("SEQUENCE_FIRST", "sequence_first_" . (($sequence_first != 0 && $sequence_prev != 0)?"on":"off"));
      $tpl->parse("SEQUENCE_PREV", "sequence_prev_" . (($sequence_prev != 0)?"on":"off"));
      $tpl->parse("SEQUENCE_NEXT", "sequence_next_" . (($sequence_next != 0)?"on":"off"));
      $tpl->parse("SEQUENCE_LAST", "sequence_last_" . (($sequence_last != 0)?"on":"off"));

      $tpl->parse("SEQUENCE", "sequence");
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
