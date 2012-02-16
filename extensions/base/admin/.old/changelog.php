<?php 
// no direct call
if (!defined('_VALID_KOALA')) {
	header("location:/");
	exit;
}
?>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
</head>
<body>
<p>
<?php

	readfile( "../changelog.txt" );
	
?>
</p>
</body>
</html>
