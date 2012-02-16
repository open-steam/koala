<?php
//**** PHP.ini section
defined("DISPLAY_ERRORS") or define("DISPLAY_ERRORS", 0);
defined("ERROR_REPORTING") or define("ERROR_REPORTING", E_ALL | E_NOTICE);
defined("LOW_API_CACHE") or define("LOW_API_CACHE", true);

defined("PATH_SERVER") or define("PATH_SERVER", "https://localhost");
defined("PATH_URL") or define("PATH_URL", PATH_SERVER . "/");
defined("PATH_URL_ERROR") or define( "PATH_URL_ERROR",	PATH_URL . "error/" );
// the adress where cgi-bin scripts are located
defined("PATH_URL_CGI") or define("PATH_URL_CGI", PATH_URL . "cgi-bin/");
//address of the user administration; the UI's toolbar contains a link to that address
defined("PATH_URL_USERADMIN") or define("PATH_URL_USERADMIN", PATH_URL . "useradmin/");
//address of the user manual; the UI contains a help button with a link to that address
defined("PATH_URL_USERMANUAL") or define("PATH_URL_USERMANUAL", PATH_URL . "help/");
//address of the user agreement; users will not be able to access the server with their
//account but be redirected to this page as long as they haven't changed their password
defined("PATH_URL_DISCLAIMER") or define("PATH_URL_DISCLAIMER", PATH_URL . "disclaimer/");

//**** path section
defined("PATH_BASE") or define("PATH_BASE", dirname(dirname(dirname(dirname(__FILE__)))). "/");
defined("PATH_CORE") or define("PATH_CORE", PATH_BASE . "koala-core/");
defined("PATH_DEPENDING") or define("PATH_DEPENDING", PATH_BASE . "koala-depending/");
defined("PATH_EXTENSIONS") or define("PATH_EXTENSIONS", PATH_BASE . "koala-extensions/");
defined("PATH_PLATFORMS") or define("PATH_PLATFORMS", PATH_BASE . "koala-platforms/");
defined("PATH_PLATFORMS_DEFAULT") or define("PATH_PLATFORMS_DEFAULT", PATH_BASE . "koala-platforms/default/");
defined("PATH_STYLES") or define("PATH_STYLES", PATH_BASE . "koala-styles/");
defined("PATH_TOOLS") or define("PATH_TOOLS", PATH_BASE . "koala-tools/");

defined("PATH_ETC") or define("PATH_ETC", PATH_CORE . "etc/");
defined("PATH_CACHE") or define("PATH_CACHE", PATH_CURRENT_PLATFORM . "cache/");
file_exists(PATH_CACHE) or die("Folder for caching is missing (" . PATH_CACHE . ").");
is_writable(PATH_CACHE) or die("Not write access to folder " . PATH_CACHE);
defined("PATH_LIB") or define("PATH_LIB", PATH_CORE . "lib/");
defined("PATH_LOG") or define("PATH_LOG", PATH_CURRENT_PLATFORM . "log/");
file_exists(PATH_LOG) or die("Folder for logging is missing (" . PATH_LOG . ").");
is_writable(PATH_LOG) or die("Not write access to folder " . PATH_LOG);
defined("PATH_TEMP") or define("PATH_TEMP", PATH_CURRENT_PLATFORM . "temp/");
file_exists(PATH_TEMP) or die("Folder for temp files is missing (" . PATH_TEMP . ").");
is_writable(PATH_TEMP) or die("Not write access to folder " . PATH_TEMP);
defined("PATH_LOCALE") or define("PATH_LOCALE", PATH_PLATFORMS . PLATFORM_FOLDER . "/locale/");
?>