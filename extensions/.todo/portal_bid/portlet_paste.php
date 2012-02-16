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

 Author: Thorsten Schäfer <tms82@upb.de>

 ****************************************************************************/

//include stuff
require_once("../../config/config.php");
require_once("$steamapi_doc_root/steam_connector.class.php");
require_once("$config_doc_root/classes/template.inc");
require_once("$config_doc_root/includes/sessiondata.php");

//******************************************************
//** Presumption
//******************************************************
$column = (isset($_GET["column"]))
          ?$_GET["column"]
          :((isset($_POST["column"]))
              ?$_POST["column"]
              :"");
$action = (isset($_GET["action"]))
            ?$_GET["action"]
            :((isset($_POST["action"]))
                ?$_POST["action"]
                :"");

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
$column = ($column != 0)?steam_factory::get_object($steam, $column):0;

//get permissions
$readable = $column->check_access_read( $steam->get_login_user() );
$writable = $column->check_access_write( $steam->get_login_user() );

if(!$writable || !isset($column))
	die("Einf&uuml;gen nicht m&ouml;glich!<br>");

//get all portlets and links to portlets
$backpack = $steam->get_login_user()->get_inventory();


//get "bid:portlet" attribute of all backpack objects
foreach($backpack as $tmp) {
	$tmp->get_attributes(array("bid:portlet"), 1);
}
$steam->buffer_flush();

//move all portlets to current portal
foreach($backpack as $tmp) {
	if ( $tmp->get_attribute("bid:portlet") !== 0 )
		$tmp->set_attributes(array(
			OBJ_TYPE => "container_portlet_bid"
		), 1);
		$tmp->move($column, 1);
}
$steam->buffer_flush();


//Logout & Disconnect
$steam->disconnect();

// redirect to column_edit.php
echo("<html><body onload='javascript:opener.top.location.reload();window.close();'></body></html");
exit;

?>
