<?php

  /****************************************************************************
  admin_language.php - build the language dependent templates for the topic portlet
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
  $topic_doc_root = "$config_doc_root/modules/portal2/portlets/topic";

  //get list of all language directories
  $languages = array();
  $handle = opendir ('.');
  while (false !== ($file = readdir ($handle)))
  {
    if(is_dir($file) && $file != "." && $file != ".." && $file != "CVS" && $file != ".svn")
      array_push($languages, $file);
  }
  closedir($handle);


  echo("<br><b>Portlet: topic</b>");

  //Parse all templates
  foreach($languages as $language)
  {
    //check whether language dir existents
    @mkdir("$topic_doc_root/templates/$language");


    //get language file
    $tpl = new Template("$topic_doc_root/language/$language", "keep");
    $tpl->set_file("language", "language.ihtml");

    //get blueprint for dialog
    $tpl->set_root("$portal_doc_root/language");
    $tpl->set_file("blueprint", "dialog_blueprint.ihtml");
    $tpl->set_var("DOC_ROOT", $config_webserver_ip);

    //set template root dir back to the general design
    $tpl->set_root("$topic_doc_root/language");

    echo("<br><br>Sprache: $language<br>");


    //*******************************************************************
    //* category_edit.ihtml
    //*******************************************************************

    $current_file = "category_edit";

    $tpl->set_file($current_file, "$current_file.ihtml");
    $tpl->set_block("language", "category_edit_feedback_headline_null");
    $tpl->set_block("language", "category_edit_title");
    $tpl->set_block("language", "category_edit_description");
    $tpl->set_block("language", "category_edit_ubb");
    $tpl->set_block("language", "category_edit_button_ok");
    $tpl->set_block("language", "category_edit_button_cancel");
    $tpl->set_var(array(
      "DOC_ROOT" => $config_webserver_ip
    ));
    $tpl->parse("TITLE", "category_edit_title");
    $tpl->parse("LANGUAGE_FEEDBACK_HEADLINE_NULL", "category_edit_feedback_headline_null");
    $tpl->parse("LANGUAGE_DESCRIPTION", "category_edit_description");
    $tpl->parse("LANGUAGE_UBB", "category_edit_ubb");
    $tpl->parse("BUTTON_LABEL", "category_edit_button_ok");
    $tpl->parse("LANGUAGE_BUTTON_CANCEL", "category_edit_button_cancel");

    $tpl->parse("CONTENT", $current_file);

    $tpl->parse("OUT", "blueprint");
    $out = $tpl->get_var("OUT");

    $tpl->unset_var("BUTTON_LABEL");

    $fp = fopen("$topic_doc_root/templates/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);
    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $topic_doc_root/templates/$language/$current_file.ihtml)<br>");


    //*******************************************************************
    //* category_sort.ihtml
    //*******************************************************************

    $current_file = "category_sort";

    $tpl->set_file($current_file, "$current_file.ihtml");
    $tpl->set_block("language", "category_sort_title");
    $tpl->set_block("language", "category_sort_button_ok");
    $tpl->set_block("language", "category_sort_button_cancel");
    $tpl->set_var(array(
      "DOC_ROOT" => $config_webserver_ip
    ));
    $tpl->parse("TITLE", "category_sort_title");
    $tpl->parse("BUTTON_LABEL", "category_sort_button_ok");
    $tpl->parse("LANGUAGE_BUTTON_CANCEL", "category_sort_button_cancel");
    $tpl->parse("CONTENT", $current_file);

    $tpl->parse("OUT", "blueprint");
    $out = $tpl->get_var("OUT");

    $tpl->unset_var("BUTTON_LABEL");

    $fp = fopen("$topic_doc_root/templates/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);
    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $topic_doc_root/templates/$language/$current_file.ihtml)<br>");


    //*******************************************************************
    //* edit.ihtml
    //*******************************************************************

    $current_file = "edit";

    $tpl->set_file($current_file, "$current_file.ihtml");
    $tpl->set_block("language", "edit_title");
    $tpl->set_block("language", "edit_script_category_confirm_1");
    $tpl->set_block("language", "edit_script_category_confirm_2");
    $tpl->set_block("language", "edit_script_topic_confirm_1");
    $tpl->set_block("language", "edit_script_topic_confirm_2");
    $tpl->set_block("language", "edit_no_category");
    $tpl->set_block("language", "edit_no_topic");
    $tpl->set_block("language", "edit_alt_edit_category");
    $tpl->set_block("language", "edit_alt_delete_category");
    $tpl->set_block("language", "edit_alt_new_topic");
    $tpl->set_block("language", "edit_alt_edit_topic");
    $tpl->set_block("language", "edit_alt_sort_topic");
    $tpl->set_block("language", "edit_alt_delete_topic");
    $tpl->set_block("language", "edit_topic_new");
    $tpl->set_block("language", "edit_topic_sort");
    $tpl->set_block("language", "edit_button_new");
    $tpl->set_block("language", "edit_button_sort");
    $tpl->set_block("language", "edit_button_cancel");
    $tpl->set_var(array(
      "DOC_ROOT" => $config_webserver_ip
    ));
    $tpl->parse("TITLE", "edit_title");
    $tpl->parse("LANGUAGE_SCRIPT_CATEGORY_CONFIRM_1", "edit_script_category_confirm_1");
    $tpl->parse("LANGUAGE_SCRIPT_CATEGORY_CONFIRM_2", "edit_script_category_confirm_2");
    $tpl->parse("LANGUAGE_SCRIPT_TOPIC_CONFIRM_1", "edit_script_topic_confirm_1");
    $tpl->parse("LANGUAGE_SCRIPT_TOPIC_CONFIRM_2", "edit_script_topic_confirm_2");
    $tpl->parse("LANGUAGE_NO_CATEGORY", "edit_no_category");
    $tpl->parse("LANGUAGE_NO_TOPIC", "edit_no_topic");
    $tpl->parse("LANGUAGE_ALT_EDIT_CATEGORY", "edit_alt_edit_category");
    $tpl->parse("LANGUAGE_ALT_DELETE_CATEGORY", "edit_alt_delete_category");
    $tpl->parse("LANGUAGE_ALT_NEW_TOPIC", "edit_alt_new_topic");
    $tpl->parse("LANGUAGE_ALT_EDIT_TOPIC", "edit_alt_edit_topic");
    $tpl->parse("LANGUAGE_ALT_SORT_TOPIC", "edit_alt_sort_topic");
    $tpl->parse("LANGUAGE_ALT_DELETE_TOPIC", "edit_alt_delete_topic");
    $tpl->parse("LANGUAGE_TOPIC_NEW", "edit_topic_new");
    $tpl->parse("LANGUAGE_TOPIC_SORT", "edit_topic_sort");
    $tpl->parse("LANGUAGE_BUTTON_NEW", "edit_button_new");
    $tpl->parse("LANGUAGE_BUTTON_SORT", "edit_button_sort");
    $tpl->parse("LANGUAGE_BUTTON_CANCEL", "edit_button_cancel");

    $tpl->parse("CONTENT", $current_file);

    $tpl->parse("OUT", "blueprint");
    $out = $tpl->get_var("OUT");

    $fp = fopen("$topic_doc_root/templates/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);
    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $topic_doc_root/templates/$language/$current_file.ihtml)<br>");


    //*******************************************************************
    //* topic_edit.ihtml
    //*******************************************************************

    $current_file = "topic_edit";

    $tpl->set_file($current_file, "$current_file.ihtml");
    $tpl->set_block("language", "topic_edit_title");
    $tpl->set_block("language", "topic_edit_feedback_headline_null");
    $tpl->set_block("language", "topic_edit_form_title");
    $tpl->set_block("language", "topic_edit_form_description");
    $tpl->set_block("language", "topic_edit_form_link_url");
    $tpl->set_block("language", "topic_edit_form_link_target");
    $tpl->set_block("language", "topic_edit_ubb");
    $tpl->set_block("language", "topic_edit_button_ok");
    $tpl->set_block("language", "topic_edit_button_cancel");
    $tpl->set_var(array(
      "DOC_ROOT" => $config_webserver_ip
    ));
    $tpl->parse("TITLE", "topic_edit_title");
    $tpl->parse("LANGUAGE_FEEDBACK_HEADLINE_NULL", "topic_edit_feedback_headline_null");
    $tpl->parse("LANGUAGE_FORM_TITLE", "topic_edit_form_title");
    $tpl->parse("LANGUAGE_FORM_DESCRIPTION", "topic_edit_form_description");
    $tpl->parse("LANGUAGE_FORM_LINK_URL", "topic_edit_form_link_url");
    $tpl->parse("LANGUAGE_FORM_LINK_TARGET", "topic_edit_form_link_target");
    $tpl->parse("LANGUAGE_UBB", "topic_edit_ubb");
    $tpl->parse("BUTTON_LABEL", "topic_edit_button_ok");
    $tpl->parse("LANGUAGE_BUTTON_CANCEL", "topic_edit_button_cancel");

    $tpl->parse("CONTENT", $current_file);

    $tpl->parse("OUT", "blueprint");
    $out = $tpl->get_var("OUT");

    $tpl->unset_var("BUTTON_LABEL");

    $fp = fopen("$topic_doc_root/templates/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);
    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $topic_doc_root/templates/$language/$current_file.ihtml)<br>");


    //*******************************************************************
    //* topic_sort.ihtml
    //*******************************************************************

    $current_file = "topic_sort";

    $tpl->set_file($current_file, "$current_file.ihtml");
    $tpl->set_block("language", "topic_sort_title");
    $tpl->set_block("language", "topic_sort_button_ok");
    $tpl->set_block("language", "topic_sort_button_cancel");
    $tpl->set_var(array(
      "DOC_ROOT" => $config_webserver_ip
    ));
    $tpl->parse("TITLE", "topic_sort_title");
    $tpl->parse("BUTTON_LABEL", "topic_sort_button_ok");
    $tpl->parse("LANGUAGE_BUTTON_CANCEL", "topic_sort_button_cancel");
    $tpl->parse("CONTENT", $current_file);

    $tpl->parse("OUT", "blueprint");
    $out = $tpl->get_var("OUT");

    $tpl->unset_var("BUTTON_LABEL");

    $fp = fopen("$topic_doc_root/templates/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);
    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $topic_doc_root/templates/$language/$current_file.ihtml)<br>");



    //*******************************************************************
    //* view.ihtml
    //*******************************************************************

    $current_file = "view";

    $tpl->set_file($current_file, "$current_file.ihtml");

    $tpl->parse("OUT", $current_file);
    $out = $tpl->get_var("OUT");

    $fp = fopen("$topic_doc_root/templates/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);

    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $topic_doc_root/templates/$language/$current_file.ihtml)<br>");


  }

?>
