<?php
  /****************************************************************************
  derive_icon.php - function to derive the icons URL in regard of a file
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

  Modifications by hase, 18.05.2005:
  Added several MIME types
  ****************************************************************************/

  function derive_icon($properties)
  {
    global $config_doc_root;
    global $config_webserver_ip;
    include("$config_doc_root/config/mimetype_map.php");

    //bidOWL:Collection Types
    $collectiontype = (isset($properties["bid:collectiontype"]) && is_string($properties["bid:collectiontype"]))?$properties["bid:collectiontype"]:"";
    if($collectiontype === "sequence")
      return "$config_webserver_ip/icons/mimetype/sequence.gif";
    else if($collectiontype === "cluster")
      return "$config_webserver_ip/icons/mimetype/cluster.gif";
    else if($collectiontype === "gallery")
	  return "$config_webserver_ip/icons/mimetype/gallery.gif";


    //bidOWL:Document Types
    $objtype = (isset($properties["obj_type"]))?$properties["obj_type"]:"";
    $doctype = (isset($properties["bid:doctype"]) && is_string($properties["bid:doctype"]))?$properties["bid:doctype"]:"";
    
    if($objtype === "container_portal_bid" || $doctype === "portal")
      return "$config_webserver_ip/icons/mimetype/portal.gif";
		else if ($objtype === "container_portlet_bid") 
      return "$config_webserver_ip/icons/mimetype/portlet.gif";
    else if($objtype === "LARS_DESKTOP")
      return "$config_webserver_ip/icons/mimetype/lars_desktop.gif";
    else if($doctype === "questionary")
      return "$config_webserver_ip/icons/mimetype/questionary.gif";
    else if ($objtype === "text_forumthread_bid")   
      return "$config_webserver_ip/icons/mimetype/forumthread.gif";

    //steam:Types
    $object = (isset($properties["object"]) && is_object($properties["object"]))?$properties["object"]:new steam_object(null);
			
    if($object instanceof steam_docextern)
      return "$config_webserver_ip/icons/mimetype/www.gif";
    else if($object instanceof steam_trashbin)
      return "$config_webserver_ip/icons/mimetype/trashbin.gif";
    else if($object instanceof steam_exit)
      return "$config_webserver_ip/icons/mimetype/exit.gif";
    else if($object instanceof steam_room && $properties["bid:presentation"] === "index")
      return "$config_webserver_ip/icons/mimetype/folder_closed_index.gif";
	else if($object instanceof steam_room)
      return "$config_webserver_ip/icons/mimetype/folder_closed.gif";
    else if($object instanceof steam_container)
      return "$config_webserver_ip/icons/mimetype/folder_closed.gif";
    else if($object instanceof steam_date)
      return "$config_webserver_ip/icons/mimetype/date.gif";
    else if($object instanceof steam_calendar)
      return "$config_webserver_ip/icons/mimetype/calendar.gif";
    else if($object instanceof steam_messageboard)
      return "$config_webserver_ip/icons/mimetype/forum.gif";
    else if($object instanceof steam_link)
      return "$config_webserver_ip/icons/mimetype/link.gif";


    //mimetypes by object name
    $name = (isset($properties["name"]) && is_string($properties["name"]))?$properties["name"]:"";
    $icons = array(
      "generic" => "$config_webserver_ip/icons/mimetype/generic.gif",
      "application/x-coreldraw" => "$config_webserver_ip/icons/mimetype/coreldraw.gif",
      "application/mjet-mm" => "$config_webserver_ip/icons/mimetype/mindmap.gif",
      "application/msword" => "$config_webserver_ip/icons/mimetype/msword.gif",
      "application/ms-excel" => "$config_webserver_ip/icons/mimetype/msexcel.gif",
      "application/ms-powerpoint" => "$config_webserver_ip/icons/mimetype/mspowerpoint.gif",
      "application/pdf" => "$config_webserver_ip/icons/mimetype/pdf.gif",
      "application/vnd.oasis.opendocument.presentation" => "$config_webserver_ip/icons/mimetype/odp.gif",
      "application/vnd.oasis.opendocument.spreadsheet" => "$config_webserver_ip/icons/mimetype/ods.gif",
      "application/vnd.oasis.opendocument.text" => "$config_webserver_ip/icons/mimetype/odt.gif",
      "application/vnd.sun.xml.calc" => "$config_webserver_ip/icons/mimetype/starcalc.gif",
      "application/vnd.sun.xml.impress" => "$config_webserver_ip/icons/mimetype/starimpress.gif",
      "application/vnd.sun.xml.writer" => "$config_webserver_ip/icons/mimetype/starwriter.gif",
      "application/vnd.visio" => "$config_webserver_ip/icons/mimetype/visio.gif",
      "application/vnd.openxmlformats-officedocument.wordprocessingml.document" => "$config_webserver_ip/icons/mimetype/msword.gif",
      "application/vnd.openxmlformats-officedocument.presentationml.presentation" => "$config_webserver_ip/icons/mimetype/mspowerpoint.gif",
      "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" => "$config_webserver_ip/icons/mimetype/msexcel.gif",
      "application/x-robolab" => "$config_webserver_ip/icons/mimetype/robolab.gif",
      "application/x-shockwave-flash" => "$config_webserver_ip/icons/mimetype/shockwave.gif",
      "application/zip" => "$config_webserver_ip/icons/mimetype/zip.gif",
      "audio/mpeg" => "$config_webserver_ip/icons/mimetype/audio.gif",
      "audio/x-midi" => "$config_webserver_ip/icons/mimetype/audio.gif",
      "audio/x-mp3" => "$config_webserver_ip/icons/mimetype/audio.gif",
      "audio/x-wav" => "$config_webserver_ip/icons/mimetype/audio.gif",
      "image/gif" => "$config_webserver_ip/icons/mimetype/image.gif",
      "image/jpeg" => "$config_webserver_ip/icons/mimetype/image.gif",
      "image/jpg" => "$config_webserver_ip/icons/mimetype/image.gif",
      "image/x-ms-bmp" => "$config_webserver_ip/icons/mimetype/image.gif",
      "image/png" => "$config_webserver_ip/icons/mimetype/image.gif",
      "text/html" => "$config_webserver_ip/icons/mimetype/html.gif",
      "text/plain" => "$config_webserver_ip/icons/mimetype/text.gif",
      "text/xml" => "$config_webserver_ip/icons/mimetype/html.gif",
      "video/x-flv" => "$config_webserver_ip/icons/mimetype/movie.gif",
      "video/mpeg" => "$config_webserver_ip/icons/mimetype/movie.gif",
      "video/quicktime" => "$config_webserver_ip/icons/mimetype/movie.gif",
      "video/x-msvideo" => "$config_webserver_ip/icons/mimetype/movie.gif",
      "video/x-ms-wmv" => "$config_webserver_ip/icons/mimetype/movie.gif"
    );

    //follow the steam mimetype
    if(isset($properties["mimetype"]) && isset($icons[$properties["mimetype"]]))
      return $icons[$properties["mimetype"]];

    //derive mimetype out of filename
    $tail = strrchr($name, '.');
    if(isset($mimetype_map[$tail]) && isset($icons[$mimetype_map[$tail]]))
      return $icons[$mimetype_map[$tail]];

    //default icon
    return $icons["generic"];
  }

?>
