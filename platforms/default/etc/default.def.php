<?php
//**** PHP.ini section
defined("DISPLAY_ERRORS") or define("DISPLAY_ERRORS", 0);
defined("ERROR_REPORTING") or define("ERROR_REPORTING", (E_ERROR | E_WARNING | E_PARSE | E_NOTICE)); //do not use E_STRICT, this causes pear/it-template errors in php 5.4
defined("LOW_API_CACHE") or define("LOW_API_CACHE", true);

defined("PATH_SERVER") or define("PATH_SERVER", "http://localhost");
defined("PATH_URL") or define("PATH_URL", PATH_SERVER . "/");
defined("PATH_URL_ERROR") or define("PATH_URL_ERROR",	PATH_URL . "error/");
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
defined("PATH_CORE") or define("PATH_CORE", PATH_BASE . "core/");
defined("PATH_DEPENDING") or define("PATH_DEPENDING", PATH_BASE . "depending/");
defined("PATH_EXTENSIONS") or define("PATH_EXTENSIONS", PATH_BASE . "extensions/");
defined("PATH_PLATFORMS") or define("PATH_PLATFORMS", PATH_BASE . "platforms/");
defined("PATH_PLATFORMS_DEFAULT") or define("PATH_PLATFORMS_DEFAULT", PATH_BASE . "platforms/default/");
defined("PATH_STYLES") or define("PATH_STYLES", PATH_BASE . "styles/");
defined("PATH_TOOLS") or define("PATH_TOOLS", PATH_BASE . "tools/");

defined("PATH_ETC") or define("PATH_ETC", PATH_CORE . "etc/");
defined("PATH_CACHE") or define("PATH_CACHE", PATH_CURRENT_PLATFORM . "cache/");
file_exists(PATH_CACHE) or die("Folder for caching is missing (" . PATH_CACHE . ").");
is_writable(PATH_CACHE) or die("Not write access to folder " . PATH_CACHE);
defined("PATH_LIB") or define("PATH_LIB", PATH_CORE . "lib/");
defined("PATH_LOG") or define("PATH_LOG", PATH_CURRENT_PLATFORM . "log/");
file_exists(PATH_LOG) or die("Folder for logging is missing (" . PATH_LOG . ").");
is_writable(PATH_LOG) or die("Not write access to folder " . PATH_LOG);
defined("PATH_TEMP") or define("PATH_TEMP", PATH_CURRENT_PLATFORM . "temp/");
defined("PATH_PERSISTENCE") or define("PATH_PERSISTENCE", PATH_CURRENT_PLATFORM . "persistence/");
file_exists(PATH_TEMP) or die("Folder for temp files is missing (" . PATH_TEMP . ").");
is_writable(PATH_TEMP) or die("Not write access to folder " . PATH_TEMP);
defined("PATH_LOCALE") or define("PATH_LOCALE", PATH_PLATFORMS . PLATFORM_FOLDER . "/locale/");

//phpsteam api
defined("API_DOUBLE_FILENAME_NOT_ALLOWED") or define("API_DOUBLE_FILENAME_NOT_ALLOWED", true);
defined("API_MAX_INVENTORY_COUNT") or define("API_MAX_INVENTORY_COUNT", 500);
defined("API_MAX_CONTENT_SIZE") or define("API_MAX_CONTENT_SIZE", 52428800); //50mb
defined("API_TEMP_DIR") or define("API_TEMP_DIR", PATH_TEMP);
defined("FILE_PERSISTENCE_BASE_PATH") or define("FILE_PERSISTENCE_BASE_PATH", PATH_PERSISTENCE);
