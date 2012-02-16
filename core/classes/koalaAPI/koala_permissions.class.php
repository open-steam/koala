<?php

interface koala_permissions {
  public function set_access( $access = -1, $group_members = 0, $group_admins = 0 );
}

?>
