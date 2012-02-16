<?php
class module_groups extends steam_object {
  
  private $steam_object;
  
  public function __construct( $steam_object ) {
    parent::__construct( $steam_object->get_steam_connector(), $steam_object->get_id(), CLASS_MODULE );
    $this->steam_object = $steam_object;
  }
  
	public function get_top_groups( $pBuffer = FALSE )
	{
		return $this->steam_object->get_steam_connector()->predefined_command(
			$this->steam_object,
			"get_top_groups",
			array( ),
			$pBuffer
		);
	}
}
?>
