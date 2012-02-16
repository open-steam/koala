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

  Author: Thorsten Schaefer
  EMail: tms82@upb.de

  ****************************************************************************/

  $tmp_doc_root = "$config_doc_root/javascript/tinymce";
  $tpl = new Template("$tmp_doc_root/language", "keep");

  echo("<br><br><hr><b>MODULE: TinyMCE</b><hr>");


  //*******************************************************************
  //* fullscreen.ihtml
  //*******************************************************************

  $current_file = "fullscreen";

  $tpl->set_file($current_file, "$current_file.ihtml");

  $tpl->set_var(array(
    "DOC_ROOT" => $config_webserver_ip,
    "CGI_ROOT" => $config_cgi_ip
  ));

  $tpl->parse("OUT", $current_file);
  $out = $tpl->get_var("OUT");

  $fp = fopen("$tmp_doc_root/jscripts/tiny_mce/plugins/fullscreen/$current_file.htm", "w");
  fwrite($fp, $out);

  fclose($fp);

  echo("&nbsp;&nbsp;&nbsp; $current_file.ihtml abgeschlossen. (... $tmp_doc_root/jscripts/tiny_mce/plugins/fullscreen/$current_file.htm)<br>");

?>
