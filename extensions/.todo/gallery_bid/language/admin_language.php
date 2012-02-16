<?php

  /****************************************************************************
  admin_language.php - build the language dependent templates of the gallery 
  module
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

  Author: Moritz Boedicker
  EMail: moritz@upb.de

  ****************************************************************************/
//  require_once("./config/config.php");
//  require_once("$config_doc_root/classes/template.inc");
//  require_once("$config_doc_root/classes/debugHelper.php");

  $tmp_doc_root = "$config_doc_root/modules/gallery";


  //get list of all language directories
  $languages = array();
  $handle = opendir ('.');
  while (false !== ($file = readdir ($handle)))
  {
    if(is_dir($file) && $file != "." && $file != ".." && $file != "CVS" && $file != ".svn")
      array_push($languages, $file);
  }
  closedir($handle);


  echo("<br><br><hr><b>MODULE: Gallery</b><hr>");

  //Parse all templates
  foreach($languages as $language)
  {
    //check whether language dir existents
    @mkdir("$tmp_doc_root/templates/$language");


    //get language file
    $tpl = new Template("$tmp_doc_root/language/$language", "keep");
    $tpl->set_file("language", "language.ihtml");

    //get blueprint for dialog
    //$tpl->set_root("$tmp_doc_root/language");
    //$tpl->set_file("blueprint", "dialog_blueprint.ihtml");
    
	
	$tpl->set_var("DOC_ROOT", $config_webserver_ip);

    //set template root dir back to the general design
    $tpl->set_root("$tmp_doc_root/language");

    echo("<br><br>Sprache: $language<br>");


    //*******************************************************************
    //* gallery.ihtml
    //*******************************************************************

    $current_file = "gallery";

    $tpl->set_file($current_file, "$current_file.ihtml");
    $tpl->set_block("language", "gallery_image_properties_button");
    $tpl->set_block("language", "gallery_remove_image_button");
    $tpl->set_block("language", "gallery_image_fullscreen_button");
    $tpl->set_block("language", "gallery_image_save_button");

    $tpl->set_var(array(
      "FORM_NAME" => "gallery",
      "FORM_URL" => "./index.php?object={ENVIRONMENT_ID}",
      "DOC_ROOT" => $config_webserver_ip
    ));

    $tpl->parse("LANGUAGE_IMAGE_PROPERTIES_BUTTON", "gallery_image_properties_button");
    $tpl->parse("LANGUAGE_REMOVE_IMAGE_BUTTON", "gallery_remove_image_button");
    $tpl->parse("LANGUAGE_IMAGE_FULLSCREEN_BUTTON", "gallery_image_fullscreen_button");
    $tpl->parse("LANGUAGE_IMAGE_SAVE_BUTTON", "gallery_image_save_button");

    $tpl->parse("OUT", $current_file);
    $out = $tpl->get_var("OUT");

    $fp = fopen("$tmp_doc_root/templates/$language/$current_file.ihtml", "w");
    fwrite($fp, $out);

    fclose($fp);

    echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $tmp_doc_root/templates/$language/$current_file.ihtml)<br>");
	
  }
?>
