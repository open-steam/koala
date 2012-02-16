<?php

class unitmanager {

  private $installed_unittypes;
  private $containing_course;
  private $em;
  
  /**
   *  Use Factory Method to allow to use function caching later
   */
  static function create_unitmanager( $course ) {
    return new unitmanager($course);
  }
  
  public function __construct( $course = FALSE ) {
    
  	$this->containing_course = $course;
 	$this->em = lms_steam::get_extensionmanager();
  	$unittypes = $this->em->get_extensions_by_obj_type(NULL, "unit_koala");
  	$this->installed_unittypes = array();
  	foreach ( $unittypes as $unit ) {
  		if ( ! $unit->is_enabled() ) continue;
  		$this->installed_unittypes[] = $unit;
  	}
  }

  /**
   *  Use Factory Method to allow to use function caching later
   */
  function create_unit( $unit_object ) {
    
  }
  
  
  function get_installed_unittypes() {
    return $this->installed_unittypes;
  }
  
  public function get_unit_objects()
  {
	$workroom = new koala_container( $this->containing_course->get_workroom() );
	$workroom->set_types_visible( CLASS_ROOM | CLASS_CONTAINER | CLASS_DOCEXTERN );
	$workroom->set_obj_types_invisible( array( "container_wiki_koala", "room_wiki_koala", "KOALA_WIKI" ) );
	return $workroom->get_inventory();
  }
        
	public function get_units() {
	  $units = $this->get_unit_objects();
	  //print_r($units);
	  $um = new unitmanager($this);
	  $cu = array();
	  foreach ($units as $unitobject) {
	    $nu = $um->create_unit( $unitobject );
	    if (is_object($nu)) $cu[] = $nu;
	  }
	  return $cu;
	}
	public function get_unittype($unitname)
	{
		foreach ($this->installed_unittypes as $unit)
		{
			if($unit->get_name() == $unitname)
				return $unit;
		}
		return NULL;
	}
}
?>
