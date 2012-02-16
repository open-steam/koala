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

  $tmp_doc_root = "$config_doc_root/modules/search";
 

  //get list of all language directories
  $languages = array();
  $handle = opendir ('.');
  while (false !== ($file = readdir ($handle)))
  {
    if(is_dir($file) && $file != "." && $file != ".." && $file != "CVS" && $file != ".svn")
      array_push($languages, $file);
  }
  closedir($handle);


  echo("<br><br><hr><b>MODULE: Suche</b><hr>");

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
    $tpl->set_var("DOC_ROOT", $config_webserver_ip);
    $tpl->set_var("CONFIG_DOC_ROOT", $config_doc_root);

    //set template root dir back to the general design
    $tpl->set_root("$tmp_doc_root/language");

    echo("<br><br>Sprache: $language<br>");


    //*******************************************************************
    //* search_mask.ihtml
    //*******************************************************************

    $current_file = "search_mask";

    $tpl->set_file($current_file, "$current_file.ihtml");
    $tpl->set_block("language", "search_search");
    $tpl->set_block("language", "search_commitsearch");
    $tpl->set_block("language", "search_cancel");
    $tpl->set_block("language", "search_keyword");
    $tpl->set_block("language", "search_fulltext");
    $tpl->set_block("language", "search_options");
    $tpl->set_block("language", "search_everything");
    $tpl->set_block("language", "search_portals");
    $tpl->set_block("language", "search_users");
    $tpl->set_block("language", "search_groups");
    $tpl->set_block("language", "search_toofewcharacters");
    $tpl->set_block("language", "search_noresults");
 
    $tpl->set_var(array(
       "DOC_ROOT" => $config_webserver_ip
    ));

    $tpl->parse("TITLE", "search_search");
    $tpl->parse("BUTTON_SEARCH", "search_commitsearch");
    $tpl->parse("BUTTON_CLOSE", "search_cancel");
    $tpl->parse("KEYWORD", "search_keyword");
    $tpl->parse("FULLTEXT", "search_fulltext");
    $tpl->parse("SEARCHOPTIONS", "search_options");
    $tpl->parse("SEARCHEVERYTHING", "search_everything");
    $tpl->parse("SEARCHPORTALS", "search_portals");
    $tpl->parse("SEARCHUSERS", "search_users");
    $tpl->parse("SEARCHGROUPS", "search_groups");
    $tpl->parse("TOOFEWCHARACTERS", "search_toofewcharacters");
    $tpl->parse("NORESULTS", "search_noresults");

    $tpl->parse("OUT", $current_file);
    $out = $tpl->get_var("OUT");

    $fp = fopen("$tmp_doc_root/templates/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);

    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $tmp_doc_root/templates/$language/$current_file.ihtml)<br>");

    //*******************************************************************
    //* search_results.ihtml
    //*******************************************************************

    $current_file = "search_results";

    $tpl->set_file($current_file, "$current_file.ihtml");
    $tpl->set_block("language", "search_searchresults");
    $tpl->set_block("language", "search_resultsclose");
    $tpl->set_block("language", "search_newsearch");
    $tpl->set_block("language", "search_previouspage");
    $tpl->set_block("language", "search_nextpage");
    $tpl->set_block("language", "search_objectdescription");
    $tpl->set_block("language", "search_created");
    $tpl->set_block("language", "search_lastchange");
    $tpl->set_block("language", "search_useridcard");
    $tpl->set_block("language", "search_groupidcard");
    
    $tpl->set_var(array(
       "DOC_ROOT" => $config_webserver_ip
    ));

    $tpl->parse("TITLE", "search_searchresults");
    $tpl->parse("BUTTON_NEW_SEARCH", "search_newsearch");
    $tpl->parse("BUTTON_CLOSE", "search_resultsclose");
    $tpl->parse("PREVIOUSPAGE", "search_previouspage");
    $tpl->parse("NEXTPAGE", "search_nextpage");
    $tpl->parse("OBJECTDESCRIPTION", "search_objectdescription");
    $tpl->parse("OBJECTCREATED", "search_created");
    $tpl->parse("OBJECTLASTCHANGE", "search_lastchange");
    $tpl->parse("IDCARDUSER", "search_useridcard");
    $tpl->parse("IDCARDGROUP", "search_useridcard");

    $tpl->parse("OUT", $current_file);
    $out = $tpl->get_var("OUT");

    $fp = fopen("$tmp_doc_root/templates/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);

    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $tmp_doc_root/templates/$language/$current_file.ihtml)<br>");

  }
?>
