<?php

	/****************************************************************************
	result.php - view all results of the questionary
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
	  
	Author: Tobias Müller
	EMail: tmueller@upb.de
	
	****************************************************************************/


  	//include stuff
  	require_once("../../config/config.php");
  	require_once("$steamapi_doc_root/steam_connector.class.php");
  	require_once("$config_doc_root/classes/template.inc");
  	require_once("$config_doc_root/includes/sessiondata.php");
  	require_once("$config_doc_root/includes/derive_menu.php");
  	require_once("$config_doc_root/includes/derive_url.php");

  	//login und $steam def. in "./includes/login.php"
  	$steam = new steam_connector($config_server_ip,
  								 $config_server_port,
  								 $login_name,
  								 $login_pwd);

  	if( !$steam || !$steam->get_login_status() )
  	{
    	$steam->disconnect();
		header("Location: $config_webserver_ip/accessdenied.html");
   		exit();
  	}

  	$tpl = new Template("./templates/$language", "keep");
 	$tpl->set_file(array(
    	"content" => "search_mask.ihtml"
  	));
  	
  	$tpl->set_var(array("FORM_NAME" => "bid_search", "FORM_URL" => "searchresults.php"));
  	
  	$tpl->parse("OUT", "content");
    $tpl->p("OUT");
?>
