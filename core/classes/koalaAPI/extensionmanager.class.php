<?php

/*
 * Class for management of modular koaLA extensions
 */
class extensionmanager
{
	private $installed_extensions = array();
	private $path_to_extension = array();
	private $extensions_dir;
	private static $singleton;

	/** Returns a singleton instance of the extensionmanager. Use this instead of "new extensionmanager()" as it
	  * performs some initialization.
	  */
	function get_extensionmanager()
	{
		if ( is_object( self::$singleton ) )
			return self::$singleton;
		self::$singleton = new extensionmanager();
		self::$singleton->init();
		return self::$singleton;
	}

	/**
	 * Avoid creating an extensionmanager directly, always use lms_steam::get_extensionmanager() as it will return a
	 * singleton extensionmanager and therefore avoid initialization race conditions and save performance.
	 */
	private function __construct()
	{
	}

	/** Prevents infinite recursions when initialising extensions that use an extensionmanager in their constructor or
	 * enable() method. Called by lms_steam::get_extensionmanager after creating an extensionmanager singleton. */
	function init()
	{
		$this->extensions_dir = $this->get_extensions_dir();
    	$this->installed_extensions = $this->find_installed_extensions($this->extensions_dir);
		$this->path_to_extension = $this->find_extension_paths( $this->installed_extensions );
	}

  function get_extensions_dir() {
		if (defined( "PATH_EXTENSIONS" ) && is_dir( PATH_EXTENSIONS ) ) return PATH_EXTENSIONS;
		else if (defined( "PATH_KOALA" ) && is_dir( PATH_KOALA . "extensions/" )) return PATH_KOALA . "extensions/";
		else
		{
			throw new Exception( "No directory for extensions found!" );
		}
	}

	private function find_extension_paths( $extensions )
	{
		$bypath = array();
		foreach ( $extensions as $extension ) {
			$path_name = $extension->get_path_name();
			if ( !is_string( $path_name ) || empty( $path_name ) )
				continue;
			if ( !isset( $bypath[ $path_name ] ) )
				$bypath[ $path_name ] = array( $extension );
			else
				$bypath[ $path_name ][] = $extension;
		}
		return $bypath;
	}

	private function find_installed_extensions( $extensions_dir )
	{
		$extensions = array();
		$extension_names = array();
		
		$filenames = scandir( $extensions_dir );
		foreach ($filenames as $filename)
		{
			if((is_dir($extensions_dir . $filename)) && ($filename != '.') && ($filename != '..') && ($filename != "CVS"))
			{
				$extension_dir = $extensions_dir . $filename;
				$files = scandir($extension_dir);
				$classes_exists = FALSE;
				foreach($files as $file)
				{
					if(($file === "classes") && (is_dir($extension_dir . "/" . $file)))
					{
						$classes_exists = TRUE;
						$classes_dir = $extension_dir . "/" . $file;
						$classes = scandir($classes_dir);
						$extension_class_exists = FALSE;
						foreach($classes as $class)
						{
							if((is_file($classes_dir . "/" . $class)) && (strlen($class) > 20) && (substr($class, -20) == ".extension.class.php"))
							{
								$extension_class_exists = TRUE;
								$classname = substr($class,0,-20);
								require_once($classes_dir . "/" . $class);
                // TODO: THis leads into Server Request by determining the associates steam object
							  $extensions[ $filename ] = new $classname();
							}
						}
					}
				}
				if(!$classes_exists) throw new Exception("Extension '" . $filename . "' has no classes folder!");
				if(!$extension_class_exists) throw new Exception("Extension '" . $filename . "' has no main class to load!");
			}
		}
		return $extensions;
	}

	public function get_installed_extensions()
	{
		return $this->installed_extensions;
	}
	
	/**
	 * This function returns all installed extensions that are globally enabled (active).
	 * @return array() array of active extensions
	*/
	public function get_enabled_extensions()
	{
		$enabled_extensions = array();
		foreach ( $this->installed_extensions as $ex )
		{
			if ($ex->is_enabled()) $enabled_extensions[] = $ex;
		}
		return $enabled_extensions;
	}
	
	public function get_extension( $name )
	{
		if ( !isset( $this->installed_extensions[ $name ] ) )
			return FALSE;
		else
			return $this->installed_extensions[ $name ];
	}

	public function get_extension_by_id( $id )
	{
		foreach ( $this->installed_extensions as $ex)
			if ($ex->get_id() === $id) return $ex;
		return FALSE;
	}
	
	/**
	 * Return an extension by its OBJ_TYPE
	 */
	public function get_extension_by_obj_type( $obj_type )
	{
		foreach( $this->installed_extensions as $extension)
			if ($extension->get_obj_type() == $obj_type)  return $extension;
		return NULL;
	}
	
	public function get_extensions_by_class( $koala_class_name )
	{
		$extensions = array();
		foreach ( $this->installed_extensions as $extension )
			if ( $extension->is_enabled() && $extension->can_extend( $koala_class_name ) )
				$extensions[] = $extension;
		return $extensions;
	}

	/**
	 * Return all extensions that are enabled and depend on the given extension parameter (see requirements)
	 * @param $extension: the koala_extension object that acts as base extension
	 * @return all dependent extension, if present, else array()
	 */
	public function get_dependent_extensions( $extension )
	{
		$dep_extensions = array();
		foreach( $this->get_enabled_extensions() as $ex )
		{
			$extension_in_req = FALSE;
			foreach($ex->get_requirements() as $requirement)
				if($extension->get_name() === $requirement) $extension_in_req = TRUE;
				
			if( $ex->get_requirements() != array() && $extension_in_req )
				$dep_extensions[] = $ex;
		}
		return $dep_extensions;
	}
	
	/**
	 * Return all installed extension that match a specific prefix or postfix in it the OBJ_TYPE
	 */
	public function get_extensions_by_obj_type($starts_with = NULL, $ends_with = NULL)
	{
		$ret = array();
		
		foreach($this->installed_extensions as $extension)
		{
			$et = $extension->get_obj_type();
			
			if( is_string($starts_with) ) {
				if ( strlen($starts_with) > strlen($et) ) continue;
				if ( strpos($et, $starts_with) != 0 ) continue;
			}
			if( is_string($ends_with) ) {
				if ( strlen($ends_with) > strlen($et) ) continue;
				$endpos = strlen($et) - strlen($ends_with);
				if ( strpos($et, $ends_with, $endpos) != $endpos ) continue;
			}
			$ret[] = $extension;
		}
		return $ret;
	}

	/**
	 * Returns an array of extension objects that can act upon a given
	 * path (starting with the part that identifies the extension,
	 * e.g. "units/...").
	 *
	 * @param string|array $path the path to handle, starting with the path element
	 *   that identifies the extension itself (e.g. starts with "units/...")
	 * @return array an array of extension objects that can act upon that path
	 */
	public function get_extensions_by_path( $path )
	{
		if ( is_string( $path ) ) $path = explode( "/", $path );
		if ( !isset( $path[0] ) ) return array();
		if ( isset( $this->path_to_extension[ $path[0] ] ) )
			return $this->path_to_extension[ $path[0] ];
		else
			return array();
	}

	/**
	 * Gives a path to the extensions and lets them handle it. If an
	 * extension handles the path, then execution is stopped there.
	 * If no extension decides to handle the path, then this function will
	 * return.
	 *
	 * @param string|array $path the path to handle, starting with the path
	 *   element that identifies the extension itself (e.g. starts with
	 *   "units/...")
	 * @param object $owner the owner of the extension, if the extension is
	 *   being used in the context of another object, or FALSE
	 */
	public function handle_path( $path, $owner = FALSE, $portal = FALSE )
	{
		if(!isset($portal) || !is_object($portal))
		{
			$portal = lms_portal::get_instance();
			$portal->initialize( GUEST_NOT_ALLOWED );
		}
		
		if ( is_string( $path ) ) $path = explode( "/", $path );
		foreach ( $this->get_extensions_by_path( $path ) as $extension ) {
			if ( ! $extension->can_extend( get_class( $owner ) ) || ! $extension->is_enabled() ) continue;
			if ( $extension->handle_path( array_slice( $path, 1 ), $owner, $portal ) )
				exit;
		}
	}
	
	/**
	 * Funktion for enabling a specific extension by its name globally.
	 * @param $name the name of the extension to be enabled
	*/
	public function enable_extension($name)
	{
		$this->get_extension($name)->enable();
	}
	
	/**
	 * Funktion for disabling a specific extension by its name globally.
	 * Extensions that depend on the disabled one will be disabled too!
	 * @param $name the name of the extension to be disabled
	*/
	public function disable_extension($name)
	{
		$extension = $this->get_extension($name);
		$extension->disable();
		foreach( $this->installed_extensions as $ex )
		{
			$requirements = $ex->get_requirements();
			foreach( $requirements as $req )
				if($req === $extension->get_name()) $ex->disable();
		}
	}
  
  public static function get_config_objects() {
    $configs = array();
    $config_container = steam_factory::get_object_by_name( $GLOBALS[ "STEAM" ]->get_id(), "/config/koala/extensions/" );
    if (is_object($config_container)) {
      $config_objects = $config_container->get_inventory_raw();
      steam_factory::load_attributes($GLOBALS["STEAM"]->get_id(), $config_objects, array(OBJ_NAME));
    } else $config_objects = array();
    foreach($config_objects as $co) {
      $configs[$co->get_name()] = $co;
    }
    return $configs;
  }
  

}
?>
