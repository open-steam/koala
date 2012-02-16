// created by: André Dietisheim (dietisheim@sphere.ch)
// created:	2002-04-22
// modified by: André Dietisheim (dietisheim@sphere.ch)
// modified: 2004-01-14
// version: 2.2.0

function Xmenu( sNavigationName, sNavigation, globals, styles, contents )
{
	if( !Xmenu.prototype.instances ) Xmenu.prototype.instances = new Array();
	Xmenu.prototype.instances[ Xmenu.prototype.instances.length ] = this; // store this instance in static Array

	this.index = Xmenu.prototype.instances.length - 1;

	this.sNavigationName = sNavigationName;
	this.sNavigation = sNavigation;
	this.iType = globals[ 0 ];
	this.iCloseDelay = globals[ 1 ] * 1000;
	this.bClick = globals[ 2 ];
	this.bMenuBelow = globals[ 3 ];
	this.bLeftAlign = globals[ 4 ];
	this.bKeepExpansionState = globals[ 5 ];
	this.bHighlightClickedNodes = globals[ 6 ];
	this.styles = styles;
	this.contents = contents;

	this.iContent = 0;
	this.tree = null;
	this.outNode = null;
	this.lastNode = null;
	this.absY = 0;
	this.timeout = null;
	this.mouseover = false;
	this.bOpened = false;

	iParentLayerWidth = ( is.iemac5up )? 0 : globals[ 7 ][ 2 ]; // XparentLayer disturbs Xlayer-events on iemac5
	iParentLayerHeight = ( is.iemac5up )? 0 : globals[ 7 ][ 1 ];
	this.xlayerParent = new XlayerParent( "XlayerParent" + this.index, globals[ 7 ][ 0 ], null, iParentLayerWidth, iParentLayerHeight );

	this.tree = this.buildTree( 0, 0, false, null, "tree" );

	this.nodeFound = null;
	this.navigationNode = null;
	if ( this.findNode( this.sNavigation, this.tree ) )
	{ // node indicated in request found
		this.navigationNode = eval( "this." + this.nodeFound );
	}
}

Xmenu.prototype.VERTICAL = 0;
Xmenu.prototype.HORIZONTAL = 1;
Xmenu.prototype.COLLAPSING = 2;	

Xmenu.prototype.buildTree = function( iAbsX, iAbsY, bSibling, sParent, sPath )
{	
		var node = this.buildNode( iAbsX, iAbsY, bSibling, sParent, sPath );
		this.iContent++;

		if ( this.iContent < this.contents.length && node.iLevel < this.contents[ this.iContent ][ 2 ] )
		{ // child
			node.child = this.buildTree(  node.absX, node.absY, false, "this." + node.sPath, node.sPath + ".child" );
		}
		if ( this.iContent < this.contents.length && node.iLevel == this.contents[ this.iContent ][ 2 ] )
		{ // sibling
			node.sibling = this.buildTree( node.absX, node.absY, true, node.sParent, node.sPath + ".sibling" );
		}
		node.xlayer = this.addXlayer( this.xlayerParent, node, this.styles )
		return node;
}

Xmenu.prototype.buildNode = function( iAbsX, iAbsY, bSibling, sParent, sPath )
{
	var node = new Object();
	node.child = null;
	node.sibling = null;
	node.sParent = sParent;
	node.sPath = sPath;

	node.sText = this.contents[ this.iContent ][ 0 ];
	node.sHref = this.contents[ this.iContent ][ 1 ];
	node.iLevel = this.contents[ this.iContent ][ 2 ];

	if ( this.iType == this.VERTICAL )
	{
		if ( !bSibling )
		{ // child
			if ( node.iLevel > 1|| ( node.iLevel == 1 && !this.bMenuBelow ) )
			{ // level 1 && menu to the right || level 2,3, ...: add width + xOffset
				node.absX = iAbsX + this.styles[ node.iLevel + 1 ][ 2 ] + this.styles[ node.iLevel + 1 ][ 0 ];
			}
			else
			{ // level 0, 1 || node 1 && menu below: add xOffset
				node.absX = iAbsX + this.styles[ node.iLevel + 1 ][ 0 ];
			}
			if ( node.iLevel != 1 || ( node.iLevel == 1 && !this.bMenuBelow ) )
			{ // level 0, 2, 3, ... : add yOffset
				node.absY = iAbsY + this.styles[ node.iLevel + 1 ][ 1 ];
			}
			else
			{ // level 1: add height of last node + yOffset
				node.absY = iAbsY + this.styles[ node.iLevel + 1 ][ 1 ] + this.styles[ node.iLevel ][ 3 ];
			}
		}
		else
		{ // sibling
			node.absX = iAbsX;
			node.absY = iAbsY + this.styles[ node.iLevel + 1 ][ 3 ];
		}
	}
	else if ( this.iType == this.HORIZONTAL )
	{
		if ( !bSibling )
		{ // child
			if ( this.bLeftAlign )
			{ // left aligned
				if ( !this.bMenuBelow && node.iLevel > 0 )  // menu appearing to the right of root-node && level 1,2,3,...: start at width of root-node
					node.absX = this.styles[ node.iLevel + 1 ][ 0 ] + this.styles[ 1 ][ 2 ] + this.styles[ 1 ][ 0 ];
				else // start at xOffset
					node.absX = this.styles[ node.iLevel + 1 ][ 0 ];

			}
			else
			{ // normal
				if ( !this.bMenuBelow && node.iLevel == 1 )  // menu appearing to the right of root-node && level 1...: add XOffset + width of root-node
					node.absX = iAbsX + this.styles[ node.iLevel + 1 ][ 0 ] + this.styles[ 1 ][ 2 ];
				else
					node.absX = iAbsX + this.styles[ node.iLevel + 1 ][ 0 ];
			}
			if ( node.iLevel > 0 )
			{ // level 1, 2, 3, ...: add height of last node + yOffset
				if ( !this.bMenuBelow && node.iLevel == 1 )
					node.absY = iAbsY + this.styles[ node.iLevel + 1 ][ 1 ];
				else
					node.absY = iAbsY + this.styles[ node.iLevel ][ 3 ] + this.styles[ node.iLevel + 1 ][ 1 ];
			}
			else
			{ // level 0: add yOffset
				node.absY = iAbsY + this.styles[ node.iLevel + 1 ][ 1 ];
			}
		}
		else
		{ // sibling
			node.absX = iAbsX + this.styles[ node.iLevel ][ 2 ];
			node.absY = iAbsY;
		}
	}
	else if ( this.iType == this.COLLAPSING )
	{
		if ( !bSibling )
		{ // child
			node.absX = iAbsX + this.styles[ node.iLevel + 1 ][ 0 ];
			node.absY = iAbsY + this.styles[ node.iLevel + 1 ][ 1 ];
		}
		else
		{ // sibling
			node.absX = iAbsX;
			node.absY = iAbsY + this.styles[ node.iLevel + 1 ][ 3 ];
		}
	}

	return node;
}

Xmenu.prototype.addXlayer = function( xparentLayer, node, styles )
{
	var parent =	null;
	var x =	"left"
	var y =	"top";
	var offsetX = node.absX;
	var offsetY = node.absY;
	var w =	styles[ node.iLevel + 1 ][ 2 ];
	var h = styles[ node.iLevel + 1 ][ 3 ];
	var clipTop = 0;
	var clipRight = w;
	var clipBottom = h;
	var clipLeft = 0;
	var zIndex =	node.iLevel;
	var visibility = false;
	var bgcolor = styles[ node.iLevel + 1 ][ 5 ][ 0 ];
	var fading =	styles[ node.iLevel + 1 ][ 4 ];
	var events =	[ 
		"onmouseover", "Xmenu.prototype.instances[" + this.index + "].onmouseover( Xmenu.prototype.instances[" + this.index + "]." + node.sPath + ")",
		"onmouseout", "Xmenu.prototype.instances[" + this.index + "].onmouseout( Xmenu.prototype.instances[" + this.index + "]." + node.sPath + ")",
		"onclick", "Xmenu.prototype.instances[" + this.index + "].onclick( Xmenu.prototype.instances[" + this.index + "]." + node.sPath + ")"
		];						
	var sText =  node.sText;
	var align =  styles[ node.iLevel + 1 ][ 5 ][ 2 ];
	var fgcolor =  styles[ node.iLevel + 1 ][ 5 ][ 1 ];
	var href =  this.createHref( node ).sHref
	var bold =  styles[ node.iLevel + 1 ][ 5 ][ 3 ];
	var fontface =  styles[ node.iLevel + 1 ][ 5 ][ 4 ];
	var fontsize =  styles[ node.iLevel + 1 ][ 5 ][ 5 ];
	if ( styles[ node.iLevel + 1 ][ 5 ][ 7 ] )
	{	// icon defined
		var icon = ( node.child || styles[ node.iLevel + 1 ][ 5 ][ 6 ] )? styles[ node.iLevel + 1 ][ 5 ][ 7 ] : "img/spacer.gif";
		var icon_w = styles[ node.iLevel + 1 ][ 5 ][ 8 ];
		var icon_h = styles[ node.iLevel + 1 ][ 5 ][ 9 ];
		var iconBorder = styles[ node.iLevel + 1 ][ 5 ][ 10 ];
	}
	else
	{	// icon not defined
		var icon = null;
		var icon_w = 0;
		var icon_h = 0;
		var iconBorder = 0;
	}
	var src = null; // iframe: src

	return new Xlayer( parent, xparentLayer, x, y, offsetX, offsetY, w, h, clipTop, clipRight, clipBottom, clipLeft, zIndex, visibility, bgcolor, fading, events, sText, bold, align, fgcolor, href, icon, icon_w, icon_h, iconBorder, fontface, fontsize, src );
}

Xmenu.prototype.create = function()
{
	this.createXlayers( null );
	this.setVisibSiblings( this.tree, true );
}

Xmenu.prototype.createXlayers = function( tree )
{
	if ( !tree ) 
	{ // call without param -> take root node
		tree = this.tree;
	}
	if ( tree.child )
	{
		this.createXlayers( tree.child );
	}
	if ( tree.sibling )
	{
		 this.createXlayers( tree.sibling );
	}

	tree.xlayer.create();
}

Xmenu.prototype.open = function()
{	
	if ( this.navigationNode != null )
	{
		this.openLastClicked();
	}
	else
	{
		this.setVisibSiblings( this.tree, true );
	}
	this.bOpened = true;
	this.openListener.menuOpened( this );
}

Xmenu.prototype.openLastClicked = function()
{
	node = this.navigationNode;
	this.lastNode = node;
	if ( node.child != null )
	{
		this.setVisibSiblings( node.child, true );
	}
	while ( node != null )
	{
		this.highlightClickedNode( node );
		if ( node.sParent != null )
		{
			this.setVisibSiblings( eval( node.sParent ).child, true );
			node = eval( node.sParent );
		}
		else
		{
			this.setVisibSiblings( this.tree, true );
			node = null;
		}
	}
}

Xmenu.prototype.setOpenListener = function( openListener )
{
	this.openListener = openListener;
}

Xmenu.prototype.findNode = function( sText, node )
{
	if ( this.nodeFound )
		return true;

	if ( node.child )
		this.findNode( sText, node.child );

	if ( node.sibling )
		this.findNode( sText, node.sibling );

	if ( sText == node.sText )
		this.nodeFound = node.sPath;

	if ( this.nodeFound ) 
		return true;
	else 
		return false;
}

Xmenu.prototype.close = function()
{
	if ( this.bOpened && !this.bKeepExpansionState )
	{
		this.setVisibChildren( this.tree.child, false );
		if ( this.iType == this.COLLAPSING )
			this.setCollapsePos( this.tree );
		if ( this.bClick && this.lastNode )
			this.clearHighlightChildren( this.tree );

		this.bOpened = false;
		this.closeListener.menuClosed( this );
	}
}

Xmenu.prototype.setCloseListener = function( closeListener )
{
	this.closeListener = closeListener;
}

Xmenu.prototype.onmouseover = function( node )
{
	this.mouseover = true; // set flag: 'onmouseover executed'
	if ( ( this.iType == this.VERTICAL || this.iType == this.HORIZONTAL ) && !this.bClick )
	{
		if ( !this.bOpened )
		{ // this menu will open
			this.bOpened = true;
			this.openListener.menuOpened( this );
		}

		if ( this.outNode )
		{
			if ( this.outNode.iLevel > node.iLevel )
			{
				this.setVisibSiblings( eval( this.outNode.sParent + ".child" ), false );
				this.setVisibSiblings( this.outNode.child, false );
			}
			else if ( this.outNode.iLevel == node.iLevel )
			{
				this.setVisibSiblings( this.outNode.child, false );
			}
		}
		this.setVisibSiblings( node.child, true );
	}
	if ( this.checkClickPath( node ) )
	{ // current node is not the node that was clicked (or its parents)
		this.highlight( node, true );
	}
	
	return true;
}

Xmenu.prototype.onmouseout = function( node )
{
	if ( this.checkClickPath( node ) )
	{
		this.highlight( node, false );
	}

	if ( ( this.iType == this.VERTICAL || this.iType == this.HORIZONTAL ) && !this.bClick )
	{ // close menu if no onmouseover until timeout
		setTimeout( "Xmenu.prototype.instances[" + this.index + "].checkOnmouseover()", this.iCloseDelay );
	}

	this.outNode = node;
	this.mouseover = false;

	return true;
}

Xmenu.prototype.checkClickPath = function( node )
{
	if ( this.bHighlightClickedNodes )
	{
		lastNode = this.lastNode;
		while ( lastNode != null )
		{
			if ( lastNode == node )
			{ // node clicked found
				return false;
			}
			else
			{ // continue looking for it
				lastNode = eval( lastNode.sParent );
			}
		}
		return true;
	}
	else
	{
		return true;
	}
}

Xmenu.prototype.checkOnmouseover = function()
{
	if ( !this.mouseover && !( this.bKeepExpansionState && this.bClick ) ) // onmouseover executed since delay?
	{
		this.close();
	}
}

Xmenu.prototype.onclick = function( node )
{	
	if ( node.sHref )
	{ // follow href
		window.document.location.href = node.sHref;
	}
	else if (
		( ( this.iType == this.VERTICAL || this.iType == this.HORIZONTAL ) && this.bClick ) || 
		this.iType == this.COLLAPSING )
	{
		this.highlight( node, true );
		if ( !this.bOpened )
		{ // this menu will open
			this.bOpened = true;
			this.openListener.menuOpened( this );
		}
	
		if ( this.iType == this.COLLAPSING )
		{
			this.collapse( node );
		}
		else if ( ( this.iType == this.VERTICAL || this.iType == this.HORIZONTAL ) && this.bClick )
		{
			this.showClickedNodes( node, this.lastNode );
		}
		this.lastNode = node;
	}
}

Xmenu.prototype.showClickedNodes = function( node, hideNode )
{
	if ( hideNode == node && node.child && node.child.xlayer.isVisible() )
	{ // reclose branch
		this.setVisibChildren( node.child, false );
		this.clearHighlightChildren( node, false );
	}
	else
	{
		if ( hideNode )
		{ // hide old nodes
			this.setVisibChildren( this.tree, false );
			this.clearHighlightChildren( this.tree, false );
		}
		if ( node.child ) this.setVisibSiblings( node.child, true );
		while ( node )
		{ // show new nodes
			this.highlightClickedNode( node, true );
			if ( node.sParent ) 
			{
				this.setVisibSiblings( eval( node.sParent ).child, true );
			}
			else
			{
				this.setVisibSiblings( this.tree, true );
			}
			node = eval( node.sParent );
		}
	}
}

Xmenu.prototype.clearHighlightChildren = function( node )
{
	if ( node )
	{
		if	( node.child )
		{
			 this.clearHighlightChildren( node.child );
		}
		if ( node.sibling )
		{
			 this.clearHighlightChildren( node.sibling );
		}
		this.highlight( node, false );
	}
}

Xmenu.prototype.collapse = function( node )
{
	this.showClickedNodes( node, this.lastNode );
	this.setCollapsePos( this.tree );
}

Xmenu.prototype.setCollapsePos = function( node )
{
	if ( node == this.tree ) // start looping
		this.absY = this.tree.xlayer.y;
			
	if ( node.xlayer.visibility )
	{
		node.xlayer.setPos( node.xlayer.x, this.absY );
		this.absY += node.xlayer.h;
	}

	if ( node.child ) 
		this.setCollapsePos( node.child );
	if ( node.sibling ) 
		this.setCollapsePos( node.sibling );
}

Xmenu.prototype.highlight = function( node, bHighlight )
{
	var index = ( bHighlight )? 6 : 5;		// style for mouseover or mouseout ?
	node.xlayer.setBgColor( this.styles[ node.iLevel + 1 ][ index ][ 0 ] );
	if ( !is.nn4up && !is.iemac5up ) node.xlayer.setFgColor( this.styles[ node.iLevel + 1 ][ index ][ 1 ] );
}

Xmenu.prototype.highlightClickedNode = function( node )
{
	if ( node && this.bHighlightClickedNodes )
	{
		node.xlayer.setBgColor( this.styles[ 0 ][ 0 ] );
		if ( !is.nn4up && !is.iemac5up ) 
			node.xlayer.setFgColor( this.styles[ 0 ][ 1 ] );
	}
}

Xmenu.prototype.setVisibSiblings = function( node, bVisibility )
{
	if ( node )
	{
		if ( node.sibling )
		{
			 this.setVisibSiblings( node.sibling, bVisibility );
		}
		node.xlayer.setVisibility( bVisibility );
	}
}

Xmenu.prototype.setVisibChildren = function( node, bVisibility )
{
	if ( node )
	{
		if	( node.child )
		{
			 this.setVisibChildren( node.child, bVisibility );
		}
		if	( node.sibling )
		{
			 this.setVisibChildren( node.sibling, bVisibility );
		}
		node.xlayer.setVisibility( bVisibility );
	}
}

Xmenu.prototype.createHref = function( node )
{
	if ( node.sHref == "#" )
	{
		node.sHref = document.URL.replace( new RegExp( this.sNavigationName + "=[^&]*", "" ), this.sNavigationName + "=" + escape( node.sText ) );	// create link to same page poping up current menu-entry
		if ( node.sHref.indexOf( this.sNavigationName + "=" ) < 0 )
		{
			node.sHref = document.URL + "?" + this.sNavigationName + "=" + escape( node.sText );
		}
		return node;
	}
	return node;
}

Xmenu.prototype.isNavigationNodeFound = function()
{
	return this.navigationNode != null;
}
