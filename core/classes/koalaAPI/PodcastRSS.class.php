<?php

class PodcastRSS
{

	var $channel_name;
	var $channel_link;
	var $channel_description;
	var $channel_code_setting;
  var $channel_author;
  var $channel_date;

	public function __construct( $name, $link, $description, $date = FALSE,  $author = "koaLA System",  $code_setting = "UTF-8")
	{
		$this->channel_name = $name;
		$this->channel_link = $link;
		$this->channel_description = $description;
		$this->channel_code_setting = $code_setting;
    $this->channel_author = $author;
    if ($date === FALSE) $date = time();
    $this->channel_date = $date;
	}

	public function generate_http_header()
	{
		if ( headers_sent() )
		{
			throw new Exception( "Headers already sent." );
		}
		header( "Content-Type: text/xml" );
		header( "Expires: " . gmdate( "D, d m Y H:i:s" ) . " GMT" );
		header( "Last-Modified: " . gmdate( "D, d m Y H:i:s" ) . " GMT" );
		header( "Cache-Control: no-store, no-cache, must-revalidate" );
		header( "Cache-Control: post-check=0, pre-check=0", false );
		header( "Pragma: no-cache" );
	}

	public function generate_xml_header($block = FALSE)
	{
		print( "<?xml version=\"1.0\" encoding=\"" . $this->channel_code_setting . "\" ?>\n" );
		print( "<rss xmlns:itunes=\"http://www.itunes.com/dtds/podcast-1.0.dtd\" version=\"2.0\">\n" );
		print( "<channel>\n" );
    print($block?"<itunes:block>yes</itunes:block>\n":"");
		print( "<title>" . $this->channel_name . "</title>\n" );
		print( "<description>" . $this->channel_description . "</description>\n" );
		print( "<link>" . $this->channel_link . "</link>\n" );
		print( "<pubDate>" . strftime("%a, %d %b %Y %H:%M:%S GMT", $this->channel_date) . "</pubDate>\n" );
		print( "<itunes:author>". $this->channel_author ."</itunes:author>\n" );
	}

	public function generate_item( $attributes, $use_enclosure = FALSE, $enclosure_url = FALSE, $enclosure_length = FALSE, $enclosure_type = "audio/mp3" )
	{
		print( "\t<item>\n" );
		while( list( $tag, $value ) = each( $attributes ) )
		{
			print( "\t\t<$tag>$value</$tag>\n" );
		}
    if ($use_enclosure) {
      print( "\t\t<enclosure url=\"$enclosure_url\" length=\"$enclosure_length\" type=\"$enclosure_type\" />\n" );
    }
		print( "\t</item>\n" );
	}

	public function generate_xml_footer()
	{
		print( "</channel>\n</rss>" );
	}

}


?>
