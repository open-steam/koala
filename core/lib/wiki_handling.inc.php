<?php

/**
 * A small helper class to store call parameters for callback classes
 *
 */
class StaticCallbackParameterBuffer 
{
	 private static $params = array();

	 public static function get_parameter($param) 
	 {
	   return self::$params[$param];
	 }
	 
	 public static function add_parameter($param, $value) 
	 {
	   self::$params[$param] = $value;	
	 }
	 
}

function link_render_callback($matches)
{
  return '<a href="'.basename($matches[2]).'">'.$matches[4].'</a>';
}

function image_render_callback($matches) 
{
	if ( stristr( $matches[1], "http://" ) || stristr( $matches[1], "https://" ) ) return $matches[0];
  
	$ret = "";
	$hits = array();
	$t = preg_match( "/.*thumb:([0-9]+)+x([0-9]+)+.*/" , $matches[1], $hits);
	
	if ($t)
	{
		$width = $hits[1];
		$height = $hits[2];
	} else {
		$width = -1;
		$height = -1;
	}
  
	preg_match( '/src="(.*?)"/', $matches[1], $title );
	$wiki_path = StaticCallbackParameterBuffer::get_parameter( "wiki_path" );
	$path = dirname($wiki_path) . '/' . $title[1];
	$obj = steam_factory::path_to_object($GLOBALS[ "STEAM" ]->get_id(), $path);
	
	if ( is_object( $obj ) )
	{
		$content = $obj->get_content();
		$image = imagecreatefromstring( $content );
		$width = imagesx( $image );
		$height = imagesy( $image );

		if ( $width > 770 )
		{
			$height = (int) ( $height * 770 / $width );
			$width = 770;
		}
		
		$ret = '<img src="' . PATH_URL . 'get_document.php?id=' . $obj->get_id() . '&width=' . $width . '&height=' . $height . '" ';
		$ret .= 'alt="' . $obj->get_attribute('OBJ_DESC') . '" ';
		$ret .= 'title="' . $obj->get_attribute('OBJ_DESC') . '" ';
		$ret .= '/>';
	}
	else
	{
		$ret = '<div class="Missing_Image">' . str_replace( '%IMAGE%', $title[1], gettext( 'Image <br> "%IMAGE%" <br> no longer available' ) ) . '</div>';		
	}
	
	return $ret;
}

function wiki_to_html_plain( $wiki_doc, $version_doc = 0 )
{
	$orig_doc = $wiki_doc;
	if(isset($version_doc) && is_object($version_doc) && $version_doc instanceof steam_document)
		$wiki_doc = $version_doc;
	
	if ( $wiki_doc->get_attribute( DOC_MIME_TYPE ) == "text/wiki"  )
	{
		$wiki_module = $GLOBALS[ "STEAM" ]->get_module( "wiki" );
		if ( ! is_object( $wiki_module ) )
		{
			throw new Exception( "Wiki-Module is not installed." );
		}
		// Let Steam render the wiki document for us
		$tmp = $GLOBALS[ "STEAM" ]->predefined_command(
				$wiki_module,
				"wiki_to_html_plain",
				array( $wiki_doc ),
				0
		);
		// The Steam wiki rendering is related to the steam web interface. So we need to
		// tweek the links to other wiki pages.
		$tmp = preg_replace_callback( 
		  '/<a.*href="(.*)\?path=(.*)&(.*)">(.*)<\/a>/U', 
		  'link_render_callback',
		  $tmp 
		);
		// The Steam wiki rendering is related to the steam web interface. So we need to
    // tweek the links to image resources.
    
	$wiki_path = $orig_doc->get_path();
    StaticCallbackParameterBuffer::add_parameter("wiki_path", $wiki_path);
    $tmp = preg_replace_callback( 
      '/(<img .*?>)/', 
      'image_render_callback',
      $tmp 
    );
		return $tmp;
	}
	else
	{
		return $wiki_doc->get_content();
	}
}

function wiki_diff_html( $old_version, $new_version )
{
	//we use the diff module of the steam backend
	$module = $GLOBALS[ "STEAM" ]->get_module("diff");
	if ( ! is_object( $module ) )
	{
		throw new Exception( "Diff-Module is not present in your sTeam server." );
	}
	
	$xml = $GLOBALS[ "STEAM" ]->predefined_command(
			$module,
			"diff_xml",
			array($old_version, $new_version, 0),
			0
	);
	
	//using SimpleXML to parse the XML string
	$xml = simplexml_load_string($xml);

	$html = "<div id=\"diff\" style=\"padding-bottom: 1em;\">";
	$html .= "<p>";
  $pos = 1;
  $skip = 0;
  $deleted = 0;
  $added = 0;
	foreach( $xml->content->line as $contentline )
	{
    $deleted = 0;
    $added = 0;
    if ($skip > 0) {
      $skip --;
      $pos++;
    } else {
      $linechange = FALSE;
      foreach($xml->change as $change) {
        if ($change->attributes()->line1 == $pos) $linechange = $change;
      }
      
      if( $linechange )
      {
        $html .= "<div style=\"font-family: courier;\">";
        if( !empty($linechange->deleted) )
        {
          $html .= "<div style=\"background-color: #FFDEDE\">";
          foreach($linechange->deleted->line as $delline)
          {
            $html .= "<big>-&#160;&#160;&#160;" . $delline . "</big><br />";
            $deleted ++;
          }
          $html .= "</div>";
        }
        if( !empty($linechange->added) )
        {
          $html .= "<div style=\"background-color: #DEFFDE\">";
          foreach($linechange->added->line as $addline)
          {
            $html .= "<big>+&#160;&#160;&#160;" . $addline . "</big><br />";
            $added++;
          }
          $html .= "</div>";
        }
        $html .= "</div>";
      }
      else
      {
        $html .= "<div style=\"font-family: courier;\">";
        $html .= "<big>&#160;&#160;&#160;&#160;" . $contentline . "</big><br />";
        $html .= "</div>";
      }
      if ($added > 1) $skip = $added-1;
      $pos++;
    }
	}
	$html .= "</p>";
	$html .= "</div>";
	
	return $html;
}	

?>
