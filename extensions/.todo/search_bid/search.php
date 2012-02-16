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
  	require_once("$steamapi_doc_root/modules/searching.class.php");
  	require_once("$steamapi_doc_root/modules/search_define.class.php");

	require_once("$config_doc_root/includes/sessiondata.php");

	define ("SEARCH_EVERYTHING", 0);
	define ("SEARCH_PORTALS", 1);
	define ("SEARCH_USERS", 2);
	define ("SEARCH_GROUPS", 3);
	
	/**
	 * searches for objects on a sTeam-server
	 * 
	 * @param $searchfor the phrase we want to search for (e.g. "test", "blah", ...)
	 * @param $fulltext true if we want to do a fulltext-search, otherwise false.
	 * @param $searchwhat defines if we want to search only for special types. possible values are 
	 * SEARCH_EVERYTHING, SEARCH_PORTALS, SEARCH_USERS, SEARCH_GROUPS
	 * @param $steam_connector the connector to the sTeam-Server
	 * @return array an array with some attributes of the object. The object-ids are the keys 
	 */
  	function search ($searchfor,
  					 $fulltext,
  					 $searchwhat,
  					 $steam_connector)
  	{  	
  		$retval = array();
  		
		if ($searchfor[0] != "%")
			$searchfor = "%" . $searchfor;
		
		if ($searchfor[strlen($searchfor) - 1] != "%")
			$searchfor = $searchfor . "%";

		$searchobject = $steam_connector->get_server_module("searching");

		$search = new search_define();
		$search->extendAttr("OBJ_NAME", search_define::like($searchfor));
		$search->extendAttr("OBJ_DESC", search_define::like($searchfor));
		
		if ($search == true)
		{
			$search->addFulltextSearch($searchfor);
		}

		$result = $searchobject->search($search);
		
		//I don't know why, but if we search for OBJ_NAME, OBJ_DESC and OBJ_KEYWORDS
		//together, some objects are not found if not their exact name is given.
		$keywordsearch = new search_define();
		$keywordsearch->extendAttr("OBJ_KEYWORDS", search_define::like($searchfor));
		$result = array_merge ($result, $searchobject->search($keywordsearch));

		//It seems this is not needed but I am *not* sure!
		/*if ($fulltext == true)
		{
			$fulltextsearch = new search_define();
			$fulltextsearch->addFulltextSearch($searchfor);
			$result = array_merge ($result, $searchobject->search($fulltextsearch));
		}*/
		
		$bufferattributes = array();
		$bufferacces = array();
		
		//Get all the attributes and the read-access buffered - it's faster
		foreach ($result as $singleresult)
		{
			$attributes = $singleresult->get_attributes(array(
										OBJ_NAME, 
								   		OBJ_DESC, 
								   		OBJ_OWNER, 
								   		OBJ_CREATION_TIME, 
								   		OBJ_LAST_CHANGED, 
										"bid:doctype", 
										"bid:collectiontype",  
										DOC_MIME_TYPE, 
										"bid:hidden",
										USER_FULLNAME,
										USER_FIRSTNAME), 1);
			$bufferattributes[$singleresult->get_id()] = $attributes;
			
			$bufferacces[$singleresult->get_id()] = $singleresult->check_access_read( $steam_connector->get_login_user(), 1);
		}

		$bufferresults = $steam_connector->buffer_flush();

		foreach ($result as $singleresult)
		{
			//Check for files, the user is not allowed to read. 
			if ($bufferresults[$bufferacces[$singleresult->get_id()]] != 1)
				continue;
			
			$attributes = $bufferresults[$bufferattributes[$singleresult->get_id()]];
			
			if ($singleresult->get_type() == CLASS_USER)
			{
				$attributes["isuser"] = true;
			}
			
			if ($singleresult->get_type() == CLASS_GROUP)
			{
				$attributes["isgroup"] = true;
			}
			
			//Only search for users - so we can ignore the other results.
			if ($searchwhat == SEARCH_USERS)
			{
				if ($singleresult->get_type() == CLASS_USER)
			 	{
			 		$retval[$singleresult->get_id()] = $attributes;
			 	}
		 		continue;
		 	}

			//Only search for groups - so we can ignore the other results.
			if ($searchwhat == SEARCH_GROUPS)
			{
				if ($singleresult->get_type() == CLASS_GROUP)
			 	{
			 		$attributes["isgroup"] = true;
			 		$retval[$singleresult->get_id()] = $attributes;
			 	}
		 		continue;
		 	}
		 	
		 	//A portal consists of a lot of elements but we only want to find one portal
		 	//that's why we need to get the root element.
		 	//Unfortunatly, we can not buffer that...
		 	while($attributes["bid:doctype"] === "portlet"
			|| 	  $attributes["bid:doctype"] === "portlet:msg"
			||	  $attributes["bid:doctype"] === "portlet:picture")
			{
				$singleresult = $singleresult->get_environment();
				$attributes = $singleresult->get_attributes(array(
										OBJ_NAME, 
								   		OBJ_DESC, 
								   		OBJ_OWNER, 
								   		OBJ_CREATION_TIME, 
								   		OBJ_LAST_CHANGED, 
										"bid:doctype", 
										"bid:collectiontype",  
										DOC_MIME_TYPE, 
										"bid:hidden",
										USER_FULLNAME,
										USER_FIRSTNAME));
			}
			
			//Only search for portals
			if ($searchwhat == SEARCH_PORTALS)
			{
				if ($attributes["bid:doctype"] === "portal")
				{
					$retval[$singleresult->get_id()] = $attributes;
				}
				continue;
			}
		 	
		 	//if we want to search for eveything
		 	if ($searchwhat == SEARCH_EVERYTHING)
		 	{
		 		$retval[$singleresult->get_id()] = $attributes;
		 	}
		}

		return $retval;
  	}
  
?>
