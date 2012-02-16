<?php

  /****************************************************************************
  layout.php - edit layout settings of a questionary
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

  ****************************************************************************/


  //include stuff
  require_once("../../config/config.php");
  require_once("./config/config.php");
  require_once("$steamapi_doc_root/steam_connector.class.php");
  require_once("$config_doc_root/classes/template.inc");
  require_once("$config_doc_root/includes/sessiondata.php");
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
  
  
  //save
  $error=array();
  if($action=="save")
  {  
  	if($_POST['background']=="" || $_POST['hr_color']=="" || $_POST['question_background']=="" || $_POST['question_border_color']=="" || $_POST['question_text_color']=="" || $_POST['question_text_size']=="" || $_POST['caption_background']=="" || $_POST['caption_border_color']=="" || $_POST['caption_text_color']=="" || $_POST['caption_text_size']=="" || $_POST['answer_background']=="" || $_POST['answer_border_color']=="" || $_POST['answer_text_color']=="" || $_POST['answer_text_size']=="")
	{	
		$error[]="error_empty_field";
	}
	if($_POST['question_text_size']<7 || $_POST['question_text_size']>73 || $_POST['caption_text_size']<7 || $_POST['caption_text_size']>73 || $_POST['answer_text_size']<7 || $_POST['answer_text_size']>73)
	{
		$error[]="error_text_size";
	}
	if(!preg_match('/^#[0-9ABCDEFabcdef]{6}$/',$_POST['background']) || !preg_match('/^#[0-9ABCDEFabcdef]{6}$/',$_POST['hr_color']) || !preg_match('/^#[0-9ABCDEFabcdef]{6}$/',$_POST['question_background']) || !preg_match('/^#[0-9ABCDEFabcdef]{6}$/',$_POST['question_border_color']) || !preg_match('/^#[0-9ABCDEFabcdef]{6}$/',$_POST['question_text_color']) || !preg_match('/^#[0-9ABCDEFabcdef]{6}$/',$_POST['caption_background']) || !preg_match('/^#[0-9ABCDEFabcdef]{6}$/',$_POST['caption_border_color']) || !preg_match('/^#[0-9ABCDEFabcdef]{6}$/',$_POST['caption_text_color']) || !preg_match('/^#[0-9ABCDEFabcdef]{6}$/',$_POST['answer_background']) || !preg_match('/^#[0-9ABCDEFabcdef]{6}$/',$_POST['answer_border_color']) || !preg_match('/^#[0-9ABCDEFabcdef]{6}$/',$_POST['answer_text_color']))
	{
		$error[]="error_color_format";
	}
	if(count($error)==0)
	{
		//build layout array
		$layout["background"] = $_POST['background'];
		$layout["hr_color"] = $_POST['hr_color'];
		$layout["question_background"] = $_POST['question_background'];
		$layout["question_border_color"] = $_POST['question_border_color'];
		$layout["question_text_color"] = $_POST['question_text_color'];
		$layout["question_text_size"] = $_POST['question_text_size'];
		$layout["caption_background"] = $_POST['caption_background'];
		$layout["caption_border_color"] = $_POST['caption_border_color'];	
		$layout["caption_text_color"] = $_POST['caption_text_color'];
		$layout["caption_text_size"] = $_POST['caption_text_size'];
		$layout["answer_background"] = $_POST['answer_background'];
		$layout["answer_border_color"] = $_POST['answer_border_color'];	
		$layout["answer_text_color"] = $_POST['answer_text_color'];
		$layout["answer_text_size"] = $_POST['answer_text_size'];
		$layout["template"] = $_POST['template'];	
		
		//save
		$result=$questionary->set_attribute("bid:questionary:layout", $layout);
	}
  }
  
  
  //get attributes
  $questionary_name = $questionary->get_name();
  $layout = $questionary->get_attribute("bid:questionary:layout");
  
  
  //Disconnect
  $steam->disconnect();
  
  
  //******************************************************
  //** Display Stuff
  //******************************************************

  //template stuff
  $tpl = new Template("./templates/$language", "keep");
  $tpl->set_file("content", "edit_layout.ihtml");
  $tpl->set_block("content", "button_mission", "MISSION_BUTTON");
  $tpl->set_block("content", "button_spacer", "DUMMY");
  $tpl->set_block("content", "button_save", "DUMMY");
  $tpl->set_block("content", "error_text_size", "DUMMY");
  $tpl->set_block("content", "error_color_format", "DUMMY");
  $tpl->set_block("content", "error_empty_field", "DUMMY");
  $tpl->set_block("content", "data_saved", "DUMMY");
  $tpl->set_block("content", "option_template", "OPTION_TEMPLATE");
  $tpl->set_block("content", "jsarray_template", "JSARRAY_TEMPLATE");
    
  $tpl->set_var(array(
    "DUMMY" => "",
	"FEEDBACK" => "",
    "QUESTIONARY_ID" => $questionary->get_id(),
	"QUESTIONARY_NAME" => $questionary_name,
	"BUTTON_CANCEL_MISSION" => "",
    "BUTTON_CANCEL_URL" => "$config_webserver_ip/modules/questionary/edit.php"
  ));
  
  
  //set message
  if(count($error)>0)
  {
  	foreach( $error as $err)
	{
		$tpl->parse("FEEDBACK", $err, true);
	}
  }
  else if($result) $tpl->parse("FEEDBACK", 'data_saved', true);
  
  
  //set template javascript array
  foreach($templates as $template)
  {
	  $tpl->set_var(array(
		"TEMPLATE_VALUE"		=> $template["template"],
		"BACKGROUND"			=> $template["background"],
		"HR_COLOR"				=> $template["hr_color"],
		"CAPTION_BACKGROUND"	=> $template["caption_background"],
		"CAPTION_BORDER_COLOR"	=> $template["caption_border_color"],
		"CAPTION_TEXTCOLOR"		=> $template["caption_text_color"],
		"CAPTION_TEXT_SIZE"		=> $template["caption_text_size"],
		"QUESTION_BACKGROUND"	=> $template["question_background"],
		"QUESTION_BORDER_COLOR"	=> $template["question_border_color"],
		"QUESTION_TEXT_COLOR"	=> $template["question_text_color"],
		"QUESTION_TEXT_SIZE"	=> $template["question_text_size"],
		"ANSWER_BACKGROUND"		=> $template["answer_background"],
		"ANSWER_BORDER_COLOR"	=> $template["answer_border_color"],
		"ANSWER_TEXT_COLOR"		=> $template["answer_text_color"],
		"ANSWER_TEXT_SIZE"		=> $template["answer_text_size"]			
		));
	$tpl->parse("JSARRAY_TEMPLATE", "jsarray_template", true);
  }
  
  
  //set template combobox
  foreach($templates as $template)
  {
	$tpl->set_var(array(
		"TEMPLATE_VALUE"		=> $template["template"],
		"TEMPLATE_SELECT"		=> isset($_POST["template"]) && $_POST["template"]==$template["template"] ? 'selected="selected"' : ($layout["template"]==$template["template"] ? 'selected="selected"':""),
		"TEMPLATE_NAME"			=> $language=="ge" ? $template["name_ge"]:$template["name_en"]				
		));
	$tpl->parse("OPTION_TEMPLATE", "option_template", true);
  }

  
  //set formular
  if(isset($_POST['background']))
  {
  	$tpl->set_var(array(
		"BACKGROUND" => $_POST["background"],
		"HR_COLOR" => $_POST["hr_color"],
		"QUESTION_BACKGROUND" => $_POST["question_background"],
		"QUESTION_BORDER_COLOR" => $_POST["question_border_color"],
		"QUESTION_TEXT_COLOR" => $_POST["question_text_color"],
		"QUESTION_TEXT_SIZE" => $_POST["question_text_size"],
		"CAPTION_BACKGROUND" => $_POST["caption_background"],
		"CAPTION_BORDER_COLOR" => $_POST["caption_border_color"],
		"CAPTION_TEXT_COLOR" => $_POST["caption_text_color"],
		"CAPTION_TEXT_SIZE" => $_POST["caption_text_size"],
		"ANSWER_BACKGROUND" => $_POST["answer_background"],
		"ANSWER_BORDER_COLOR" => $_POST["answer_border_color"],
		"ANSWER_TEXT_COLOR" => $_POST["answer_text_color"],
		"ANSWER_TEXT_SIZE" => $_POST["answer_text_size"]
	  ));
  }
  else
  {
	$tpl->set_var(array(
		"BACKGROUND" => $layout["background"],
		"HR_COLOR" => $layout["hr_color"],
		"QUESTION_BACKGROUND" => $layout["question_background"],
		"QUESTION_BORDER_COLOR" => $layout["question_border_color"],
		"QUESTION_TEXT_COLOR" => $layout["question_text_color"],
		"QUESTION_TEXT_SIZE" => $layout["question_text_size"],
		"CAPTION_BACKGROUND" => $layout["caption_background"],
		"CAPTION_BORDER_COLOR" => $layout["caption_border_color"],
		"CAPTION_TEXT_COLOR" => $layout["caption_text_color"],
		"CAPTION_TEXT_SIZE" => $layout["caption_text_size"],
		"ANSWER_BACKGROUND" => $layout["answer_background"],
		"ANSWER_BORDER_COLOR" => $layout["answer_border_color"],
		"ANSWER_TEXT_COLOR" => $layout["answer_text_color"],
		"ANSWER_TEXT_SIZE" => $layout["answer_text_size"]
	  ));
  }
  
  
  //******************************************************
  //** Buttons
  //******************************************************

  //Save button
  $tpl->set_var(array(
    "BUTTON_MISSION" => "save",
    "BUTTON_URL" => "$config_webserver_ip/modules/questionary/edit_layout.php"
  ));
  $tpl->parse("BUTTON_LABEL", "button_save");
  $tpl->parse("MISSION_BUTTON", "button_mission", true);

  //space
  $tpl->parse("MISSION_BUTTON", "button_spacer", true);


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