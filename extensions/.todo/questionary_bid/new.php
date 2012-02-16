<?php

  /****************************************************************************
  new.php - create a questionary
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

  Author: Patrick Tönnis
  EMail: toennis@uni-paderborn.de
  
  Author: Henrik Beige
  EMail: hebeige@gmx.de

  ****************************************************************************/


  //include stuff
  require_once("../../config/config.php");
  require_once("$steamapi_doc_root/steam_connector.class.php");
  require_once("$config_doc_root/classes/template.inc");
  require_once("$config_doc_root/includes/sessiondata.php");
  require_once("$config_doc_root/includes/norm_post.php");
  require_once("./config/config.php");


  //******************************************************
  //** Presumption
  //******************************************************

  $name = trim((isset($_POST["name"]))?$_POST["name"]:"");


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
    header("Location: $config_webserver_ip/accessdenied.html");
    exit();
  }

  //current room steam object
  if( (int) $object != 0 ) $current_room = steam_factory::get_object( $steam, $object );
  else $current_room = $steam->get_login_user()->get_workroom(); 

  //get write permission
  $access_write = $current_room->check_access_write( $steam->get_login_user() );
  $access_read = $current_room->check_access_read( $steam->get_login_user() );
  $access_insert = $current_room->check_access_insert( $steam->get_login_user() );
  $allowed =  $access_write && $access_read && $access_insert ? true : false;
  if(!$allowed)
  {
    //Disconnect & close
    $steam->disconnect();
    die("<html>\n<body onload='javascript:window.close();'>\n</body>\n</html>");
  }


  //create questionary if adviced too
  if(isset($_POST["mission"]) && $_POST["mission"] == "create" && $name != "" && $allowed)
  {
    //create new questionary
	$questionary = steam_factory::create_container( $steam, $name, $current_room );
	$questions_folder = steam_factory::create_container( $steam, 'questions', $questionary );
	$answers_folder = steam_factory::create_container( $steam, 'answers', $questionary );
	
	if(isset($_POST["description"]))	$description=norm_post($_POST["description"]);
	else $description="";
	
	if($questionary && $questions_folder && $answers_folder)
	{
	  //set standard layout
	  $layout=$templates[1];
	  unset($layout["name_ge"]);
	  unset($layout["name_en"]);

	  //define rights for the attributes
	  $loginuser_id = $steam->get_login_user()->get_id(); 
	  $rootid = steam_factory::username_to_object($steam, "root")->get_id();
	  $author_rights[$loginuser_id] = $loginuser_id;
	  $editor_rights[$loginuser_id] = $loginuser_id;
	  $analyst_rights[$loginuser_id] = $loginuser_id;
	  $author_rights[$rootid] = $rootid;
	  $editor_rights[$rootid] = $rootid;
	  $analyst_rights[$rootid] = $rootid;
	  
	  //define container as questionary and set all attributes
	  $attributes = array(
	    "bid:doctype" => "questionary",
	    "bid:questionary:fillout" => "1",
	    "bid:questionary:editanswer" => "1",
		"bid:questionary:editownanswer" => "0",
	    "bid:questionary:number" => "1",
	    "bid:questionary:resultcreator" => "1",
	    "bid:questionary:resultcreationtime" => "1",
		"bid:questionary:description" => $description,
		"bid:questionary:edittime" => array(0,0,0),
		"bid:questionary:enabled" => false,
		"bid:questionary:layout" => $layout,
		"bid:questionary:author_rights" => $author_rights,
		"bid:questionary:editor_rights" => $editor_rights,
		"bid:questionary:analyst_rights" => $analyst_rights
	  );
	  
	  $result = $questionary->set_attributes($attributes); 
	}
  }

  //Disconnect
  $steam->disconnect();


  //on successfull creation do redirect
  if(isset($result) && $result)
  {
    ?>
        <html>
        <script type="text/javascript">
        <!--
        function urefresh()
        {
          opener.top.location.href = "<?=$config_webserver_ip?>/index.php?object=<?=$questionary->get_id()?>";
          window.location.href = "<?=$config_webserver_ip?>/modules/questionary/edit.php?questionary=<?=$questionary->get_id()?>";
        }
        //-->
        </script>
        <body onload='javascript:urefresh();'></body>
        </html>
     <?php
     exit;
  }


  //******************************************************
  //** Display Stuff
  //******************************************************

  //template stuff
  $tpl = new Template("./templates/$language", "keep");
  $tpl->set_file("content", "new.ihtml");
  $tpl->set_block("content", "error_no_title", "FEEDBACK");
  $tpl->set_block("content", "button_spacer", "DUMMY");
  $tpl->set_block("content", "button_mission", "BUTTON_MISSION_ROW");
  $tpl->set_var(array(
    "DUMMY" => "",
    "FEEDBACK" => "",
    "OBJECT_ID" => $current_room->get_id(),
	"QUESTIONARY_ID" => "",

    "BUTTON_CANCEL_MISSION" => "close",
    "BUTTON_CANCEL_URL" => "",

    "BUTTON_MISSION" => "create",
    "BUTTON_URL" => "$config_webserver_ip/modules/questionary/new.php"
  ));

  //parse out error message if no title has been named
  if(isset($_POST["mission"]) && $name == "")
    $tpl->parse("FEEDBACK", "error_no_title");


  //******************************************************
  //** Buttons
  //******************************************************

  //cancel button settings
  $tpl->set_var(array(
    "BUTTON_CANCEL_MISSION" => "close",
    "BUTTON_CANCEL_URL" => ""
  ));


  //parse in save button
  $tpl->set_var(array(
    "BUTTON_MISSION" => "create",
    "BUTTON_URL" => "$config_webserver_ip/modules/questionary/new.php"
  ));
  $tpl->parse("BUTTON_LABEL", "create");
  $tpl->parse("BUTTON_MISSION_ROW", "button_mission", true);

  //parse spacer
  $tpl->parse("BUTTON_MISSION_ROW", "button_spacer" ,true);


  out();

  function out()
  {
    //parse all out
    global $tpl;
    $tpl->parse("OUT", "content");
    $tpl->p("OUT");

    exit;
  }


?>