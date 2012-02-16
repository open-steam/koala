// JavaScript Document

tooltip = null;
document.onmouseup = setMousePosition;

function setMousePosition(e)
{
	x = (document.all) ? window.event.x + document.body.scrollLeft : e.pageX;
	y = (document.all) ? window.event.y + document.body.scrollTop  : e.pageY;
}

function showTooltip(id) 
{
	tooltip = document.getElementById(id);
	width=parseInt(tooltip.style.width);
	height=parseInt(tooltip.style.height);
	if( (y - height-10) < 0 ) newy = 0;
	else	newy = y - height-10;
	tooltip.style.display = "block";
	tooltip.style.left = (x - width - 10) + "px";
	tooltip.style.top  = newy + "px";
}

function hideTooltip() 
{
	if (tooltip != null) tooltip.style.display = "none";
}