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
  require_once("$config_doc_root/classes/logHelper.php");
  require_once("$config_doc_root/config/mimetype_map.php");
  require_once("$steamapi_doc_root/steam_connector.class.php");

  require_once("$config_doc_root/includes/derive_mimetype.php");
  require_once 'thumbnail.class.php';

  $object = (int) (isset($_GET["object"]))?trim($_GET["object"]):0;
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
  if( $object !== 0 && $object !== "")
  {
    if ($object - 0 == 0) {
      $object = str_replace (" ", "+", $object);
      $object = steam_factory::path_to_object($steam, $object);
      $object = ($object->get_id());
    }

    try {
       $object = steam_factory::get_object( $steam, $object );
    } catch (steam_exception $se) {
        echo "Es ist ein Fehler aufgetreten.";
        security_log("get.php Fehler aufgetreten: " . $se);
        exit;
    }

    if (!$object instanceof steam_document) {
        echo "Kein g&uuml;ltiges Dokument.";
        security_log("get.php Kein Dokument");
        exit;
    }
    
    // store object data in array below
    // (makes future usage of disk cache mechanisms easier too)
    $data = array();
    if (isset($_GET["mode"]) && $_GET["mode"] == "thumbnail" && isset($_GET["width"]) && isset($_GET["height"])) {
      $width     = (int)$_GET["width"];
      $height    = (int)$_GET["height"];
      
      
      if (PHP_THUMBNAIL) {
      	$thumbnail = new thumbnail();
      	$data = $thumbnail->get_thumbnail($object->get_id(), $width, $height);
      } else {
	      $object->get_attributes( array(
	          "OBJ_NAME",
	          "DOC_MIME_TYPE",
	          "DOC_LAST_MODIFIED"
	        ), TRUE );
	      if ($debug_log) debug_log("getting thumbnail for object: " . $object->get_id());
	      $tnr_imagecontent = $object->get_thumbnail_data($width, $height, 0, TRUE);
	      $result = $steam->buffer_flush();
	      $data["mimetype"]    = $result[$tnr_imagecontent]["mimetype"];
	      $data["lastmodified"]= $result[$tnr_imagecontent]["timestamp"];
	      $data["name"]        = $result[$tnr_imagecontent]["name"];
	      $data["content"]     = $result[$tnr_imagecontent]["content"];
	      $data["contentsize"] = $result[$tnr_imagecontent]["contentsize"];
      	  // For debug issues:
          //error_log("get.php: thumbnail name=" . $data["name"] . " width=". $width . " height=" . $height);
      }
    }
    else {
      //get object attribs
      $data["name"] = $object->get_attribute(OBJ_NAME);
    
      //derive mimetype
      $data["mimetype"] = derive_mimetype($data["name"]);
    
      //get content
      try {
        $filecontent = $object->download();
      } catch (steam_exception $se) {
        echo "Es ist ein Fehler aufgetreten.";
        security_log("get.php Fehler aufgetreten: " . $se);
        exit;
      }
      if ( empty($filecontent) ) {
        //echo "Die Datei " . $object . " kann nicht angezeigt werden";
        header("WWW-Authenticate: Basic realm=\"Test Authentication System\"");
        header("HTTP/1.0 401 Unauthorized");
        exit();
      }
      $data["content"] = $filecontent;
      $data["contentsize"] = strlen($filecontent);
    }
    header( "Pragma: private\n" );
	header( "Cache-Control: must-revalidate, post-check=0, pre-check=0\n" );
	//header( "Content-Type: " . $this->get_attribute( "DOC_MIME_TYPE" ) . "\n");
	//header( "Content-Disposition: attachment; filename=\"" . $data["name"] . "\"\n" );
	//header( "Content-Length: " . $this->get_content_size() . "\n");
    //header('Cache-Control: private');
    //header('Cache-Control: must-revalidate');
    //header("Accept-Ranges: bytes");
    header("Content-Type: " . $data["mimetype"]);
    header("Content-Length: " . $data["contentsize"]);
    header("Pragma: public");
    header('Connection: close');
    
    header("Content-Disposition: inline; filename=" . $data["name"]);
    
    echo($data["content"]);

  }
  else
  {
    //ID Fehler ausgeben
    security_log("get.php mit fehlender ID ...");

    echo("Download nicht m&ouml;glich. ID wurde nicht korrekt &uuml;bergeben.<br>");
    exit();
  }

  //Logout & Disconnect
  $steam->disconnect();

?>
