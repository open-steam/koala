<?php

/****************************************************************************
 index.php - display a portal
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

 Author: Harald Selke <hase@uni-paderborn.de>

 ****************************************************************************/

$rootdir = "../..";


//include stuff
require_once("$rootdir/config/config.php");
require_once("$steamapi_doc_root/steam_connector.class.php");
require_once("$config_doc_root/classes/template.inc");
require_once("$config_doc_root/classes/UBBCode.php");
//  require_once("$config_doc_root/classes/debugHelper.php");

require_once("$config_doc_root/includes/sessiondata.php");
require_once("$config_doc_root/includes/derive_url.php");
require_once("$config_doc_root/includes/slashes.php");
require_once("$config_doc_root/includes/tools.php");

//******************************************************
//** Presumption
//******************************************************
$UBB = new UBBCode();

//******************************************************
//** sTeam Stuff
//******************************************************

$steam = new steam_connector($config_server_ip, $config_server_port, $login_name, $login_pwd);

if( !$steam || !$steam->get_login_status() )
{
	header("Location: $config_webserver_ip/index.html");
	exit();
}

//current room steam object
$portal = steam_factory::get_object($steam, isset($_GET["object"])?$_GET["object"]:"");

//get permissions
$readable = $portal->check_access_read($steam->get_login_user(), 1);
$writeable = $portal->check_access_write($steam->get_login_user(), 1);
$result = $steam->buffer_flush();
$readable = $result[$readable];
$writeable = $result[$writeable];

//fetch columns
$columns = $portal->get_inventory(CLASS_CONTAINER, array(OBJ_TYPE, "bid:portal:column:width"));
$columnPortlets = array();

//now fetch portlets in all columns; buffering to be done
foreach($columns as $column) {
	if($column->get_attribute(OBJ_TYPE) != "container_portalColumn_bid") continue;
	$columnPortlets [$column->get_id()] =
		$column->get_inventory("", array(OBJ_NAME,
					OBJ_DESC, "bid:portlet", "bid:portlet:content"));
}

//******************************************************
//** Display Stuff
//******************************************************

if(!$readable)
die("Ansicht nicht m&ouml;glich!<br>");

//template stuff
$tpl = new Template("./templates/$language", "keep");
$tpl->set_file("content", "index.ihtml");
$tpl->set_block("content", "portlet_cell", "PORTLET_CELL");
$tpl->set_block("content", "empty_column", "DUMMY"); 
$tpl->set_block("content", "portal_column", "PORTAL_COLUMN");
$tpl->set_block("content", "edit_area", "DUMMY");
$tpl->set_var(array(
    "DUMMY" => "", 
    "DOC_ROOT" => $config_webserver_ip,
    "OBJECT_ID" => $portal->get_id(),
    "PORTAL_NAME" => $portal->get_name(),
    "EDIT_AREA" => "",
));
$portal_title = $portal->get_attribute(OBJ_DESC);

if ($portal_title != "") $tpl->set_var("PORTAL_NAME", $portal_title);

/** 
 * If you change the absolute portal width here, please remember to change
 * it in modules/portal2/css/portal.css and modules/portal2/header.php 
 * as well. The absolute width of the portal is used to calculate the 
 * absolute width of portal columns with relative width specifications.
 */

// $portal_width = 800;
$portal_width = 0;

foreach($columns as $column) {

/*
  $column_width = calculate_absolute_length(
    $column->get_attribute("bid:portal:column:width"),
    $portal_width);
*/

    $column_width = $column->get_attribute("bid:portal:column:width");
    $portal_width += $column_width;

	$tpl->set_var(array(
      "PORTAL_COLUMN_ID" => $column->get_id(),
      "PORTAL_COLUMN_WIDTH" => $column->get_attribute("bid:portal:column:width")
	));

	//clear variable for next column
	$tpl->unset_var("PORTLET_CELL");

	//editing area for column
	if ($writeable && $steam->get_login_user()->get_name() != "guest") {
		$tpl->parse("EDIT_AREA", "edit_area");
	}

	if (sizeof($columnPortlets[$column->get_id()]) > 0) {
		// column does contain portlets
		foreach ($columnPortlets [$column->get_id()] as $portlet) {
			if($portlet->get_attribute("bid:portlet")) {
				//get the linked portlet if neccessary
				if( $portlet instanceof steam_link )
					$portlet = $portlet->get_link_object();

				//get content of portlet
				$content = $portlet->get_attribute("bid:portlet:content");

				if(is_array($content) && count($content) > 0)
				array_walk($content, "_stripslashes");
				else
				$content = array();

				//get portlet data in handy format
				$portlet_name = $portlet->get_attribute(OBJ_DESC);
				if (trim($portlet_name) == "")
					$portlet_name = $portlet->get_attribute(OBJ_NAME);
				$portlet_type = $portlet->get_attribute("bid:portlet");

				//produce portlet output and store in output buffer => $content
				ob_start();
				include("./portlets/$portlet_type/view.php");
				$content = ob_get_contents();
				ob_end_clean();
			}
			else
				$content = "&nbsp;";

			$tpl->set_var(array(
        		"PORTLET" => $content,
			));
			$tpl->parse("PORTLET_CELL", "portlet_cell", true);
		}		 
	}
	else {
		// column does not contain any portlets yet
    $tpl->parse("PORTLET_CELL", "empty_column", true);
	}

	$tpl->parse("PORTAL_COLUMN", "portal_column", true);
}

$tpl->set_var(array(
   "PORTAL_WIDTH" => $portal_width . 'px'
));

//Logout & Disconnect
$steam->disconnect();

//parse all out
$tpl->parse("OUT", "content");
$tpl->p("OUT");

?>
