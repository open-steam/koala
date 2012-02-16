<?php
  /****************************************************************************
  steam_types.php - defines a Search for sTeam
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

  Author: Tobias Müller
  EMail: tobias.mueller@mediahaven.de

  ****************************************************************************/

class search_define
{
	var $limitSearch;
	var $extendSearch;
	var $fulltextSearch;
	
	function init() 
	{
		$this->limitSearch = array();
		$this->extendSearch = array();
		$this->fulltextSearch = array();
	}
	
	function steam_search()
	{
		$this->init();
	}

	function eq($val)
	{
		if(is_numeric($val))
			return array( "=", (int)$val);
		else
			return array("=", "\"".$val."\"" );
	}

	function uneq($val) 
	{
		if(is_numeric($val))
			return array("!=", (int)$val);
		else
			return array("!=", "\"".$val."\"");
	}

	function like($val)
	{
		return array("like", "\"".$val."\"");
	}
	
	function search($store, $thekey, $value_operation)
	{
		$searching = array();
		$searching["storage"] 	= $store;
		$searching["key"]	 	= $thekey;
		$searching["value"]	 	= $value_operation;
		return $searching;
	}
	
	function extendAttr($attr, $value) 
	{
		$this->extendSearch[sizeof($this->extendSearch)] = $this->search("attrib", $attr, $value);
	}
	
	function limitAttr($attr, $value) 
	{
		$this->limitSearch[sizeof($this->limitSearch)] = $this->search("attrib", $attr, $value);
	}
	
	function extendSearch($store, $thekey, $value) 
	{
		$this->extendSearch[sizeof($this->extendSearch)] = $this->search($store, $thekey, $value);
	}

	function limitSearch($store, $thekey, $value) 
	{
		$this->limitSearch[sizeof($this->limitSearch)] = $this->search($store, $thekey, $value);
	}
	
	function addFulltextSearch ($value)
	{
		$this->fulltextSearch[sizeof($this->fulltextSearch)] = array ("storage" => "doc_ft", "value" => $value);
	}
	
	function getSearch() 
	{
		return $this->extendSearch;
	}
	
	function getExclusions() 
	{
		return $this->limitSearch;
	}
	
	function getFulltext ()
	{
		return $this->fulltextSearch;
	}
}
?>
