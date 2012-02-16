<?php
include_once( "../../etc/koala.conf.php" );

$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );

if (isset($_REQUEST["node"])) {
	$objects = steam_factory::path_to_object($GLOBALS['STEAM']->get_id(), $_REQUEST["node"])->get_inventory();
} else {
	$objects = $GLOBALS['STEAM']->get_current_steam_user()->get_workroom()->get_inventory();
}

$result = array();



foreach($objects as $object) {
	if ($object instanceof steam_container) {
		$leaf = false;
	} else {
		$leaf = true;
	}
	$result[] = array("text" => $object->get_name(), "leaf" => $leaf, "id" => $object->get_path());
}

echo json_encode($result);

?>