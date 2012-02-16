<?php

  /****************************************************************************
  layout.php - layout a questionary
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
  require_once("../../../config/config.php");
  require_once("$steamapi_doc_root/steam_connector.class.php");
  require_once("../config/config.php");
  require_once("$config_doc_root/includes/sessiondata.php");


  //******************************************************
  //** Presumption
  //******************************************************

  $questionary_id = (isset($_GET["questionary"]))?$_GET["questionary"]:((isset($_POST["questionary"]))?$_POST["questionary"]:"");
  

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
    exit();
  }


  //current room steam object
  if( (int) $questionary_id != 0 ) 
  {
  	$questionary = steam_factory::get_object( $steam, $questionary_id );
  }
  else  
  {
  	$steam->disconnect();
	exit();
  }
  
  
  //get Layout
  $layout = $questionary->get_attribute("bid:questionary:layout");
  
  
  //Disconnect
  $steam->disconnect();

?>
body {
	background-color: <?php echo $layout["background"];?>;
}

.table_header {
	font-size:  <?php echo $layout["question_text_size"];?>px;
	background-color: <?php echo $layout["question_background"];?>;
	color: <?php echo $layout["question_text_color"];?>;
	border: 1px solid <?php echo $layout["question_border_color"];?>;
}

.main_table {
	width: 80%;
}

.table_content {
	font-size:  <?php echo $layout["answer_text_size"];?>px;
	font-weight: normal;
	background-color: <?php echo $layout["answer_background"];?>;
	color:  <?php echo $layout["answer_text_color"];?>;
	border: 1px solid <?php echo $layout["answer_border_color"];?>;
}

.content_inner_table {
	font-size:  <?php echo $layout["answer_text_size"];?>px;
	font-weight: normal;
	background-color: <?php echo $layout["answer_background"];?>;
	color:  <?php echo $layout["answer_text_color"];?>;
	vertical-align:middle;
}

.hr {
	border: 1px solid <?php echo $layout["hr_color"];?>;
}

.elements {
	font-size: <?php echo $layout["answer_text_size"];?>px;
	font-weight: normal;
	color:  <?php echo $layout["answer_text_color"];?>;
}

.caption {
	font-size: <?php echo $layout["caption_text_size"];?>px;
	font-weight: bold;
	vertical-align: middle;
	height: 35px;
	color:  <?php echo $layout["caption_text_color"];?>;
	background-color: <?php echo $layout["caption_background"];?>;
	border: 1px solid <?php echo $layout["caption_border_color"];?>;
}
.button {
	font-size: <?php echo $layout["answer_text_size"];?>px;
	font-weight: bold;
	color:  <?php echo $layout["answer_text_color"];?>;
	background-color: <?php echo $layout["answer_background"];?>;
}
.progress{
	font-size: <?php echo $layout["answer_text_size"];?>px;
	color:  <?php echo $layout["answer_text_color"];?>;
	background-color: <?php echo $layout["background"];?>;
	text-align: right;
	border: 1px solid <?php echo $layout["caption_border_color"];?>;
}
.grading_grades{
	font-size: <?php echo $layout["answer_text_size"];?>px;
	background-color: <?php echo $layout["answer_background"];?>;
	color:  <?php echo $layout["answer_text_color"];?>;
	font-weight: bold;
}
.grading_row_1{
	font-size: <?php echo $layout["answer_text_size"];?>px;
	background-color: <?php echo $layout["question_background"];?>;
	color:  <?php echo $layout["question_text_color"];?>;
}
.grading_row_2{
	font-size: <?php echo $layout["answer_text_size"];?>px;
	background-color: <?php echo $layout["answer_background"];?>;
	color:  <?php echo $layout["answer_text_color"];?>;
}
.tendency_row_1{
	font-size: <?php echo $layout["answer_text_size"];?>px;
	background-color: <?php echo $layout["question_background"];?>;
	color:  <?php echo $layout["question_text_color"];?>;
}
.tendency_row_2{
	font-size: <?php echo $layout["answer_text_size"];?>px;
	background-color: <?php echo $layout["answer_background"];?>;
	color:  <?php echo $layout["answer_text_color"];?>;
}