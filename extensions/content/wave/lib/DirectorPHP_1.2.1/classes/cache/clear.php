<?php
	$names = explode(',', $_GET['name']);
	foreach($names as $name) {
		$name = str_replace('/', DIRECTORY_SEPARATOR, str_replace('.', '', $name));
		$to_clear = dirname(__FILE__) . DIRECTORY_SEPARATOR . $name;
		if (file_exists($to_clear)) {
			unlink($to_clear);
		}
	}
?>