// created by: André Dietisheim (dietisheim@sphere.ch)
// created:	2001-31-12
// modified by: André Dietisheim (dietisheim@sphere.ch)
// modified: 2004-01-13
// version:	0.7.1

function Browser( browsers ) 
{
	this.browsers = browsers;	// browser detection array
	this.createBooleans();
}

Browser.prototype.createBooleans = function() 
{
	var name = navigator.appName;
	var cname = navigator.appCodeName;
	var usragt = navigator.userAgent;
	var ver = navigator.appVersion;

	for ( i = 0; i < this.browsers.length; i++ ) 
	{
		var browserArray = this.browsers[ i ]; // browsers-array

		var sCheck = browserArray[ 1 ]; // 'logical expr' that detects the browser
		var sCurrentVersion = browserArray[ 2 ]; // 'regexp' that gets current version
		var sBrand = browserArray[ 0 ]; // browser-obj 'property' (is.xx)
		var availableVersions = browserArray[ 3 ]; // 'versions' to check for

		if ( eval( sCheck ) )
		{ // browser recognized
			eval( "this." + sBrand + " = true" ); // browser-obj property (is.xx)

			var regexp, ver, sMinorVersion, sMajorVersion;
			regexp = new RegExp( sCurrentVersion );
			regexp.exec( usragt ); // parse navigator.userAgent
			sMajorVersion = RegExp.$1;
			sMinorVersion = RegExp.$2;

			for ( j = 0; j < availableVersions.length; j++ )
			{ // set objects for current an upper versions
				if ( parseFloat(availableVersions[ j ]) <= eval( sMajorVersion + "." + sMinorVersion ) )
				{
					eval( "this." + sBrand + availableVersions[ j ].substr( 0, 1 ) + availableVersions[ j ].substr( 2, 1 ) + "up = true" );
				}
				if ( parseFloat(availableVersions[ j ]) == eval( sMajorVersion + "." + sMinorVersion ) ) 
				{
					eval( "this." + sBrand + availableVersions[ j ].substr( 0, 1 ) + availableVersions[ j ].substr( 2, 1 ) + "= true" );
				}
			}
		}
	}
}

is = new Browser ( [
	// Internet Explorer Windows ---
	[ "iewin",
		"cname.indexOf( 'Mozilla' ) >= 0 && name.indexOf( 'Microsoft Internet Explorer' ) >= 0 && usragt.indexOf( 'MSIE' ) >= 0 && usragt.indexOf( 'Opera' ) < 0 && usragt.indexOf( 'Windows' ) >= 0", // IE detection expression
		"MSIE.([0-9]).([0-9])",	// regexpr for version (in navigator.userAgent)
		[ "5", "5.5", "6" ] ],	// published versions
	// Internet Explorer Macintosh ---
	[ "iemac",
		"cname.indexOf( 'Mozilla' ) >= 0 && name.indexOf( 'Microsoft Internet Explorer' ) >= 0 && usragt.indexOf( 'MSIE' ) >= 0 && usragt.indexOf('Opera') < 0 && usragt.indexOf('Mac') >= 0",
		"MSIE.([0-9]).([0-9])",
		[ "5", "5.1", "5.2" ] ],
	// Gecko (Mozilla, Galeon, Firebird, Netscape >=6.x) ---
	[ "gk", 
		"cname.indexOf( 'Mozilla' ) >= 0 && name.indexOf( 'Netscape' ) >= 0 && usragt.indexOf( 'Gecko' ) >= 0 && usragt.indexOf( 'Safari' ) < 0",
		"[rv[:| ]*([0-9]).([0-9])|Galeon\/([0-9]).([0-9])]",
		[ "0.9", "1.0", "1.1", "1.2", "1.3", "1.4", "1.5" ] ],
	// Netscape Navigator ---
	[ "nn",
		"cname.indexOf( 'Mozilla' ) >= 0 && name.indexOf( 'Netscape' ) >=0 && parseInt( ver ) <= 4",
		"([0-9]).([0-9])",
		[ "4", "4.5", "4.7", "4.8" ] ],
	// Opera ---
	[ "op",
		"cname.indexOf( 'Mozilla' ) >= 0 && ( name.indexOf( 'Microsoft Internet Explorer' ) >=0 || name.indexOf( 'Opera' ) >= 0 ) && usragt.indexOf( 'Opera' ) >= 0",
		"Opera.([0-9]).([0-9])",
		[ "5", "5.1", "6", "7", "7.1", "7.2" ] ],
	// Safari ---
	[ "sf",
		"cname.indexOf( 'Mozilla' ) >= 0 && name.indexOf( 'Netscape' ) >=0 && usragt.indexOf('AppleWebKit' ) >= 0 && usragt.indexOf('Safari') >= 0",
		"AppleWebKit\/([0-9])",
		[ "48", "85" ] ]
] );
