<?php
include_once( "../../etc/koala.conf.php" );

$filename = PATH_KOALA . "log/messages.log";
            
if (file_exists($filename)) {
	$fp = fopen($filename, "r");
	$content = fread($fp, filesize($filename));  
	$content = explode("\n", $content);
    $content = array_reverse($content );
    $users = array();
    foreach($content as $key=>$value)    {
    	$values = preg_split('/\s+/', $value);
    	if (count($values) > 3) {
    		$time = strtotime($values[0] . " " . $values[1]);
    		$user = $values[3];	
    	}
    	if (time() - $time < 120) {
    		$users[$user] = "1";
    	} else {
    		break;
    	}
    }
    echo count($users);
}

?>