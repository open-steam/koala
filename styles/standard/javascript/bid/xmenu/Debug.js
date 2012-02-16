function Debug()
{	
	this.outputElementName = "debug";
	this.sText = null;
}

debug = new Debug();

Debug.prototype.html = function( sText )
{	
	sTextAreaHtml =
		"<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\">" +
			"<tr>" +
				"<td colspan=\"3\">" +
					"<form>" +
						"<textarea type=\"text\" name=\"" + this.outputElementName + "\" id=\"debug\" cols=\"150\" rows=\"10\">" +
						"</textarea><br>" +
						"<input type=\"button\" value=\"select all\" onClick=\"javascript:document.getElementById( 'debug' ).select()\">" +
						"<input type=\"reset\" value=\"clear\">" +
					"</form>" +
				"</td>" +
			"</tr>" +
		"</table>";	
		
	document.write( sTextAreaHtml );
}

Debug.prototype.flushBuffer = function()
{
	var outputElement = this.getOutput();
	if ( outputElement )
	{
		outputElement.value = this.sText + "\n" + outputElement.value;
	}
}

Debug.prototype.bufferedWrite = function( sText )
{
	this.sText = sText + "\n" + this.sText;
}

Debug.prototype.write = function( sText )
{
	var outputElement = this.getOutput(); 
	if ( outputElement )
	{
		outputElement.value = sText + "\n" + outputElement.value;
	}
}

Debug.prototype.getOutput = function()
{
	var outputElement = null;
	if ( is.nn4up )
	{
		outputElement = document.forms[ "\"" + this.outputElementName + "\"" ];
	}
	else if ( is.gk || is.iewin5up || is.iemac5up || is.sf )
	{
		outputElement = document.getElementById( this.outputElementName );
	}
	return outputElement;
}