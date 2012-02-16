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
  require_once("../../../../config/config.php");
  require_once("$steamapi_doc_root/steam_connector.class.php");
  require_once("$config_doc_root/classes/template.inc");
  require_once("$config_doc_root/includes/sessiondata.php");
  require_once("$config_doc_root/classes/UBBCode.php");


  //******************************************************
  //** sTeam Stuff
  //******************************************************
  $object = (int) (isset($_GET["object"]))?trim($_GET["object"]):0;

  //login und $steam def. in "./includes/login.php"
  $steam = new steam_connector(	$config_server_ip, $config_server_port, $login_name, $login_pwd);

  if( !$steam || !$steam->get_login_status() ) {
    echo("*** Login fehlgeschlagen! ***<br>");
    exit();
  }

  $UBB = new UBBCode();

  // if ID has been properly specified => download rss feed by calling /scripts/rss.pike and deliver output
  if( (int) $object != 0 )
    $current_room = steam_factory::get_object( $steam, $object );
  else {
    echo "This script can only render a valid RSS feed if you provide a valid ObjectID.<br/>";
    echo "Example: " . $config_webserver_ip . "/modules/portal2/portlets/msg/rss.php?object=" . $steam->get_login_user()->get_workroom()->get_id();
    exit;
  }
  
  if ($current_room->check_access_read($steam->get_login_user())) {

    // Get room's attributes and store them
    if ($current_room->get_attribute("OBJ_DESC"))
      $feed_title = $current_room->get_attribute("OBJ_DESC");
    else
      $feed_title = $current_room->get_name();
    $feed_description = $feed_title;
    $feed_link = $config_webserver_ip . $current_room->get_environment()->get_environment()->get_path();

    // Get inventory and store all relevant attributes in array entries
    //$inventory = $current_room->get_inventory("", array ("OBJ_OWNER"));
    $inventory = $current_room->get_inventory();
    $rss_items = array();
    foreach ($inventory as $item) {
      if ($item->get_attribute("DOC_MIME_TYPE") == "text/plain") {
        $item_title = '<title>' . $item->get_name() . '</title>';

        $item_content = $item->get_content();
        $item_image = $item->get_attribute("bid:portlet:msg:picture_id");
        if ($item_image)
          $item_content = $item_content . '<div><img src="' . $config_webserver_ip . '/tools/get.php?object=' . $item_image . '" /></div>';

        $item_description = '<description><![CDATA[' . $item_content . ']]></description>';

        $item_link = $item->get_attribute("bid:portlet:msg:link_url");
        if ($item_link == ' ')
          $item_link = $feed_link;
        $item_link = '<link>' . $feed_link . '</link>';

        $lastchanged = $item->get_attribute(DOC_LAST_MODIFIED);
        if ($lastchanged === 0) {
          $lastchanged = $item->get_attribute(OBJ_CREATION_TIME);
        }
        $item_pubDate = '<pubDate>' . strftime("%a, %d %b %Y %H:%M:%S GMT", $lastchanged) . '</pubDate>';

        // $author = '<author>' . $item->get_attribute("OBJ_OWNER") . '</author>';
        $item_guid = '<guid>' . $config_webserver_ip . '/index.php?object=' . $item->get_id() . '</guid>';

        array_push ($rss_items, $item_title . $item_description . $item_link . $item_pubDate . $item_guid);
      }
    }

    header('Content-Type: text/xml');
    header('Cache-Control: private');
    header('Cache-Control: must-revalidate');
    // header("Content-Length: " . 0); // do something sensible here?
    header("Pragma: public");
    header('Connection: close');
    header("Content-Disposition: inline; filename=rss_feed.rss");

    echo "<?xml version='1.0' encoding='utf-8'?>\n";
    echo "<rss version='2.0'>\n";
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
