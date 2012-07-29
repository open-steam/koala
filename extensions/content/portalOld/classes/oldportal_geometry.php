<?php

  /****************************************************************************
  geometry.php - geometry class of the portal
  Copyright (C)

  ///////////////////////////////////////////////////////////////////////////////////
  //CONSTRUTOR
  function geometry($import = 0)

  //PUBLIC METHODS
  function delete($item, $dir = "up")
  function delete_content($item)
  function exchange($first, $second)
  function export()
  function get_all()
  function get_content($item)
  function get_down($item)
  function get_expandable_segments($item)
  function get_left($item)
  function get_objects()
  function get_right($item)
  function get_segment_from_id($id)
  function get_segment_by_depth($depth, $case)
  function get_up($item)
  function import($import)
  function insert($object_id, $up, $down, $left, $right, $width_absolute = "100%", $height_absolute = "100%", $alignment_hor = "left", $alignment_vert = "top")
  function resize($item, $width_absolute, $height_absolute, $alignment_hor, $alignment_vert)
  function set_content($item, $content)
  function undo_last()
  function validate_geometry()

  //PRIVATE METHODS
  function _insert($id, $dir)
  function _sort_array($id, $dir)
  function _sort_segments()
  function _make_int_array($array)
  function _new_depth()
  function _new_depth_mini($case = 1, $item = 0)
  function _size_mini($item, $dir)
  function _update_properties()
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

  Author: Henrik Beige
  EMail: hebeige@gmx.de

  ****************************************************************************/

define("VERTICAL",0);
define("HORIZONTAL",1);

class oldportal_geometry
{

  var $items;
  var $items_undo;


  //************************************************************
  // Constructor
  //
  // Set default to all variables
  //************************************************************
  function oldportal_geometry($import = 0)
  {
    $this->items = array(
      0 => array(
        "id" => 0,
        "height" => 0,
        "width" => 0,
        "height_absolute" => 0,
        "width_absolute" => 0,
        "alignment_hor" => 0,
        "alignment_vert" => 0,
        "depth_vert" => 0,
        "depth_hor" => 0,
        "up" => array(),
        "down" => array(),
        "left" => array(),
        "right" => array()
      )
    );

    if(!($import === 0))
      $this->import($import);
  }


  //****************************************************************************************
  // P U B L I C   M E T H O D E S
  //****************************************************************************************


  //************************************************************
  // delete($item, $expand_item = 0)
  // $item = segments descriptor - if it does not exist from beginning return true as if it was succesfully deleted
  // $expand_item = segments descriptors of the segments that shall be expanded to the space thats left by the deleted segment
  //
  // delete a segment out of the geometry
  //************************************************************
  function delete($item, $dir = "up")
  {
    settype($item, "integer");

    if(!isset($this->items[$item]))
      return true;

    //Save geometry before action
    $this->items_undo = $this->items;


    //define some array keys associated with the direction $dir
    $dir_array = array(
      "up" => array(
                "counter" => "down",
                "beside1" => "left",
                "beside2" => "right",
                "depth" => "depth_hor",
                "size" => "width"
              ),
      "right" => array(
                "counter" => "left",
                "beside1" => "up",
                "beside2" => "down",
                "depth" => "depth_vert",
                "size" => "height"
              ),
      "down" => array(
                "counter" => "up",
                "beside1" => "left",
                "beside2" => "right",
                "depth" => "depth_hor",
                "size" => "width"
              ),
      "left" => array(
                "counter" => "right",
                "beside1" => "up",
                "beside2" => "down",
                "depth" => "depth_vert",
                "size" => "height"
              )
    );

    //expanded direction
    $counter = $dir_array[$dir]["counter"];
    $depth =   $dir_array[$dir]["depth"];
    $size =    $dir_array[$dir]["size"];
    $beside2 = $dir_array[$dir]["beside2"];
    $beside1 = $dir_array[$dir]["beside1"];

    //update the segment in expanding direction $dir and counter direction $counter
    foreach($this->items[$item][$dir] as $dir_id)
    {
      $dir_item = $this->items[$dir_id];

      //delete segment
      $tmp_id = array_search($item, $dir_item[$counter]);
      unset($this->items[$dir_id][$counter][$tmp_id]);

      //get beginning and ending depth of the segment from which the segment has been deleted
      $upper_bound = $dir_item[$depth] + $dir_item[$size];
      $lower_bound = $dir_item[$depth];

      //set correct links to segment below the deleted segment
      foreach($this->items[$item][$counter] as $tmp)
      {
        if($lower_bound <= $this->items[$tmp] || $this->items[$tmp] < $upper_bound)
        {
          array_push($this->items[$dir_id][$counter], $tmp);
          array_push($this->items[$tmp][$dir], $dir_id);
        }

        //unset deleted segment in that segment
        $tmp_id = array_search($item, $this->items[$tmp][$dir]);
        unset($this->items[$tmp][$dir][$tmp_id]);

        //erase doubles in arrays of segments below
        $this->items[$tmp][$dir] = array_unique($this->items[$tmp][$dir]);
      }

      //erase doubles
      $this->items[$dir_id][$counter] = array_unique($this->items[$dir_id][$counter]);
    }


    //get lowest and highest segment of the segment in expanded direction
    $lowest = reset($this->items[$item][$dir]);
    $highest = end($this->items[$item][$dir]);


    //update beside1 items
    foreach($this->items[$item][$beside1] as $beside_id)
    {
      $beside_item = $this->items[$beside_id];

      //unset deleted segments
      $tmp_id = array_search($item, $beside_item[$beside2]);
      unset($this->items[$beside_id][$beside2][$tmp_id]);

      //insert lowest segment in direction
      array_push($this->items[$beside_id][$beside2], $lowest);

      //insert current segment in lowest segment
      array_push($this->items[$lowest][$beside1], $beside_id);

      //erase doubles
      $this->items[$beside_id][$beside2] = array_unique($this->items[$beside_id][$beside2]);
    }
    $this->items[$lowest][$beside1] = array_unique($this->items[$lowest][$beside1]);


    //update beside2 items
    foreach($this->items[$item][$beside2] as $beside_id)
    {
      $beside_item = $this->items[$beside_id];

      //unset deleted segments
      $tmp_id = array_search($item, $beside_item[$beside1]);
      unset($this->items[$beside_id][$beside1][$tmp_id]);

      //insert lowest segment in direction
      array_push($this->items[$beside_id][$beside1], $highest);

      //insert current segment in highest segment
      array_push($this->items[$highest][$beside2], $beside_id);

      //erase doubles
      $this->items[$beside_id][$beside1] = array_unique($this->items[$beside_id][$beside1]);
    }
    $this->items[$highest][$beside2] = array_unique($this->items[$highest][$beside2]);


    //delete item itself
    unset($this->items[$item]);


    //delete connection from root node on itself
    $this->items[0]["right"] = array_diff($this->items[0]["right"], array(0));
    $this->items[0]["down"] = array_diff($this->items[0]["down"], array(0));
    $this->items[0]["left"] = array_diff($this->items[0]["left"], array(0));
    $this->items[0]["up"] = array_diff($this->items[0]["up"], array(0));


    //update size and depth
    $this->_update_properties();

    return true;
  }


  //************************************************************
  // delete_content($item)
  // $item = segments descriptor - if it does not exist from beginning return true as if it was succesfully deleted
  //
  // delete a the portlet object of a segment
  //************************************************************
  function delete_content($item)
  {
    settype($item, "integer");

    //Save geometry before action
    $this->items_undo = $this->items;

    if(isset($this->items[$item]))
      $this->items[$item]["id"] = 0;

    return true;
  }


  //************************************************************
  // exchange($first, $second)
  // $first = segments descriptor of the first segment
  // $second = segments descriptor of the second segment
  //
  // exchange the segments in the geometry
  //************************************************************
  function exchange($first, $second)
  {
    settype($first, "integer");
    settype($second, "integer");

    if(!isset($this->items[$first]) || !isset($this->items[$second]))
      return false;

    //Save geometry before action
    $this->items_undo = $this->items;

    $tmp = $this->items[$first]["id"];
    $this->items[$first]["id"] = $this->items[$second]["id"];
    $this->items[$second]["id"] = $tmp;

    return true;
  }


  //************************************************************
  //method: export()
  //return = a minimal image of the current geometry
  //************************************************************
  function export()
  {
    $export = array();
    foreach($this->items as $key => $item)
    {
      $export[$key]["id"] = $item["id"];
      $export[$key]["up"] = $item["up"];
      $export[$key]["down"] = $item["down"];
      $export[$key]["left"] = $item["left"];
      $export[$key]["right"] = $item["right"];
      $export[$key]["height_absolute"] = $item["height_absolute"];
      $export[$key]["width_absolute"] = $item["width_absolute"];
      $export[$key]["alignment_hor"] = $item["alignment_hor"];
      $export[$key]["alignment_vert"] = $item["alignment_vert"];
    }

    return $export;
  }


  //************************************************************
  //method: get_all()
  //$item = segment ID that shall be examined (integer)
  //return = all segments sorted from top to bottom and from left to right
  //************************************************************
  function get_all()
  {
    $all = array();
    $depth = 1;
    while($segments = $this->get_segment_by_depth($depth++ , VERTICAL))
      $all = $all + $segments;

    return $all;
  }


  //************************************************************
  //method: get_content($item)
  //$item = segment ID that shall be examined (integer)
  //return = the content of segment $item
  //************************************************************
  function get_content($item)
  {
    settype($item, "integer");

    return (isset($this->items[$item]))?$this->items[$item]["id"]:false;
  }


  //************************************************************
  //method: get_down($item)
  //$item = segment ID that shall be examined (integer)
  //return = a list of segment IDs that are down/south of $item
  //************************************************************
  function get_down($item)
  {
    settype($item, "integer");

    return (isset($this->items[$item]))?$this->items[$item]["down"]:false;
  }


  //************************************************************
  //method: get_expandable_segments($item)
  //$item = segment ID of the segment that shall be examined (integer)
  //return = array of segment IDs
  //
  //derive a list of all segments that are neighbour to $item and may be expanded when $item would be deleted
  //************************************************************
  function get_expandable_segments($item)
  {
    settype($item, "integer");

    $result = array(
      "up" => array(),
      "right" => array(),
      "down" => array(),
      "left" => array()
    );

    $directions = array(
      "up" => "down",
      "right" => "left",
      "down" => "up",
      "left" => "right"
    );

    //check on expandable segments around the chosen segment $item
    foreach($directions as $dir => $counter)
    {
      //check upper segments
      $single = true;

      foreach($this->items[$item][$dir] as $tmp)
      {
        if(sizeof($this->items[$tmp][$counter]) == 1)
          $result[$dir][] = $tmp;
        else
          $single = false;
      }

      //if there are segments in the current direction that have more neighboors in counter direction then the current segment => direction not expandable
      //if there are no segments in direction expandable => direction not expandable
      //if there is only the root segment in direction expandable => direction not expandable
      if(!$single || sizeof($result[$dir]) == 0 || (sizeof($result[$dir]) == 1 && $result[$dir][0] === 0))
        $result[$dir] = false;

//      $debug->dump($result[$dir]);
    }

    return $result;
  }

  //************************************************************
  //method: get_left($item)
  //$item = segment ID that shall be examined
  //return = a list of segment IDs that are left/west of $item
  //************************************************************
  function get_left($item)
  {
    settype($item, "integer");

    return (isset($this->items[$item]))?$this->items[$item]["left"]:false;
  }


  //************************************************************
  //method: get_objects()
  //return = a list of all steam_objects in same order as in list
  //************************************************************
  function get_objects()
  {
    $list = array();
    foreach($this->items as $item)
      $list[] = $item["id"];

    return $list;
  }


  //************************************************************
  //method: get_right($item)
  //$item = segment ID that shall be examined (integer)
  //return = a list of segment IDs that are right/east of $item
  //************************************************************
  function get_right($item)
  {
    settype($item, "integer");

    return (isset($this->items[$item]))?$this->items[$item]["right"]:false;
  }


  //************************************************************
  //method: get_segment_from_id($id)
  //$id = segment ID from which the segment id shall be derived
  //return = a list of segment IDs that are right/east of $item
  //************************************************************
  function get_segment_from_id($id)
  {
    foreach($this->items as $key => $item)
      if(is_object($item["id"]) && is_object($id) && $item["id"]->get_id() == $id->get_id() )
        return $key;

    return false;
  }


  //************************************************************
  //method: get_segment_by_depth($depth, $case)
  //$depth = the depth the segments need to have (integer)
  //$case = Dimension of the depth can be HORIZONTAL or VERTICAL
  //return = array of segment IDs
  //
  //derive a list of segment IDs, with the depth $depth in the given dimension $case
  //************************************************************
  function get_segment_by_depth($depth, $case)
  {
    if($case == VERTICAL)
    {
      $direction = "depth_vert";
      $direction_counter = "depth_hor";
    }
    else
    {
      $direction = "depth_hor";
      $direction_counter = "depth_vert";
    }

    //get segment at specified depth
    $tmp = array();
    foreach($this->items as $key => $item)
      if($item[$direction] == $depth)
        $tmp[$key] = $item[$direction_counter];

    //sort by depth
    asort($tmp);

    //get segment identifiers
    $tmp = array_keys($tmp);


    //build array of complete items, ordered from left to right
    $sort = array();
    foreach($tmp as $item)
      $sort[$item] = $this->items[$item];

    return $sort;
  }


  //************************************************************
  //method:  get_up($item)
  //$item = segments descriptor of the item that shall be examined
  //return = a list of segments descriptors that are up/north of $item
  //************************************************************
  function get_up($item)
  {
    settype($item, "integer");

    return (isset($this->items[$item]))?$this->items[$item]["up"]:false;
  }


  //************************************************************
  //method: import($import)
  //$import = image of geometry (array)
  //return = nothing
  //
  //import image of geometry must be created through $this->export()
  //************************************************************
  function import($import)
  {
    //Save geometry before action
    $this->items_undo = $this->items;

    $this->items = $import;
    $this->_update_properties();
  }


  //************************************************************
  //method: insert($object_id, $item, $up, $down, $left, $right)
  //$id = steam_object id of the steam_object where all portlet information are saved in
  //$up = all segments that are up/north of the new segment
  //$down = all segments that are down/south of the new segment
  //$left = all segments that are left/west of the new segment
  //$right = all segments that are right/east of the new segment
  //$width_absolute = absolute width suggestion for segment $item (optional)
  //$height_absolute = absolute height suggestion for segment $item (optional)
  //$alignment_hor = aligment of the portlet in segment $id in horizontal dimension (optional)
  //$alignment_vert = aligment of the portlet in segment $id in vertical dimension (optional)
  //return = segment id
  //
  //Insert a new segement into the geometry
  //************************************************************
  function insert($object_id, $up, $down, $left, $right, $width_absolute = "100%", $height_absolute = "100%", $alignment_hor = "left", $alignment_vert = "top")
  {

    //Save geometry before action
    $this->items_undo = $this->items;


    //makes arrays if neccessary
    $up = (!is_array($up))?array($up):$up;
    $down = (!is_array($down))?array($down):$down;
    $left = (!is_array($left))?array($left):$left;
    $right = (!is_array($right))?array($right):$right;

    $up = $this->_make_int_array($up);
    $down = $this->_make_int_array($down);
    $left = $this->_make_int_array($left);
    $right = $this->_make_int_array($right);


    //insert item
    $this->items[] = array(
      "id" => $object_id,
      "up" => $up,
      "down" => $down,
      "left" => $left,
      "right" => $right,
      "height" => 0,
      "width" => 0,
      "height_absolute" => $height_absolute,
      "width_absolute" => $width_absolute,
      "alignment_hor" => $alignment_hor,
      "alignment_vert" => $alignment_vert
    );
    end($this->items);
    $id = key($this->items);


    //update arrays of the segments around
    $this->_insert($id, "up");
    $this->_insert($id, "right");
    $this->_insert($id, "down");
    $this->_insert($id, "left");

    //update size and depth
    $this->_update_properties();

    return $id;
  }

  //************************************************************
  //method: resize($item, $width_absolute, $height_absolute)
  //$item = ID of the segment
  //$width_absolute = absolute width suggestion for segment $item
  //$height_absolute = absolute height suggestion for segment $item
  //$alignment_hor = aligment of the portlet in segment $id in horizontal dimension
  //$alignment_vert = aligment of the portlet in segment $id in vertical dimension
  //return = if it does not exist return false, else true
  //
  //set the portlet object of a segment
  //************************************************************
  function resize($item, $width_absolute, $height_absolute, $alignment_hor, $alignment_vert)
  {
    settype($item, "integer");

    //Save geometry before action
    $this->items_undo = $this->items;

    $this->items[$item]["width_absolute"] = $width_absolute;
    $this->items[$item]["height_absolute"] = $height_absolute;
    $this->items[$item]["alignment_hor"] = $alignment_hor;
    $this->items[$item]["alignment_vert"] = $alignment_vert;
  }

  //************************************************************
  //method: set_content($item, $content)
  //$item = ID of the segment
  //$content = steam_object of the portlet to be the new content
  //return = if it does not exist return false, else true
  //
  //set the portlet object of a segment
  //************************************************************
  function set_content($item, $content)
  {
    settype($item, "integer");

    //Save geometry before action
    $this->items_undo = $this->items;

    if(isset($this->items[$item]))
      $this->items[$item]["id"] = $content;
    else return false;

    return true;
  }


  //************************************************************
  //method: undo_last()
  //return = true
  //
  //undo last action
  //************************************************************
  function undo_last()
  {
    //Recover geometry from last action
    $this->items_undo = $this->items;

    return true;
  }


  //************************************************************
  //method: validate_geometry()
  //return = true => geo is valid; array => geo is invalid - array with data with the position and the segments that are overlapping
  //
  //check if geometry is valid
  //************************************************************
  function validate_geometry()
  {

    //build map of all segments to see if any segments are overlapping
    foreach($this->items as $key => $segment)
    {
      if($key == 0) continue;

      for($x = $segment["depth_hor"]; $x < $segment["depth_hor"] + $segment["width"]; $x++)
        for($y = $segment["depth_vert"]; $y < $segment["depth_vert"] + $segment["height"]; $y++)
          if(isset($map[$x][$y]))
            $overlap[] = array(
              "x" => $x,
              "y" => $y,
              0 => $map[$x][$y],
              1 => $key
            );
          else
            $map[$x][$y] = $key;
    }

    //check on holes in the built map
    for($x = 1; $x <= $this->items[0]["width"]; $x++)
      for($y = 1; $y <= $this->items[0]["height"]; $y++)
        if(!isset($map[$x][$y]))
          $overlap[] = array(
            "x" => $x,
            "y" => $y
          );

    //if there are any overlaps return array
    if(isset($overlap) && sizeof($overlap) > 0)
      return $overlap;
    else
      return true;
  }

  //****************************************************************************************
  // P R I V A T E   M E T H O D E S
  //****************************************************************************************

  //***************************************************************************
  //method: _insert($id, $dir)
  //$id = ID of one segment
  //$dir = one of "up", "right", "down", "left"
  //return = the size (integer)
  //
  //insert new segment to the segments in the direction $dir of the new segment
  //***************************************************************************
  function _insert($id, $dir)
  {
    $tmp_dir = array(
      "up" => "down",
      "right" => "left",
      "down" => "up",
      "left" => "right"
    );
    $counter = $tmp_dir[$dir];

    //insert new segment to the segments in the direction $dir of the new segment
    foreach($this->items[$id][$dir] as $key => $item)
    {
      //insert segment in segments array in counter direction
      array_push($this->items[$item][$counter], $id);

      //is the new segment has some segments in counter direction like the segment in direction erase them in segment in direction
      $intersect = array_intersect($this->items[$item][$counter], $this->items[$id][$counter]);
      foreach($intersect as $key => $value)
        unset($this->items[$item][$counter][$key]);
    }
    //insert at root segment
    if($this->items[$id][$dir] == array()) array_push($this->items[0][$counter], $id);
  }


  //***************************************************************************
  //method: _sort_array($id, $dir)
  //$id = segment ID in which an array shall be sorted
  //$dir = one of "up", "right", "down", "left"
  //return = sorted array
  //
  //sort an array $array of segments in the direction $dir
  //***************************************************************************
  function _sort_array($id, $dir)
  {
    $tmp_dir = array(
      "up" => "depth_hor",
      "right" => "depth_vert",
      "down" => "depth_hor",
      "left" => "depth_vert"
    );
    $depth = $tmp_dir[$dir];

    //build sort array
    $tmp = array();
    foreach($this->items[$id][$dir] as $key => $item)
      $tmp[$item] = $this->items[$item][$depth];

    //sort by depth
    asort($tmp);

    //return sorted array
    $this->items[$id][$dir] = array_keys($tmp);
  }


  //***************************************************************************
  //method: _sort_segments()
  //return = nothing
  //
  //sort all arrays in the directions "up", "right", "down", "left" of ALL segments
  //***************************************************************************
  function _sort_segments()
  {
    foreach($this->items as $key => $segment)
    {
      $this->_sort_array($key, "up");
      $this->_sort_array($key, "right");
      $this->_sort_array($key, "down");
      $this->_sort_array($key, "left");
    }
  }


  //***************************************************************************
  //method: _make_int_array($array)
  //$array = array to be converted
  //return = converted array (array)
  //
  //convert all elements of an array to variable type (integer), recursivly
  //***************************************************************************
  function _make_int_array($array)
  {
    $array = (!is_array($array))?array($array):$array;
    array_walk($array, "_make_int");

    return $array;
  }


  //***************************************************************************
  //method: _new_depth()
  //return = nothing
  //
  //calculate all depth horizontal/vertical of all segments
  //***************************************************************************
  function _new_depth()
  {
    //initially set all depths to 0
    foreach($this->items as $key => $item)
    {
      $this->items[$key]["depth_hor"] = 0;
      $this->items[$key]["depth_vert"] = 0;
    }


    //calculate depth in both directions
    $this->_new_depth_mini(VERTICAL);
    $this->_new_depth_mini(HORIZONTAL);
  }


  //***************************************************************************
  //method: _new_depth_mini($case = 1, $item = 0)
  //$case = the dimension in which the depth shall be calculated can be VERTICAL or HORIZONTAL
  //$item = segment id
  //return = nothing
  //
  //calculate all depth either horizontal or vertical of all segments. Dimension depends on $case
  //***************************************************************************
  function _new_depth_mini($case = 1, $item = 0)
  {
    //derive direction of depth/width
    if($case)
    {
      $save = "depth_vert";
      $direction = "down";
      $size = "height";
    }
    else
    {
      $save = "depth_hor";
      $direction = "right";
      $size = "width";
    }

    //calculate current depth
    $current_depth = ($item === 0)?1:$this->items[$item][$save] + $this->items[$item][$size];

    //calculate depth of all following segments
    foreach($this->items[$item][$direction] as $current)
    {
      //if current segment is root segment => skip
      if($current === 0) continue;

      //update depth if neccessary
      if(!isset($this->items[$current][$save]) || $this->items[$current][$save] < $current_depth)
        $this->items[$current][$save] = $current_depth;

      //next successor
      $this->_new_depth_mini($case, $current);
    }
  }


  //***************************************************************************
  //method: _size_mini($item, $dir)
  //$item = whole array of one segment
  //$dir = one of "up", "right", "down", "left"
  //return = the size (integer)
  //
  //derive size of segment $item in columns/lines the segment spans, in regard of its neighbors in the direction $dir
  //***************************************************************************
  function _size_mini($item, $dir)
  {
    $tmp_dir = array(
      "up" => array("counter" => "down", "size" => "width"),
      "right" => array("counter" => "left", "size" => "height"),
      "down" => array("counter" => "up", "size" => "width"),
      "left" => array("counter" => "right", "size" => "height")
    );
    $counter_dir = $tmp_dir[$dir]["counter"];
    $size_dir = $tmp_dir[$dir]["size"];


    //go through all neighboors in given direction
    $size = 0;
    foreach($item[$dir] as $item_dir)
    {
      //skip root segment
      if($item_dir === 0) continue;

      //get neighbor
      $tmp = $this->items[$item_dir];

      //if $item_dir is the only neighbor of $tmp then add full height,
      //if $item_dir is not the only neighbor of $tmp asume size of 1
      if(sizeof($tmp[$counter_dir]) == 1)
        $size += $tmp[$size_dir];
      else
        $size += 1;
    }

    return $size;
  }


  //***************************************************************************
  //method: _update_properties($item, $dir)
  //return = nothing
  //
  //calculate sizes of segments iterativ. Within size calculation calculate all depths in both dimensions
  //***************************************************************************
  function _update_properties()
  {

    //Presumption - size = max size of neighboors in associated direction
    //normalize all arrays so the array keys start with 0 and increase by 1 till end of array
    foreach($this->items as $key => $item)
    {
      $this->items[$key]["height"] = max(sizeof($item["left"]), sizeof($item["right"]));
      $this->items[$key]["width"] = max(sizeof($item["up"]), sizeof($item["down"]));
      $this->items[$key]["up"] = array_values($this->items[$key]["up"]);
      $this->items[$key]["right"] = array_values($this->items[$key]["right"]);
      $this->items[$key]["down"] = array_values($this->items[$key]["down"]);
      $this->items[$key]["left"] = array_values($this->items[$key]["left"]);
    }

    //iterate size calculation until nothing changes anymore
    $changed = true;
    while($changed)
    {
      //make sure the iteration stops if no changes happened
      $changed = false;

      //calculate depths
      $this->_new_depth();

      //calculate sizes
      foreach($this->items as $item_key => $item)
      {

        //derive height
        $size_left = $this->_size_mini($item, "left");
        $size_right = $this->_size_mini($item, "right");
        $size_vert = max($size_left, $size_right);

        //derive width
        $size_up = $this->_size_mini($item, "up");
        $size_down = $this->_size_mini($item, "down");
        $size_hor = max($size_up, $size_down);


        //if sizes changed => update them
        if($item["height"] < $size_vert)
        {
          $this->items[$item_key]["height"]= $size_vert;
          $changed = true;
        }
        if($item["width"] < $size_hor)
        {
          $this->items[$item_key]["width"]= $size_hor;
          $changed = true;
        }

      } // foreach
    } // while($changed)

    //calculate final depths
    $this->_new_depth();

    //sort all direction arrays in all segments
    $this->_sort_segments();

  } //function


}

//array_walk does not support class methods as callback
function _make_int(&$item)
{
  if(is_array($item))
    array_walk($item, "_make_int");

  $item = (int) $item;
}

?>