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
  require_once("./classes/sort_array.php");
  require_once("./classes/nav_bar.php");
  require_once("$config_doc_root/includes/sessiondata.php");
  require_once("$config_doc_root/includes/derive_menu.php");
  require_once("$config_doc_root/includes/derive_url.php");
  require_once("./classes/rights.php");
  

  //******************************************************
  //** Presumption
  //******************************************************

  $questionary_id = (isset($_GET["object"]))?$_GET["object"]:((isset($_POST["object"]))?$_POST["object"]:"");
  $answer_id = (int) (isset($_GET["answer"]))?$_GET["answer"]:0;
  $mission = (isset($_GET["mission"]))?$_GET["mission"]:"";
  $page_number = (int) (isset($_GET["page"]))?$_GET["page"]:0;
  $sort_direction = (isset($_GET["sort"]) && $_GET["sort"] == "d")?SORT_DESCENDING:SORT_ASCENDING;
  $sort_input_id = (isset($_GET["input_id"]))?$_GET["input_id"]:"";
  $breakresult = (isset($_GET["breakresult"]))?$_GET["breakresult"]:((isset($_POST["breakresult"]))?$_POST["breakresult"]:"10");

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
    $steam->disconnect();
	header("Location: $config_webserver_ip/accessdenied.html");
    exit();
  }


  //current room steam object
  if( (int) $questionary_id != 0 ) 
  {
  	$questionary = steam_factory::get_object( $steam, $questionary_id );
	$question_folder = $questionary->get_object_by_name('questions');
  	$answer_folder = $questionary->get_object_by_name('answers');
	if((int) $answer_id != 0 ) $answer = steam_factory::get_object( $steam, $answer_id );
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
  if(!$is_author && !$is_analyst)
  {
    //Disconnect & close
    $steam->disconnect();
    die("<html><body>no access rights</body></html>");
  }  
  
  
  //get questionary attributes
  $creator = $questionary->get_creator();
  $attributes = $questionary->get_attributes(array(OBJ_NAME,
  											OBJ_LAST_CHANGED,
                                            "bid:questionary:number",
                                            "bid:questionary:editanswer",
                                            "bid:questionary:resultcreator",
                                            "bid:questionary:resultcreationtime"
											) );
  
  $questionary_last_changed = $attributes[OBJ_LAST_CHANGED];
  $questionary_name = $attributes[OBJ_NAME];
  $numbering = $attributes["bid:questionary:number"];
  $editanswer = $attributes["bid:questionary:editanswer"];
  $resultcreator = $attributes["bid:questionary:resultcreator"];
  $resultcreationtime = $attributes["bid:questionary:resultcreationtime"];


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


  //action delete
  if($mission == "delete" && $answer && $is_author)
  {
    $answer->delete();
	$steam->disconnect();
	header("Location: $config_webserver_ip/modules/questionary/result.php?object=".$questionary->get_id());
	exit();
  }
	

  //get all input_ids from all questions that are in the results list
  $header = array();
  foreach($entities as $id => $entity)
    if(isset($entity["input_id"]) && $entity["output"])
    {
	  switch($entity["type"])
	  {
	  	case QUESTIONARY_INPUT_GRADING:
			$grading_question="&#10;";
			foreach($entity["grading_options"] as $oid => $value)
			{
				$grading_question.= $value."&#10;";
			}
			$header[$id] = array( trim($entity["input_id"]), $grading_question );
			break;
		case QUESTIONARY_INPUT_TENDENCY:
			$tendency_question="&#10;";
			foreach($entity["tendency_elements"] as $oid => $value)
			{
				$tendency_question.= $value[0]." - ".$value[1]."&#10;";
			}
			$header[$id] = array( trim($entity["input_id"]), $tendency_question );
			break;
		default:
			$header[$id] = array( trim($entity["input_id"]), trim($entity["question"]) );	
	  }
    }
  $colspan = count($header)*2;	//header + space row
  if($editanswer)  $colspan++;
  if($resultcreator) $colspan+=2;	//cell + space row
  if($resultcreationtime) $colspan+=2; //cell + space row
  
  
  //get all results
  $inventory = $answer_folder->get_inventory();
  
  //get creator objects
  //if needed ... get creatornames, else without names
  $values=array();
  if($resultcreator)
  {
    $i=0;
	foreach($inventory as $id => $single_answer)  
	{
		$values[$i] = $single_answer->get_attributes(array("bid:questionary:input", OBJ_CREATION_TIME));
		$values[$i]["obj_id"] = $single_answer->get_id();
		$values[$i]["creator_object_id"] = $single_answer->get_creator()->get_id();
		$values[$i]["creator_name"] = $single_answer->get_creator()->get_name();
		$i++;
	}	
  }
  else
  {
  	$i=0;
	foreach($inventory as $id => $single_answer)  
	{
		$values[$i] = $single_answer->get_attributes(array("bid:questionary:input", OBJ_CREATION_TIME));
		$values[$i]["obj_id"] = $single_answer->get_id();
		$i++;
	}
  }

  //sort results
  $sort = new sort_array($values);
  $values = $sort->sort($sort_input_id, $sort_direction, 1);


  //******************************************************
  //** Display Stuff
  //******************************************************

  //template stuff
  $tpl = new Template("./templates/$language", "keep");
  $tpl->set_file("content", "result.ihtml");
  $tpl->set_block("content", "error_not_allowed", "DUMMY");
  $tpl->set_block("content", "header_creator", "DUMMY");
  $tpl->set_block("content", "header_creation_time", "DUMMY");
  $tpl->set_block("content", "button_ascending", "DUMMY");
  $tpl->set_block("content", "button_descending", "DUMMY");
  $tpl->set_block("content", "button_ascending_selected", "DUMMY");
  $tpl->set_block("content", "button_descending_selected", "DUMMY");
  $tpl->set_block("content", "header_cell", "HEADER_CELL");
  $tpl->set_block("content", "header_cell_selected", "DUMMY");
  $tpl->set_block("content", "header_cell_blank", "DUMMY");
  $tpl->set_block("content", "result_cell", "RESULT_CELL");
  $tpl->set_block("content", "edit_cell", "DUMMY");
  $tpl->set_block("content", "delete_cell", "DUMMY");
  $tpl->set_block("content", "result_row", "RESULT_ROW");
  $tpl->set_block("content", "result_row_none", "DUMMY");
  $tpl->set_var(array(
    "DUMMY" => "",
    "MENU" => derive_menu("questionary", $questionary, "", (($is_author)?3:1)),

    "OBJECT_ID" => $questionary->get_id(),
    "QUESTIONARY_NAME" => $questionary_name,
    "COLSPAN" => $colspan,
    "CURRENT_PAGE" => $page_number,
	"SELECTED_10" => (($breakresult === "10")?"SELECTED":""),
    "SELECTED_30" => (($breakresult === "30")?"SELECTED":""),
	"SELECTED_50" => (($breakresult === "50")?"SELECTED":""),
    "SELECTED_ALL" => (($breakresult === "all")?"SELECTED":""),
	"BREAKRESULT" => $breakresult,
	"CREATOR" => $creator->get_name(),
	"LAST_CHANGED" => date("d.m.Y H:i", $questionary_last_changed)
  ));


  //parse out error message if forbidden
  if($forbidden)
  {
    $tpl->parse("content", "error_not_allowed");
    out();
  }


  //get navigation bar
  $navbar = new nav_bar($_SERVER["REQUEST_URI"], count($answer_folder->get_inventory()), $page_number, $breakresult);
  $tpl->set_var("NAV_BAR", $navbar->get_bar());


  //parse out header
  $q_number=1;
  foreach($header as $header_element)
  {
    if($numbering) 
	{	
		$number_output=$q_number.". ";
		$q_number++;
	}
	else $number_output="";
	$tpl->set_var("HEADER_TITLE", $number_output.$header_element[1]);
	$tpl->set_var("HEADER_TITLE_SHORT", strlen($number_output.$header_element[1])>25?substr($number_output.$header_element[1],0,25)."...":$number_output.$header_element[1]);
	$tpl->set_var("INPUT_ID", $header_element[0]);
    $tpl->parse("BUTTON_ASCENDING", (($header_element[0] == $sort_input_id && $sort_direction == SORT_ASCENDING)?"button_ascending_selected":"button_ascending"));
    $tpl->parse("BUTTON_DESCENDING", (($header_element[0] == $sort_input_id && $sort_direction == SORT_DESCENDING)?"button_descending_selected":"button_descending"));
    $tpl->parse("HEADER_CELL", (($header_element[0] == $sort_input_id)?"header_cell_selected":"header_cell"), true);
  }
  //answer author header
  if($resultcreator)
  {
    $tpl->parse("HEADER_TITLE", "header_creator");
	$tpl->parse("HEADER_TITLE_SHORT", "header_creator");
	$tpl->set_var("INPUT_ID", "creator");
	$tpl->parse("BUTTON_ASCENDING", (($sort_input_id == "creator" && $sort_direction == SORT_ASCENDING)?"button_ascending_selected":"button_ascending"));
    $tpl->parse("BUTTON_DESCENDING", (($sort_input_id == "creator" && $sort_direction == SORT_DESCENDING)?"button_descending_selected":"button_descending"));
    $tpl->parse("HEADER_CELL", (($sort_input_id == "creator")?"header_cell_selected":"header_cell"), true);
  }

  //creation time header
  if($resultcreationtime)
  {
    $tpl->parse("HEADER_TITLE", "header_creation_time");
	$tpl->parse("HEADER_TITLE_SHORT", "header_creation_time");
	$tpl->set_var("INPUT_ID", OBJ_CREATION_TIME);
	$tpl->parse("BUTTON_ASCENDING", (($sort_input_id == OBJ_CREATION_TIME && $sort_direction == SORT_ASCENDING)?"button_ascending_selected":"button_ascending"));
    $tpl->parse("BUTTON_DESCENDING", (($sort_input_id == OBJ_CREATION_TIME && $sort_direction == SORT_DESCENDING)?"button_descending_selected":"button_descending"));
    $tpl->parse("HEADER_CELL", (($sort_input_id == "creation_Time")?"header_cell_selected":"header_cell"), true);
  }

  //header cells for the edit cells
  if($editanswer)
    $tpl->parse("HEADER_CELL", "header_cell_blank", true);
  


  //parse out no results message
  if(count($inventory) == 0) $tpl->parse("RESULT_ROW", "result_row_none");
  else //parse out results
  {
    $item_count = 0;
    if($breakresult=="all")
	{
		$startitem = 0;
    	$enditem = count($inventory);
	}
	else
	{
		$startitem = $breakresult * $page_number;
    	$enditem = $startitem + $breakresult;
	}

	foreach($values as $key => $value)
    {
      $item_count++;
      if($item_count <= $startitem) continue;
      if($item_count > $enditem) break;
	  
      $tpl->unset_var("RESULT_CELL");
	  
	  $tpl->set_var("ANSWER_ID",  $value['obj_id']);

      $result = $value["bid:questionary:input"];
	  foreach($header as $id => $header_element)
      {
        $question = $geo->get_id($id);
		
        switch ($question["type"])
        {
          case QUESTIONARY_INPUT_SELECT:
          case QUESTIONARY_INPUT_RADIO:
            $text = @$question["options"][$result[$header_element[0]]];
            break;
          case QUESTIONARY_INPUT_CHECKBOX:
            $text = "";
			foreach($result[$header_element[0]] as $option)
              $text .= @$question["options"][$option] . "<br>";
            break;
		  case QUESTIONARY_INPUT_TENDENCY:
		  case QUESTIONARY_INPUT_GRADING:
            $text = "";
            foreach($result[$header_element[0]] as $option)
              $text .= $option."<br>";
            break;
          default:
            $text = @$result[$header_element[0]];
            break;
        }
		
        //parse cell either selected or not
        $tpl->set_var(array(
          "RESULT" => stripslashes($text),
          "BGCOLOR" => get_color($header_element[0], $sort_input_id, $item_count)
        ));
        $tpl->parse("RESULT_CELL", "result_cell", true);
      }

      //author name cell
      if($resultcreator)
      {
        $tpl->set_var(array(
          "RESULT" => $value["creator_name"],
          "BGCOLOR" => get_color("creator", $sort_input_id, $item_count)
        ));
        $tpl->parse("RESULT_CELL", "result_cell", true);
      }


      //creation date cell
      if($resultcreationtime)
      {
        $tpl->set_var(array(
          "RESULT" => date("H:i d.m.y", $value["OBJ_CREATION_TIME"]),
          "BGCOLOR" => get_color(OBJ_CREATION_TIME, $sort_input_id, $item_count)
        ));
        $tpl->parse("RESULT_CELL", "result_cell", true);
      }


      //set right color for "details" and maybe "edit"
      $tpl->set_var("BGCOLOR", get_color("", "details", $item_count));

      //if answer is editable parse out proper link
      if($editanswer && $is_author) $tpl->parse("RESULT_CELL", "edit_cell", true);

      //authors can delete answers
      if($is_author)  $tpl->parse("RESULT_CELL", "delete_cell", true);
	  
	  //insert 2 empty cells for analysts, instead of the two icons for delete and edit
	  if(!$is_author)
	  {
	  	$tpl->set_var("RESULT","");
        $tpl->parse("RESULT_CELL", "result_cell", true);
	  }

      //parse row
      $tpl->parse("RESULT_ROW", "result_row", true);
    }
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
      return ($name != $sort)?"#DDDDDD":"#D5D5D5";
    else
      return ($name != $sort)?"#FFFFFF":"#E8E8E8";
  }

?>