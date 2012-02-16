<?php
  /****************************************************************************
  result_details.php - view all answers of ONE participant
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
  require_once("./classes/questionary_geo.php");
  require_once("$config_doc_root/includes/sessiondata.php");
  require_once("$config_doc_root/includes/derive_menu.php");
  require_once("./classes/rights.php");
  

  //******************************************************
  //** Presumption
  //******************************************************

  $questionary_id = (isset($_GET["object"]))?$_GET["object"]:((isset($_POST["object"]))?$_POST["object"]:"");
  $answer_id = (isset($_GET["answer"]))?$_GET["answer"]:((isset($_POST["answer"]))?$_POST["answer"]:"");

  //save post vars
  $post = $_POST;


  //******************************************************
  //** sTeam Stuff
  //******************************************************

  $steam = new steam_connector(	$config_server_ip,
  								$config_server_port,
  								$login_name,
  								$login_pwd);

  if( !$steam || !$steam->get_login_status() )
  {
    header("Location: $config_webserver_ip/accessdenied.html");
    exit();
  }


  //current steam objects 
  if( (int) $questionary_id != 0 ) 
  {
  	$questionary = steam_factory::get_object( $steam, $questionary_id );
	$question_folder = $questionary->get_object_by_name('questions');
  	$answer_folder = $questionary->get_object_by_name('answers');
	if((int) $answer_id != 0 ) $answer = steam_factory::get_object( $steam, $answer_id );
  }
  else  
  {
	header("Location: $config_webserver_ip/index.php");
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
  if(!$is_author && !$is_analyst)
  {
    //Disconnect & close
    $steam->disconnect();
    die("<html><body></body></html>");
  }  
  
  
  $attributes = $questionary->get_attributes(array(OBJ_NAME,
                                                   OBJ_CREATION_TIME,
                                                   "bid:questionary:geometry",
												   "bid:questionary:resultcreator",
												   "bid:questionary:resultcreationtime",
												   "bid:questionary:number")  );
  $resultcreator = $attributes["bid:questionary:resultcreator"];
  $resultcreationtime = $attributes["bid:questionary:resultcreationtime"];  
  $numbering=$attributes["bid:questionary:number"];
  $questionary_name = $attributes[OBJ_NAME];
  $creator = $answer->get_creator();
  $creator_name = $creator->get_name();
  $answer = $answer->get_attribute("bid:questionary:input");
  
  
  //check rights
  $forbidden = !($is_author || $is_analyst);


  //get questionary geometry
  $questions = $question_folder->get_inventory();
  $geo = new questionary_geo();
  foreach($questions as $question)
  {
	$question=$question->get_attribute("bid:question:geometry",1);
  }
  $buffer = $steam->buffer_flush();
  foreach($buffer as $question)
  {
 	$geo->insert($question);
  }
  $entities = $geo->get_all();


  //get all input_ids from all questions that are in the results list
  $header_names = array();
  $header_ids = array();
  foreach($entities as $id => $entity)
    if(isset($entity["input_id"]))
    {
      $header_names[$id] = trim($entity["question"]);
	  $header_ids[$id] = trim($entity["input_id"]);
    }


  //******************************************************
  //** Display Stuff
  //******************************************************

  //template stuff
  $tpl = new Template("./templates/$language", "keep");
  $tpl->set_file("content", "result_details.ihtml");
  $tpl->set_block("content", "error_not_allowed", "DUMMY");
  $tpl->set_block("content", "check_on", "DUMMY");
  $tpl->set_block("content", "check_off", "DUMMY");
  $tpl->set_block("content", "answer_row", "ANSWER_ROW");
  $tpl->set_block("content", "button_mission", "DUMMY");
  $tpl->set_block("content", "creator_row", "CREATOR_ROW");
  $tpl->set_block("content", "creation_time_row", "CREATION_TIME_ROW");
 
  $tpl->set_var(array(
    "DUMMY" => "",
    "OBJECT_ID" => $questionary->get_id(),
    "QUESTIONARY_NAME" => $questionary_name,
    "CREATION_TIME_ROW" => "",
	"CREATOR_ROW" 	=> "",
    "BUTTON_CANCEL_URL" => "",
    "BUTTON_CANCEL_MISSION" => "close"
  ));


  //parse out error message if forbidden
  if($forbidden)
  {
    $tpl->parse("content", "error_not_allowed");
    out();
  }
  
  //parse author and creationtime
  if($resultcreator)
  {
  	$tpl->set_var("CREATOR_NAME", $creator_name);
	$tpl->parse("CREATOR_ROW", "creator_row");
  }
  if($resultcreationtime)
  {
  	$tpl->set_var("CREATION_TIME", date("H:i:s - d.m.Y", $attributes[OBJ_CREATION_TIME]));
	$tpl->parse("CREATION_TIME_ROW", "creation_time_row");
  }

  $question_counter=1;
  foreach($header_ids as $key => $name)
  {
    $question_name="";
	$question = $geo->get_id($key);
	switch ($question["type"])
    {
      case QUESTIONARY_INPUT_SELECT:
      case QUESTIONARY_INPUT_RADIO:
        $text = "";
        foreach($question["options"] as $option_key => $option_name)
          $text .= $tpl->get_var((($option_key == $answer[$name])?"check_on":"check_off")) . $option_name . "<br>";
        break;
      case QUESTIONARY_INPUT_CHECKBOX:
        $text = "";
        foreach($question["options"] as $option_key => $option_name)
          $text .= $tpl->get_var(((in_array($option_key, $answer[$name]))?"check_on":"check_off")) . $option_name . "<br>";
        break;
	  case QUESTIONARY_INPUT_GRADING:
        $text = "";
		if($numbering) 
		{
			$question_name = $question_counter.".<br>";
			$text.="<br>";
		}
		foreach($question["grading_options"] as $option_key => $option_name)
		{
		  $question_name .= $option_name."<br>";
		  $text.= $answer[$name][$option_key]."<br>";
		}
		break;
	  case QUESTIONARY_INPUT_TENDENCY:
        $text = "";
		if($numbering) 
		{
			$question_name = $question_counter.".<br>";
			$text.="<br>";
		}
		foreach($question["tendency_elements"] as $option_key => $option_name)
		{
		  $question_name .= $option_name[0]." - ".$option_name[1]."<br>";
		  $text.= $answer[$name][$option_key]."<br>";
		}
		break;
      default:
        $text = $answer[$name];
        break;
    }

    if($numbering && $question_name=="")	$question_name=$question_counter.". ".$question["question"];
	else if($question_name=="")	$question_name=$question["question"];
	
	$tpl->set_var(array(
      "QUESTION" => $question_name,
      "ANSWER" => $text
    ));

    $tpl->parse("CHECK_MUST", (($question["must"])?"check_on":"check_off"));
    $tpl->parse("ANSWER_ROW", "answer_row", true);
	$question_counter++;
  }

  
  //Disconnect
  $steam->disconnect();


  out();

  function out()
  {
    //parse all out
    global $tpl;
    $tpl->parse("OUT", "content");
    $tpl->p("OUT");

    exit;
  }

  function get_color($name, $sort, $count)
  {
    if($count % 2 == 0)
      return ($name != $sort)?"#EFEFEF":"#D8D8D8";
    else
      return ($name != $sort)?"#FFFFFF":"#E8E8E8";
  }

?>