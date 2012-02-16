<?php

  /****************************************************************************
  rss.php - script to download rss feed content from steam server
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

  Author: Harald Selke

  ****************************************************************************/

  //******************************************************
  //** include stuff
  //******************************************************
  require_once("../config/config.php");
  require_once("$steamapi_doc_root/steam_connector.class.php");
  require_once("$config_doc_root/classes/template.inc");
  require_once("$config_doc_root/includes/sessiondata.php");


  //******************************************************
  //** sTeam Stuff
  //******************************************************
  $object = (int) (isset($_GET["object"]))?trim($_GET["object"]):0;

  $steam = new steam_connector(	$config_server_ip, $config_server_port, $login_name, $login_pwd);

  if( !$steam || !$steam->get_login_status() ) {
    echo("*** Login fehlgeschlagen! ***<br>");
    exit();
  }

  // if ID has been properly specified => download rss feed by calling /scripts/rss.pike and deliver output
  if( (int) $object != 0 )
    $current_room = steam_factory::get_object( $steam, $object );
  else {
    echo "This script can only render a valid RSS feed if you provide a valid ObjectID.<br/>";
    echo "Example: " . $config_webserver_ip . "/tools/rss.php?object=" . $steam->get_login_user()->get_workroom()->get_id();
    exit;
  }

  $config_webserver_ip = str_replace("https://", "http://", $config_webserver_ip);

  if ($current_room->check_access_read($steam->get_login_user())) {

    // Get room's attributes and store them
    if ($current_room->get_attribute("OBJ_DESC"))
      $feed_title = $current_room->get_attribute("OBJ_DESC");
    else
      $feed_title = $current_room->get_name();
    $feed_description = $feed_title;
    $feed_link = $config_webserver_ip . $current_room->get_path();

    // Get inventory and store all relevant attributes in array entries
    //$inventory = $current_room->get_inventory("", array ("OBJ_OWNER"));
    $inventory = $current_room->get_inventory();
    $rss_items = array();
    foreach ($inventory as $item) {
      if ($item->get_attribute("OBJ_DESC"))
        $display_name = $item->get_attribute("OBJ_DESC");
      else
        $display_name = $item->get_name();
      $item_title = '<title>' . $display_name . '</title>';
      $item_description = '<description>' . $display_name . '</description>';
      $item_link = '<link>' . $config_webserver_ip . $item->get_path() . '</link>';

      $lastchanged = $item->get_attribute(DOC_LAST_MODIFIED);
      if ($lastchanged === 0) {
        $lastchanged = $item->get_attribute(OBJ_CREATION_TIME);
      }
      $item_pubDate = '<pubDate>' . strftime("%a, %d %b %Y %H:%M:%S GMT", $lastchanged) . '</pubDate>';

      // $author = '<author>' . $item->get_attribute("OBJ_OWNER") . '</author>';
      $item_guid = '<guid>' . $config_webserver_ip . '/index.php?object=' . $item->get_id() . '</guid>';

      $item_mime_type = $item->get_attribute("DOC_MIME_TYPE");
      if ($item_mime_type)
        $item_enclosure = '<enclosure url="' . $config_webserver_ip . '/download/' . $item->get_id() . '/' . $item->get_name() . '" length="' . $item->get_attribute("DOC_SIZE") . '" type="' . $item_mime_type . '" />';
      else 
        $item_enclosure = '';
      array_push ($rss_items, $item_title . $item_description . $item_link . $item_pubDate . $item_guid . $item_enclosure);
    }

    header('Content-Type: text/xml');
    header('Cache-Control: private');
    header('Cache-Control: must-revalidate');
    // header("Content-Length: " . 0); // do something sensible here?
    header('Pragma: public');
    header('Connection: close');
    header("Content-Disposition: inline; filename=rss_feed.rss");

    echo "<?xml version='1.0' encoding='utf-8'?>\n";
    echo "<rss version='2.0' xmlns:itunes='http://www.itunes.com/dtds/podcast-1.0.dtd'>\n";
    echo "<channel>\n";
    echo "<title>" . $feed_title . "</title>\n";
    echo "<description>" . $feed_description . "</description>\n";
    echo "<link>" . $feed_link . "</link>\n";
    echo "<generator>PHPsTeam/bid-owl 2.0</generator>\n";
    echo "<ttl>60</ttl>\n";

    echo "<image><url>" . $config_webserver_ip . "/icons/bid_Logo_neu.gif</url>\n";
    echo "<title>" . $feed_title . "</title>\n";
    echo "<description>" . $feed_description . "</description>\n";
    echo "<link>" . $feed_link . "</link>\n";
    echo "</image>\n";
    echo "<itunes:image href='" . $config_webserver_ip . "/icons/bid_Logo_neu.gif' />\n";
    echo "<itunes:category text=\"Education\" />\n";
    echo "<itunes:explicit>no</itunes:explicit>\n";

    foreach ($rss_items as $item)
      echo "<item>" . $item . "</item>\n";

    echo "</channel>\n";
    echo "</rss>\n";

  }
  else {
    echo "The access rights of the requested object do not allow you to read it.";
    exit;
  }

  //Logout & Disconnect
  $steam->disconnect();

?>
