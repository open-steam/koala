<?php

/****************************************************************************
 portlet_insert.php - create a new portlet in a portal
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
 Thorsten Schäfer <tms82@upb.de>

 ****************************************************************************/

//include stuff
require_once("../../config/config.php");
require_once("$steamapi_doc_root/steam_connector.class.php");
require_once("$config_doc_root/classes/template.inc");
require_once("$config_doc_root/includes/sessiondata.php");

//******************************************************
//** Presumption
//******************************************************

$column = (isset($_GET["column"]))?$_GET["column"]:((isset($_POST["column"]))?$_POST["column"]:"");
$action = (isset($_POST["mission"]))?$_POST["mission"]:"";

//******************************************************
//** sTeam Stuff
//******************************************************

//login und $steam def. in "./includes/login.php"
$steam = new steam_connector(
    $config_server_ip,
    $config_server_port,
    $login_name,
    $login_pwd);

if( !$steam || !$steam->get_login_status() )
{
    header("Location: $config_webserver_ip/index.html");
    exit();
}

//current room steam object
$column = ($column != 0)?steam_factory::get_object($steam, $column):$steam->get_login_user()->get_workroom();
$column_name = $column->get_attribute(OBJ_NAME);

//get write permission
$allowed = $column->check_access_read( $steam->get_login_user() );

if(!$allowed)
die("Erstellung nicht m&ouml;glich!<br>");

// Action save
if($action == "save" && trim($_POST["title"]) != ""){
    require_once("portlets/" . $_POST["portlet_type"] . "/name.php");
    $tmp_mapping = $_POST["portlet_type"] . "_version";
    $version = $$tmp_mapping;

    $new_portlet = steam_factory::create_container($steam, rawurlencode($_POST["title"]), $column);
    $new_portlet->set_attributes(array(
        OBJ_DESC => trim($_POST["title"]),
        OBJ_TYPE => "container_portlet_bid",
        "bid:portlet" => $_POST["portlet_type"],
        "bid:portlet:version" => $version,
    ));
    //redirect to portlet edit script
    $steam->disconnect();
    header("Location: $config_webserver_ip/modules/portal2/portlets/" .
                 $_POST["portlet_type"] . "/edit.php?portlet=" . $new_portlet->get_id());
exit;
}

// Action cancel
if ($action == "cancel") {
    // redirect to column edit script
    $steam->disconnect();
    header("Location: ".
        "$config_webserver_ip/modules/portal2/edit_column.php?column=".
        $column->get_id());
    exit;
}

//Logout/Disconnect
$steam->disconnect();


//******************************************************
//** Display Stuff
//******************************************************
$tpl = new Template("./templates/$language", "keep");
$tpl->set_file("content", "portlet_insert.ihtml");

$tpl->set_block("content", "portlet_type_row", "PORTLET_TYPE_ROW");
$tpl->set_block("content", "error_title", "DUMMY");
$tpl->set_block("content", "button_mission", "BUTTON_MISSION_ROW");
$tpl->set_block("content", "button_label_ok", "DUMMY");
$tpl->set_var(array(
    "DUMMY" => "",
    "DOC_ROOT" => $config_webserver_ip,
    "COLUMN_ID" => $column->get_id(),
    "PORTLET_TITLE" => "",
    "ERROR_TITLE" => "",
    "BUTTON_MISSION" => "save",
    "BUTTON_URL" => "$config_webserver_ip/modules/portal2/portlet_insert.php",
    "BUTTON_CANCEL_ACTION" => "javascript:form_submit('cancel', '$config_webserver_ip/modules/portal2/portlet_insert.php'); return false;",
));
$tpl->parse("BUTTON_LABEL", "button_label_ok");
$tpl->parse("BUTTON_MISSION_ROW", "button_mission", true);

// build list of portlet types, based on the portlet directories in
// "./portlets" directory. Open a known directory, and proceed to read 
// its contents
$dir = "./portlets";
$portlets = array();

if(is_dir($dir))
{
    if($dh = opendir($dir))
    {
        while(($file = readdir($dh)) !== false) {
            if($file[0] !== "." && $file !== ".." && $file !== "CVS" && $file !== ".svn")
            {
                //get language dependent portletnames
                include("$dir/$file/name.php");
                $tmp_mapping = $file . "_name";
                $name_mapping = $$tmp_mapping;
                $portlets[$file] = $name_mapping[$language];
            }
        }
        closedir($dh);
    }
}

// Having obtained the list of known portlets, sort them in lexicographic 
// order before displaying them
asort($portlets);
foreach ($portlets as $file => $name) {
    $tpl->set_var(array(
        "PORTLET_TYPE" => $file, 
        "PORTLET_NAME" => $name
    ));
    $tpl->parse("PORTLET_TYPE_ROW", "portlet_type_row", true);
}

if (isset($_POST["title"]) && trim($_POST["title"]) == "")
    $tpl->parse("ERROR_TITLE", "error_title");

//parse all out
$tpl->parse("OUT", "content");
$tpl->p("OUT");

?>
