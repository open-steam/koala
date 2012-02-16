<?php

  /****************************************************************************
  config.php - configuration of bid-owl module - questionary
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


  //******************************************************
  //** New Element Combobox URLs
  //******************************************************
 
  $insert_element_map = array(
    "empty_line"     => "insert_empty_line.php",
    "full_line"      => "insert_full_line.php",
	"caption"		 => "insert_caption.php",
    "new_page"       => "insert_new_page.php",
    "description"    => "insert_description.php",
    "input_text"           => "insert_input_text.php",
    "input_checkbox"       => "insert_input_checkbox.php",
    "input_radiobutton"    => "insert_input_radiobutton.php",
    "input_selectbox"      => "insert_input_selectbox.php",
	"input_grading"        => "insert_input_grading.php",
	"input_tendency"       => "insert_input_tendency.php"
  );
  
  
  //******************************************************
  //** Layout templates of a questionary
  //******************************************************
  
  $templates = array();
  
  //standard template
  $templates[1]["name_ge"]="Standard";
  $templates[1]["name_en"]="Default";
  $templates[1]["template"]="1";
  $templates[1]["background"]= "#efefef";
  $templates[1]["hr_color"]= "#245a86";
  $templates[1]["caption_background"]= "#efefef";
  $templates[1]["caption_border_color"]= "#efefef";
  $templates[1]["caption_text_color"]= "#245a86";
  $templates[1]["caption_text_size"]= "18";
  $templates[1]["question_background"]= "#efefef";
  $templates[1]["question_border_color"]= "#efefef";
  $templates[1]["question_text_color"]= "#000000";
  $templates[1]["question_text_size"]= "12";
  $templates[1]["answer_background"]= "#efefef";
  $templates[1]["answer_border_color"]= "#efefef";
  $templates[1]["answer_text_color"]= "#000000";
  $templates[1]["answer_text_size"]= "12";
  
  //blue template
  $templates[2]["name_ge"]="Blau";
  $templates[2]["name_en"]="Blue";
  $templates[2]["template"]="2";
  $templates[2]["background"]= "#d5def8";
  $templates[2]["hr_color"]= "#003399";
  $templates[2]["caption_background"]= "#d5def8";
  $templates[2]["caption_border_color"]= "#d5def8";
  $templates[2]["caption_text_color"]= "#003399";
  $templates[2]["caption_text_size"]= "18";
  $templates[2]["question_background"]= "#d5def8";
  $templates[2]["question_border_color"]= "#d5def8";
  $templates[2]["question_text_color"]= "#003399";
  $templates[2]["question_text_size"]= "12";
  $templates[2]["answer_background"]= "#d5def8";
  $templates[2]["answer_border_color"]= "#d5def8";
  $templates[2]["answer_text_color"]= "#003399";
  $templates[2]["answer_text_size"]= "12";
 
  //green template
  $templates[3]["name_ge"]="Gr&uuml;n";
  $templates[3]["name_en"]="Green";
  $templates[3]["template"]="3";
  $templates[3]["background"]= "#bbe3cb";
  $templates[3]["hr_color"]= "#003300";
  $templates[3]["caption_background"]= "#bbe3cb";
  $templates[3]["caption_border_color"]= "#bbe3cb";
  $templates[3]["caption_text_color"]= "#003300";
  $templates[3]["caption_text_size"]= "18";
  $templates[3]["question_background"]= "#bbe3cb";
  $templates[3]["question_border_color"]= "#bbe3cb";
  $templates[3]["question_text_color"]= "#000000";
  $templates[3]["question_text_size"]= "12";
  $templates[3]["answer_background"]= "#bbe3cb";
  $templates[3]["answer_border_color"]= "#bbe3cb";
  $templates[3]["answer_text_color"]= "#000000";
  $templates[3]["answer_text_size"]= "12";
  
  //red template
  $templates[4]["name_ge"]="Rot";
  $templates[4]["name_en"]="Red";
  $templates[4]["template"]="4";
  $templates[4]["background"]= "#f5e5e5";
  $templates[4]["hr_color"]= "#990000";
  $templates[4]["caption_background"]= "#f5e5e5";
  $templates[4]["caption_border_color"]= "#f5e5e5";
  $templates[4]["caption_text_color"]= "#333333";
  $templates[4]["caption_text_size"]= "18";
  $templates[4]["question_background"]= "#f5e5e5";
  $templates[4]["question_border_color"]= "#f5e5e5";
  $templates[4]["question_text_color"]= "#000000";
  $templates[4]["question_text_size"]= "12";
  $templates[4]["answer_background"]= "#f5e5e5";
  $templates[4]["answer_border_color"]= "#f5e5e5";
  $templates[4]["answer_text_color"]= "#000000";
  $templates[4]["answer_text_size"]= "12";
  
  //yellow template
  $templates[5]["name_ge"]="Gelb";
  $templates[5]["name_en"]="Yellow";
  $templates[5]["template"]="5";
  $templates[5]["background"]= "#fff9e0";
  $templates[5]["hr_color"]= "#FFCC00";
  $templates[5]["caption_background"]= "#fff9e0";
  $templates[5]["caption_border_color"]= "#fff9e0";
  $templates[5]["caption_text_color"]= "#333333";
  $templates[5]["caption_text_size"]= "18";
  $templates[5]["question_background"]= "#fff9e0";
  $templates[5]["question_border_color"]= "#fff9e0";
  $templates[5]["question_text_color"]= "#000000";
  $templates[5]["question_text_size"]= "12";
  $templates[5]["answer_background"]= "#fff9e0";
  $templates[5]["answer_border_color"]= "#fff9e0";
  $templates[5]["answer_text_color"]= "#000000";
  $templates[5]["answer_text_size"]= "12";
  
  //black and white template
  $templates[6]["name_ge"]="Schwarzwei&szlig;";
  $templates[6]["name_en"]="Black and white";
  $templates[6]["template"]="6";
  $templates[6]["background"]= "#f4f4f4";
  $templates[6]["hr_color"]= "#000000";
  $templates[6]["caption_background"]= "#f4f4f4";
  $templates[6]["caption_border_color"]= "#f4f4f4";
  $templates[6]["caption_text_color"]= "#333333";
  $templates[6]["caption_text_size"]= "18";
  $templates[6]["question_background"]= "#f4f4f4";
  $templates[6]["question_border_color"]= "#f4f4f4";
  $templates[6]["question_text_color"]= "#000000";
  $templates[6]["question_text_size"]= "12";
  $templates[6]["answer_background"]= "#f4f4f4";
  $templates[6]["answer_border_color"]= "#f4f4f4";
  $templates[6]["answer_text_color"]= "#000000";
  $templates[6]["answer_text_size"]= "12";
  
  
?>