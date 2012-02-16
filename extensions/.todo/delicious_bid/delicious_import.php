<?php

  /****************************************************************************
  delicious.php - import links from del.cio.us as steam_docextern into
  current room. Requires a del.icio.us account.

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

  Author: Bastian Schröder <bastian@upb.de>

  ****************************************************************************/

  //include stuff
  require_once("./config/config.php");
  require_once("$steamapi_doc_root/steam_connector.class.php");
  require_once("./classes/template.inc");
  require_once("$config_doc_root/config/language_map.php");
//  require_once("./classes/debugHelper.php");

  require_once("./includes/sessiondata.php");

  $action = (isset($_POST["action"]))?$_POST["action"]:"";


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

  //current room steam object
  if( (int) $object != 0 ) $current_room = steam_factory::get_object( $steam, $object );
  else $current_room = $steam->get_login_user()->get_workroom();

  // action: try to login and get tags for user
  if( isset($_POST["DELICIOUS_USER"]) && isset($_POST["DELICIOUS_PASSWD"]) ){

		// get posts for a tag, if set.
		if( isset($_POST["DELICIOUS_TAG"]) ){

			// build the url fpr the del.icio.us api
			$delicious_url =
				"https://" . $_POST["DELICIOUS_USER"] . ":" . $_POST["DELICIOUS_PASSWD"] . "@" .
				"api.del.icio.us/v1/posts/get" .
				"?tag=" . $_POST["DELICIOUS_TAG"]

			;

			// construct xml DOM object
			$xml = simplexml_load_string( implode("", file($delicious_url)) );
			$delicous_posts = $xml->post;
		}
		// get tags
		else{
			 // build the url for the del.icio.us api
		  	$delicious_url =
		  		"https://" . $_POST["DELICIOUS_USER"] . ":" . $_POST["DELICIOUS_PASSWD"] . "@" .
		  		"api.del.icio.us/v1/tags/get"
		  	;

		  	// construct xml DOM object
		  	// get all tags
		  	$xml = simplexml_load_string( implode("", file($delicious_url)) );
			foreach( $xml->tag as $obj){
				foreach( $obj->attributes()as $attribute => $value)
					if( $attribute == "tag" ) $delicious_tags[] = "" . $value;
			}

		  	if( !isset($delicious_tags) )
		  		echo "<script type='text/javascript'>alert('login failed.');</script>";
		}

  }
  // action: import links
  if( isset($_POST["selected_posts"]) ){

  	foreach($_POST["selected_posts"] as $post_hash ){
  		$post = $_POST["delicious"][$post_hash];

  		$obj = steam_factory::create_docextern( $steam, urlencode($post["href"]), $post["href"], $current_room, $post["description"] );
  		$obj->set_attributes(array(
  			"hash" => $post_hash,
  			"others" => $post["others"],
  			"tag" => $post["tag"],
  			"time" => $post["time"]
  		));
  	}

  	// close this popup
  	echo "<script type=\"text/javascript\">opener.location.reload();window.close();</script>";

  }


  //******************************************************
  //** Display Stuff
  //******************************************************

  //template stuff
  $tpl = new Template("./templates/$language", "keep");
  $tpl->set_file("content", "delicious_import.ihtml");
  $tpl->set_block("content", "getposts_title", "DUMMY");
  $tpl->set_block("content", "getposts_form", "DUMMY");
  $tpl->set_block("content", "getposts_button", "DUMMY");
  $tpl->set_block("content", "import_title", "DUMMY");
  $tpl->set_block("content", "import_form", "DUMMY");
  $tpl->set_block("content", "import_button", "DUMMY");
  $tpl->set_block("content", "post_row", "POST_ROW");
  $tpl->set_block("content", "gettag_form", "DUMMY");
  $tpl->set_block("content", "gettag_button", "DUMMY");
  $tpl->set_block("content", "gettag_title", "DUMMY");
  $tpl->set_var(array(
  	"DUMMY" => "",
  	"POST_ROW" => "",
    "DELICIOUS_USER" => "", #$steam->get_login_user()->get_attribute(OBJ_NAME),
    "DELICIOUS_PASSWD" => "",
	"ENVIRONMENT_ID" => $current_room->get_id()
  ));

  // step3: del.icio.us respose returned
  if( isset($delicous_posts) ){
	// parse all links returned by del.icio.us api
	foreach( $delicous_posts as $xmlObj ){
		$tpl->parse("TITLE", "import_title");
		$tpl->parse("CONTENT", "import_form", true);
		$tpl->parse("BUTTONS", "import_button");

		// get all xml attributes
		$postAttributes = array();
		foreach( $xmlObj->attributes() as $attribute=>$value )
	  		$postAttributes[$attribute] = $value;

	  	$tpl->set_var(array(
			"POST_HREF" => $postAttributes["href"],
			"POST_DESCRIPTION" => $postAttributes["description"],
			"POST_HASH" => $postAttributes["hash"],
			"POST_OTHERS" => $postAttributes["others"],
			"POST_TAG" => $postAttributes["tag"],
			"POST_TIME" => $postAttributes["time"]
		));
		$tpl->parse("POST_ROW", "post_row", true);
	}

  }

  // step2: login to del.icio.us
  else if( isset($delicious_tags) ){

  	$tags = "";
	foreach($delicious_tags as $tag)
		$tags .= "<option>$tag</option>";

	$tpl->set_var(array(
	    "DELICIOUS_USER" => $_POST["DELICIOUS_USER"],
	    "DELICIOUS_PASSWD" => $_POST["DELICIOUS_PASSWD"],
	    "DELICIOUS_TAG" => $tags
 	));
	$tpl->parse("TITLE", "gettag_title");
  	$tpl->parse("CONTENT", "gettag_form");
	$tpl->parse("BUTTONS", "gettag_button");
  }

  // step1: login to del.icio.us
  else{
   $tpl->parse("TITLE", "getposts_title");
   $tpl->parse("CONTENT", "getposts_form");
   $tpl->parse("BUTTONS", "getposts_button");
  }

  //Logout & Disconnect
  $steam->disconnect();

  $tpl->pparse("OUT", "content");

?>