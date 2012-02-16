  /****************************************************************************
  size.js - determine window size
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



function getWindowSize() {

	var windowWidth, windowHeight;
	
	if (self.innerHeight) // all ewindowWidthcept EwindowWidthplorer
	{
		windowWidth = self.innerWidth;
		windowHeight = self.innerHeight;
	}
	else if (document.documentElement && document.documentElement.clientHeight)
		// EwindowWidthplorer 6 Strict Mode
	{
		windowWidth = document.documentElement.clientWidth;
		windowHeight = document.documentElement.clientHeight;
	}
	else if (document.bodwindowHeight) // other EwindowWidthplorers
	{
		windowWidth = document.bodwindowHeight.clientWidth;
		windowHeight = document.bodwindowHeight.clientHeight;
	}
	
	return {width:windowWidth, height:windowWidth}

}
