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
//  require_once("./config/config.php");
//  require_once("$config_doc_root/classes/template.inc");
//  require_once("$config_doc_root/classes/debugHelper.php");

  $tmp_doc_root = "$config_doc_root/modules/portal2";


  //get list of all language directories
  $languages = array();
  $handle = opendir ('.');
  while (false !== ($file = readdir ($handle)))
  {
    if(is_dir($file) && $file != "." && $file != ".." && $file != "CVS" && $file != ".svn")
      array_push($languages, $file);
  }
  closedir($handle);


  echo("<br><br><hr><b>MODULE: Portal 2</b><hr>");

  //Parse all templates
  foreach($languages as $language)
  {
    //check whether language dir existents
    @mkdir("$tmp_doc_root/templates/$language");


    //get language file
    $tpl = new Template("$tmp_doc_root/language/$language", "keep");
    $tpl->set_file("language", "language.ihtml");

    //get blueprint for dialog
    $tpl->set_root("$tmp_doc_root/language");
    $tpl->set_file("blueprint", "dialog_blueprint.ihtml");
    $tpl->set_block("blueprint", "buttons", "BUTTONS");
    $tpl->set_var("DOC_ROOT", $config_webserver_ip);

    //set template root dir back to the general design
    $tpl->set_root("$tmp_doc_root/language");

    echo("<br><br>Sprache: $language<br>");


    //*******************************************************************
    //* index.ihtml
    //*******************************************************************

    $current_file = "index";

    $tpl->set_file($current_file, "$current_file.ihtml");
    $tpl->set_block("language", "index_column_action_edit");
    $tpl->set_block("language", "index_column_empty");

    $tpl->set_var(array(
      "FORM_NAME" => "editColumn",
      "FORM_URL" => "./index.php?object={ENVIRONMENT_ID}",
      "DOC_ROOT" => $config_webserver_ip
    ));
    $tpl->parse("LANGUAGE_COLUMN_ACTION_EDIT", "index_column_action_edit");
    $tpl->parse("LANGUAGE_COLUMN_EMPTY", "index_column_empty");

    
    $tpl->parse("OUT", $current_file);
    $out = $tpl->get_var("OUT");

    $fp = fopen("$tmp_doc_root/templates/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);

    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $tmp_doc_root/templates/$language/$current_file.ihtml)<br>");

    //*******************************************************************
    //* new.ihtml
    //*******************************************************************

    $current_file = "new";

    $tpl->set_file($current_file, "$current_file.ihtml");
    $tpl->set_block("language", "new_title");
    $tpl->set_block("language", "new_form_title");
    $tpl->set_block("language", "new_form_portal_columns");
    $tpl->set_block("language", "new_form_3_col");
    $tpl->set_block("language", "new_form_2_col");
    $tpl->set_block("language", "new_form_1_col");
    $tpl->set_block("language", "new_error_no_title");
    $tpl->set_block("language", "new_button_ok");
    $tpl->set_block("language", "new_button_cancel");

    $tpl->set_var(array(
      "FORM_NAME" => "insertForm",
      "FORM_URL" => "./new.php?object={ENVIRONMENT_ID}",
      "DOC_ROOT" => $config_webserver_ip
    ));
    $tpl->parse("TITLE", "new_title");
    $tpl->parse("LANGUAGE_FORM_TITLE", "new_form_title");
    $tpl->parse("LANGUAGE_FORM_PORTAL_COLUMNS", "new_form_portal_columns");
    $tpl->parse("LANGUAGE_FORM_3_COL", "new_form_3_col");
    $tpl->parse("LANGUAGE_FORM_2_COL", "new_form_2_col");
    $tpl->parse("LANGUAGE_FORM_1_COL", "new_form_1_col");
    $tpl->parse("LANGUAGE_ERROR_NO_TITLE", "new_error_no_title");
    $tpl->parse("LANGUAGE_BUTTON_NEW", "new_button_ok");
    $tpl->parse("LANGUAGE_BUTTON_CANCEL", "new_button_cancel");
    $tpl->parse("BUTTONS", "buttons");
    $tpl->parse("CONTENT", $current_file);

    $tpl->parse("OUT", "blueprint");
    $out = $tpl->get_var("OUT");

    $fp = fopen("$tmp_doc_root/templates/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);

    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $tmp_doc_root/templates/$language/$current_file.ihtml)<br>");

    //*******************************************************************
    //* edit_column.ihtml
    //*******************************************************************

    $current_file = "edit_column";

    $tpl->set_file($current_file, "$current_file.ihtml");
    $tpl->set_block("language", "edit_column_title");
    $tpl->set_block("language", "edit_column_form_title");
    $tpl->set_block("language", "edit_column_form_portal_columns");
    $tpl->set_block("language", "edit_column_error_no_title");
    $tpl->set_block("language", "edit_column_error_column_width");
    $tpl->set_block("language", "edit_column_error_no_access");
    $tpl->set_block("language", "edit_column_sort_portlets_title");
    $tpl->set_block("language", "edit_column_sort_portlets_up");
    $tpl->set_block("language", "edit_column_sort_portlets_down");
    $tpl->set_block("language", "edit_column_add_portlet_title");
    $tpl->set_block("language", "edit_column_add_portlet_button");
    $tpl->set_block("language", "edit_column_edit_column_width_title");
    $tpl->set_block("language", "edit_column_properties_button");
    $tpl->set_block("language", "edit_column_copy_portlet_button");
    $tpl->set_block("language", "edit_column_cut_portlet_button");
    $tpl->set_block("language", "edit_column_link_portlet_button");
    $tpl->set_block("language", "edit_column_remove_portlet_button");
    $tpl->set_block("language", "edit_column_paste_portlet_button");
    $tpl->set_block("language", "edit_column_button_ok");
    $tpl->set_block("language", "edit_column_button_cancel");

    $tpl->set_var(array(
      "FORM_NAME" => "editColumn",
      "FORM_URL" => "./edit_column.php?object={PORTAL_COLUMN_ID}",
      "DOC_ROOT" => $config_webserver_ip
    ));
    $tpl->parse("TITLE", "edit_column_title");
    $tpl->parse("LANGUAGE_FORM_TITLE", "edit_column_form_title");
    $tpl->parse("LANGUAGE_ERROR_NO_TITLE", "edit_column_error_no_title");
    $tpl->parse("LANGUAGE_ERROR_COLUMN_WIDTH", "edit_column_error_column_width");
    $tpl->parse("LANGUAGE_ERROR_NO_ACCESS", "edit_column_error_no_access");
    $tpl->parse("LANGUAGE_SORT_PORTLETS_TITLE", "edit_column_sort_portlets_title");
    $tpl->parse("LANGUAGE_SORT_PORTLETS_UP", "edit_column_sort_portlets_up");
    $tpl->parse("LANGUAGE_SORT_PORTLETS_DOWN", "edit_column_sort_portlets_down");
    $tpl->parse("LANGUAGE_ADD_PORTLET_TITLE", "edit_column_add_portlet_title");
    $tpl->parse("LANGUAGE_ADD_PORTLET_BUTTON", "edit_column_add_portlet_button");
    $tpl->parse("LANGUAGE_EDIT_COLUMN_WIDTH_TITLE", "edit_column_edit_column_width_title");
    $tpl->parse("LANGUAGE_PROPERTIES_BUTTON", "edit_column_properties_button");
    $tpl->parse("LANGUAGE_COPY_PORTLET_BUTTON", "edit_column_copy_portlet_button");
    $tpl->parse("LANGUAGE_CUT_PORTLET_BUTTON", "edit_column_cut_portlet_button");
    $tpl->parse("LANGUAGE_LINK_PORTLET_BUTTON", "edit_column_link_portlet_button");
    $tpl->parse("LANGUAGE_REMOVE_PORTLET_BUTTON", "edit_column_remove_portlet_button");
    $tpl->parse("LANGUAGE_PASTE_PORTLET_BUTTON", "edit_column_paste_portlet_button");
    $tpl->parse("LANGUAGE_BUTTON_OK", "edit_column_button_ok");
    $tpl->parse("LANGUAGE_BUTTON_CANCEL", "edit_column_button_cancel");
#$tpl->parse("BUTTONS", "buttons");
    $tpl->parse("CONTENT", $current_file);

    $tpl->parse("OUT", "blueprint");
    $out = $tpl->get_var("OUT");

    $fp = fopen("$tmp_doc_root/templates/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);

    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $tmp_doc_root/templates/$language/$current_file.ihtml)<br>");    
    
    //*******************************************************************
    //* portlet_insert.ihtml
    //*******************************************************************

    $current_file = "portlet_insert";

    $tpl->set_file($current_file, "$current_file.ihtml");
    $tpl->set_block("language", "portlet_insert_title");
    $tpl->set_block("language", "portlet_insert_form_title");
    $tpl->set_block("language", "portlet_insert_portlet_type");
    $tpl->set_block("language", "portlet_insert_button_ok");
    $tpl->set_block("language", "portlet_insert_button_cancel");
    $tpl->set_block("language", "portlet_insert_error_title");

    $tpl->set_var(array(
      "FORM_NAME" => "portletInsert",
      "FORM_URL" => "./portlet_insert.php?object={PORTAL_COLUMN_ID}",
      "DOC_ROOT" => $config_webserver_ip
    ));
    $tpl->parse("TITLE", "portlet_insert_title");
    $tpl->parse("LANGUAGE_FORM_TITLE", "portlet_insert_form_title");   
    $tpl->parse("LANGUAGE_FORM_PORTLET_TYPE", "portlet_insert_portlet_type");
    $tpl->parse("LANGUAGE_BUTTON_OK", "portlet_insert_button_ok");
    $tpl->parse("LANGUAGE_BUTTON_CANCEL", "portlet_insert_button_cancel");
    $tpl->parse("LANGUAGE_ERROR_TITLE", "portlet_insert_error_title");
    $tpl->parse("BUTTONS", "buttons");
    $tpl->parse("CONTENT", $current_file);

    $tpl->parse("OUT", "blueprint");
    $out = $tpl->get_var("OUT");

    $fp = fopen("$tmp_doc_root/templates/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);

    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $tmp_doc_root/templates/$language/$current_file.ihtml)<br>");

  }

  //Portlets
  include("$tmp_doc_root/portlets/appointment/language/admin_language.php");
  include("$tmp_doc_root/portlets/headline/language/admin_language.php");
  include("$tmp_doc_root/portlets/media/language/admin_language.php");
  include("$tmp_doc_root/portlets/msg/language/admin_language.php");
  include("$tmp_doc_root/portlets/poll/language/admin_language.php");
  include("$tmp_doc_root/portlets/rss/language/admin_language.php");
  include("$tmp_doc_root/portlets/topic/language/admin_language.php");
?>
