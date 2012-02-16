<?php
  /****************************************************************************
  rights.php - class to set/get rights using PHPsTeam, for bid-owl system
  Copyright (C)
  
  ///////////////////////////////////////////////////////////////////////////////////
  //CONSTRUCTOR
  function rights($steam, $questionary, $question_folder, $answer_folder)
  
  //PUBLIC METHODS
  function set_rights_fillout($entity)
  function set_rights_edit($entity)
  function set_rights_evaluate($entity)
  function unset_rights($entity)
  function unset_rights_nofavourit($all_favourites)
  function check_access_fillout($entity , $group_ids)
  function check_access_edit($entity, $group_ids)
  function check_access_evaluate($entity, $group_ids)
  ///////////////////////////////////////////////////////////////////////////////////

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
 
  Author: Patrick Tönnis
  EMail: toennis@upb.de

  ****************************************************************************/

class rights
{
  var $steam;
  var $questionary;
  var $question_folder;
  var $answer_folder;
  
  //************************************************************
  // Constructor
  //
  // Set default to all variables
  //************************************************************
  function rights($steam, $questionary, $question_folder, $answer_folder)
  {
    $this->steam = $steam;
	$this->questionary = $questionary;
	$this->question_folder = $question_folder;
	$this->answer_folder = $answer_folder;
  }
  
  //************************************************************
  //method: set_rights_fillout($entity)
  //		sets the rights for an proband
  //
  //@param sTeam object $entity
  //************************************************************
  function set_rights_fillout($entity)
  {
	$entity_id = $entity->get_id();
	
	$this->questionary->set_read_access($entity, 0);
	$this->questionary->set_write_access($entity, 0);
	$this->questionary->set_rights_insert($entity, 0);
	$this->question_folder->set_read_access($entity, 1);
	$this->question_folder->set_write_access($entity, 0);
	$this->question_folder->set_rights_insert($entity, 0);
	$this->answer_folder->set_read_access($entity, 1);
	$this->answer_folder->set_write_access($entity, 1);
	$this->answer_folder->set_rights_insert($entity, 1);
	
	$rights = $this->questionary->get_attribute("bid:questionary:editor_rights");
	$rights[$entity_id]=$entity_id;
	$this->questionary->set_attribute( "bid:questionary:editor_rights", $rights);
  }
  
  //************************************************************
  //method: set_rights_edit($entity)
  //		sets the rights for an author
  //
  //@param sTeam object $entity
  //************************************************************
  function set_rights_edit($entity)
  {
	$entity_id = $entity->get_id();
	
	$this->questionary->set_read_access($entity, 1);
	$this->questionary->set_write_access($entity, 1);
	$this->questionary->set_rights_insert($entity, 1);
	
	$rights = $this->questionary->get_attributes(array("bid:questionary:editor_rights", "bid:questionary:author_rights", "bid:questionary:analyst_rights"));
	$rights["bid:questionary:editor_rights"][$entity_id]=$entity_id;
	$rights["bid:questionary:author_rights"][$entity_id]=$entity_id;
	$rights["bid:questionary:analyst_rights"][$entity_id]=$entity_id;
	$this->questionary->set_attributes( array(	"bid:questionary:editor_rights" => $rights["bid:questionary:editor_rights"],
												"bid:questionary:author_rights" => $rights["bid:questionary:author_rights"],
												"bid:questionary:analyst_rights" => $rights["bid:questionary:analyst_rights"])
								 );
  }
  
  //************************************************************
  //method: set_rights_evaluate($entity)
  //		sets the rights for an analyst
  //
  //@param sTeam object $entity
  //************************************************************  
  function set_rights_evaluate($entity)
  {
	$entity_id = $entity->get_id();
	
	$this->questionary->set_read_access($entity, 0);
	$this->questionary->set_write_access($entity, 0);
	$this->questionary->set_rights_insert($entity, 0);
	$this->question_folder->set_read_access($entity, 1);
	$this->question_folder->set_write_access($entity, 0);
	$this->question_folder->set_rights_insert($entity, 0);
	$this->answer_folder->set_read_access($entity, 1);
	$this->answer_folder->set_write_access($entity, 0);
	$this->answer_folder->set_rights_insert($entity, 0);
	
	$rights = $this->questionary->get_attribute("bid:questionary:analyst_rights");
	$rights[$entity_id]=$entity_id;
	$this->questionary->set_attribute( "bid:questionary:analyst_rights", $rights);
  }
  
  
  //************************************************************
  //method: unset_rights($entity)
  //		unset the rights for a user
  //
  //@param sTeam object $entity
  //************************************************************  
  function unset_rights($entity)
  {
	$entity_id = $entity->get_id();
	
	$this->questionary->set_read_access($entity, 0);
	$this->questionary->set_write_access($entity, 0);
	$this->questionary->set_rights_insert($entity, 0);
	$this->question_folder->set_read_access($entity, 0);
	$this->question_folder->set_write_access($entity, 0);
	$this->question_folder->set_rights_insert($entity, 0);
	$this->answer_folder->set_read_access($entity, 0);
	$this->answer_folder->set_write_access($entity, 0);
	$this->answer_folder->set_rights_insert($entity, 0);
	
	$rights = $this->questionary->get_attributes(array("bid:questionary:editor_rights", "bid:questionary:author_rights", "bid:questionary:analyst_rights"));
	unset($rights["bid:questionary:editor_rights"][$entity_id]);
	unset($rights["bid:questionary:author_rights"][$entity_id]);
	unset($rights["bid:questionary:analyst_rights"][$entity_id]);
	$this->questionary->set_attributes( array(	"bid:questionary:editor_rights" => $rights["bid:questionary:editor_rights"],
												"bid:questionary:author_rights" => $rights["bid:questionary:author_rights"],
												"bid:questionary:analyst_rights" => $rights["bid:questionary:analyst_rights"])
								 );
  }
  
  
  //************************************************************
  //method: unset_rights_nofavourit($all_favourites)
  //		unset all rights for users and groups, that are no more a favourit
  //
  //@param array with sTeam object $all_favourites
  //************************************************************  
  function unset_rights_nofavourit($all_favourites)
  {	
	foreach($all_favourites as $favourit)  $all[]=$favourit->get_id();
	
	//load rights
  	$access_questionary = array_keys($this->questionary->resolve_access());
	$access_question_folder = array_keys($this->question_folder->resolve_access());
	$access_answer_folder = array_keys($this->answer_folder->resolve_access());
	$access = array_merge($access_questionary, $access_question_folder, $access_answer_folder);
	$access = array_unique($access);
	
	foreach($access as $guid)
	{
		if(!in_array($guid, $all) && $guid!=$this->questionary->get_creator()->get_id())
		{
			$entity = steam_factory::get_object( $this->steam, $guid);
			if( $entity->get_name()!="root")
			{			
				$this->questionary->set_read_access($entity, 0);
				$this->questionary->set_write_access($entity, 0);
				$this->questionary->set_rights_insert($entity, 0);
				$this->question_folder->set_read_access($entity, 0);
				$this->question_folder->set_write_access($entity, 0);
				$this->question_folder->set_rights_insert($entity, 0);
				$this->answer_folder->set_read_access($entity, 0);
				$this->answer_folder->set_write_access($entity, 0);
				$this->answer_folder->set_rights_insert($entity, 0);
				
				$rights = $this->questionary->get_attributes(array("bid:questionary:editor_rights", "bid:questionary:author_rights", "bid:questionary:analyst_rights"));
				unset($rights["bid:questionary:editor_rights"][$guid]);
				unset($rights["bid:questionary:author_rights"][$guid]);
				unset($rights["bid:questionary:analyst_rights"][$guid]);
				$this->questionary->set_attributes( array(	"bid:questionary:editor_rights" => $rights["bid:questionary:editor_rights"],
															"bid:questionary:author_rights" => $rights["bid:questionary:author_rights"],
															"bid:questionary:analyst_rights" => $rights["bid:questionary:analyst_rights"]));
			}
		}
	}							 
  }
  
  
  //************************************************************
  //method: check_access_fillout($entity , $group_ids)
  //		checks the fillout rights of a group or an user and return an cooresponding boolean value
  //
  //@param sTeam-object $entity
  //@param array $group_ids
  //************************************************************  
  function check_access_fillout($entity , $group_ids)
  {
	if(!is_array($group_ids)) $ids[]= $group_ids;
	else $ids = $group_ids;
	
	$att_editor_rights = $this->questionary->get_attribute("bid:questionary:editor_rights", true);
	
	$access_write  = $this->answer_folder->check_access_write($entity, true);
  	$access_insert = $this->answer_folder->check_access_insert($entity, true);
 	$access_read_1 = $this->question_folder->check_access_read($entity, true); 
  	$access_read_2 = $this->answer_folder->check_access_read($entity, true);
	
	$buffer=$this->steam->buffer_flush();
	
	if(	$buffer[$access_write] && $buffer[$access_insert] && $buffer[$access_read_1] && $buffer[$access_read_2]
		&& ( 
				in_array( $entity->get_id(), $buffer[$att_editor_rights] )
			|| 	array_intersect( $ids, $buffer[$att_editor_rights] )
		   )
	  )	 	return true;
	else 	return false;
  }
  
  
  //************************************************************
  //method: check_access_edit($entity , $group_ids)
  //		checks the edit rights of a group or an user and return an cooresponding boolean value
  //
  //@param sTeam-object $entity
  //@param array $group_ids
  //************************************************************  
  function check_access_edit($entity, $group_ids)
  {
	if(!is_array($group_ids)) $ids[]= $group_ids;
	else $ids = $group_ids;
	
	$att_author_rights = $this->questionary->get_attribute("bid:questionary:author_rights", true);
	
	$access_write  = $this->questionary->check_access_write($entity, true);
  	$access_insert = $this->questionary->check_access_insert($entity, true);
 	$access_read   = $this->questionary->check_access_read($entity, true); 

	$buffer=$this->steam->buffer_flush();
	if(	$buffer[$access_write] && $buffer[$access_insert] && $buffer[$access_read]
		&& ( 
				in_array( $entity->get_id(), $buffer[$att_author_rights] )
			|| 	array_intersect( $ids, $buffer[$att_author_rights] )
		   )
	  )	 	return true;
	else 	return false;
  }
  
  
  //************************************************************
  //method: check_access_evaluate($entity , $group_ids)
  //		checks the evaluate rights of a group or an user and return an cooresponding boolean value
  //
  //@param sTeam-object $entity
  //@param array $group_ids
  //************************************************************  
  function check_access_evaluate($entity, $group_ids)
  {
  	if(!is_array($group_ids)) $ids[]= $group_ids;
	else $ids = $group_ids;
	
	$att_author_rights = $this->questionary->get_attribute("bid:questionary:analyst_rights", true);
  
 	$access_read_1 = $this->question_folder->check_access_read($entity, true); 
  	$access_read_2 = $this->answer_folder->check_access_read($entity, true);

	$buffer=$this->steam->buffer_flush();
	
	if(	$buffer[$access_read_1] && $buffer[$access_read_2]
		&& ( 
				in_array( $entity->get_id(), $buffer[$att_author_rights] )
			|| 	array_intersect( $ids, $buffer[$att_author_rights] )
		   )
	  )	  	return true;
	else 	return false;
  }
}
?>