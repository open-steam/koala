<?php

  /****************************************************************************
  index.php - display the start page of the questionary and the possibilities which the user can do
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

  Author: Patrick Tönis
  EMail:  toennis@uni-paderborn.de

  ****************************************************************************/


  //include stuff
  require_once("../../config/config.php");
  require_once("$steamapi_doc_root/steam_connector.class.php");
  require_once("./classes/questionary_geo.php");
  require_once("$config_doc_root/classes/template.inc");
  require_once("$config_doc_root/includes/sessiondata.php");
  require_once("$config_doc_root/includes/derive_menu.php");
  require_once("$config_doc_root/includes/derive_url.php");
  require_once("./classes/rights.php");


  //******************************************************
  //** Presumption
  //******************************************************

  $questionary_id = (isset($_GET["object"]))?$_GET["object"]:((isset($_POST["object"]))?$_POST["object"]:"");
  $action = (isset($_GET["mission"]))?$_GET["mission"]:((isset($_POST["mission"]))?$_POST["mission"]:"");
  
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
    $steam->disconnect();
	header("Location: $config_webserver_ip/accessdenied.html");
    exit();
  }
  
  
  //current steam objects 
  if( (int) $questionary_id != 0 ) 
  {
  	$questionary = steam_factory::get_object( $steam, $questionary_id );
	$question_folder = $questionary->get_object_by_name('questions');
  	$answer_folder = $questionary->get_object_by_name('answers');
	$answers = $answer_folder->get_inventory();
  }
  else  
  {
  	$steam->disconnect();
	header("Location: $config_webserver_ip/index.php");
	exit();
  }
  
  
  //create new RIGHTS object
  $rights = new rights($steam, $questionary, $question_folder, $answer_folder);
  
  
  //check permissions  
  $login_user = $steam->get_login_user();
  $login_user_id = $login_user->get_id();
  $login_user_groups = $login_user->get_groups();
  foreach($login_user_groups as $login_user_group)  $login_user_group_ids[]=$login_user_group->get_id();
  $is_editor = $rights->check_access_fillout($login_user, $login_user_group_ids); 
  $is_analyst = $rights->check_access_evaluate($login_user, $login_user_group_ids); 
  $is_author = $rights->check_access_edit($login_user, $login_user_group_ids); 
  
  
  //get Attributes
  $attributes = $questionary->get_attributes(array(	OBJ_NAME, OBJ_DESC,
													"bid:questionary:fillout",
													"bid:questionary:description",
													"bid:questionary:editownanswer",
													"bid:questionary:edittime",
													"bid:questionary:enabled"
												  ),1 
											); 
  $nor = $answer_folder->count_inventory(1);
  $buffer = $steam->buffer_flush();

  $attributes = $buffer[$attributes];
  $questionary_display_name = $attributes[OBJ_NAME];
  if (isset($attributes[OBJ_DESC]) && $attributes[OBJ_DESC] != "")
  {
    $questionary_display_name = $attributes[OBJ_DESC];
  }

  $number_results = $buffer[$nor];						
  $att_fillout = $attributes["bid:questionary:fillout"];
  $att_description = $attributes["bid:questionary:description"];
  $att_edit_own_answer = $attributes["bid:questionary:editownanswer"];
  $att_edit_time = $attributes["bid:questionary:edittime"];
  $att_enabled = $attributes["bid:questionary:enabled"];


  //delete
  if(isset($_GET['answer']) && $action=="delete" && $att_edit_own_answer)
  {
	$answer = steam_factory::get_object( $steam, $_GET['answer']);
	
	if(is_object($answer) && $answer->get_creator()->get_id() == $steam->get_login_user()->get_id())
	{
		$answer->delete();
		
		//$answers = $answer_folder->get_inventory(); //update $answers
		$steam->disconnect();
		header("Location: $config_webserver_ip/modules/questionary/index.php?object=".$questionary->get_id());
		exit();
	}
  }
  
  
  //enable disable questionary
  if($action=="enable" && $is_author && $att_edit_time[0]==0)
  {
  	$questionary->set_attribute("bid:questionary:enabled", 1);
	$att_enabled=1;
  }
  if($action=="disable" && $is_author && $att_edit_time[0]==0)
  {
  	$questionary->set_attribute("bid:questionary:enabled", 0);
	$att_enabled=0;
  }
  
  
  //check enabled
  if($att_edit_time[0]==1 && time()>$att_edit_time[1] && time()<$att_edit_time[2]) $enabled=1;
  else
  {
	if($att_enabled==1)	$enabled=1;	
	else			$enabled=0;
  } 
  
  
  //check if the user can fill out (another) questionary
  $fillout=1;
  if($att_fillout == "n") $fillout=1;
  else
  {
	//get creators of all items in inventory
	$creators_obj=array();
    foreach($answers as $each_answer)
	{
	  $creators_obj[] = $each_answer->get_creator(1);
	}
	$buffer = $steam->buffer_flush();
		
	//get each creator id
	$creator_ids=array();
	foreach($creators_obj as $each_creator_obj)
	{
		$creator_ids[]= $buffer[$each_creator_obj]->get_id();
	}

    //find current user if he has filledout out it once
    foreach($creator_ids as $creator)
    {
      if($creator == $login_user_id)
	  {
	  	$fillout = 0;
        break;
	  }
	}
  }
  
  
  //******************************************************
  //** Display Stuff
  //******************************************************

  //template stuff
  $tpl = new Template("./templates/$language", "keep");
  $tpl->set_file("content", "index.ihtml");
  
  $tpl->set_block("content", "possibility_new_answer", "DUMMY");
  $tpl->set_block("content", "possibility_view_result", "DUMMY");
  $tpl->set_block("content", "possibility_edit_questionary", "DUMMY");
  $tpl->set_block("content", "no_possibility_row", "NO_POSSIBILITY_ROW");
  $tpl->set_block("content", "no_possibility_no_edit_time", "DUMMY");
  $tpl->set_block("content", "no_possibility_send_already_answer", "DUMMY");
  $tpl->set_block("content", "no_possibility_no_rights", "DUMMY");
  $tpl->set_block("content", "possibility_editanswer_row", "POSSIBILITY_EDITANSWER_ROW");
  $tpl->set_block("content", "possibility_row", "POSSIBILITY_ROW");
  $tpl->set_block("content", "start_possibility_editanswer_row", "START");
  $tpl->set_block("content", "end_possibility_editanswer_row", "END");
  $tpl->set_block("content", "possibility_editanswer_content", "EDIT_ANSWER_CONTENT");
  $tpl->set_block("content", "possibility_editanswer_nocontent", "DUMMY");
  $tpl->set_block("content", "enable_questionary", "DUMMY");
  $tpl->set_block("content", "disable_questionary", "DUMMY");
  $tpl->set_block("content", "creator_area", "CREATOR_AREA");
  $tpl->set_block("content", "creator_possibility_row", "CREATOR_POSSIBILITY_ROW");
  $tpl->set_block("content", "controllcenter_possibility_row", "CONTROLLCENTER_POSSIBILITY_ROW");
  $tpl->set_block("content", "edit_time_row", "EDIT_TIME_ROW");
  
  $tpl->set_var(array(
    "DUMMY" => "",
    "MENU" => "",
	"POSSIBILITY_ROW" => "",
	"NO_POSSIBILITY_ROW" => "",
	"CREATOR_AREA" => "",
	"CREATOR_POSSIBILITY_ROW" => "",
	"CONTROLLCENTER_POSSIBILITY_ROW" => "",
	"START" => "",
	"END" => "",
	"EDIT_ANSWER_CONTENT" => "",
    "QUESTIONARY_ID" => $questionary->get_id(),
	"QUESTIONARY_NAME" => $questionary_display_name,
	"DESCRIPTION" => $att_description,
    "FEEDBACK" => "",
	"EDIT_TIME_ROW" => "",
	"COUNT_RESULT" =>  $number_results
  ));
  
  //start end date
  if($att_edit_time[0]==1)
  {
  	$tpl->set_var( array(	"START_DATE" => date("d.m.Y", $att_edit_time[1]),
    						"END_DATE" => date("d.m.Y", $att_edit_time[2]),
  						)
				  );
	$tpl->parse("EDIT_TIME_ROW", "edit_time_row");
  }
  
  
  //derive Menü
  if($is_author) $tpl->set_var("MENU",derive_menu("questionary", $questionary, "", 3));
  else $tpl->set_var("MENU",derive_menu("questionary", $questionary, "", 0));
    
	
  $possibility_counter=0;
  //fill out possibility
  if($fillout && $enabled && $is_editor)
  {
  	$tpl->parse("LABEL_POSSIBILITY", "possibility_new_answer");
	$tpl->set_var("URL", "$config_webserver_ip/modules/questionary/answer.php?object=".$questionary->get_id());
	$tpl->set_var("ONCLICK", "");
  	$tpl->parse("POSSIBILITY_ROW", "possibility_row", true);
	$possibility_counter++;
  }
  
  //resultview possibility
  if($is_analyst && !$is_author)
  {
  	$tpl->parse("LABEL_POSSIBILITY", "possibility_view_result");
	$tpl->set_var("URL", "$config_webserver_ip/modules/questionary/result.php?object=".$questionary->get_id());
	$tpl->set_var("ONCLICK", "");
  	$tpl->parse("POSSIBILITY_ROW", "possibility_row", true);
	$possibility_counter++;
  }
    
  
  //edit answers
  if($att_edit_own_answer && $enabled && $is_editor)
  {
	$z=0;
	$tpl->parse("START", "start_possibility_editanswer_row");
	
	foreach($answers as $answer)
	{
	  if($answer->get_creator()->get_id() == $steam->get_login_user()->get_id())
	  {		
		$creation_time = $answer->get_attribute("OBJ_CREATION_TIME");
		$timeformat=date("d.m.Y G:i", $creation_time)." Uhr";
		
		$tpl->set_var(array(	"CREATION_TIME" => $timeformat,
								"URL_EDIT"		=> "$config_webserver_ip/modules/questionary/answer.php?object=".$questionary->get_id()."&answer=".$answer->get_id(),
								"ANSWER_ID"		=> $answer->get_id()
							)
					 );
		if($z%2==0)	$tpl->set_var("BGCOLOR", "#DDDDDD");
		else 		$tpl->set_var("BGCOLOR", "#D5D5D5");
		$tpl->parse("EDIT_ANSWER_CONTENT", "possibility_editanswer_content", true);
		$z++;
	  }
	}
	if($z==0) 
	{
		$tpl->parse("EDIT_ANSWER_CONTENT", "possibility_editanswer_nocontent");
	}
	
	$tpl->parse("END", "end_possibility_editanswer_row");
	
  }
  
  //no possibilitie row
  if($possibility_counter==0)
  {
	if($fillout==0) 
	{	
		$tpl->parse("NO_POSSIBILITY", "no_possibility_send_already_answer");
		$tpl->parse("NO_POSSIBILITY_ROW", "no_possibility_row");
	}
	else
	{
		if($enabled==0) 
		{
			$tpl->parse("NO_POSSIBILITY", "no_possibility_no_edit_time");
			$tpl->parse("NO_POSSIBILITY_ROW", "no_possibility_row");
		}
		else
		{
			$tpl->parse("NO_POSSIBILITY", "no_possibility_no_rights");
			$tpl->parse("NO_POSSIBILITY_ROW", "no_possibility_row");
		}
	}
  }
  
  
  //AUTHOR AREA
  if($is_author)
  {	
	$tpl->parse("CREATOR_AREA", "creator_area");
	
	//edit Questionary
	if(count($answer_folder->get_inventory())==0)
	{
		$tpl->parse("LABEL_CONTROLLCENTER_POSSIBILITY", "possibility_edit_questionary");
		$tpl->set_var("CONTROLLCENTER_ONCLICK", "window.open('$config_webserver_ip/modules/questionary/edit.php?questionary=".$questionary->get_id()."', 'editPortlet', 'resizable, scrollbars, width=700, height=600')");
		$tpl->parse("CONTROLLCENTER_POSSIBILITY_ROW", "controllcenter_possibility_row");
	}
	
	//resultview possibility
	$tpl->parse("LABEL_CREATOR_POSSIBILITY", "possibility_view_result");
	$tpl->set_var("CREATOR_URL", "$config_webserver_ip/modules/questionary/result.php?object=".$questionary->get_id());
	$tpl->parse("CREATOR_POSSIBILITY_ROW", "creator_possibility_row", true);
	
	//enable disable questionary
	if($att_edit_time[0]==0)
	{
		if($att_enabled==1)		
		{
			$tpl->parse("LABEL_CREATOR_POSSIBILITY", "disable_questionary");
			$tpl->set_var("CREATOR_URL", "$config_webserver_ip/modules/questionary/index.php?mission=disable&object=".$questionary->get_id());
		}
		else
		{
			$tpl->parse("LABEL_CREATOR_POSSIBILITY", "enable_questionary");
			$tpl->set_var("CREATOR_URL", "$config_webserver_ip/modules/questionary/index.php?mission=enable&object=".$questionary->get_id());
		}
		$tpl->parse("CREATOR_POSSIBILITY_ROW", "creator_possibility_row", true);
	}      	
  }
  
  
  out();
  
  function out()
  {
    //parse all out
    global $tpl;
    $tpl->parse("OUT", "content");
    $tpl->p("OUT");

    exit;
  }
  
  //Logout & Disconnect
  $steam->disconnect();
  
?>