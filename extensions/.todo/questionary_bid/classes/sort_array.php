<?php
  /****************************************************************************
  sort_array.php - class to sort multidimensional arrays by lower level information
  Copyright (C)

////////////////////////////////////////////////////////////////////////
  CONSTRUCTOR:
    function sort_array ($array = array())

  PUBLIC FUNCTIONS:
    function get_array()
    function set_array($array = array)
    function sort($key, $dir = SORT_ASCENDING, $level = 0)

  CONSTANTS:
    SORT_DESCENDING = 0
    SORT_ASCENDING = 1

////////////////////////////////////////////////////////////////

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

define("SORT_DESCENDING", 0);
define("SORT_ASCENDING", 1);

/**
* class to sort multidimensional arrays by lower level information
*
* @author	    Henrik Beige <hebeige@gmx.de>
* @copyright	Henrik Beige <hebeige@gmx.de> - distributed under the GPL
*/
class sort_array
{
  /**
  * array to be sorted
  *
  * @access   private
  * @var      Array
  */
  var $array;


  /**
  * Constructor
  * Sets array in class
  * @access   public
  * @param    Array       $array
  **/
  function sort_array($array = array())
  {
    $this->array = $array;
  }


  /**
  * returns array that is stored in class
  * @access   public
  **/
  function get_array()
  {
    return $this->array;
  }


  /**
  * stores array in class
  * @access   public
  * @param    Array       $array
  **/
  function set_array($array = array())
  {
    $this->array = $array;
  }


  /**
  * Sorts array that is stored in class
  * Sorts array that is stored in class by the value that is defined through $key at the array level $level
  * @access   public
  * @param    Any         $key
  * @param    Integer     $dir
  * @param    Integer     $level
  **/
  function sort($key, $dir = SORT_ASCENDING, $level = 0)
  {
    //if $key is not set return original unsorted array
    $first = reset($this->array);
    if(!isset($first["bid:questionary:input"][$key]) && !isset($first[$key]) ) return $this->array;

    //built temp array for sorting
    $arr = array();
    foreach($this->array as $tmp_key => $tmp_value)
	{
      if(isset($first["bid:questionary:input"][$key])) $arr[$tmp_key] = $tmp_value["bid:questionary:input"][$key];
	  if(isset($first[$key]))	$arr[$tmp_key] = $tmp_value[$key];
	}

    //sort temp array
    natcasesort($arr);

    //reverse sorting if needed
    if($dir == SORT_DESCENDING)
      $arr = array_reverse($arr, true);

    //rebuilt original array
    $result = array();
    foreach($arr as $tmp_key => $tmp_value)
      $result[$tmp_key] = $this->array[$tmp_key];

    //set internal array to result
    $this->array = $result;

    //return sorted array
    return $result;
  }
}

?>