<?php
    $topdir = '..';
    $confFile = $topdir . '/etc/koala.conf.php';
    if ( ! file_exists( $topdir . '/etc/koala.conf.php' ) ) {
    	$topdir = '../..';
    	$confFile = $topdir . '/etc/koala.conf.php';
    }
    require_once( $confFile );

  if (!defined("USE_DOCUMENT_CACHE")) define("USE_DOCUMENT_CACHE", TRUE);
  if (!defined('USE_DATABASE_DOWNLOAD')) define( 'USE_DATABASE_DOWNLOAD', FALSE );

  require_once( PATH_LIB . "cache_handling.inc.php" );
  require_once( PATH_LIB . "download_handling.inc.php" );

  // getting login data from session or 
  if ( isset($_SESSION[ "LMS_USER" ]) && $_SESSION[ "LMS_USER" ] instanceof lms_user && $_SESSION[ "LMS_USER" ]->is_logged_in() ) {
    $login = $_SESSION[ "LMS_USER" ]->get_login();
    $password = $_SESSION[ "LMS_USER" ]->get_password();
  }
  else {
    $login = 'guest';
    $password = 'guest';
  }
  
$image_width     = (isset($_GET["width"])?(int)$_GET["width"]:false);
$image_height    = (isset($_GET["height"])?(int)$_GET["height"]:false);

  if ( isset( $_GET["id"] ) ) {
    $identifier = (int)$_GET["id"];
    $identifier_type = "id";
  }
  else if ( isset( $_GET["name"] ) ) {
    $identifier = (string)$_GET["name"];
    $identifier_type = "name";
  }
  else
    throw new Exception( "No 'id' or 'name' param provided." );
    
 function get_document_data($login, $password, $identifier, $identifier_type, $width = false, $height = false) {
     $STEAM = new steam_connector(STEAM_SERVER, STEAM_PORT, $login, $password );
     if ( $identifier_type === "name" ) {
     	$document = $STEAM->predefined_command( $STEAM->get_module("icons"), "get_icon_by_name", array( (string)$identifier ), 0 );
     }
     else if ( $identifier_type === "id" ) {
	     $document = steam_factory::get_object( $STEAM->get_id(), (int)$identifier );
     }
     // If user is not logged in, open login dialog. If user is logged in
     // and not guest, then display "Access denied" message.
     if (!$document->check_access_read( $STEAM->get_current_steam_user())) {
       if ($login == 'guest') throw new Exception( "Access denied. Please login.", E_USER_AUTHORIZATION );
       else {
         throw new Exception( "Access denied.", E_USER_RIGHTS );
       }
     }

	 if ( ! is_object( $document ) )
	    return array( "content" => array() );  // array is considered to be an error
     $document->get_attributes( array("OBJ_NAME","DOC_MIME_TYPE","DOC_LAST_MODIFIED"), TRUE );
   
    if (!$width && !$height) {
      //$tnr_content = $document->get_content(TRUE);  // workaround: get data from sTeam webinterface
      $tnr_contentsize = $document->get_content_size(TRUE);
    }
    else {
      $tnr_imagecontent = $document->get_thumbnail_data($width, $height, 0, TRUE);
    }
    $result = $STEAM->buffer_flush();
    if (isset($tnr_imagecontent)) { // handle thumbnail data
      $data["mimetype"]    = $result[$tnr_imagecontent]["mimetype"];
      $data["lastmodified"]= $result[$tnr_imagecontent]["timestamp"];
      $data["name"]        = $result[$tnr_imagecontent]["name"];
      $data["content"]     = $result[$tnr_imagecontent]["content"];
      $data["contentsize"] = $result[$tnr_imagecontent]["contentsize"];
    }
    else if ( $identifier_type === "id" ) {
      $data["mimetype"]    = $document->get_attribute( "DOC_MIME_TYPE" );
      $data["lastmodified"]= $document->get_attribute( "DOC_LAST_MODIFIED" );
      $data["name"]        = $document->get_name();
      $data["contentsize"] = $result[$tnr_contentsize];
      //$data["content"]     = $result[$tnr_content];  // workaround: get data from sTeam webinterface
      
      // workaround: get content from sTeam webinterface, because 
      //$data["content"] = $document->get_content();
      //   results in *huge* memory overheads (a 15 MB download fails with 60 MB scrip memory limit!
      if (defined("LOG_DEBUGLOG")) {
        $time1 = microtime(TRUE);
        logging::write_log( LOG_DEBUGLOG, "get_document::get_document_data(" . $login . ", *****" . ", " . $identifier . ", ". $identifier_type . ", false, false)\t " . $login . " \t". $identifier . " \t" . $document->get_name() . " \t" .  $data["contentsize"] . " Bytes \t... " );
      }
      $https_port = (int)$STEAM->get_config_value( "https_port" );
      if ( $https_port == 443 || $https_port == 0 ) $https_port = "";
      else $https_port = ":" . (string)$https_port;
      $ch = curl_init( "https://" . STEAM_SERVER . $https_port . "/scripts/get.pike?object=" . $identifier );
      curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
      curl_setopt( $ch, CURLOPT_BINARYTRANSFER, TRUE );
      curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
      curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
      curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 120 );
      curl_setopt( $ch, CURLOPT_USERPWD, $login . ":" . $password );
      $data["content"] = curl_exec( $ch );
      curl_close( $ch );
      
      if (defined("LOG_DEBUGLOG")) {
        logging::append_log( LOG_DEBUGLOG, " \t" . round((microtime(TRUE) - $time1) * 1000 ) . " ms");
      }
    }
    else {
      $data = array( "content" => array() );  // array is considered an error
    }
    return $data;
  }

if (USE_DATABASE_DOWNLOAD && !$image_height && !$image_width && $login != 'guest' &&  !( isset($_GET["type"]) && ($_GET["type"] == "usericon" || $_GET["type"] == "objecticon" )  )) {
  $valid_database_call = TRUE;
  require ("get_document_database.php");
} else { 
  if (USE_DOCUMENT_CACHE && $login != 'guest' &&  isset($_GET["type"]) && ($_GET["type"] == "usericon" || $_GET["type"] == "objecticon" )) {
    $image_width     = (isset($_GET["width"])?(int)$_GET["width"]:-1);
    $image_height    = (isset($_GET["height"])?(int)$_GET["height"]:-1);
    $DEBUG = FALSE;
    $cache = get_icon_cache( 3600 );
    $data = $cache->get("icon_" . $identifier_type . "_" . $identifier, $image_width . "x" . $image_height);
    
    // get the data or not in cache
    
    if (!isset($data) || !$data) {
      $data = get_document_data($login, $password, $identifier, $identifier_type, $image_width, $image_height);
      $cache->save($data, "icon_" . $identifier_type . "_" . $identifier, $image_width . "x" . $image_height);
    
    } else {
      if ($DEBUG) error_log("cache hit for icon " . $identifier_type . "=" . $identifier . " " . $image_width . "x" . $image_height);
    }
    $timestamp = $data["lastmodified"];

    if ($DEBUG) error_log("calling conditional get for icon " . $identifier_type . "=" . $identifier . " timestamp=". $timestamp);
    // A PHP implementation of conditional get, see
    //   http://fishbowl.pastiche.org/archives/001132.html
    $last_modified = substr(date('r', $timestamp), 0, -5).'GMT'; 
    
    // Send the headers
    header('Pragma: public');
    header('Cache-Control: public, must-revalidate');
    header("Last-Modified: $last_modified");
    //$etag = '"'.md5($last_modified).'"';
    //header("ETag: $etag");      //Removed ETag due to a bug in internet explorer
    
    // See if the client has provided the required headers
    $if_modified_since = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ?
        stripslashes($_SERVER['HTTP_IF_MODIFIED_SINCE']) :
        false;
        
//    $if_none_match = isset($_SERVER['HTTP_IF_NONE_MATCH']) ?
//        stripslashes($_SERVER['HTTP_IF_NONE_MATCH']) :
//        false;
        
    if ($if_modified_since && $if_modified_since == $last_modified) {
      if ($DEBUG) error_log("using 304 Not modified !");  
      // Nothing has changed since last request - serve a 304 and exit
      header('HTTP/1.0 304 Not Modified');
      exit;
    }
    header( "Content-Type: " . $data[ "mimetype" ] );
    header( "Content-Length:" .  $data["contentsize"] );
    print $data["content"];
  } 
  // output of normal document. No caching here
  else {
    $image_width     = (isset($_GET["width"])?(int)$_GET["width"]:false);
    $image_height    = (isset($_GET["height"])?(int)$_GET["height"]:false);
    if ($image_height || $image_width) { 
      $data = get_document_data( $login, $password, $identifier, $identifier_type, $image_width, $image_height );
    } else {
      $data = get_document_data( $login, $password, $identifier, $identifier_type);
    }
    if (is_array($data["content"]))
      header("HTTP/1.1 404 Not Found");
    else {
      header( "Pragma: private" );
      header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
      header( "Content-Type: " . $data[ "mimetype" ] );
      header( "Content-Length:" .  $data["contentsize"] );
      // The "attachment" statement in the line below forces the Browser's "Save as..." Dialog to pop up
      // Crappy: deleting the "attachment" statement leads into problems using "save as..." in browsers as they ignore the "filename" if "attachment" is missing. (Firefox does so...)
//      header( "Content-Disposition: attachment; filename=\"" . $data["name"] . "\"");
      if (isset($_GET["filename"])) {
//download was started using "/download/<id>/<filename>/" use <filename> as name
        header( "Content-Disposition: filename=\"" . $_GET["filename"] . "\"");
      } else {
        header( "Content-Disposition: attachment; filename=\"" . $data["name"] . "\"");
      }
      //stop notice, if database download is enabled 
      @ob_flush();
      print $data["content"];
    }
  }
}
?>
