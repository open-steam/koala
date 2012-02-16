<?php

class RssChannel
{
	var $channel_name;

	var $channel_link;

	var $channel_description;

	var $channel_code_setting;

	function RssChannel( $pChannelName, $pChannelLink, $pChannelDescription, $pChannelCodeSetting = "UTF-8")
	{
		$this->set_name( $pChannelName );
		$this->set_link( $pChannelLink);
		$this->set_description( $pChannelDescription );
		$this->set_code( $pChannelCodeSetting );
	}
	
	function set_name ( $pChannelName )
	{
		$this->channel_name = $pChannelName;
	}
	
	function set_link ( $pChannelLink )
	{
		$this->channel_link = $pChannelLink;
	}
	
	function set_description ( $pChannelDescription )
	{
		$this->channel_description = $pChannelDescription;
	}
	
	function set_code ( $pChannelCodeSetting = "UTF-8" )
	{
		$this->channel_code_setting = $pChannelCodeSetting;
	}

	function generate_http_header()
	{
		if ( headers_sent() )
		{
			throw new Exception( "HEADERS ALREADY SENT" );
		}
		// HTTP-Header
		header( "Content-Type: text/xml" );
		header( "Expires: " . gmdate( "D, d m Y H:i:s" ) . " GMT" );
		header( "Last-Modified: " . gmdate( "D, d m Y H:i:s" ) . " GMT" );
		header( "Cache-Control: no-store, no-cache, must-revalidate" );
		header( "Cache-Control: post-check=0, pre-check=0", false );
		header( "Pragma: no-cache" );
	}

	function generate_xml_header()
	{
		// XML-Header
		$xml_header =  
			"<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n" .
			//"<!DOCTYPE rss PUBLIC \"-//Netscape Communications//DTD RSS 2.0//EN\" \"http://my.netscape.com/publish/formats/rss-2.0.dtd\">".
			"<rss version=\"2.0\"\n" .
			"xmlns:dc=\"http://purl.org/dc/elements/1.1/\"\n" .
			"xmlns:sy=\"http://purl.org/rss/1.0/modules/syndication/\"\n".
			"xmlns:admin=\"http://webns.net/mvcb/\"\n".
			"xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\"\n".
			"xmlns:content=\"http://purl.org/rss/1.0/modules/content/\">\n" .
			"<channel>\n".
			"\t<title>" . htmlspecialchars( $this->channel_name ) . "</title>\n" .
			"\t<link>" . $this->channel_link . "</link>\n" .
			"\t<description>". htmlspecialchars( $this->channel_description, ENT_COMPAT, "UTF-8" ) . "</description>\n" .
			"\t<dc:language>de</dc:language>\n" .
			"\t<dc:date>" . date( "r" ) . "</dc:date>\n" .
			"\t<admin:generatorAgent>koaLA RSS Feed</admin:generatorAgent>\n" ;
		return $xml_header;
	}

	function generate_item( $pTitle, $pBody, $pContent, $pAuthor, $pTimestamp, $pCategory, $pLink )
	{
		$item =
			"\t<item>\n".
			"\t\t<title>" . htmlspecialchars( $pTitle, ENT_COMPAT, "UTF-8" ) . "</title>\n".
			"\t\t<dc:creator>" . htmlspecialchars( $pAuthor, ENT_COMPAT, "UTF-8" ) . "</dc:creator>\n".
			"\t\t<dc:date>" . date( "r", $pTimestamp ) . "</dc:date>\n".
			"\t\t<link>" . $pLink . "</link>\n".
			"\t\t<category>" . htmlspecialchars( $pCategory, ENT_COMPAT, "UTF-8" ) . "</category>".
			"\t\t<dc:description><![CDATA[" . $pBody . "]]></dc:description>".
			"\t\t<content:encoded><![CDATA[<html><body>" . $pContent . "<br/></body></html>]]></content:encoded>\n".
			"\t</item>\n";
		return $item;
	}

	function generate_xml_footer()
	{
		$xml_footer =
			"\t</channel>\n</rss>";
		return $xml_footer;
	}

}
?>
