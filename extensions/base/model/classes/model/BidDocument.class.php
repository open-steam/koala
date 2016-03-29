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
  function _get_textplain($config_webserver_ip, $content){
    $content = preg_replace('/\r/', '', $content);
    $content = preg_replace('/\{\{([^}}|\|]*)\|(.*)\}\}/', '<span class="note" title="\2" style="cursor:pointer;">\1</span>', $content);
    $content = preg_replace('/\{\{(.*)\}\}/', '<span class="marked">\1</span>', $content);

    //link extern
    function replace_callback_url($match){
    if(isset($match[4])){
        return sprintf('<a href="%s">%s</a>', $match[1], $match[4]);
    } else {
        return sprintf('<a href="%s">%s</a>', $match[1], $match[1]);
    }
    }

    $pattern = "~\[(http(s)?://([^\]\s]*))(\s(.*?))?\]~i";
    $content = preg_replace_callback($pattern, "replace_callback_url", $content);

    $content = preg_replace('/\'\'\'(.*)\'\'\'/U', '<strong>\1</strong>', $content);
    $content = preg_replace('/\'\'(.*)\'\'/U', '<em>\1</em>', $content);
    $content = preg_replace('/=====(.*)=====/', '<h4>\1</h4>', $content);
    $content = preg_replace('/====(.*)====/', '<h3>\1</h3>', $content);
    $content = preg_replace('/===(.*)===/', '<h2>\1</h2>', $content);
    $content = preg_replace('/==(.*)==/', '<h1>\1</h1>', $content);
    $content = preg_replace('/^----$/m', '<hr>', $content);

    //lists

    $content = str_replace("\r\n", "\n", $content);
    $lines = explode("\n", $content);
    $contentNew = "";

    $inNumberedList = false;    //ol
    $inUnnumberedList = false;  //ul

    foreach ($lines as $line) {
        //begin a list
        if(!$inNumberedList && substr($line, 0,1)=="#"){
            $cleanedLine = substr($line,1);
            $contentNew.="<ol><li>".$cleanedLine."</li>\n";
            $inNumberedList = true;
        } else

        if(!$inUnnumberedList && substr($line, 0,1)=="*"){
            $cleanedLine = substr($line,1);
            $contentNew.="<ul><li>".$cleanedLine."</li>\n";
            $inUnnumberedList=true;
        } else


        //inside a list
        if($inNumberedList && substr($line, 0,1)=="#"){
            $cleanedLine = substr($line,1);
            $contentNew.="<li>".$cleanedLine."</li>\n";
        } else

        if($inUnnumberedList && substr($line, 0,1)=="*"){
            $cleanedLine = substr($line,1);
            $contentNew.="<li>".$cleanedLine."</li>\n";
        } else


        //end a list
        if($inUnnumberedList && substr($line, 0,1)!="*"){
            $cleanedLine = substr($line,1);
            $inUnnumberedList = false;
            $contentNew.="</ul>".$cleanedLine."\n";
        } else

        if($inNumberedList && substr($line, 0,1)!="#"){
            $cleanedLine = substr($line,1);
            $inNumberedList = false;
            $contentNew.="</ol>".$cleanedLine."\n";
        } else

        //not in a list
        $contentNew.=$line."\n";
    }

    $content = $contentNew;

    //table
    $content = preg_replace('/^\{\|(.*)$/m', '<table \1><tr>', $content);
    $content = preg_replace('/^\|\}$/m', '</tr></table>', $content);
    $content = preg_replace('/^\|-$/m', '</tr><tr>', $content);
    $content = preg_replace('/^\|(.*)$/m', '<td>\1</td>', $content);
    $content = preg_replace('/^\!(.*)$/m', '<th>\1</th>', $content);

    $path = substr($this->object->get_path(), 0, strrpos($this->object->get_path(), "/")) . "/";

    //ext url - works
    $content = preg_replace('/\[\[Bild:http:([^|]*)\|(.*)\]\]/U',
            '<img src="http:\1" alt="\2" title="\2">', $content);

    //obj id - works
    $content = preg_replace('/\[\[Bild:([0-9]*)\|(.*)\]\]/U',
            '<img src="/Download/Document/\1" alt="\2" title="\2">', $content);

    //audio
    //extern
    $content = preg_replace('/\[\[Audio:http:([^\]\]]*)\]\]/U',
            '<audio src="http:\1"></audio>', $content);

    //object id
    $content = preg_replace('/\[\[Audio:([0-9]*)\]\]/U',
            '<audio src="/Download/Document/\1"></audio>', $content);

    //video
    //extern
    $content = preg_replace('/\[\[Video:http:([^\]\]]*)\]\]/U',
            '<video src="http:\1"></video>', $content);

    $content = preg_replace('/\[\[Video:https:([^\]\]]*)\]\]/U',
            '<video src="https:\1"></video>', $content);

    //object id
    $content = preg_replace('/\[\[Video:([0-9]*)\]\]/U',
            '<video src="/Download/Document/\1"></video>', $content);

    //link, intern
    $content = preg_replace('/\[\[([^\]\]|\|]*)\|([^\]\]]*)\]\]/', '<a href="' . $config_webserver_ip . $path . '\1" target="_top">\2</a>', $content);
    $content = preg_replace('/\[\[([^\]\]]*)\]\]/', '<a href="' . $config_webserver_ip . $path . '\1" target="_top">\1</a>', $content);

    //p
    $content = preg_replace('/^$/m', '</p><p>', $content);

    return $content;

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
    $transaction_key = 0;
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

   $path_tok_2 = preg_replace(array("/^\\/+/", "/\\/+/"), array("", "/"), $path_tok_2);

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
