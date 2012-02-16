<?php

/****************************************************************************
 portlet_edit.php - Edit / Trash / Order Portlets in a column
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
$portlet = (isset($_GET["portlet"]))?$_GET["portlet"]:((isset($_POST["portlet"]))?$_POST["portlet"]:"");
$action = (isset($_GET["action"]))?$_GET["action"]:((isset($_POST["action"]))?$_POST["action"]:"");

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
$portlet = ($portlet != 0)?steam_factory::get_object($steam, $portlet):0;
/** log-in user */
$steamUser =  $steam->get_login_user();

try{
    $portlet_type = $portlet->get_attribute("bid:portlet");
}
catch( Exception $e ){
    $portlet_type = ".";
}

//get read permission
$readable = $portlet->check_access_read( $steam->get_login_user() );
$writable = $portlet->check_access_write( $steam->get_login_user() );
$trash = $steamUser->get_attribute(USER_TRASHBIN, 0);

if(!$readable || !isset($portlet))
die("Kopieren nicht m&ouml;glich!<br>");

//put copy into backpack
if($action=="copy"){
    include("$config_doc_root/modules/portal2/portlets/$portlet_type/copy.php");
    $double = call_user_func("copy_$portlet_type", $steam, $portlet);
    $result = $double->move( $steam->get_login_user() );

    // redirect to column_edit.php
    echo("<html><body onload='javascript:opener.top.location.reload();window.close();'></body></html");

    $steam->disconnect();
    exit;
}

//put reference into backpack
else if($action == "reference") {
    $link = steam_factory::create_link( $steam, $portlet );
    $link->set_attributes(array(
            OBJ_DESC => $portlet->get_attribute(OBJ_DESC),
            "bid:portlet" => $portlet->get_attribute("bid:portlet"),
            ));
    $link->move( $steam->get_login_user() );

    // redirect to column_edit.php
    echo("<html><body onload='javascript:opener.top.location.reload();window.close();'></body></html");
    $steam->disconnect();
    exit;
}
else if($action=="delete"){
    if(!$writable) die("Entfernen nicht m&ouml;glich!<br>");
    
    $portlet->delete(1);
    $steam->buffer_flush();
    
    // redirect to column_edit.php
    echo("<html><body onload='javascript:opener.top.location.reload();window.close();'></body></html");
            
    $steam->disconnect();
    exit;
}
else if($action=="trash"){
    if(!$writable) die("Entfernen nicht m&ouml;glich!<br>");

    //move objects to trashbin
    if (is_object($trash)) {
        $portlet->move($trash, 1);
        $steam->buffer_flush();
    }
    
    // redirect to column_edit.php
    echo("<html><body onload='javascript:opener.top.location.reload();window.close();'></body></html");
            
    $steam->disconnect();
    exit;
}
//cut into backpack
else if($action == "cut") {
    if(!$writable) die("Ausschneiden nicht m&ouml;glich!<br>");
    
    $portlet->move($steam->get_login_user());

    // redirect to column_edit.php
    echo("<html><body onload='javascript:opener.top.location.reload();window.close();'></body></html");
    $steam->disconnect();
    exit;   
}

$steam->disconnect();
?>
