<?php

  /****************************************************************************
  idcard.php - display the idcard of a user or a group
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

  Author: Henrik Beige, Harald Selke
  EMail: hebeige@gmx.de, hase@uni-paderborn.de

  ****************************************************************************/

  //include stuff
  require_once("./config/config.php");
  require_once("$steamapi_doc_root/steam_connector.class.php");
  require_once("$config_doc_root/classes/template.inc");
//  require_once("$config_doc_root/classes/debugHelper.php");

  require_once("$config_doc_root/includes/sessiondata.php");

  //******************************************************
  //** Precondition
  //******************************************************

  $action = (isset($_POST["action"]))?$_POST["action"]:"";
  $user_id = (isset($_GET["user"]))?$_GET["user"]:((isset($_POST["user"]))?$_POST["user"]:0);
  $group_id = (isset($_GET["group"]))?$_GET["group"]:((isset($_POST["group"]))?$_POST["group"]:0);

  //******************************************************
  //** sTeam Stuff
  //******************************************************

  $steam = new steam_connector($config_server_ip, $config_server_port, $login_name, $login_pwd);
  if(!$steam || !$steam->get_login_status())
  {
    header("Location: $config_webserver_ip/index.html");
    exit();
  }

  if ($login_name == "guest") {
    print("Visitenkarten können nur von angemeldeten Benutzern eingesehen werden.");
    return;
  }
  
  if ($user_id !== 0) {
    $user = steam_factory::get_user($steam, $user_id);
    $user_data = $user->get_attributes(array(OBJ_NAME, OBJ_DESC, USER_FIRSTNAME, USER_FULLNAME, USER_EMAIL, USER_ADRESS, OBJ_ICON, "bid:user_callto", "bid:user_im_adress", "bid:user_im_protocol"));
//    $user_email_forwarding = $user->get_email_forwarding();
  }
  else if ($group_id !== 0) {
    $group = steam_factory::get_group($steam, $group_id);
    $group_data = $group->get_attributes(array(OBJ_NAME, OBJ_DESC));
  }

  $user_favourites = $steam->get_login_user()->get_buddies();

  if ($action == "save") {
    if (count($user_favourites) == 0)
      $user_favourites = array();
    if ($user_id !== 0)
      array_push($user_favourites, $user);
    else if ($group_id !== 0)
      array_push($user_favourites, $group);
    $steam->get_login_user()->set_attribute("USER_FAVOURITES", $user_favourites);
  }

  //Logout & Disconnect
  $steam->disconnect();

  //******************************************************
  //** Display Stuff
  //******************************************************

  //template stuff
  $tpl = new Template("./templates/$language", "keep");
  $tpl->set_file("content", "idcard.ihtml");
  $tpl->set_block("content", "user_information", "DUMMY");
  $tpl->set_block("content", "group_information", "DUMMY");
  $tpl->set_block("content", "button_add_favourite_active", "DUMMY");
  $tpl->set_block("content", "button_add_favourite_inactive", "DUMMY");
  
  if ($user_id !== 0) {
    if($steam->get_login_user()->is_buddy($user) || $login_name == $user_id)
      $tpl->parse("BUTTON_ADD_FAVOURITE", "button_add_favourite_inactive");
    else
      $tpl->parse("BUTTON_ADD_FAVOURITE", "button_add_favourite_active");

    $tpl->set_var(array(
      "DUMMY" => "",
      "USER_NAME" => $user_data[OBJ_NAME],
      "USER_DESCRIPTION" => $user_data[OBJ_DESC],
      "USER_FIRSTNAME" => $user_data[USER_FIRSTNAME],
      "USER_FULLNAME" => $user_data[USER_FULLNAME],
      "USER_EMAIL" => $user_data[USER_EMAIL],
      "USER_ADRESS" => str_replace (array("\r\n", "\n", "\r"), ", ", $user_data[USER_ADRESS]),
      "USER_ICON" => $user_data[OBJ_ICON]->get_id(),
      "USER_CALLTO" => $user_data["bid:user_callto"],
      "USER_IM_ADRESS" => $user_data["bid:user_im_adress"],
      "USER_IM_PROTOCOL" => $user_data["bid:user_im_protocol"]
    ));

    if($user_data[USER_EMAIL] === 0)
      $tpl->parse("USER_EMAIL", "");
    if($user_data["bid:user_callto"] === 0)
      $tpl->parse("USER_CALLTO", "");
    if($user_data["USER_IM_ADRESS"] === 0) {
      $tpl->parse("USER_IM_ADRESS", "");
      $tpl->parse("USER_IM_PROTOCOL", "");
    }

    $tpl->parse("OBJECT_INFORMATION", "user_information");
  }
  else if ($group_id !== 0) {
    if($steam->get_login_user()->is_buddy($group))
      $tpl->parse("BUTTON_ADD_FAVOURITE", "button_add_favourite_inactive");
    else
      $tpl->parse("BUTTON_ADD_FAVOURITE", "button_add_favourite_active");

    $tpl->set_var(array(
      "DUMMY" => "",
      "GROUP_NAME" => $group_data[OBJ_NAME],
      "GROUP_DESCRIPTION" => $group_data[OBJ_DESC],
    ));

    $tpl->parse("OBJECT_INFORMATION", "group_information");
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
