<?php

  /****************************************************************************
  favourites_search.php - search for favourite groups and users
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

  Author: Thorsten Schäfer
  EMail: uni@thorstenschaefer.name

  ****************************************************************************/

//include stuff
require_once("./config/config.php");
require_once("$steamapi_doc_root/steam_connector.class.php");
require_once("$config_doc_root/classes/template.inc");
//require_once("$config_doc_root/classes/debugHelper.php");
require_once("$config_doc_root/includes/sessiondata.php");
require_once("$steamapi_doc_root/modules/searching.class.php");
require_once("$steamapi_doc_root/modules/search_define.class.php");

//******************************************************
//** Precondition
//******************************************************
$action = (isset($_POST["action"]))?$_POST["action"]:"";

//******************************************************
//** sTeam Stuff
//******************************************************
$steam = new steam_connector(
  $config_server_ip,
  $config_server_port,
  $login_name,
  $login_pwd);

if(!$steam || !$steam->get_login_status())
{
  header("Location: $config_webserver_ip/index.html");
  exit();
}

/** log-in user */
$steamUser =  $steam->get_login_user();

/** get buddies */
$buddies = $steamUser->get_buddies(1);
$result = $steam->buffer_flush();
$buddies = $result[$buddies];
$buddies_user = array();
$buddies_group = array();
$buddies_user_name = array();
$buddies_group_name = array();

foreach ($buddies as $buddy) {
  $id = $buddy->get_id();
  switch($buddy->get_type()) {
    case CLASS_USER:
      $buddies_user[$id] = $buddy;
      $buddies_user_name[$id] = $buddy->get_name(1);
      break;
    case CLASS_GROUP:
      $buddies_group[$id] = $buddy;
      $buddies_group_name[$id] = $buddy->get_groupname(1);
      break;
  }
}
$result = $steam->buffer_flush();
foreach ($buddies_user_name as $id=>$val) {
  $buddies_user_name[$id] = $result[$buddies_user_name[$id]];
}
foreach ($buddies_group_name as $id=>$val) {
  $buddies_group_name[$id] = $result[$buddies_group_name[$id]];
}

// sort favourites
natcasesort($buddies_user_name);
natcasesort($buddies_group_name);

//******************************************************
//** Save
//******************************************************
if ($action == "save") {
  $new_buddies = array();
  foreach ($buddies as $buddy) {
    $id = $buddy->get_id();
    if (!isset($_POST["remove_".$id])) {
      $new_buddies[] = $buddy;
    }
  }
  $steamUser->set_buddies($new_buddies, 1);
  $steam->buffer_flush();
  $steam->disconnect();
	echo("<html>\n<body onload='window.location.href=\"$config_webserver_ip/favourites_show.php\";'>\n</body>\n</html>");
	exit;
}

//Logout & Disconnect
$steam->disconnect();

//******************************************************
//** Display Stuff
//******************************************************
//template stuff
$tpl = new Template("./templates/$language", "keep");
$tpl->set_file(array(
  "content" => "favourites_show.ihtml"
));
$tpl->set_block("content", "button_label_show_favourites", "DUMMY");
$tpl->set_block("content", "result_entry", "DUMMY");
$tpl->set_block("content", "no_favourites", "DUMMY");
$tpl->set_var(array(
  "DUMMY" => "",
  "ERROR_FEEDBACK" => "",
  "USER_ENTRY" => "",
  "GROUP_ENTRY" => ""
));

$loopCount = 0;
foreach ($buddies_user_name as $id=>$buddy) {
  $tpl->set_var(array(
    "OBJ_ID" => $id,
    "OBJ_NAME" => $buddy,
    "OBJ_TYPE" => "user",
    "SEARCH_ENTRY_MOD2" => $loopCount%2
  ));
  $tpl->parse("USER_ENTRY", "result_entry", True);
  $loopCount += 1;
}
if ($loopCount == 0) {
  $tpl->parse("USER_ENTRY", "no_favourites", False);
}

$loopCount = 0;
foreach ($buddies_group_name as $id=>$buddy) {
  $tpl->set_var(array(
    "OBJ_ID" => $id,
    "OBJ_NAME" => $buddy,
    "OBJ_TYPE" => "group",
    "SEARCH_ENTRY_MOD2" => $loopCount%2
  ));
  $tpl->parse("GROUP_ENTRY", "result_entry", True);
  $loopCount += 1;
}
if ($loopCount == 0) {
  $tpl->parse("GROUP_ENTRY", "no_favourites", False);
}

//parse all out
$tpl->parse("OUT", "content");
$tpl->p("OUT");
?>
