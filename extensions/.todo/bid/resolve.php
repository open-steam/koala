<?php

  /****************************************************************************
  resolve.php - resolve the steam path through $_GET balues (deprecated ???)
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

  require_once("./config/config.php");
  require_once("$steamapi_doc_root/steam_connector.class.php");
  require_once("./classes/template.inc");
//  require_once("./classes/debugHelper.php");

  require_once("./includes/sessiondata.php");


  if(isset($_SERVER["REQUEST_URI"]) || $_SERVER["REQUEST_URI"] != "")
  {
    $path = ($_SERVER["REQUEST_URI"] == "index.html")?"":substr($_SERVER["REQUEST_URI"], strlen($pre_tmp));

    if($path == "" && (!isset($login_name) || $login_name == "" || !isset($login_pwd) || $login_pwd == ""))
    {
      header("Location: $config_webserver_ip/index.html" . $object->get_id() );
      exit;
    }

    //******************************************************
    //** sTeam Stuff
    //******************************************************

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


	$object = steam_factory::path_to_object( $steam, $path );
	
	//Logout & Disconnect
	$steam->disconnect();


    //compute request
    if($object)
    {
      header("Location: $config_webserver_ip/index.php?object=" . $object->get_id() );
      exit();
    }

  }

  header("HTTP/1.0 404 Not Found");

?>
