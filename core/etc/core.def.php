<?php
defined("RED_BANNER_ERROR_LEVEL") or define("RED_BANNER_ERROR_LEVEL", E_ERROR);
defined("DEVELOPMENT_MODE") or define("DEVELOPMENT_MODE", FALSE);
defined("STATISTICS_LEVEL") or define("STATISTICS_LEVEL", 0);
defined("LANGUAGE_DEFAULT_STEAM") or define("LANGUAGE_DEFAULT_STEAM", "german");
defined("CHARSET") or define("CHARSET", "utf-8");
defined("LANGUAGE_DEFAULT") or define("LANGUAGE_DEFAULT", "de_DE");
defined("LANGUAGE_CHOSEN") or define("LANGUAGE_CHOSEN", LANGUAGE_DEFAULT);
defined("ENABLE_GETTEXT") or define("ENABLE_GETTEXT", TRUE);
defined("ENABLE_CACHING") or define("ENABLE_CACHING", FALSE);
defined("JAVASCRIPT_SECURITY") or define("JAVASCRIPT_SECURITY", FALSE);

defined("PORTAL_TEMPLATE") or define("PORTAL_TEMPLATE",	PATH_EXTENSIONS . "system/frame/ui/html/portal.template.html" );
defined("PORTAL_ICON_BAR") or defin("PORTAL_ICON_BAR", TRUE);
defined("PATH_JAVASCRIPT") or define("PATH_JAVASCRIPT", PATH_URL . "styles/standard/javascript/");

/// String: Encryption key for session data
/// This is used to encrypt data stored in the PHP session. It is only used
/// by the server and can be set to any string value (ideally a random string
/// of 10 characters or longer)
defined("ENCRYPTION_KEY") or define( "ENCRYPTION_KEY", "sdfgbvdghter" );

define("LOG_ERROR",		PATH_LOG . "errors.log" );
file_exists(LOG_ERROR) or die("File for errors is missing (" . LOG_ERROR . ").");
is_writable(LOG_ERROR) or die("Not write access to file " . LOG_ERROR);
define("LOG_MESSAGES",	PATH_LOG . "messages.log");
file_exists(LOG_MESSAGES) or die("File for messages is missing (" . LOG_MESSAGES . ").");
is_writable(LOG_MESSAGES) or die("Not write access to file " . LOG_MESSAGES);
define("LOG_SECURITY",	PATH_LOG . "security.log");
file_exists(LOG_SECURITY) or die("File for security is missing (" . LOG_SECURITY . ").");
is_writable(LOG_SECURITY) or die("Not write access to file " . LOG_SECURITY);

defined("BLACKLISTED_EXTENSIONS") or define("BLACKLISTED_EXTENSIONS", "");
defined("PLATFROM_MENUS") or define("PLATFROM_MENUS", "");
defined("SESSION_NAME") or define("SESSION_NAME", str_replace(".", "-", PLATFORM_ID . KOALA_VERSION));
?>