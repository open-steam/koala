<?php

// Tiny wrapper for use session as kind of a cache
// Use only for global values which are not expected to be changed during
// a session
// e.g. lms_steam::is_steam_admin and lms_steam::is_semester_admin

if (!defined("CACHE_UNDEFINED")) define("CACHE_UNDEFINED", "KOALA_CACHE_UNDEFINED");
if (!defined("USE_SESSIONCACHE")) define("USE_SESSIONCACHE", FALSE);

class sessioncache {
  static public function initialize_cache() {
    if (!USE_SESSIONCACHE) return CACHE_UNDEFINED;
    if (!isset($_SESSION["KOALA_CACHE"]))
      $_SESSION["KOALA_CACHE"] = array();
  }
  
  static public function clean_cache() {
    if (!USE_SESSIONCACHE) return FALSE;
    if (isset($_SESSION["KOALA_CACHE"]))
      unset($_SESSION["KOALA_CACHE"]);
    return TRUE;
  }
  
	static public function get_value( $key = "" ) {
    if (!USE_SESSIONCACHE) return CACHE_UNDEFINED;
    if (!isset($_SESSION["KOALA_CACHE"])) {
      sessioncache::initialize_cache();
      return CACHE_UNDEFINED;
    }
    if (!isset($_SESSION["KOALA_CACHE"][$key])) return CACHE_UNDEFINED;
    return $_SESSION["KOALA_CACHE"][$key];
	}

	static public function set_value( $key = "", $value = "" ) {
    if (!USE_SESSIONCACHE) return FALSE;
    if (!isset($_SESSION["KOALA_CACHE"])) {
      sessioncache::initialize_cache();
    }
    $_SESSION["KOALA_CACHE"][$key] = $value;
    return TRUE;
	}

	static public function unset_value( $key = "", $value = "" ) {
    if (!USE_SESSIONCACHE) return FALSE;
    if (!isset($_SESSION["KOALA_CACHE"])) {
      sessioncache::initialize_cache();
      return TRUE;
    }
    unset($_SESSION["KOALA_CACHE"][$key]);
    return TRUE;
	}
}
?>
