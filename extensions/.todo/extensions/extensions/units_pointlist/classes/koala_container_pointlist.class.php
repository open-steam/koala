<?php

class koala_container_pointlist extends koala_container
{
	protected $unit;

	public function __construct( $steam_object, $unit )
	{
		parent::__construct( $steam_object );
		$this->unit = $unit;
	}

	public function get_unit()
	{
		return $this->unit;
	}

	public function get_url()
	{
		return koala_object::get_koala_object( lms_steam::get_root_creator( $this->steam_object ) )->get_url() . "units/" . $this->get_id() . "/";
	}

	public function get_context_menu( $context, $params = array() )
	{
		if ( $context !== 'units' ) return array();
		$user = lms_steam::get_current_user();
		$menu = array();
		if ( $this->steam_object->check_access_write( $user ) ) {
      if (isset($_GET["show"]) && $_GET["show"] == "all") {
        $menu[] = array( "name" => gettext( "Show one" ), "link" => $this->get_link_base() );
      }
      else {
        $menu[] = array( "name" => gettext( "Show all" ), "link" => $this->get_link_base() . "?show=all" );      
      }
      $menu[] = array( "name" => gettext( "Export (Excel)" ), "link" => $this->get_link_base() . "export_excel" );      
			$menu[] = array( "name" => gettext( "Edit sheets" ), "link" => $this->get_link_base() . "sheets_edit" );      
			$menu[] = array( "name" => gettext( "Preferences" ), "link" => $this->get_link_base() . "edit" );
			$menu[] = array( "name" => gettext( "Delete unit" ), "link" => $this->get_link_base() . "delete" );
		}
		return $menu;
	}

	protected function get_link_path_internal( $top_object )
	{
		$koala_creator = koala_object::get_koala_object( lms_steam::get_root_creator( $this->steam_object ) );
		$link_path = $koala_creator->get_link_path( $top_object );
		$link_path[] = array( "name" => $this->get_display_name(), "link" => $koala_creator->get_url() . "units/" . $this->get_id() . "/", "koala_obj" => $this, "obj" => $this->steam_object );
		return $link_path;
	}

  // Override Delete function to clean pointlist-data, e.g. the proxy and
  // dont use the trashbin 
  public function delete() {
    $proxy = $this->steam_object->get_attribute("UNIT_POINTLIST_PROXY");
    if (is_object($proxy)) $proxy->delete();
    $tg = $this->steam_object->get_attribute("UNIT_POINTLIST_TUTORIALGROUP");
    if (is_object($tg)) $tg->set_attribute("UNIT_POINTLIST", 0);
 		$this->steam_object->delete();
  }
}

?>
