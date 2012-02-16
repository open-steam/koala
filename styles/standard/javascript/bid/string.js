  /****************************************************************************
  str.js - str helper 
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

  Author: Benedikt Redmer
  EMail: ben@redmer.net

  ****************************************************************************/



function trimString(str) {

	while (str.search(new RegExp("  ", "g")) != -1)
		str = str.replace(new RegExp("  ", "g"), " ");

	str = str.replace(new RegExp("^ ", "g"), "");
	str = str.replace(new RegExp(" $", "g"), "");
	
	return str;

}



function splitString(str, chr) {

	var a = str.split(chr);
	
	while (a.length>0 && a[0]=="")
		a.pop();
	
	return a;
	
}

function quoteString(str) {

        str = str.replace(new RegExp(",", "g"), "&#44;");

        return str;

}

