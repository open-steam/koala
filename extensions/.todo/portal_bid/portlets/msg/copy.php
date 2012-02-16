<?php

  /****************************************************************************
  copy.php - copy a single messages portlet
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

function copy_msg($steam, $source)
{
  //create container
  $copy = steam_factory::create_container( $steam, $source->get_attribute(OBJ_NAME), $steam->get_login_user() );

  $copy->set_attribute("bid:doctype", "portlet");
  $copy->set_attribute("bid:portlet", "msg");

  //copy pictures if available
  $old_content = $source->get_attribute("bid:portlet:content");
  $copy_content = array();

  foreach($old_content as $msg_id){
  	$message = steam_factory::get_object($steam, $msg_id);
  	$new_message = steam_factory::create_copy( $steam, $message );
  	$new_message->move( $copy );
  	array_push($copy_content, $new_message->get_id() );

  	$picture_id = $message->get_attribute("bid:portlet:msg:picture_id");
    if($picture_id != null || $picture_id != "")
    {
      //duplicate picture
      $new_picture = steam_factory::create_copy( $steam, steam_factory::get_object($steam, $picture_id) );
      $new_picture->move( $copy );

      //update portlet content
      $new_message->set_attribute("bid:portlet:msg:picture_id", $new_picture->get_id() );
    }
  }

  //set correct content
  $copy->set_attribute("bid:portlet:content", $copy_content);

  return $copy;
}
?>