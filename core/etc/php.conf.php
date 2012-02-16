<?php
ini_set('display_errors', DISPLAY_ERRORS);
error_reporting(ERROR_REPORTING);

ini_set("date.timezone", TIMEZONE);

ini_set("magic_quotes_gpc", MAGIC_QUOTES_GPC);
ini_set("magic_quotes_runtime", MAGIC_QUOTES_RUNTIME);
ini_set("magic_quotes_sybase", MAGIC_QUOTES_SYBASE);

ini_set("gd.jpeg_ignore_warning", GD_JPEG_IGNORE_WARNING);

ini_set("include_path", "." . PATH_SEPARATOR . PATH_DEPENDING . "classes/" . PATH_SEPARATOR . PATH_DEPENDING . "classes/PEAR/");

ini_set('max_execution_time', MAX_EXECUTION_TIME);
set_time_limit(MAX_EXECUTION_TIME);

if (!ini_set("memory_limit", MEMORY_LIMIT)) {
        error_log("Can't set memory_limit to " . MEMORY_LIMIT . " (is ". ini_get("memory_limit") . ").");
}
if (ini_get("safe_mode")) {
     	error_log("Safe mode is turned on!");
}
?>