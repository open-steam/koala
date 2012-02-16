<?php

  /****************************************************************************
  admin_language.php - build the language dependent templates of the forum module
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

  Author: Stephanie Sarach
  EMail: haribo@upb.de

  ****************************************************************************/
//  require_once("../../../config/config.php");
//  require_once("$config_doc_root/classes/template.inc");
//  require_once("$config_doc_root/classes/debugHelper.php");

  $tmp_doc_root = "$config_doc_root/modules/forum";


  //get list of all language directories
  $languages = array();
  $handle = opendir ('.');
  while (false !== ($file = readdir ($handle)))
  {
    if(is_dir($file) && $file != "." && $file != ".." && $file != "CVS" && $file != ".svn")
      array_push($languages, $file);
  }
  closedir($handle);


  echo("<br><br><hr><b>MODULE: Forum</b><hr>");

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
    $tpl->set_var("DOC_ROOT", $config_webserver_ip);
    $tpl->set_var("CONFIG_DOC_ROOT", $config_doc_root);

    //set template root dir back to the general design
    $tpl->set_root("$tmp_doc_root/language");

    echo("<br><br>Sprache: $language<br>");


    //*******************************************************************
    //* forum_view_forum.ihtml
    //*******************************************************************

    $current_file = "forum_view_forum";

    $tpl->set_file($current_file, "$current_file.ihtml");
    $tpl->set_block("language", "fvf_action_edit_forum");
    $tpl->set_block("language", "fvf_action_subscribe_forum");
    $tpl->set_block("language", "fvf_action_unsubscribe_forum");
    $tpl->set_block("language", "fvf_action_delete_forum");
    $tpl->set_block("language", "fvf_add_categorie");
    $tpl->set_block("language", "fvf_paste_categorie");
    $tpl->set_block("language", "fvf_new_message_info");
    $tpl->set_block("language", "fvf_administrated_from");
    $tpl->set_block("language", "fvf_created_from");
    $tpl->set_block("language", "fvf_created_on");
    $tpl->set_block("language", "fvf_header_categorie");
    $tpl->set_block("language", "fvf_header_message");
    $tpl->set_block("language", "fvf_header_last_post_info");
    $tpl->set_block("language", "fvf_last_post_created_from");
    $tpl->set_block("language", "fvf_site_title");
    $tpl->set_block("language", "fvf_no_access");
    $tpl->set_block("language", "fvf_no_entries");
    $tpl->set_var(array(
       "DOC_ROOT" => $config_webserver_ip,
       "BODY_ON_LOAD" => "if (document.form_blueprint.title) document.form_blueprint.title.focus();"
    ));

    $tpl->parse("LANGUAGE_ACTION_EDIT_FORUM", "fvf_action_edit_forum");
    $tpl->parse("LANGUAGE_ACTION_SUBSCRIBE_FORUM", "fvf_action_subscribe_forum");
    $tpl->parse("LANGUAGE_ACTION_UNSUBSCRIBE_FORUM", "fvf_action_unsubscribe_forum");
    $tpl->parse("LANGUAGE_ACTION_DELETE", "fvf_action_delete_forum");
    $tpl->parse("LANGUAGE_ADD_CATEGORIE", "fvf_add_categorie");
    $tpl->parse("LANGUAGE_PASTE_CATEGORIE", "fvf_paste_categorie");
    $tpl->parse("LANGUAGE_NEW_MESSAGE_INFO", "fvf_new_message_info");
    $tpl->parse("LANGUAGE_ADMINISTRATED_FROM", "fvf_administrated_from");
    $tpl->parse("LANGUAGE_CREATED_FROM", "fvf_created_from");
    $tpl->parse("LANGUAGE_CREATED_ON", "fvf_created_on");
    $tpl->parse("LANGUAGE_HEADER_CATEGORIE", "fvf_header_categorie");
    $tpl->parse("LANGUAGE_HEADER_MESSAGE", "fvf_header_message");
    $tpl->parse("LANGUAGE_HEADER_LAST_POST_INFO", "fvf_header_last_post_info");
    $tpl->parse("LANGUAGE_LAST_POST_CREATED_FROM", "fvf_last_post_created_from");
    $tpl->parse("LANGUAGE_SITE_TITLE", "fvf_site_title");
    $tpl->parse("LANGUAGE_NO_ACCESS", "fvf_no_access");
    $tpl->parse("LANGUAGE_NO_ENTRIES", "fvf_no_entries");

    $tpl->parse("OUT", $current_file);
    $out = $tpl->get_var("OUT");

    $fp = fopen("$tmp_doc_root/templates/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);

    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $tmp_doc_root/templates/$language/$current_file.ihtml)<br>");

     //*******************************************************************
    //* forum_view_category.ihtml
    //*******************************************************************

    $current_file = "forum_view_category";

    $tpl->set_file($current_file, "$current_file.ihtml");
    $tpl->set_block("language", "fvc_action_edit");
    $tpl->set_block("language", "fvc_action_delete");
    $tpl->set_block("language", "fvc_action_delete_confirm");
    $tpl->set_block("language", "fvc_add_message");
    $tpl->set_block("language", "fvc_administrated_from");
    $tpl->set_block("language", "fvc_categorie_action_edit");
    $tpl->set_block("language", "fvc_categorie_action_delete");
    $tpl->set_block("language", "fvc_created_from");
    $tpl->set_block("language", "fvc_created_on");
    $tpl->set_block("language", "fvc_header_categorie");
    $tpl->set_block("language", "fvc_posted_on");
    $tpl->set_block("language", "fvc_script_confirm_1");
    $tpl->set_block("language", "fvc_script_confirm_2");
    $tpl->set_block("language", "fvc_site_title");
    $tpl->set_block("language", "fvc_to_the_top");
    $tpl->set_block("language", "fvc_no_access");
    $tpl->set_block("language", "fvc_no_entries");
    $tpl->set_block("language", "fvc_delete_annotation");
    $tpl->set_block("language", "fvc_edit_annotation");
    $tpl->set_var(array(
       "DOC_ROOT" => $config_webserver_ip,
       "BODY_ON_LOAD" => "if (document.form_blueprint.title) document.form_blueprint.title.focus();"
    ));

    $tpl->parse("LANGUAGE_ACTION_EDIT", "fvc_action_edit");
    $tpl->parse("LANGUAGE_ACTION_DELETE", "fvc_action_delete");
    $tpl->parse("LANGUAGE_ACTION_DELETE_CONFIRM", "fvc_action_delete_confirm");
    $tpl->parse("LANGUAGE_ADD_MESSAGE", "fvc_add_message");
    $tpl->parse("LANGUAGE_ADMINISTRATED_FROM", "fvc_administrated_from");
    $tpl->parse("LANGUAGE_CAT_ACTION_EDIT", "fvc_categorie_action_edit");
    $tpl->parse("LANGUAGE_CAT_ACTION_DELETE", "fvc_categorie_action_delete");
    $tpl->parse("LANGUAGE_CREATED_FROM", "fvc_created_from");
    $tpl->parse("LANGUAGE_CREATED_ON", "fvc_created_on");
    $tpl->parse("LANGUAGE_HEADER_CATEGORIE", "fvc_header_categorie");
    $tpl->parse("LANGUAGE_POSTED_ON", "fvc_posted_on");
    $tpl->parse("LANGUAGE_SCRIPT_CONFIRM_1", "fvc_script_confirm_1");
    $tpl->parse("LANGUAGE_SCRIPT_CONFIRM_2", "fvc_script_confirm_2");
    $tpl->parse("LANGUAGE_SITE_TITLE", "fvc_site_title");
    $tpl->parse("LANGUAGE_TO_THE_TOP", "fvc_to_the_top");
    $tpl->parse("LANGUAGE_NO_ACCESS", "fvc_no_access");
    $tpl->parse("LANGUAGE_NO_ENTRIES", "fvc_no_entries");
    $tpl->parse("LANGUAGE_DELETE_ANNOTATION", "fvc_delete_annotation");
    $tpl->parse("LANGUAGE_EDIT_ANNOTATION", "fvc_edit_annotation");

    $tpl->parse("OUT", $current_file);
    $out = $tpl->get_var("OUT");

    $fp = fopen("$tmp_doc_root/templates/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);

    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $tmp_doc_root/templates/$language/$current_file.ihtml)<br>");


    //*******************************************************************
    //* forum_add_categorie.ihtml
    //*******************************************************************

    $current_file = "forum_add_categorie";

    $tpl->set_file($current_file, "$current_file.ihtml");
    $tpl->set_block("language", "fac_button_cancel");
    $tpl->set_block("language", "fac_button_label_save");
    $tpl->set_block("language", "fac_error");
    $tpl->set_block("language", "fac_error_content");
    $tpl->set_block("language", "fac_error_description");
    $tpl->set_block("language", "fac_error_title");
    $tpl->set_block("language", "fac_expert_formatation");
    $tpl->set_block("language", "fac_form_content");
    $tpl->set_block("language", "fac_form_description");
    $tpl->set_block("language", "fac_form_title");
    $tpl->set_block("language", "fac_site_form");
    $tpl->set_block("language", "fac_site_title");
    $tpl->set_block("language", "fac_no_access");
    $tpl->set_block("language", "fac_error_not_logged_in");

    $tpl->set_var(array(
       "DOC_ROOT" => $config_webserver_ip,
       "BODY_ON_LOAD" => "{BODY_ON_LOAD}"
    ));

    $tpl->parse("LANGUAGE_BUTTON_CANCEL", "fac_button_cancel");
    $tpl->parse("LANGUAGE_BUTTON_LABEL_SAVE", "fac_button_label_save");
    $tpl->parse("LANGUAGE_ERROR", "fac_error");
    $tpl->parse("LANGUAGE_ERROR_CONTENT","fac_error_content");
    $tpl->parse("LANGUAGE_ERROR_DESCRIPTION", "fac_error_description");
    $tpl->parse("LANGUAGE_ERROR_TITLE","fac_error_title");
    $tpl->parse("LANGUAGE_FORM_EXPERT_FORMAT", "fac_expert_formatation");
    $tpl->parse("LANGUAGE_FORM_CONTENT", "fac_form_content");
    $tpl->parse("LANGUAGE_FORM_DESCRIPTION", "fac_form_description");
    $tpl->parse("LANGUAGE_FORM_TITLE", "fac_form_title");
    $tpl->parse("LANGUAGE_SITE_FORM", "fac_site_form");
    $tpl->parse("LANGUAGE_SITE_TITLE", "fac_site_title");
    $tpl->parse("LANGUAGE_NO_ACCESS", "fac_no_access");
    $tpl->parse("LANGUAGE_ERROR_NOT_LOGGED_IN", "fac_error_not_logged_in");

    $tpl->parse("CONTENT", $current_file);
    $tpl->parse("OUT", "blueprint");

    $out = $tpl->get_var("OUT");

    $tpl->unset_var(array("BUTTON_LABEL", "BUTTON_MISSION", "BUTTON_URL"));

    $fp = fopen("$tmp_doc_root/templates/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);

    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $tmp_doc_root/templates/$language/$current_file.ihtml)<br>");


     //*******************************************************************
    //* forum_edit_categorie.ihtml
    //*******************************************************************

    $current_file = "forum_edit_categorie";

    $tpl->set_file($current_file, "forum_add_categorie.ihtml");
    $tpl->set_block("language", "fec_button_cancel");
    $tpl->set_block("language", "fec_button_label_save");
    $tpl->set_block("language", "fec_error");
    $tpl->set_block("language", "fec_error_content");
    $tpl->set_block("language", "fec_error_description");
    $tpl->set_block("language", "fec_error_title");
    $tpl->set_block("language", "fec_form_content");
    $tpl->set_block("language", "fec_form_description");
    $tpl->set_block("language", "fec_form_title");
    $tpl->set_block("language", "fec_site_form");
    $tpl->set_block("language", "fec_site_title");
    $tpl->set_block("language", "fec_no_access");
    $tpl->set_block("language", "fec_error_not_logged_in");

    $tpl->set_var(array(
       "DOC_ROOT" => $config_webserver_ip,
       "BODY_ON_LOAD" => "{BODY_ON_LOAD}"
    ));

    $tpl->parse("LANGUAGE_BUTTON_CANCEL", "fec_button_cancel");
    $tpl->parse("LANGUAGE_BUTTON_LABEL_SAVE", "fec_button_label_save");
    $tpl->parse("LANGUAGE_ERROR", "fec_error");
    $tpl->parse("LANGUAGE_ERROR_CONTENT", "fec_error_content");
    $tpl->parse("LANGUAGE_ERROR_DESCRIPTION", "fec_error_description");
    $tpl->parse("LANGUAGE_ERROR_TITLE","fec_error_title");
    $tpl->parse("LANGUAGE_FORM_CONTENT", "fec_form_content");
    $tpl->parse("LANGUAGE_FORM_DESCRIPTION", "fec_form_description");
    $tpl->parse("LANGUAGE_FORM_TITLE", "fec_form_title");
    $tpl->parse("LANGUAGE_SITE_FORM", "fec_site_form");
    $tpl->parse("LANGUAGE_SITE_TITLE", "fec_site_title");
    $tpl->parse("LANGUAGE_NO_ACCESS", "fec_no_access");
    $tpl->parse("LANGUAGE_ERROR_NOT_LOGGED_IN", "fec_error_not_logged_in");

    $tpl->parse("CONTENT", $current_file);
    $tpl->parse("OUT", "blueprint");

    $out = $tpl->get_var("OUT");

    $tpl->unset_var(array("BUTTON_LABEL", "BUTTON_MISSION", "BUTTON_URL"));

    $fp = fopen("$tmp_doc_root/templates/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);

    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $tmp_doc_root/templates/$language/$current_file.ihtml)<br>");

    //*******************************************************************
    //* forum_add_forum.ihtml
    //*******************************************************************

    $current_file = "forum_add_forum";

    $tpl->set_file($current_file, "$current_file.ihtml");
    $tpl->set_block("language", "faf_button_cancel");
    $tpl->set_block("language", "faf_button_label_save");
    $tpl->set_block("language", "faf_error");
    $tpl->set_block("language", "faf_error_title");
    $tpl->set_block("language", "faf_expert_formatation");
    $tpl->set_block("language", "faf_form_description");
    $tpl->set_block("language", "faf_form_subtitle");
    $tpl->set_block("language", "faf_form_title");
    $tpl->set_block("language", "faf_site_form");
    $tpl->set_block("language", "faf_site_title");
    $tpl->set_block("language", "faf_no_access");
    $tpl->set_block("language", "faf_error_not_logged_in");

    $tpl->set_var(array(
       "DOC_ROOT" => $config_webserver_ip,
       "BODY_ON_LOAD" => "{BODY_ON_LOAD}"
    ));

    $tpl->parse("LANGUAGE_BUTTON_CANCEL", "faf_button_cancel");
    $tpl->parse("LANGUAGE_BUTTON_LABEL_SAVE", "faf_button_label_save");
    $tpl->parse("LANGUAGE_ERROR", "faf_error");
    $tpl->parse("LANGUAGE_ERROR_TITLE","faf_error_title");
    $tpl->parse("LANGUAGE_FORM_EXPERT_FORMAT", "faf_expert_formatation");
    $tpl->parse("LANGUAGE_FORM_DESCRIPTION", "faf_form_description");
    $tpl->parse("LANGUAGE_FORM_SUBTITLE", "faf_form_subtitle");
    $tpl->parse("LANGUAGE_FORM_TITLE", "faf_form_title");
    $tpl->parse("LANGUAGE_SITE_FORM", "faf_site_form");
    $tpl->parse("LANGUAGE_SITE_TITLE", "faf_site_title");
    $tpl->parse("LANGUAGE_NO_ACCESS", "faf_no_access");
    $tpl->parse("LANGUAGE_ERROR_NOT_LOGGED_IN", "faf_error_not_logged_in");

    $tpl->parse("CONTENT", $current_file);
    $tpl->parse("OUT", "blueprint");
    $out = $tpl->get_var("OUT");

    $tpl->unset_var(array("BUTTON_LABEL", "BUTTON_MISSION", "BUTTON_URL"));

    $fp = fopen("$tmp_doc_root/templates/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);

    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $tmp_doc_root/templates/$language/$current_file.ihtml)<br>");

    //*******************************************************************
    //* forum_edit_forum.ihtml
    //*******************************************************************

    $current_file = "forum_edit_forum";

    $tpl->set_file($current_file, "forum_add_forum.ihtml");
    $tpl->set_block("language", "fef_button_cancel");
    $tpl->set_block("language", "fef_button_label_save");
    $tpl->set_block("language", "fef_error");
    $tpl->set_block("language", "fef_error_title");
    $tpl->set_block("language", "fef_form_description");
    $tpl->set_block("language", "fef_form_subtitle");
    $tpl->set_block("language", "fef_form_title");
    $tpl->set_block("language", "fef_site_form");
    $tpl->set_block("language", "fef_site_title");
    $tpl->set_block("language", "fef_no_access");
    $tpl->set_block("language", "fef_error_not_logged_in");

    $tpl->set_var(array(
       "DOC_ROOT" => $config_webserver_ip,
       "BODY_ON_LOAD" => "{BODY_ON_LOAD}"
    ));

    $tpl->parse("LANGUAGE_BUTTON_CANCEL", "fef_button_cancel");
    $tpl->parse("LANGUAGE_BUTTON_LABEL_SAVE", "fef_button_label_save");
    $tpl->parse("LANGUAGE_ERROR", "fef_error");
    $tpl->parse("LANGUAGE_ERROR_TITLE","fef_error_title");
    $tpl->parse("LANGUAGE_FORM_DESCRIPTION", "fef_form_description");
    $tpl->parse("LANGUAGE_FORM_SUBTITLE", "fef_form_subtitle");
    $tpl->parse("LANGUAGE_FORM_TITLE", "fef_form_title");
    $tpl->parse("LANGUAGE_SITE_FORM", "fef_site_form");
    $tpl->parse("LANGUAGE_SITE_TITLE", "fef_site_title");
    $tpl->parse("LANGUAGE_NO_ACCESS", "fef_no_access");
    $tpl->parse("LANGUAGE_ERROR_NOT_LOGGED_IN", "fef_error_not_logged_in");

    $tpl->parse("CONTENT", $current_file);
    $tpl->parse("OUT", "blueprint");
    $out = $tpl->get_var("OUT");

    $tpl->unset_var(array("BUTTON_LABEL", "BUTTON_MISSION", "BUTTON_URL"));

    $fp = fopen("$tmp_doc_root/templates/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);

    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $tmp_doc_root/templates/$language/$current_file.ihtml)<br>");


    //*******************************************************************
    //* forum_add_message.ihtml
    //*******************************************************************

    $current_file = "forum_add_message";

    $tpl->set_file($current_file, "$current_file.ihtml");
    $tpl->set_block("language", "fam_button_cancel");
    $tpl->set_block("language", "fam_button_label_save");
    $tpl->set_block("language", "fam_error");
    $tpl->set_block("language", "fam_error_content");
    $tpl->set_block("language", "fam_error_title");
    $tpl->set_block("language", "fam_expert_formatation");
    $tpl->set_block("language", "fam_form_content");
    $tpl->set_block("language", "fam_form_title");
    $tpl->set_block("language", "fam_site_form");
    $tpl->set_block("language", "fam_site_title");
    $tpl->set_block("language", "fam_no_access");
    $tpl->set_block("language", "fam_error_not_logged_in");

    $tpl->set_var(array(
       "DOC_ROOT" => $config_webserver_ip,
       "BODY_ON_LOAD" => "{BODY_ON_LOAD}"
    ));

    $tpl->parse("LANGUAGE_BUTTON_CANCEL", "fam_button_cancel");
    $tpl->parse("LANGUAGE_BUTTON_LABEL_SAVE", "fam_button_label_save");
    $tpl->parse("LANGUAGE_ERROR", "fam_error");
    $tpl->parse("LANGUAGE_ERROR_CONTENT","fam_error_content");
    $tpl->parse("LANGUAGE_ERROR_TITLE","fam_error_title");
    $tpl->parse("LANGUAGE_FORM_EXPERT_FORMAT", "fam_expert_formatation");
    $tpl->parse("LANGUAGE_FORM_CONTENT", "fam_form_content");
    $tpl->parse("LANGUAGE_FORM_TITLE", "fam_form_title");
    $tpl->parse("LANGUAGE_SITE_FORM", "fam_site_form");
    $tpl->parse("LANGUAGE_SITE_TITLE", "fam_site_title");
    $tpl->parse("LANGUAGE_NO_ACCESS", "fam_no_access");
    $tpl->parse("LANGUAGE_ERROR_NOT_LOGGED_IN", "fam_error_not_logged_in");

    $tpl->parse("CONTENT", $current_file);
    $tpl->parse("OUT", "blueprint");
    $out = $tpl->get_var("OUT");

    $tpl->unset_var(array("BUTTON_LABEL", "BUTTON_MISSION", "BUTTON_URL"));

    $fp = fopen("$tmp_doc_root/templates/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);

    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $tmp_doc_root/templates/$language/$current_file.ihtml)<br>");

    //*******************************************************************
    //* forum_edit_message.ihtml
    //*******************************************************************

    $current_file = "forum_edit_message";

    $tpl->set_file($current_file, "forum_add_message.ihtml");
    $tpl->set_block("language", "fem_button_cancel");
    $tpl->set_block("language", "fem_button_label_save");
    $tpl->set_block("language", "fem_error");
    $tpl->set_block("language", "fem_error_content");
    $tpl->set_block("language", "fem_error_description");
    $tpl->set_block("language", "fem_error_title");
    $tpl->set_block("language", "fem_form_content");
    $tpl->set_block("language", "fem_form_description");
    $tpl->set_block("language", "fem_form_title");
    $tpl->set_block("language", "fem_site_form");
    $tpl->set_block("language", "fem_site_title");
    $tpl->set_block("language", "fem_no_access");
    $tpl->set_block("language", "fem_error_not_logged_in");

    $tpl->set_var(array(
       "DOC_ROOT" => $config_webserver_ip,
       "BODY_ON_LOAD" => "{BODY_ON_LOAD}"
    ));

    $tpl->parse("LANGUAGE_BUTTON_CANCEL", "fem_button_cancel");
    $tpl->parse("LANGUAGE_BUTTON_LABEL_SAVE", "fem_button_label_save");
    $tpl->parse("LANGUAGE_ERROR", "fem_error");
    $tpl->parse("LANGUAGE_ERROR_DESCRIPTION","fem_error_description");
    $tpl->parse("LANGUAGE_ERROR_TITLE","fem_error_title");
    $tpl->parse("LANGUAGE_ERROR_CONTENT", "fem_error_content");
    $tpl->parse("LANGUAGE_FORM_CONTENT", "fem_form_content");
    $tpl->parse("LANGUAGE_FORM_DESCRIPTION", "fem_form_description");
    $tpl->parse("LANGUAGE_FORM_TITLE", "fem_form_title");
    $tpl->parse("LANGUAGE_SITE_FORM", "fem_site_form");
    $tpl->parse("LANGUAGE_SITE_TITLE", "fem_site_title");
    $tpl->parse("LANGUAGE_NO_ACCESS", "fem_no_access");
    $tpl->parse("LANGUAGE_ERROR_NOT_LOGGED_IN", "fem_error_not_logged_in");

    $tpl->parse("CONTENT", $current_file);
    $tpl->parse("OUT", "blueprint");
    $out = $tpl->get_var("OUT");

    $tpl->unset_var(array("BUTTON_LABEL", "BUTTON_MISSION", "BUTTON_URL"));

    $fp = fopen("$tmp_doc_root/templates/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);

    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $tmp_doc_root/templates/$language/$current_file.ihtml)<br>");
  }
?>
