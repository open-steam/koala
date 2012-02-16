// created by: André Dietisheim (dietisheim@sphere.ch)
// created:	2002-05-11
// modified by: André Dietisheim (dietisheim@sphere.ch)
// modified: 2003-10-10
// version: 2.0.0 Beta
// tested: IE 5x Win, IE 5x Mac, Gecko, NN 4x

function Xmenus( sNavigationName, sNavigation )
{
		if( !Xmenus.prototype.instances ) Xmenus.prototype.instances = new Array();
		Xmenus.prototype.instances[ Xmenus.prototype.instances.length ] = this;

		this.index = Xmenus.prototype.instances.length - 1;

		this.iCloseDelay = 1;
		this.xmenus = new Array();

		this.sNavigationName = sNavigationName;
		this.sNavigation = sNavigation;
		this.navigationMenu = null;

		this.lastMenu = null;
		this.timeout = null;
		this.bReopenDisabled = false;
}

Xmenus.prototype.add = function( entry )
{
	this.xmenus[ this.xmenus.length ] = new Xmenu( this.sNavigationName, this.sNavigation, entry[ 0 ], entry[ 1 ], entry[ 2 ] );
}

Xmenus.prototype.create = function()
{
	for ( j = 0; j < this.xmenus.length; j++ )
	{
		this.xmenus[ j ].setOpenListener( this );
		this.xmenus[ j ].setCloseListener( this );
		this.xmenus[ j ].create();
		if ( this.xmenus[ j ].isNavigationNodeFound() )
		{
			this.navigationMenu = this.xmenus[ j ];
			this.lastMenu = this.xmenus[ j ];
			this.xmenus[ j ].open();
		}
	}
}

Xmenus.prototype.menuOpened = function( xmenu )
{ // fired by Xmenu on menu open
	if ( this.lastMenu != null && this.lastMenu != xmenu )
	{
		this.bReopenDisabled = true;
		this.lastMenu.close();
		this.bReopenDisabled = false;
	}
	this.bOpened = true;
	this.lastMenu = xmenu;
}

Xmenus.prototype.menuClosed = function( xmenu )
{  // fired by Xmenu on menu close
	if ( !this.bReopenDisabled )
	{
		this.timeout = setTimeout( "Xmenus.prototype.instances[" + this.index + "].reopenAfterClose()", this.iCloseDelay * 1000 );
	}
	this.bOpened = false;
}

Xmenus.prototype.reopenAfterClose = function()
{
	if ( !this.bOpened && this.navigationMenu != null )
	{ //no other menu is opened -> open this one
		this.navigationMenu.open();
	}
}
