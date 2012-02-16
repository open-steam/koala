<?php

  /****************************************************************************
  admin_language.php - build the language dependent templates for the headline portlet
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
  $portlet_doc_root = "$config_doc_root/modules/portal2/portlets/poll";

  //get list of all language directories
  $languages = array();
  $handle = opendir ('.');
  while (false !== ($file = readdir ($handle)))
  {
    if(is_dir($file) && $file != "." && $file != ".." && $file != "CVS" && $file != ".svn")
      array_push($languages, $file);
  }
  closedir($handle);


  echo("<br><b>Portlet: headline</b>");

  //Parse all templates
  foreach($languages as $language)
  {
    //check whether language dir existents
    @mkdir("$portal_doc_root/portlets/headline/templates/$language");


    //get language file
    $tpl = new Template("$portlet_doc_root/language/$language", "keep");
    $tpl->set_file("language", "language.ihtml");

    //get blueprint for dialog
    $tpl->set_root("$portal_doc_root/language");
    $tpl->set_file("blueprint", "dialog_blueprint.ihtml");
    $tpl->set_var("DOC_ROOT", $config_webserver_ip);

    //set template root dir back to the general design
    $tpl->set_root("$portlet_doc_root/language");

    echo("<br><br>Sprache: $language<br>");


    //*******************************************************************
    //* edit.ihtml
    //*******************************************************************

    $current_file = "edit";

    $tpl->set_file($current_file, "$current_file.ihtml");
    $tpl->set_block("language", "edit_title");
    $tpl->set_block("language", "edit_feedback_poll_topic_null");
    $tpl->set_block("language", "edit_poll_topic");
    $tpl->set_block("language", "edit_poll_options");
    $tpl->set_block("language", "edit_startdate");
    $tpl->set_block("language", "edit_enddate");
    $tpl->set_block("language", "edit_button_ok");
    $tpl->set_block("language", "edit_button_cancel");
    $tpl->set_var(array(
      "DOC_ROOT" => $config_webserver_ip
    ));
    $tpl->parse("TITLE", "edit_title");
    $tpl->parse("LANGUAGE_FEEDBACK_POLL_TOPIC_NULL", "edit_feedback_poll_topic_null");
    $tpl->parse("LANGUAGE_POLL_TOPIC", "edit_poll_topic");
    $tpl->parse("LANGUAGE_POLL_OPTIONS", "edit_poll_options");
    $tpl->parse("LANGUAGE_STARTDATE", "edit_startdate");
    $tpl->parse("LANGUAGE_ENDDATE", "edit_enddate");
    $tpl->parse("BUTTON_LABEL", "edit_button_ok");
    $tpl->parse("LANGUAGE_BUTTON_CANCEL", "edit_button_cancel");
    $tpl->parse("CONTENT", $current_file);

    $tpl->parse("OUT", "blueprint");
    $out = $tpl->get_var("OUT");

    $tpl->unset_var("BUTTON_LABEL");

    $fp = fopen("$portlet_doc_root/templates/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);

    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $portlet_doc_root/templates/$language/$current_file.ihtml)<br>");


    //*******************************************************************
    //* view.ihtml
    //*******************************************************************

    $current_file = "view";

    $tpl->set_file($current_file, "$current_file.ihtml");

    $tpl->set_block("language", "vote");
    $tpl->parse("LANGUAGE_VOTE", "vote");

    $tpl->parse("OUT", $current_file);
    $out = $tpl->get_var("OUT");

    $fp = fopen("$portlet_doc_root/templates/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);

    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $portlet_doc_root/templates/$language/$current_file.ihtml)<br>");


  }

?>
