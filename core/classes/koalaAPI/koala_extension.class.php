<?php
require_once( PATH_LIB . "format_handling.inc.php" );

/**
 * Abstract class for representation of a koala extension
 */
abstract class koala_extension extends koala_object
{

	private $name;
	private $description;
	private $path;
	private $obj_type;
	private $classtype;
	private $category;
	private $enabled_default;
	private $values;
	private $description_file;
	private $requirements = array();
  
  
	function __construct( $description_file )
	{
    $em = extensionmanager::get_extensionmanager();
    
		$this->description_file = $description_file;
		$this->values = $this->parse_description_file($this->description_file);
		$this->name = $this->values['name'];
		$this->path = $this->values['path'];
		$this->description = $this->values['description'];
		$this->obj_type = $this->values['obj_type'];
		$this->classtype = $this->values['classtype'];
		$this->category = $this->values['category'];
		$this->enabled_default = $this->values['enabled_default'] === "TRUE" ? TRUE : FALSE;
		$this->requirements = $this->values['req_modules'];
		parent::__construct( steam_factory::get_object_by_name( $GLOBALS[ "STEAM" ]->get_id(), "/config/koala/extensions/" . $this->get_name() ) );
	}
	
	/**
	 * Enable an extension globally. This is done via the extensions persistent object.
	 * Dependencies to other extensions are taken into account. If a required extension
	 * is not active, activation will not be done.
	 * @return boolean activation success or not
	*/
	public function enable()
	{
		if(! $this->is_enabled())
		{	
			$can_be_enabled = TRUE;
			
			$em = lms_steam::get_extensionmanager();
			
			foreach ($this->requirements as $req_extension)
			{
				$req_extension = $em->get_extension($req_extension);
				if(! $req_extension->is_enabled()) $can_be_enabled = FALSE;
			}
				  
			if($can_be_enabled)
			{
				// if no extension config object exists, then create one:
				if ( !is_object($this->steam_object) ) {
					$this->steam_object = steam_factory::get_object_by_name( $GLOBALS[ "STEAM" ]->get_id(), "/config/koala/extensions/" . $this->get_name() );
					if ( !is_object($this->steam_object) )
					{
						$env = steam_factory::path_to_object( $GLOBALS[ "STEAM" ]->get_id(), "/config/koala/extensions" );
						$this->steam_object = steam_factory::create_object( $GLOBALS[ "STEAM" ]->get_id(), $this->get_name(), CLASS_OBJECT, $env );
						$this->steam_object->set_attributes( array(
										'OBJ_TYPE' => 'object_extension_koala',
										'OBJ_DESC' => $this->description,
										'EXTENSION_ENABLED' => 'TRUE'
						));
					}
				}
				
				$this->set_attribute("EXTENSION_ENABLED", "TRUE");
				return TRUE;
			}
			return FALSE;
		}
	}
	
	/**
	 * Disable an extension globally. This is done via the extensions persistent object.
	*/
	public function disable()
	{
		if($this->is_enabled())
		{
			$this->set_attribute("EXTENSION_ENABLED", "FALSE");
		}
	}
	
	/**
	 * If the extension knows which koala_object derivate matches a given
	 * steam_object derivate, then it will return a matching koala_*
	 * instance wrapped around the steam object. Otherwise it will return
	 * FALSE.
	 *
	 * @param steam_object $steam_object the steam object to wrap
	 * @return a koala_object derivate, or FALSE if the extension doesn't know
	 *   what kind of koala object matches the given steam object
	 */
	static public function get_koala_object_for ( $steam_object, $type, $obj_type )
	{
		return FALSE;
	}

	function parse_description_file( $file )
	{
		$doc = simplexml_load_file( $file );
		$values = array('name' => (String) $doc->name,
				'path' => (String) $doc->path,
				'description' => (String) $doc->description,
				'obj_type' => (String) $doc->OBJ_TYPE,
				'category' => (String) $doc->category,
				'classtype' => (String) $doc->CLASSTYPE,
				'enabled_default' => (String) $doc->enabled_default,
				'req_modules' => array());
		
		foreach($doc->requires->module as $module)
			$values['req_modules'][] = (String) $module;
		
		return $values;
	}

	function get_requirements($as_String = FALSE)
	{
		if(!$as_String)
			return $this->requirements;
		else
		{
			$value = "";
			foreach($this->requirements as $req) $value = $value . $req . " ";
			return $value;
		}
	}

	function get_name() { return $this->name; }

	function get_display_name () { return h( $this->name ); }
	
	function get_description() { return $this->description; }

	function get_display_description() { return h( $this->description ); }

	/**
	 * Override this function if you want your extension to define a portal
	 * headline. The headline is an array of arrays("name","link") or strings
	 * (just names).
	 *
	 * @param array $headline the headline so far, as set by the html handler
	 * @param string $context the context, as set in the html handler
	 * @param array $params the context params, as set in the html handler
	 * @return array the headline as an array of parts, or FALSE if you don't
	 *   want to define a headline
	 */
	function get_headline ( $headline = array(), $context = "", $params = array() )
	{
		return FALSE;
	}

	function get_classtype() { return $this->classtype; }

	function get_obj_type() { return $this->obj_type; }

	/**
	 * Returns whether a koala class can be extended by this extension.
	 * 
	 * Override this function to let your extension decide whether it can
	 * extend a given class.
	 *
	 * @param string $koala_class_name check whether this class can be extended
	 *   by this extension
	 * @return boolean TRUE if the koala object can be extended by this
	 *   extension, FALSE otherwise
	 */
	function can_extend ( $koala_class_name )
	{
		return FALSE;
	}

	/**
	 * Enables the extension for a certain koala_object. If you want to
	 * globally enable an extension, use enable().
	 * Extensions might refuse to be enabled for certain objects, so you might
	 * want to check is_enabled_for() after calling this method.
	 * 
	 * @see enable
	 * @see is_enabled_for
	 * 
	 * Override this function in your extension to remember (e.g. in an
	 * attribute) whether the extension is enabled.
	 *
	 * @param koala_object $koala_object the object for which to enable the
	 *   extension
	 */
	abstract public function enable_for ( $koala_object );

	/**
	 * Disables the extension for a certain koala_object. If you want to
	 * globally disable an extension, use enable().
	 * Extensions might refuse to be disabled for certain objects, so you might
	 * want to check is_enabled_for() after calling this method.
	 * 
	 * @see disable
	 * @see is_enabled_for
	 * 
	 * Override this function in your extension to remember (e.g. in an
	 * attribute) whether the extension is enabled.
	 *
	 * @param koala_object $koala_object the object for which to disable the
	 *   extension
	 */
	abstract public function disable_for( $koala_object );

	/**
	 * Checks whether the extension is enabled. If you pass a koala_object as
	 * a parameter, then the function will check whether the extension is
	 * enabled for that object. Otherwise it will check whether the extension
	 * is enabled globally.
	 * 
	 * You cannot override this function, but you can override is_enabled_for()
	 * in your extension to specify whether it is enabled or disabled for a
	 * given object.
	 * 
	 * @see is_default_enabled
	 * @see is_enabled_for
	 * 
	 * @param koala_object $koala_object
	 * @return boolean TRUE if the extension is enabled, FALSE if it is disabled
	 */
	final function is_enabled( $koala_object = FALSE )
	{
		if ( $koala_object === FALSE )
		{
			if ( !is_object($this->steam_object) ) return FALSE;
      
      $ret = sessioncache::get_value("koala_extension::is_enabled: " . $this->get_name());
      if ($ret === CACHE_UNDEFINED) {
        $ret = $this->get_attribute("EXTENSION_ENABLED");
        sessioncache::set_value("koala_extension::is_enabled: " . $this->get_name(), $ret);
      }
			return $ret === "TRUE" ? TRUE : FALSE;;
		}
		$enabled_for = $this->is_enabled_for( $koala_object );
    return $enabled_for;
	}

	/**
	 * Override this function to check whether your extension is enabled or
	 * disabled for a given object.
	 * 
	 * @see is_enabled
	 *
	 * @param koala_object $koala_object the object to check
	 * @return TRUE if the extension is enabled for that object, FALSE if it is
	 *   disabled
	 */
	abstract protected function is_enabled_for( $koala_object );

	function get_category() { return $this->category; }
	function get_path() { return PATH_EXTENSIONS . $this->path; }
	function get_description_file() { return $this->description_file; }
	function get_extension_information() { return $this->values; }
	
	function get_menu( $params = array() ) { return array(); }
	function get_context_menu( $context, $params = array() ) { return array(); }

	/**
	 * Gives a path to the extension and lets it handle it. If the
	 * extension handles the path, return TRUE or use exit to stop execution.
	 * 
	 * @param string|array $path the path to handle, starting with the path
	 *   element after the one that identifies the extension itself (e.g.
	 *   starts after "units/...")
	 * @param object $owner the owner of the extension, if the extension is
	 *   being used in the context of another object, or FALSE
	 */
	function handle_path( $path, $owner = FALSE, $portal = FALSE ) { return FALSE; }

	public function get_enabled_default() {return $this->enabled_default;}
	
	abstract function get_path_name(); 
	abstract function get_wrapper_class($obj);
	abstract static function get_version();
	function get_member_info($user) { return ""; }
	function get_filter_html($portal, $parent_id, $search_id) {return "";}
}
?>
