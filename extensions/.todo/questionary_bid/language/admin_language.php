<?php

  /****************************************************************************
  admin_language.php - build the language dependent templates of the portal module
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

  Author: Henrik Beige
  EMail: hebeige@gmx.de

  ****************************************************************************/
//  require_once("../../../config/config.php");
//  require_once("$config_doc_root/classes/template.inc");
//  require_once("$config_doc_root/classes/debugHelper.php");

  $tmp_doc_root = "$config_doc_root/modules/questionary";


  //get list of all language directories
  $languages = array();
  $handle = opendir ('.');
  while (false !== ($file = readdir ($handle)))
  {
    if(is_dir($file) && $file != "." && $file != ".." && $file != "CVS" && $file != ".svn")
      array_push($languages, $file);
  }
  closedir($handle);


  echo("<br><br><hr><b>MODULE: Questionary</b><hr>");


  //Parse all templates
  foreach($languages as $language)
  {
    //check whether language dir existents
    @mkdir("$tmp_doc_root/templates/$language");


    //get language file
    $tpl = new Template("$tmp_doc_root/language/$language", "keep");
    $tpl->set_file("language", "language.ihtml");
	$tpl->set_file("language2", "language2.ihtml");

    //get blueprint for dialog
    $tpl->set_root("$tmp_doc_root/language");
    $tpl->set_file("blueprint", "dialog_blueprint.ihtml");
    $tpl->set_var("DOC_ROOT", $config_webserver_ip);

    //set template root dir back to the general design
    $tpl->set_root("$tmp_doc_root/language");

    echo("<br><br>Sprache: $language<br>");


    //*******************************************************************
    //* edit.ihtml
    //*******************************************************************

    $current_file = "edit";

    $tpl->set_file($current_file, "$current_file.ihtml");
    $tpl->set_block("language", "edit_title");
    $tpl->set_block("language", "edit_script_confirm_1");
    $tpl->set_block("language", "edit_script_confirm_2");
    $tpl->set_block("language", "edit_topic_1");
    $tpl->set_block("language", "edit_topic_2");
    $tpl->set_block("language", "edit_topic_3");
    $tpl->set_block("language", "edit_topic_4");
    $tpl->set_block("language", "edit_topic_5");
    $tpl->set_block("language", "edit_topic_6");
    $tpl->set_block("language", "edit_no_question");
    $tpl->set_block("language", "edit_alt_edit");
    $tpl->set_block("language", "edit_alt_delete");
    $tpl->set_block("language", "edit_option_label");
    $tpl->set_block("language", "edit_option_1");
    $tpl->set_block("language", "edit_option_2");
    $tpl->set_block("language", "edit_option_3");
    $tpl->set_block("language", "edit_option_4");
    $tpl->set_block("language", "edit_option_5");
    $tpl->set_block("language", "edit_option_6");
    $tpl->set_block("language", "edit_option_7");
    $tpl->set_block("language", "edit_option_8");
    $tpl->set_block("language", "edit_option_9");
    $tpl->set_block("language", "edit_option_10");
    $tpl->set_block("language", "edit_option_11");
	$tpl->set_block("language", "edit_option_12");
	$tpl->set_block("language", "edit_option_13");
	$tpl->set_block("language", "edit_label_option_layout");
	$tpl->set_block("language", "edit_label_option_question");
    $tpl->set_block("language", "edit_button_label_general");
    $tpl->set_block("language", "edit_button_label_insert");
    $tpl->set_block("language", "edit_button_label_sort");
	$tpl->set_block("language", "edit_button_label_preview");
	$tpl->set_block("language", "edit_button_label_layout");
    $tpl->set_block("language", "edit_button_cancel_label");
	$tpl->set_block("language", "edit_enabling_questionary");
	$tpl->set_block("language", "edit_enable");
	$tpl->set_block("language", "edit_disable");
	$tpl->set_block("language", "edit_help_nothing");
	$tpl->set_block("language", "edit_help_example");
	$tpl->set_block("language", "edit_help_empty_line_top");
	$tpl->set_block("language", "edit_help_empty_line_li");
	$tpl->set_block("language", "edit_help_full_line_top");
	$tpl->set_block("language", "edit_help_full_line_li");
	$tpl->set_block("language", "edit_help_full_line_li2");
	$tpl->set_block("language", "edit_help_new_page_top");
	$tpl->set_block("language", "edit_help_new_page_li");
	$tpl->set_block("language", "edit_help_caption_top");
	$tpl->set_block("language", "edit_help_caption_li");
	$tpl->set_block("language", "edit_help_caption_li2");
	$tpl->set_block("language", "edit_help_description_top");
	$tpl->set_block("language", "edit_help_description_li");
	$tpl->set_block("language", "edit_help_text_top");
	$tpl->set_block("language", "edit_help_text_li");
	$tpl->set_block("language", "edit_help_text_li2");
	$tpl->set_block("language", "edit_help_checkbox_top");
	$tpl->set_block("language", "edit_help_checkbox_li");
	$tpl->set_block("language", "edit_help_checkbox_li2");
	$tpl->set_block("language", "edit_help_checkbox_li3");
	$tpl->set_block("language", "edit_help_radiobutton_top");
	$tpl->set_block("language", "edit_help_radiobutton_li");
	$tpl->set_block("language", "edit_help_radiobutton_li2");
	$tpl->set_block("language", "edit_help_radiobutton_li3");
	$tpl->set_block("language", "edit_help_selectbox_top");
	$tpl->set_block("language", "edit_help_selectbox_li");
	$tpl->set_block("language", "edit_help_selectbox_li2");
	$tpl->set_block("language", "edit_help_selectbox_li3");
	$tpl->set_block("language", "edit_help_grading_top");
	$tpl->set_block("language", "edit_help_grading_li");
	$tpl->set_block("language", "edit_help_grading_li2");
	$tpl->set_block("language", "edit_help_tendency_top");
	$tpl->set_block("language", "edit_help_tendency_li");
	$tpl->set_block("language", "edit_help_tendency_li2");
	$tpl->set_block("language", "edit_help_tendency_li3");
	
    $tpl->parse("TITLE", "edit_title");
    $tpl->parse("LANGUAGE_SCRIPT_CONFIRM_1", "edit_script_confirm_1");
    $tpl->parse("LANGUAGE_SCRIPT_CONFIRM_2", "edit_script_confirm_2");
    $tpl->parse("LANGUAGE_TOPIC_1", "edit_topic_1");
    $tpl->parse("LANGUAGE_TOPIC_2", "edit_topic_2");
    $tpl->parse("LANGUAGE_TOPIC_4", "edit_topic_4");
    $tpl->parse("LANGUAGE_TOPIC_5", "edit_topic_5");
    $tpl->parse("LANGUAGE_TOPIC_6", "edit_topic_6");
    $tpl->parse("LANGUAGE_NO_QUESTION", "edit_no_question");
    $tpl->parse("LANGUAGE_ALT_EDIT", "edit_alt_edit");
    $tpl->parse("LANGUAGE_ALT_DELETE", "edit_alt_delete");
    $tpl->parse("LANGUAGE_OPTION_LABEL", "edit_option_label");
    $tpl->parse("LANGUAGE_OPTION_1", "edit_option_1");
    $tpl->parse("LANGUAGE_OPTION_2", "edit_option_2");
    $tpl->parse("LANGUAGE_OPTION_3", "edit_option_3");
    $tpl->parse("LANGUAGE_OPTION_4", "edit_option_4");
    $tpl->parse("LANGUAGE_OPTION_5", "edit_option_5");
    $tpl->parse("LANGUAGE_OPTION_6", "edit_option_6");
    $tpl->parse("LANGUAGE_OPTION_7", "edit_option_7");
    $tpl->parse("LANGUAGE_OPTION_8", "edit_option_8");
    $tpl->parse("LANGUAGE_OPTION_9", "edit_option_9");
    $tpl->parse("LANGUAGE_OPTION_10", "edit_option_10");
    $tpl->parse("LANGUAGE_OPTION_11", "edit_option_11");
	$tpl->parse("LANGUAGE_OPTION_12", "edit_option_12");
	$tpl->parse("LANGUAGE_OPTION_13", "edit_option_13");
	$tpl->parse("LANGUAGE_LABEL_OPTION_LAYOUT", "edit_label_option_layout");
	$tpl->parse("LANGUAGE_LABEL_OPTION_QUESTIONS", "edit_label_option_question");
    $tpl->parse("LANGUAGE_BUTTON_LABEL_GENERAL", "edit_button_label_general");
    $tpl->parse("LANGUAGE_BUTTON_LABEL_INSERT", "edit_button_label_insert");
    $tpl->parse("LANGUAGE_BUTTON_LABEL_SORT", "edit_button_label_sort");
	$tpl->parse("LANGUAGE_BUTTON_LABEL_PREVIEW", "edit_button_label_preview");
	$tpl->parse("LANGUAGE_BUTTON_LABEL_LAYOUT", "edit_button_label_layout");
    $tpl->parse("LANGUAGE_BUTTON_CANCEL", "edit_button_cancel_label");
	$tpl->parse("LANGUAGE_ENABLING_QUESTIONARY", "edit_enabling_questionary");
	$tpl->parse("LANGUAGE_ENABLE", "edit_enable");
	$tpl->parse("LANGUAGE_DISABLE", "edit_disable");
	$tpl->parse("LANGUAGE_HELP_NOTHING", "edit_help_nothing");
	$tpl->parse("LANGUAGE_HELP_EXAMPLE", "edit_help_example");	
	$tpl->parse("LANGUAGE_HELP_EMPTY_LINE_TOP", "edit_help_empty_line_top");
	$tpl->parse("LANGUAGE_HELP_EMPTY_LINE_LI", "edit_help_empty_line_li");
	$tpl->parse("LANGUAGE_HELP_FULL_LINE_TOP", "edit_help_full_line_top");
	$tpl->parse("LANGUAGE_HELP_FULL_LINE_LI", "edit_help_full_line_li");
	$tpl->parse("LANGUAGE_HELP_FULL_LINE_LI2", "edit_help_full_line_li2");
	$tpl->parse("LANGUAGE_HELP_NEW_PAGE_TOP", "edit_help_new_page_top");
	$tpl->parse("LANGUAGE_HELP_NEW_PAGE_LI", "edit_help_new_page_li");
	$tpl->parse("LANGUAGE_HELP_CAPTION_TOP", "edit_help_caption_top");
	$tpl->parse("LANGUAGE_HELP_CAPTION_LI", "edit_help_caption_li");
	$tpl->parse("LANGUAGE_HELP_CAPTION_LI2", "edit_help_caption_li2");
	$tpl->parse("LANGUAGE_HELP_DESCRIPTION_TOP", "edit_help_description_top");
	$tpl->parse("LANGUAGE_HELP_DESCRIPTION_LI", "edit_help_description_li");
	$tpl->parse("LANGUAGE_HELP_TEXT_TOP", "edit_help_text_top");
	$tpl->parse("LANGUAGE_HELP_TEXT_LI", "edit_help_text_li");
	$tpl->parse("LANGUAGE_HELP_TEXT_LI2", "edit_help_text_li2");
	$tpl->parse("LANGUAGE_HELP_CHECKBOX_TOP", "edit_help_checkbox_top");
	$tpl->parse("LANGUAGE_HELP_CHECKBOX_LI", "edit_help_checkbox_li");
	$tpl->parse("LANGUAGE_HELP_CHECKBOX_LI2", "edit_help_checkbox_li2");
	$tpl->parse("LANGUAGE_HELP_CHECKBOX_LI3", "edit_help_checkbox_li3");
	$tpl->parse("LANGUAGE_HELP_RADIOBUTTON_TOP", "edit_help_radiobutton_top");
	$tpl->parse("LANGUAGE_HELP_RADIOBUTTON_LI", "edit_help_radiobutton_li");
	$tpl->parse("LANGUAGE_HELP_RADIOBUTTON_LI2", "edit_help_radiobutton_li2");
	$tpl->parse("LANGUAGE_HELP_RADIOBUTTON_LI3", "edit_help_radiobutton_li3");
	$tpl->parse("LANGUAGE_HELP_SELECTBOX_TOP", "edit_help_selectbox_top");
	$tpl->parse("LANGUAGE_HELP_SELECTBOX_LI", "edit_help_selectbox_li");
	$tpl->parse("LANGUAGE_HELP_SELECTBOX_LI2", "edit_help_selectbox_li2");
	$tpl->parse("LANGUAGE_HELP_SELECTBOX_LI3", "edit_help_selectbox_li3");
	$tpl->parse("LANGUAGE_HELP_GRADING_TOP", "edit_help_grading_top");
	$tpl->parse("LANGUAGE_HELP_GRADING_LI", "edit_help_grading_li");
	$tpl->parse("LANGUAGE_HELP_GRADING_LI2", "edit_help_grading_li2");
	$tpl->parse("LANGUAGE_HELP_TENDENCY_TOP", "edit_help_tendency_top");
	$tpl->parse("LANGUAGE_HELP_TENDENCY_LI", "edit_help_tendency_li");
	$tpl->parse("LANGUAGE_HELP_TENDENCY_LI2", "edit_help_tendency_li2");
	$tpl->parse("LANGUAGE_HELP_TENDENCY_LI3", "edit_help_tendency_li3");


    $tpl->parse("CONTENT", $current_file);
    $tpl->parse("OUT", "blueprint");
    $out = $tpl->get_var("OUT");

    $tpl->unset_var("BUTTON_LABEL");

    $fp = fopen("$tmp_doc_root/templates/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);

    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $tmp_doc_root/templates/$language/$current_file.ihtml)<br>");


    //*******************************************************************
    //* edit_general.ihtml
    //*******************************************************************

    $current_file = "edit_general";

    $tpl->set_file($current_file, "$current_file.ihtml");
    $tpl->set_block("language", "edit_general_title");
    $tpl->set_block("language", "edit_general_label_number");
    $tpl->set_block("language", "edit_general_label_fill_out");
    $tpl->set_block("language", "edit_general_label_fill_out_1");
    $tpl->set_block("language", "edit_general_label_fill_out_n");
    $tpl->set_block("language", "edit_general_label_edit_answer");
    $tpl->set_block("language", "edit_general_label_edit_answer_true");
    $tpl->set_block("language", "edit_general_label_edit_answer_false");
	$tpl->set_block("language", "edit_general_label_edit_own_answer");
	$tpl->set_block("language", "edit_general_label_edit_own_answer_true");
    $tpl->set_block("language", "edit_general_label_edit_own_answer_false");
    $tpl->set_block("language", "edit_general_label_result_creator");
    $tpl->set_block("language", "edit_general_label_result_creator_true");
    $tpl->set_block("language", "edit_general_label_result_creator_false");
    $tpl->set_block("language", "edit_general_label_result_creation_time");
    $tpl->set_block("language", "edit_general_label_result_creation_time_true");
    $tpl->set_block("language", "edit_general_label_result_creation_time_false");
    $tpl->set_block("language", "edit_general_label_save");
    $tpl->set_block("language", "edit_general_label_properties");
    $tpl->set_block("language", "edit_general_button_cancel");
	$tpl->set_block("language", "edit_general_button_rights");
	$tpl->set_block("language", "edit_general_label_rights");
	$tpl->set_block("language", "edit_general_questionary_settings");
	$tpl->set_block("language", "edit_general_result_settings");
	$tpl->set_block("language", "edit_general_label_description");
	$tpl->set_block("language", "edit_general_label_edit_time");
	$tpl->set_block("language", "edit_general_from");
	$tpl->set_block("language", "edit_general_to");
	$tpl->set_block("language", "edit_general_januaray");
	$tpl->set_block("language", "edit_general_february");
	$tpl->set_block("language", "edit_general_march");
	$tpl->set_block("language", "edit_general_april");
	$tpl->set_block("language", "edit_general_may");
	$tpl->set_block("language", "edit_general_june");
	$tpl->set_block("language", "edit_general_july");
	$tpl->set_block("language", "edit_general_august");
	$tpl->set_block("language", "edit_general_september");
	$tpl->set_block("language", "edit_general_october");
	$tpl->set_block("language", "edit_general_november");
	$tpl->set_block("language", "edit_general_december");
	$tpl->set_block("language", "edit_general_first_no_date");
	$tpl->set_block("language", "edit_general_sec_no_date");
	$tpl->set_block("language", "edit_general_sec_date_is_smaller");
	$tpl->set_block("language", "edit_general_first_date_is_past");
	
    $tpl->parse("TITLE", "edit_general_title");
    $tpl->parse("LANGUAGE_LABEL_NUMBER", "edit_general_label_number");
    $tpl->parse("LANGUAGE_LABEL_FILL_OUT", "edit_general_label_fill_out");
    $tpl->parse("LANGUAGE_LABEL_FILL_OUT_1", "edit_general_label_fill_out_1");
    $tpl->parse("LANGUAGE_LABEL_FILL_OUT_N", "edit_general_label_fill_out_n");
    $tpl->parse("LANGUAGE_LABEL_EDIT_ANSWER", "edit_general_label_edit_answer");
    $tpl->parse("LANGUAGE_LABEL_EDIT_ANSWER_TRUE", "edit_general_label_edit_answer_true");
    $tpl->parse("LANGUAGE_LABEL_EDIT_ANSWER_FALSE", "edit_general_label_edit_answer_false");
	$tpl->parse("LANGUAGE_LABEL_EDIT_OWN_ANSWER", "edit_general_label_edit_own_answer");
	$tpl->parse("LANGUAGE_LABEL_EDIT_OWN_ANSWER_TRUE", "edit_general_label_edit_own_answer_true");
    $tpl->parse("LANGUAGE_LABEL_EDIT_OWN_ANSWER_FALSE", "edit_general_label_edit_own_answer_false");
    $tpl->parse("LANGUAGE_LABEL_RESULT_CREATOR", "edit_general_label_result_creator");
    $tpl->parse("LANGUAGE_LABEL_RESULT_CREATOR_TRUE", "edit_general_label_result_creator_true");
    $tpl->parse("LANGUAGE_LABEL_RESULT_CREATOR_FALSE", "edit_general_label_result_creator_false");
    $tpl->parse("LANGUAGE_LABEL_RESULT_CREATION_TIME", "edit_general_label_result_creation_time");
    $tpl->parse("LANGUAGE_LABEL_RESULT_CREATION_TIME_TRUE", "edit_general_label_result_creation_time_true");
    $tpl->parse("LANGUAGE_LABEL_RESULT_CREATION_TIME_FALSE", "edit_general_label_result_creation_time_false");
    $tpl->parse("LANGUAGE_LABEL_SAVE", "edit_general_label_save");
    $tpl->parse("LANGUAGE_LABEL_PROPERTIES", "edit_general_label_properties");
    $tpl->parse("LANGUAGE_BUTTON_CANCEL", "edit_general_button_cancel");
	$tpl->parse("LANGUAGE_BUTTON_RIGHTS", "edit_general_button_rights");
	$tpl->parse("LANGUAGE_LABEL_RIGHTS", "edit_general_label_rights");
	$tpl->parse("LANGUAGE_QUESTIONARY_SETTINGS", "edit_general_questionary_settings");
	$tpl->parse("LANGUAGE_RESULT_SETTINGS", "edit_general_result_settings");
	$tpl->parse("LANGUAGE_LABEL_DESCRIPTION", "edit_general_label_description");
	$tpl->parse("LANGUAGE_LABEL_EDIT_TIME", "edit_general_label_edit_time");
	$tpl->parse("LANGUAGE_FROM", "edit_general_from");
	$tpl->parse("LANGUAGE_TO", "edit_general_to");
	$tpl->parse("LANGUAGE_JANUARY", "edit_general_januaray");
	$tpl->parse("LANGUAGE_FEBRUARY", "edit_general_february");
	$tpl->parse("LANGUAGE_MARCH", "edit_general_march");
	$tpl->parse("LANGUAGE_APRIL", "edit_general_april");
	$tpl->parse("LANGUAGE_MAY", "edit_general_may");
	$tpl->parse("LANGUAGE_JUNE", "edit_general_june");
	$tpl->parse("LANGUAGE_JULY", "edit_general_july");
	$tpl->parse("LANGUAGE_AUGUST", "edit_general_august");
	$tpl->parse("LANGUAGE_SEPTEMBER", "edit_general_september");
	$tpl->parse("LANGUAGE_OCTOBER", "edit_general_october");
	$tpl->parse("LANGUAGE_NOVEMBER", "edit_general_november");
	$tpl->parse("LANGUAGE_DECEMBER", "edit_general_december");
	$tpl->parse("LANGUAGE_FIRST_NO_DATE", "edit_general_first_no_date");
	$tpl->parse("LANGUAGE_SEC_NO_DATE", "edit_general_sec_no_date");
	$tpl->parse("LANGUAGE_SEC_DATE_IS_SMALLER", "edit_general_sec_date_is_smaller");
	$tpl->parse("LANGUAGE_FIRST_DATE_IS_PAST", "edit_general_first_date_is_past");
	

    $tpl->parse("CONTENT", $current_file);
    $tpl->parse("OUT", "blueprint");
    $out = $tpl->get_var("OUT");

    $tpl->unset_var("BUTTON_LABEL");

    $fp = fopen("$tmp_doc_root/templates/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);

    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $tmp_doc_root/templates/$language/$current_file.ihtml)<br>");


    //*******************************************************************
    //* edit_layout.ihtml
    //*******************************************************************

    $current_file = "edit_layout";

    $tpl->set_file($current_file, "$current_file.ihtml");
    $tpl->set_block("language", "edit_layout_title");
	$tpl->set_block("language", "edit_layout_general");
	$tpl->set_block("language", "edit_layout_error_text_size");
	$tpl->set_block("language", "edit_layout_error_color_format");
	$tpl->set_block("language", "edit_layout_error_empty_field");
	$tpl->set_block("language", "edit_layout_data_saved");
	$tpl->set_block("language", "edit_layout_background");
	$tpl->set_block("language", "edit_layout_hr_color");
	$tpl->set_block("language", "edit_layout_question");
	$tpl->set_block("language", "edit_layout_question_background");
	$tpl->set_block("language", "edit_layout_question_border_color");
	$tpl->set_block("language", "edit_layout_question_text_color");
	$tpl->set_block("language", "edit_layout_question_text_size");
	$tpl->set_block("language", "edit_layout_caption");
	$tpl->set_block("language", "edit_layout_caption_background");
	$tpl->set_block("language", "edit_layout_caption_border_color");
	$tpl->set_block("language", "edit_layout_caption_text_color");
	$tpl->set_block("language", "edit_layout_caption_text_size");
	$tpl->set_block("language", "edit_layout_answer");
	$tpl->set_block("language", "edit_layout_answer_background");
	$tpl->set_block("language", "edit_layout_answer_border_color");
	$tpl->set_block("language", "edit_layout_answer_text_color");
	$tpl->set_block("language", "edit_layout_answer_text_size");
	$tpl->set_block("language", "edit_layout_button_save");
	$tpl->set_block("language", "edit_layout_preview");
	$tpl->set_block("language", "edit_layout_color_palette");
	$tpl->set_block("language", "edit_layout_button_cancel");
	$tpl->set_block("language", "edit_layout_template");
	$tpl->set_block("language", "edit_layout_option_user_defined");

    $tpl->parse("TITLE", "edit_layout_title");
	$tpl->parse("LANGUAGE_ERROR_TEXT_SIZE", "edit_layout_error_text_size");
	$tpl->parse("LANGUAGE_ERROR_COLOR_FORMAT", "edit_layout_error_color_format");
	$tpl->parse("LANGUAGE_ERROR_EMPTY_FIELD", "edit_layout_error_empty_field");
	$tpl->parse("LANGUAGE_DATA_SAVED", "edit_layout_data_saved");
	$tpl->parse("LANGUAGE_LABEL_GENERAL", "edit_layout_general");
	$tpl->parse("LANGUAGE_LABEL_BACKGROUND", "edit_layout_background");
	$tpl->parse("LANGUAGE_LABEL_HR_COLOR", "edit_layout_hr_color");
	$tpl->parse("LANGUAGE_LABEL_QUESTION", "edit_layout_question");
	$tpl->parse("LANGUAGE_LABEL_QUESTION_BACKGROUND", "edit_layout_question_background");
	$tpl->parse("LANGUAGE_LABEL_QUESTION_BORDER_COLOR", "edit_layout_question_border_color");
	$tpl->parse("LANGUAGE_LABEL_QUESTION_TEXT_COLOR", "edit_layout_question_text_color");
	$tpl->parse("LANGUAGE_LABEL_QUESTION_TEXT_SIZE", "edit_layout_question_text_size");
	$tpl->parse("LANGUAGE_LABEL_CAPTION", "edit_layout_caption");
	$tpl->parse("LANGUAGE_LABEL_CAPTION_BACKGROUND", "edit_layout_caption_background");
	$tpl->parse("LANGUAGE_LABEL_CAPTION_BORDER_COLOR", "edit_layout_caption_border_color");
	$tpl->parse("LANGUAGE_LABEL_CAPTION_TEXT_COLOR", "edit_layout_caption_text_color");
	$tpl->parse("LANGUAGE_LABEL_CAPTION_TEXT_SIZE", "edit_layout_caption_text_size");
	$tpl->parse("LANGUAGE_LABEL_ANSWER", "edit_layout_answer");
	$tpl->parse("LANGUAGE_LABEL_ANSWER_BACKGROUND", "edit_layout_answer_background");
	$tpl->parse("LANGUAGE_LABEL_ANSWER_BORDER_COLOR", "edit_layout_answer_border_color");
	$tpl->parse("LANGUAGE_LABEL_ANSWER_TEXT_COLOR", "edit_layout_answer_text_color");
	$tpl->parse("LANGUAGE_LABEL_ANSWER_TEXT_SIZE", "edit_layout_answer_text_size");
	$tpl->parse("LANGUAGE_BUTTON_SAVE", "edit_layout_button_save");
	$tpl->parse("LANGUAGE_BUTTON_LABEL_PREVIEW", "edit_layout_preview");
	$tpl->parse("LANGUAGE_BUTTON_COLOR_PALETTE", "edit_layout_color_palette");
	$tpl->parse("LANGUAGE_BUTTON_CANCEL", "edit_layout_button_cancel");
	$tpl->parse("LANGUAGE_LABEL_TEMPLATE", "edit_layout_template");
	$tpl->parse("LANGUAGE_OPTION_USER_DEFINED", "edit_layout_option_user_defined");
	$tpl->parse("LANGUAGE_OPTION_STANDARD", "edit_layout_option_standard");
	
    $tpl->parse("CONTENT", $current_file);
    $tpl->parse("OUT", "blueprint");
    $out = $tpl->get_var("OUT");

    $tpl->unset_var("BUTTON_LABEL");

    $fp = fopen("$tmp_doc_root/templates/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);

    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $tmp_doc_root/templates/$language/$current_file.ihtml)<br>");


    //*******************************************************************
    //* color_palette.ihtml
    //*******************************************************************
	
	$current_file = "color_palette";

    $tpl->set_file($current_file, "$current_file.ihtml");
    $tpl->set_block("language", "color_title");
	$tpl->set_block("language", "color_preview");
	$tpl->set_block("language", "color_selected");
	$tpl->set_block("language", "color_button_ok");
	$tpl->set_block("language", "color_button_cancel");
	
	$tpl->parse("TITLE", "color_title");
	$tpl->parse("LANGUAGE_PREVIEW", "color_preview");
	$tpl->parse("LANGUAGE_SELECTED", "color_selected");
	$tpl->parse("LANGUAGE_BUTTON_OK", "color_button_ok");
	$tpl->parse("LANGUAGE_BUTTON_CANCEL", "color_button_cancel");
	
	$tpl->parse("OUT", $current_file);
    $out = $tpl->get_var("OUT");

    $fp = fopen("$tmp_doc_root/templates/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);

    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $tmp_doc_root/templates/$language/$current_file.ihtml)<br>");
	
	
	//*******************************************************************
    //* edit_sort.ihtml
    //*******************************************************************

    $current_file = "edit_sort";

    $tpl->set_file($current_file, "$current_file.ihtml");
    $tpl->set_block("language", "edit_sort_title");
    $tpl->set_block("language", "edit_sort_empty_line");
    $tpl->set_block("language", "edit_sort_full_line");
    $tpl->set_block("language", "edit_sort_new_page");
    $tpl->set_block("language", "edit_sort_button_label_save");
    $tpl->set_block("language", "edit_sort_button_label_cancel");

    $tpl->parse("TITLE", "edit_sort_title");
    $tpl->parse("LANGUAGE_EMPTY_LINE", "edit_sort_empty_line");
    $tpl->parse("LANGUAGE_FULL_LINE", "edit_sort_full_line");
    $tpl->parse("LANGUAGE_NEW_PAGE", "edit_sort_new_page");
    $tpl->parse("LANGUAGE_BUTTON_LABEL_SAVE", "edit_sort_button_label_save");
    $tpl->parse("LANGUAGE_BUTTON_CANCEL", "edit_sort_button_label_cancel");

    $tpl->parse("CONTENT", $current_file);
    $tpl->parse("OUT", "blueprint");
    $out = $tpl->get_var("OUT");

    $tpl->unset_var("BUTTON_LABEL");

    $fp = fopen("$tmp_doc_root/templates/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);

    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $tmp_doc_root/templates/$language/$current_file.ihtml)<br>");


 	//*******************************************************************
    //* edit_preview.ihtml
    //*******************************************************************

    $current_file = "edit_preview";

    $tpl->set_file($current_file, "$current_file.ihtml");
    $tpl->set_block("language", "edit_preview_title");
    $tpl->set_block("language", "edit_preview_button_previous");
    $tpl->set_block("language", "edit_preview_button_next");
	$tpl->set_block("language", "edit_preview_button_refresh");
	$tpl->set_block("language", "edit_preview_button_cancel");
	$tpl->set_block("language", "edit_preview_grading_label_one");
	$tpl->set_block("language", "edit_preview_grading_label_two");
	$tpl->set_block("language", "edit_preview_grading_label_three");
	$tpl->set_block("language", "edit_preview_grading_label_four");
	$tpl->set_block("language", "edit_preview_grading_label_five");
	$tpl->set_block("language", "edit_preview_grading_label_six");

    $tpl->parse("TITLE", "edit_preview_title");
    $tpl->parse("LANGUAGE_LABEL_BUTTON_PREVIOUS", "edit_preview_button_previous");
    $tpl->parse("LANGUAGE_LABEL_BUTTON_NEXT", "edit_preview_button_next");
	$tpl->parse("LANGUAGE_LABEL_BUTTON_REFRESH", "edit_preview_button_refresh");
	$tpl->parse("LANGUAGE_BUTTON_CANCEL", "edit_preview_button_cancel");
	$tpl->parse("LANGUAGE_GRADING_LABEL_ONE", "edit_preview_grading_label_one");
	$tpl->parse("LANGUAGE_GRADING_LABEL_TWO", "edit_preview_grading_label_two");
	$tpl->parse("LANGUAGE_GRADING_LABEL_THREE", "edit_preview_grading_label_three");
	$tpl->parse("LANGUAGE_GRADING_LABEL_FOUR", "edit_preview_grading_label_four");
	$tpl->parse("LANGUAGE_GRADING_LABEL_FIVE", "edit_preview_grading_label_five");
	$tpl->parse("LANGUAGE_GRADING_LABEL_SIX", "edit_preview_grading_label_six");

    $tpl->parse("OUT", $current_file);
    $out = $tpl->get_var("OUT");

    $fp = fopen("$tmp_doc_root/templates/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);

    fclose($fp);


    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $tmp_doc_root/templates/$language/$current_file.ihtml)<br>");
	

    //*******************************************************************
    //* index.ihtml
    //*******************************************************************

    $current_file = "index";

    $tpl->set_file($current_file, "$current_file.ihtml");
    $tpl->set_block("language", "index_new_answer");
	$tpl->set_block("language", "index_edit_answer");
	$tpl->set_block("language", "index_view_result");
	$tpl->set_block("language", "index_edit_questionary");
	$tpl->set_block("language", "index_title");
	$tpl->set_block("language", "index_description");
	$tpl->set_block("language", "index_what_do");
	$tpl->set_block("language", "index_creation_time");
	$tpl->set_block("language", "index_action");
	$tpl->set_block("language", "index_edit");
	$tpl->set_block("language", "index_delete");
	$tpl->set_block("language", "index_no_answers");
	$tpl->set_block("language", "index_script_confirm");
	$tpl->set_block("language", "index_no_edit_time");
	$tpl->set_block("language", "index_no_rights");
	$tpl->set_block("language", "index_send_already_answer");
	$tpl->set_block("language", "index_only_for_creators");
	$tpl->set_block("language", "index_enable_questionary");
	$tpl->set_block("language", "index_disable_questionary");
	$tpl->set_block("language", "index_start_date");
	$tpl->set_block("language", "index_end_date");
   
    $tpl->parse("LANGUAGE_NEW_ANSWER", "index_new_answer");
	$tpl->parse("LANGUAGE_EDIT_ANSWER", "index_edit_answer");
	$tpl->parse("LANGUAGE_VIEW_RESULT", "index_view_result");
	$tpl->parse("LANGUAGE_EDIT_QUESTIONARY", "index_edit_questionary");
	$tpl->parse("TITLE", "index_title");
	$tpl->parse("LANGUAGE_DESCRIPTION", "index_description");
	$tpl->parse("LANGUAGE_WHAT_DO", "index_what_do");
	$tpl->parse("LANGUAGE_CREATION_TIME", "index_creation_time");
	$tpl->parse("LANGUAGE_ACTION", "index_action");
	$tpl->parse("LANGUAGE_EDIT", "index_edit");
    $tpl->parse("LANGUAGE_DELETE", "index_delete");
	$tpl->parse("LANGUAGE_NO_ANSWERS", "index_no_answers");
	$tpl->parse("LANGUAGE_SCRIPT_CONFIRM", "index_script_confirm");
	$tpl->parse("LANGUAGE_NO_EDIT_TIME", "index_no_edit_time");
	$tpl->parse("LANGUAGE_NO_RIGHTS", "index_no_rights");
	$tpl->parse("LANGUAGE_SEND_ALREADY_ANSWER", "index_send_already_answer");
	$tpl->parse("LANGUAGE_ONLY_FOR_CREATORS", "index_only_for_creators");
	$tpl->parse("LANGUAGE_ENABLE_QUESTIONARY", "index_enable_questionary");
	$tpl->parse("LANGUAGE_DISABLE_QUESTIONARY", "index_disable_questionary");
	$tpl->parse("LANGUAGE_START_DATE", "index_start_date");
	$tpl->parse("LANGUAGE_END_DATE", "index_end_date");

	
	
    $tpl->parse("OUT", $current_file);
    $out = $tpl->get_var("OUT");

    $fp = fopen("$tmp_doc_root/templates/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);

    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $tmp_doc_root/templates/$language/$current_file.ihtml)<br>");
	
	
	//*******************************************************************
    //* answer.ihtml
    //*******************************************************************

    $current_file = "answer";

    $tpl->set_file($current_file, "$current_file.ihtml");
    $tpl->set_block("language", "answer_title");
    $tpl->set_block("language", "answer_label_error_1");
    $tpl->set_block("language", "answer_label_error_2");
    $tpl->set_block("language", "answer_label_error_no_write_access");
    $tpl->set_block("language", "answer_label_error_multiple_input");
	$tpl->set_block("language", "answer_label_error_not_enabled");
    $tpl->set_block("language", "answer_saved");
    $tpl->set_block("language", "answer_saved_result");
    $tpl->set_block("language", "answer_label_button_previous");
    $tpl->set_block("language", "answer_label_button_next");
    $tpl->set_block("language", "answer_label_button_finish");
	$tpl->set_block("language", "answer_label_button_home");
	$tpl->set_block("language", "answer_grading_label_one");
	$tpl->set_block("language", "answer_grading_label_two");
	$tpl->set_block("language", "answer_grading_label_three");
	$tpl->set_block("language", "answer_grading_label_four");
	$tpl->set_block("language", "answer_grading_label_five");
	$tpl->set_block("language", "answer_grading_label_six");
	
    $tpl->parse("TITLE", "answer_title");
    $tpl->parse("LANGUAGE_ERROR_1", "answer_label_error_1");
    $tpl->parse("LANGUAGE_ERROR_2", "answer_label_error_2");
    $tpl->parse("LANGUAGE_ERROR_NO_WRITE_ACCESS", "answer_label_error_no_write_access");
    $tpl->parse("LANGUAGE_ERROR_MULTIPLE_INPUT", "answer_label_error_multiple_input");
	$tpl->parse("LANGUAGE_ERROR_NOT_ENABLED", "answer_label_error_not_enabled");
    $tpl->parse("LANGUAGE_SAVED", "answer_saved");
    $tpl->parse("LANGUAGE_SAVED_RESULT", "answer_saved_result");
    $tpl->parse("LANGUAGE_LABEL_BUTTON_PREVIOUS", "answer_label_button_previous");
    $tpl->parse("LANGUAGE_LABEL_BUTTON_NEXT", "answer_label_button_next");
    $tpl->parse("LANGUAGE_LABEL_BUTTON_FINISH", "answer_label_button_finish");
	$tpl->parse("LANGUAGE_LABEL_BUTTON_HOME", "answer_label_button_home");
	$tpl->parse("LANGUAGE_GRADING_LABEL_ONE", "answer_grading_label_one");
	$tpl->parse("LANGUAGE_GRADING_LABEL_TWO", "answer_grading_label_two");
	$tpl->parse("LANGUAGE_GRADING_LABEL_THREE", "answer_grading_label_three");
	$tpl->parse("LANGUAGE_GRADING_LABEL_FOUR", "answer_grading_label_four");
	$tpl->parse("LANGUAGE_GRADING_LABEL_FIVE", "answer_grading_label_five");
	$tpl->parse("LANGUAGE_GRADING_LABEL_SIX", "answer_grading_label_six");

    $tpl->parse("OUT", $current_file);
    $out = $tpl->get_var("OUT");

    $fp = fopen("$tmp_doc_root/templates/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);

    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $tmp_doc_root/templates/$language/$current_file.ihtml)<br>");


    //*******************************************************************
    //* insert_description.ihtml
    //*******************************************************************

    $current_file = "insert_description";

    $tpl->set_file($current_file, "$current_file.ihtml");
    $tpl->set_block("language", "insert_description_title");
    $tpl->set_block("language", "insert_description_error_no_text");
    $tpl->set_block("language", "insert_description_label_text");
    $tpl->set_block("language", "insert_description_button_insert");
    $tpl->set_block("language", "insert_description_button_edit");
    $tpl->set_block("language", "insert_description_button_cancel");
    $tpl->parse("TITLE", "insert_description_title");
    $tpl->parse("LANGUAGE_ERROR_NO_TEXT", "insert_description_error_no_text");
    $tpl->parse("LANGUAGE_LABEL_TEXT", "insert_description_label_text");
    $tpl->parse("LANGUAGE_BUTTON_INSERT", "insert_description_button_insert");
    $tpl->parse("LANGUAGE_BUTTON_EDIT", "insert_description_button_edit");
    $tpl->parse("LANGUAGE_BUTTON_CANCEL", "insert_description_button_cancel");

    $tpl->parse("CONTENT", $current_file);
    $tpl->parse("OUT", "blueprint");
    $out = $tpl->get_var("OUT");

    $tpl->unset_var("BUTTON_LABEL");

    $fp = fopen("$tmp_doc_root/templates/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);

    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $tmp_doc_root/templates/$language/$current_file.ihtml)<br>");
	
	
    //*******************************************************************
    //* insert_caption.ihtml
    //*******************************************************************

    $current_file = "insert_caption";

    $tpl->set_file($current_file, "$current_file.ihtml");
    $tpl->set_block("language", "insert_caption_title");
    $tpl->set_block("language", "insert_caption_error_no_text");
    $tpl->set_block("language", "insert_caption_label_text");
    $tpl->set_block("language", "insert_caption_button_insert");
    $tpl->set_block("language", "insert_caption_button_edit");
    $tpl->set_block("language", "insert_caption_button_cancel");
    $tpl->parse("TITLE", "insert_caption_title");
    $tpl->parse("LANGUAGE_ERROR_NO_TEXT", "insert_caption_error_no_text");
    $tpl->parse("LANGUAGE_LABEL_TEXT", "insert_caption_label_text");
    $tpl->parse("LANGUAGE_BUTTON_INSERT", "insert_caption_button_insert");
    $tpl->parse("LANGUAGE_BUTTON_EDIT", "insert_caption_button_edit");
    $tpl->parse("LANGUAGE_BUTTON_CANCEL", "insert_caption_button_cancel");

    $tpl->parse("CONTENT", $current_file);
    $tpl->parse("OUT", "blueprint");
    $out = $tpl->get_var("OUT");

    $tpl->unset_var("BUTTON_LABEL");

    $fp = fopen("$tmp_doc_root/templates/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);

    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $tmp_doc_root/templates/$language/$current_file.ihtml)<br>");


    //*******************************************************************
    //* insert_input_checkbox.ihtml
    //*******************************************************************

    $current_file = "insert_input_checkbox";

    $tpl->set_file($current_file, "$current_file.ihtml");
    $tpl->set_block("language", "insert_input_checkbox_title");
    $tpl->set_block("language", "insert_input_checkbox_error_no_number_col");
    $tpl->set_block("language", "insert_input_checkbox_error_no_options");
    $tpl->set_block("language", "insert_input_checkbox_script_confirm_1");
    $tpl->set_block("language", "insert_input_checkbox_script_confirm_2");
    $tpl->set_block("language", "insert_input_checkbox_label_question");
	$tpl->set_block("language", "insert_input_checkbox_label_answer");
    $tpl->set_block("language", "insert_input_checkbox_label_question_pos");
    $tpl->set_block("language", "insert_input_checkbox_label_question_pos_left");
    $tpl->set_block("language", "insert_input_checkbox_label_question_pos_top");
    $tpl->set_block("language", "insert_input_checkbox_label_question_text");
    $tpl->set_block("language", "insert_input_checkbox_label_columns");
    $tpl->set_block("language", "insert_input_checkbox_label_columns2");
    $tpl->set_block("language", "insert_input_checkbox_label_must");
    $tpl->set_block("language", "insert_input_checkbox_label_output");
    $tpl->set_block("language", "insert_input_checkbox_label_no_option");
    $tpl->set_block("language", "insert_input_checkbox_label_topic_1");
    $tpl->set_block("language", "insert_input_checkbox_label_topic_2");
    $tpl->set_block("language", "insert_input_checkbox_label_topic_3");
    $tpl->set_block("language", "insert_input_checkbox_label_option");
    $tpl->set_block("language", "insert_input_checkbox_alt_edit");
	$tpl->set_block("language", "insert_input_checkbox_alt_delete");
    $tpl->set_block("language", "insert_input_checkbox_button_insert");
    $tpl->set_block("language", "insert_input_checkbox_button_sort");
    $tpl->set_block("language", "insert_input_checkbox_button_save");
    $tpl->set_block("language", "insert_input_checkbox_button_cancel");
    $tpl->parse("TITLE", "insert_input_checkbox_title");
    $tpl->parse("LANGUAGE_ERROR_NO_NUMBER_COL", "insert_input_checkbox_error_no_number_col");
    $tpl->parse("LANGUAGE_ERROR_NO_OPTIONS", "insert_input_checkbox_error_no_options");
    $tpl->parse("LANGUAGE_SCRIPT_CONFIRM_1", "insert_input_checkbox_script_confirm_1");
    $tpl->parse("LANGUAGE_SCRIPT_CONFIRM_2", "insert_input_checkbox_script_confirm_2");
    $tpl->parse("LANGUAGE_LABEL_QUESTION", "insert_input_checkbox_label_question");
	$tpl->parse("LANGUAGE_LABEL_ANSWER", "insert_input_checkbox_label_answer");
    $tpl->parse("LANGUAGE_LABEL_QUESTION_POS", "insert_input_checkbox_label_question_pos");
    $tpl->parse("LANGUAGE_LABEL_QUESTION_POS_LEFT", "insert_input_checkbox_label_question_pos_left");
    $tpl->parse("LANGUAGE_LABEL_QUESTION_POS_TOP", "insert_input_checkbox_label_question_pos_top");
    $tpl->parse("LANGUAGE_LABEL_QUESTION_TEXT", "insert_input_checkbox_label_question_text");
    $tpl->parse("LANGUAGE_LABEL_COLUMNS", "insert_input_checkbox_label_columns");
	$tpl->parse("LANGUAGE_LABEL_COLUMNS2", "insert_input_checkbox_label_columns2");
    $tpl->parse("LANGUAGE_LABEL_MUST", "insert_input_checkbox_label_must");
    $tpl->parse("LANGUAGE_LABEL_OUTPUT", "insert_input_checkbox_label_output");
    $tpl->parse("LANGUAGE_LABEL_NO_OPTION", "insert_input_checkbox_label_no_option");
    $tpl->parse("LANGUAGE_LABEL_TOPIC_1", "insert_input_checkbox_label_topic_1");
    $tpl->parse("LANGUAGE_LABEL_TOPIC_2", "insert_input_checkbox_label_topic_2");
    $tpl->parse("LANGUAGE_LABEL_TOPIC_3", "insert_input_checkbox_label_topic_3");
    $tpl->parse("LANGUAGE_LABEL_OPTION", "insert_input_checkbox_label_option");
    $tpl->parse("LANGUAGE_ALT_EDIT", "insert_input_checkbox_alt_edit");
	$tpl->parse("LANGUAGE_ALT_DELETE", "insert_input_checkbox_alt_delete");
    $tpl->parse("LANGUAGE_BUTTON_INSERT", "insert_input_checkbox_button_insert");
    $tpl->parse("LANGUAGE_BUTTON_SORT", "insert_input_checkbox_button_sort");
    $tpl->parse("LANGUAGE_BUTTON_SAVE", "insert_input_checkbox_button_save");
    $tpl->parse("LANGUAGE_BUTTON_CANCEL", "insert_input_checkbox_button_cancel");

    $tpl->parse("CONTENT", $current_file);
    $tpl->parse("OUT", "blueprint");
    $out = $tpl->get_var("OUT");

    $tpl->unset_var("BUTTON_LABEL");

    $fp = fopen("$tmp_doc_root/templates/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);

    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $tmp_doc_root/templates/$language/$current_file.ihtml)<br>");


    //*******************************************************************
    //* insert_input_checkbox_sort.ihtml
    //*******************************************************************

    $current_file = "insert_input_checkbox_sort";

    $tpl->set_file($current_file, "$current_file.ihtml");
    $tpl->set_block("language", "insert_input_checkbox_sort_title");
    $tpl->set_block("language", "insert_input_checkbox_sort_button_save");
    $tpl->set_block("language", "insert_input_checkbox_sort_button_cancel");
    $tpl->parse("TITLE", "insert_input_checkbox_sort_title");
    $tpl->parse("LANGUAGE_BUTTON_SAVE", "insert_input_checkbox_sort_button_save");
    $tpl->parse("LANGUAGE_BUTTON_CANCEL", "insert_input_checkbox_sort_button_cancel");

    $tpl->parse("CONTENT", $current_file);
    $tpl->parse("OUT", "blueprint");
    $out = $tpl->get_var("OUT");

    $tpl->unset_var("BUTTON_LABEL");

    $fp = fopen("$tmp_doc_root/templates/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);

    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $tmp_doc_root/templates/$language/$current_file.ihtml)<br>");


    //*******************************************************************
    //* insert_input_radiobutton.ihtml
    //*******************************************************************

    $current_file = "insert_input_radiobutton";

    $tpl->set_file($current_file, "$current_file.ihtml");
    $tpl->set_block("language", "insert_input_radiobutton_title");
    $tpl->set_block("language", "insert_input_radiobutton_error_no_number_col");
    $tpl->set_block("language", "insert_input_radiobutton_error_no_options");
    $tpl->set_block("language", "insert_input_radiobutton_script_confirm_1");
    $tpl->set_block("language", "insert_input_radiobutton_script_confirm_2");
    $tpl->set_block("language", "insert_input_radiobutton_label_question");
	$tpl->set_block("language", "insert_input_radiobutton_label_answer");
    $tpl->set_block("language", "insert_input_radiobutton_label_question_pos");
    $tpl->set_block("language", "insert_input_radiobutton_label_question_pos_left");
    $tpl->set_block("language", "insert_input_radiobutton_label_question_pos_top");
    $tpl->set_block("language", "insert_input_radiobutton_label_question_text");
    $tpl->set_block("language", "insert_input_radiobutton_label_columns");
	$tpl->set_block("language", "insert_input_radiobutton_label_columns2");
    $tpl->set_block("language", "insert_input_radiobutton_label_must");
    $tpl->set_block("language", "insert_input_radiobutton_label_output");
    $tpl->set_block("language", "insert_input_radiobutton_label_no_option");
    $tpl->set_block("language", "insert_input_radiobutton_label_topic_1");
    $tpl->set_block("language", "insert_input_radiobutton_label_topic_2");
    $tpl->set_block("language", "insert_input_radiobutton_label_topic_3");
    $tpl->set_block("language", "insert_input_radiobutton_label_option");
    $tpl->set_block("language", "insert_input_radiobutton_label_option_not_selected");
    $tpl->set_block("language", "insert_input_radiobutton_alt_edit");
	$tpl->set_block("language", "insert_input_radiobutton_alt_delete");
    $tpl->set_block("language", "insert_input_radiobutton_button_insert");
    $tpl->set_block("language", "insert_input_radiobutton_button_sort");
    $tpl->set_block("language", "insert_input_radiobutton_button_save");
    $tpl->set_block("language", "insert_input_radiobutton_button_cancel");
    $tpl->parse("TITLE", "insert_input_radiobutton_title");
    $tpl->parse("LANGUAGE_ERROR_NO_NUMBER_COL", "insert_input_radiobutton_error_no_number_col");
    $tpl->parse("LANGUAGE_ERROR_NO_OPTIONS", "insert_input_radiobutton_error_no_options");
    $tpl->parse("LANGUAGE_SCRIPT_CONFIRM_1", "insert_input_radiobutton_script_confirm_1");
    $tpl->parse("LANGUAGE_SCRIPT_CONFIRM_2", "insert_input_radiobutton_script_confirm_2");
    $tpl->parse("LANGUAGE_LABEL_QUESTION", "insert_input_radiobutton_label_question");
	$tpl->parse("LANGUAGE_LABEL_ANSWER", "insert_input_radiobutton_label_answer");
    $tpl->parse("LANGUAGE_LABEL_QUESTION_POS", "insert_input_radiobutton_label_question_pos");
    $tpl->parse("LANGUAGE_LABEL_QUESTION_POS_LEFT", "insert_input_radiobutton_label_question_pos_left");
    $tpl->parse("LANGUAGE_LABEL_QUESTION_POS_TOP", "insert_input_radiobutton_label_question_pos_top");
    $tpl->parse("LANGUAGE_LABEL_QUESTION_TEXT", "insert_input_radiobutton_label_question_text");
    $tpl->parse("LANGUAGE_LABEL_COLUMNS", "insert_input_radiobutton_label_columns");
	$tpl->parse("LANGUAGE_LABEL_COLUMNS2", "insert_input_radiobutton_label_columns2");
    $tpl->parse("LANGUAGE_LABEL_MUST", "insert_input_radiobutton_label_must");
    $tpl->parse("LANGUAGE_LABEL_OUTPUT", "insert_input_radiobutton_label_output");
    $tpl->parse("LANGUAGE_LABEL_NO_OPTION", "insert_input_radiobutton_label_no_option");
    $tpl->parse("LANGUAGE_LABEL_TOPIC_1", "insert_input_radiobutton_label_topic_1");
    $tpl->parse("LANGUAGE_LABEL_TOPIC_2", "insert_input_radiobutton_label_topic_2");
    $tpl->parse("LANGUAGE_LABEL_TOPIC_3", "insert_input_radiobutton_label_topic_3");
    $tpl->parse("LANGUAGE_LABEL_OPTION", "insert_input_radiobutton_label_option");
    $tpl->parse("LANGUAGE_LABEL_OPTION_NOT_SELECTED", "insert_input_radiobutton_label_option_not_selected");
    $tpl->parse("LANGUAGE_ALT_EDIT", "insert_input_radiobutton_alt_edit");
	$tpl->parse("LANGUAGE_ALT_DELETE", "insert_input_radiobutton_alt_delete");
    $tpl->parse("LANGUAGE_BUTTON_INSERT", "insert_input_radiobutton_button_insert");
    $tpl->parse("LANGUAGE_BUTTON_SORT", "insert_input_radiobutton_button_sort");
    $tpl->parse("LANGUAGE_BUTTON_SAVE", "insert_input_radiobutton_button_save");
    $tpl->parse("LANGUAGE_BUTTON_CANCEL", "insert_input_radiobutton_button_cancel");

    $tpl->parse("CONTENT", $current_file);
    $tpl->parse("OUT", "blueprint");
    $out = $tpl->get_var("OUT");

    $tpl->unset_var("BUTTON_LABEL");

    $fp = fopen("$tmp_doc_root/templates/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);

    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $tmp_doc_root/templates/$language/$current_file.ihtml)<br>");


    //*******************************************************************
    //* insert_input_radiobutton_sort.ihtml
    //*******************************************************************

    $current_file = "insert_input_radiobutton_sort";

    $tpl->set_file($current_file, "$current_file.ihtml");
    $tpl->set_block("language", "insert_input_radiobutton_sort_title");
    $tpl->set_block("language", "insert_input_radiobutton_sort_button_save");
    $tpl->set_block("language", "insert_input_radiobutton_sort_button_cancel");
    $tpl->parse("TITLE", "insert_input_radiobutton_sort_title");
    $tpl->parse("BUTTON_LABEL", "insert_input_radiobutton_sort_button_save");
    $tpl->parse("LANGUAGE_BUTTON_CANCEL", "insert_input_radiobutton_sort_button_cancel");

    $tpl->parse("CONTENT", $current_file);
    $tpl->parse("OUT", "blueprint");
    $out = $tpl->get_var("OUT");

    $tpl->unset_var("BUTTON_LABEL");

    $fp = fopen("$tmp_doc_root/templates/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);

    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $tmp_doc_root/templates/$language/$current_file.ihtml)<br>");


    //*******************************************************************
    //* insert_input_selectbox.ihtml
    //*******************************************************************

    $current_file = "insert_input_selectbox";

    $tpl->set_file($current_file, "$current_file.ihtml");
    $tpl->set_block("language2", "insert_input_selectbox_title");
    $tpl->set_block("language2", "insert_input_selectbox_error_no_number_width");
    $tpl->set_block("language2", "insert_input_selectbox_error_no_number_rows");
    $tpl->set_block("language2", "insert_input_selectbox_error_no_options");
    $tpl->set_block("language2", "insert_input_selectbox_script_confirm_1");
    $tpl->set_block("language2", "insert_input_selectbox_script_confirm_2");
    $tpl->set_block("language2", "insert_input_selectbox_label_question");
	$tpl->set_block("language2", "insert_input_selectbox_label_answer");
    $tpl->set_block("language2", "insert_input_selectbox_label_question_pos");
    $tpl->set_block("language2", "insert_input_selectbox_label_question_pos_left");
    $tpl->set_block("language2", "insert_input_selectbox_label_question_pos_top");
    $tpl->set_block("language2", "insert_input_selectbox_label_question_text");
    $tpl->set_block("language2", "insert_input_selectbox_label_width");
	$tpl->set_block("language2", "insert_input_selectbox_label_pixel");
    $tpl->set_block("language2", "insert_input_selectbox_label_rows");
	$tpl->set_block("language2", "insert_input_selectbox_label_rows2");
    $tpl->set_block("language2", "insert_input_selectbox_label_must");
    $tpl->set_block("language2", "insert_input_selectbox_label_output");
    $tpl->set_block("language2", "insert_input_selectbox_label_no_option");
    $tpl->set_block("language2", "insert_input_selectbox_label_topic_1");
    $tpl->set_block("language2", "insert_input_selectbox_label_topic_2");
    $tpl->set_block("language2", "insert_input_selectbox_label_topic_3");
    $tpl->set_block("language2", "insert_input_selectbox_label_option");
    $tpl->set_block("language2", "insert_input_selectbox_alt_edit");
	$tpl->set_block("language2", "insert_input_selectbox_alt_delete");
    $tpl->set_block("language2", "insert_input_selectbox_button_insert");
    $tpl->set_block("language2", "insert_input_selectbox_button_sort");
    $tpl->set_block("language2", "insert_input_selectbox_button_save");
    $tpl->set_block("language2", "insert_input_selectbox_button_cancel");
    $tpl->parse("TITLE", "insert_input_selectbox_title");
    $tpl->parse("LANGUAGE_ERROR_NO_NUMBER_WIDTH", "insert_input_selectbox_error_no_number_width");
    $tpl->parse("LANGUAGE_ERROR_NO_NUMBER_ROWS", "insert_input_selectbox_error_no_number_rows");
    $tpl->parse("LANGUAGE_ERROR_NO_OPTIONS", "insert_input_selectbox_error_no_options");
    $tpl->parse("LANGUAGE_SCRIPT_CONFIRM_1", "insert_input_selectbox_script_confirm_1");
    $tpl->parse("LANGUAGE_SCRIPT_CONFIRM_2", "insert_input_selectbox_script_confirm_2");
    $tpl->parse("LANGUAGE_LABEL_QUESTION", "insert_input_selectbox_label_question");
	$tpl->parse("LANGUAGE_LABEL_ANSWER", "insert_input_selectbox_label_answer");
    $tpl->parse("LANGUAGE_LABEL_QUESTION_POS", "insert_input_selectbox_label_question_pos");
    $tpl->parse("LANGUAGE_LABEL_QUESTION_POS_LEFT", "insert_input_selectbox_label_question_pos_left");
    $tpl->parse("LANGUAGE_LABEL_QUESTION_POS_TOP", "insert_input_selectbox_label_question_pos_top");
    $tpl->parse("LANGUAGE_LABEL_QUESTION_TEXT", "insert_input_selectbox_label_question_text");
    $tpl->parse("LANGUAGE_LABEL_WIDTH", "insert_input_selectbox_label_width");
	$tpl->parse("LANGUAGE_LABEL_PIXEL", "insert_input_selectbox_label_pixel");
    $tpl->parse("LANGUAGE_LABEL_ROWS", "insert_input_selectbox_label_rows");
    $tpl->parse("LANGUAGE_LABEL_ROWS2", "insert_input_selectbox_label_rows2");
    $tpl->parse("LANGUAGE_LABEL_MUST", "insert_input_selectbox_label_must");
    $tpl->parse("LANGUAGE_LABEL_OUTPUT", "insert_input_selectbox_label_output");
    $tpl->parse("LANGUAGE_LABEL_NO_OPTION", "insert_input_selectbox_label_no_option");
    $tpl->parse("LANGUAGE_LABEL_TOPIC_1", "insert_input_selectbox_label_topic_1");
    $tpl->parse("LANGUAGE_LABEL_TOPIC_2", "insert_input_selectbox_label_topic_2");
    $tpl->parse("LANGUAGE_LABEL_TOPIC_3", "insert_input_selectbox_label_topic_3");
    $tpl->parse("LANGUAGE_LABEL_OPTION", "insert_input_selectbox_label_option");
    $tpl->parse("LANGUAGE_ALT_EDIT", "insert_input_selectbox_alt_edit");
	$tpl->parse("LANGUAGE_ALT_DELETE", "insert_input_selectbox_alt_delete");
    $tpl->parse("LANGUAGE_BUTTON_INSERT", "insert_input_selectbox_button_insert");
    $tpl->parse("LANGUAGE_BUTTON_SORT", "insert_input_selectbox_button_sort");
    $tpl->parse("LANGUAGE_BUTTON_SAVE", "insert_input_selectbox_button_save");
    $tpl->parse("LANGUAGE_BUTTON_CANCEL", "insert_input_selectbox_button_cancel");

    $tpl->parse("CONTENT", $current_file);
    $tpl->parse("OUT", "blueprint");
    $out = $tpl->get_var("OUT");

    $tpl->unset_var("BUTTON_LABEL");

    $fp = fopen("$tmp_doc_root/templates/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);

    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $tmp_doc_root/templates/$language/$current_file.ihtml)<br>");


    //*******************************************************************
    //* insert_input_selectbox_sort.ihtml
    //*******************************************************************

    $current_file = "insert_input_selectbox_sort";

    $tpl->set_file($current_file, "$current_file.ihtml");
    $tpl->set_block("language2", "insert_input_selectbox_sort_title");
    $tpl->set_block("language2", "insert_input_selectbox_sort_button_save");
    $tpl->set_block("language2", "insert_input_selectbox_sort_button_cancel");
    $tpl->parse("TITLE", "insert_input_selectbox_sort_title");
    $tpl->parse("BUTTON_LABEL", "insert_input_selectbox_sort_button_save");
    $tpl->parse("LANGUAGE_BUTTON_CANCEL", "insert_input_selectbox_sort_button_cancel");

    $tpl->parse("CONTENT", $current_file);
    $tpl->parse("OUT", "blueprint");
    $out = $tpl->get_var("OUT");

    $tpl->unset_var("BUTTON_LABEL");

    $fp = fopen("$tmp_doc_root/templates/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);

    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $tmp_doc_root/templates/$language/$current_file.ihtml)<br>");


    //*******************************************************************
    //* insert_input_text.ihtml
    //*******************************************************************

    $current_file = "insert_input_text";

    $tpl->set_file($current_file, "$current_file.ihtml");
    $tpl->set_block("language2", "insert_input_text_title");
    $tpl->set_block("language2", "insert_input_text_error_no_number_width");
    $tpl->set_block("language2", "insert_input_text_error_no_number_maxlength");
	$tpl->set_block("language2", "insert_input_text_error_no_number_rows");
	$tpl->set_block("language2", "insert_input_text_error_bigger_zero_rows");
    $tpl->set_block("language2", "insert_input_text_label_question");
    $tpl->set_block("language2", "insert_input_text_label_question_pos");
    $tpl->set_block("language2", "insert_input_text_label_question_pos_left");
    $tpl->set_block("language2", "insert_input_text_label_question_pos_top");
    $tpl->set_block("language2", "insert_input_text_label_question_text");
    $tpl->set_block("language2", "insert_input_text_label_answer");
    $tpl->set_block("language2", "insert_input_text_label_answer_value");
	$tpl->set_block("language2", "insert_input_text_label_answer_rows");
	$tpl->set_block("language2", "insert_input_text_label_answer_rows2");
	$tpl->set_block("language2", "insert_input_text_label_answer_chars");
	$tpl->set_block("language2", "insert_input_text_label_answer_pixel");
    $tpl->set_block("language2", "insert_input_text_label_answer_width");
    $tpl->set_block("language2", "insert_input_text_label_answer_maxlength");	
    $tpl->set_block("language2", "insert_input_text_label_must");
    $tpl->set_block("language2", "insert_input_text_label_output");
    $tpl->set_block("language2", "insert_input_text_button_insert");
    $tpl->set_block("language2", "insert_input_text_button_edit");
    $tpl->set_block("language2", "insert_input_text_button_cancel");
    $tpl->parse("TITLE", "insert_input_text_title");
     $tpl->parse("LANGUAGE_ERROR_NO_NUMBER_WIDTH", "insert_input_text_error_no_number_width");
    $tpl->parse("LANGUAGE_ERROR_NO_NUMBER_MAXLENGTH", "insert_input_text_error_no_number_maxlength");
	$tpl->parse("LANGUAGE_ERROR_NO_NUMBER_ROWS", "insert_input_text_error_no_number_rows");
	$tpl->parse("LANGUAGE_ERROR_BIGGER_ZERO_ROWS", "insert_input_text_error_bigger_zero_rows");
    $tpl->parse("LANGUAGE_LABEL_QUESTION", "insert_input_text_label_question");
    $tpl->parse("LANGUAGE_LABEL_QUESTION_POS", "insert_input_text_label_question_pos");
    $tpl->parse("LANGUAGE_LABEL_QUESTION_POS_LEFT", "insert_input_text_label_question_pos_left");
    $tpl->parse("LANGUAGE_LABEL_QUESTION_POS_TOP", "insert_input_text_label_question_pos_top");
    $tpl->parse("LANGUAGE_LABEL_QUESTION_TEXT", "insert_input_text_label_question_text");
    $tpl->parse("LANGUAGE_LABEL_ANSWER", "insert_input_text_label_answer");
    $tpl->parse("LANGUAGE_LABEL_ANSWER_VALUE", "insert_input_text_label_answer_value");
	$tpl->parse("LANGUAGE_LABEL_ANSWER_ROWS", "insert_input_text_label_answer_rows");
	$tpl->parse("LANGUAGE_LABEL_ANSWER_ROWS2", "insert_input_text_label_answer_rows2");
	$tpl->parse("LANGUAGE_LABEL_ANSWER_CHARS", "insert_input_text_label_answer_chars");
	$tpl->parse("LANGUAGE_LABEL_ANSWER_PIXEL", "insert_input_text_label_answer_pixel");
    $tpl->parse("LANGUAGE_LABEL_ANSWER_WIDTH", "insert_input_text_label_answer_width");
    $tpl->parse("LANGUAGE_LABEL_ANSWER_MAXLENGTH", "insert_input_text_label_answer_maxlength");
    $tpl->parse("LANGUAGE_LABEL_MUST", "insert_input_text_label_must");
    $tpl->parse("LANGUAGE_LABEL_OUTPUT", "insert_input_text_label_output");
    $tpl->parse("LANGUAGE_BUTTON_INSERT", "insert_input_text_button_insert");
    $tpl->parse("LANGUAGE_BUTTON_EDIT", "insert_input_text_button_edit");
    $tpl->parse("LANGUAGE_BUTTON_CANCEL", "insert_input_text_button_cancel");

    $tpl->parse("CONTENT", $current_file);
    $tpl->parse("OUT", "blueprint");
    $out = $tpl->get_var("OUT");

    $tpl->unset_var("BUTTON_LABEL");

    $fp = fopen("$tmp_doc_root/templates/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);

    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $tmp_doc_root/templates/$language/$current_file.ihtml)<br>");


    //*******************************************************************
    //* new.ihtml
    //*******************************************************************

    $current_file = "new";

    $tpl->set_file($current_file, "$current_file.ihtml");
    $tpl->set_block("language2", "new_title");
    $tpl->set_block("language2", "new_label_title");
	$tpl->set_block("language2", "new_label_description");
    $tpl->set_block("language2", "new_error_no_title");
    $tpl->set_block("language2", "new_button_label");
    $tpl->set_block("language2", "new_button_cancel_label");

    $tpl->parse("TITLE", "new_title");
    $tpl->parse("LANGUAGE_LABEL_TITLE", "new_label_title");
	$tpl->parse("LANGUAGE_LABEL_DESCRIPTION", "new_label_description");
    $tpl->parse("LANGUAGE_ERROR_NO_TITLE", "new_error_no_title");
    $tpl->parse("BUTTON_LABEL", "new_button_label");
    $tpl->parse("LANGUAGE_BUTTON_CANCEL", "new_button_cancel_label");
    $tpl->parse("CONTENT", $current_file);
    $tpl->parse("OUT", "blueprint");
    $out = $tpl->get_var("OUT");

    $tpl->unset_var("BUTTON_LABEL");

    $fp = fopen("$tmp_doc_root/templates/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);

    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $tmp_doc_root/templates/$language/$current_file.ihtml)<br>");


    //*******************************************************************
    //* result.ihtml
    //*******************************************************************

    $current_file = "result";

    $tpl->set_file($current_file, "$current_file.ihtml");
    $tpl->set_block("language2", "result_title");
    $tpl->set_block("language2", "result_header_creator");
    $tpl->set_block("language2", "result_header_creation_time");
    $tpl->set_block("language2", "result_label_results_none");
    $tpl->set_block("language2", "result_label_sort_ascending");
    $tpl->set_block("language2", "result_label_sort_descending");
    $tpl->set_block("language2", "result_label_details");
    $tpl->set_block("language2", "result_label_export");
    $tpl->set_block("language2", "result_label_pages");
    $tpl->set_block("language2", "result_label_edit_answer");
    $tpl->set_block("language2", "result_label_delete_answer");
    $tpl->set_block("language2", "result_error_not_allowed");
    $tpl->set_block("language2", "result_script_confirm");
	$tpl->set_block("language2", "result_label_break_result");
	$tpl->set_block("language2", "result_label_all");
	$tpl->set_block("language2", "result_owner");
	$tpl->set_block("language2", "result_last_change");
    $tpl->parse("LANGUAGE_TITLE", "result_title");
    $tpl->parse("LANGUAGE_HEADER_CREATION_TIME", "result_header_creation_time");
    $tpl->parse("LANGUAGE_HEADER_CREATOR", "result_header_creator");
    $tpl->parse("LANGUAGE_RESULTS_NONE", "result_label_results_none");
    $tpl->parse("LANGUAGE_SORT_ASCENDING", "result_label_ascending");
    $tpl->parse("LANGUAGE_SORT_DESCENDING", "result_label_descending");
    $tpl->parse("LANGUAGE_LABEL_DETAILS", "result_label_details");
    $tpl->parse("LANGUAGE_LABEL_EXPORT", "result_label_export");
    $tpl->parse("LANGUAGE_LABEL_PAGES", "result_label_pages");
    $tpl->parse("LANGUAGE_LABEL_EDIT_ANSWER", "result_label_edit_answer");
    $tpl->parse("LANGUAGE_LABEL_DELETE_ANSWER", "result_label_delete_answer");
    $tpl->parse("LANGUAGE_ERROR_NOT_ALLOWED", "result_error_not_allowed");
    $tpl->parse("LANGUAGE_SCRIPT_CONFIRM", "result_script_confirm");
	$tpl->parse("LANGUAGE_LABEL_BREAK_RESULT", "result_label_break_result");
	$tpl->parse("LANGUAGE_LABEL_ALL", "result_label_all");
	$tpl->parse("LANGUAGE_OWNER", "result_owner");
	$tpl->parse("LANGUAGE_LAST_CHANGE", "result_last_change");
	
    $tpl->unset_var("TITLE");

    $tpl->parse("OUT", $current_file);
    $out = $tpl->get_var("OUT");

    $fp = fopen("$tmp_doc_root/templates/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);

    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $tmp_doc_root/templates/$language/$current_file.ihtml)<br>");


    //*******************************************************************
    //* result_details.ihtml
    //*******************************************************************

    $current_file = "result_details";

    $tpl->set_file($current_file, "$current_file.ihtml");
    $tpl->set_block("language2", "result_details_title");
    $tpl->set_block("language2", "result_details_label_creator");
    $tpl->set_block("language2", "result_details_label_creation_time");
    $tpl->set_block("language2", "result_details_label_question");
    $tpl->set_block("language2", "result_details_label_answer");
    $tpl->set_block("language2", "result_details_error_not_allowed");
    $tpl->parse("TITLE", "result_details_title");
    $tpl->parse("LANGUAGE_LABEL_CREATOR", "result_details_label_creator");
    $tpl->parse("LANGUAGE_LABEL_CREATION_TIME", "result_details_label_creation_time");
    $tpl->parse("LANGUAGE_LABEL_QUESTION", "result_details_label_question");
    $tpl->parse("LANGUAGE_LABEL_ANSWER", "result_details_label_answer");
    $tpl->parse("LANGUAGE_ERROR_NOT_ALLOWED", "result_details_error_not_allowed");

    $tpl->parse("CONTENT", $current_file);
    $tpl->parse("OUT", "blueprint");
    $out = $tpl->get_var("OUT");

    $tpl->unset_var("BUTTON_LABEL");

    $fp = fopen("$tmp_doc_root/templates/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);

    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $tmp_doc_root/templates/$language/$current_file.ihtml)<br>");


    //*******************************************************************
    //* result_export.ihtml
    //*******************************************************************

    $current_file = "result_export";

    $tpl->set_file($current_file, "$current_file.ihtml");
    $tpl->set_block("language2", "result_export_questionary");
    $tpl->set_block("language2", "result_export_obj_id");
    $tpl->set_block("language2", "result_export_date");
    $tpl->set_block("language2", "result_export_fillout");
    $tpl->set_block("language2", "result_export_fillout_1");
    $tpl->set_block("language2", "result_export_fillout_n");
    $tpl->set_block("language2", "result_export_breakresult");
    $tpl->set_block("language2", "result_export_question");
    $tpl->set_block("language2", "result_export_label_creator");
    $tpl->set_block("language2", "result_export_label_creation_time");
	$tpl->set_block("language2", "result_export_label_description");
	$tpl->set_block("language2", "result_export_label_edit_answer");
	$tpl->set_block("language2", "result_export_label_edit_own_answer");
	$tpl->set_block("language2", "result_export_label_edit_time");
	$tpl->set_block("language2", "result_export_label_yes");
	$tpl->set_block("language2", "result_export_label_no");
    $tpl->parse("LANGUAGE_LABEL_QUESTIONARY", "result_export_questionary");
    $tpl->parse("LANGUAGE_LABEL_OBJ_ID", "result_export_obj_id");
    $tpl->parse("LANGUAGE_LABEL_DATE", "result_export_date");
    $tpl->parse("LANGUAGE_LABEL_FILLOUT", "result_export_fillout");
    $tpl->parse("LANGUAGE_LABEL_FILLOUT_1", "result_export_fillout_1");
    $tpl->parse("LANGUAGE_LABEL_FILLOUT_N", "result_export_fillout_n");
    $tpl->parse("LANGUAGE_LABEL_BREAKRESULT", "result_export_breakresult");
    $tpl->parse("LANGUAGE_LABEL_QUESTION", "result_export_question");
    $tpl->parse("LANGUAGE_LABEL_CREATOR", "result_export_label_creator");
    $tpl->parse("LANGUAGE_LABEL_CREATION_TIME", "result_export_label_creation_time");
	$tpl->parse("LANGUAGE_LABEL_DESCRIPTION", "result_export_label_description");
	$tpl->parse("LANGUAGE_LABEL_EDIT_ANSWER", "result_export_label_edit_answer");
	$tpl->parse("LANGUAGE_LABEL_EDIT_OWN_ANSWER", "result_export_label_edit_own_answer");
	$tpl->parse("LANGUAGE_LABEL_EDIT_TIME", "result_export_label_edit_time");
	$tpl->parse("LANGUAGE_LABEL_YES", "result_export_label_yes");
	$tpl->parse("LANGUAGE_LABEL_NO", "result_export_label_no");
	
    $tpl->parse("OUT", $current_file);
    $out = $tpl->get_var("OUT");

    $fp = fopen("$tmp_doc_root/templates/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);

    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $tmp_doc_root/templates/$language/$current_file.ihtml)<br>");


    //*******************************************************************
    //* rights.ihtml
    //*******************************************************************

    $current_file = "rights";

    $tpl->set_file($current_file, "$current_file.ihtml");

    $tpl->set_block("language2", "rights_title");
    $tpl->set_block("language2", "rights_publish_1");
    $tpl->set_block("language2", "rights_publish_2");
    $tpl->set_block("language2", "rights_publish_3");
    $tpl->set_block("language2", "rights_groups_none");
    $tpl->set_block("language2", "rights_favourites_none");
    $tpl->set_block("language2", "rights_favourites_double");
    $tpl->set_block("language2", "rights_fillout");
    $tpl->set_block("language2", "rights_edit");
    $tpl->set_block("language2", "rights_evaluate");
    $tpl->set_block("language2", "rights_owner");
    $tpl->set_block("language2", "rights_groups");
	$tpl->set_block("language2", "rights_group");
	$tpl->set_block("language2", "rights_user");
    $tpl->set_block("language2", "rights_favourites");
    $tpl->set_block("language2", "rights_button_save");

    $tpl->parse("TITLE", "rights_title");
    $tpl->parse("LANGUAGE_PUBLISH_1", "rights_publish_1");
    $tpl->parse("LANGUAGE_PUBLISH_2", "rights_publish_2");
    $tpl->parse("LANGUAGE_PUBLISH_3", "rights_publish_3");
    $tpl->parse("LANGUAGE_GROUPS_NONE", "rights_groups_none");
    $tpl->parse("LANGUAGE_FAVOURITES_NONE", "rights_favourites_none");
    $tpl->parse("LANGUAGE_FAVOURITES_DOUBLE", "rights_favourites_double");
    $tpl->parse("LANGUAGE_FILLOUT", "rights_fillout");
    $tpl->parse("LANGUAGE_EDIT", "rights_edit");
    $tpl->parse("LANGUAGE_EVALUATE", "rights_evaluate");
    $tpl->parse("LANGUAGE_OWNER", "rights_owner");
    $tpl->parse("LANGUAGE_GROUPS", "rights_groups");
	$tpl->parse("LANGUAGE_GROUP", "rights_group");
	$tpl->parse("LANGUAGE_USER", "rights_user");
    $tpl->parse("LANGUAGE_FAVOURITES", "rights_favourites");
    $tpl->parse("LANGUAGE_BUTTON_SAVE", "rights_button_save");

	$tpl->parse("CONTENT", $current_file);
    $tpl->parse("OUT", "blueprint");
    $out = $tpl->get_var("OUT");

    $tpl->unset_var("BUTTON_LABEL");

    $fp = fopen("$tmp_doc_root/templates/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);

    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $tmp_doc_root/templates/$language/$current_file.ihtml)<br>");

  
  
    //*******************************************************************
    //* insert_input_grading.ihtml
    //*******************************************************************

    $current_file = "insert_input_grading";

    $tpl->set_file($current_file, "$current_file.ihtml");
    $tpl->set_block("language2", "insert_input_grading_title");
	$tpl->set_block("language2", "insert_input_grading_error_no_text");
	$tpl->set_block("language2", "insert_input_grading_error_no_options");
	$tpl->set_block("language2", "insert_input_grading_button_insert");
	$tpl->set_block("language2", "insert_input_grading_button_sort");
	$tpl->set_block("language2", "insert_input_grading_button_save");
	$tpl->set_block("language2", "insert_input_grading_button_cancel");
	$tpl->set_block("language2", "insert_input_grading_script_confirm_1");
	$tpl->set_block("language2", "insert_input_grading_script_confirm_2");
	$tpl->set_block("language2", "insert_input_grading_label_general");
	$tpl->set_block("language2", "insert_input_grading_label_question");
	$tpl->set_block("language2", "insert_input_grading_label_description_text");
	$tpl->set_block("language2", "insert_input_grading_label_question_text");
	$tpl->set_block("language2", "insert_input_grading_label_action");
	$tpl->set_block("language2", "insert_input_grading_alt_edit");
	$tpl->set_block("language2", "insert_input_grading_alt_delete");
	$tpl->set_block("language2", "insert_input_grading_label_no_option");
	$tpl->set_block("language2", "insert_input_grading_label_must");
	$tpl->set_block("language2", "insert_input_grading_label_output");

    $tpl->parse("TITLE", "insert_input_grading_title");
	$tpl->parse("LANGUAGE_ERROR_NO_TEXT", "insert_input_grading_error_no_text");
	$tpl->parse("LANGUAGE_ERROR_NO_OPTIONS", "insert_input_grading_error_no_options");
	$tpl->parse("LANGUAGE_BUTTON_INSERT", "insert_input_grading_button_insert");
    $tpl->parse("LANGUAGE_BUTTON_SORT", "insert_input_grading_button_sort");
    $tpl->parse("LANGUAGE_BUTTON_SAVE", "insert_input_grading_button_save");
    $tpl->parse("LANGUAGE_BUTTON_CANCEL", "insert_input_grading_button_cancel");
	$tpl->parse("LANGUAGE_SCRIPT_CONFIRM_1", "insert_input_grading_script_confirm_1");
    $tpl->parse("LANGUAGE_SCRIPT_CONFIRM_2", "insert_input_grading_script_confirm_2");
	$tpl->parse("LANGUAGE_LABEL_GENERAL", "insert_input_grading_label_general");
	$tpl->parse("LANGUAGE_LABEL_QUESTION", "insert_input_grading_label_question");
	$tpl->parse("LANGUAGE_LABEL_DESCRIPTION_TEXT", "insert_input_grading_label_description_text");
	$tpl->parse("LANGUAGE_LABEL_QUESTION_TEXT", "insert_input_grading_label_question_text");
	$tpl->parse("LANGUAGE_LABEL_ACTION", "insert_input_grading_label_action");
	$tpl->parse("LANGUAGE_ALT_EDIT", "insert_input_grading_alt_edit");
	$tpl->parse("LANGUAGE_ALT_DELETE", "insert_input_grading_alt_delete");
    $tpl->parse("LANGUAGE_LABEL_NO_OPTION", "insert_input_grading_label_no_option");
	$tpl->parse("LANGUAGE_LABEL_MUST", "insert_input_grading_label_must");
    $tpl->parse("LANGUAGE_LABEL_OUTPUT", "insert_input_grading_label_output");  

    $tpl->parse("CONTENT", $current_file);
    $tpl->parse("OUT", "blueprint");
    $out = $tpl->get_var("OUT");

    $tpl->unset_var("BUTTON_LABEL");

    $fp = fopen("$tmp_doc_root/templates/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);

    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $tmp_doc_root/templates/$language/$current_file.ihtml)<br>");
  
  
    //*******************************************************************
    //* insert_input_grading_sort.ihtml
    //*******************************************************************

    $current_file = "insert_input_grading_sort";

    $tpl->set_file($current_file, "$current_file.ihtml");
    $tpl->set_block("language2", "insert_input_grading_sort_title");
    $tpl->set_block("language2", "insert_input_grading_sort_button_save");
    $tpl->set_block("language2", "insert_input_grading_sort_button_cancel");
    $tpl->parse("TITLE", "insert_input_grading_sort_title");
    $tpl->parse("BUTTON_LABEL", "insert_input_grading_sort_button_save");
    $tpl->parse("LANGUAGE_BUTTON_CANCEL", "insert_input_grading_sort_button_cancel");

    $tpl->parse("CONTENT", $current_file);
    $tpl->parse("OUT", "blueprint");
    $out = $tpl->get_var("OUT");

    $tpl->unset_var("BUTTON_LABEL");

    $fp = fopen("$tmp_doc_root/templates/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);

    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $tmp_doc_root/templates/$language/$current_file.ihtml)<br>");
	
	
	//*******************************************************************
    //* insert_input_tendency.ihtml
    //*******************************************************************

    $current_file = "insert_input_tendency";

    $tpl->set_file($current_file, "$current_file.ihtml");
    $tpl->set_block("language2", "insert_input_tendency_title");
	$tpl->set_block("language2", "insert_input_tendency_error_no_text");
	$tpl->set_block("language2", "insert_input_tendency_error_no_options");
	$tpl->set_block("language2", "insert_input_tendency_error_no_number_steps");
	$tpl->set_block("language2", "insert_input_tendency_error_no_text_tendency");
	$tpl->set_block("language2", "insert_input_tendency_button_insert");
	$tpl->set_block("language2", "insert_input_tendency_button_sort");
	$tpl->set_block("language2", "insert_input_tendency_button_save");
	$tpl->set_block("language2", "insert_input_tendency_button_cancel");
	$tpl->set_block("language2", "insert_input_tendency_script_confirm_1");
	$tpl->set_block("language2", "insert_input_tendency_script_confirm_2");
	$tpl->set_block("language2", "insert_input_tendency_label_general");
	$tpl->set_block("language2", "insert_input_tendency_label_description_text");
	$tpl->set_block("language2", "insert_input_tendency_label_tendency_steps");
	$tpl->set_block("language2", "insert_input_tendency_label_steps");
	$tpl->set_block("language2", "insert_input_tendency_label_must");
	$tpl->set_block("language2", "insert_input_tendency_label_output");
	$tpl->set_block("language2", "insert_input_tendency_label_tendency_elements");
	$tpl->set_block("language2", "insert_input_tendency_label_tendency_element");
	$tpl->set_block("language2", "insert_input_tendency_label_tendency_to");
	$tpl->set_block("language2", "insert_input_tendency_label_element");
	$tpl->set_block("language2", "insert_input_tendency_label_action");
	$tpl->set_block("language2", "insert_input_tendency_alt_edit");
	$tpl->set_block("language2", "insert_input_tendency_alt_delete");
	$tpl->set_block("language2", "insert_input_tendency_label_no_option");
	
    $tpl->parse("TITLE", "insert_input_tendency_title");
	$tpl->parse("LANGUAGE_ERROR_NO_TEXT", "insert_input_tendency_error_no_text");
	$tpl->parse("LANGUAGE_ERROR_NO_OPTIONS", "insert_input_tendency_error_no_options");
	$tpl->parse("LANGUAGE_ERROR_NO_NUMBER_STEPS", "insert_input_tendency_error_no_number_steps");
	$tpl->parse("LANGUAGE_ERROR_NO_TEXT_TENDENCY", "insert_input_tendency_error_no_text_tendency");
	$tpl->parse("LANGUAGE_BUTTON_INSERT", "insert_input_tendency_button_insert");
    $tpl->parse("LANGUAGE_BUTTON_SORT", "insert_input_tendency_button_sort");
    $tpl->parse("LANGUAGE_BUTTON_SAVE", "insert_input_tendency_button_save");
    $tpl->parse("LANGUAGE_BUTTON_CANCEL", "insert_input_tendency_button_cancel");
	$tpl->parse("LANGUAGE_SCRIPT_CONFIRM_1", "insert_input_tendency_script_confirm_1");
    $tpl->parse("LANGUAGE_SCRIPT_CONFIRM_2", "insert_input_tendency_script_confirm_2");
	$tpl->parse("LANGUAGE_LABEL_GENERAL", "insert_input_tendency_label_general");
	$tpl->parse("LANGUAGE_LABEL_DESCRIPTION_TEXT", "insert_input_tendency_label_description_text");
	$tpl->parse("LANGUAGE_LABEL_TENDENCY_STEPS", "insert_input_tendency_label_tendency_steps");
	$tpl->parse("LANGUAGE_LABEL_STEPS", "insert_input_tendency_label_steps");
	$tpl->parse("LANGUAGE_LABEL_MUST", "insert_input_tendency_label_must");
    $tpl->parse("LANGUAGE_LABEL_OUTPUT", "insert_input_tendency_label_output");  
	$tpl->parse("LANGUAGE_LABEL_TENDENCY_ELEMENTS", "insert_input_tendency_label_tendency_elements");
	$tpl->parse("LANGUAGE_LABEL_TENDENCY_ELEMENT", "insert_input_tendency_label_tendency_element");
	$tpl->parse("LANGUAGE_LABEL_TENDENCY_TO", "insert_input_tendency_label_tendency_to");
	$tpl->parse("LANGUAGE_LABEL_ELEMENT", "insert_input_tendency_label_element");
	$tpl->parse("LANGUAGE_LABEL_ACTION", "insert_input_tendency_label_action");
	$tpl->parse("LANGUAGE_ALT_EDIT", "insert_input_tendency_alt_edit");
	$tpl->parse("LANGUAGE_ALT_DELETE", "insert_input_tendency_alt_delete");
	$tpl->parse("LANGUAGE_LABEL_NO_OPTION", "insert_input_tendency_label_no_option");
    
	
    $tpl->parse("CONTENT", $current_file);
    $tpl->parse("OUT", "blueprint");
    $out = $tpl->get_var("OUT");

    $tpl->unset_var("BUTTON_LABEL");

    $fp = fopen("$tmp_doc_root/templates/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);

    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $tmp_doc_root/templates/$language/$current_file.ihtml)<br>");
	
	
	//*******************************************************************
    //* insert_input_tendency_sort.ihtml
    //*******************************************************************

    $current_file = "insert_input_tendency_sort";

    $tpl->set_file($current_file, "$current_file.ihtml");
    $tpl->set_block("language2", "insert_input_tendency_sort_title");
    $tpl->set_block("language2", "insert_input_tendency_sort_button_save");
    $tpl->set_block("language2", "insert_input_tendency_sort_button_cancel");
    $tpl->parse("TITLE", "insert_input_tendency_sort_title");
    $tpl->parse("BUTTON_LABEL", "insert_input_tendency_sort_button_save");
    $tpl->parse("LANGUAGE_BUTTON_CANCEL", "insert_input_tendency_sort_button_cancel");

    $tpl->parse("CONTENT", $current_file);
    $tpl->parse("OUT", "blueprint");
    $out = $tpl->get_var("OUT");

    $tpl->unset_var("BUTTON_LABEL");

    $fp = fopen("$tmp_doc_root/templates/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);

    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $tmp_doc_root/templates/$language/$current_file.ihtml)<br>");
  }
?>