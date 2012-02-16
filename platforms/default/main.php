<?php
include_once dirname(__FILE__) . '/etc/local.def.php';
include_once dirname(__FILE__) . '/etc/default.def.php';

include_once PATH_ETC . 'php.def.php';
include_once PATH_ETC . 'core.def.php';

include_once PATH_ETC . 'php.conf.php';
include_once PATH_ETC . 'core.conf.php';

if (strstr(strtolower(urldecode($_SERVER['REQUEST_URI'])), "/asset/")) {
		$cache_status = CacheSettings::caching_enabled();
		CacheSettings::enable_caching();
		$cache = get_cache_function("asset", 3600 );
		$em = ExtensionMaster::getInstance();
		$cache->call(array(&$em, "handleRequest"));
		if (!$cache_status) {
			CacheSettings::disable_caching();
		}
} else {
	ExtensionMaster::getInstance()->handleRequest();
}
?>