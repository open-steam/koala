<?php
define( "CACHE_LIFETIME_STATIC", 	3600 );
define( "CACHE_LIFETIME_DYNAMIC",	120  );
define( "CACHE_LIFETIME_HIGH_DYNAMIC",	30   );

class CacheSettings 
{
  public static $enable_caching = ENABLE_CACHING;

  public static function enable_caching() 
  {
    self::$enable_caching = TRUE;
  }
  
  public static function disable_caching() 
  {
    self::$enable_caching = FALSE;
  }
  
  public static function caching_enabled() 
  {
    return self::$enable_caching;
  }
  
}

// requires path_cache in global config array
function get_cache_function( $defaultGroup = "cache", $lifetime = CACHE_LIFETIME_DYNAMIC )
{
	$cache_options = array(
			"caching"       => CacheSettings::caching_enabled(),
			"cacheDir"      => PATH_CACHE,
			"defaultGroup"  => $defaultGroup,
			"lifeTime"      => $lifetime,
			"fileNameProtection" => FALSE,
			"writeControl"  => TRUE,
			"readControl"   => TRUE,
			"readControlType" => "strlen",
			"automaticCleaningFactor" => 200
			);
	$cache = new Cache_Lite_Function( $cache_options );
	return $cache;
}

// requires path_cache in global config array
function get_cache_output( $lifetime = CACHE_LIFETIME_STATIC )
{
	require_once( "Cache/Lite/Output.php" );
	$cache_options = array(
			"cacheDir"	=> PATH_CACHE,
			"lifetime"	=> $lifetime,
			"writeControl"	=> TRUE,
			"readControl"	=> TRUE,
			"readControlType" => "md5"	
			);
	$cache = new Cache_Lite_Output( $cache_options );
	return $cache;
}

function get_icon_cache( $lifetime = CACHE_LIFETIME_STATIC ) {
  require_once( "Cache/Lite.php" );
  $cache_options = array(
    "cacheDir" => PATH_CACHE,
    "lifeTime" => $lifetime,
    "automaticSerialization" => TRUE
    );
  $cache = new Cache_Lite( $cache_options );
  return $cache;
}

function get_cache( $lifetime = CACHE_LIFETIME_STATIC ) {
  require_once( "Cache/Lite.php" );
  $cache_options = array(
    "cacheDir" => PATH_CACHE,
    "lifeTime" => $lifetime,
    "automaticSerialization" => TRUE
    );
  $cache = new Cache_Lite( $cache_options );
  return $cache;
}

?>
