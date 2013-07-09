<?php
namespace Download\Commands;
abstract class AbstractDownloadCommand extends \AbstractCommand implements \IResourcesCommand {
	
	protected $params;
	protected $id;
	protected $filename;
	private $login;
	private $password;
	private $data;
	
	public function isGuestAllowed(\IRequestObject $iRequestObject) {
		return true;
	}
	
	public function validateData(\IRequestObject $requestObject) {
		if (isset($_SESSION[ "LMS_USER" ]) && $_SESSION[ "LMS_USER" ] instanceof \lms_user && $_SESSION[ "LMS_USER" ]->is_logged_in()) {
	    	$this->login = $_SESSION[ "LMS_USER" ]->get_login();
	    	$this->password = $_SESSION[ "LMS_USER" ]->get_password();
		} else {
	    	$this->login = 'guest';
	    	$this->password = 'guest';
		}
		$this->params = $requestObject->getParams();
		if (isset($this->params[0])){
			$this->id = $this->params[0];
			if (isset($this->params[1])){
				$this->filename = $this->params[1];
			}
			return true;
		}
		return false;
	}
	
	public function processData(\IRequestObject $requestObject) {
		$this->data = $this->get_document_data($this->login, $this->password, $this->id, "id", isset($this->width) ? $this->width : null, isset($this->height) ? $this->height : null);
	}
	
	public function resourcesResponse() {
		if (is_array($this->data["content"]))
	    	ExtensionMaster::getInstance()->send404Error();
	    else {
	    	header( "Pragma: private" );
	    	header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
	    	header( "Content-Type: " . $this->data[ "mimetype" ] );
	    	header( "Content-Length:" .  $this->data["contentsize"] );
	    	// The "attachment" statement in the line below forces the Browser's "Save as..." Dialog to pop up
	    	// Crappy: deleting the "attachment" statement leads into problems using "save as..." in browsers as they ignore the "filename" if "attachment" is missing. (Firefox does so...)
			// header( "Content-Disposition: attachment; filename=\"" . $data["name"] . "\"");
	    	if (isset($this->filename)) {
				//download was started using "/download/<id>/<filename>/" use <filename> as name
	        	header( "Content-Disposition: attachment; filename=\"" . $this->filename . "\"");
	      } else {
	        	header( "Content-Disposition: attachment; filename=\"" . $this->data["name"] . "\"");
	      }
	      //stop notice, if database download is enabled 
	      @ob_flush();
	      print $this->data["content"];
		}
	}
        
        
 private function get_document_data($login, $password, $identifier, $identifier_type, $width = false, $height = false) {
     $STEAM = \steam_connector::connect(STEAM_SERVER, STEAM_PORT, $login, $password );
     if ( $identifier_type === "name" ) {
     	$document = $STEAM->predefined_command( $STEAM->get_module("icons"), "get_icon_by_name", array( (string)$identifier ), 0 );
     }
     
     else if ( $identifier_type === "id" ) {
	    $document = \steam_factory::get_object( $STEAM->get_id(), (int)$identifier );
	    if (!isset($this->filename)) {
                header("location: " . PATH_URL . "Download/Document/{$identifier}/{$document->get_name()}");
                exit;
            }
    }
    
    // If user is not logged in, open login dialog. If user is logged in
    // and not guest, then display "Access denied" message.
    if (!$document->check_access_read( $STEAM->get_current_steam_user())) {
       if ($login == 'guest') throw new \Exception( "Access denied. Please login.", E_USER_AUTHORIZATION );
       else {
         throw new \Exception( "Access denied.", E_USER_RIGHTS );
        }
    }

    if ( ! is_object( $document ) ){
        return array( "content" => array() );  // array is considered to be an error
    
    }
    
    //somebody tries to download a container or link, this is not possible
    if (($document instanceof \steam_container)  || ($document instanceof \steam_link)){
        \ExtensionMaster::getInstance()->send404Error();
    }
    
    $document->get_attributes( array("OBJ_NAME","DOC_MIME_TYPE","DOC_LAST_MODIFIED"), TRUE );
   
    
    if (!$width && !$height) {
      //$tnr_content = $document->get_content(TRUE);  // workaround: get data from sTeam webinterface
      //$tnr_contentsize = $document->get_content_size(TRUE); //Not used
    }
    else {
      //TODO: loading times in gallery to long
      //test: removed first scaling parameter
      //$tnr_imagecontent = $document->get_thumbnail_data(-1, $height, 0, TRUE);
      
      $cache =  $this->getThumbnailDataFix($document,$width, $height, 0, TRUE);
      if ($cache !== 0) return $cache;
      
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
        $data["contentsize"] = $document->get_content_size();
        $data["content"]     = $document->get_content();
    }
    return $data;
  }
  
  /*
   * return ist ein array mit diversen feldern
   * felder
   * mimetype
   * lastmodfied
   * name
   * content
   * contentsize
   */
  private function getThumbnailDataFix($steamObject, $width, $height, $ratio=0, $bo=TRUE){
      $log=true;
      if($log) \logging::write_log( LOG_ERROR, "DL: begin"); //test
      
      $data = array();
      
      /*
      $data["mimetype"]    = $result[$tnr_imagecontent]["mimetype"];
      $data["lastmodified"]= $result[$tnr_imagecontent]["timestamp"];
      $data["name"]        = $result[$tnr_imagecontent]["name"];
      $data["content"]     = $result[$tnr_imagecontent]["content"];
      $data["contentsize"] = $result[$tnr_imagecontent]["contentsize"];
      */
      
      if($log) \logging::write_log( LOG_ERROR, "DL: steam-obj:"); //test
      if($log) \logging::write_log( LOG_ERROR, '#'.var_export($steamObject,true)); //test
      
      $thumnailsData = $steamObject->get_attribute("DOC_THUMBNAILS");
      
      if($log) \logging::write_log( LOG_ERROR, "DL: thumb-attr:"); //test
      if($log) \logging::write_log( LOG_ERROR, '#'.var_export($thumnailsData,true)); //test
      
      //test presense of thumbnail
      $thumbnailIsPresent = false;
      if(0 !== $thumnailsData){
          if($log) \logging::write_log( LOG_ERROR, "DL: thumb present"); //test
          $thumbnailIsPresent = true;
      }
      
      if($thumbnailIsPresent){
          foreach ($thumnailsData as $resolutionString => $imageSet){
              if($log) \logging::write_log( LOG_ERROR, "DL: first selected"); //test
              $timestamp = $imageSet["timestamp"];
              $imageCacheObject = $imageSet["image"];
              $xResoluton = $imageSet["x"];
              $yResoluton = $imageSet["y"];
              
              //if($log) \logging::write_log( LOG_ERROR, "DL: imageCacheObject:"); //test
              //if($log) \logging::write_log( LOG_ERROR, '#'.var_export($imageCacheObject,true)); //test
              
              if($log) \logging::write_log( LOG_ERROR, '#a'.var_export(intval($xResoluton,true))); //test
              if($log) \logging::write_log( LOG_ERROR, '#b'.var_export(intval($yResoluton,true))); //test
              if($log) \logging::write_log( LOG_ERROR, '#c'.var_export(intval($width,true))); //test
              if($log) \logging::write_log( LOG_ERROR, '#d'.var_export(intval($height,true))); //test
              
              if (intval($xResoluton) == intval($width)) break;
              if (intval($yResoluton) == intval($height)) break;
          }
          
          $data["mimetype"]    = $imageCacheObject->get_attribute( "DOC_MIME_TYPE" );
          $data["lastmodified"]= $imageCacheObject->get_attribute( "DOC_LAST_MODIFIED" );
          $data["name"]        = $imageCacheObject->get_name();
          $data["content"]     = $imageCacheObject->get_content();
          $data["contentsize"] = $imageCacheObject->get_content_size();
          
          if($log) \logging::write_log( LOG_ERROR, "DL: returned thumb"); //test
          if($log) \logging::write_log( LOG_ERROR, "DL: data:"); //test
          if($log) \logging::write_log( LOG_ERROR, '#'.var_export($data,true)); //test
          if($log) \logging::write_log( LOG_ERROR, "DL: cache hit!"); //test
          return $data;
      }
      
      
      
      //create thumbnail TODO
      if(!$thumbnailIsPresent){
          $newwidth = $width;
          $newheight = $height;
          
          
          ini_set('memory_limit', '1024M');
          set_time_limit(60);
          ini_set('gd.jpeg_ignore_warning', 1);
          
          $imageFullSize = imagecreatefromstring($steamObject->get_content());
          
          $isa =$imageFullSize->getImageSize();
          $origwidth = $isa[0];
          $origheight = $isa[1];
          
          
          
          $imageMinimized=ImageCreateTrueColor($newwidth,$newheight);
          ImageCopyResampled($imageMinimized,$imageFullSize,0,0,0,0,$newwidth,$newheight,$origwidth,$origheight);
          
          // start buffering
          ob_start();
          imagejpeg($imageMinimized, NULL, 85);// output jpeg (or any other chosen) format & quality
          $imageMinimizedStream = ob_get_contents();// capture output to string
          ob_end_clean();// end capture
          imagedestroy($imageMinimized);// be tidy; free up memory
          
          
          
          return 0;
          
          
          
      }
      
      
      
      
      
      //fallback
      return 0;
      /*
      if($log) \logging::write_log( LOG_ERROR, "DL: cache miss - fallback"); //test
      $tnr_imagecontent = $steamObject->get_thumbnail_data($width, $height, $ratio, $bo);
      
      if($log) \logging::write_log( LOG_ERROR, "DL: tnr_imagecontent"); //test
      if($log) \logging::write_log( LOG_ERROR, '#'.var_export($tnr_imagecontent,true)); //test
      if($log) \logging::write_log( LOG_ERROR, "DL: end"); //test
      return $tnr_imagecontent;
      */
  }
}
?>