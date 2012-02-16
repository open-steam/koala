<?php
defined("URL_403") or define("URL_403", PATH_URL . "403/");
defined("LOG_403") or define("LOG_403", PATH_LOG . "403.log");
file_exists(LOG_403) or die("File for 403 errors is missing (" . LOG_403 . ").");
is_writable(LOG_403) or die("Not write access to file " . LOG_403);
?>