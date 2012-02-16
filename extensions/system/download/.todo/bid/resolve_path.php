<?php

  /****************************************************************************
  get.php - script to download object content from steam server
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
  require_once("../config/config.php");
  require_once("$config_doc_root/config/mimetype_map.php");
  require_once("$steamapi_doc_root/steam_connector.class.php");

  require_once("$config_doc_root/includes/derive_mimetype.php");

  $object_path = (string) (isset($_GET["path"]))?trim($_GET["path"]):0;
  $login_name = (isset($_SERVER["PHP_AUTH_USER"]))?$_SERVER["PHP_AUTH_USER"]:"guest";
  $login_pwd = (isset($_SERVER["PHP_AUTH_PW"]))?$_SERVER["PHP_AUTH_PW"]:"guest";

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

  //if ID has been properly specified => download and output
  if( $object_path !== 0 && $object_path !== "")
  {
    $object = steam_factory::path_to_object( $steam, $object_path );

    //get object attribs
    $name["OBJ_NAME"] = $object->get_attribute(OBJ_NAME);
    $name["DOC_MIME_TYPE"] = $object->get_attribute(DOC_MIME_TYPE);

    //derive mimetype
    $mimetype = derive_mimetype($name);

    //get content
    $filecontent = $object->download();
    if ( empty($filecontent) ) {
      //echo "Die Datei " . $object . " kann nicht angezeigt werden";
      header("WWW-Authenticate: Basic realm=\"Test Authentication System\"");
      header("HTTP/1.0 401 Unauthorized");
      exit();
    }
      
    $length = strlen($filecontent);

    header('Cache-Control: private');
    header('Cache-Control: must-revalidate');
    //header("Accept-Ranges: bytes");
    header("Content-Type: " . $mimetype);
    header("Content-Length: " . $length);
    header("Pragma: public");
    header('Connection: close');
    
    header("Content-Disposition: inline; filename=" . $name["OBJ_NAME"]);

    echo($filecontent);

  }
  else
  {
    //ID Fehler ausgeben
    error_log("get.php mit fehlender ID ...", 3, "/var/log/httpd/phpsteam.log");

    echo("Download nicht m&ouml;glich. ID wurde nicht korrekt &uumlbergeben.<br>");
    exit();
  }

  //Logout & Disconnect
  $steam->disconnect();

?>
