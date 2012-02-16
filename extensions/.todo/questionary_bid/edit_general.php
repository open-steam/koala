<?php

  /****************************************************************************
  edit_general.php - edit general settings of a questionary
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
  require_once("./classes/rights.php");


  //******************************************************
  //** Presumption
  //******************************************************

  $questionary_id = (isset($_GET["questionary"]))?$_GET["questionary"]:((isset($_POST["questionary"]))?$_POST["questionary"]:"");
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
  	header("Location: $config_webserver_ip/index.php");
  }


  //create new RIGHTS object
  $rights = new rights($steam, $questionary, $question_folder, $answer_folder);
  

  //check author permission  
  $login_user = $steam->get_login_user();
  $login_user_id = $login_user->get_id();
  $login_user_groups = $login_user->get_groups();
  foreach($login_user_groups as $login_user_group)  $login_user_group_ids[]=$login_user_group->get_id();
  $is_author = $rights->check_access_edit($login_user, $login_user_group_ids); 
  if(!$is_author || count($answer_folder->get_inventory())>0)
  {
    //Disconnect & close
    $steam->disconnect();
    die("<html>\n<body onload='javascript:window.close();'>\n</body>\n</html>");
  }
  

  //get questionary attributes
  $attributes = $questionary->get_attributes(array(
    OBJ_NAME,
	"bid:questionary:description",
	"bid:questionary:edittime",
    "bid:questionary:fillout",
    "bid:questionary:number",
    "bid:questionary:editanswer",
	"bid:questionary:editownanswer",
	"bid:questionary:enabled",
    "bid:questionary:resultcreator",
    "bid:questionary:resultcreationtime"
  ));
  $questionary_name = $attributes[OBJ_NAME];
  $number = $attributes["bid:questionary:number"];
  $description = $attributes["bid:questionary:description"];
  $edittime = $attributes["bid:questionary:edittime"];
  $fillout = $attributes["bid:questionary:fillout"];
  $editanswer = $attributes["bid:questionary:editanswer"];
  $editownanswer = $attributes["bid:questionary:editownanswer"];
  $enabled = $attributes["bid:questionary:enabled"];
  $resultcreator = $attributes["bid:questionary:resultcreator"];
  $resultcreationtime = $attributes["bid:questionary:resultcreationtime"];


  $errors=array();
  
  
  //Action save
  if($action == "save")
  {
    if($_POST['edit_time']==1) //check dates
	{
		$first_date = mktime(0,0,0,$_POST['from_month'],$_POST['from_day'], $_POST['from_year']);
		$sec_date = mktime(23,59,59, $_POST['to_month'], $_POST['to_day'], $_POST['to_year']);

		if($first_date==$edittime[1] && $sec_date==$edittime[2])	$arr_edit_time=array(1, $first_date, $sec_date); //save old dates
		else
		{		
			//check new dates		
			if(!checkdate($_POST['from_month'],$_POST['from_day'],$_POST['from_year']))	$errors[]="error_first_no_date";
			if(!checkdate($_POST['to_month'],$_POST['to_day'],$_POST['to_year']))	$errors[]="error_sec_no_date";
					
			if($first_date>$sec_date)	$errors[]="error_sec_date_is_smaller";
			if($first_date + 86400 < time())		$errors[]="error_first_date_is_past";
			
			if(count($errors)==0) $arr_edit_time=array(1, $first_date, $sec_date);
			else $arr_edit_time=array(0,0,0);
		}
	}
	else $arr_edit_time=array(0,0,0);
	
	
	if(count($errors)==0)
	{
		$attributes = array(
		  "bid:questionary:number" => (isset($_POST["number"])),
		  "bid:questionary:description" => norm_post($_POST["description"]),
		  "bid:questionary:fillout" => $_POST["fillout"],
		  "bid:questionary:editanswer" => $_POST["editanswer"],
		  "bid:questionary:editownanswer" => $_POST["editownanswer"],
		  "bid:questionary:edittime" => $arr_edit_time,
		  "bid:questionary:enabled" => $arr_edit_time[0]==1 ? false : $enabled,
		  "bid:questionary:resultcreator" => $_POST["resultcreator"],
		  "bid:questionary:resultcreationtime" => $_POST["resultcreationtime"]
		);
		$result = $questionary->set_attributes($attributes);
	}
  }


  //Disconnect
  $steam->disconnect();


  //redirect to edit page again
  if(isset($result) && $result)
  {
    header("Location: $config_webserver_ip/modules/questionary/edit.php?questionary=" . $questionary->get_id());
    exit();
  }


  //******************************************************
  //** Display Stuff
  //******************************************************

  //template stuff
  $tpl = new Template("./templates/$language", "keep");
  $tpl->set_file("content", "edit_general.ihtml");
  $tpl->set_block("content", "label_save", "DUMMY");
  $tpl->set_block("content", "label_properties", "DUMMY");
  $tpl->set_block("content", "button_mission", "MISSION_BUTTON");
  $tpl->set_block("content", "button_space", "DUMMY");
  $tpl->set_block("content", "combo_to_day_entry", "TO_DAY_ENTRY");
  $tpl->set_block("content", "combo_from_day_entry", "FROM_DAY_ENTRY");
  $tpl->set_block("content", "combo_to_year_entry", "TO_YEAR_ENTRY");
  $tpl->set_block("content", "combo_from_year_entry", "FROM_YEAR_ENTRY");
  $tpl->set_block("content", "error_first_no_date", "DUMMY");
  $tpl->set_block("content", "error_sec_no_date", "DUMMY");
  $tpl->set_block("content", "error_sec_date_is_smaller", "DUMMY");
  $tpl->set_block("content", "error_first_date_is_past", "DUMMY");
  $tpl->set_block("content", "error_feedback", "ERROR_FEEDBACK");
  
  $tpl->set_var(array(
    "DUMMY" => "",
    "QUESTIONARY_ID" => $questionary->get_id(),
	"ERROR_FEEDBACK" =>"",
    "BUTTON_CANCEL_MISSION" => "",
    "BUTTON_CANCEL_URL" => "$config_webserver_ip/modules/questionary/edit.php",
	"QUESTIONARY_NAME" => $questionary_name
  ));
  
  
  //display errors
  if(count($errors)>0)
  {
  	foreach($errors as $error)
	{
		$tpl->parse("FEEDBACK", $error);
		$tpl->parse("ERROR_FEEDBACK", "error_feedback" , true);
	}
  }
  
  
  //disable date if edittime isnt set
  if($edittime[0]==0) $tpl->set_var("DISABLED", 'disabled="disabled"');
  
  
  //set from day
  for($day=1; $day<=31; $day++) 	
  {	
	$tpl->set_var("FROM_DAY", $day);
	if($edittime[1]!=0 && date("j", $edittime[1]) == $day) $tpl->set_var("FROM_DAY_SELECTED", 'selected="selected"');
	else $tpl->set_var("FROM_DAY_SELECTED", '');
	$tpl->parse("FROM_DAY_ENTRY", "combo_from_day_entry", true);
  }
  //set from month
  for($m=1; $m<=12; $m++) 	
  {
	if( $edittime[1]!=0 && date("n", $edittime[1]) == $m ) $tpl->set_var("FROM_MONTH_SELECTED_".$m, 'selected="selected"');
	else $tpl->set_var("FROM_MONTH_SELECTED_".$m, '');
  }
  //set from year
  $year_today=date("Y");
  for($year=$year_today; $year<$year_today+5; $year++) 	
  {
	$tpl->set_var("FROM_YEAR", $year);
	if($edittime[1]!=0 && date("Y", $edittime[1]) == $year) $tpl->set_var("FROM_YEAR_SELECTED", 'selected="selected"');
	else $tpl->set_var("FROM_YEAR_SELECTED", '');	
	$tpl->parse("FROM_YEAR_ENTRY", "combo_from_year_entry", true);
  }
  
  //set to day
  for($day=1; $day<=31; $day++) 	
  {
	$tpl->set_var("TO_DAY", $day);
	if($edittime[2]!=0 && date("j", $edittime[2]) == $day) $tpl->set_var("TO_DAY_SELECTED", 'selected="selected"');
	else $tpl->set_var("TO_DAY_SELECTED", '');
	$tpl->parse("TO_DAY_ENTRY", "combo_to_day_entry", true);
  }
  //set to month
  for($m=1; $m<=12; $m++) 	
  {
	if($edittime[2]!=0 && date("n", $edittime[2]) == $m) $tpl->set_var("TO_MONTH_SELECTED_".$m, 'selected="selected"');
	else $tpl->set_var("TO_MONTH_SELECTED_".$m, '');
  }
  //set to year
  for($year=$year_today; $year<$year_today+5; $year++)
  {
	$tpl->set_var("TO_YEAR", $year);
	if($edittime[2]!=0 && date("Y", $edittime[2]) == $year) $tpl->set_var("TO_YEAR_SELECTED", 'selected="selected"');
	else $tpl->set_var("TO_YEAR_SELECTED", '');
	$tpl->parse("TO_YEAR_ENTRY",  "combo_to_year_entry", true);
  }	

  //set values
  $tpl->set_var(array(
    "NUMBER" => (($number)?"CHECKED":""),
	"EDIT_TIME" => (($edittime[0])?"CHECKED":""),
	"DESCRIPTION" => $description,
    "FILL_OUT_1" => (($fillout === "1")?"CHECKED":""),
    "FILL_OUT_N" => (($fillout === "n")?"CHECKED":""),
    "EDIT_ANSWER_TRUE" => (($editanswer)?"CHECKED":""),
    "EDIT_ANSWER_FALSE" => ((!$editanswer)?"CHECKED":""),
	"EDIT_OWN_ANSWER_TRUE" => (($editownanswer)?"CHECKED":""),
    "EDIT_OWN_ANSWER_FALSE" => ((!$editownanswer)?"CHECKED":""),
    "RESULT_CREATOR_TRUE" => (($resultcreator)?"CHECKED":""),
    "RESULT_CREATOR_FALSE" => ((!$resultcreator)?"CHECKED":""),
    "RESULT_CREATION_TIME_TRUE" => (($resultcreationtime)?"CHECKED":""),
    "RESULT_CREATION_TIME_FALSE" => ((!$resultcreationtime)?"CHECKED":"")
  ));
  
  
  //******************************************************
  //** Buttons
  //******************************************************

  //Save button
  $tpl->set_var(array(
    "BUTTON_MISSION" => "save",
    "BUTTON_URL" => "$config_webserver_ip/modules/questionary/edit_general.php"
  ));
  $tpl->parse("BUTTON_LABEL", "label_save");
  $tpl->parse("MISSION_BUTTON", "button_mission", true);

  //space
  $tpl->parse("MISSION_BUTTON", "button_space", true);


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