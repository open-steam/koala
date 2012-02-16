<?php
	require_once( "../../etc/koala.conf.php" );
	echo "**************** reset sync script ****************" . $newline;
	try {
		echo "check root access to server";
		$steam_user = new lms_user( STEAM_ROOT_LOGIN, STEAM_ROOT_PW); //TODO: use phpsteam here. this fails if wrong login data for root
		$steam_user->login();
		echo "\t\t\t\t\t\t\t\t\t[OK]" . $newline;
		echo "reset lock flag";
		$paulsync_folder = steam_factory::path_to_object($GLOBALS["STEAM"]->get_id(), "/home/root/documents/paulsync");
		if (is_object($paulsync_folder)) {
			$paulsync_folder->set_attribute("PAUL_SYNC_RUNNING", "FALSE");
			echo "\t\t\t\t\t\t\t\t\t\t\t[OK]" . $newline;
		} else {
			echo "\t\t\t\t\t\t[FAIL]" . $newline;
			echo "--> ERROR: is server should not be synced with paul" . $newline;
		}
	} catch (Exception $e) {
		echo "\t\t\t\t\t\t[FAIL]" . $newline;
		echo "--> ERROR: failed to connect to steam:" . $ex->getMessage() . $newline;
	}
	exit;
?>