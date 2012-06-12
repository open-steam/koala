<?php
class HtmlDocument
{
  var $rawContent;
  var $object;

  function HtmlDocument($steamObject=NULL){
    if($steamObject===NULL){
        $this->rawContent = "";
        $this->object = NULL;
    }else{
        $this->rawContent = $steamObject->get_content();
        $this->object = $steamObject;
    }
  }
  

  function getRawContent() {
    return $this->rawContent;
  }
 
  
  function getHtmlContent(){
      $html = $this->getRawContent();
      $html = $this->makeViewModifications($html);
      return $html;
  }
  
  /*
   * transform a html document object to the html output
   * 
   * paths will be replaced
   * 
   * this retuns cleand html, do not clean again
   * added javascript will be destroyd
   */
  function makeViewModifications($html, $steamObject=NULL) {
    //mod 0  
    if($this->object!==NULL){
        $dirname = dirname($this->object->get_path()) . "/";
    }  
    else{
        $dirname = "";
    }
    
    if($steamObject!==NULL){
        $dirname = dirname($steamObject->get_path()) . "/";
    }
     
    
        
    //mod1
    //document mod: replace not vaild hrefs
    preg_match_all('/href="([%a-z0-9.-_\/]*)"/iU', $html, $matches);
    $orig_matches = $matches[0];
    $path_matches = $matches[1];
    foreach ($path_matches as $key => $path) {
        $path = urldecode($path);
        if (parse_url($path, PHP_URL_SCHEME) != null) {
            continue;
        }
        $ref_object = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $dirname . $path);
        if ($ref_object instanceof \steam_object) {
            $new_path = PATH_URL . "explorer/index/" . $ref_object->get_id();
        } else {
            $new_path = PATH_URL . "404/";
        }
        $html = str_replace($orig_matches[$key], "href=\"" . $new_path . "\"", $html);
    }
    
    //clean html tags
    $html = cleanHTML($html);
    
    
    //works for /download/document paths
    preg_match_all('/<img.*src="(.*)".*>/iU', $html, $matches);
    
    $origMatches = $matches[0];
    $pathMatches = $matches[1];
    
    foreach ($pathMatches as $key => $path) {
        if(substr($path, 0, 19)=="/Download/Document/"){
            $objectId = intval(substr($path, 19));
        }else{
            continue;
        }
        
        try{
            $steamObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
            if(!($steamObject instanceof \steam_object)) throw new \steam_exception;
            if($steamObject===NULL) throw new \steam_exception;
        }  catch (\steam_exception $e){
            //echo "fehler: objekt nicht gefunden";
            $newPath = PATH_URL . "styles/standard/images/404.jpg";
            $html = str_replace($origMatches[$key], "<img src=\"" . $newPath . "\">", $html);
        }
    }
    
    
    
    
    //works for relative steam paths
    preg_match_all('/<img.*src="(.*)".*>/iU', $html, $matches);
    
    $origMatches = $matches[0];
    $pathMatches = $matches[1];
    
    foreach ($pathMatches as $key => $path) {
        if(!(substr($path, 0, 19)=="/Download/Document/") && (!(substr($path, 0, 4)=="http"))){
            //$path = $path;
        }else{
            continue;
        }
        try{
            $steamObject = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $dirname . $path);
            if(!($steamObject instanceof \steam_object)) throw new \steam_exception;
            if($steamObject===NULL) throw new \steam_exception;
        }  catch (\steam_exception $e){
            $newPath = PATH_URL . "styles/standard/images/404.jpg";
            $html = str_replace($origMatches[$key], "<img src=\"" . $newPath . "\">", $html);
            continue;
        }
        
        //here it is a real steam path
        $newPath = PATH_URL . "Download/Document/" . $steamObject->get_id();
        $html = str_replace($origMatches[$key], "<img src=\"" . $newPath . "\">", $html);
    }
    
    
    
    /* works not
    //document mod: replace not vaild srcs
    preg_match_all('/src="([%a-z0-9.\-_\/]*)"/iU', $html, $matches);
    $orig_matches = $matches[0];
    $path_matches = $matches[1];
    foreach ($path_matches as $key => $path) {
        $path = urldecode($path);
        if (parse_url($path, PHP_URL_SCHEME) != null) {
            continue;
        }
        var_dump($dirname . $path);
        $ref_object = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $dirname . $path);
        if ($ref_object instanceof \steam_object) {
            $new_path = PATH_URL . "Download/Document/" . $ref_object->get_id();
        } else {
            $new_path = PATH_URL . "styles/standard/images/404.jpg";
        }
        $html = str_replace($orig_matches[$key], "src=\"" . $new_path . "\"", $html);
    }
    */
    
    
    
    //new
    //video mod: replace not vaild src
    preg_match_all('/<video .* src="([%a-z0-9:.\-_\/]*)".*<\/video>/iU', $html, $matches); //get the video tag, works

    $origMatches = $matches[0];
    $pathMatches = $matches[1];

    //case: objectid
    //case: steam path
    //case: ext path

    foreach ($pathMatches as $key => $src) {
        try {
            $steamObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $src);
            if($steamObject===NULL) throw new \steam_exception;

            if ($steamObject instanceof \steam_object) {
                $new_path = PATH_URL . "Download/Document/" . $steamObject->get_id();
                $html = str_replace($origMatches[$key], "src=\"" . $new_path . "\"", $html);
            }else{
                throw new \steam_exception;
            }
        }catch(\steam_exception $e){
            //object not found
            //var_dump("not found");
        }
        //$ref_object = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $dirname . $path);
        //replace
    }
    
    
    
    //modifcations for flow player
    $pattern = '/<video .* src="([%a-z0-9:.\-_\/]*)".*<\/video>/iU';
    preg_match_all($pattern, $html, $matches);
    
    $fullMatches = $matches[0];
    $bracketMatches = $matches[1];
    
    foreach ($fullMatches as $key => $value) {
        $playerTagId = uniqid(); 
        //this would be stripped by htmlclean
        $jsPlayer='<script language="JavaScript"> flowplayer("'.$playerTagId.'","/styles/standard/javascript/Flowplayer/flowplayer-3.2.10.swf",{clip:{autoPlay: false,autoBuffering: true, url:"'.$bracketMatches[$key].'"}});</script>';
        
        $html = str_replace($value, '<div style="display:block;width:425px;height:300px;"><a href="'.$bracketMatches[$key].'" id="'.$playerTagId.'" style="display:block;width:425px;height:300px;"></a></div>'.$jsPlayer, $html);
        //$html = str_replace($value, '<div style="display:block;width:425px;height:300px;"><a href="'.$bracketMatches[$key].'" id="player" style="display:block;width:425px;height:300px;"></a></div><script language="JavaScript">flowplayer("player", "styles/standard/javascript/Flowplayer/flowplayer-3.2.10.swf", {clip: {autoPlay: true,autoBuffering: true,url: "'.$bracketMatches[$key].'"}});</script>', $html);
    }
    
    return $html;
  }
  
  
  
  function makeEditorModifications($html, $steamObject=NULL) {
    
    //mod 0  
    if($this->object!==NULL){
        $dirname = dirname($this->object->get_path()) . "/";
    }  
    else{
        $dirname = "/";
    }
    
    
    if($steamObject!==NULL){
        $dirname = dirname($steamObject->get_path()) . "/";
    }
        
    
    /*/*dont do this in editor mode
    //mod1
    //document mod: replace not vaild hrefs
    preg_match_all('/href="([%a-z0-9.-_\/]*)"/iU', $html, $matches);
    $orig_matches = $matches[0];
    $path_matches = $matches[1];
    foreach ($path_matches as $key => $path) {
        $path = urldecode($path);
        if (parse_url($path, PHP_URL_SCHEME) != null) {
            continue;
        }
        $ref_object = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $dirname . $path);
        if ($ref_object instanceof \steam_object) {
            $new_path = PATH_URL . "explorer/index/" . $ref_object->get_id();
        } else {
            $new_path = PATH_URL . "404/";
        }
        $html = str_replace($orig_matches[$key], "href=\"" . $new_path . "\"", $html);
    }
    
    

    //document mod: replace not vaild srcs
    preg_match_all('/src="([%a-z0-9.\-_\/]*)"/iU', $html, $matches);
    $orig_matches = $matches[0];
    $path_matches = $matches[1];
    foreach ($path_matches as $key => $path) {
        $path = urldecode($path);
        if (parse_url($path, PHP_URL_SCHEME) != null) {
            continue;
        }
        $ref_object = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $dirname . $path);
        if ($ref_object instanceof \steam_object) {
            $new_path = PATH_URL . "Download/Document/" . $ref_object->get_id();
        } else {
            $new_path = PATH_URL . "styles/standard/images/404.jpg";
        }
        $html = str_replace($orig_matches[$key], "src=\"" . $new_path . "\"", $html);
    }
    */
    
    
    
    //works for relative steam paths
    preg_match_all('/<img.*src="(.*)".*>/iU', $html, $matches);
    
    $origMatches = $matches[0];
    $pathMatches = $matches[1];
    
    foreach ($pathMatches as $key => $path) {
        if(!(substr($path, 0, 19)=="/Download/Document/") && (!(substr($path, 0, 4)=="http"))){
            //$path = $path;
        }else{
            continue;
        }
        try{
            $steamObject = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $dirname . $path);
            if(!($steamObject instanceof \steam_object)) throw new \steam_exception;
            if($steamObject===NULL) throw new \steam_exception;
        }  catch (\steam_exception $e){
            $newPath = PATH_URL . "styles/standard/images/404.jpg";
            $html = str_replace($origMatches[$key], "<img src=\"" . $newPath . "\">", $html);
            continue;
        }
        
        //here it is a real steam path
        $newPath = PATH_URL . "Download/Document/" . $steamObject->get_id();
        $html = str_replace($origMatches[$key], "<img src=\"" . $newPath . "\">", $html);
    }
    
    
    
    
    //new
    //video mod: replace not vaild src
    preg_match_all('/<video .* src="([%a-z0-9:.\-_\/]*)".*<\/video>/iU', $html, $matches); //get the video tag, works


    $origMatches = $matches[0];
    $pathMatches = $matches[1];

    //var_dump($pathMatches);

    //case: objectid
    //case: steam path
    //case: ext path


    foreach ($pathMatches as $key => $src) {


        try {
            $steamObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $src);
            if($steamObject===NULL) throw new \steam_exception;

            if ($steamObject instanceof \steam_object) {
                $new_path = PATH_URL . "Download/Document/" . $steamObject->get_id();
                $html = str_replace($origMatches[$key], "src=\"" . $new_path . "\"", $html);
            }else{
                throw new \steam_exception;
            }


        }catch(\steam_exception $e){
            //object not found
            //var_dump("not found");
        }


        //$ref_object = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $dirname . $path);




        //replace

    }
    
    return $html;
  }  
}
?>