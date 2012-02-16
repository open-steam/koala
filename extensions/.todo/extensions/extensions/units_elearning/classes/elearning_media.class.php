<?php
require_once( PATH_EXTENSIONS . "units_elearning/classes/elearning_object.class.php");
require_once( PATH_EXTENSIONS . "units_elearning/classes/watermark_downloader.class.php");

class elearning_media extends elearning_object {
	
	private $cache;
	
	function __construct($parent_tmp, $steamObject_tmp, $xml) {
		$this->parent = $parent_tmp;
		$this->steamObject = $steamObject_tmp;
		$this->xml = $xml;
		$this->cache = get_cache_function( "unit_elearning", 3600 );
	}
	
	function download() {
		$steam_object_name = $this->cache->call(array($this->steamObject, "get_name"));
		$_GET['id']=$this->steamObject->get_id();
		$_GET['filename'] = $steam_object_name;
		
		if (strpos($steam_object_name, ".jpg") !== false ){
			$dl = new watermark_downloader();
			$dl->download();
			exit;
		} else {
			ob_start();
  			include(PATH_PUBLIC . 'get_document.php');
 			$data = ob_get_contents();
  			ob_end_clean();
  			echo $data;
		}
	}
	
}

?>