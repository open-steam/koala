<?php
set_include_path(get_include_path() . PATH_SEPARATOR . PATH_DOCROOT."/classes/");
require_once(PATH_DOCROOT."/classes/Cache/Lite.php");
require_once(PATH_DOCROOT."/classes/Cache/Lite/Function.php" );

class thumbnail {
	
	public function get_thumbnail($oid, $width, $height) {
		global $steam;
		$object = steam_factory::get_object( $steam, $oid );
		$cache = $this->get_cache_function( "thumb", 36000 );
		if ($object->check_access_read($steam->get_login_user())) {
			$lastmodified = $object->get_attribute("DOC_LAST_MODIFIED");
			if (isset($_SERVER['HTTP_CACHE_CONTROL']) && $_SERVER['HTTP_CACHE_CONTROL']=='no-cache') {
	 			$cache->drop("thumbnail::generate_thumbnail", $oid, $width, $height, $lastmodified);
			}
			$data = $cache->call("thumbnail::generate_thumbnail", $oid, $width, $height, $lastmodified);
		} else {
			if (isset($_SERVER['HTTP_CACHE_CONTROL']) && $_SERVER['HTTP_CACHE_CONTROL']=='no-cache') {
	 			$cache->drop("thumbnail::generate_thumbnail_noaccess", $width, $height);
			}
			$data = $cache->call("thumbnail::generate_thumbnail_noaccess", $width, $height);
		}
		return $data;
	}
	
	function get_cache_function( $defaultGroup = "cache", $lifetime = 3600 )
	{
		$cache_options = array(
				"caching"       => TRUE,
				"cacheDir"      => PATH_DOCROOT . "/cache/",
				"defaultGroup"  => $defaultGroup,
				"lifeTime"      => $lifetime,
				"fileNameProtection" => FALSE,
				"writeControl"  => TRUE,
				"readControl"   => TRUE,
				"readControlType" => "strlen",
				"automaticCleaningFactor" => 200
				);
		$cache = new Cache_Lite_Function( $cache_options );
		return $cache;
	}
	
	public static function generate_thumbnail($oid, $width, $height, $lastmodified) {
		global $steam;
		$object = steam_factory::get_object( $steam, $oid );
		$content = $object->get_content();
      	$tn_name = "tn_" . $lastmodified . "_" . $object->get_id() . $width . $height;
      
      	$im = imagecreatefromstring($content);
      	$img_width = imagesx($im);
      	$img_height = imagesy($im);
      
    	$aspect_ratio = $img_height/$img_width;
	   if ($height <= $width) {
    		if ($img_height >= $img_width) {
    			$new_height = $height;
    			$new_width = round($new_height/$aspect_ratio);
    		} else {
    			$new_width = $width;
    			$new_height = round($aspect_ratio * $new_width);
    		}
    	} else {
    	    if ($img_height < $img_width) {
    			$new_height = $height;
    			$new_width = round($new_height/$aspect_ratio);
    		} else {
    			$new_width = $width;
    			$new_height = round($aspect_ratio * $new_width);
    		}
    	}
      
      	$im_resize = imagecreatetruecolor($new_width,$new_height); 
      	imagecopyresized($im_resize, $im, 0, 0, 0, 0, $new_width, $new_height, $img_width, $img_height);
      	imagejpeg($im_resize, PATH_THUMB . $tn_name);
      
      	imagedestroy($im_resize);
      	imagedestroy($im);
      
      	$fh = fopen(PATH_THUMB . $tn_name, "r");
      	$tn_size = filesize(PATH_THUMB . $tn_name);
      	$tn_data = fread($fh, $tn_size);
      	fclose($fh);
      	unlink(PATH_THUMB . $tn_name);
      
      	$data = array();
      	$data["mimetype"]    = "image/jpeg";
      	$data["lastmodified"]= $lastmodified;
      	$data["name"]        = $object->get_name();
      	$data["content"]     = $tn_data;
      	$data["contentsize"] = $tn_size;
      	return $data;
	}
	
	public static function generate_thumbnail_noaccess($width, $height) {
		$fh = fopen(PATH_DOCROOT . "/tools/noaccess.jpg", "r");
      	$tn_size = filesize(PATH_DOCROOT . "/tools/noaccess.jpg");
      	$content = fread($fh, $tn_size);
      	fclose($fh);
      	$lastmodified = "00000";
      	$tn_name = "tn_" . $lastmodified . "_noaccess";
      
      	$im = imagecreatefromstring($content);
      	$img_width = imagesx($im);
      	$img_height = imagesy($im);
      
    	$aspect_ratio = $img_height/$img_width;
    	if ($height <= $width) {
    		if ($img_height >= $img_width) {
    			$new_height = $height;
    			$new_width = round($new_height/$aspect_ratio);
    		} else {
    			$new_width = $width;
    			$new_height = round($aspect_ratio * $new_width);
    		}
    	} else {
    	    if ($img_height < $img_width) {
    			$new_height = $height;
    			$new_width = round($new_height/$aspect_ratio);
    		} else {
    			$new_width = $width;
    			$new_height = round($aspect_ratio * $new_width);
    		}
    	}
      
      	$im_resize = imagecreatetruecolor($new_width,$new_height); 
      	imagecopyresized($im_resize, $im, 0, 0, 0, 0, $new_width, $new_height, $img_width, $img_height);
      	imagejpeg($im_resize, PATH_THUMB . $tn_name);
      
      	imagedestroy($im_resize);
      	imagedestroy($im);
      
      	$fh = fopen(PATH_THUMB . $tn_name, "r");
      	$tn_size = filesize(PATH_THUMB . $tn_name);
      	$tn_data = fread($fh, $tn_size);
      	fclose($fh);
      	unlink(PATH_THUMB . $tn_name);
      
      	$data = array();
      	$data["mimetype"]    = "image/jpeg";
      	$data["lastmodified"]= $lastmodified;
      	$data["name"]        = "no_access";
      	$data["content"]     = $tn_data;
      	$data["contentsize"] = $tn_size;
      	return $data;
	}
	
}