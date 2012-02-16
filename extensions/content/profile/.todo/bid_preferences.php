<?php

  /****************************************************************************
  preferences.php - display the preferences of a user
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
  require_once("./classes/template.inc");
  require_once("$config_doc_root/config/language_map.php");
//  require_once("./classes/debugHelper.php");

  require_once("./includes/sessiondata.php");

  $action = (isset($_POST["action"]))?$_POST["action"]:"";


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

  if ($action == "change")
  {
    $user_prefs = array(
      OBJ_DESC => $_POST["USER_DESCRIPTION"],
      USER_ADRESS => $_POST["USER_ADRESS"],
      USER_EMAIL => $_POST["USER_EMAIL"],
      USER_LANGUAGE => $_POST["user_language"],
      "bid:user_callto" => $_POST["user_callto"],
      "bid:user_im_adress" => $_POST["user_im_adress"],
      "bid:user_im_protocol" => $_POST["user_im_protocol"],
      "bid:treeview_mini" => $_POST["treeview_mini"],
      "bid:show_hidden" => $_POST["show_hidden"]
    );
    $user_prefs["bid:treeview"] = (isset($_POST["treeview"]))?$_POST["treeview"]:"on";
    
	$steam->get_login_user()->set_attributes( $user_prefs );
  }

  $_SESSION["treeview"] = ($steam->get_login_user()->get_attribute("bid:treeview") == "on")?true:false;
  $_SESSION["treeview_mini"] = ($steam->get_login_user()->get_attribute("bid:treeview_mini") == "on")?true:false;
  $_SESSION["show_hidden"] = ($steam->get_login_user()->get_attribute("bid:show_hidden") == "on")?true:false;
  $_SESSION["language"] = $language_map_counter[ $steam->get_login_user()->get_attribute(USER_LANGUAGE) ];

  //after save action and disconnect close window and update the opener
  if($action && $action == "change")
  {
    echo("<html>\n<body onload='javascript:window.opener.top.location.reload();");
    echo("window.close();'>\n</body>\n</html>");
    exit;
  }

  //******************************************************
  //** Display Stuff
  //******************************************************

  //template stuff
  $tpl = new Template("./templates/$language", "keep");
  $tpl->set_file("content", "preferences.ihtml");
  $tpl->set_var(array(
    "USER_NAME" => $steam->get_login_user()->get_name(),
    "USER_DESCRIPTION" => $steam->get_login_user()->get_attribute(OBJ_DESC),
    "USER_FIRSTNAME" => $steam->get_login_user()->get_attribute(USER_FIRSTNAME),
    "USER_LASTNAME" => $steam->get_login_user()->get_attribute(USER_FULLNAME),
    "USER_ADRESS" => $steam->get_login_user()->get_attribute(USER_ADRESS),
    "USER_EMAIL" => $steam->get_login_user()->get_attribute(USER_EMAIL),
    "USER_CALLTO" => $steam->get_login_user()->get_attribute("bid:user_callto"),
    "USER_IM_ADRESS" => $steam->get_login_user()->get_attribute("bid:user_im_adress")
//    "USER_ICON" => $user_data[OBJ_ICON]->id
  ));

  if( $steam->get_login_user()->get_attribute(USER_ADRESS) === 0 )
    $tpl->parse("USER_ADRESS", "");
  if( $steam->get_login_user()->get_attribute(USER_EMAIL) === 0 )
    $tpl->parse("USER_EMAIL", "");
  if( $steam->get_login_user()->get_attribute("bid:user_callto") === 0)
    $tpl->parse("USER_CALLTO", "");
  if( $steam->get_login_user()->get_attribute("bid:user_im_adress") === 0) {
    $tpl->parse("USER_IM_ADRESS", "");
  }

  $tpl->set_var(array(
    "AIM_SELECTED" => (($steam->get_login_user()->get_attribute("bid:user_im_protocol") === 'aim')?"SELECTED":""),
    "XMPP_SELECTED" => (($steam->get_login_user()->get_attribute("bid:user_im_protocol") === 'xmpp')?"SELECTED":""),
    "SKYPE_SELECTED" => (($steam->get_login_user()->get_attribute("bid:user_im_protocol") === 'skype')?"SELECTED":"")
  ));

  $tpl->set_var(array(
    "LANGUAGE1_SELECTED" => (($steam->get_login_user()->get_attribute(USER_LANGUAGE) === 'german')?"SELECTED":""),
    "LANGUAGE2_SELECTED" => (($steam->get_login_user()->get_attribute(USER_LANGUAGE) === 'english')?"SELECTED":""),
    "LANGUAGE3_SELECTED" => (($steam->get_login_user()->get_attribute(USER_LANGUAGE) === 'french')?"SELECTED":"")
  ));

  $tpl->set_var(array(
    "HIDDEN_ON" => (($steam->get_login_user()->get_attribute("bid:show_hidden") === 'on')?"CHECKED":""),
    "HIDDEN_OFF" => ((!$steam->get_login_user()->get_attribute("bid:show_hidden") || $steam->get_login_user()->get_attribute("bid:show_hidden") === 'off')?"CHECKED":"")
  ));

  $tpl->set_var(array(
    "TREEVIEW_MINI" => ((!$steam->get_login_user()->get_attribute("bid:treeview_mini") || $steam->get_login_user()->get_attribute("bid:treeview_mini") === 'on')?"CHECKED":""),
    "TREEVIEW_MAXI" => (($steam->get_login_user()->get_attribute("bid:treeview_mini") === 'off')?"CHECKED":""),
    "TREEVIEW_OFF" => (($steam->get_login_user()->get_attribute("bid:treeview") === 'off')?"CHECKED":"")
  ));

  $tpl->parse("BUTTON_LABEL", "submit_button");
  
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