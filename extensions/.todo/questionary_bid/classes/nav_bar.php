<?php
  /****************************************************************************
  nav_bar.php - class to build a navigation link bar with page_numbers
  Copyright (C)

////////////////////////////////////////////////////////////////////////
  CONSTRUCTOR:
    function nav_bar($baseadress, $count, $currentpage, $pagebreak)

  PUBLIC FUNCTIONS:
    function adress_planchet($baseadress = "")
    function get_bar()

  CONSTANTS:
    NAV_BAR_PAGE_RANGE - Number of pagelinks before and after the current page

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

DEFINE("NAV_BAR_PAGE_RANGE", 1);

/**
* class to build a navigation link bar with page_numbers
*
* @author	    Henrik Beige <hebeige@gmx.de>
* @copyright	Henrik Beige <hebeige@gmx.de> - distributed under the GPL
*/
class nav_bar
{
  /**
  * baseadress where the pagenumber shall be appended at the links
  *
  * @access   private
  * @var      Integer
  */
  var $baseadress;

  /**
  * total number of items
  *
  * @access   private
  * @var      Integer
  */
  var $total;

  /**
  * page number for which the bar should be created, starting with 0
  *
  * @access   private
  * @var      Array
  */
  var $currentpage;

  /**
  * number of items per page
  *
  * @access   private
  * @var      Integer
  */
  var $pagebreak;


  /**
  * Constructor
  * Sets needed information
  * @access   public
  * @param    Array       $array
  **/
  function nav_bar($baseadress, $total, $currentpage, $pagebreak)
  {
    $this->baseadress = $baseadress;
    $this->total = $total;
    $this->currentpage = $currentpage;
    $this->pagebreak = $pagebreak;

    $this->adress_planchet();
  }


  /**
  * builds an adequate planchet out the baseadress so that the page number can be added to the string simply
  * @access   private
  **/
  function adress_planchet($baseadress = "")
  {
    //set new baseadress
    if($baseadress != "")
      $this->baseadress = $baseadress;

    //get URL parts
    $url = parse_url($this->baseadress);

    //split query string
    parse_str($url["query"], $query);
    if(isset($query["page"]))
      unset($query["page"]);
	if(isset($query["breakresult"]))
      unset($query["breakresult"]);


    //build new baseadress
    $this->baseadress = $url["path"] . "?";
    foreach($query as $key => $value)
		$this->baseadress .= "$key=$value&";
    $this->baseadress .=  "breakresult=".$this->pagebreak."&";
	$this->baseadress .=  "page=";
  }


  /**
  * returns the completely built navigation bar
  * @access   public
  **/
  function get_bar()
  {
	//return empty string on special cases
    if($this->total <= 0 ||
       $this->pagebreak <= 0)
      return "1";


    //calculate total number of pages
    $totalpages = ceil($this->total / $this->pagebreak) - 1;

    //get start and end page according to the NAV_BAR_PAGE_RANGE
    $startpage = $this->currentpage - NAV_BAR_PAGE_RANGE;
    $endpage = $this->currentpage + NAV_BAR_PAGE_RANGE;
    $endpage = ($this->currentpage + NAV_BAR_PAGE_RANGE > $totalpages)?$totalpages:$this->currentpage + NAV_BAR_PAGE_RANGE;

    $bar = "";

    //build startpage link
    if($startpage > 0)
      $bar = "<a href=\"" . $this->baseadress . "\">1</a>&nbsp;...&nbsp;";
    else
      $startpage = 0;

    //buildlinks within NAV_BAR_PAGE_RANGE
    for($i = $startpage; $i <= $endpage; $i++)
      if($i == $this->currentpage)
        $bar .= ($i + 1) . "&nbsp;";
      else
        $bar .= "<a href=\"" . $this->baseadress . "$i\">" . ($i + 1) . "</a>&nbsp;";

    //build endpage link
    if($endpage < $totalpages)
      $bar .= "...&nbsp;<a href=\"" . $this->baseadress . "$totalpages\">".($totalpages+1)."</a>";

    return $bar;
  }
}

?>