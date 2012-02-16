<?php

class koala_container_units extends koala_container
{
	protected $owner;

	public function __construct ( $steam_object, $link_base = FALSE ) {
		parent::__construct( $steam_object, $link_base );
		$this->set_types_invisible( CLASS_USER|CLASS_CALENDAR|CLASS_MESSAGEBOARD );
		$this->set_obj_types_invisible( array( "container_wiki_koala", "KOALA_WIKI" ) );
	}

	public function set_owner ( $owner )
	{
		$this->owner = $owner;
	}

	function get_display_name ()
	{
		return gettext( 'Units' );
	}

	function get_url ()
	{
		if ( !is_object( $this->owner ) )
			return $this->get_link_base();
		return $this->owner->get_url() . 'units/';
	}

	protected function get_link_path_internal ( $top_object )
	{
		if ( !is_object( $owner ) )
			return array( get_link() );
		$link_path = $owner->get_link_path();
		$link_path[] = get_link();
		return $link_path;
	}

	public function accepts_object ( $koala_object )
	{
		$obj_type = $koala_object->get_attribute( OBJ_TYPE );
		return strrpos( $obj_type, 'unit_koala' ) == strlen( $obj_type) - 10;
	}

	public function get_inventory ( $pOffset = 0, $pLength = 0, $pBuffer = FALSE )
	{
		$filters = array();
		$filters[] = array( '-', 'class', CLASS_USER|CLASS_CALENDAR|CLASS_MESSAGEBOARD );
		$filters[] = array( '-', '!access', SANCTION_READ );
		$filters[] = array( '+', 'attribute', OBJ_TYPE, 'suffix', 'unit_koala' );
		$filters[] = array( '+', 'attribute', OBJ_TYPE, 'prefix', 'container_pyramiddiscussion' );
		$filters[] = array( '+', 'attribute', OBJ_TYPE, 'prefix', 'room_pyramiddiscussion' );
		$filters[] = array( '-', 'attribute', OBJ_TYPE, 'prefix', 'container_wiki' );
		$filters[] = array( '-', 'attribute', OBJ_TYPE, '==', 'KOALA_WIKI' );
		$filters[] = array( '+', 'class', CLASS_CONTAINER );
		$sort = array();
		// you can insert sort options here
		return $this->steam_object->get_inventory_filtered( $filters, $sort, $pOffset, $pLength, $pBuffer );
	}

	public function get_context_menu ( $context, $params = array() )
	{
		if ( $context !== 'units' ) return array();
		$user = lms_steam::get_current_user();
		$menu = array();
		if ( $this->steam_object->check_access_insert( $user ) ) {
			$menu[] = array( 'name' => gettext( 'Create new unit' ), 'link' => $this->get_url() . 'new' );
		}
		return $menu;
	}

}

?>
