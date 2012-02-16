<?php
include_once( "../../etc/koala.conf.php" );
$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );

$object = steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), "/home/root/");
if (!is_object($object)) {
	die("Kein Object gefunden.");
}

$clientsupport = $GLOBALS['STEAM']->get_module("package:clientsupport");
if (is_object($clientsupport)) {
      $result = $GLOBALS['STEAM']->predefined_command( $clientsupport, "query_object_data", array($object, 1, 1), false);
} else {
	die("package:clientsupport nicht installiert");
}

echo "<h1>clientsupport PHP test</h1>";
echo "<pre>";
print_r($result);

// JSON encoded result
print_r(json_encode($result));
?>