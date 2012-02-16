<?php

  /****************************************************************************
  admin_language.php - build language dependent templates for the appointment portlet
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
  $appointment_doc_root = "$config_doc_root/modules/portal2/portlets/appointment";

  //get list of all language directories
  $languages = array();
  $handle = opendir ('.');
  while (false !== ($file = readdir ($handle)))
  {
    if(is_dir($file) && $file != "." && $file != ".." && $file != "CVS" && $file != ".svn")
      array_push($languages, $file);
  }
  closedir($handle);


  echo("<br><b>Portlet: appointment</b>");

  //Parse all templates
  foreach($languages as $language)
  {
    //check whether language dir existents
    @mkdir("$appointment_doc_root/templates/$language");


    //get language file
    $tpl = new Template("$appointment_doc_root/language/$language", "keep");
    $tpl->set_file("language", "language.ihtml");

    //get blueprint for dialog
    $tpl->set_root("$portal_doc_root/language");
    $tpl->set_file("blueprint", "dialog_blueprint.ihtml");
    $tpl->set_var("DOC_ROOT", $config_webserver_ip);

    //set template root dir back to the general design
    $tpl->set_root("$appointment_doc_root/language");

    echo("<br><br>Sprache: $language<br>");


    //*******************************************************************
    //* appointment_edit.ihtml
    //*******************************************************************

    $current_file = "appointment_edit";

    $tpl->set_file($current_file, "$current_file.ihtml");
    $tpl->set_block("language", "appointment_edit_title");
    $tpl->set_block("language", "appointment_edit_feedback_headline_null");
    $tpl->set_block("language", "appointment_edit_feedback_startdate_wrong");
    $tpl->set_block("language", "appointment_edit_feedback_enddate_wrong");
    $tpl->set_block("language", "appointment_edit_feedback_time_wrong");
    $tpl->set_block("language", "appointment_edit_topic");
    $tpl->set_block("language", "appointment_edit_location");
    $tpl->set_block("language", "appointment_edit_startdate");
    $tpl->set_block("language", "appointment_edit_enddate");
    $tpl->set_block("language", "appointment_edit_time");
    $tpl->set_block("language", "appointment_edit_time_2");
    $tpl->set_block("language", "appointment_edit_description");
    $tpl->set_block("language", "appointment_edit_linkurl");
    $tpl->set_block("language", "appointment_edit_ubb");
    $tpl->set_block("language", "appointment_edit_button_ok");
    $tpl->set_block("language", "appointment_edit_button_cancel");
    $tpl->set_var(array(
      "DOC_ROOT" => $config_webserver_ip
    ));
    $tpl->parse("TITLE", "appointment_edit_title");
    $tpl->parse("LANGUAGE_FEEDBACK_HEADLINE_NULL", "appointment_edit_feedback_headline_null");
    $tpl->parse("LANGUAGE_FEEDBACK_STARTDATE_WRONG", "appointment_edit_feedback_startdate_wrong");
    $tpl->parse("LANGUAGE_FEEDBACK_ENDDATE_WRONG", "appointment_edit_feedback_enddate_wrong");
    $tpl->parse("LANGUAGE_FEEDBACK_TIME_WRONG", "appointment_edit_feedback_time_wrong");
    $tpl->parse("LANGUAGE_TOPIC", "appointment_edit_topic");
    $tpl->parse("LANGUAGE_LOCATION", "appointment_edit_location");
    $tpl->parse("LANGUAGE_STARTDATE", "appointment_edit_startdate");
    $tpl->parse("LANGUAGE_ENDDATE", "appointment_edit_enddate");
    $tpl->parse("LANGUAGE_TIME", "appointment_edit_time");
    $tpl->parse("LANGUAGE_TIME_2", "appointment_edit_time_2");
    $tpl->parse("LANGUAGE_DESCRIPTION", "appointment_edit_description");
    $tpl->parse("LANGUAGE_LINKURL", "appointment_edit_linkurl");
    $tpl->parse("LANGUAGE_UBB", "appointment_edit_ubb");
    $tpl->parse("BUTTON_LABEL", "appointment_edit_button_ok");
    $tpl->parse("LANGUAGE_BUTTON_CANCEL", "appointment_edit_button_cancel");

    $tpl->parse("CONTENT", $current_file);

    $tpl->parse("OUT", "blueprint");
    $out = $tpl->get_var("OUT");

    $tpl->unset_var("BUTTON_LABEL");

    $fp = fopen("$appointment_doc_root/templates/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);
    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $appointment_doc_root/templates/$language/$current_file.ihtml)<br>");



    //*******************************************************************
    //* edit.ihtml
    //*******************************************************************

    $current_file = "edit";

    $tpl->set_file($current_file, "$current_file.ihtml");
    $tpl->set_block("language", "edit_title");
    $tpl->set_block("language", "edit_script_appointment_confirm_1");
    $tpl->set_block("language", "edit_script_appointment_confirm_2");
    $tpl->set_block("language", "edit_no_appointment");
    $tpl->set_block("language", "edit_alt_edit_appointment");
    $tpl->set_block("language", "edit_alt_delete_appointment");
    $tpl->set_block("language", "edit_button_new");
    $tpl->set_block("language", "edit_button_sort");
    $tpl->set_block("language", "edit_button_cancel");
    $tpl->set_block("language", "edit_app_earliest_first");
    $tpl->set_block("language", "edit_app_latest_first");
    $tpl->set_var(array(
      "DOC_ROOT" => $config_webserver_ip
    ));
    $tpl->parse("TITLE", "edit_title");
    $tpl->parse("LANGUAGE_SCRIPT_APPOINTMENT_CONFIRM_1", "edit_script_appointment_confirm_1");
    $tpl->parse("LANGUAGE_SCRIPT_APPOINTMENT_CONFIRM_2", "edit_script_appointment_confirm_2");
    $tpl->parse("LANGUAGE_NO_APPOINTMENT", "edit_no_appointment");
    $tpl->parse("LANGUAGE_ALT_EDIT_APPOINTMENT", "edit_alt_edit_appointment");
    $tpl->parse("LANGUAGE_ALT_DELETE_APPOINTMENT", "edit_alt_delete_appointment");
    $tpl->parse("LANGUAGE_BUTTON_NEW", "edit_button_new");
    $tpl->parse("LANGUAGE_BUTTON_SORT", "edit_button_sort");
    $tpl->parse("LANGUAGE_BUTTON_CANCEL", "edit_button_cancel");
    $tpl->parse("LANGUAGE_APP_EARLIEST_FIRST", "edit_app_earliest_first");
    $tpl->parse("LANGUAGE_APP_LATEST_FIRST", "edit_app_latest_first");

    $tpl->parse("CONTENT", $current_file);

    $tpl->parse("OUT", "blueprint");
    $out = $tpl->get_var("OUT");

    $fp = fopen("$appointment_doc_root/templates/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);
    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $appointment_doc_root/templates/$language/$current_file.ihtml)<br>");



    //*******************************************************************
    //* view.ihtml
    //*******************************************************************

    $current_file = "view";

    $tpl->set_file($current_file, "$current_file.ihtml");
    $tpl->set_block("language", "view_time");
    $tpl->parse("LANGUAGE_TIME", "view_time");

    $tpl->parse("OUT", $current_file);
    $out = $tpl->get_var("OUT");

    $fp = fopen("$appointment_doc_root/templates/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);

    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $appointment_doc_root/templates/$language/$current_file.ihtml)<br>");


  }

?>
