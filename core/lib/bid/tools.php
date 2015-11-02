<?php

/****************************************************************************
 tools.php - Various tool functions
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

 Author: Thorsten Schaefer <tms82@upb.de>

 ****************************************************************************/

/*
 * Check if the given string is a valid width
 * i.e. is a number with an optional suffix pt or %
 * and is within its ranges. If the column width is
 * not valid, the given default value will be returned.
 */
function check_width_string($column_width, $pc_min, $pc_max, $px_min, $px_max, $default_value) {
 if (preg_match('/([0-9]+)(px|%){0,1}$/', trim($column_width), $substring)) {
    if ($substring[2] == "") {
        $substring[2] = "px";
    }
    if ($substring[2] == "%") {
      if ($substring[1] <= $pc_max && $substring[1] >= $pc_min) {
        return $substring[1].$substring[2];
      }
    }
    else if ($substring[2] == "px") {
      if ($substring[1] <= $px_max && $substring[1] >= $px_min) {
        return $substring[1].$substring[2];
      }
    }
  }
  return $default_value;
}

/**
 * If the given value ends with a percent sign then this sign is removed
 * from the output. Otherwise an empty string is returned.
 */
function extract_percentual_length($value) {
  if (preg_match('/([0-9]+)(%)$/', trim($value), $substring)) {
    return $substring[1];
  }

  return "";
}

/**
 * Remove any trailing % or pt signs from the given length value.
 * Return the empty string if the given value is not a length.
 */
function extract_length($value) {
  if (preg_match('/([0-9]+)(px|%){0,1}$/', trim($value), $substring)) {
    return $substring[1];
  }

  return "";
}

/**
 * Check if the given length is a relative length and convert
 * it to an absolute length using the given base value.
 */
function calculate_absolute_length($length, $base_value) {
  if (($relative_length = extract_percentual_length($length)) != "") {
    return floor($base_value * $relative_length / 100);
  } else {
    return extract_length($length);
  }
}
?>
