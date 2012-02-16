<?php
  /****************************************************************************
  header.php - header include of the portlet specific edit scripts
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
  //start output buffering
  ob_start();


  //include stuff
  require_once("../../../../config/config.php");
  require_once("$steamapi_doc_root/steam_connector.class.php");
  require_once("$config_doc_root/classes/template.inc");
//  require_once("$config_doc_root/classes/debugHelper.php");

  require_once("$config_doc_root/includes/sessiondata.php");
  require_once("$config_doc_root/includes/norm_post.php");
  require_once("$config_doc_root/includes/slashes.php");
  require_once("$config_doc_root/includes/derive_url.php");
  require_once("$config_doc_root/includes/tools.php");

  //******************************************************
  //** Presumption
  //******************************************************

  $portlet = (isset($_GET["portlet"]))?$_GET["portlet"]:
             ((isset($_POST["portlet"]))?$_POST["portlet"]:$_SESSION["portlet"]);
  $action = (isset($_GET["mission"]))?$_GET["mission"]:((isset($_POST["mission"]))?$_POST["mission"]:"");

  $_SESSION["portlet"] = $portlet;

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
    #header("Location: $config_webserver_ip/start.html"); ??
    exit();
  }


  //get portlet, portal and the needed data from them
  $portlet = steam_factory::get_object($steam, $portlet);
  $portlet->get_attributes( array("bid:portlet", "bid:portlet:content") );

  $portlet_name = $portlet->get_attribute(OBJ_NAME);
  $portlet_title = $portlet->get_attribute(OBJ_DESC);
  if (trim($portlet_title) == "")
    $portlet_title = $portlet_name;
  else
    $portlet_name = $portlet_title;
  $portlet_type = $portlet->get_attribute("bid:portlet");
  $portlet_content = $portlet->get_attribute("bid:portlet:content" );

  /** The environment of the portlet should be a column and not a portal??? */
  $portal = $portlet->get_environment();
  $portal_name = $portal->get_attribute(OBJ_NAME);
  $portal_geometry = $portal->get_attribute("bid:geometry" );

  $portal_width = 800;
  
  $column_width = calculate_absolute_length(
    $portal->get_attribute("bid:portal:column:width"), $portal_width);



  if(is_array($portlet_content))
    array_walk($portlet_content, "_stripslashes");
  else
    $portlet_content = array();



  //get write permission
  $allowed = $portal->check_access_read( $steam->get_login_user() );

  if(!$allowed)
  {
    echo("Erstellung nicht m&ouml;glich!<br>");
    exit();
  }

?>
