// created by: André Dietisheim (dietisheim@sphere.ch)
// created: 2001-12-20
// modified by: André Dietisheim (dietisheim@sphere.ch)
// modified: 2004-01-12
// version: 2.1.0

function Xlayer( sParent, xlayerParent, x, y, offsetX, offsetY, w, h,  clip_top, clip_right, clip_bottom, clip_left, zIndex, visibility, bgcolor, fading, events, desc, bold, align, fgcolor, href, icon, icon_w, icon_h, iconBorder, fontface, fontsize, src )
{
	if ( !Xlayer.prototype.instances )
		Xlayer.prototype.instances = new Array();
	Xlayer.prototype.instances[ Xlayer.prototype.instances.length ] = this; // Store this Instance In static array

	this.index = Xlayer.prototype.instances.length - 1;
	this.sParent = sParent;
	this.parent = null;
	this.xlayerParent = xlayerParent;
	this.lyr = null;
	this.id = "Xlayer"+this.index
	this.x = x || 0;
	this.y = y || 0;
	this.offsetX = offsetX ||	0;
	this.offsetY = offsetY ||	0;
	this.w = w ||	0;
	this.h = h || 0;
	this.clip_top = clip_top || 0;
	this.clip_right = clip_right || w;
	this.clip_bottom = clip_bottom ||	h;
	this.clip_left = clip_left || 0;
	this.zIndex = zIndex || 0;
	this.visibility = visibility;
	this.bgcolor = bgcolor || "black";

	// caption ---
	this.sText = desc || null;
	this.bold = bold || false;
	this.align = align || "center";
	this.fgcolor = fgcolor || "white";
	this.sHref = ( ( is.nn4up || is.iewin5up ) && !href )? "#" : href; // nn4 always need a href to process clicks, iewin to avoid text-cursor
	this.fontface = fontface || "Helvetica";
	this.fontsize = fontsize || 2;
	this.icon = icon ||	null;
	this.tmpImg = new Image(); // preload images
	this.tmpImg.src = icon;
	this.icon_w = icon_w || 0;
	this.icon_h = icon_h || 0;
	this.iconBorder = iconBorder || 0;

	// iframe ----
	this.iframe = null;
	this.scrollbars = null;
	this.src = src ||	null;
	this.events = events || null; // array: event, func, event, func, ...
	this.fading =	fading || null; // array: start, stop, steps, delay
	this.iOpacity = 0;
}

Xlayer.prototype.create = function()
{
	this.parent = XlayerParent.prototype.getLayer( this.sParent ); // parent = another layer or document.body
	this.parentCoordsOnly = XlayerParent.prototype.getLayer( this.xlayerParent.sId );

	if ( is.nn4up )
	{
		if ( this.w == "100%" )
			this.lyr = new Layer( this.parent.innerWidth, this.parent );
		else
			this.lyr = new Layer( this.w, this.parent );
	}
	else if ( is.iewin5up || is.iemac5up || is.gk || is.sf || is.op7up )
	{
		this.lyr = document.createElement( "DIV" ); // create layer
		this.lyr.style.position = "absolute";
		this.lyr.style.overflow = "hidden";
		this.lyr.id = this.id;
		this.parent.appendChild( this.lyr ); // insert into DOM
	}

	this.setVisibility( this.visibility );
	this.setSize( this.w, this.h );
	this.setCaption( this.sText, this.bold, this.icon, this.icon_w, this.icon_h, this.iconBorder );
	this.setBgColor( this.bgcolor );
	this.setFgColor( this.fgcolor );
	this.setPos( this.x, this.y, this.offsetX, this.offsetY );
	this.setZindex( this.zIndex );
	this.setEvents( this.lyr, this.events );
	this.fade( this.fading );
}

Xlayer.prototype.kill = function()
{
	if ( is.nn4up )
	{
		for ( i = 0; i < document.layers.length ; i++ ) // scan trough layers-array in NN-DOM
		{
			this.setVisibility( false );
			if ( document.layers[i].id == this.lyr.id )	
			{
				index = i;
				//document.layers.splice(i, 1)
				//delete document.layers[i]
				document.layers[i] = null;
				break;
			}
		}
	}
	else if ( is.iewin5up || is.iemac5up || is.gk || is.sf || is.op7up )
	{
		var lyr;
		lyr = document.getElementById( this.lyr.id );
		document.body.removeChild( lyr );
	}
	this.iOpacity = 0;
}

Xlayer.prototype.setFgColor = function( color )
{
	if ( this.sText )
	{
		this.fgcolor = color;

		if ( is.nn4up )
			this.setCaption( this.sText, this.bold, this.icon, this.icon_w, this.icon_h, this.iconBorder );
		else if ( is.iewin5up || is.iemac5up || is.gk || is.sf || is.op7up )
		{
			if ( this.sText )
			{
				document.getElementById( this.id+"d" ).style.color = color;
				//this.setCaption( this.sText, this.bold, this.icon, this.icon_w, this.icon_h, this.iconBorder );
			}
		}
	}

}

Xlayer.prototype.setBgColor = function( color )
{
	this.bgcolor = color;

	if ( is.nn4up )
	{
		this.lyr.document.bgColor = color;
	}
	else if ( is.iewin5up || is.iemac5up || is.gk || is.sf || is.op7up )
	{
		this.lyr.style.backgroundColor = color;
	}
}

Xlayer.prototype.setSize = function( w, h )
{
	var old_w = this.w; // store old values
	var old_h = this.h;

	this.w = w; // store new values
	this.h = h;

	if ( is.nn4up )
	{
		if ( w == "100%" )
			this.lyr.resizeTo( window.innerWidth, h );
		else 
			this.lyr.resizeTo( w, h );
	}
	else if ( is.iewin5up || is.iemac5up || is.gk || is.sf || is.op7up )
	{
		if ( w == "100%" )
		{
			this.lyr.style.width = "100%";
			this.lyr.style.height = h + 'px';
		}
		else
		{
			this.lyr.style.width = w + 'px';
			this.lyr.style.height = h + 'px';
		}

		this.setClipping( this.clip_top, ( this.clip_right + w - old_w ),  ( this.clip_bottom + h - old_h ), this.clip_left );

		if ( is.iewin5up && this.iframe ) // recreate iframe on resize
			this.setIframe( this.src );
	}
}

Xlayer.prototype.setPos = function( x, y, offsetX, offsetY )
{
	var parent;
	if ( this.parentCoordsOnly )
		parent = this.parentCoordsOnly;
	else
		parent = this.parent;
		
	// calc x, y ---
	if ( x == "centered" )
		x = XlayerParent.prototype.getX( parent ) + ( XlayerParent.getW( parent ) / 2 ) - this.w / 2;
	else if ( x == "left" )
		x = this.xlayerParent.getX( parent );
	else if ( x == "right" )
		x = XlayerParent.prototype.getX( parent ) + XlayerParent.prototype.getW( parent ) - this.w;

	if ( y == "centered" )
		y = XlayerParent.prototype.getY( parent ) + ( XlayerParent.prototype.getH( parent ) / 2 ) - this.h / 2;
	else if ( y == "top" )
		y = XlayerParent.prototype.getY( parent );
	else if ( y == "bottom" )
		y = XlayerParent.prototype.getY( parent ) + XlayerParent.prototype.getH( parent ) - this.h;

	if ( offsetX )
		x += offsetX;
	if ( offsetY )
		y += offsetY;

	this.x = x;
	this.y = y;

	// set position ---
	if ( is.nn4up )
	{
		this.lyr.moveTo( this.x, this.y );
	}
	else if ( is.iewin5up || is.iemac5up || is.gk || is.sf || is.op7up )
	{
		this.lyr.style.top = this.y;
		this.lyr.style.left = this.x;
	}
}

Xlayer.prototype.setVisibility = function( visibility ) 
{
	this.visibility = visibility;
	if ( this.lyr ) 
	{
		if ( is.nn4up ) 
		{
			this.lyr.visibility = (visibility)? "show" : "hide";
		}
		else if ( is.iewin5up || is.iemac5up || is.gk || is.sf || is.op7up ) 
		{
			this.lyr.style.visibility = (visibility)? "visible" : "hidden";
		}
	}
}

Xlayer.prototype.isVisible = function() 
{
	return this.visibility;
}

Xlayer.prototype.setFontsize = function( fontsize )
{
	this.fontsize = fontsize;
}

Xlayer.prototype.setFontface = function( fontface )
{
	this.fontface = fontface;
}

Xlayer.prototype.setClipping = function( top, right, bottom, left )
{
	if ( is.iewin5up || is.iemac5up || is.gk || is.sf || is.op7up )
	{
		this.lyr.style.clip = "rect(" + top + "px " + right + "px " + bottom + "px " + left + "px)";
	}
	else if ( is.nn4up )
	{
		this.lyr.clip.top = top;
		this.lyr.clip.right = right;
		this.lyr.clip.bottom = bottom;
		this.lyr.clip.left = left;
	}
	this.clip_top = top;
	this.clip_right = right;
	this.clip_bottom = bottom;
	this.clip_left = left;
}

Xlayer.prototype.setZindex = function( zIndex )
{
	this.zIndex =	zIndex;

	if ( is.iewin5up || is.iemac5up || is.gk || is.sf || is.op7up )
	{
		this.lyr.style.zIndex = zIndex;
	}
	else if ( is.nn4up )
	{
		this.lyr.zIndex = zIndex;
	}
}

Xlayer.prototype.setEvents = function( element, events )
{
	if( events )
	{
		for ( i = 0; i < events.length; )
		{
			var evt = events[ i++ ];
			var func = events[ i++ ];

			if ( is.gk || is.sf || is.op7up ) element.setAttribute( evt, func, 0 );

			else if ( is.iewin5up || is.iemac5up ) element[ evt.toLowerCase() ] = new Function( func );

			else if ( is.nn4up )
			{
				element.captureEvents( Event[ evt.toUpperCase().substring( 2 ) ] );
				element[ evt.toLowerCase() ] = new Function( func );
			}
		}
	}
}

Xlayer.prototype.setBody = function( html )
{
	if ( is.iewin5up || is.iemac || is.op7up )
		this.lyr.innerHTML = html;
	else if ( is.gk || is.sf )
	{
		while ( this.lyr.hasChildNodes() )
				this.lyr.removeChild( this.lyr.firstChild );
		var r = this.lyr.ownerDocument.createRange();
		r.selectNodeContents( this.lyr );
		r.deleteContents();
		var df = r.createContextualFragment( html );
		this.lyr.appendChild( df );
	}
	else if( is.nn4up )
	{
		this.lyr.document.open()
		this.lyr.document.write( html );
		this.lyr.document.close();
	}
}

Xlayer.prototype.scroll = function( orientation, step )
{
	this.orientation = orientation;
	this.step = step;

	// scrolling possible (clipping present)
	if ( ( this.clip_right < this.w ) || ( this.clip_top != 0 ) || ( this.clip_left > 0 ) || ( this.clip_bottom < this.h ) ) 
	{ // scrolling possible
		if ( orientation == "horiz" )
		{
			if ( this.clip_left + step > 0 && this.clip_right  + step < this.w ) 
			{	// border reached?
				this.setPos(this.x - step, this.y);
				this.setClipping(this.clip_top, this.clip_right + step, this.clip_bottom, this.clip_left + step);
			}
		}
		else if ( orientation == "vert" )
		{
			if ( this.clip_top + step > 0 && this.clip_bottom + step < this.h ) 
			{	// border reached?
				this.setPos( this.x, this.y - step );
				this.setClipping( this.clip_top + step, this.clip_right, this.clip_bottom + step, this.clip_left );
			}
		}
	}
}

Xlayer.prototype.setOpacity = function( iOpac )
{
	if ( is.iewin5up || is.iemac5up )
		this.lyr.style.filter = "alpha(opacity=" + iOpac + ")";

	else if ( is.gk )
	{
		this.lyr.style.MozOpacity = iOpac / 100;//opac + "%";
	}
}

Xlayer.prototype.fade = function( fading )
{
	if ( fading )
	{
		start =	fading[ 0 ]; // opacity start value
		stop =	fading[ 1 ]; // stop
		steps =	fading[ 2 ]; // number of steps
		delay =	fading[ 3 ]; // delay in ms

		this.iOpacity = this.iOpacity + parseInt( ( stop - start ) / steps );
		this.setOpacity( this.iOpacity );

		if ( this.iOpacity < stop )
			setTimeout( "Xlayer.prototype.instances[" + this.index + "].fade( Xlayer.prototype.instances[" + this.index + "].fading )", delay);

		this.fading = fading;
		return true;
	}
}

Xlayer.prototype.setIframe = function( src, scrollbars )
{
	this.src =	src;

	if ( scrollbars != null )
	{
		this.scrollbars = ( scrollbars )? "yes"	: "no";
	}
	else if ( this.scrollbars == null )
	{
		this.scrollbars = "yes";			// default for scrollbars: 'yes'
	}

	if ( is.nn4up )
	{
		this.lyr.src = src;
	}
	else if ( is.iewin5 )
	{ // ugly workaround: ie5 basically cannot create dynamically : frame, iframe

		this.lyr.innerHTML = "<iframe width='100%' height='100%' frameborder='0' scrolling='" + this.scrollbars + "' id='" + this.id + "_iframe" + "'></iframe>";
		this.lyr.contentWindow = new Object();
		this.lyr.contentWindow.location = new Object();
		this.iframe = document.getElementById(this.id + "_iframe");		// store iframe
		this.lyr.contentWindow.location.iframe = this.iframe;
		this.lyr.contentWindow.location.iframe.id = "";
		this.lyr.contentWindow.location.iframe.src = src
	}
	else if ( is.iewin55up || is.iemac5up || is.gk || is.sf || is.op7up )
	{
		var iframe;
		iframe = document.createElement( "IFRAME" );			// create iframe
		iframe.src = src;
		iframe.name = this.id + "_iframe";
		iframe.scrolling = this.scrollbars;
		iframe.frameBorder = "0";
		iframe.style.visibility = "inherit";

		if ( is.iewin55up )
		{
			iframe.style.width = this.w + "px";
			iframe.style.height = this.h + "px";
		}
		else if ( is.iemac5up || is.gk || is.sf || is.op7up )
		{
			iframe.style.width = "inherit";
			iframe.style.height = "inherit";
		}

		while ( this.lyr.hasChildNodes() )
		{	// remove existing layer child-nodes
			this.lyr.removeChild( this.lyr.lastChild );
		}
		this.lyr.appendChild( iframe ) // insert iframe into layer

		this.iframe = iframe;
	}
}

Xlayer.prototype.setCaption = function( sText, bBold, sIcon, icon_w, icon_h, iIconBorder )
{
	this.sText = sText;
	this.icon = sIcon;
	this.icon_w = icon_w;
	this.icon_h = icon_h;

	var tab_head = '<table style="cursor: pointer;" width="100%" border="0" cellpadding="0" cellspacing="0">';
	var tab_foot = '</table>';

	if ( sText || sIcon )
	{
		// content ---
		var img = "", desc = "", html ="", tab_body = "";
		if ( sIcon )
			img = '<img src="' + sIcon + '" width="' + icon_w + '" height="' + icon_h + '">';
		if ( sText )
		{
			if ( is.nn4up )
				desc = '<font id="' + this.id + 'd" color="' + this.fgcolor + '" size="' + ( parseInt( "0" + ( this.fontsize / 4 ), 10 ) ) + '" face="' + this.fontface + '">' + ( ( bBold )? '<b>' : '' ) + sText + ( ( bBold )? '</b>' : '' ) + '</font>';
			else if ( is.gk || is.sf || is.iemac5up || is.iewin5up || is.op7up )
				desc = '<span id="' + this.id + 'd" style="color: ' + this.fgcolor + '; font-size: ' + this.fontsize + '; font-family: ' + this.fontface + '; ' + ( ( bBold )? ' font-weight: bold;' : '' ) + '" >' + sText + '</span>';
		}
		if ( this.sHref )
		{
			if ( is.nn4up || is.iewin5up ) // no '<a href' for gecko and iemac
				desc = "<a href='" + this.sHref + "' style='text-decoration: none;'>" + desc + "</a>";
		}

		// text & icons ---
		if ( sIcon && sText )
		{
			tab_body =
				'<tr>' +
					'<td nowrap ';
			if ( is.iemac5 )
			{
				tab_body += 'style="position: absolute; top: ' + ( ( this.h - this.fontsize ) / 2 ) + '; left: 0; bottom: ' + this.fontsize + '; right:' + ( this.w - icon_w - iIconBorder ) + '; height: ' + this.fontsize + '; width: ' + ( this.w - icon_w - iIconBorder ) + '; vertical-align: middle;" ';
			}
			tab_body +=
						'width="' + ( this.w - icon_w  - iIconBorder ) + '" height="' + this.h + '" align="' + this.align + '" valign="middle">' +
						desc +
					'</td>' +
					'<td ';
			if ( is.iemac5 )
			{
				tab_body += 'style="position: absolute; top: ' + ( ( this.h - icon_h ) / 2 ) + '; left: ' + ( this.w - icon_w - iIconBorder ) + '; bottom: ' + icon_h + '; right:' + ( icon_w + iIconBorder ) + 'height: ' + icon_h + '; width: ' + ( icon_w + iIconBorder ) + '" ';
			}
			tab_body +=
					'width="' + ( icon_w + iIconBorder ) + '" height="' + this.h + '" align="' + this.align + '" valign="middle" >' +
						img +
					'</td>' +
				'</tr>';
		}
		// text only ---
		else if ( sText && !sIcon )
		{
			tab_body = '<tr><td ';
			if ( is.iemac5 )
			{
				tab_body += 'style="position: absolute; top: 0; left: 0" ';
			}
			tab_body +=
				'width="' + this.w + '" height="' + this.h + '" align="' + this.align + '" valign="middle">' + desc + '</td></tr>';
		}
		// icon only ---
		else if ( sIcon && !sText )
		{
			tab_body = '<tr><td nowrap ';
			if ( is.iemac5 )
			{
				tab_body += 'style="position: absolute; top: 0; left: 0" ';
			}
			tab_body += 'width="' + this.w + '" height="' + this.h + '" align="' + this.align + '" valign="middle">' + sIcon + '</td></tr>';
		}
		html = tab_head + tab_body + tab_foot;
		this.setBody( html );
	}
}
