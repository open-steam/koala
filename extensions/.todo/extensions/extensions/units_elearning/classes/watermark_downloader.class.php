<?php

define("ENABLE_IMAGE_WATERMARKING", false);

class watermark_downloader {
	
	function download() {
		global $STYLE;
		if (!isset( $_GET["id"] ) ) throw new Exception( "No 'id' or 'name' param provided." );

  		/* call get_document.php to get the image */

		ob_start();
		include ('get_document.php');
		$data = ob_get_contents();
		ob_end_clean();

		if (ENABLE_IMAGE_WATERMARKING) {
  /* create a gd object from data */
		$im = @imagecreatefromstring($data);
  
		if (!$im) throw new Exception( "Ressource is not an image!" );
		else {
		    /* Watermark settings - Implement a company specifil logo here later. */
			$transition = 85;
			$watermarkfile = imagecreatefrompng(PATH_PUBLIC . 'styles/'.$STYLE.'/images/logo_bdz.png'); //TODO: Change the path here!
			$waternarkpic_width = imagesx($watermarkfile);
			$waternarkpic_height = imagesy($watermarkfile);
			$watermarkdest_x = 15;
			$watermarkdest_y = 15;
		
			imagecopymerge($im, $watermarkfile, $watermarkdest_x, $watermarkdest_y, 0, 0, $waternarkpic_width, $waternarkpic_height, $transition);
			imagejpeg($im);
  		}
		} else {
			echo $data;
		}
	}
}
  
?>