<?php

  /****************************************************************************
  searching.class.php - contains functions for searching on a sTeam-Server
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

class searching {

	private $steam_object;

	public function __construct( $steam_object ) {
		$this->steam_object = $steam_object;
	}

	/**
	 * Searches for objects which fit the options defined by $search_parameters
	 * 
	 * Exclusions may not work - I never got it working myself.
	 * 
	 * For searching bulletinboards please use the searchsupport.class.php
	 * 
	 * Don't search for OBJ_NAME, OBJ_DESC and OBJ_KEYWORDS together, make two searches, one
	 * for OBJ_NAME, OBJ_DESC and one for OBJ_KEYWORDS
	 * 
	 * @param $search_parameters searches created by the search_define-class
	 * @return an array of objects which are the result of the search
	 */
	function search($search_parameters, $class_type = 0){
		$searches = $search_parameters->getSearch();
		$exlusions = $search_parameters->getExclusions();
		$fulltext = $search_parameters->getFulltext();
		
		$searches = ($searches == null ? array() : $searches);
		$exlusions = ($exlusions == null ? array() : $exlusions);
		$fulltext = ($fulltext == null ? array() : $fulltext);
		
		$myrequest = new steam_request(
    						$this->steam_object->get_steam_connector()->get_id(),
    						$this->steam_object->get_steam_connector()->get_transaction_id(), 
  							$this->steam_object, 
  							array("searchAsync", array($searches, 
  													$exlusions, 
 													$fulltext,
  													$class_type)),
  							COAL_COMMAND);
  		
    	$answer = $this->steam_object->get_steam_connector()->command($myrequest);
		return $answer->get_arguments();
	}
}
?>
