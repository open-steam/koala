<?php
  /****************************************************************************
  norm_post.php - function to normalize input from $_POST
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

  ****************************************************************************/

//convert $post to be suitable as input
function norm_post($post)
{
  $post = (isset($_POST[$post]))?$_POST[$post]:$post;

  $post = stripslashes($post);
  $post = trim($post);
  $post = str_replace(array("<", ">", "\"", "'"), array("&lt;", "&gt;", "&quot;", "&prime;"), $post);

/*
  $post = strip_tags($post);
  $post = trim($post);
  $post = addcslashes($post, "\0..\37!@\@\177..\377");
*/
  //TODO : TS: Warum wurde hier ein Leerzeichen eingefŸgt?
  return $post . "";
}
?>