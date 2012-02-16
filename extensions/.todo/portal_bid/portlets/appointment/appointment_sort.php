<?php

  /****************************************************************************
  appointment_sort.php - sort the the appointments within one portlet
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

  Author: Thorsten Schäfer <tms82@upb.de>

  ****************************************************************************/

function sort_appointments() {
  global $portlet;
  global $portlet_content;

  $app_order = $portlet->get_attribute("bid:portlet:app:app_order");
  if (isset($app_order) && $app_order == "latest_first") {
    $sort_order = SORT_DESC;
  }
  else {
    $sort_order = SORT_ASC;
  }
  foreach($portlet_content as $key => $appointment) {
    $year[$key] = $appointment["start_date"]["year"];
    $month[$key] = $appointment["start_date"]["month"];
    $day[$key] = $appointment["start_date"]["day"];
  }
  array_multisort($year, $sort_order, $month, $sort_order, $day, $sort_order, $portlet_content);
}
