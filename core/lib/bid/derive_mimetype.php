<?php

  /****************************************************************************
  derive_mimetype.php - function to derive the mimetype of a filename
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

function derive_mimetype($name)
{
  global $config_doc_root;
  include("$config_doc_root/config/mimetype_map.php");

  //if name is stringtype derive mime through names tail
  if(is_string($name) && isset($mimetype_map[strrchr($name, '.')]))
    $mimetype = $mimetype_map[strrchr($name, '.')];

  //if name is array derive mime through names tail or directly over mimetype
  else if(is_array($name))
  {
    if(isset($name[DOC_MIME_TYPE]) && trim($name[DOC_MIME_TYPE]) != "")
      $mimetype = $name[DOC_MIME_TYPE];
    else if(isset($name[OBJ_NAME]))
      $mimetype = $mimetype_map[strrchr($name[OBJ_NAME], '.')];
    else
      $mimetype = "";
  }

  //if failed => no mimetype
  else
    $mimetype = "";

  return $mimetype;
}

?>