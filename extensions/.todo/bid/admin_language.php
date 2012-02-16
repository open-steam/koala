<?php

  /****************************************************************************
  admin_language.php - build the language dependent template sets
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

  Author: Henrik Beige <hebeige@gmx.de>
        Bastian Schröder <bastian@upb.de>

  ****************************************************************************/

  $templateoutputdir = "../templates";

  require_once("../config/config.php");
  require_once("$config_doc_root/classes/template.inc");
//  require_once("$config_doc_root/classes/debugHelper.php");


  //get list of all language directories
  $languages = array();
  $handle = opendir ('.');
  while (false !== ($file = readdir ($handle)))
  {
    if(is_dir($file) && $file != "." && $file != ".." && $file != "CVS" && $file != ".svn")
      array_push($languages, $file);
  }
  closedir($handle);


  echo("<h1>Main Templates</h1>");


  //Parse all templates
  foreach($languages as $language)
  {
    //check whether language dir existents
    @mkdir("$templateoutputdir/$language");


    //get language file
    $tpl = new Template("./$language", "keep");
    $tpl->set_file("language", "language.ihtml");

    //set template root dir back to the general design
    $tpl->set_root(".");

    //get dialog blueprint
    $tpl->set_file("blueprint", "dialog_blueprint.ihtml");
    $tpl->set_block("blueprint", "buttons", "BUTTONS");

    echo("<h3>Sprache: $language</h3>");


    //*******************************************************************
    //* cluster.ihtml
    //*******************************************************************

    $current_file = "cluster";

    $tpl->set_file($current_file, "$current_file.ihtml");

    $tpl->set_var(array(
      "DUMMY" => "",
      "DOC_ROOT" => $config_webserver_ip
    ));

    $tpl->parse("OUT", $current_file);
    $out = $tpl->get_var("OUT");

    $fp = fopen("$templateoutputdir/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);
    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen.<br>");


    //*******************************************************************
    //* contentframe.ihtml
    //*******************************************************************
    $tpl->set_file("contentframe", "contentframe.ihtml");
    $tpl->set_block("language", "contentframe_no_content");
    $tpl->set_block("language", "contentframe_title_1");
    $tpl->set_block("language", "contentframe_title_2");
    $tpl->set_block("language", "contentframe_title_3");
    $tpl->set_block("language", "contentframe_title_4");
    $tpl->set_block("language", "contentframe_title_5");
    $tpl->set_block("language", "contentframe_title_6");
    $tpl->set_block("language", "contentframe_title_7");
    $tpl->set_block("language", "contentframe_title_8");
    $tpl->set_block("language", "contentframe_title_9");
    $tpl->set_block("language", "contentframe_title_10");
    $tpl->set_block("language", "contentframe_title_11");
    $tpl->set_block("language", "contentframe_footer_1");
    $tpl->set_block("language", "contentframe_footer_2");
    $tpl->set_block("language", "contentframe_properties");
    $tpl->set_block("language", "contentframe_properties_title");
    $tpl->set_block("language", "contentframe_hidden");
    $tpl->set_block("language", "contentframe_head_mounted_descr");
    $tpl->set_block("language", "contentframe_head_mounted_document");
    $tpl->set_block("language", "contentframe_broken_link");

    $tpl->parse("LANGUAGE_NO_CONTENT", "contentframe_no_content");
    $tpl->parse("LANGUAGE_TITLE_1", "contentframe_title_1");
    $tpl->parse("LANGUAGE_TITLE_2", "contentframe_title_2");
    $tpl->parse("LANGUAGE_TITLE_3", "contentframe_title_3");
    $tpl->parse("LANGUAGE_TITLE_4", "contentframe_title_4");
    $tpl->parse("LANGUAGE_TITLE_5", "contentframe_title_5");
    $tpl->parse("LANGUAGE_TITLE_6", "contentframe_title_6");
    $tpl->parse("LANGUAGE_TITLE_7", "contentframe_title_7");
    $tpl->parse("LANGUAGE_TITLE_8", "contentframe_title_8");
    $tpl->parse("LANGUAGE_TITLE_9", "contentframe_title_9");
    $tpl->parse("LANGUAGE_TITLE_10", "contentframe_title_10");
    $tpl->parse("LANGUAGE_TITLE_11", "contentframe_title_11");
    $tpl->parse("LANGUAGE_FOOTER_1", "contentframe_footer_1");
    $tpl->parse("LANGUAGE_FOOTER_2", "contentframe_footer_2");
    $tpl->parse("LANGUAGE_PROPERTIES", "contentframe_properties");
    $tpl->parse("LANGUAGE_PROPERTIES_TITLE", "contentframe_properties_title");
    $tpl->parse("LANGUAGE_HIDDEN", "contentframe_hidden");
    $tpl->parse("LANGUAGE_HEAD_MOUNTED_DESCR", "contentframe_head_mounted_descr");
    $tpl->parse("LANGUAGE_HEAD_MOUNTED_DOCUMENT", "contentframe_head_mounted_document");
    $tpl->parse("LANGUAGE_BROKEN_LINK", "contentframe_broken_link");

    $tpl->parse("OUT", "contentframe");
    $out = $tpl->get_var("OUT");

    $fp = fopen("$templateoutputdir/$language/contentframe.ihtml", "w");
    fwrite($fp, $out);

    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; contentframe.ihtml abgeschlossen.<br>");

    //*******************************************************************
    //* taggedFolder.ihtml
    //*******************************************************************
    $tpl->set_file("taggedFolder", "taggedFolder.ihtml");
    $tpl->set_block("language", "taggedFolder_no_content");
    $tpl->set_block("language", "taggedFolder_title_1");
    $tpl->set_block("language", "taggedFolder_title_2");
    $tpl->set_block("language", "taggedFolder_title_3");
    $tpl->set_block("language", "taggedFolder_title_4");
    $tpl->set_block("language", "taggedFolder_title_5");
    $tpl->set_block("language", "taggedFolder_title_6");
    $tpl->set_block("language", "taggedFolder_title_7");
    $tpl->set_block("language", "taggedFolder_title_8");
    $tpl->set_block("language", "taggedFolder_title_9");
    $tpl->set_block("language", "taggedFolder_title_10");
    $tpl->set_block("language", "taggedFolder_title_11");
    $tpl->set_block("language", "taggedFolder_footer_1");
    $tpl->set_block("language", "taggedFolder_footer_2");
    $tpl->set_block("language", "taggedFolder_properties");
    $tpl->set_block("language", "taggedFolder_properties_title");
    $tpl->set_block("language", "taggedFolder_hidden");
    $tpl->set_block("language", "taggedFolder_head_mounted_descr");
    $tpl->set_block("language", "taggedFolder_head_mounted_document");
    $tpl->set_block("language", "taggedFolder_broken_link");

    $tpl->parse("LANGUAGE_NO_CONTENT", "taggedFolder_no_content");
    $tpl->parse("LANGUAGE_TITLE_1", "taggedFolder_title_1");
    $tpl->parse("LANGUAGE_TITLE_2", "taggedFolder_title_2");
    $tpl->parse("LANGUAGE_TITLE_3", "taggedFolder_title_3");
    $tpl->parse("LANGUAGE_TITLE_4", "taggedFolder_title_4");
    $tpl->parse("LANGUAGE_TITLE_5", "taggedFolder_title_5");
    $tpl->parse("LANGUAGE_TITLE_6", "taggedFolder_title_6");
    $tpl->parse("LANGUAGE_TITLE_7", "taggedFolder_title_7");
    $tpl->parse("LANGUAGE_TITLE_8", "taggedFolder_title_8");
    $tpl->parse("LANGUAGE_TITLE_9", "taggedFolder_title_9");
    $tpl->parse("LANGUAGE_TITLE_10", "taggedFolder_title_10");
    $tpl->parse("LANGUAGE_TITLE_11", "taggedFolder_title_11");
    $tpl->parse("LANGUAGE_FOOTER_1", "taggedFolder_footer_1");
    $tpl->parse("LANGUAGE_FOOTER_2", "taggedFolder_footer_2");
    $tpl->parse("LANGUAGE_PROPERTIES", "taggedFolder_properties");
    $tpl->parse("LANGUAGE_PROPERTIES_TITLE", "taggedFolder_properties_title");
    $tpl->parse("LANGUAGE_HIDDEN", "taggedFolder_hidden");
    $tpl->parse("LANGUAGE_HEAD_MOUNTED_DESCR", "taggedFolder_head_mounted_descr");
    $tpl->parse("LANGUAGE_HEAD_MOUNTED_DOCUMENT", "taggedFolder_head_mounted_document");
    $tpl->parse("LANGUAGE_BROKEN_LINK", "taggedFolder_broken_link");

    $tpl->parse("OUT", "taggedFolder");
    $out = $tpl->get_var("OUT");

    $fp = fopen("$templateoutputdir/$language/taggedFolder.ihtml", "w");
    fwrite($fp, $out);

    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; taggedFolder.ihtml abgeschlossen.<br>");

    //*******************************************************************
    //* delicious_import.ihtml
    //*******************************************************************

    $current_file = "delicious_import";

    $tpl->set_file($current_file, "$current_file.ihtml");

    $tpl->set_block("language", "delicious_title");
    $tpl->set_block("language", "delicious_user");
    $tpl->set_block("language", "delicious_passwd");
    $tpl->set_block("language", "delicious_tag");
    $tpl->set_block("language", "delicious_loginbutton");
    $tpl->set_block("language", "delicious_importtitle");
    $tpl->set_block("language", "delicious_importbutton");
    $tpl->set_block("language", "delicious_nextbutton");
  $tpl->set_block("language", "delicious_tagtitle");

    $tpl->set_var(array(
      "FORM_NAME" => "deliciousForm",
      "FORM_URL" => "./delicious_import.php?object={ENVIRONMENT_ID}",
      "DOC_ROOT" => $config_webserver_ip,
      "CGI_ROOT" => $config_cgi_ip,
      "BODY_ON_LOAD" => "if (document.deliciousForm.title) document.deliciousForm.title.focus();"
    ));

    $tpl->parse("LANGUAGE_DELICIOUS_TITLE", "delicious_title");
    $tpl->parse("LANGUAGE_DELICIOUS_USER", "delicious_user");
    $tpl->parse("LANGUAGE_DELICIOUS_PASSWD", "delicious_passwd");
    $tpl->parse("LANGUAGE_DELICIOUS_TAG", "delicious_tag");
    $tpl->parse("LANGUAGE_DELICIOUS_LOGIN", "delicious_loginbutton");
    $tpl->parse("LANGUAGE_DELICIOUS_IMPORTTITLE", "delicious_importtitle");
    $tpl->parse("LANGUAGE_DELICIOUS_IMPORT", "delicious_importbutton");
    $tpl->parse("LANGUAGE_DELICIOUS_NEXT", "delicious_nextbutton");
    $tpl->parse("LANGUAGE_DELICIOUS_TAGTITLE", "delicious_tagtitle");
    $tpl->parse("DIALOG_BLUEPRINT", "blueprint");


    $tpl->parse("OUT", $current_file);
    $out = $tpl->get_var("OUT");

    $fp = fopen("$templateoutputdir/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);
    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen.<br>");


    //*******************************************************************
    //* dialognew.ihtml
    //*******************************************************************

    $current_file = "dialognew";

    $tpl->set_file("$current_file", "$current_file.ihtml");
    $tpl->set_block("language", "dialognew_folder_title");
    $tpl->set_block("language", "dialognew_folder_form_title");
    $tpl->set_block("language", "dialognew_folder_form_type");
    $tpl->set_block("language", "dialognew_folder_form_type_1");
    $tpl->set_block("language", "dialognew_folder_form_type_2");
    $tpl->set_block("language", "dialognew_folder_form_type_3");
    $tpl->set_block("language", "dialognew_folder_form_type_4");
    $tpl->set_block("language", "dialognew_folder_button");
    $tpl->set_block("language", "dialognew_file_title");
    $tpl->set_block("language", "dialognew_file_form_title");
    $tpl->set_block("language", "dialognew_file_form_file");
    $tpl->set_block("language", "dialognew_file_max_file_size");
    $tpl->set_block("language", "dialognew_file_max_file_size_unit");
    $tpl->set_block("language", "dialognew_file_button");
    $tpl->set_block("language", "dialognew_text_title");
    $tpl->set_block("language", "dialognew_text_form_title");
    $tpl->set_block("language", "dialognew_text_form_text");
    $tpl->set_block("language", "dialognew_text_button");
    $tpl->set_block("language", "dialognew_link_title");
    $tpl->set_block("language", "dialognew_link_form_title");
    $tpl->set_block("language", "dialognew_link_form_link");
    $tpl->set_block("language", "dialognew_link_button");
    $tpl->set_block("language", "dialognew_error_title");
    $tpl->set_block("language", "dialognew_error_file");
    $tpl->set_block("language", "dialognew_error_url");
    $tpl->set_block("language", "dialognew_button_cancel");
    $tpl->set_block("language", "dialognew_error_not_logged_in");

    $tpl->set_var(array(
      "FORM_NAME" => "insertForm",
      "FORM_URL" => "./dialognew.php?object={ENVIRONMENT_ID}",
      "DOC_ROOT" => $config_webserver_ip,
      "CGI_ROOT" => $config_cgi_ip,
      #"BODY_ON_LOAD" => "if (document.insertForm.title) document.insertForm.title.focus();"
      "BODY_ON_LOAD" => "{BODY_ON_LOAD}"
    ));
    $tpl->parse("DIALOG_BLUEPRINT", "blueprint");
    $tpl->parse("LANGUAGE_FOLDER_TITLE", "dialognew_folder_title");
    $tpl->parse("LANGUAGE_FOLDER_FORM_TITLE", "dialognew_folder_form_title");
    $tpl->parse("LANGUAGE_FOLDER_FORM_TYPE", "dialognew_folder_form_type");
    $tpl->parse("LANGUAGE_FOLDER_FORM_TYPE_1", "dialognew_folder_form_type_1");
    $tpl->parse("LANGUAGE_FOLDER_FORM_TYPE_2", "dialognew_folder_form_type_2");
    $tpl->parse("LANGUAGE_FOLDER_FORM_TYPE_3", "dialognew_folder_form_type_3");
    $tpl->parse("LANGUAGE_FOLDER_FORM_TYPE_4", "dialognew_folder_form_type_4"); 
    $tpl->parse("LANGUAGE_FOLDER_BUTTON", "dialognew_folder_button");
    $tpl->parse("LANGUAGE_FILE_TITLE", "dialognew_file_title");
    $tpl->parse("LANGUAGE_FILE_FORM_TITLE", "dialognew_file_form_title");
    $tpl->parse("LANGUAGE_FILE_FORM_FILE", "dialognew_file_form_file");
    $tpl->parse("LANGUAGE_FILE_MAX_FILE_SIZE", "dialognew_file_max_file_size");
    $tpl->parse("LANGUAGE_FILE_MAX_FILE_SIZE_UNIT", "dialognew_file_max_file_size_unit");
    $tpl->parse("LANGUAGE_FILE_BUTTON", "dialognew_file_button");
    $tpl->parse("LANGUAGE_TEXT_TITLE", "dialognew_text_title");
    $tpl->parse("LANGUAGE_TEXT_FORM_TITLE", "dialognew_text_form_title");
    $tpl->parse("LANGUAGE_TEXT_FORM_TEXT", "dialognew_text_form_text");
    $tpl->parse("LANGUAGE_TEXT_BUTTON", "dialognew_text_button");
    $tpl->parse("LANGUAGE_LINK_TITLE", "dialognew_link_title");
    $tpl->parse("LANGUAGE_LINK_FORM_TITLE", "dialognew_link_form_title");
    $tpl->parse("LANGUAGE_LINK_FORM_LINK", "dialognew_link_form_link");
    $tpl->parse("LANGUAGE_LINK_BUTTON", "dialognew_link_button");
    $tpl->parse("LANGUAGE_DELETE_TITLE", "dialognew_delete_title");
    $tpl->parse("LANGUAGE_DELETE_FORM_OBJECTS", "dialognew_delete_form_objects");
    $tpl->parse("LANGUAGE_DELETE_BUTTON", "dialognew_delete_button");
    $tpl->parse("LANGUAGE_ERROR_TITLE", "dialognew_error_title");
    $tpl->parse("LANGUAGE_ERROR_FILE", "dialognew_error_file");
    $tpl->parse("LANGUAGE_ERROR_URL", "dialognew_error_url");
    $tpl->parse("LANGUAGE_ERROR_NOT_LOGGED_IN", "dialognew_error_not_logged_in");
    $tpl->parse("LANGUAGE_BUTTON_CANCEL", "dialognew_button_cancel");
    $tpl->parse("BUTTONS", "buttons");

    $tpl->parse("OUT", "$current_file");
    $out = $tpl->get_var("OUT");

    $fp = fopen("$templateoutputdir/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);
    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen.<br>");



    //*******************************************************************
    //* document.ihtml
    //*******************************************************************

    $current_file = "document";

    $tpl->set_file($current_file, "$current_file.ihtml");

    $tpl->set_var(array(
      "DUMMY" => "",
      "DOC_ROOT" => $config_webserver_ip
    ));

    $tpl->parse("CONTENT", $current_file);
    $out = $tpl->get_var("CONTENT");

    $fp = fopen("$templateoutputdir/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);
    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen.<br>");


    //*******************************************************************
    //* edit_file.ihtml
    //*******************************************************************

    $current_file = "edit_file";

    $tpl->set_file($current_file, "edit_file.ihtml");
    $tpl->set_block("language", "edit_file_title");
    $tpl->set_block("language", "edit_file_close");
    $tpl->set_block("language", "edit_file_save");
    $tpl->set_block("language", "edit_file_error_not_logged_in");

    $tpl->set_var(array(
      "DUMMY" => "",
      "FORM_NAME" => "edit_file",
      "FORM_URL" => "./edit_file.php?object={DOCUMENT_ID}",
      "FORM_ACTION" => "save",
      "DOC_ROOT" => $config_webserver_ip,
      "CGI_ROOT" => $config_cgi_ip,
      "BUTTONS" => "",
      "BODY_ON_LOAD" => "{BODY_ON_LOAD}"
    ));

    $tpl->parse("TITLE", "edit_file_title");
    $tpl->parse("LANGUAGE_CLOSE", "edit_file_close");
    $tpl->parse("LANGUAGE_SAVE", "edit_file_save");
    $tpl->parse("CONTENT", $current_file);
    $tpl->parse("LANGUAGE_ERROR_NOT_LOGGED_IN", "edit_file_error_not_logged_in");

    $tpl->parse("OUT", "blueprint");
    $out = $tpl->get_var("OUT");

    $fp = fopen("$templateoutputdir/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);
    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen.<br>");

    //*******************************************************************
    //* favourites_search.ihtml
    //*******************************************************************

    $current_file = "favourites_search";

    $tpl->set_file("$current_file", "$current_file.ihtml");
    $tpl->set_block("language", "favourites_search_title");
    $tpl->set_block("language", "favourites_search_title2");
    $tpl->set_block("language", "favourites_search_button_search");
    $tpl->set_block("language", "favourites_search_button_show_favourites");
    $tpl->set_block("language", "favourites_search_radio_group");
    $tpl->set_block("language", "favourites_search_radio_user");
    $tpl->set_block("language", "favourites_search_result_title");
    $tpl->set_block("language", "favourites_search_no_result");
    $tpl->set_block("language", "favourites_search_error_search_string_too_short");

    $tpl->set_var(array(
      "DUMMY" => "",
      "FORM_NAME" => "favourites_search",
      "FORM_URL" => "./favourites_search.php",
      "FORM_ACTION" => "save",
      "DOC_ROOT" => $config_webserver_ip,
      "CGI_ROOT" => $config_cgi_ip,
      "BUTTONS" => "",
      "BODY_ON_LOAD" => "if (document.favourites_search.title) document.favourites_search.title.focus();"
    ));

    $tpl->parse("TITLE", "favourites_search_title");
    $tpl->parse("LANGUAGE_SEARCH_TITLE", "favourites_search_title2");
    $tpl->parse("LANGUAGE_BUTTON_SEARCH", "favourites_search_button_search");
    $tpl->parse("LANGUAGE_BUTTON_SHOW_FAVOURITES", "favourites_search_button_show_favourites");
    $tpl->parse("LANGUAGE_RADIO_GROUP", "favourites_search_radio_group");
    $tpl->parse("LANGUAGE_RADIO_USER", "favourites_search_radio_user");
    $tpl->parse("LANGUAGE_SEARCH_RESULT_TITLE", "favourites_search_result_title");
    $tpl->parse("LANGUAGE_NO_RESULT", "favourites_search_no_result");
    $tpl->parse("LANGUAGE_ERROR_SEARCH_STRING_TOO_SHORT", "favourites_search_error_search_string_too_short");
    $tpl->parse("CONTENT", $current_file);
    $tpl->parse("OUT", "blueprint");

    $out = $tpl->get_var("OUT");

    $fp = fopen("$templateoutputdir/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);
    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen.<br>");

    //*******************************************************************
    //* favourites_show.ihtml
    //*******************************************************************

    $current_file = "favourites_show";

    $tpl->set_file("$current_file", "$current_file.ihtml");
    $tpl->set_block("language", "favourites_show_title");
    $tpl->set_block("language", "favourites_show_user");
    $tpl->set_block("language", "favourites_show_group");
    $tpl->set_block("language", "favourites_show_button_remove_favourites");
    $tpl->set_block("language", "favourites_show_no_favourites");

    $tpl->set_var(array(
      "DUMMY" => "",
      "FORM_NAME" => "favourites_show",
      "FORM_URL" => "./favourites_show.php",
      "FORM_ACTION" => "save",
      "DOC_ROOT" => $config_webserver_ip,
      "CGI_ROOT" => $config_cgi_ip,
      "BUTTONS" => "",
      "BODY_ON_LOAD" => "if (document.favourites_show.title) document.favourites_show.title.focus();"
    ));

    $tpl->parse("TITLE", "favourites_show_title");
    $tpl->parse("LANGUAGE_USER", "favourites_show_user");
    $tpl->parse("LANGUAGE_GROUP", "favourites_show_group");
    $tpl->parse("LANGUAGE_BUTTON_REMOVE_FAVOURITES", "favourites_show_button_remove_favourites");
    $tpl->parse("LANGUAGE_NO_FAVOURITES", "favourites_show_no_favourites");
    $tpl->parse("CONTENT", $current_file);
    $tpl->parse("OUT", "blueprint");

    $out = $tpl->get_var("OUT");
$fp = fopen("$templateoutputdir/$language/$current_file.ihtml", "w"); fwrite($fp, $out);
    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen.<br>");

    //*******************************************************************
    ////* idcard.ihtml
    //*******************************************************************

    $current_file = "idcard";
    $tpl->set_file($current_file, "$current_file.ihtml");
    $tpl->set_block("language", "idcard_title");
    $tpl->set_block("language", "idcard_username");
    $tpl->set_block("language", "idcard_name");
    $tpl->set_block("language", "idcard_description");
    $tpl->set_block("language", "idcard_email");
    $tpl->set_block("language", "idcard_adress");
    $tpl->set_block("language", "idcard_callto");
    $tpl->set_block("language", "idcard_im");
    $tpl->set_block("language", "idcard_groupname");
    $tpl->set_block("language", "idcard_group_description");
    $tpl->set_block("language", "idcard_group_admins");
    $tpl->set_block("language", "idcard_add_favourite");
    $tpl->set_block("language", "idcard_close");

    $tpl->set_var(array(
      "DUMMY" => "",
      "FORM_NAME" => "idcard",
      "FORM_URL" => "#",
      "FORM_ACTION" => "save",
      "DOC_ROOT" => $config_webserver_ip,
      "CGI_ROOT" => $config_cgi_ip,
      "BUTTONS" => "",
      "BODY_ON_LOAD" => "if (document.idcard.title) document.idcard.title.focus();"
    ));

    $tpl->parse("TITLE", "idcard_title");
    $tpl->parse("LANGUAGE_USERNAME", "idcard_username");
    $tpl->parse("LANGUAGE_NAME", "idcard_name");
    $tpl->parse("LANGUAGE_DESCRIPTION", "idcard_description");
    $tpl->parse("LANGUAGE_EMAIL", "idcard_email");
    $tpl->parse("LANGUAGE_ADRESS", "idcard_adress");
    $tpl->parse("LANGUAGE_CALLTO", "idcard_callto");
    $tpl->parse("LANGUAGE_IM", "idcard_im");
    $tpl->parse("LANGUAGE_GROUPNAME", "idcard_groupname");
    $tpl->parse("LANGUAGE_GROUP_DESCRIPTION", "idcard_group_description");
    $tpl->parse("LANGUAGE_GROUP_ADMINS", "idcard_group_admins");
    $tpl->parse("LANGUAGE_ADD_FAVOURITE", "idcard_add_favourite");
    $tpl->parse("LANGUAGE_CLOSE", "idcard_close");
    $tpl->parse("CONTENT", $current_file);

    $tpl->parse("OUT", "blueprint");
    $out = $tpl->get_var("OUT");

    $fp = fopen("$templateoutputdir/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);
    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen.<br>");



    //*******************************************************************
    //* index.ihtml
    //*******************************************************************

    $current_file = "index";

    $tpl->set_file("$current_file", "$current_file.ihtml");
    $tpl->parse("OUT", "$current_file");
    $out = $tpl->get_var("OUT");

    $fp = fopen("$templateoutputdir/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);
    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen.<br>");


    //*******************************************************************
    //* menu.ihtml
    //*******************************************************************

    $current_file = "menu";

    $tpl->set_file("$current_file", "$current_file.ihtml");
    $tpl->set_block("language", "menu_new");
    $tpl->set_block("language", "menu_edit");
    $tpl->set_block("language", "menu_new_folder");
    $tpl->set_block("language", "menu_new_file");
    $tpl->set_block("language", "menu_new_html");
    $tpl->set_block("language", "menu_new_text");
    $tpl->set_block("language", "menu_new_url");
    $tpl->set_block("language", "menu_new_forum");
    $tpl->set_block("language", "menu_new_portal");
    $tpl->set_block("language", "menu_new_questionary");
    $tpl->set_block("language", "menu_new_ftp");
    $tpl->set_block("language", "menu_new_delicious");
    $tpl->set_block("language", "menu_edit_bookmark");
    $tpl->set_block("language", "menu_edit_copy");
    $tpl->set_block("language", "menu_edit_cut");
    $tpl->set_block("language", "menu_edit_reference");
    $tpl->set_block("language", "menu_edit_delete");
    $tpl->set_block("language", "menu_edit_destroy");
    $tpl->set_block("language", "menu_edit_paste");
    $tpl->set_block("language", "menu_edit_properties");
    $tpl->set_block("language", "menu_forum_1_1");
    $tpl->set_block("language", "menu_forum_2_1");
    $tpl->set_block("language", "menu_forum_category_1_1");
    $tpl->set_block("language", "menu_calendar_1_1");
    $tpl->set_block("language", "menu_calendar_2_1");
    $tpl->set_block("language", "menu_portal");
    $tpl->set_block("language", "menu_portlet");
    $tpl->set_block("language", "menu_portal_new_area");
    $tpl->set_block("language", "menu_portal_new_component");
    $tpl->set_block("language", "menu_portal_new_entry");
    $tpl->set_block("language", "menu_portal_edit_areas");
    $tpl->set_block("language", "menu_portal_area_edit");
    $tpl->set_block("language", "menu_portal_area_move");
    $tpl->set_block("language", "menu_portal_area_delete");
    $tpl->set_block("language", "menu_portal_edit_portlets");
    $tpl->set_block("language", "menu_portal_portlet_edit");
    $tpl->set_block("language", "menu_portal_portlet_copy");
    $tpl->set_block("language", "menu_portal_portlet_link");
    $tpl->set_block("language", "menu_portal_portlet_paste");
    $tpl->set_block("language", "menu_portal_portlet_delete");
    $tpl->set_block("language", "menu_portal_portlet_properties");
    $tpl->set_block("language", "menu_portal_edit_display");
    $tpl->set_block("language", "menu_questionary_1_1");
    $tpl->set_block("language", "menu_questionary_2_1");
    $tpl->set_block("language", "menu_questionary_2_2");
    $tpl->parse("LANGUAGE_NEW", "menu_new");
    $tpl->parse("LANGUAGE_EDIT", "menu_edit");
    $tpl->parse("LANGUAGE_NEW_FOLDER", "menu_new_folder");
    $tpl->parse("LANGUAGE_NEW_FILE", "menu_new_file");
    $tpl->parse("LANGUAGE_NEW_HTML", "menu_new_html");
    $tpl->parse("LANGUAGE_NEW_TEXT", "menu_new_text");
    $tpl->parse("LANGUAGE_NEW_URL", "menu_new_url");
    $tpl->parse("LANGUAGE_NEW_FORUM", "menu_new_forum");
    $tpl->parse("LANGUAGE_NEW_PORTAL", "menu_new_portal");
    $tpl->parse("LANGUAGE_NEW_DELICIOUS", "menu_new_delicious");
    $tpl->parse("LANGUAGE_NEW_QUESTIONARY", "menu_new_questionary");
    $tpl->parse("LANGUAGE_NEW_FTP", "menu_new_ftp");
    $tpl->parse("LANGUAGE_EDIT_BOOKMARK", "menu_edit_bookmark");
    $tpl->parse("LANGUAGE_EDIT_COPY", "menu_edit_copy");
    $tpl->parse("LANGUAGE_EDIT_CUT", "menu_edit_cut");
    $tpl->parse("LANGUAGE_EDIT_REFERENCE", "menu_edit_reference");
    $tpl->parse("LANGUAGE_EDIT_DELETE", "menu_edit_delete");
    $tpl->parse("LANGUAGE_EDIT_DESTROY", "menu_edit_destroy");
    $tpl->parse("LANGUAGE_EDIT_PASTE", "menu_edit_paste");
    $tpl->parse("LANGUAGE_EDIT_PROPERTIES", "menu_edit_properties");
    $tpl->parse("LANGUAGE_FORUM_1_1", "menu_forum_1_1");
    $tpl->parse("LANGUAGE_FORUM_2_1", "menu_forum_2_1");
    $tpl->parse("LANGUAGE_FORUM_CATEGORY_1_1", "menu_forum_category_1_1");
    $tpl->parse("LANGUAGE_CALENDAR_1_1", "menu_calendar_1_1");
    $tpl->parse("LANGUAGE_CALENDAR_2_1", "menu_calendar_2_1");
    $tpl->parse("LANGUAGE_PORTAL", "menu_portal");
    $tpl->parse("LANGUAGE_PORTLET", "menu_portlet");
    $tpl->parse("LANGUAGE_PORTAL_NEW_AREA", "menu_portal_new_area");
    $tpl->parse("LANGUAGE_PORTAL_NEW_COMPONENT", "menu_portal_new_component");
    $tpl->parse("LANGUAGE_PORTAL_NEW_ENTRY", "menu_portal_new_entry");
    $tpl->parse("LANGUAGE_PORTAL_EDIT_AREAS", "menu_portal_edit_areas");
    $tpl->parse("LANGUAGE_PORTAL_AREA_EDIT", "menu_portal_area_edit");
    $tpl->parse("LANGUAGE_PORTAL_AREA_MOVE", "menu_portal_area_move");
    $tpl->parse("LANGUAGE_PORTAL_AREA_DELETE", "menu_portal_area_delete");
    $tpl->parse("LANGUAGE_PORTAL_EDIT_PORTLETS", "menu_portal_edit_portlets");
    $tpl->parse("LANGUAGE_PORTAL_PORTLET_EDIT", "menu_portal_portlet_edit");
    $tpl->parse("LANGUAGE_PORTAL_PORTLET_COPY", "menu_portal_portlet_copy");
    $tpl->parse("LANGUAGE_PORTAL_PORTLET_LINK", "menu_portal_portlet_link");
    $tpl->parse("LANGUAGE_PORTAL_PORTLET_PASTE", "menu_portal_portlet_paste");
    $tpl->parse("LANGUAGE_PORTAL_PORTLET_DELETE", "menu_portal_portlet_delete");
    $tpl->parse("LANGUAGE_PORTAL_PORTLET_PROPERTIES", "menu_portal_portlet_properties");
    $tpl->parse("LANGUAGE_PORTAL_EDIT_DISPLAY", "menu_portal_edit_display");
    $tpl->parse("LANGUAGE_QUESTIONARY_1_1", "menu_questionary_1_1");
    $tpl->parse("LANGUAGE_QUESTIONARY_2_1", "menu_questionary_2_1");
    $tpl->parse("LANGUAGE_QUESTIONARY_2_2", "menu_questionary_2_2");
    $tpl->parse("OUT", "$current_file");
    $out = $tpl->get_var("OUT");

    $fp = fopen("$templateoutputdir/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);
    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen.<br>");



    //*******************************************************************
    //* password.ihtml
    //*******************************************************************

    $current_file = "password";

    $tpl->set_file($current_file, "$current_file.ihtml");
    $tpl->set_block("language", "password_title");
    $tpl->set_block("language", "password_old_pwd");
    $tpl->set_block("language", "password_new_pwd");
    $tpl->set_block("language", "password_new_pwd_confirm");
    $tpl->set_block("language", "password_feedback_old_pwd");
    $tpl->set_block("language", "password_feedback_new_pwd");
    $tpl->set_block("language", "password_submit");

    $tpl->set_var(array(
      "DUMMY" => "",
      "FORM_NAME" => "password",
      "FORM_URL" => "./password.php",
      "FORM_ACTION" => "change",
      "DOC_ROOT" => $config_webserver_ip,
      "CGI_ROOT" => $config_cgi_ip,
      "BODY_ON_LOAD" => "if (document.password.title) document.password.title.focus();"
    ));

    $tpl->parse("TITLE", "password_title");
    $tpl->parse("LANGUAGE_OLD_PWD", "password_old_pwd");
    $tpl->parse("LANGUAGE_NEW_PWD", "password_new_pwd");
    $tpl->parse("LANGUAGE_NEW_PWD_CONFIRM", "password_new_pwd_confirm");
    $tpl->parse("LANGUAGE_FEEDBACK_OLD_PWD", "password_feedback_old_pwd");
    $tpl->parse("LANGUAGE_FEEDBACK_NEW_PWD", "password_feedback_new_pwd");
    $tpl->parse("BUTTON_LABEL", "password_submit");
    $tpl->parse("BUTTONS", "buttons");
    $tpl->parse("CONTENT", $current_file);

    $tpl->parse("OUT", "blueprint");
    $out = $tpl->get_var("OUT");

    $fp = fopen("$templateoutputdir/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);
    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen.<br>");



    //*******************************************************************
    //* preferences.ihtml
    //*******************************************************************

    $current_file = "preferences";

    $tpl->set_file($current_file, "$current_file.ihtml");
    $tpl->set_block("language", "preferences_title");
    $tpl->set_block("language", "preferences_username");
    $tpl->set_block("language", "preferences_name");
    $tpl->set_block("language", "preferences_description");
    $tpl->set_block("language", "preferences_adress");
    $tpl->set_block("language", "preferences_email");
    $tpl->set_block("language", "preferences_callto");
    $tpl->set_block("language", "preferences_favourites_show");
    $tpl->set_block("language", "preferences_favourites_search");
    $tpl->set_block("language", "preferences_language");
    $tpl->set_block("language", "preferences_language1");
    $tpl->set_block("language", "preferences_language2");
    $tpl->set_block("language", "preferences_language3");
    $tpl->set_block("language", "preferences_hidden");
    $tpl->set_block("language", "preferences_hidden_show");
    $tpl->set_block("language", "preferences_hidden_hide");
    $tpl->set_block("language", "preferences_treeview");
    $tpl->set_block("language", "preferences_treeview_mini");
    $tpl->set_block("language", "preferences_treeview_maxi");
    $tpl->set_block("language", "preferences_treeview_off");
    $tpl->set_block("language", "preferences_modules");
    $tpl->set_block("language", "preferences_portal");
    $tpl->set_block("language", "preferences_calendar");
    $tpl->set_block("language", "preferences_forum");
    $tpl->set_block("language", "preferences_questionary");
    $tpl->set_block("language", "preferences_submit");
    $tpl->set_block("language", "preferences_cancel");

    $tpl->set_var(array(
      "DUMMY" => "",
      "FORM_NAME" => "preferences",
      "FORM_URL" => "./preferences.php",
      "FORM_ACTION" => "change",
      "DOC_ROOT" => $config_webserver_ip,
      "CGI_ROOT" => $config_cgi_ip,
      "BODY_ON_LOAD" => "if (document.preferences.title) document.preferences.title.focus();"
    ));

    $tpl->parse("TITLE", "preferences_title");
    $tpl->parse("LANGUAGE_USERNAME", "preferences_username");
    $tpl->parse("LANGUAGE_NAME", "preferences_name");
    $tpl->parse("LANGUAGE_DESCRIPTION", "preferences_description");
    $tpl->parse("LANGUAGE_ADRESS", "preferences_adress");
    $tpl->parse("LANGUAGE_EMAIL", "preferences_email");
    $tpl->parse("LANGUAGE_CALLTO", "preferences_callto");
    $tpl->parse("LANGUAGE_FAVOURITES_SHOW", "preferences_favourites_show");
    $tpl->parse("LANGUAGE_FAVOURITES_SEARCH", "preferences_favourites_search");
    $tpl->parse("LANGUAGE_LANGUAGE", "preferences_language");
    $tpl->parse("LANGUAGE_LANGUAGE1", "preferences_language1");
    $tpl->parse("LANGUAGE_LANGUAGE2", "preferences_language2");
    $tpl->parse("LANGUAGE_LANGUAGE3", "preferences_language3");
    $tpl->parse("LANGUAGE_HIDDEN", "preferences_hidden");
    $tpl->parse("LANGUAGE_HIDDEN_SHOW", "preferences_hidden_show");
    $tpl->parse("LANGUAGE_HIDDEN_HIDE", "preferences_hidden_hide");
    $tpl->parse("LANGUAGE_TREEVIEW", "preferences_treeview");
    $tpl->parse("LANGUAGE_TREEVIEW_MINI", "preferences_treeview_mini");
    $tpl->parse("LANGUAGE_TREEVIEW_MAXI", "preferences_treeview_maxi");
    $tpl->parse("LANGUAGE_TREEVIEW_OFF", "preferences_treeview_off");
    $tpl->parse("LANGUAGE_MODULES", "preferences_modules");
    $tpl->parse("LANGUAGE_PORTAL", "preferences_portal");
    $tpl->parse("LANGUAGE_CALENDAR", "preferences_calendar");
    $tpl->parse("LANGUAGE_FORUM", "preferences_forum");
    $tpl->parse("LANGUAGE_QUESTIONARY", "preferences_questionary");
    $tpl->parse("BUTTON_LABEL", "preferences_submit");
    $tpl->parse("BUTTON_CANCEL", "preferences_cancel");
    $tpl->parse("BUTTONS", "buttons");
    $tpl->parse("CONTENT", $current_file);

    $tpl->parse("OUT", "blueprint");
    $out = $tpl->get_var("OUT");

    $fp = fopen("$templateoutputdir/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);
    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen.<br>");



    //*******************************************************************
    //* properties.ihtml
    //*******************************************************************

    $current_file = "properties";

    $tpl->set_file("$current_file", "$current_file.ihtml");
    $tpl->set_block("language", "properties_page_title");
    $tpl->set_block("language", "properties_tab1");
    $tpl->set_block("language", "properties_tab2");
    $tpl->set_block("language", "properties_tab3");
    $tpl->set_block("language", "properties_tab1_1");
    $tpl->set_block("language", "properties_tab1_2");
    $tpl->set_block("language", "properties_tab1_3");
    $tpl->set_block("language", "properties_tab1_5");
    $tpl->set_block("language", "properties_tab1_6");
    $tpl->set_block("language", "properties_tab1_8");
    $tpl->set_block("language", "properties_tab1_9");
    $tpl->set_block("language", "properties_tab1_button");
    $tpl->set_block("language", "properties_tab2_1");
    $tpl->set_block("language", "properties_tab2_2");
    $tpl->set_block("language", "properties_tab2_3");
    $tpl->set_block("language", "properties_tab2_4");
    $tpl->set_block("language", "properties_tab3_title");
    $tpl->set_block("language", "properties_tab3_presentation");
    $tpl->set_block("language", "properties_tab3_presentation_normal");
    $tpl->set_block("language", "properties_tab3_presentation_index");
    $tpl->set_block("language", "properties_tab3_presentation_head");
    $tpl->set_block("language", "properties_tab3_collectiontype");
    $tpl->set_block("language", "properties_tab3_collectiontype_normal");
    $tpl->set_block("language", "properties_tab3_collectiontype_cluster");
    $tpl->set_block("language", "properties_tab3_collectiontype_sequence");
  $tpl->set_block("language", "properties_tab3_collectiontype_gallery");
  $tpl->set_block("language", "properties_tab3_collectiontype_taggedFolder");
    $tpl->set_block("language", "properties_tab3_alt_1");
    $tpl->set_block("language", "properties_tab3_alt_2");
    $tpl->set_block("language", "properties_submit");
    $tpl->set_block("language", "properties_button_cancel");

    $tpl->parse("LANGUAGE_PAGE_TITLE", "properties_page_title");
    $tpl->parse("LANGUAGE_TAB1", "properties_tab1");
    $tpl->parse("LANGUAGE_TAB2", "properties_tab2");
    $tpl->parse("LANGUAGE_TAB3", "properties_tab3");
    $tpl->parse("LANGUAGE_TAB1_1", "properties_tab1_1");
    $tpl->parse("LANGUAGE_TAB1_2", "properties_tab1_2");
    $tpl->parse("LANGUAGE_TAB1_3", "properties_tab1_3");
    $tpl->parse("LANGUAGE_TAB1_5", "properties_tab1_5");
    $tpl->parse("LANGUAGE_TAB1_6", "properties_tab1_6");
    $tpl->parse("LANGUAGE_TAB1_8", "properties_tab1_8");
    $tpl->parse("LANGUAGE_TAB1_9", "properties_tab1_9");
    $tpl->parse("LANGUAGE_TAB1_BUTTON", "properties_tab1_button");
    $tpl->parse("LANGUAGE_TAB2_1", "properties_tab2_1");
    $tpl->parse("LANGUAGE_TAB2_2", "properties_tab2_2");
    $tpl->parse("LANGUAGE_TAB2_3", "properties_tab2_3");
    $tpl->parse("LANGUAGE_TAB2_4", "properties_tab2_4");
    $tpl->parse("LANGUAGE_TAB3_TITLE", "properties_tab3_title");
    $tpl->parse("LANGUAGE_TAB3_PRESENTATION", "properties_tab3_presentation");
    $tpl->parse("LANGUAGE_TAB3_PRESENTATION_NORMAL", "properties_tab3_presentation_normal");
    $tpl->parse("LANGUAGE_TAB3_PRESENTATION_INDEX", "properties_tab3_presentation_index");
    $tpl->parse("LANGUAGE_TAB3_PRESENTATION_HEAD", "properties_tab3_presentation_head");
    $tpl->parse("LANGUAGE_TAB3_COLLECTIONTYPE", "properties_tab3_collectiontype");
    $tpl->parse("LANGUAGE_TAB3_COLLECTIONTYPE_NORMAL", "properties_tab3_collectiontype_normal");
    $tpl->parse("LANGUAGE_TAB3_COLLECTIONTYPE_CLUSTER", "properties_tab3_collectiontype_cluster");
    $tpl->parse("LANGUAGE_TAB3_COLLECTIONTYPE_SEQUENCE", "properties_tab3_collectiontype_sequence");
  $tpl->parse("LANGUAGE_TAB3_COLLECTIONTYPE_GALLERY", "properties_tab3_collectiontype_gallery");
  $tpl->parse("LANGUAGE_TAB3_COLLECTIONTYPE_TAGGEDFOLDER", "properties_tab3_collectiontype_taggedFolder");
    $tpl->parse("LANGUAGE_TAB3_ALT_1", "properties_tab3_alt_1");
    $tpl->parse("LANGUAGE_TAB3_ALT_2", "properties_tab3_alt_2");
    $tpl->parse("LANGUAGE_SUBMIT", "properties_submit");
    $tpl->parse("LANGUAGE_CANCEL", "properties_button_cancel");

    $tpl->parse("OUT", "$current_file");
    $out = $tpl->get_var("OUT");

    $fp = fopen("$templateoutputdir/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);
    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen.<br>");



    //*******************************************************************
    //* rights.ihtml
    //*******************************************************************

    $current_file = "rights";

    $tpl->set_file($current_file, "$current_file.ihtml");

    $tpl->set_block("language", "rights_title");
    $tpl->set_block("language", "rights_publish_1");
    $tpl->set_block("language", "rights_publish_2");
    $tpl->set_block("language", "rights_publish_3");
    $tpl->set_block("language", "rights_publish_4");
    $tpl->set_block("language", "rights_groups_none");
    $tpl->set_block("language", "rights_favourites_none");
    $tpl->set_block("language", "rights_favourites_double");
    $tpl->set_block("language", "rights_additional_none");
    $tpl->set_block("language", "rights_additional_double");
    $tpl->set_block("language", "rights_read");
    $tpl->set_block("language", "rights_write");
    $tpl->set_block("language", "rights_sanction");
    $tpl->set_block("language", "rights_owner");
    $tpl->set_block("language", "rights_alt_businesscard");
    $tpl->set_block("language", "rights_groups");
    $tpl->set_block("language", "rights_favourites");
    $tpl->set_block("language", "rights_additional");
    $tpl->set_block("language", "rights_button_ok");

    $tpl->set_var(array(
      "FORM_NAME" => "rightsform",
      "FORM_URL" => "./rights.php?object={OBJECT_ID}",
      "FORM_ACTION" => "save",
      "DOC_ROOT" => $config_webserver_ip,
      "CGI_ROOT" => $config_cgi_ip,
      "BODY_ON_LOAD" => "if (document.rightsform.title) document.rightsform.title.focus();"
    ));

    $tpl->parse("TITLE", "rights_title");
    $tpl->parse("LANGUAGE_PUBLISH_1", "rights_publish_1");
    $tpl->parse("LANGUAGE_PUBLISH_2", "rights_publish_2");
    $tpl->parse("LANGUAGE_PUBLISH_3", "rights_publish_3");
    $tpl->parse("LANGUAGE_PUBLISH_4", "rights_publish_4");
    $tpl->parse("LANGUAGE_GROUPS_NONE", "rights_groups_none");
    $tpl->parse("LANGUAGE_FAVOURITES_NONE", "rights_favourites_none");
    $tpl->parse("LANGUAGE_FAVOURITES_DOUBLE", "rights_favourites_double");
    $tpl->parse("LANGUAGE_ADDITIONAL_NONE", "rights_additional_none");
    $tpl->parse("LANGUAGE_ADDITIONAL_DOUBLE", "rights_additional_double");
    $tpl->parse("LANGUAGE_READ", "rights_read");
    $tpl->parse("LANGUAGE_WRITE", "rights_write");
    $tpl->parse("LANGUAGE_SANCTION", "rights_sanction");
    $tpl->parse("LANGUAGE_OWNER", "rights_owner");
    $tpl->parse("LANGUAGE_ALT_BUSINESSCARD", "rights_alt_businesscard");
    $tpl->parse("LANGUAGE_GROUPS", "rights_groups");
    $tpl->parse("LANGUAGE_FAVOURITES", "rights_favourites");
    $tpl->parse("LANGUAGE_ADDITIONAL", "rights_additional");
    $tpl->parse("BUTTON_LABEL", "rights_button_ok");
    $tpl->parse("BUTTONS", "buttons");
    $tpl->parse("CONTENT", $current_file);

    $tpl->parse("OUT", "blueprint");
    $out = $tpl->get_var("OUT");

    $fp = fopen("$templateoutputdir/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);
    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen.<br>");



    //*******************************************************************
    //* topframe.ihtml
    //*******************************************************************

    $current_file = "topframe";

    $tpl->set_file("$current_file", "$current_file.ihtml");
    $tpl->set_block("language", "topframe_serverhome");
    $tpl->set_block("language", "topframe_menu_new");
    $tpl->set_block("language", "topframe_menu_edit");
    $tpl->set_block("language", "topframe_home");
    $tpl->set_block("language", "topframe_up");
    $tpl->set_block("language", "topframe_user_administration");
    $tpl->set_block("language", "topframe_preferences");
    $tpl->set_block("language", "topframe_document_edit");
    $tpl->set_block("language", "topframe_upload_image");
    $tpl->set_block("language", "topframe_properties");
    $tpl->set_block("language", "topframe_login");
    $tpl->set_block("language", "topframe_logout");
    $tpl->set_block("language", "topframe_desktop");
    $tpl->set_block("language", "topframe_search");
    $tpl->set_block("language", "topframe_printview");
    $tpl->set_block("language", "topframe_info");
    $tpl->set_block("language", "topframe_help");
    $tpl->set_block("language", "topframe_sequence_start");
    $tpl->set_block("language", "topframe_sequence_first");
    $tpl->set_block("language", "topframe_sequence_previous");
    $tpl->set_block("language", "topframe_sequence_next");
    $tpl->set_block("language", "topframe_sequence_last");

    $tpl->parse("LANGUAGE_SERVERHOME", "topframe_serverhome");
    $tpl->parse("LANGUAGE_MENU_NEW", "topframe_menu_edit");
    $tpl->parse("LANGUAGE_MENU_EDIT", "topframe_menu_new");
    $tpl->parse("LANGUAGE_HOME", "topframe_home");
    $tpl->parse("LANGUAGE_UP", "topframe_up");
    $tpl->parse("LANGUAGE_USER_ADMINISTRATION", "topframe_user_administration");
    $tpl->parse("LANGUAGE_PREFERENCES", "topframe_preferences");
    $tpl->parse("LANGUAGE_DOCUMENT_EDIT", "topframe_document_edit");
    $tpl->parse("LANGUAGE_UPLOAD_IMAGE", "topframe_upload_image");
    $tpl->parse("LANGUAGE_PROPERTIES", "topframe_properties");
    $tpl->parse("LANGUAGE_LOGIN", "topframe_login");
    $tpl->parse("LANGUAGE_LOGOUT", "topframe_logout");
    $tpl->parse("LANGUAGE_DESKTOP", "topframe_desktop");
    $tpl->parse("LANGUAGE_SEARCH", "topframe_search");
    $tpl->parse("LANGUAGE_PRINTVIEW", "topframe_printview");
    $tpl->parse("LANGUAGE_INFO", "topframe_info");
    $tpl->parse("LANGUAGE_HELP", "topframe_help");
    $tpl->parse("LANGUAGE_SEQUENCE_START", "topframe_sequence_start");
    $tpl->parse("LANGUAGE_SEQUENCE_FIRST", "topframe_sequence_first");
    $tpl->parse("LANGUAGE_SEQUENCE_PREVIOUS", "topframe_sequence_previous");
    $tpl->parse("LANGUAGE_SEQUENCE_NEXT", "topframe_sequence_next");
    $tpl->parse("LANGUAGE_SEQUENCE_LAST", "topframe_sequence_last");

    $tpl->parse("OUT", "$current_file");
    $out = $tpl->get_var("OUT");

    $fp = fopen("$templateoutputdir/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);
    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen.<br>");



    //*******************************************************************
    //* trashbin.ihtml
    //*******************************************************************

    $current_file = "trashbin";

    $tpl->set_file("$current_file", "$current_file.ihtml");
    $tpl->set_block("language", "trashbin_name");
    $tpl->set_block("language", "trashbin_no_content");
    $tpl->set_block("language", "trashbin_title_1");
    $tpl->set_block("language", "trashbin_title_2");
    $tpl->set_block("language", "trashbin_title_3");
    $tpl->set_block("language", "trashbin_title_4");

    $tpl->parse("TRASHBIN_NAME", "trashbin_name");
    $tpl->parse("LANGUAGE_NO_CONTENT", "trashbin_no_content");
    $tpl->parse("LANGUAGE_TITLE_1", "trashbin_title_1");
    $tpl->parse("LANGUAGE_TITLE_2", "trashbin_title_2");
    $tpl->parse("LANGUAGE_TITLE_3", "trashbin_title_3");
    $tpl->parse("LANGUAGE_TITLE_4", "trashbin_title_4");

    $tpl->parse("OUT", "$current_file");
    $out = $tpl->get_var("OUT");

    $fp = fopen("$templateoutputdir/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);
    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen.<br>");



    //*******************************************************************
    //* treeframe.ihtml
    //*******************************************************************

    $current_file = "treeframe";

    $tpl->set_file("$current_file", "$current_file.ihtml");
    $tpl->set_block("language", "treeframe_user_calendar");
    $tpl->set_block("language", "treeframe_hidden");

    $tpl->parse("LANGUAGE_USER_CALENDAR", "treeframe_user_calendar");
    $tpl->parse("LANGUAGE_HIDDEN", "treeframe_hidden");

    $tpl->parse("OUT", "$current_file");
    $out = $tpl->get_var("OUT");

    $fp = fopen("$templateoutputdir/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);
    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen.<br>");

  }


  //*******************************************************************
  //* MODULE: Calendar
  //*******************************************************************

  #include("$config_doc_root/modules/calendar/language/admin_language.php");


  //*******************************************************************
  //* MODULE: Forum
  //*******************************************************************

  include("$config_doc_root/modules/forum/language/admin_language.php");


  //*******************************************************************
  //* MODULE: Portal
  //*******************************************************************

  include("$config_doc_root/modules/portal/language/admin_language.php");


  //*******************************************************************
  //* MODULE: Portal2
  //*******************************************************************

  include("$config_doc_root/modules/portal2/language/admin_language.php");


  //*******************************************************************
  //* MODULE: Questionary
  //*******************************************************************

  include("$config_doc_root/modules/questionary/language/admin_language.php");
  
  //*******************************************************************
  //* MODULE: Gallery
  //*******************************************************************

  include("$config_doc_root/modules/gallery/language/admin_language.php");
  
  //*******************************************************************
  //* MODULE: Search
  //*******************************************************************  
  
  include("$config_doc_root/modules/search/language/admin_language.php");

  //*******************************************************************
  //* TinyMCE
  //*******************************************************************  
  
  include("$config_doc_root/javascript/tinymce/language/admin_language.php");

?>
