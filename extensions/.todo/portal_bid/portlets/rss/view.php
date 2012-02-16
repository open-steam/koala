<?php
  /****************************************************************************
  view.php - view the rss portlet
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

  Author: Henrik Beige, Harald Selke
  EMail: hebeige@gmx.de, hase@uni-paderborn.de

  ****************************************************************************/

require_once('rss_fetch.inc');

$num_items = (isset($content["num_items"]))?$content["num_items"]:0;
$rss = fetch_rss(derive_url($content["address"]));
if ($num_items == 0)
  $items = $rss->items;
else
  $items = array_slice($rss->items, 0, $num_items);
$desc_length = (isset($content["desc_length"]))?$content["desc_length"]:0;
$allow_html = ($content["allow_html"]=="checked"?true:false);
$style = (isset($content["style"]))?$content["style"]:"rss_feed";

$tmpl = new Template("./portlets/rss/templates/$language", "keep");
$tmpl->set_file("content", "view.ihtml");
$tmpl->set_block("content", "edit_button", "DUMMY");
$tmpl->set_block("content", "feed_error", "DUMMY");
$tmpl->set_block("content", "itemurl", "DUMMY");
$tmpl->set_block("content", "rss_item", "RSS_ITEM");
$tmpl->set_var(array(
  "DUMMY" => "",
  "EDIT_BUTTON" => "",
  "PORTLET_ROOT" => "$config_webserver_ip/modules/portal2/portlets/rss",
  "PORTLET_ID" => $portlet->get_id(),
  "RSS_NAME" => $portlet_name,
  "RSS_STYLE" => $style
));

if ($portlet->check_access_write($steam->get_login_user()))
  $tmpl->parse("EDIT_BUTTON", "edit_button");

if(sizeof($content) > 0)
{
  if ($rss) {
    foreach($items as $item)
    {
      if ($allow_html) {
        $itemtitle = utf8_encode($item["title"]);
        $itemdesc = utf8_encode($item["description"]);
      }
      else {
        $itemtitle = utf8_encode(strip_tags($item["title"]));
        $itemdesc = utf8_encode(strip_tags($item["description"]));
      }
      if ($desc_length == 0)
        $itemdesc = "";
      else if ($desc_length > 0 && strlen($itemdesc) > $desc_length)
        $itemdesc = substr($itemdesc, 0, $desc_length) . "...";
      $tmpl->set_var(array(
        "ITEMTITLE" => $itemtitle,
        "ITEMDESC" => $itemdesc,
        "ITEMURL" => derive_url($item["link"]),
        "LINK" => ""
      ));
      $tmpl->parse("LINK", "itemurl");
      $tmpl->parse("RSS_ITEM", "rss_item", 1);
    }
    $tmpl->pparse("OUT", "content");
  }
  else {
    $tmpl->set_var(array(
      "RSS_ITEM" => ""
    ));
    $tmpl->parse("RSS_ITEM", "feed_error");
    $tmpl->pparse("OUT", "content");
  }
}
else
 echo("");

?>