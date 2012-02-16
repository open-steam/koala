<?php
  /****************************************************************************
  questionary_geo.php - class questionary_geo 
  Copyright (C)

  ///////////////////////////////////////////////////////////////////////////////////
  //CONSTRUCTOR
  function questionary_geo()
  
  //PUBLIC METHODS
  function get_all()
  function add_description($text)
  function add_caption($text)
  function add_empty_line()
  function add_full_line()
  function add_input_checkbox($question, $question_position, $columns, $options, $checked, $must, $output)
  function add_input_radiobutton($question, $question_position, $columns, $options, $checked, $must, $output)
  function add_input_selectbox($question, $question_position, $width, $rows, $options, $selected, $must, $output)
  function add_input_text($question, $question_position, $width, $maxlength, $value, $must, $output)
  function add_input_textarea($question, $question_position, $width, $height, $value, $must, $output)
  function add_new_page()
  function add_input_grading($description, $grading_options, $must, $output)
  function add_input_tendency($description, $tendency_elements, $steps, $must, $output)
  function insert($def)
  function get_type($input_id)
  function get_page($page = 0)
  function get_count_pages()
  function get_page_questionnumber($page = 0)
  function is_last_page($page)
  function get_id($id)
  function sequence_array($array)
  ///////////////////////////////////////////////////////////////////////////////////

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


define("QUESTIONARY_DESCRIPTION", 0);
define("QUESTIONARY_EMPTY_LINE", 1);
define("QUESTIONARY_INPUT_CHECKBOX", 2);
define("QUESTIONARY_INPUT_FILE", 3);
define("QUESTIONARY_INPUT_RADIO", 4);
define("QUESTIONARY_INPUT_SELECT", 5);
define("QUESTIONARY_CAPTION", 6);
define("QUESTIONARY_INPUT_TEXT", 7);
define("QUESTIONARY_INPUT_TEXTAREA", 8);
define("QUESTIONARY_NEW_PAGE", 9);
define("QUESTIONARY_FULL_LINE", 10);
define("QUESTIONARY_INPUT_GRADING", 11);
define("QUESTIONARY_INPUT_TENDENCY", 12);


class questionary_geo
{

  var $elements;
  
  
  //************************************************************
  // Constructor
  //
  // Set default to all variables
  //************************************************************
  function questionary_geo()
  {
    $this->elements = array();
  }
  

  //************************************************************
  //method: get_all()
  //return = all elements
  //************************************************************
  function get_all()
  {
    return $this->elements;
  }


  //************************************************************
  //method: add_description()
  //		adds a description element
  //
  //@param string $text
  //************************************************************
  function add_description($text)
  {
    $def = array(
      "type" => QUESTIONARY_DESCRIPTION,
      "text" => $text
    );

    $this->insert($def);
  }
  
  //************************************************************
  //method: add_caption()
  //		adds a caption element
  //
  //@param string $text
  //************************************************************
  function add_caption($text)
  {
    $def = array(
      "type" => QUESTIONARY_CAPTION,
      "text" => $text
    );

    $this->insert($def);
  }

  //************************************************************
  //method: add_empty_line()
  //		adds a empty_line
  //
  //************************************************************
  function add_empty_line()
  {
    $def = array(
      "type" => QUESTIONARY_EMPTY_LINE
    );

    $this->insert($def);
  }

  //************************************************************
  //method: add_full_line()
  //		adds a full line
  //
  //************************************************************
  function add_full_line()
  {
    $def = array(
      "type" => QUESTIONARY_FULL_LINE
    );

    $this->insert($def);
  }

  //************************************************************
  //method: add_input_checkbox()
  //		adds a checkbox question
  //
  //@param string $question	(question text)
  //@param string $question_position (top or left)
  //@param int $columns	(number of columns)
  //@param array  $options (answer possibilities)
  //@param 		  $checked	(prechecked value)
  //@param bool $must	(mandatory)
  //@param bool $output  
  //************************************************************
  function add_input_checkbox($question, $question_position, $columns, $options, $checked, $must, $output)
  {
    $options=$this->sequence_array($options); //create sequential number array keys, to avoid errors in the order
	
	$def = array(
      "type" => QUESTIONARY_INPUT_CHECKBOX,
      "input_id" => "input_".time(),
      "question" => $question,
      "question_position" => $question_position,
      "columns" => $columns,
      "options" => $options,
      "checked" => $checked,
      "must" => $must,
      "output" => $output
    );

    $this->insert($def);
  }

  //************************************************************
  //method: add_input_radiobutton()
  //		adds a radiobutton question
  //
  //@param string $question	(question text)
  //@param string $question_position (top or left)
  //@param int $columns	(number of columns)
  //@param array  $options (answer possibilities)
  //@param 		  $checked	(prechecked value)
  //@param bool $must	(mandatory)
  //@param bool $output  
  //************************************************************
  function add_input_radiobutton($question, $question_position, $columns, $options, $checked, $must, $output)
  {
    $options=$this->sequence_array($options); //create sequential number array keys, to avoid errors in the order
	
	$def = array(
      "type" => QUESTIONARY_INPUT_RADIO,
      "input_id" => "input_".time(),
      "question" => $question,
      "question_position" => $question_position,
      "options" => $options,
      "columns" => $columns,
      "checked" => $checked,
      "must" => $must,
      "output" => $output
    );

    $this->insert($def);
  }

  //************************************************************
  //method: add_input_selectbox()
  //		adds a selectbox question
  //
  //@param string $question	(question text)
  //@param string $question_position (top or left)
  //@param int $width (width of the selectbox)
  //@param int $rows (number of rows of the selectbox)
  //@param array  $options (answer possibilities)
  //@param 		  $selected	(prechecked value)
  //@param bool $must	(mandatory)
  //@param bool $output  
  //************************************************************
  function add_input_selectbox($question, $question_position, $width, $rows, $options, $selected, $must, $output)
  {
	$options=$this->sequence_array($options); //create sequential number array keys, to avoid errors in the order
	
	$def = array(
      "type" => QUESTIONARY_INPUT_SELECT,
      "input_id" => "input_".time(),
      "question" => $question,
      "question_position" => $question_position,
      "width" => $width,
      "rows" => $rows,
      "options" => $options,
      "selected" => $selected,
      "must" => $must,
      "output" => $output
    );

    $this->insert($def);
  }

  //************************************************************
  //method: add_input_text()
  //		adds a text question
  //
  //@param string $question	(question text)
  //@param string $question_position (top or left)
  //@param int $width (width of the textfield)
  //@param int $maxlength (the maximum number of chars in the textfield)
  //@param string $value (default value of the textfield)
  //@param bool $must	(mandatory)
  //@param bool $output  
  //************************************************************
  function add_input_text($question, $question_position, $width, $maxlength, $value, $must, $output)
  {
    $def = array(
      "type" => QUESTIONARY_INPUT_TEXT,
      "input_id" => "input_".time(),
      "question" => $question,
      "question_position" => $question_position,
      "width" => $width,
      "maxlength" => $maxlength,
      "value" => $value,
      "must" => $must,
      "output" => $output
    );

    $this->insert($def);
   }

  //************************************************************
  //method: add_input_textarea()
  //		adds a textarea question
  //
  //@param string $question	(question text)
  //@param string $question_position (top or left)
  //@param int $width (width of the textarea field)
  //@param int $height (the heightof the textarea field)
  //@param string $value (default value of the textarea field)
  //@param bool $must	(mandatory)
  //@param bool $output  
  //************************************************************
  function add_input_textarea($question, $question_position, $width, $height, $value, $must, $output)
  {
    $def = array(
      "type" => QUESTIONARY_INPUT_TEXTAREA,
      "input_id" => "input_".time(),
      "question" => $question,
      "question_position" => $question_position,
      "width" => $width,
      "height" => $height,
      "value" => $value,
      "must" => $must,
      "output" => $output
    );

    $this->insert($def);
  }

  //************************************************************
  //method: add_new_page()
  //		adds a new Page element
  //
  //************************************************************
  function add_new_page()
  {
    $def = array(
      "type" => QUESTIONARY_NEW_PAGE
    );

    $this->insert($def);
  }

  //************************************************************
  //method: add_input_grading()
  //		adds a grading element with several questions
  //
  //@param string $description	(description text)
  //@param array $grading_options (several questions)
  //@param bool $must	(mandatory)
  //@param bool $output  
  //************************************************************
  function add_input_grading($description, $grading_options, $must, $output)
  {
	$grading_options=$this->sequence_array($grading_options); //create sequential number array keys, to avoid errors in the order
	
	$def = array(
      "type" => QUESTIONARY_INPUT_GRADING,
      "input_id" => "input_".time(),
      "description" => $description,
      "grading_options" => $grading_options,
      "must" => $must,
      "output" => $output
    );

    $this->insert($def);
  }
 
  //***************************************************************
  //method: add_input_tendency()
  //		adds a tendency element with several tendency elements
  //
  //@param string $description	(description text)
  //@param array $tendency_elements (several tendency elements)
  //@param bool $must	(mandatory)
  //@param bool $output  
  //***************************************************************
  function add_input_tendency($description, $tendency_elements, $steps, $must, $output)
  {
    $tendency_elements=$this->sequence_array($tendency_elements); //create sequential number array keys, to avoid errors in the order
	
	$def = array(
      "type" => QUESTIONARY_INPUT_TENDENCY,
      "input_id" => "input_".time(),
      "description" => $description,
      "tendency_elements" => $tendency_elements,
	  "tendency_steps" => $steps,
      "must" => $must,
      "output" => $output
    );

    $this->insert($def, $pre);
  }

  //************************************************************
  //method: insert()
  //		adds a new element to item
  //
  //@param string $text
  //************************************************************
  function insert($def)
  {
	$this->elements[] = $def;
  }

  //************************************************************
  //method: get_type()
  //		search an element an return its type
  //
  //@param string $input_id
  //return = int type of an element
  //************************************************************  
  function get_type($input_id)
  {
  		$all = $this->elements;
		foreach($all as $key => $value)
		{
			if($value["input_id"]==$input_id)	return $value["type"];
		}
		return -1;
  }

  //************************************************************
  //method: get_page()
  //		returns the page
  //
  //@param int $page 
  //return = array with elements of the page $page
  //************************************************************
  function get_page($page = 0)
  {
    $page_count = 0;

    $page_items = array();
    $all = $this->elements;
    foreach($all as $item)
    {
      if($item["type"] == QUESTIONARY_NEW_PAGE)
        $page_count++;
      else if($page_count == $page)
        $page_items[] = $item;
      else if($page_count > $page)
        break;
    }
    return $page_items;
  }


  //************************************************************
  //method: get_count_pages()
  //
  //return = returns the number of pages
  //************************************************************
  function get_count_pages()
  {
    $page_count = 0;

    $all = $this->elements;
    foreach($all as $item)
    {
      if($item["type"] == QUESTIONARY_NEW_PAGE) $page_count++;
    }
    return $page_count+1;
  }


  //************************************************************
  //method: get_page_questionnumber()
  //
  //return = returns the  question number of the current page
  //************************************************************
  function get_page_questionnumber($page = 0)
  {
    $page_count = 0;
    $question_number = 0;

    $all = $this->elements;
    foreach($all as $item)
      if(isset($item["input_id"]))
      {
        $question_number++;
        if($page_count >= $page)
          break;
      }
      else if($item["type"] == QUESTIONARY_NEW_PAGE)
        $page_count++;

    return $question_number;
  }


  //************************************************************
  //method: is_last_page()
  //
  //return = bool 
  //************************************************************
  function is_last_page($page)
  {
    $page_count = 0;

    foreach($this->elements as $key => $item)
    {
      if($key == 0) continue;
      if($item["type"] == QUESTIONARY_NEW_PAGE) $page_count++;
    }

    return ($page >= $page_count);
  }
  
  //************************************************************
  //method: get_last_element()
  //
  //return = last element
  //************************************************************
  function get_last_element()
  {
    $all=$this->elements;
    return end($all);
  }

  //************************************************************
  //method: get_id()
  //
  //@param int $id 
  //return = element with the defined id
  //************************************************************  
  function get_id($id)
  {
    return (isset($this->elements[$id]))?$this->elements[$id]:array();
  }
  
  //************************************************************
  //method: sequence_array()
  //
  //@param array $array 
  //return = an array with sequential keys
  //************************************************************  
  function sequence_array($array)
  {
	$tmp_array=$array;
	$array=array();
	foreach($tmp_array as $key => $value)	$array[]=$value;
	
	return $array;
  }
}
?>