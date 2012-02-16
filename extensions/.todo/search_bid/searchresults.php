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
	define("STEPPING", 10);

  	//include stuff
  	require_once("../../config/config.php");
  	require_once("$steamapi_doc_root/steam_connector.class.php");
  	require_once("$config_doc_root/classes/template.inc");
  	require_once("$config_doc_root/includes/sessiondata.php");
  	require_once("$config_doc_root/includes/derive_menu.php");
  	require_once("$config_doc_root/includes/derive_url.php");
  	require_once("$config_doc_root/includes/derive_icon.php");
  	  	
  	require_once("search.php");

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
  	
  	if ($_GET["action"] == "search")
  	{
  		$result = search ($_GET["keyword"], isset($_GET["fulltext"]), $_GET["search_options"], $steam);
  		$result = paginate($result, STEPPING);
  		$_SESSION["bid_search_result"] = $result;
  	}
  	else
  	{
  		$result = $_SESSION["bid_search_result"];
  	}

  	if (!isset($_GET["page"]))
  	{
  		$pagenumber = 0;
  	}
  	else
  	{
  		$pagenumber = $_GET["page"];
  	}
  	
  	$page = $result[$pagenumber];
  	
  	if ($pagenumber > 0)
  	{
  		$haspreviouspage = true;
  		$previouspage = $pagenumber - 1;
  	}
  	else
  	{
  		$haspreviouspage = false;
  	}
  	
  	if (isset($result[$pagenumber + 1]))
  	{
   		$hasnextpage = true;
  		$nextpage = $pagenumber + 1; 		
  	}
  	else
  	{
  		$hasnextpage = false;
  	}
  	
  	$tpl = new Template("./templates/$language", "keep");
  	$tpl->set_file(array(
    	"content" => "search_results.ihtml"
  	));
  	$tpl->set_block("content", "resultentry", "DUMMY");
  	$tpl->set_block("content", "userresultentry", "DUMMY");
  	$tpl->set_block("content", "groupresultentry", "DUMMY");
  	$tpl->set_block("content", "previouspage", "DUMMY");
   	$tpl->set_block("content", "nextpage", "DUMMY");
   	$tpl->set_block("content", "noresults", "DUMMY");
		
  	$tpl->set_var(array(
		"FORM_NAME" => "bid_search", 
		"FORM_URL" => "index.php", 
		"PREVIOUSPAGENUMBER" => $previouspage,
		"NEXTPAGENUMBER" => $nextpage,
		"DUMMY" => ""));
	
	if ($haspreviouspage)
		$tpl->parse("PREVIOUSPAGELINK", "previouspage");
	else
		$tpl->parse("PREVIOUSPAGELINK", "");

	if ($hasnextpage)		
  		$tpl->parse("NEXTPAGELINK", "nextpage");
  	else
  		$tpl->parse("NEXTPAGELINK", "");
  	
  	if (sizeof($page) > 0)
  	{
	  	foreach ($page as $key => $value)
	  	{
	  		$icon = derive_icon(array(
		        "name" => $value["OBJ_NAME"],
		        "bid:collectiontype" => $value["bid:collectiontype"],
		        "bid:doctype" => $value["bid:doctype"],
		        "mimetype" => $value["DOC_MIME_TYPE"]
	      	));
	      
	  		$tpl->set_var(array(
	      		"OBJ_NAME" => $value["OBJ_NAME"],
	      		"OBJ_ID" => $key,
	      		"OBJ_DESC" => ($value["OBJ_DESC"] != "" ? $value["OBJ_DESC"] : "-"),
	      		"USERFIRSTNAME" => $value["USER_FULLNAME"],
	      		"USERLASTNAME" => $value["USER_FIRSTNAME"],
	      		"LASTCHANGE" => date("d.m.Y - H:i:s", $value["OBJ_LAST_CHANGED"]),
	      		"CREATED" => date("d.m.Y - H:i:s", $value["OBJ_CREATION_TIME"]),
	      		"OBJ_ICON" => $icon
	      	));
	      	if ($value["isuser"] == true)
	      		$tpl->parse("ENTRIES", "userresultentry", true);
	      	else if ($value["isgroup"] == true)
	      		$tpl->parse("ENTRIES", "groupresultentry", true);
	      	else
	      		$tpl->parse("ENTRIES", "resultentry", true);
	  	}
  	}
  	else
  	{
  		$tpl->parse("ENTRIES", "noresults");
  	}
	  		
  	
  	$tpl->parse("OUT", "content");
  	
    $tpl->p("OUT");
  	
  	function paginate ($to_paginate, $size)
  	{
  		$retval = array();
  		$singlepage = array();
  		$counter = 0;
  		
		foreach ($to_paginate as $key => $value)
		{
			$singlepage[$key] = $value;
			if ($counter + 1 == $size)
			{
				$retval[] = $singlepage;
				$singlepage = array();
				$counter = 0;
			}
			else
			{
				$counter++;
			}
		}
		
		if ($counter != 0)
		{
			$retval[] = $singlepage;
		}
		
		return $retval;
  	}
?>
