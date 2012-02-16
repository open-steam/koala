<?php
include_once( "../etc/koala.conf.php" );
if (!defined("SERVERMONITOR") || !SERVERMONITOR) {
	header("location:/");
	exit;
}
require_once( "HTML/Template/IT.php" );
if (isset($_GET["img"])) {
	$file = PATH_KOALA . "scripts/serverstats/" . $_GET["img"];
	header( "Content-Type: " . "image/png" );
	header( "Content-Length: " . filesize($file));
	readfile($file);
	exit;
}

$html = "<h1>Server Monitor</h1>";

$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "serverstats.template.html" );
$content->setCurrentBlock("BLOCK_STAT");
$content->setVariable("STAT_TYPE", "steam");
$content->parse("BLOCK_STAT");
$content->setCurrentBlock("BLOCK_STAT");
$content->setVariable("STAT_TYPE", "user");
$content->parse("BLOCK_STAT");

$html .= $content->get();
echo $html;
?>