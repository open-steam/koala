<?php

class koala_object {

	protected $steam_object;

	public function __construct( $steam_object ) {
		$this->steam_object = $steam_object;
	}

	/**
	 * Returns a koala_* instance matching the given steam_* object.
	 * You can use this function if you don't know which type a steam object
	 * is, e.g. to be able to call specialized functions like get_display_name().
	 * 
	 * Note that this function will at least query the object's type and its
	 * OBJ_TYPE attribute. Extensions may also query additional attributes,
	 * so this function might be costly regarding server requests. Only use it
	 * when you need a matching koala object (e.g. for get_display_name()) and
	 * you cannot determine the matching class yourself.
	 *
	 * @param Object $steam_object the steam_* object for which to return a
	 *   matching koala_* wrapper object
	 * @return Object a koala_* wrapper object that matches the given steam_*
	 *   object in class
	 */
	static public final function get_koala_object( $steam_object, $vars = array() ) {
		if ( !is_object( $steam_object ) ) return FALSE;
		if ( $steam_object instanceof koala_object ) return $steam_object;

    if (isset($vars[OBJ_TYPE])) {
      $obj_type = $vars[OBJ_TYPE];
    } else $obj_type = $steam_object->get_attribute( OBJ_TYPE );
    $type = $steam_object->get_type();
    //TODO: rewrite old extensionmanager
/*    
		foreach ( lms_steam::get_extensionmanager()->get_installed_extensions() as $extension ) {
			$obj = $extension->get_koala_object_for( $steam_object, $type, $obj_type );
			if ( is_object( $obj ) ) {
				return $obj;
      }
		}*/
		switch ( TRUE ) {
			case $steam_object instanceof steam_user:
				return new koala_user( $steam_object );
			case $steam_object instanceof steam_group:
				$course_group = koala_group_course::get_course_group( $steam_object );
				if ( is_object( $course_group ) )
					return new koala_group_course( $course_group );
				else
					return new koala_group_default( $steam_object );
			case $steam_object instanceof steam_container:
        // check for a wiki container first
				if ( $obj_type === "container_wiki_koala" || $obj_type === "room_wiki_koala" || $obj_type === "KOALA_WIKI" ) {
					return new lms_wiki( $steam_object );
        }
        // check for workroom
				if ( is_object( $creator = $steam_object->get_creator() ) && is_object( $workroom = $creator->get_workroom() ) && $workroom->get_id() == $steam_object->get_id() )
					return new koala_container_workroom( $steam_object );
        // ok, its just a container
				return new koala_container( $steam_object );
			default:
				// forum:
				if ( $steam_object instanceof steam_messageboard )
					return new lms_forum( $steam_object );
				// weblog:
				if ( $obj_type === "calendar_weblog_koala" )
					return new lms_weblog( $steam_object );
				// wiki:
				if ( $obj_type === "container_wiki_koala" || $obj_type === "room_wiki_koala" || $obj_type === "KOALA_WIKI" )
					return new lms_wiki( $steam_object );
				
				return new koala_object( $steam_object );
		}
	}

	public function get_steam_object() {
		return $this->steam_object;
	}

	/**
	 * Convenience function, calls get_id() on the steam_object.
	 *
	 * @return Int the steam_object's object id or 0 if no valid steam_object
	 */
	public function get_id() {
		if ( is_object( $this->steam_object ) && $this->steam_object instanceof steam_object )
			return $this->steam_object->get_id();
		else return 0;
	}

	/**
	 * Convenience function, calls get_name() on the steam_object.
	 *
	 * @return String name of the object
	 */
	public function get_name() {
		return $this->steam_object->get_name();
	}

	/**
	 * Convenience function, calls set_name($name) on the steam_object.
	 *
	 * @param String $name name for the object
	 * @return String name of the object
	 */
	public function set_name( $name ) {
		return $this->steam_object->set_name( $name );
	}

	/**
	 * Returns the name of the object that should be displayed as the name of
	 * this object in koaLA. This is the steam_object's name by default, but
	 * derived classes may choose to return the steam_object's description or
	 * a localized text instead.
	 *
	 * @see get_link
	 * 
	 * @return String the koaLA display name of the object
	 */
	public function get_display_name() {
		return h( $this->steam_object->get_name() );
	}

	/**
	 * Return the url under which this object can be displayed, or FALSE if
	 * it cannot be displayed through a URL.
	 *
	 * @see get_link
	 * 
	 * @return the url to this object, or FALSE
	 */
	public function get_url ()
	{
		return FALSE;
	}

	/**
	 * Return a link array ("name" => display name, "link" => object url).
	 * The "link" index is optional and will not exist if the object cannot
	 * be displayed through a URL.
	 * 
	 * @see get_display_name
	 * @see get_url
	 *
	 * @return array a link array("name", "link") (link is optional)
	 */
	public function get_link ()
	{
		$link = array( "name" => $this->get_display_name() );
		$url = $this->get_url();
		if ( $url ) $link[ "link" ] = $url;
		return $link;
	}

	protected function get_link_path_internal( $top_object )
	{
		$parent = $this->steam_object->get_environment();
		if ( !is_object( $parent ) ) {
			$parent = $this->steam_object->get_creator();
			$koala_parent = koala_object::get_koala_object( $parent );
			$link_path = array();
			$link_path[-1] = $koala_parent->get_link_path( $top_object );
		}
		else {
			if ( $parent instanceof steam_user )
				$koala_parent = new koala_container_clipboard( $parent );
			else
				$koala_parent = koala_object::get_koala_object( $parent );
			$link_path = $koala_parent->get_link_path( $top_object );
		}
		$link = $this->get_link();
		$link[ "koala_obj" ] = $this;
		$link[ "obj" ] = $this->steam_object;
		$link_path[] = $link;
		return $link_path;
	}

	public final function get_link_path( $top_object = FALSE, $offset = FALSE, $length = FALSE )
	{
		if ( !is_object( $top_object ) )
			$top_object = $this;
		$link_path = $this->get_link_path_internal( $top_object );
		if ( $offset === FALSE )
			return $link_path;
		else {
			$index = array_search( $offset, array_keys( $link_path ) );
			if ( $index === FALSE )
				return array();
			if ( $length === FALSE )
				return array_slice( $link_path, $index );
			else
				return array_slice( $link_path, $index, $length );
		}
		return $link_path;
	}

	static public function get_link_path_html( $link_path )
	{
		$html_path = "";
		foreach ( $link_path as $path_item ) {
			if ( !empty( $html_path ) ) $html_path .= "&nbsp;/ ";
			if ( empty( $path_item[ "link" ] ) )
				$html_path .= $path_item[ "name" ];
			else
				$html_path .= "<a href='" . $path_item[ "link" ] . "'>" . $path_item[ "name" ] . "</a>";
		}
		return $html_path;
	}

	public function get_attribute( $attribute )
	{
		return $this->steam_object->get_attribute( $attribute );
	}

	public function get_attributes( $attributes )
	{
		return $this->steam_object->get_attributes( $attributes );
	}

	public function set_attribute( $attribute, $value, $buffer = 0 )
	{
		return $this->steam_object->set_attribute( $attribute, $value, $buffer );
	}

	public function set_attributes( $attributes )
	{
		return $this->steam_object->set_attributes( $attributes );
	}

	public function delete ()
	{
		lms_steam::delete( $this->steam_object );
	}

	/**
	 * Returns the extensions that are available for this koala_object. By
	 * default, this function only retuns those extensions that haven't been
	 * disabled for this object.
	 * 
	 * @param boolean $include_disabled FALSE: return only those extensions
	 *   that are available for this object and have not been disabled. TRUE:
	 *   return all extensions that are available for this object, including
	 *   those that have been disabled for it.
	 * @return array an array of the available extensions for this object
	 */
	public function get_extensions( $include_disabled = FALSE )
	{
    // TODO Code below triggers 24 !! Requests: Optimize this to cut down Server requests displaying courses
    //logging::log_requests("koala_object::get_extensions() 1");
		$extensions = array();
		$extension_manager = lms_steam::get_extensionmanager();
		foreach ( $extension_manager->get_installed_extensions() as $extension ) {
			if ( !$extension->can_extend( get_class( $this ) ) ) continue;
      //logging::log_requests("koala_object::get_extensions() 1,1");
			if ( ($include_disabled || $extension->is_enabled( $this ) ) && $extension->is_enabled() ) {
        //logging::log_requests("koala_object::get_extensions() 1,2");
				$extensions[] = $extension;
      }
		}
    //logging::log_requests("koala_object::get_extensions() 2");
		return $extensions;
	}

	/**
	 * Return the index of this object's access permissions scheme (see
	 * /etc/permissions.def.php).
	 *
	 * @see get_access_descriptions
	 * @see set_access
	 * 
	 * @param Boolean $pBuffer FALSE = send command now, TRUE = buffer command
	 * @return access permissions index (see etc/permissions.def.php)
	 */
	public function get_access_scheme( $pBuffer = FALSE )
	{
		return $this->steam_object->get_attribute( KOALA_ACCESS, $pBuffer );
	}

	/**
	 * Override this function to return an array of access schemes (see
	 * /etc/permissions.def.php).
	 * 
	 * //TODO: documentation
	 *
	 * @see get_access_scheme
	 * @see set_access
	 * 
	 * @param Object $group
	 * @return Array
	 */
	static public function get_access_descriptions( $group )
	{
		return array();
	}

	/**
	 * Set the access permission scheme for this object.
	 * 
	 * @see get_access_scheme
	 * @see get_access_descriptions
	 *
	 * @param Int $access_key access scheme index (see etc/permissions.def.php)
	 * @param Int $access_members the access mask (SANCTION_*) for "members" in the access scheme
	 * @param Int $access_all the access mask (SANCTION_*) for everyone in the access scheme
	 * @param Object $group_members steam_group for the "members" in the access
	 *   scheme, or a steam_user if you want to give the "members" permissions
	 *   for a user
	 * @param Object $group_staff steam_group for the "staff" in the access scheme 
	 * @param Object $group_admins steam_group for the "admins" in the access scheme
	 */
	public function set_access( $access_key = -1, $access_members = 0, $access_all = 0, $group_members = 0, $group_staff = 0, $group_admins = 0, $access_attribute_key = KOALA_ACCESS )
	{
    if ($access_key === PERMISSION_UNDEFINED) return TRUE;
		if ( !is_object( $group_members ) ) {
			throw new Exception( "group_members is no object", E_PARAMETER );
		}
		if ( $access_key < PERMISSION_UNDEFINED ) {
			throw new Exception( "access key must be greater than zero", E_PARAMETER );
		}
		$group_steam = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), STEAM_ALL_USER );
		$buffer = TRUE;
		// disable acquiring
		$this->steam_object->set_acquire( 0, $buffer );
		
		// reset access for groups sTeam and learners
		$this->steam_object->set_sanction($group_steam, 0, $buffer );
		$this->steam_object->set_sanction($group_members, 0, $buffer );
		
		// set given access to members and sTeam group
		$this->steam_object->set_sanction($group_steam, $access_all, $buffer );
		$this->steam_object->set_sanction($group_members, $access_members, $buffer );
		
		// Give staff and admins full access in any case
		if (is_object($group_staff)) {
			$this->steam_object->set_sanction( $group_staff, SANCTION_ALL, $buffer );
			$this->steam_object->sanction_meta( SANCTION_ALL, $group_staff, $buffer );
		}
		if (is_object($group_admins)) {
			$this->steam_object->set_sanction( $group_admins, SANCTION_ALL, $buffer );
			$this->steam_object->sanction_meta( SANCTION_ALL, $group_admins, $buffer );
		}
		
		// Store access setting in the steam_object
		$this->steam_object->set_attribute($access_attribute_key, $access_key, $buffer);
		// flush the buffer to set rights in open-sTeam
		$GLOBALS[ "STEAM" ]->buffer_flush();
	}
}
?>
