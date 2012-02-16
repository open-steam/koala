<?php

  /****************************************************************************
  answer.php - display a questionary
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

  Author: Patrick T�nnis
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
  require_once("$config_doc_root/includes/derive_url.php");
  require_once("$config_doc_root/includes/norm_post.php");
  require_once("./classes/rights.php");
  require_once("$config_doc_root/classes/UBBCode.php");


  //******************************************************
  //** Presumption
  //******************************************************

  $questionary_id = (isset($_GET["object"]))?$_GET["object"]:((isset($_POST["object"]))?$_POST["object"]:"");
  $answer_id = (isset($_GET["answer"]))?$_GET["answer"]:((isset($_POST["answer"]))?$_POST["answer"]:false);
  $page_number = (int) (isset($_POST["page"]))?$_POST["page"]:0;
  $direction = (isset($_POST["direction"]))?$_POST["direction"]:"";
  

  if($direction == "next")  $new_page_number = $page_number + 1;
  else 	if($direction == "previous") $new_page_number = $page_number - 1;
  		else $new_page_number = $page_number;

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

  //login user
  $login_user = $steam->get_login_user();
  $login_user_id = $login_user->get_id();

  //current room steam object
  if( (int) $questionary_id != 0 ) 
  {
  	$questionary = steam_factory::get_object( $steam, $questionary_id );
	$question_folder = $questionary->get_object_by_name('questions');
  	$answer_folder = $questionary->get_object_by_name('answers');
	if((int) $answer_id!= 0 ) 
	{
		$answer = steam_factory::get_object( $steam, $answer_id );
		$is_answer_creator = $answer->get_creator()->get_id() == $login_user_id;
	}
  }
  else  
  {
  	header("Location: $config_webserver_ip/index.php");
  }
  

  //create new RIGHTS object
  $rights = new rights($steam, $questionary, $question_folder, $answer_folder);
  
  
  //check permissions  
  $login_user_groups = $login_user->get_groups();
  foreach($login_user_groups as $login_user_group)  $login_user_group_ids[]=$login_user_group->get_id();
  $is_editor = $rights->check_access_fillout($login_user, $login_user_group_ids); 
  $is_analyst = $rights->check_access_evaluate($login_user, $login_user_group_ids); 
  $is_author = $rights->check_access_edit($login_user, $login_user_group_ids); 
  if(!$is_author && !$is_editor )//|| isset($answer) && !$is_answer_creator && !$is_author)
  {
    //Disconnect & close
    $steam->disconnect();
    die("<html><body>No access right</body></html>");
  }
					
  
  //get attributes
  $attributes = $questionary->get_attributes(array(	OBJ_NAME, OBJ_DESC,
  													"bid:questionary:fillout", 
													"bid:questionary:number",
													"bid:questionary:edittime",
													"bid:questionary:editanswer",
													"bid:questionary:editownanswer",
													"bid:questionary:enabled"
												   )
											);
  $fillout = $attributes["bid:questionary:fillout"];
  $questionary_display_name = $attributes[OBJ_NAME];
  if (isset($attributes[OBJ_DESC]) && $attributes[OBJ_DESC] != "")
  {
    $questionary_display_name = $attributes[OBJ_DESC];
  }
  $question_number = $attributes["bid:questionary:number"];
  $edit_time = $attributes["bid:questionary:edittime"];
  $edit_answer = $attributes["bid:questionary:editanswer"];
  $edit_own_answer = $attributes["bid:questionary:editownanswer"];
  $att_enabled = $attributes["bid:questionary:enabled"];
  
  
  //to avoid that user can change answers from other users
  if( (isset($answer) && !$is_answer_creator && $is_author && !$edit_answer) || (isset($answer) && !$is_answer_creator && !$is_author))
  {
    $no_write_access = true;
  }
  

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
 
 
  //check if the user can fill out (another) questionary
  $forbidden = false;
  if($fillout == 1 && !isset($answer))
  {
    //get creators of all items in inventory
	$creators_obj=array();
	$answers = $answer_folder->get_inventory();
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
      if($forbidden = ($creator == $login_user_id)) break;
  }


  //check if the questionary is enabled
  if($edit_time[0]==1 && time()>$edit_time[1] && time()<$edit_time[2]) $enabled=1;
  else
  {
	if($att_enabled==1)	$enabled=1;	
	else			$enabled=0;
  } 


  //prepare to edit an answer
  if($answer && count($post) == 0 && ($is_author && $edit_answer || $is_answer_creator && $edit_own_answer))
  {
	$post = $answer->get_attribute("bid:questionary:input");
    
	if(!is_array($post))  $post = array();
  }
  

  //check if mandatory fields havent been filled out (only if there has been a page change)
  if($page_number < $new_page_number || $direction == "finish")
  {
    $error = array();
    $check_content = $geo->get_page($page_number);
    foreach($check_content as $tmp)
    {
      $entity = $tmp;
      $must = (isset($entity["must"]) && $entity["must"]);
      if(!$must) continue;

      switch($entity["type"])
      {
        case QUESTIONARY_INPUT_CHECKBOX:
          $input_id = trim($entity["input_id"]);
          $options = $entity["options"];
          $isset = false;
          foreach($options as $key => $text)
            $isset = $isset || isset($post[$input_id . "_" . $key]);
          if(!$isset)
            $error[] = $entity["question"];
          break;
        case QUESTIONARY_INPUT_RADIO:
        case QUESTIONARY_INPUT_SELECT:
          if(!isset($post[trim($entity["input_id"])]))
            $error[] = $entity["question"];
          break;
        case QUESTIONARY_INPUT_TEXT:
        case QUESTIONARY_INPUT_TEXTAREA:
          if(trim($post[trim($entity["input_id"])]) == "")
            $error[] = $entity["question"];
          break;
		case QUESTIONARY_INPUT_GRADING:
          $input_id = trim($entity["input_id"]);
          $grading_options = $entity["grading_options"];
          $isset = false;
          foreach($grading_options as $key => $text)
		  {
            if(!isset($post[$input_id . "_" . $key]) ) $error[] = $text;
		  }
          break;
		case QUESTIONARY_INPUT_TENDENCY:
          $input_id = trim($entity["input_id"]);
          $tendency_elements = $entity["tendency_elements"];
          $isset = false;
          foreach($tendency_elements as $key => $text)
		  {
            if(!isset($post[$input_id . "_" . $key]) ) $error[] = $text[0]." - ".$text[1] ;
		  }
          break;
      }
    }
  }

  //Action save
  if($direction == "finish" && (isset($error) && count($error) == 0) && $is_editor && !$forbidden && $enabled)
  {
    $all = $geo->get_all();

    $input = array();
    foreach($all as $item)
    {
      if(isset($item["input_id"]))
      {
        $input_id = trim($item["input_id"]);
        switch($item["type"])
        {
          case QUESTIONARY_INPUT_CHECKBOX:	$options = array();
											foreach($item["options"] as $key => $option)
											   if(isset($post[$input_id."_".$key]))	$options[] = $key;
											$input[$input_id] = $options;
											break;
		  case QUESTIONARY_INPUT_GRADING:	$grading_options = array();
											foreach($item["grading_options"] as $key => $option)
											   if(isset($post[$input_id."_".$key]))	$grading_options[] = $post[$input_id."_".$key];
											$input[$input_id] = $grading_options;
											break;	
		  case QUESTIONARY_INPUT_TENDENCY:	$tendency_elements = array();
											foreach($item["tendency_elements"] as $key => $option)
											   if(isset($post[$input_id."_".$key]))	$tendency_elements[] = $post[$input_id."_".$key];
											$input[$input_id] = $tendency_elements;
											break;		
		  default:							$input[$input_id] = $post[$input_id];
        }         
      }
    }

    //if answer is set then store data in answer object otherwise create a new one
    if($answer) $input_object = $answer;
    else $input_object = steam_factory::create_container( $steam, time()."", $answer_folder );

    //if answer object is ok then save data
    if($input_object)
      $saved = $input_object->set_attribute("bid:questionary:input", $input);
  }


  //******************************************************
  //** Display Stuff
  //******************************************************

  //template stuff
  $tpl = new Template("./templates/$language", "keep");
  $tpl->set_file("content", "answer.ihtml");
  $tpl->set_block("content", "error", "DUMMY");
  $tpl->set_block("content", "error_no_write_access", "DUMMY");
  $tpl->set_block("content", "error_multiple_input", "DUMMY");
  $tpl->set_block("content", "error_not_enabled", "DUMMY");
  $tpl->set_block("content", "saved", "DUMMY");
  $tpl->set_block("content", "empty_line", "DUMMY");
  $tpl->set_block("content", "full_line", "DUMMY");
  $tpl->set_block("content", "caption", "DUMMY");
  $tpl->set_block("content", "description", "DUMMY");
  $tpl->set_block("content", "input_text", "DUMMY");
  $tpl->set_block("content", "input_textarea", "DUMMY");
  $tpl->set_block("content", "input_checkbox_cell", "INPUT_CHECKBOX_CELL");
  $tpl->set_block("content", "input_checkbox_row", "INPUT_CHECKBOX_ROW");
  $tpl->set_block("content", "input_checkbox", "DUMMY");
  $tpl->set_block("content", "input_radiobutton_cell", "INPUT_RADIOBUTTON_CELL");
  $tpl->set_block("content", "input_radiobutton_row", "INPUT_RADIOBUTTON_ROW");
  $tpl->set_block("content", "input_radiobutton", "DUMMY");
  $tpl->set_block("content", "input_selectbox_row", "INPUT_SELECTBOX_ROW");
  $tpl->set_block("content", "input_selectbox", "DUMMY");
  $tpl->set_block("content", "input_grading_row", "INPUT_GRADING_ROW");
  $tpl->set_block("content", "input_grading", "DUMMY");
  $tpl->set_block("content", "input_tendency_cell", "INPUT_TENDENCY_CELL");
  $tpl->set_block("content", "input_tendency_row", "INPUT_TENDENCY_ROW");
  $tpl->set_block("content", "input_tendency", "DUMMY");
  $tpl->set_block("content", "question_align_left", "DUMMY");
  $tpl->set_block("content", "question_align_top", "DUMMY");
  $tpl->set_block("content", "quest_row", "QUEST_ROW");
  $tpl->set_block("content", "feedback_row", "FEEDBACK_ROW");
  $tpl->set_block("content", "button_previous", "BUTTON_PREV");
  $tpl->set_block("content", "form", "FORM");
  $tpl->set_block("content", "form_edit", "DUMMY");
  $tpl->set_block("content", "button_next", "BUTTON_NEXT");
  $tpl->set_block("content", "button_finish", "BUTTON_FIN");
  $tpl->set_block("content", "button_home", "BUTTON_HOME");
  $tpl->set_block("content", "hidden_row", "HIDDEN_ROW");
  $tpl->set_block("content", "progress_row", "PROGRESS_ROW");
  $tpl->set_var(array(
    "DUMMY" => "",
    "MENU" => "",
    "OBJECT_ID" => $questionary->get_id(),
    "ANSWER_ID" => $answer_id,
    "QUESTIONARY_NAME" => $questionary_display_name,
    "HIDDEN_ROW" => "",
    "QUEST_ROW" => "",
    "FEEDBACK_ROW" => "",
	"PROGRESS_ROW" => "",
	"BUTTON_HOME" => "",
    "FORM" => ""
  ));
  
  $UBB = new UBBCode();
  
  if($is_author) {
    $tpl->set_var("MENU",derive_menu("questionary", $questionary, "", 3));
  }
  else {
    $tpl->set_var("MENU",derive_menu("questionary", $questionary, "", 0));
  }

  //check if input is possible => exit
  if(!$is_editor || $forbidden || !$enabled || $no_write_access)
  {
    //check if user is allowed to write in object
    if(!$is_editor || $no_write_access)
	{
      $tpl->parse("FEEDBACK", "error_no_write_access"); 
	  $tpl->parse("FEEDBACK_ROW", "feedback_row", true);
	}
    else if($forbidden) //check if user is allowed to fill out form (if not single input and multiple tries come along)
	{
      $tpl->parse("FEEDBACK", "error_multiple_input"); 
	  $tpl->parse("FEEDBACK_ROW", "feedback_row", true);
	}
	else if(!$enabled)
	{
      $tpl->parse("FEEDBACK", "error_not_enabled"); 
	  $tpl->parse("FEEDBACK_ROW", "feedback_row", true);
	}
	
	$tpl->set_var(array(
    "BUTTON_FIN" => "",
    "BUTTON_PREV" => "",
    "BUTTON_NEXT" => ""
  	));

	//home button
	$tpl->parse("BUTTON_HOME", "button_home");

    out();
  }


  //output if saved => exit
  if(isset($saved) && $saved)
  {
    $tpl->set_var(array(
      "BUTTON_FIN" => "",
   	  "BUTTON_PREV" => "",
      "BUTTON_NEXT" => "",
      "FORM" => ""
    ));
    $tpl->parse("FEEDBACK", "saved"); 
	$tpl->parse("FEEDBACK_ROW", "feedback_row", true);
	
    //home button
	$tpl->parse("BUTTON_HOME", "button_home");
	
	out();
  }


  //if its an edit of an existing answer parse out proper heading form-tag
  $tpl->parse("FORM", (($answer)?"form_edit":"form"));


  //parse input errors if there are any
  if(isset($error) && count($error) > 0)
    foreach($error as $question)
    {
      $tpl->set_var("QUESTION", $question); 
	  $tpl->parse("FEEDBACK", "error"); 
	  $tpl->parse("FEEDBACK_ROW", "feedback_row", true);
    }
  else
    $page_number = $new_page_number;


  //set new page number
  $tpl->set_var("PAGE_NUMBER", $page_number);


  //set progress bar
  $count_pages = $geo->get_count_pages();
  if($count_pages>1)
  {
  	for($i=1; $i<=$count_pages; $i++)
	{
		if($i==$page_number+1) $output .= " - <b>".$i."</b> -&nbsp;&nbsp;";   
		else				 $output .= " - ".$i." -&nbsp;&nbsp;";
	}
  	$tpl->set_var("PROGRESS", $output);
	$tpl->parse("PROGRESS_ROW", "progress_row");
  }


  //build current questionary page
  $double = false;
  $page_content = $geo->get_page($page_number);
  $numbering = ($question_number)?$geo->get_page_questionnumber($page_number):0;
  foreach($page_content as $segment)
  {
    $entity = $segment;

    //clear cell for line
    $tpl->unset_var("QUEST_CELL");


    //get question number if needed
    $numbering_string = ($numbering != 0 && isset($entity["input_id"]))?$numbering++ . ". ":"";


    // build HTML for each element
    switch($entity["type"])
    {
      case QUESTIONARY_DESCRIPTION:
        $tpl->set_var("DESCRIPTION", $UBB->encode ($entity["text"]));
        $tpl->parse("QUEST_CELL", "description", true);
        break;
		
	  case QUESTIONARY_CAPTION:
        $tpl->set_var("CAPTION", nl2br(norm_post($entity["text"])));
        $tpl->parse("QUEST_CELL", "caption", true);
        break;

      case QUESTIONARY_EMPTY_LINE:
        $tpl->parse("QUEST_CELL", "empty_line", true);
        break;

      case QUESTIONARY_FULL_LINE:
        $tpl->parse("QUEST_CELL", "full_line", true);
        break;

      case QUESTIONARY_INPUT_CHECKBOX:
        $columns = trim($entity["columns"]);
        $input_id = trim($entity["input_id"]);
        $tpl->set_var(array(
          "INPUT_ID" => $input_id,
          "QUESTION" => $numbering_string . nl2br(norm_post($entity["question"])),
          "QUESTION_STYLE" => (($entity["must"])?"bold":"normal")
        ));
        $tpl->unset_var("INPUT_CHECKBOX_ROW");

        //check whether this question is already ansered, to know that the default values shouldnt marked
		$question_answered=false;
		foreach($entity["options"] as $value => $text)
        {
			if(isset($post[$input_id."_".$value])) $question_answered=true;
		}
		
		$col_items = 1;
		foreach($entity["options"] as $value => $text)
        {
			//mark the answerpossibility if the question is already answered
			if(	isset($post[$input_id."_".$value]) || ( isset($post[$input_id]) && in_array($value, $post[$input_id]) ) ) $checked ="CHECKED"; 
			else 	if(in_array($value, $entity["checked"]) && !$question_answered && !isset($answer) )	$checked="CHECKED"; //mark the predefined answer but only if this question wasnt answered before
					else $checked="";
		  
			//$checked = isset($post[$input_id."_".$value]) || ( isset($post[$input_id]) && in_array($value, $post[$input_id]) )  ?"CHECKED":"";
			if(isset($post[$input_id . "_" . $value]))
			unset($post[$input_id . "_" . $value]);
	
			$tpl->set_var(array(
				"INPUT_ID_OPTION" => $input_id . "_" . $value,
				"VALUE" => $value,
				"CHECKED" => $checked,
				"OPTION" => $text
			));
			$tpl->parse("INPUT_CHECKBOX_CELL", "input_checkbox_cell", true);
			if($col_items++ >= $columns || $entity["options"][$value]==end($entity["options"]))
			{
				$col_items = 1;
				$tpl->parse("INPUT_CHECKBOX_ROW", "input_checkbox_row", true);
				$tpl->unset_var("INPUT_CHECKBOX_CELL");
			}
        }
        $tpl->unset_var("INPUT_CHECKBOX_CELL");

        $tpl->parse("INPUT_ELEMENT", "input_checkbox");
        $tpl->parse("QUEST_CELL", (($entity["question_position"] == "left")?"question_align_left":"question_align_top"), true);
        break;


      case QUESTIONARY_INPUT_RADIO:
        $input_id = trim($entity["input_id"]);
        //get the value either from post saves or preselected
        $checked = (isset($post[$input_id]))?$post[$input_id]:$entity["checked"];
        $tpl->set_var(array(
          "INPUT_ID" => $input_id,
          "QUESTION" => $numbering_string . nl2br(norm_post($entity["question"])),
          "QUESTION_STYLE" => (($entity["must"])?"bold":"normal")
        ));
        $tpl->unset_var("INPUT_RADIOBUTTON_ROW");

        $col_items = 1;
        $columns = trim($entity["columns"]);
        foreach($entity["options"] as $value => $text)
        {
          $tpl->set_var(array(
            "VALUE" => $value,
            "CHECKED" => (($value == $checked)?"CHECKED":""),
            "OPTION" => $text
          ));
          $tpl->parse("INPUT_RADIOBUTTON_CELL", "input_radiobutton_cell", true);
          if($col_items++ >= $columns || $entity["options"][$value]==end($entity["options"]))
          {
            $col_items = 1;
            $tpl->parse("INPUT_RADIOBUTTON_ROW", "input_radiobutton_row", true);
            $tpl->unset_var("INPUT_RADIOBUTTON_CELL");
          }
        }
        $tpl->unset_var("INPUT_RADIOBUTTON_CELL");

        $tpl->parse("INPUT_ELEMENT", "input_radiobutton");
        $tpl->parse("QUEST_CELL", (($entity["question_position"] == "left")?"question_align_left":"question_align_top"), true);
        break;


      case QUESTIONARY_INPUT_SELECT:
        $input_id = trim($entity["input_id"]);
        //get the value either from post saves or preselected
        $selected = (isset($post[$input_id]))?$post[$input_id]:$entity["selected"];
        $tpl->set_var(array(
          "INPUT_ID" => $input_id,
          "QUESTION" => $numbering_string . nl2br(norm_post($entity["question"])),
          "QUESTION_STYLE" => (($entity["must"])?"bold":"normal"),
          "ROWS" => trim($entity["rows"]),
          "WIDTH" => trim($entity["width"])
        ));
        $tpl->unset_var("INPUT_SELECTBOX_ROW");

        foreach($entity["options"] as $value => $text)
        {
          $tpl->set_var(array(
            "VALUE" => $value,
            "SELECTED" => (($value == $selected)?"SELECTED":""),
            "OPTION" => $text
          ));
          $tpl->parse("INPUT_SELECTBOX_ROW", "input_selectbox_row", true);
        }
        $tpl->parse("INPUT_ELEMENT", "input_selectbox");
        $tpl->parse("QUEST_CELL", (($entity["question_position"] == "left")?"question_align_left":"question_align_top"), true);
        break;


      case QUESTIONARY_INPUT_TEXT:
        $input_id = trim($entity["input_id"]);
        //get the value either from post saves or preselected
        $value = (isset($post[$input_id]))?$post[$input_id]:trim($entity["value"]);
        $tpl->set_var(array(
          "INPUT_ID" => $input_id,
          "QUESTION" => $numbering_string . nl2br(norm_post($entity["question"])),
          "QUESTION_STYLE" => (($entity["must"])?"bold":"normal"),
          "MAXLENGTH" => trim($entity["maxlength"]),
          "WIDTH" => trim($entity["width"]),
          "VALUE" => $value
        ));
        $tpl->parse("INPUT_ELEMENT", "input_text");
        $tpl->parse("QUEST_CELL", (($entity["question_position"] == "left")?"question_align_left":"question_align_top"), true);
        break;

      case QUESTIONARY_INPUT_TEXTAREA:
        $input_id = trim($entity["input_id"]);
        //get the value either from post saves or preselected
        $value = (isset($post[$input_id]))?$post[$input_id]:trim($entity["value"]);
        $tpl->set_var(array(
          "INPUT_ID" => $input_id,
          "QUESTION" => $numbering_string . nl2br(norm_post($entity["question"])),
          "QUESTION_STYLE" => (($entity["must"])?"bold":"normal"),
          "WIDTH" => trim($entity["width"]),
          "HEIGHT" => trim($entity["height"]),
          "VALUE" => $value
        ));
        $tpl->parse("INPUT_ELEMENT", "input_textarea");
        $tpl->parse("QUEST_CELL", (($entity["question_position"] == "left")?"question_align_left":"question_align_top"), true);
        break;
		
	  case QUESTIONARY_INPUT_GRADING:
		$input_id = trim($entity["input_id"]);      
        $tpl->set_var(array(	"QUESTION" => $numbering_string . nl2br(norm_post($entity["description"])),
          						"QUESTION_STYLE" => (($entity["must"])?"bold":"normal")
        					));
        $tpl->unset_var("INPUT_GRADING_ROW");
		
		foreach($entity["grading_options"] as $value => $text)
        {
			$tpl->set_var(array(
				"INPUT_ID" => $input_id."_".$value,
				"GRADING_OPTION" => $text,
				"CSS_CLASS" => $value%2==0 ? "grading_row_1": "grading_row_2"
			  ));
			for($i=1; $i<7; $i++)
			{
				$tpl->set_var("CHECKED_".$i, (isset($post[$input_id."_".$value]) && $post[$input_id."_".$value]==$i) || $post[$input_id][$value]==$i ?'checked="checked"':"");
			}  
     		$tpl->parse("INPUT_GRADING_ROW", "input_grading_row", true);
        }
        $tpl->parse("INPUT_ELEMENT", "input_grading");
        $tpl->parse("QUEST_CELL", "question_align_top", true);
        break;
		
	  case QUESTIONARY_INPUT_TENDENCY:
		$input_id = trim($entity["input_id"]);
        $tpl->set_var(array(	"QUESTION" => $numbering_string . nl2br(norm_post($entity["description"])),
          						"QUESTION_STYLE" => (($entity["must"])?"bold":"normal")
        					));
        $tpl->unset_var("INPUT_TENDENCY_ROW");
		
		foreach($entity["tendency_elements"] as $value => $text)
        {
			$checked="";
			$tpl->set_var(array(
				"TENDENCY_ELEMENT_A" => $text[0],
				"TENDENCY_ELEMENT_B" => $text[1],
				"CSS_CLASS" => $value%2==0 ? "tendency_row_2": "tendency_row_1"
			  ));
			$tpl->unset_var("INPUT_TENDENCY_CELL");
			for($i=1; $i<=$entity["tendency_steps"]; $i++)
			{
				//get the value either from post saves or preselected
				$tpl->set_var(array(	"CHECKED" => isset($post[$input_id."_".$value]) && $post[$input_id."_".$value]==$i || $post[$input_id][$value]==$i ?'checked="checked"':"",
										"INPUT_ID" => $input_id."_".$value,
										"VALUE" => $i
				));
				$tpl->parse("INPUT_TENDENCY_CELL", "input_tendency_cell", true);
			}  
     		$tpl->parse("INPUT_TENDENCY_ROW", "input_tendency_row", true);
        }
        $tpl->parse("INPUT_ELEMENT", "input_tendency");
        $tpl->parse("QUEST_CELL", "question_align_top", true);
        break;
    }

    //erase currently displayed elements from post save list
    if(isset($entity["input_id"]))
      unset($post[trim($entity["input_id"])]);


    //parse the whole row
    $tpl->parse("QUEST_ROW", "quest_row", true);
  }

  //parse old post without the elements value currently displayed
  foreach($post as $key => $post_var)
  {
    if($key == "object" ||
       $key == "page" ||
       $key == "direction")
    continue;

	if(is_array($post_var))		//that happend where multiple answers are possible
	{
		$type=$geo->get_type($key);
		switch($type)
		{
			case QUESTIONARY_INPUT_TENDENCY:	
			case QUESTIONARY_INPUT_GRADING:	
				foreach($post_var as $id => $value)
				{
					$tpl->set_var(array(
					  "HIDDEN_NAME" => $key."_".$id,
					  "HIDDEN_VALUE" => $value
					));
					$tpl->parse("HIDDEN_ROW", "hidden_row", true);
				}
				break;
			case QUESTIONARY_INPUT_CHECKBOX:	
				foreach($post_var as $id => $value)
				{
					$tpl->set_var(array(
					  "HIDDEN_NAME" => $key."_".$value,
					  "HIDDEN_VALUE" => $value
					));
					$tpl->parse("HIDDEN_ROW", "hidden_row", true);
				}
		}
	}
	else
	{
		$tpl->set_var(array(
		  "HIDDEN_NAME" => $key,
		  "HIDDEN_VALUE" => $post_var
		));
		$tpl->parse("HIDDEN_ROW", "hidden_row", true);
	}
  }

  //Disconnect
  $steam->disconnect();


  //******************************************************
  //** Buttons
  //******************************************************

  $tpl->set_var(array(
    "BUTTON_FIN" => "",
    "BUTTON_PREV" => "",
    "BUTTON_NEXT" => ""
  ));

  //prev button
  if($page_number >= 1)
    $tpl->parse("BUTTON_PREV", "button_previous", true);

  //next or finish button
  if($geo->is_last_page($page_number))
    $tpl->parse("BUTTON_FIN", "button_finish", true);
  else
    $tpl->parse("BUTTON_NEXT", "button_next", true);


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