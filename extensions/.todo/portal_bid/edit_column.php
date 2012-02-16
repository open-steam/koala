<?php

/****************************************************************************
 edit_column.php - the dialog to edit a column
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

 Author: Thorsten Schäfer <tms82@upb.de>

 ****************************************************************************/

//include stuff
require_once("../../config/config.php");
require_once("$steamapi_doc_root/steam_connector.class.php");
require_once("$config_doc_root/classes/template.inc");
//require_once("$config_doc_root/classes/debugHelper.php");

require_once("$config_doc_root/includes/sessiondata.php");
require_once("$config_doc_root/includes/derive_icon.php");
require_once("$config_doc_root/includes/tools.php");

//******************************************************
//** Presumption
//******************************************************
$column_id = (isset($_GET["column"]))
             ?$_GET["column"]
             :((isset($_POST["column"]))
                ?$_POST["column"]
                :"");
$action = (isset($_POST["mission"]))
          ?$_POST["mission"]
          :"";

//******************************************************
//** sTeam Stuff
//******************************************************
$steam = new steam_connector($config_server_ip,
$config_server_port,
$login_name,
$login_pwd);
if( !$steam || !$steam->get_login_status() )
{
    header("Location: $config_webserver_ip/index.html");
    exit();
}

//current column steam object
$column = steam_factory::get_object($steam, $column_id);

// get portal (i.e. environment)
$portal = $column->get_environment();

// get column attributes
$column_attributes = $column->get_attributes(
    array(OBJ_NAME, OBJ_DESC, "bid:portal:column:width"),
    1);

//get permissions
$readable = $column->check_access_read($steam->get_login_user(), 1);
$writeable = $column->check_access_write($steam->get_login_user(), 1);

// retrieve results
$result = $steam->buffer_flush();
$readable = $result[$readable];
$writeable = $result[$writeable];
$column_attributes = $result[$column_attributes];
$column_title = isset($column_attributes[OBJ_DESC])
                ? $column_attributes[OBJ_DESC]
                : $column_attributes[OBJ_NAME];

if($column->get_attribute(OBJ_TYPE) != "container_portalColumn_bid") {
    // Fehlerbehandlung einfügen
}

// fetch portlets of currrent column
$portlets_tmp = $column->get_inventory("",
                    array("bid:portlet", "bid:portlet:content"));
$portlets = array();
foreach($portlets_tmp as $value) {
    if ($value->get_attribute("bid:portlet"))
        $portlets[$value->get_id()] = $value;
}

//action performed
if($action == "save") {
    $error= array();

    $column_width = (isset($_POST["column_width"]))
	    ? check_width_string($_POST["column_width"],
		    5, 99, 20, 100000, "")
	    : "";

    if(!$writeable)
        $error[count($error)] = "error_no_access";
    else if ($column_width == "")
        $error[count($error)] = "error_column_width";
    else {
        $column->set_attributes(array(
            "bid:portal:column:width" => $column_width
        ),1);

        // Save order of portlets in column
        $sorting = trim($_POST["bid:sorting"]);
        $sorting = explode(" ", $sorting);
        if (sizeof($sorting) > 0) {
            foreach($sorting as $key => $id) {
                if ($id == 0) continue;
                $column->swap_inventory($portlets[$id], $key, 1);
            }
        }

        $steam->buffer_flush();
        $steam->disconnect();

        echo("<html><body onload='javascript:opener.top.location.reload();".
             "window.close();'></body></html");
        exit;
    }
}

//Logout & Disconnect
$steam->disconnect();

//******************************************************
//** Display Stuff
//******************************************************

$column_width = isset($_POST["column_width"])
                ? $_POST["column_width"]
                :$column_attributes["bid:portal:column:width"];

//template stuff
$tpl = new Template("./templates/$language", "keep");
$tpl->set_file(array(
    "content" => "edit_column.ihtml"
    ));
    $tpl->set_block("content", "new_title", "DUMMY");
    $tpl->set_block("content", "error_no_access", "DUMMY");
    $tpl->set_block("content", "error_column_width", "DUMMY");
    $tpl->set_block("content", "sort_portlet_row", "SORT_PORTLET_ROW");
    $tpl->set_block("content", "button_mission", "BUTTON_MISSION_ROW");
    $tpl->set_block("content", "button_label_ok", "DUMMY");
    $tpl->set_var(array(
        "DUMMY" => "",
        "BUTTON_MISSION" => "save",
        "PORTAL_TITLE" => "",
        "BUTTON_URL" => "$config_webserver_ip/modules/portal2/edit_column.php",
        "BUTTON_CANCEL_ACTION" => "opener.top.location.reload();window.close();",
        "COLUMN_ID" => $column->get_id(),
        "COLUMN_TITLE" => $column_title,
        "COLUMN_WIDTH" => $column_width,
        "PORTAL_ID" => $portal->get_id(),
        "ERROR_FEEDBACK" => "",
    ));

$tpl->parse("BUTTON_LABEL", "button_label_ok");
$tpl->parse("BUTTON_MISSION_ROW", "button_mission", true);

foreach ($portlets as $id => $portlet) {
    if($portlet->get_attribute("bid:portlet")) {
        $portlet_name = $portlet->get_attribute(OBJ_NAME);
        if (trim($portlet->get_attribute(OBJ_DESC)) != "") {
            $portlet_name = $portlet->get_attribute(OBJ_DESC);
        }

        $tpl->set_var(array(
            "PORTLET_ID" => $id,
            "PORTLET_NAME" => $portlet_name,
        ));
        $tpl->parse("SORT_PORTLET_ROW", "sort_portlet_row", true);
    }
}


//if action has been done and error occured put out error feedback
if (isset($error) && count($error) > 0 ){
    foreach($error as $error_type)
        $tpl->parse("ERROR_FEEDBACK", $error_type, true);
}

//parse all out
$tpl->parse("OUT", "content");
$tpl->p("OUT");

?>
