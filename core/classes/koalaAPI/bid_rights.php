<?php
  /****************************************************************************
  rights.php - class to set/get rights using PHPsTeam, for bid-owl system
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

class bid_rights
{
  var $steam;
  var $entities = false;


  //Constructor
  function bid_rights($steam)
  {
    $this->steam = $steam;
  }


  //get all favourites and groups and store them in $this->entities (including group "everyone")
  function get_entities()
  {
    if(!$this->entities)
    {
      //get groups from user
      $groups_id = $this->steam->groupname_to_object("everyone", 1);
      $groups_tmp_id = $this->steam->get_groups_from_user(1);

      //get favourites from user
      $favourites_id = $this->steam->get_favourites_from_user(1);

      //empty steam buffer
      $data = $this->steam->buffer_flush();


      //get groups out of data
      if(!is_array($data[$groups_tmp_id]->arguments))
        $groups = array();
      else
        $groups = array_merge($groups, $data[$groups_tmp_id]->arguments);

      //get favourites out of data
      if(!is_array($data[$favourites_id]->arguments))
        $favourites = array();
      else
        $favourites = $data[$favourites_id]->arguments;


      //derive real groups and favourites
      $groups = array_diff($groups, $favourites);
      $this->entities = array_merge($groups, $favourites);
    }
  }


  //set acquire rights from environment and set overall rights to private (no read/write)
  function set_acquire($object, $buffer = 0)
  {
    $result1 = $this->steam->set_acquire_environment($object, $buffer);
    $result2 = $this->set_private($object, $buffer);

    return $result1 && $result2;
  }

  //set $this->entities in case of they have been fetched from the calling script
  function set_entities($entities)
  {
    $this->entities = $entities;
  }

  //set $object as private (no read/write) for all $this->entities
  function set_private($object, $buffer = 0)
  {
    $this->get_entities();

    $return = array();
    foreach($this->entities as $entity)
    {
      $return[] = $this->steam->set_read_access($object, $entity, false, $buffer);
      $return[] = $this->steam->set_insert_access($object, $entity, false, $buffer);
      $return[] = $this->steam->set_write_access($object, $entity, false, $buffer);
      $return[] = $this->steam->set_move_access($object, $entity, false, $buffer);
      $return[] = $this->steam->set_annotate_access($object, $entity, false, $buffer);
      $return[] = $this->steam->set_sanction_access($object, $entity, false, $buffer);
    }

    return $return;
  }

  //set $object as public (read) for all $this->entities
  function set_public($object, $buffer = 0)
  {
    $this->get_entities();

    $return = array();
    foreach($this->entities as $entity)
    {
      $return[] = $this->steam->set_read_access($object, $entity, true, $buffer);
      $return[] = $this->steam->set_insert_access($object, $entity, false, $buffer);
      $return[] = $this->steam->set_write_access($object, $entity, false, $buffer);
      $return[] = $this->steam->set_move_access($object, $entity, false, $buffer);
      $return[] = $this->steam->set_annotate_access($object, $entity, false, $buffer);
      $return[] = $this->steam->set_sanction_access($object, $entity, false, $buffer);
    }

    return $return;
  }

  //set read access for entity on object
  function set_read_access_entity($object, $entity, $buffer = 0)
  {
    return $this->steam->set_read_access($object, $entity, true, $buffer);
  }

  //set sanction access for entity on object
  function set_sanction_access_entity($object, $entity, $buffer = 0)
  {
    return $this->steam->set_sanction_access($object, $entity, true, $buffer);
  }

  //set write access for entity on object
  function set_write_access_entity($object, $entity, $buffer = 0)
  {
    return ($this->steam->set_insert_access($object, $entity, true, $buffer)) &&
           ($this->steam->set_write_access($object, $entity, true, $buffer)) &&
           ($this->steam->set_move_access($object, $entity, true, $buffer)) &&
           ($this->steam->set_annotate_access($object, $entity, true, $buffer));
  }


  //disable acquiring rights on object
  function unset_acquire($object, $buffer = 0)
  {
    return $this->steam->set_acquire($object, 0, $buffer);
  }

  //unset read access for entity on object
  function unset_read_access_entity($object, $entity, $buffer = 0)
  {
    return $this->steam->set_read_access($object, $entity, false, $buffer);
  }

  //unset sanction access for entity on object
  function unset_sanction_access_entity($object, $entity, $buffer = 0)
  {
    return $this->steam->set_sanction_access($object, $entity, false, $buffer);
  }

  //unset write access for entity on object
  function unset_write_access_entity($object, $entity, $buffer = 0)
  {
    return ($this->steam->set_insert_access($object, $entity, false, $buffer)) &&
           ($this->steam->set_write_access($object, $entity, false, $buffer)) &&
           ($this->steam->set_move_access($object, $entity, false, $buffer)) &&
           ($this->steam->set_annotate_access($object, $entity, false, $buffer));
  }

}
?>