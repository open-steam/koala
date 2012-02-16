<?php
	# Include DirectorAPI class file
	# and create a new instance of the class
	# Be sure to have entered your API key and path in the DirectorPHP.php file.
	include('classes/DirectorPHP.php');
	$director = new Director('your-api-key', 'your-api-path');
	
	# When your application is live, it is a good idea to enable caching.
	# You need to provide a string specific to this page and a time limit 
	# for the cache. Note that in most cases, Director will be able to ping
	# back to clear the cache for you after a change is made, so don't be 
	# afraid to set the time limit to a high number.
	# 
	# $director->cache->set('myrandomstring', '+30 minutes');

	# What sizes do we want?
	$director->format->add(array('name' => 'thumb', 'width' => '100', 'height' => '100', 'crop' => 1, 'quality' => 75, 'sharpening' => 1));
	$director->format->add(array('name' => 'large', 'width' => '800', 'height' => '450', 'crop' => 0, 'quality' => 95, 'sharpening' => 1));
	
	# We can also request the album preview at a certain size
	$director->format->preview(array('width' => '100', 'height' => '50', 'crop' => 1, 'quality' => 85, 'sharpening' => 1));

	# Make API call using get_album method. Replace "1" with the numerical ID for your album
	$album = $director->album->get(1);

	# Set images variable for easy access
	$contents = $album->contents[0];
	
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
	
	<title>SlideShowPro Director API Demo</title>
	<link rel="stylesheet" href="css/lightbox.css" type="text/css" media="screen" />
	<script src="js/prototype.js" type="text/javascript"></script>
	<script src="js/scriptaculous.js?load=effects" type="text/javascript"></script>
	<script src="js/lightbox.js" type="text/javascript"></script>

	<style type="text/css">
		body{ background:#111; color: #333; font: 13px 'Lucida Grande', Verdana, sans-serif; margin:0; }
		div#header { background:#000; padding:20px; margin:0;}
		div#header img {float:left; margin-right:10px;}
		h1 {color:#fafafa;font-weight:normal;font-size:25px;margin:0;}
		p {margin:5px 0 0;padding-left:110px;}
		div#images {padding:10px 20px;}
		a img {border:0;}
	</style>
</head>
<body>

<div id="header">
	<img src="<?php echo $album->preview->url; ?>" width="<?php echo $album->preview->width; ?>" height="<?php echo $album->preview->height; ?>" />
	<h1><?php echo $album->name ?></h1>
	<?php echo $director->utils->convert_line_breaks($album->description) ?>
</div>

<div id="images">
		<?php foreach ($contents as $image): ?>
			<a href="<?php echo $image->large->url ?>" rel="lightbox[road]" title="Uploaded by <?php echo $image->creator->display_name; ?>"><img src="<?php echo $image->thumb->url ?>" width="<?php echo $image->thumb->width ?>" height="<?php echo $image->thumb->height ?>" alt="" /></a>
		<?php endforeach ?>
</div>

</body>
</html>