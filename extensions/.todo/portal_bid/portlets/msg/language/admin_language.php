<?php

  /****************************************************************************
  admin_language.php - build the language dependent templates for the messages portlet
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
//  require_once("./config/config.php");
//  require_once("$config_doc_root/classes/template.inc");
//  require_once("$config_doc_root/classes/debugHelper.php");

  $portal_doc_root = "$config_doc_root/modules/portal2";
  $messages_doc_root = "$config_doc_root/modules/portal2/portlets/msg";

  //get list of all language directories
  $languages = array();
  $handle = opendir ('.');
  while (false !== ($file = readdir ($handle)))
  {
    if(is_dir($file) && $file != "." && $file != ".." && $file != "CVS" && $file != ".svn")
      array_push($languages, $file);
  }
  closedir($handle);

  echo("<br><b>Portlet: msg</b>");

  //Parse all templates
  foreach($languages as $language)
  {
    //check whether language dir existents
    @mkdir("$messages_doc_root/templates/$language");


    //get language file
    $tpl = new Template("$messages_doc_root/language/$language", "keep");
    $tpl->set_file("language", "language.ihtml");

    //get blueprint for dialog
    $tpl->set_root("$portal_doc_root/language");
    $tpl->set_file("blueprint", "dialog_blueprint.ihtml");
    $tpl->set_var("DOC_ROOT", $config_webserver_ip);

    //set template root dir back to the general design
    $tpl->set_root("$messages_doc_root/language");

    echo("<br><br>Sprache: $language<br>");


    //*******************************************************************
    //* edit.ihtml
    //*******************************************************************

    $current_file = "edit";

    $tpl->set_file($current_file, "$current_file.ihtml");
    $tpl->set_block("language", "edit_title");
    $tpl->set_block("language", "edit_script_confirm_1");
    $tpl->set_block("language", "edit_script_confirm_2");
    $tpl->set_block("language", "edit_no_message");
    $tpl->set_block("language", "edit_alt_delete");
    $tpl->set_block("language", "edit_alt_edit");
    $tpl->set_block("language", "edit_button_new");
    $tpl->set_block("language", "edit_button_sort");
    $tpl->set_block("language", "edit_button_cancel");
    $tpl->set_block("language", "edit_new_msg_location_top");
    $tpl->set_block("language", "edit_new_msg_location_bottom");
    $tpl->set_var(array(
      "DOC_ROOT" => $config_webserver_ip
    ));
    $tpl->parse("TITLE", "edit_title");
    $tpl->parse("LANGUAGE_SCRIPT_CONFIRM_1", "edit_script_confirm_1");
    $tpl->parse("LANGUAGE_SCRIPT_CONFIRM_2", "edit_script_confirm_2");
    $tpl->parse("LANGUAGE_NO_MESSAGE", "edit_no_message");
    $tpl->parse("LANGUAGE_ALT_DELETE", "edit_alt_delete");
    $tpl->parse("LANGUAGE_ALT_EDIT", "edit_alt_edit");
    $tpl->parse("LANGUAGE_BUTTON_NEW", "edit_button_new");
    $tpl->parse("LANGUAGE_BUTTON_SORT", "edit_button_sort");
    $tpl->parse("LANGUAGE_BUTTON_CANCEL", "edit_button_cancel");
    $tpl->parse("LANGUAGE_NEW_MSG_LOCATION_TOP", "edit_new_msg_location_top");
    $tpl->parse("LANGUAGE_NEW_MSG_LOCATION_BOTTOM", "edit_new_msg_location_bottom");

    $tpl->parse("CONTENT", $current_file);

    $tpl->parse("OUT", "blueprint");
    $out = $tpl->get_var("OUT");

    $fp = fopen("$messages_doc_root/templates/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);
    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $messages_doc_root/templates/$language/$current_file.ihtml)<br>");


    //*******************************************************************
    //* edit_process.ihtml
    //*******************************************************************

    $current_file = "edit_process";

    $tpl->set_file($current_file, "$current_file.ihtml");
    $tpl->set_block("language", "edit_process_title");
    $tpl->set_block("language", "edit_process_feedback_headline_null");
    $tpl->set_block("language", "edit_process_feedback_content_null");
    $tpl->set_block("language", "edit_process_text_1");
    $tpl->set_block("language", "edit_process_text_2");
    $tpl->set_block("language", "edit_process_text_3");
    $tpl->set_block("language", "edit_process_text_4");
    $tpl->set_block("language", "edit_process_text_5");
    $tpl->set_block("language", "edit_process_text_6");
    $tpl->set_block("language", "edit_process_text_7");
    $tpl->set_block("language", "edit_process_text_8");
    $tpl->set_block("language", "edit_process_text_9");
    $tpl->set_block("language", "edit_process_text_10");
    $tpl->set_block("language", "edit_process_picture_alignment_none");
    $tpl->set_block("language", "edit_process_picture_width");
    $tpl->set_block("language", "edit_process_button_delete_image");
    $tpl->set_block("language", "edit_process_ubb");
    $tpl->set_block("language", "edit_process_button_ok");
    $tpl->set_block("language", "edit_process_button_cancel");
    $tpl->set_var(array(
      "DOC_ROOT" => $config_webserver_ip,
//      "BUTTON_MISSION" => "save",
      "BUTTON_URL" => "$config_webserver_ip/modules/portal2/portlets/msg/edit_process.php"
    ));
    $tpl->parse("TITLE", "edit_process_title");
    $tpl->parse("LANGUAGE_FEEDBACK_HEADLINE_NULL", "edit_process_feedback_headline_null");
    $tpl->parse("LANGUAGE_FEEDBACK_CONTENT_NULL", "edit_process_feedback_content_null");
    $tpl->parse("LANGUAGE_TEXT_1", "edit_process_text_1");
    $tpl->parse("LANGUAGE_TEXT_2", "edit_process_text_2");
    $tpl->parse("LANGUAGE_TEXT_3", "edit_process_text_3");
    $tpl->parse("LANGUAGE_TEXT_4", "edit_process_text_4");
    $tpl->parse("LANGUAGE_TEXT_5", "edit_process_text_5");
    $tpl->parse("LANGUAGE_TEXT_6", "edit_process_text_6");
    $tpl->parse("LANGUAGE_TEXT_7", "edit_process_text_7");
    $tpl->parse("LANGUAGE_TEXT_8", "edit_process_text_8");
    $tpl->parse("LANGUAGE_TEXT_9", "edit_process_text_9");
    $tpl->parse("LANGUAGE_TEXT_10", "edit_process_text_10");
    $tpl->parse("LANGUAGE_PICTURE_WIDTH", "edit_process_picture_width");
    $tpl->parse("LANGUAGE_PICTURE_ALIGNMENT_NONE", "edit_process_picture_alignment_none");
    $tpl->parse("LANGUAGE_BUTTON_DELETE_IMAGE", "edit_process_button_delete_image");
    $tpl->parse("LANGUAGE_UBB", "edit_process_ubb");
    $tpl->parse("BUTTON_LABEL", "edit_process_button_ok");
    $tpl->parse("LANGAUGE_BUTTON_CANCEL", "edit_process_button_CANCEL");
    $tpl->parse("CONTENT", $current_file);

    $tpl->parse("OUT", "blueprint");
    $out = $tpl->get_var("OUT");

    $tpl->unset_var("BUTTON_LABEL");

    $fp = fopen("$messages_doc_root/templates/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);
    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $messages_doc_root/templates/$language/$current_file.ihtml)<br>");


    //*******************************************************************
    //* edit_sort.ihtml
    //*******************************************************************

    $current_file = "edit_sort";

    $tpl->set_file($current_file, "$current_file.ihtml");
    $tpl->set_block("language", "edit_sort_title");
    $tpl->set_block("language", "edit_sort_button_ok");
    $tpl->set_block("language", "edit_sort_button_cancel");
    $tpl->set_var(array(
      "DOC_ROOT" => $config_webserver_ip,
      "BUTTON_MISSION" => "save",
      "BUTTON_URL" => "$config_webserver_ip/modules/portal2/portlets/msg/edit_sort.php"
    ));
    $tpl->parse("TITLE", "edit_sort_title");
    $tpl->parse("BUTTON_LABEL", "edit_sort_button_ok");
    $tpl->parse("LANGUAGE_BUTTON_CANCEL", "edit_sort_button_cancel");
    $tpl->parse("CONTENT", $current_file);

    $tpl->parse("OUT", "blueprint");
    $out = $tpl->get_var("OUT");

    $tpl->unset_var("BUTTON_LABEL");

    $fp = fopen("$messages_doc_root/templates/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);
    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $messages_doc_root/templates/$language/$current_file.ihtml)<br>");


    //*******************************************************************
    //* view.ihtml
    //*******************************************************************

    $current_file = "view";

    $tpl->set_file($current_file, "$current_file.ihtml");
    $tpl->set_block("language", "view_message_none");

    $tpl->parse("LANGUAGE_MESSAGE_NONE", "view_message_none");
    $tpl->parse("OUT", $current_file);
    $out = $tpl->get_var("OUT");

    $fp = fopen("$messages_doc_root/templates/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);

    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $messages_doc_root/templates/$language/$current_file.ihtml)<br>");


  }

?>
