  /****************************************************************************
  array.js - array helper functions
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



Array.prototype.search = function(search_value) {
	
	var found = false;
	var i = 0;
	
	while ( i<this.length && !found ) {
		if(String(this[i])==String(search_value))
			found = true;
		i++;
	} 
	
	return found;

}; 



Array.prototype.popAll = function(search_value) {
	
	while (this.length>0)
		this.pop();
	
}; 
