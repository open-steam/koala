<?php

  /****************************************************************************
  derive_url.php - function to derive a correct url with "hhtp://", ... on it
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
if(!function_exists("derive_url")){
	function derive_url($url, $path = "")
	{
	  global $config_webserver_ip;
	
	  $url = trim($url);
	  $http = strpos(strtolower($url), "http://");
	  $https = strpos(strtolower($url), "https://");
	  $ftp = strpos(strtolower($url), "ftp://");
	  $mailto = strpos(strtolower($url), "mailto:");
	  $skype = strpos(strtolower($url), "skype:");
	  $worldwind = strpos(strtolower($url), "worldwind://");
	  $relative = strpos($url, ".");
	
	  if($http === 0 || $https === 0 || $ftp === 0 || $mailto === 0 || $skype === 0 || $worldwind === 0)
	    return $url;
	  else if ($relative === 0)
	    return $config_webserver_ip . $path . $url;
	  else
	    return "http://" . $url;
	}
}
?>