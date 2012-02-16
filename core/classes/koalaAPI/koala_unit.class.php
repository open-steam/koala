<?php
require_once(PATH_ETC . "permissions.def.php");

/**
 * Abstract class for representation of a koala unit
 */
abstract class koala_unit extends koala_extension {
  
  private $action_permissions;
  
   
  function __construct( $description_file ) {
    parent::__construct( $description_file );
    $this->set_action_permissions( PERMISSION_ACTION_CUT | PERMISSION_ACTION_COPY | PERMISSION_ACTION_EDIT | PERMISSION_ACTION_DELETE);
  }
  
  protected function set_action_permissions( $perm ) {
    $this->action_permissions = $perm;
  }  
  
  public function get_action_permissions() {
    return $this->action_permissions;
  }
}
?>
