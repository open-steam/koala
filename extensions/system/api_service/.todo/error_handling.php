<?php
	function xml_error($message, $id = 9999) {
		header('Content-Type: text/xml; charset=utf-8');
		echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>"."\n";
		echo "<error id=\"" . $id . "\"><![CDATA[" . $message . "]]></error>\n";
	}
?>