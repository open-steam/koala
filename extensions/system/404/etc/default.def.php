<?php
defined("URL_404") or define("URL_404", PATH_URL . "404/");
defined("LOG_404") or define("LOG_404", PATH_LOG . "404.log");
file_exists(LOG_404) or die("File for 404 errors is missing (" . LOG_404 . ").");
is_writable(LOG_404) or die("Not write access to file " . LOG_404);
?>