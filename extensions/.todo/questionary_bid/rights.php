<?php

  /****************************************************************************
  rights.php - manage the user rights for a questionary
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
  EMail: toennis@upb.de

  ****************************************************************************/

  
  //include stuff
  require_once("../../config/config.php");
  require_once("$steamapi_doc_root/steam_connector.class.php");
  require_once("$config_doc_root/classes/template.inc");
  require_once("$config_doc_root/includes/sessiondata.php");
  require_once("./classes/rights.php");


  //******************************************************
  //** Precondition
  //******************************************************

  $questionary_id = (isset($_GET["questionary"]))?$_GET["questionary"]:((isset($_POST["questionary"]))?$_POST["questionary"]:"");
  $mission = (isset($_GET["mission"]))?$_GET["mission"]:((isset($_POST["mission"]))?$_POST["mission"]:"");

	
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
  if( (int) $questionary_id != 0 ) 
  {
  	$questionary = steam_factory::get_object( $steam, $questionary_id );
	$question_folder = $questionary->get_object_by_name('questions');
  	$answer_folder = $questionary->get_object_by_name('answers');
  }
  else  
  {
  	$steam->disconnect();
	header("Location: $config_webserver_ip/index.php");
  }
  
  
  //owner of the questionary
  $owner = $questionary->get_creator();
  $owner_id = $owner->get_id();
  $owner_name = $owner->get_name();
  
  
  //create new RIGHTS object
  $rights = new rights($steam, $questionary, $question_folder, $answer_folder);
  
  
  //check author permission  
  $login_user = $steam->get_login_user();
  $login_user_id = $login_user->get_id();
  $login_user_groups = $login_user->get_groups();
  foreach($login_user_groups as $login_user_group)  $login_user_group_ids[]=$login_user_group->get_id();
  $is_author = $rights->check_access_edit($login_user, $login_user_group_ids); 
  if(!$is_author || count($answer_folder->get_inventory())>0 || $owner_id!=$login_user_id)
  {
    //Disconnect & close
    $steam->disconnect();
    die("<html>\n<body onload='javascript:window.close();'>\n</body>\n</html>");
  }
  
  
  //get questionary name
  $questionary_name = $questionary->get_name();
  
  
  //get groups from user
  $groups = array(steam_factory::groupname_to_object($steam, "everyone"));
  $groups_tmp = $owner->get_groups();
  if(is_array($groups_tmp))	$groups = array_merge($groups, $groups_tmp);
  
  
  //get favourites from user
  $favourites=$owner->get_buddies();
  if(!is_array($favourites)) $favourites = array();
  
  
  //save changes
  $all = array_merge($groups, $favourites);
  if($mission=="save")
  {	
	//get the right arrays
    $post = $_POST;
	$publish = (isset($post["publish"]))?$post["publish"]:array();
	
	//unset all rights for users and groups, that are no more a favourit
	$rights->unset_rights_nofavourit($all);
	
	foreach($all as $entity)
	{
		//unset rights to set new rights
		$rights->unset_rights($entity);		
		
		if(isset($post["edit_" . $entity->get_id()]) && $publish == "user" )	$rights->set_rights_edit($entity);
		else
		{
			$set=false;
			//important set at first the evaluate rights and then fill out rights
			if(isset($post["evaluate_" . $entity->get_id()]) && $publish == "user" )	
			{
				$rights->set_rights_evaluate($entity);
				$set=true;
			}
			
			if(isset($post["fillout_" . $entity->get_id()]) && ($publish == "user" || $publish == "public") )	
			{
				$rights->set_rights_fillout($entity);
				$set=true;
			}
			
			if(!$set)	$rights->unset_rights($entity);
		}
	}	
	//close window
	$steam->disconnect();
	die("<html>\n<body onload='javascript:window.close();'>\n</body>\n</html>");
	exit();
  }
  
  
  //get data for the table
  //get rights for own groups
  foreach($groups as $group)
  {
    $group_id= $group->get_id();
	
	$data[$group_id]["name"] = $group->get_name();
    $data[$group_id]["fillout"] = $rights->check_access_fillout($group, $group_id);
	$data[$group_id]["edit"] = $rights->check_access_edit($group, $group_id);
	$data[$group_id]["evaluate"] = $rights->check_access_evaluate($group, $group_id);
  }
  
  
  //get rights for own favourites
  foreach($favourites as $favourite)
  {
    $favourite_id= $favourite->get_id();
	
	$data[$favourite_id]["name"] = $favourite->get_name();
    $data[$favourite_id]["fillout"] = $rights->check_access_fillout($favourite, $favourite_id);
	$data[$favourite_id]["edit"] = $rights->check_access_edit($favourite, $favourite_id);
	$data[$favourite_id]["evaluate"] = $rights->check_access_evaluate($favourite, $favourite_id);
  }

  //Disconnect
  $steam->disconnect();


  //******************************************************
  //** Display Stuff
  //******************************************************

  //template stuff
  $tpl = new Template("./templates/$language", "keep");
  $tpl->set_file(array(
    "content" => "rights.ihtml"
  ));
  $tpl->set_block("content", "group_none", "DUMMY");
  $tpl->set_block("content", "group_row", "GROUP_ROW");
  $tpl->set_block("content", "favourite_none", "DUMMY");
  $tpl->set_block("content", "favourite_row", "FAVOURITE_ROW");
  $tpl->set_block("content", "favourite_row_double", "DUMMY");
  $tpl->set_block("content", "set_crude", "SET_CRUDE");
  $tpl->set_block("content", "unset_crude", "UNSET_CRUDE");
  $tpl->set_block("content", "test_specific_checked", "TEST_SPECIFIC_CHECKED");
  $tpl->set_block("content", "test_specific_unchecked", "TEST_SPECIFIC_UNCHECKED");
  $tpl->set_block("content", "button_mission", "MISSION_BUTTON");
  $tpl->set_block("content", "button_spacer", "DUMMY");
  $tpl->set_block("content", "button_save", "DUMMY");
  $tpl->set_var(array(
    "DUMMY" => "",
    "OWNER" => $owner_name,
    "QUESTIONARY_ID" => $questionary->get_id(),
	"QUESTIONARY_NAME" => $questionary_name,
    "SET_CRUDE" => "",
    "UNSET_CRUDE" => "",
    "TEST_SPECIFIC_CHECKED" => "",
    "TEST_SPECIFIC_UNCHECKED" => ""
  ));

  //counter for correct radio button setting
  $counter = 0;
  $counter_fillout = 0;
  $counter_edit = 0;
  $counter_evaluate = 0;
  

  //output groups and groups rights
  if(sizeof($groups) > 0)
    foreach($groups as $group)
    {
      $group_id=$group->get_id();

      $tpl->set_var(array(
        "ITEM_ID" => $group_id,
        "GROUP_ID" => $group_id,
        "GROUP" => $data[$group_id]["name"],
        "CHECKED_FILLOUT" => $data[$group_id]["fillout"] ? "CHECKED":"",
        "CHECKED_EDIT" => $data[$group_id]["edit"] ? "CHECKED":"",
        "CHECKED_EVALUATE" => $data[$group_id]["evaluate"] ? "CHECKED":""
      ));

      //parse group row
      $tpl->parse("GROUP_ROW", "group_row", true);

      //parse javascript
      $tpl->parse("SET_CRUDE", "set_crude", true);
      $tpl->parse("UNSET_CRUDE", "unset_crude", true);
      $tpl->parse("TEST_SPECIFIC_CHECKED", "test_specific_checked", true);
      $tpl->parse("TEST_SPECIFIC_UNCHECKED", "test_specific_unchecked", true);


      $counter++;
      $counter_fillout = $counter_fillout + ( $data[$group_id]["fillout"] ? 1:0 );
	  $counter_edit = $counter_edit + ( $data[$group_id]["edit"] ? 1:0 );
	  $counter_evaluate = $counter_evaluate + ( $data[$group_id]["evaluate"] ? 1:0 );
    }
  else
  {
    $tpl->parse("GROUP_ROW", "group_none");
  }

  //output favourites and favourites rights
  if(sizeof($favourites) > 0)
    foreach($favourites as $favourite)
    {
	  $favourite_id = $favourite->get_id();

      $tpl->set_var(array(
        "ITEM_ID" => $favourite_id,
        "FAVOURITE_ID" => $favourite_id,
        "FAVOURITE" => $data[$favourite_id]["name"],
        "CHECKED_FILLOUT" => $data[$favourite_id]["fillout"] ? "CHECKED":"",
        "CHECKED_EDIT" => $data[$favourite_id]["edit"] ? "CHECKED":"",
        "CHECKED_EVALUATE" => $data[$favourite_id]["evaluate"] ? "CHECKED":""
      ));


      //leave items out that are allready in groups
      $double = false;
      foreach($groups as $value)
        if($favourite->get_id() == $value->get_id())
        {
          $double = true;
          break;
        }


      //parse favourite row
      if($double)
      {
        $tpl->parse("FAVOURITE_ROW", "favourite_row_double", true);
      }
      else
      {
        $tpl->parse("FAVOURITE_ROW", "favourite_row", true);
      }

      //parse javascript
      $tpl->parse("SET_CRUDE", "set_crude", true);
      $tpl->parse("UNSET_CRUDE", "unset_crude", true);
      $tpl->parse("TEST_SPECIFIC_CHECKED", "test_specific_checked", true);
      $tpl->parse("TEST_SPECIFIC_UNCHECKED", "test_specific_unchecked", true);


      $counter++;
      $counter_fillout = $counter_fillout + ( $data[$favourite_id]["fillout"] ? 1:0 );
	  $counter_edit = $counter_edit + ( $data[$favourite_id]["edit"] ? 1:0 );
	  $counter_evaluate = $counter_evaluate + ( $data[$favourite_id]["evaluate"] ? 1:0 );
    }
  else
  {
    $tpl->parse("FAVOURITE_ROW", "favourite_none");
  }


    $tpl->set_var(array(
      "RADIO_PUBLIC" => (($counter == $counter_fillout &&
                          $counter_edit > 0 &&
                          $counter_evaluate > 0)?"checked":""),
      "RADIO_PRIVATE" => (($counter_fillout == 0 &&
                           $counter_edit == 0 &&
                           $counter_evaluate == 0)?"checked":""),
      "RADIO_USER" => (($counter_fillout > 0 && $counter_fillout < $counter ||
                        $counter_edit > 0 && $counter_edit < $counter ||
                        $counter_evaluate > 0 && $counter_evaluate < $counter)?"checked":"")
    ));
	
  
  //******************************************************
  //** Buttons
  //******************************************************

  //Save button
  $tpl->set_var(array(
    "BUTTON_MISSION" => "save",
    "BUTTON_URL" => "$config_webserver_ip/modules/questionary/rights.php"
  ));
  $tpl->parse("BUTTON_LABEL", "button_save");
  $tpl->parse("MISSION_BUTTON", "button_mission", true);

  //space
  $tpl->parse("MISSION_BUTTON", "button_spacer", true);
  
  //cancel button
    $tpl->set_var(array(
	    "BUTTON_CANCEL_MISSION" => "close",
    	"BUTTON_CANCEL_URL" => ""
  ));

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