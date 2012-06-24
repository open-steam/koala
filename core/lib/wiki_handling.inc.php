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
  return '<a'.$matches[1].'href="'.basename($matches[3]).'">'.$matches[5].'</a>';
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
  
	preg_match( '/src=\'(.*?)\'/', $matches[1], $title );
	$path = $title[1];
	$path = urldecode($path);
	$obj = steam_factory::path_to_object($GLOBALS[ "STEAM" ]->get_id(), $path);
	
	if ( is_object( $obj ) && $obj instanceof steam_document) {
		$content = $obj->get_content();
		$image = imagecreatefromstring( $content );
		$width = $newWidth = imagesx( $image );
		$height = $newHeight = imagesy( $image );

		if ( $width > 767 )
		{
			$newHeight = (int) ( $height * 767 / $width );
			$newWidth = 767;
		}
		
		$ret = '<a href="javascript:showBox(' . $obj->get_id() . ',' . $width . ',' . $height . ');">';
		$ret .= '<img src="' . PATH_URL . 'download/image/' . $obj->get_id() . '/' . $newWidth . '/' . $newHeight . '" ';
		$ret .= 'id="' . $obj->get_id() . '" ';
		$ret .= 'name="' . $obj->get_attribute('OBJ_NAME') . '" ';
		$ret .= 'alt="' . $obj->get_attribute('OBJ_DESC') . '" ';
		$ret .= 'title="' . $obj->get_attribute('OBJ_DESC') . '" ';
		$ret .= '/></a>';
	}
	else
	{
		$ret = '<div class="Missing_Image">' . str_replace( '%IMAGE%', $title[1], gettext( 'Image <br> "%IMAGE%" <br> no longer available' ) ) . '</div>';		
	}
	
	return $ret;
}

function annotation_render_callback($matches)
{
	$annotation = trim($matches[1]);
	$text = trim($matches[2]);
	if ($annotation == "") {
		return "<span class=\"marked\">$text</span>";
	} else {
		return "<span class=\"annotated\">$text</span><div class=\"annotation\"><img src=\"". PATH_URL . "/styles/standard/images/wiki/comment_small.gif" ."\" title=\"$annotation\"></div>";
	}
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
				
		//Don't want preformated text.
		$tmp = preg_replace("/<pre>(.*?)<\/pre>/iU", "<p>$1</p>", $tmp);
		
		// The Steam wiki rendering is related to the steam web interface. So we need to
		// tweek the links to other wiki pages.
		$tmp = preg_replace_callback( 
		  '/<a(.*)href="(.*)\?path=(.*)&(.*)">(.*)<\/a>/U', 
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
	    // reformat annotations
	    $tmp = preg_replace_callback( 
	      "/<div class='annotate'><div class='annotation'>(.*)<\/div><div class='annotated'>(.*)<\/div><\/div>/U", /*<div class="annotation">(.*)<\/div><div class="annotated">(.*)<\/div><\/div>/U', */
	      "annotation_render_callback",
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

function wikitext_to_html( $text, $wiki_id )
{	
	/* &#039; is html-unicode for ' */
	
	// bold
	$text = preg_replace("/(&#039;){3}(.*?)(&#039;){3}/iU", "<strong>$2</strong>", $text);

	// italic
	$text = preg_replace("/(&#039;){2}(.*?)(&#039;){2}/iU", "<em>$2</em>", $text);
	
	// h3
	$text = preg_replace("/===(.*?)===/iU", "<h3>$1</h3>", $text);
	
	// h2
	$text = preg_replace("/==(.*?)==/iU", "<h2>$1</h2>", $text);

	// hr
	$text = preg_replace("/-----------/iU", "<hr />", $text);
	
	// external image
	$text = preg_replace("/\[\[Image:(.*?)\]\]/iU", "<img src=\"$1\" width=\"100\" />", $text);
	
	// internal wiki-link
	$wiki_url = PATH_URL . "wiki/" . $wiki_id . "/";
	$text = preg_replace("/\[\[(.*?)\]\]/iU", "<a href=\"" . $wiki_url . "$1.wiki\">$1</a>", $text);
	
	// external link
	$text = preg_replace("/\[(.*?)\]/iU", "<a href=\"$1\">$1</a>", $text);
	
	// UL and OL
	$inOL = false;
	$inUL = false;
	$inLI = false;
	$html = "";
	
	for ( $n = 0 ; $n < strlen( $text ) ; $n++ )
	{
		if ( $text[$n] == "#" && !$inOL )
		{
			$html .= "<ol>";
			$inOL = true;
			$inLI = true;
		}
		elseif ( $text[$n] == "*" && !$inUL )
		{
			$html .= "<ul>";
			$inUL = true;
			$inLI = true;
		} 
		
		// current character is '\n' or '\r'
		$isLineReturn = ord( $text[$n] ) == 10 || ord( $text[$n] ) == 13;
		
		if ( $inOL )
		{
			if ( $isLineReturn )
			{
				$html .= "</li>";
				$inLI = false;
			}
			elseif ( $text[$n] == "#" )
			{
				$html .= "<li>";
				$inLI = true;
			}
			elseif ( !$inLI )
			{
				$html .= "</ol>";
				$html .= $text[$n];
				$inOL = false;
			}
			else $html .= $text[$n];
		}
		elseif ( $inUL )
		{
			if ( $isLineReturn )
			{
				$html .= "</li>";
				$inLI = false;
			}
			elseif ( $text[$n] == "*" )
			{
				$html .= "<li>";
				$inLI = true;
			}
			elseif ( !$inLI )
			{
				$html .= "</ul>";
				$html .= $text[$n];
				$inUL = false;
			}
			else $html .= $text[$n];
		}
		else $html .= $text[$n];
	}
	
	$text = $html;
	
	// absätze beibehalten
	$text = preg_replace("/(\n|\r){2,}/", "<br /><br />", $text);
	//$text = preg_replace("/\n/", "<br />", $text);
	
	return $text;
}
?>