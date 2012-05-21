<?php

  /****************************************************************************
  doc_content.php - class to derive the correct content/tag for a document
  Copyright (C)

////////////////////////////////////////////////////////////////////////
  CONSTRUCTOR:
    function doc_content(&$steam, $object)

  PUBLIC FUNCTIONS:
    function get_content($config_webserver_ip = "")

  PRIVATE FUNCTIONS:
    function _get_text($config_webserver_ip, $content)
    function _get_textplain($config_webserver_ip, $content)
    function _get_texthtml($config_webserver_ip, $content)
    function _get_image($config_webserver_ip)
    function _get_other($config_webserver_ip)
    function _real_path($path)

////////////////////////////////////////////////////////////////

  This program is free software; you can redistribute it and/or modify it
  under the terms of the GNU General Public License as published by the
  Free Software Foundation; either version 2 of the License,
  or (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
  See the GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software Foundation,
  Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA

  Author: Henrik Beige <hebeige@gmx.de>
          Bastian Schröder <bastian@upb.de>

  ****************************************************************************/

/**
* class to derive the correct content/tag for a document
*
* @author	    Henrik Beige <hebeige@gmx.de>
* @copyright	Henrik Beige <hebeige@gmx.de> - distributed under the GPL
*/
class BidDocument
{
  /**
  * reference of valid socket to steam server
  *
  * @access   private
  * @var      Ressourcepointer
  */
  var $steam;

  /**
  * object from which content shall be derived
  *
  * @access   private
  * @var      Object (steam_object)
  */
  var $object;


  /**
  * Constructor
  * Get basic info from steam server
  * @access   public
  * @param    Ressource pointer &$steam
  * @param    steam_object      $object
  **/
  function BidDocument($object) {
    //store info in class
    $this->steam = $GLOBALS["STEAM"];
    $this->object = $object;
  }


  /**
  * Get content of object (forks to different output methods according to the mimetype
  * @access   public
  * @param    String  $config_webserver
  **/
  function get_content($config_webserver_ip = "") {
  	$mimetype = $this->object->get_attribute(DOC_MIME_TYPE);
  	
    //Text content
    if( strpos($mimetype, "text/") !== false )
    {
      // $content = stripslashes( $this->object->get_content() );
      $content = $this->object->get_content();

      if( $mimetype == "text/plain" || $mimetype == "text/css" )
        $content = $this->_get_textplain($config_webserver_ip, $content);
      else if( $mimetype == "text/htm" || $mimetype == "text/html" )
        $content = $this->_get_texthtmlnew($config_webserver_ip, $content);
      else if( $mimetype == "text/xml" )
        $content = $this->_get_textxml($config_webserver_ip, $content);
      #else
        #$content = $this->_get_text($config_webserver_ip, $content);
    }

    //KML content
    else if( $mimetype == "application/vnd.google-earth.kml+xml" ) {
      $content = $this->_get_kml($config_webserver_ip, $this->object->get_id());
    }

    //Image content
    else if( strpos($mimetype, "image/") !== false )
      $content = $this->_get_image($config_webserver_ip);

    //mp3 content
    else if( strpos($mimetype, "audio/mpeg") !== false )
      $content = $this->_get_mp3($config_webserver_ip);

    //Other content
    else
      $content = $this->_get_other($config_webserver_ip);

    return $content;
  }

  /**
  * Get XML content
  * @access   private
  **/
  function _get_textxml($config_webserver_ip, $content)
  {
  	$login_data = $this->steam->get_login_data();
  	$login_data = $login_data->get_arguments();
  	$modulXslt = $login_data[8]["libxslt"];
    $module = steam_factory::get_object($this->steam->get_id(), $modulXslt->get_id(), $modulXslt->get_type());
    $result = steam_connection::get_instance($this->steam->get_id())->predefined_command($module, "run", array($content, $this->object->get_attribute("xsl:public"), array(1=>"1")), "objarg", 0);
    return $result;
  }

  /**
  * Get KML content
  * @access   private
  **/
  function _get_kml($config_webserver_ip, $id)
  {
    $config_webserver_ip = str_replace("https://", "http://", $config_webserver_ip);
    return '<iframe class="googlemap" src="http://maps.google.com/maps?q=' . $config_webserver_ip . '/tools/get.php?object=' . $id . '"></iframe>';
  }

  /**
  * Get text content with \n\r exchanged with <br>
  * @access   private
  **/
  function _get_textplain($config_webserver_ip, $content)
  {
    $content = preg_replace('/\r/', '', $content);
    $content = preg_replace('/\{\{([^}}|\|]*)\|(.*)\}\}/', '<span class="note" title="\2" style="cursor:pointer;">\1</span>', $content);
    $content = preg_replace('/\{\{(.*)\}\}/', '<span class="marked">\1</span>', $content);
    $content = preg_replace('/\[http(.*?)\ (.*)\]/', '<a href="http\1" target="_new">\2</a>', $content);
    $content = preg_replace('/\[http(.*)\]/', '<a href="http\1" target="_new">http\1</a>', $content);
    $content = preg_replace('/\'\'\'(.*)\'\'\'/U', '<strong>\1</strong>', $content);
    $content = preg_replace('/\'\'(.*)\'\'/U', '<em>\1</em>', $content);
    $content = preg_replace('/=====(.*)=====/', '<h4>\1</h4>', $content);
    $content = preg_replace('/====(.*)====/', '<h3>\1</h3>', $content);
    $content = preg_replace('/===(.*)===/', '<h2>\1</h2>', $content);
    $content = preg_replace('/==(.*)==/', '<h1>\1</h1>', $content);
    $content = preg_replace('/^----$/m', '<hr>', $content);
    $content = preg_replace('/^\*(.*)/m', '<div class="unnumbered">\1</div>', $content);
    $content = preg_replace('/^#(.*)/m', '<div class="numbered">\1</div>', $content);
    $content = preg_replace('/^\{\|(.*)$/m', '<table \1><tr>', $content);
    $content = preg_replace('/^\|\}$/m', '</tr></table>', $content);
    $content = preg_replace('/^\|-$/m', '</tr><tr>', $content);
    $content = preg_replace('/^\|(.*)$/m', '<td>\1</td>', $content);
    $content = preg_replace('/^\!(.*)$/m', '<th>\1</th>', $content);

    $path = substr($this->object->get_path(), 0, strrpos($this->object->get_path(), "/")) . "/";
    
    
    //$config_webserver_ip = "localhost";
    
    //bilder
    /*
    $content = preg_replace('/\[\[Bild:http:([^|]*)\|(.*)\]\]/U',
            '<div class="wikiimage"><img src="http:\1" alt="\2" title="\2"></div>', $content);
    
    $content = preg_replace('/\[\[Bild:\/([^|]*)\|(.*)\]\]/U',
            '<div class="wikiimage"><img src="' . $config_webserver_ip . '/tools/get.php?object=/\1" alt="\2" title="\2"></div>', $content);
    
    $content = preg_replace('/\[\[Bild:([0-9]*)\|(.*)\]\]/U',
            '<div class="wikiimage"><img src="' . $config_webserver_ip . '/tools/get.php?object=\1" alt="\2" title="\2"></div>', $content);
    
    $content = preg_replace('/\[\[Bild:([^\]\]]*)\|(.*)\]\]/U',
            '<div class="wikiimage"><img src="' . $config_webserver_ip . '/tools/get.php?object=' . $path . '\1" alt="\2" title="\2"></div>', $content);
    */
    
    
    //ext url
    $content = preg_replace('/\[\[Bild:http:([^|]*)\|(.*)\]\]/U',
            '<div class="wikiimage"><img src="http:\1" alt="\2" title="\2"></div>', $content);
    
    //abs pfad
    $content = preg_replace('/\[\[Bild:\/([^|]*)\|(.*)\]\]/U',
            '<div class="wikiimage"><img src="' . $config_webserver_ip . '/Download/Document/\1/" alt="\2" title="\2"></div>', $content);
    
    //obj id
    $content = preg_replace('/\[\[Bild:([0-9]*)\|(.*)\]\]/U',
            '<div class="wikiimage"><img src="' . $config_webserver_ip . '/Download/Document/\1/" alt="\2" title="\2"></div>', $content);
    
    //rel pfad
    $content = preg_replace('/\[\[Bild:([^\]\]]*)\|(.*)\]\]/U',
            '<div class="wikiimage"><img src="' . $config_webserver_ip . '/tools/get.php?object=' . $path . '\1" alt="\2" title="\2"></div>', $content);
    
    
    
    
    
    
    //audio
    $content = preg_replace('/\[\[Audio:http:([^\]\]]*)\]\]/U', '<div class="wikiaudio"><object type="application/x-shockwave-flash" data="' . $config_webserver_ip . '/tools/emff_lila_info.swf" width="200" height="55"><param name="movie" value="' . $config_webserver_ip . '/tools/emff_lila_info.swf" /><param name="FlashVars" value="src=http:\1" /></object></div>', $content);
    $content = preg_replace('/\[\[Audio:\/([^\]\]]*)\]\]/U', '<div class="wikiaudio"><object type="application/x-shockwave-flash" data="' . $config_webserver_ip . '/tools/emff_lila_info.swf" width="200" height="55"><param name="movie" value="' . $config_webserver_ip . '/tools/emff_lila_info.swf" /><param name="FlashVars" value="src=' . $config_webserver_ip . '/tools/get.php?object=/\1" /></object></div>', $content);
    $content = preg_replace('/\[\[Audio:([0-9]*)\]\]/U', '<div class="wikiaudio"><object type="application/x-shockwave-flash" data="' . $config_webserver_ip . '/tools/emff_lila_info.swf" width="200" height="55"><param name="movie" value="' . $config_webserver_ip . '/tools/emff_lila_info.swf" /><param name="FlashVars" value="src=' . $config_webserver_ip . '/tools/get.php?object=\1" /></object></div>', $content);
    $content = preg_replace('/\[\[Audio:([^\]\]]*)\]\]/U', '<div class="wikiaudio"><object type="application/x-shockwave-flash" data="' . $config_webserver_ip . '/tools/emff_lila_info.swf" width="200" height="55"><param name="movie" value="' . $config_webserver_ip . '/tools/emff_lila_info.swf" /><param name="FlashVars" value="src=' . $config_webserver_ip . '/tools/get.php?object=' . $path . '\1" /></object></div>', $content);
    
    
    //video
    $content = preg_replace('/\[\[Video:http:([^\]\]]*)\]\]/U', '<div class="wikivideo"><embed src="' . $config_webserver_ip . '/tools/mediaplayer.swf" width="300" height="220" allowscriptaccess="always" allowfullscreen="true" flashvars="width=300&height=220&file=http:\1&autostart=false" /></div>', $content);
    $content = preg_replace('/\[\[Video:\/([^\]\]]*)\]\]/U', '<div class="wikivideo"><embed src="' . $config_webserver_ip . '/tools/mediaplayer.swf" width="300" height="220" allowscriptaccess="always" allowfullscreen="true" flashvars="width=300&height=220&file=' . $config_webserver_ip . '/tools/get.php?object=/\1&autostart=false" /></div>', $content);
    $content = preg_replace('/\[\[Video:([0-9]*)\]\]/U', '<div class="wikivideo"><embed src="' . $config_webserver_ip . '/tools/mediaplayer.swf" width="300" height="220" allowscriptaccess="always" allowfullscreen="true" flashvars="width=300&height=220&file=' . $config_webserver_ip . '/download/\1/video.flv&autostart=false" /></div>', $content);
    $content = preg_replace('/\[\[Video:([^\]\]]*)\]\]/U', '<div class="wikivideo"><embed src="' . $config_webserver_ip . '/tools/mediaplayer.swf" width="300" height="220" allowscriptaccess="always" allowfullscreen="true" flashvars="width=300&height=220&file=' . $config_webserver_ip . '/tools/get.php?object=' . $path . '\1&autostart=false" /></div>', $content);
    
    
    //etc
    $content = preg_replace('/\[\[([^\]\]|\|]*)\|([^\]\]]*)\]\]/', '<a href="' . $config_webserver_ip . $path . '\1" target="_top">\2</a>', $content);
    $content = preg_replace('/\[\[([^\]\]]*)\]\]/', '<a href="' . $config_webserver_ip . $path . '\1" target="_top">\1</a>', $content);
    $content = preg_replace('/^$/m', '</p><p>', $content);
//    $content = preg_replace('/\n\n(.*)\n\n/m', '<p>*\1*</p>', $content);
    return $content;
#	$content = eregi_replace("\r", '', $content);
#	$content = eregi_replace("\n\n", "</p>\n<p>", "<p>" . $content . "</p>");
#	return preg_replace ('|\n|', '<br />\n', $content);
#	return eregi_replace('\n\n', '<p>', $content);
  }


  /**
  * Get html text content
  * @access   private
  **/
  function _get_texthtmlnew($config_webserver_ip, $content)
  {
    // get current path
    $current_path = substr( $this->object->get_path(), 0, strrpos($this->object->get_path(), "/")) . "/";

    // set base target to top frame
    $content = preg_replace('/<head>/i', '<head><base target="_top" /><base href="' . $config_webserver_ip . $current_path . '" />', $content);

    $content = preg_replace('/link href="([a-z0-9.-_\/]*)"/iU', 'link href="' . $config_webserver_ip . '/tools/get.php?object=' . $current_path . '$1"', $content);
    $content = preg_replace('/src="([a-z0-9.\-_\/]*)"/iU', 'src="' . $config_webserver_ip . '/tools/get.php?object=' . $current_path . '$1"', $content);
    $content = preg_replace('/code="([a-z0-9.\-_\/]*)"/iU', 'src="' . $config_webserver_ip . '/tools/get.php?object=' . $current_path . '$1"', $content);

    //$content = preg_replace('/link href="^[http]([a-z0-9.-_\/]*)"/iU', 'link href="' . $config_webserver_ip . '/tools/get.php?object=' . $current_path . '$1"', $content);
    //$content = preg_replace('/src="^[http]([a-z0-9.\-_\/]*)"/iU', 'src="' . $config_webserver_ip . '/tools/get.php?object=' . $current_path . '$1"', $content);
    //$content = preg_replace('/code="^[http]([a-z0-9.\-_\/]*)"/iU', 'src="' . $config_webserver_ip . '/tools/get.php?object=' . $current_path . '$1"', $content);

    // recode to UTF-8 if necessary
//    if (mb_detect_encoding($content, 'UTF-8, ISO-8859-1') !== 'UTF-8')
//      $content = utf8_encode($content);
    return $content;
  }


  /**
  * Get text content with all links in src="..." and href="..." set to the adequat link according to the bid system
  * @access   private
  **/
  function _get_texthtml($config_webserver_ip, $content)
  {
    //get current path
    $current_path = substr( $this->object->get_path(), 0, strrpos($this->object->get_path(), "/")) . "/";

    //initiate variables
    $html_org = eregi_replace("<head>", '<head><base target="_top">', $content);
    $html = $html_org;

    $url_org = array();
    $url_new = array();
    $url_map = array(
      "src" => $config_webserver_ip . "/tools/get.php?object=",
      "data" => $config_webserver_ip . "/tools/get.php?object=",
      "code" => $config_webserver_ip . "/tools/get.php?object=",
      "link href" => $config_webserver_ip . "/tools/get.php?object=",
      "href" => $config_webserver_ip . "/index.php?object=",
      "action" => $config_webserver_ip . "/index.php?object="
    );

    //work through the whole html sourcecode until no href="..." or src="..." are left
    $transaction_key = 0;  // hack
    while( $tag_open = strpos($html, '<') !== false )
    {
      $tag_close = strpos($html, '>', $tag_open);

      //get html entity
      $entity = substr($html, $tag_open, $tag_close - $tag_open + 1);

      //save entity that has a href or src in it
      if(eregi("(^link href|^href|^src|^action|^data|^code)*(link href|href|src|action|data|code)=[\"\']([^\"^\']*)", $entity, $regs))
      {
        $scheme = $regs[2];
        $url = $regs[3];
        $split = parse_url($url);

        //only derive path if its a relative link
        if(!isset($split["scheme"]) && isset($split["path"]))
        {
          
          $path = $this->_real_path($current_path . $split["path"]);
          $transaction[$transaction_key] = steam_factory::path_to_object($this->steam->get_id(), $path );

          //only save data if its a new one
          if(!isset($url_org[$entity]))
          {
            $url_org[$entity] = $entity;
            $url_id[] = array(
              "transaction" => $transaction[$transaction_key],
              "entity" => $entity,
              "scheme" => $scheme,
              "url" => $url
            );
          }
          
          $transaction_key++;
        }
      }

      //get html from the tag_close on to make sure the while stops sometime
      $html = substr($html, $tag_close);
    }
    
    if (!isset($url_id)) return $html_org;

    //build array for string replacment
    foreach($url_id as $key => $data)
    {
	  $object = $transaction[$key]; // hack
	  
      //if there is a valid object for that path build replace array field
      if(is_object($object))
      {
        $path = $url_map[strtolower($data["scheme"])] . $object->get_id();
        $url_new[] = str_replace($data["url"], $path, $data["entity"]);
      }

      //if there is no valid object for that path simply neglect the link
      else
      {
        unset($url_org[$data["entity"]]);
        unset($url_id[$key]);
      }
    }

    //replace all links in html sourcecode
    $new_html = str_replace($url_org, $url_new, $html_org);

    if (mb_detect_encoding($new_html, 'UTF-8, ISO-8859-1') !== 'UTF-8') $new_html = utf8_encode($new_html);
    return $new_html;
  }


  /**
  * Get proper image tag
  * @access   private
  **/
  function _get_image($config_webserver_ip)
  {
    return "<center><img src=\"$config_webserver_ip/tools/get.php?object=" . $this->object->get_id() . "\" border=\"0\" title=\"" . $this->object->get_attribute( "OBJ_DESC" ) . "\" alt=\"" . $this->object->get_name() . "\"></center>";
  }


  /**
  * Get proper mp3 tag
  * @access   private
  **/
  function _get_mp3($config_webserver_ip)
  {
    $result = "<object type=\"application/x-shockwave-flash\" data=\"$config_webserver_ip/tools/emff_standard.swf\" width=\"110\" height=\"34\">";
    $result = $result . "<param name=\"movie\" value=\"$config_webserver_ip/tools/emff_standard.swf\" />";
    $result = $result . "<param name=\"FlashVars\" value=\"src=$config_webserver_ip/tools/get.php?object=" . $this->object->get_id() . "\" />";
    $result = $result . "</object>";
    return $result;
  }


  /**
  * Get proper Download tag
  * @access   private
  **/
  function _get_other($config_webserver_ip)
  {
    return "<a href=\"$config_webserver_ip/tools/get.php?object=" . $this->object->get_id() . "\">" . $this->object->get_name() . "</a>";
  }


  /**
  * Straighten URL "test/bla/download/../index.html" => "test/bla/index.html"
  * @access   private
  **/

  function _real_path($path)
  {
   if ($path == "")
     return "";

   $path = trim(preg_replace("/\\\\/", "/", (string)$path));

   if (!preg_match("/(\.\w{1,4})$/", $path)  &&
       !preg_match("/\?[^\\/]+$/", $path)  &&
       !preg_match("/\\/$/", $path))
   {
       $path .= '/';
   }

   $pattern = "/^(\\/|\w:\\/|https?:\\/\\/[^\\/]+\\/)?(.*)$/i";

   preg_match_all($pattern, $path, $matches, PREG_SET_ORDER);

   $path_tok_1 = $matches[0][1];
   $path_tok_2 = $matches[0][2];

   $path_tok_2 = preg_replace(
                   array("/^\\/+/", "/\\/+/"),
                   array("", "/"),
                   $path_tok_2);

   $path_parts = explode("/", $path_tok_2);
   $real_path_parts = array();

   for ($i = 0, $real_path_parts = array(); $i < count($path_parts); $i++)
   {
     if ($path_parts[$i] == '.')
         continue;
     else if ($path_parts[$i] == '..')
       if((isset($real_path_parts[0])  &&  $real_path_parts[0] != '..') ||
           $path_tok_1 != "")
       {
         array_pop($real_path_parts);
         continue;
       }

     array_push($real_path_parts, $path_parts[$i]);
   }

   return $path_tok_1 . implode('/', $real_path_parts);
}}
?>