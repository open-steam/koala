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
$searchString = (isset($_POST["searchString"]))?$_POST["searchString"]:"";
$searchType = (isset($_POST["searchType"]))?$_POST["searchType"]:"searchUser";

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

$searchResult = array();
$min_search_string_count = 4;
if ($action == "search") {
  $searchString = trim($searchString);

  if (strlen($searchString) < $min_search_string_count) {
    $error[] = "error_search_string_too_short";
  }
  else
  {
    /* prepare search string */
    $modSearchString = $searchString;
    if ($modSearchString[0] != "%")
      $modSearchString = "%" . $modSearchString;
    if ($modSearchString[strlen($modSearchString)-1] != "%")
      $modSearchString = $modSearchString . "%";

    $searchobject = $steam->get_server_module("searching");
    $search = new search_define();

    if ($searchType == "searchUser")
    {
      $search->extendAttr("OBJ_NAME", search_define::like($modSearchString));
      $resultItems = $searchobject->search($search, CLASS_USER);
      foreach($resultItems as $r)
      {
        $id = $r->get_id();
        $resultItemName[$id] = $r->get_name(1);
      }
    }
    elseif ($searchType == "searchGroup")
    {
      $search->extendAttr("GROUP_NAME", search_define::like($modSearchString));
      $resultItems = $searchobject->search($search, CLASS_GROUP);
      foreach($resultItems as $r) {
        $id = $r->get_id();
        $resultItemName[$id] = $r->get_groupname(1);
      }
    }
    $result = $steam->buffer_flush();
    foreach($resultItems as $r)
    {
      $id = $r->get_id();
      $resultItemName[$id] = $result[$resultItemName[$id]];
      $searchResult[] = $resultItemName[$id];
    }
  }
}

// sort favourites
natcasesort($searchResult);

/** log-in user */
$steamUser =  $steam->get_login_user();

// Logout & Disconnect
$steam->disconnect();

//******************************************************
//** Display Stuff
//******************************************************
//template stuff
$tpl = new Template("./templates/$language", "keep");
$tpl->set_file(array(
  "content" => "favourites_search.ihtml"
));
$tpl->set_block("content", "result_id_card", "DUMMY");
$tpl->set_block("content", "no_result", "DUMMY");
$tpl->set_block("content", "error_search_string_too_short", "DUMMY");
$tpl->set_var(array(
  "DUMMY" => "",
  "ERROR_FEEDBACK" => "",
  "SEARCH_RESULT_ENTRY" => "",
  "CHECKED_USER" => ($searchType == "searchUser") ? "CHECKED" : "",
  "CHECKED_GROUP" => ($searchType == "searchGroup") ? "CHECKED" : "",
  "SEARCH_STRING" => $searchString,
  "MIN_SEARCH_STRING_COUNT" => $min_search_string_count
));

$loopCount = 0;
foreach ($searchResult as $resultEntry) {
  $tpl->set_var(array(
    "OBJ_NAME" => $resultEntry,
    "OBJ_TYPE" => ($searchType == "searchUser")?"user":"group",
    "SEARCH_ENTRY_MOD2" => $loopCount%2
  ));
  $tpl->parse("SEARCH_RESULT_ENTRY", "result_id_card", True);
  $loopCount += 1;
}
if ($loopCount == 0) {
  $tpl->parse("SEARCH_RESULT_ENTRY", "no_result", False);
}

/*
 * Error handling
 */
if ($action != "" && isset($error) && count($error) > 0) {
  foreach($error as $error_type)
    $tpl->parse("ERROR_FEEDBACK", $error_type, true);
}

//parse all out
$tpl->parse("OUT", "content");
$tpl->p("OUT");
?>
