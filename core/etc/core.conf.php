<?php
include_once(PATH_ETC . "errorcodes.def.php");
include_once(PATH_ETC . "permissions.def.php");

require_once(PATH_LIB . "database_access.inc.php");
require_once(PATH_LIB . "cache_handling.inc.php");
require_once(PATH_LIB . "sessioncache.inc.php");
require_once(PATH_LIB . "format_handling.inc.php");

include_once PATH_LIB . "toolkit.php";



if (DEVELOPMENT_MODE) {
	if (strpos(strtolower($_SERVER["REQUEST_URI"]), "phpinfo")) {
		phpinfo();die;
	} else if (strpos(strtolower($_SERVER["REQUEST_URI"]), "checksetup")) {
		check_setup();die;
	}
}

// ERROR-HANDLING
if (!isPhpCli() && !isAjaxRequest()){
	include_once(PATH_LIB . "error_handler.inc.php");
	include_once(PATH_LIB . "exception_handler.inc.php");
	set_error_handler("myErrorHandler");
	register_shutdown_function("shutdown");
	set_exception_handler("send_http_error");
}

//clean disconnect handling
register_shutdown_function(function() {
	if (isset($GLOBALS["STEAM"])) {
		$GLOBALS["STEAM"]->disconnect();
	}
}
);


/*
 * setup autoloader
 */
require_once PATH_DEPENDING . "classes/autoloader/Autoloader.php";
Autoloader::getRegisteredAutoloader()->remove();
$autoloaderIndexFile = PATH_TEMP . "koala_autoloader.gz";

if (DEVELOPMENT_MODE && browserNoCache() && !isAjaxRequest() && !isPhpCli()) {
	if (file_exists($autoloaderIndexFile)) {
		unlink($autoloaderIndexFile);
	}
}

if (dropCache() && !isAjaxRequest()) {
	emptyCacheFolder();
}

$autoloader = new Autoloader(PATH_BASE);
$autoloader->register();
$autoloader->getIndex()->setIndexPath($autoloaderIndexFile);
$autoloader->getFileIterator()->setOnlyDirPattern("~/((core)|(depending)|(extensions))~");

$autoloader->getFileIterator()->setOnlyFilePattern("~\.php$~i");
$autoloader->getFileIterator()->addSkipDirPattern("~/((javascript)|(\.settings)|(\.todo)|(cache)|(log)|(temp))~");
$autoloader->getFileIterator()->addSkipFilePattern("~/\.~");
if (apache_getenv("AUTOLOADER_BUILD_RUNNING")) {
	die("System Initialisation is running. Please wait.");
}
if (!file_exists($autoloaderIndexFile)) {
	displayStartupUserInfo();
	try {
		apache_setenv("AUTOLOADER_BUILD_RUNNING", true);
		$autoloader->buildIndex();
		apache_setenv("AUTOLOADER_BUILD_RUNNING", false);
	} catch (AutoloaderException $e) {
		if ($e instanceof AutoloaderException_Parser_IO) {
			die("ERROR: Check you file permissions!");
		} else if ($e instanceof AutoloaderException_IndexBuildCollision) {
			if (!isAjaxRequest()) {
				echo $e->getMessage();
			}
		} else {
			var_dump($e); die;
		}
	}
	
	if (!isPhpCli() && !isAjaxRequest()) {
		echo "\n\n Trying to reload in 10 sec.<script type=\"text/javascript\">window.setTimeout('window.location.reload()', 10000);</script>";
		die;
	}
}

// start session
session_name(SESSION_NAME);
session_start();

// style
if (!empty( $_GET[ "style" ] ) && DEVELOPMENT_MODE == TRUE ){
	$STYLE = $_GET[ "style" ];
	$_SESSION["STYLE"] = $_GET[ "style" ];
	get_cache()->clean();
} else if (!empty($_SESSION["STYLE"]) && DEVELOPMENT_MODE == TRUE ) {
	$STYLE = $_SESSION["STYLE"];
}
define( "PATH_STYLE", PATH_URL . "styles/" . STYLE . "/" );

// statistic information
if (isset($_GET["statistics_level"])) {
  $_SESSION["STATISTICS_LEVEL"] = $_GET["statistics_level"];
}

if (!isset($_SESSION["STATISTICS_LEVEL"])) {
  if (defined("STATISTICS_LEVEL")) {
    $_SESSION["STATISTICS_LEVEL"] = STATISTICS_LEVEL;
  } else $_SESSION["STATISTICS_LEVEL"] = 0;
}

if ($_SESSION["STATISTICS_LEVEL"] > 1) {
	$GLOBALS["page_time_start"] = microtime(TRUE);
}

//setup language support
// need to set language in this script explicitely. (was set in language_support too and works for gettext but not for the strftime methods. therefore calling setlocale in this script explicitely as workaround
setlocale(LC_ALL, \language_support::get_language());

function secure_gettext($string) {
	if (is_string($string) && $string != "") {
		return gettext($string);
	} else {
		return "";
	}
}

?>