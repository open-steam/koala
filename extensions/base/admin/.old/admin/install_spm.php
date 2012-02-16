<?php

include_once( "../../etc/koala.conf.php" );
ini_set('memory_limit', '2024M');
ini_set('max_execution_time', '300');
$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );
$user = lms_steam::get_current_user();
if( !lms_steam::is_koala_admin($user) )
{
	header("location:/");
	exit;
}
$STEAM = $GLOBALS["STEAM"];

echo "<h1>Installing SPM</h1>";
$package_container = steam_factory::get_object_by_name( $STEAM->get_id(), "/packages" );
if ( !is_object( $package_container ) ) {
	echo "Could not find /packages on your open-sTeam server.<br>";
	die;
}

echo "Reading file <br>";
$myFile = PATH_TEMP . "elearning_stahl_verkauf-1_40.spm";
$fh = fopen($myFile, 'r');
$theData = fread($fh, filesize($myFile));
fclose($fh);

echo "Uploading File <br>";
$package = steam_factory::create_document( $STEAM->get_id(), "elearning_stahl_verkauf-1_40.spm", $theData, "application/download", $package_container );

echo "Installiere Package<br>";
echo $STEAM->install_package( $package );


?>