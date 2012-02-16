<?php

  /****************************************************************************
  derive_menu.php - derive a menu in regard of the current module and access to the system
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

  function derive_menu($page, $object, $path = "", $access = 3)
  {
    global $language;
    global $treeview;
    global $treeview_mini;
    global $show_hidden;
    global $config_doc_root;
    global $config_webserver_ip;
    global $config_server_ip;
    global $steam;

    if(!class_exists("Template"))
      require_once("$config_doc_root/classes/template.inc");

    if($access > 1)
      $block = "menu_spec_write";
    else
      $block = "menu_spec_read";

    if($access == 4)
      $block = "menu_spec_annotate";

    $menu_tpl = new Template("$config_doc_root/templates/$language", "keep");
    $menu_tpl->set_file("menu", "menu.ihtml");
    $menu_tpl->set_block("menu", "blueprint");
    $menu_tpl->set_block("menu", $page);
    $menu_tpl->set_block($page, $block);

    $menu_tpl->set_block("menu", "general_menu");

    $menu_tpl->set_var(array(
      "DUMMY" => "",
      "ROOTDIR" => $config_webserver_ip,
      "OBJECT_ID" => $object->get_id(),
      "OBJECT_CLASS" => "", #$object->class,
      "OBJECT_PATH" => $path,
      "USER_NAME" => $steam->get_login_user()->get_name(),
      "STEAM_SERVER" => $config_server_ip
    ));

    //parse specific menu
    $menu_tpl->parse("SPECIFIC_MENU", $block, 1);

    $menu_tpl->parse("SPECIFIC_MENU", "general_menu", 1);

    $menu_tpl->parse("OUT", "blueprint");
    return $menu_tpl->get_var("OUT");

  }

  function derive_menu_titles($page)
  {
    if(!class_exists("Template"))
      require_once("./classes/template.inc");

    global $language;

    $menu_tpl = new Template("./templates/$language", "keep");
    $menu_tpl->set_file("menu", "menu.ihtml");
    $menu_tpl->set_block("menu", $page);
    $menu_tpl->set_block($page, "menu_1");
    $menu_tpl->set_block($page, "menu_2");

    return array(
      $menu_tpl->get_var("menu_1"),
      $menu_tpl->get_var("menu_2")
    );
  }

?>