<?php

/****************************************************************************
 image_save.php - Use content disposition to save an image file
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
$image = (isset($_GET["image"]))?$_GET["image"]:((isset($_POST["image"]))?$_POST["image"]:"");

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

/** displayed image object */
$image = ($image != 0)?steam_factory::get_object($steam, $image):0;
$image_name = $image->get_name();
$filecontent = $image->download();

/** log-in user */
$steamUser =  $steam->get_login_user();

/** get permissions */
$readable = $image->check_access_read( $steam->get_login_user() );
$writable = $image->check_access_write( $steam->get_login_user() );

if(!$readable)
  die("Keine Rechte vorhanden!<br>");

/** disconnect from server */
$steam->disconnect();


header("Content-Disposition: ".
      (!strpos($HTTP_USER_AGENT,"MSIE 5.5")?"attachment; ":"").
           "filename=$image_name");
header("Content-Transfer-Encoding: binary");
header("Cache-Control: post-check=0, pre-check=0");
header("Content-Length: " . strlen($filecontent));

echo($filecontent);

?>
