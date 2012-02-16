<?php
/*
 * get icons
 * 
 * @author Marcel Jakoblew
 */
switch ($_GET["image"]){
	case "green":$file = PATH_KOALA . "extensions/exam_organization/images/icon_ok.png";break;
	case "red":$file = PATH_KOALA . "extensions/exam_organization/images/icon_warning.png";break;
	case "delete":$file = PATH_KOALA . "extensions/exam_organization/images/icon_delete.png";break;
	default: exit();
}
header( "Content-Type: " . "image/png" );
header( "Content-Length: " . filesize($file));
readfile($file);
exit();
?>