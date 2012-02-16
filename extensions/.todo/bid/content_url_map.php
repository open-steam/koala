<?php

  /****************************************************************************
  content_url_map.php - defines the entry points for the different modules
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

  $content_url_map = array(
    0 => "./contentframe.php",
    //"annotation" => "./modules/forum/index_category.php",
    //"calendar" => "./modules/calendar/index.php",
    "cluster" => "./cluster.php",
    "document" => "./document.php",
    //"download" => "./tools/get.php",
    //"forum" => "./modules/forum/index.php",
	//"gallery" => "./modules/gallery/gallery.php",
    "index" => "./document.php",
    //"portal" => "./modules/portal/index.php",
    //"portal2" => "./modules/portal2/index.php",
    //"questionary" => "./modules/questionary/index.php",
    "taggedFolder" => "./taggedFolder.php",
    "trashbin" => "./trashbin.php"
  );

?>