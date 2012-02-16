<?php
class koala_object_elearning extends koala_object
{
	protected $unit;

	public function __construct ( $steam_object, $unit )
	{
		parent::__construct( $steam_object );
		$this->unit = $unit;
	}
	
	//TODO: why not in superclass?
	public function get_unit ()
	{
		return $this->unit;
	}

	//TODO: why not in superclass?
	public function get_url ()
	{
		return koala_object::get_koala_object( lms_steam::get_root_creator( $this->steam_object ) )->get_url() . "units/" . $this->get_id() . "/";
	}
	//TODO: why not in superclass?
	protected function get_link_path_internal ( $top_object )
	{
		$koala_creator = koala_object::get_koala_object( lms_steam::get_root_creator( $this->steam_object ) );
		$link_path = $koala_creator->get_link_path( $top_object );
		$link_path[] = array( "name" => $this->get_display_name(), "link" => $koala_creator->get_url() . "units/" . $this->get_id() . "/", "koala_obj" => $this, "obj" => $this->steam_object );
		return $link_path;
	}

	public function get_context_menu ( $context, $params = array() )
	{
/*		if ( $context !== 'units' ) return array();
		$user = lms_steam::get_current_user();
		$menu = array();
		if ( $this->steam_object->check_access_write( $user ) ) {
			$menu[] = array( "name" => gettext( "Preferences" ), "link" => $this->get_url() . "edit" );
			$menu[] = array( "name" => gettext( "Delete unit" ), "link" => $this->get_url() . "delete" );
		}
		return $menu;*/
	}

}
?>