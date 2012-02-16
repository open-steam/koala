<?php 
include_once( "../../etc/koala.conf.php" );

$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );

if ($_REQUEST["case"] == "arrange") {
	$oid = substr($_REQUEST["oid"], strpos($_REQUEST["oid"], "_") + 1,strlen($_REQUEST["oid"]) - 1);
	$newX = $_REQUEST["x"] - 20;
	$newY = $_REQUEST["y"] - 50;
	$object = steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $oid);
	$object->set_attribute("OBJ_POSITION_X", (float)$newX);
	$object->set_attribute("OBJ_POSITION_Y", (float)$newY);
	echo "true";
} else if ($_REQUEST["case"] == "load") {
	$oid = substr($_REQUEST["oid"], strpos($_REQUEST["oid"], "_") + 1,strlen($_REQUEST["oid"]) - 1);
	$whiteboardsupport = $GLOBALS['STEAM']->get_module("package:whiteboardsupport");
	if (is_object($whiteboardsupport)) {
	      $objects = $GLOBALS['STEAM']->predefined_command( $whiteboardsupport, "query_inventory_data", steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $oid), false);
	} else {
		echo "package:whiteboardsupport nicht installiert";
	}
	$attributes =  $objects["attributes"];
	echo json_encode($attributes);
}

?>