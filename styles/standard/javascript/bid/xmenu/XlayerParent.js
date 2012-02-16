// created by: André Dietisheim (dietisheim@sphere.ch)
// created:	2001-12-20
// modified by: André Dietisheim (dietisheim@sphere.ch)
// modified: 2004-01-12
// version: 1.3.0

function XlayerParent( sLayerId, sImg, sDesc, iWidth, iHeight )
{
	// static var --------
	if( !XlayerParent.prototype.instances ) XlayerParent.prototype.instances = new Array();
	XlayerParent.prototype.instances[ XlayerParent.prototype.instances.length ] = this;				// store this instance in static Array

	this.sId = this.create( sLayerId, sImg, sDesc, iWidth, iHeight )
}

XlayerParent.prototype.create = function( sLayerId, sImg, sDesc, iWidth, iHeight )
{
	this.sParentLayerId = sLayerId;

	var sLayer = "";
	var content_str = '';

	if ( sImg )
		sContent = '<img src="' + sImg + '" width="' + iWidth + '" height="' + iHeight + '" border="0" >';
	else if ( sDesc )
		sContent = sDesc;

	// nn4up ----------
	if ( is.nn4up )
	{
		var sLayer = '<ilayer id="' + sLayerId + '" top=0 left=0 width=' + iWidth + ' height=' + iHeight + ' ></ilayer>';
		document.write( sLayer );
		return sLayerId;
	}

	// iewin5up, iemac5up, gk --------
	else if ( is.iewin5up || is.iemac5up || is.gk || is.sf || is.op7up )
	{
		var sLayer = '<div id="' + sLayerId + '" style="position:relative; width: ' + iWidth + 'px; height: ' + iHeight + 'px; "></div>';
		document.write( sLayer );
		return sLayerId;
	}
	else
	{
		return null;
	}
}

XlayerParent.prototype.getLayer = function( sLayerId )
{
	var layer = null;

	if ( sLayerId )
	{	// id supplied
		if ( is.iewin5up || is.iemac5up || is.gk || is.sf || is.op7up )
			return document.getElementById( sLayerId );
		else if ( is.nn4up )
			return document.layers[ sLayerId ];
	}
	else if ( !sLayerId )
	{	// null supplied
		if ( is.iewin5up || is.iemac5up || is.gk || is.sf || is.op7up )
			return document.body;
		else if ( is.nn4up )
			return window;
	}
}


XlayerParent.prototype.getX = function( layer )
{
	var x = 0;

	if ( is.nn4up )
	{
		if ( layer != window )			
			x = layer.pageX;
	}
	else if ( is.gk || is.iemac5up || is.iewin5up || is.sf || is.op7up )
	{
		if ( layer != document.body )
		{
			currentX = 0;
			object = layer;
			while ( object )
			{
				currentX += object.offsetLeft;
				object = object.offsetParent;
			}
			x = currentX;
		}

		if ( is.iemac5up )
			x += parseInt( "0" + document.body.currentStyle.marginLeft, 10  );

	}
	return x;
}


XlayerParent.prototype.getY = function( layer )
{
	var y = 0;

	if ( is.nn4up )
	{
		if ( layer != window )  y = layer.pageY;
	}
	else if ( is.gk || is.iewin || is.iemac5up || is.sf || is.op7up )
	{
		if ( layer != document.body )
		{
			currentY = 0;
			object = layer;
			while ( object )
			{
				currentY += object.offsetTop;
				object = object.offsetParent;
			}
			y = currentY;
		}
		if ( is.iemac5up )
			y += parseInt( "0" + document.body.currentStyle.marginTop, 10  );
	}

	return y;
}


XlayerParent.prototype.getW = function( layer )
{
	var w = 0;

	if ( is.nn4up )
	{
		if ( layer == window )
			return window.innerWidth;
		else
			return layer.clip.width;
	}
	else if ( is.gk || is.iemac5up || is.sf || is.op7up )
	{
		if ( layer == document.body )
			return window.innerWidth;
		else
			return layer.offsetWidth;
	}
	else if ( is.iewin5up )
	{
		if ( layer == document.body )
			return document.body.clientWidth;
		else
			return layer.offsetWidth;
	}
}


XlayerParent.prototype.getH = function( layer )
{
	var h = 0;

	if ( is.nn4up )
	{
		if ( layer == window )
			return window.innerHeight;
		else
			return layer.clip.height;
	}
	else if ( is.gk || is.iemac5up || is.sf || is.op7up )
	{
		if ( layer == document.body )
			return window.innerHeight;
		else
			return layer.offsetHeight;
	}
	else if ( is.iewin5up )
	{
		if ( layer == document.body )
			return document.body.clientHeight;
		else
			return layer.offsetHeight;
	}
}
