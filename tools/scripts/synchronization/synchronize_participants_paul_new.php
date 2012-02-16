#!/usr/bin/php
<?php
require_once( "../../etc/koala.conf.php" );
require_once( PATH_CLASSES . "lms_ldap.class.php" );
require_once( PATH_LIB . "cache_handling.inc.php" );
require_once( "Cache/Lite/Function.php" );
ini_set('memory_limit', '1024M');
$newline = "\n";
$paul_sync_time = time();
$force = FALSE;
if (!defined("PAUL_SYNC_KOALA_SEMESTER")) {
	echo "PAUL_SYNC_KOALA_SEMESTER not set";
	exit;
}
if (!defined("PAUL_SYNC_TABLE_NAME")) {
	define("PAUL_SYNC_TABLE_NAME", "transaktion_" . PAUL_SYNC_KOALA_SEMESTER);
}
if (!defined("PAUL_SYNC_TABLE_MAIL_NAME")) {
	define("PAUL_SYNC_TABLE_MAIL_NAME", "wakeupmail_" . PAUL_SYNC_KOALA_SEMESTER);
}
/*
 * PHP CLI handling
 */
if (count($argv) > 1 && in_array($argv[1], array('--help', '-help', '-h', '-?'))) {
	?>

This is an sync script Paul -> koaLA usage:
	<?php echo $argv[0]; ?> <option>

		<option>using option --help, -help, -h oder -? showes this infos. 

		-reset resets broken sync process (be careful, no other sync process should be
       		   running!)
        
        -force run sync even if soap error happen. 
        
        -create_tables create new mysql table (transaktion_"CurrentSemester" 
        			   and wakupmail_"CurrentSemester" or PAUL_SYNC_TABLE_NAME/
        			   PAUL_SYNC_TABLE_MAIL_NAME if set.)
        			   Atention: old tables with same name will be deleted
<?php
exit;
} else if (count($argv) > 1 && in_array($argv[1], array('-reset'))) {
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
} else if (count($argv) > 1 && in_array($argv[1], array('-force'))) {
	$force = TRUE;
} else if (count($argv) > 1 && in_array($argv[1], array('-create_table'))) {
	
//DROP TABLE IF EXISTS `transaktionen`;
//CREATE TABLE `transaktionen` (
//  `id` int(11) NOT NULL auto_increment,
//  `course_id_paul` varchar(30) NOT NULL,
//  `course_id_koala` varchar(120) NOT NULL,
//  `student_id` varchar(20) NOT NULL,
//  `student_login` varchar(120) NOT NULL,
//  `status_paul` int(30) NOT NULL,
//  `status_koala` varchar(30) NOT NULL,
//  PRIMARY KEY  (`id`)
//) ENGINE=MyISAM AUTO_INCREMENT=16123 DEFAULT CHARSET=latin1;
}
?><?php

$GLOBALS["PAUL_SYNC_LOG_KOALA"] = " ";

echo $newline . $newline;
$readonly = FALSE;
if (defined("PAUL_SYNC_READONLY") && PAUL_SYNC_READONLY == TRUE) {
	$readonly = TRUE;
	echo "###running in READONLY mode --> HAVE FUN!###" . $newline;
}

/*
 * helper methodes for logging
 */
function paul_sync_log( $message, $level = -1, $append = FALSE ) {
	if ( defined("PAUL_SYNC_LOGLEVEL") && $level <= PAUL_SYNC_LOGLEVEL) {
		if ($append) {
			logging::append_log( LOG_PAULSYNC, $message );
			try {
				if (defined("LOG_PAULSYNC_LAST")) logging::append_log( LOG_PAULSYNC_LAST, $message );
			} catch(Exception $ex) { /*ignore*/ }
		} else {
			logging::write_log( LOG_PAULSYNC, $message );
			try {
				if (defined("LOG_PAULSYNC_LAST")) logging::write_log( LOG_PAULSYNC_LAST, $message );
			} catch(Exception $ex) { /*ignore*/ }
		}
	}
}

function timelog( $timerid = "1", $start = FALSE ) {
	if ($start) {
		$GLOBALS["paul_sync_timelog_starttime_" . $timerid] = microtime(TRUE);
		return $GLOBALS["paul_sync_timelog_starttime_" . $timerid];
	}
	else {
		return (round((microtime(TRUE) -  $GLOBALS["paul_sync_timelog_starttime_" . $timerid]) * 1000 ) . " ms");
	}
}

/*
 * start sync process here
 */

echo "**************** starting paul sychronization ****************" . $newline;
echo "loaded libraries and init\t\t\t\t\t\t\t\t\t[OK]" . $newline;
echo "creating log file " . LOG_PAULSYNC_LAST;
if ( file_exists( LOG_PAULSYNC_LAST) ) {
	$fp = fopen( LOG_PAULSYNC_LAST, "w");
	if ($fp) {
		ftruncate($fp, 0);
		fclose($fp);
	}
}
echo "\t[OK]" . $newline;

if (!defined("LOG_PAULSYNC")) define( "LOG_PAULSYNC", LOG_MESSAGES);
echo "logging to " . LOG_PAULSYNC . "\t\t\t[OK]" . $newline;
paul_sync_log( "PAUL_SYNC\t=== START ===" );
timelog("runtime_script", TRUE);

/*
 * check if server is ready to sync
 */

echo "************ getting current semester and courses ************" . $newline;
echo "check root access to server";
try {
	$steam_user = new lms_user( STEAM_ROOT_LOGIN, STEAM_ROOT_PW); //TODO: use phpsteam here. this fails if wrong login data for root
	$steam_user->login();
	$user_module = $GLOBALS[ "STEAM" ]->get_module( "users" );
	echo "\t\t\t\t\t\t\t\t\t[OK]" . $newline;	
} catch (Exception $ex) {
	error_log("error connecting to steam:" . $ex->getMessage());
	paul_sync_log("PAUL_SYNC\tERROR\Error connecting to steam:" . $ex->getMessage(), PAUL_SYNC_LOGLEVEL_ERROR );
	echo "\t\t\t\t\t\t[FAIL]" . $newline;
	echo "--> ERROR: failed to connect to steam:" . $ex->getMessage() . $newline;
	exit;
}

echo "checking if another sync process is already running";
$abort = FALSE;
// to enable PAUL sync you must create a steam container in root's workroom: /home/root/documents/paulsync
// this is the serverside config folder for paul sync. if it doesn't exit the script will stop here.
$paulsync_folder = steam_factory::path_to_object($GLOBALS["STEAM"]->get_id(), "/home/root/documents/paulsync");
if (is_object($paulsync_folder)) {
	$lock = $paulsync_folder->get_attribute("PAUL_SYNC_RUNNING");
	if ($lock !== "TRUE") {
		echo "\t\t\t\t\t\t[OK]" . $newline;
		echo "lock server for other sync processes";
		$paulsync_folder->set_attribute("PAUL_SYNC_STARTTIME", time());
		$paulsync_folder->set_attribute("PAUL_SYNC_RUNNING", "TRUE");
		echo "\t\t\t\t\t\t\t\t[OK]" . $newline;
	} else {
		paul_sync_log("PAUL_SYNC\tERROR\tScript already running, aborting...", PAUL_SYNC_LOGLEVEL_ERROR );
		$abort = TRUE;
		echo "\t\t\t\t\t\t[FAIL]" . $newline;
		echo "--> ERROR: another sync script is already running or was terminated abnormaly" . $newline;
	}
} else {
	paul_sync_log("PAUL_SYNC\tERROR\t/home/root/documents/paulsync/ not found cannot determine script status, exiting...", PAUL_SYNC_LOGLEVEL_ERROR );
	echo "\t\t\t\t\t\t[FAIL]" . $newline;
	echo "--> ERROR: is server should not be synced with paul" . $newline;
	$abort = TRUE;
}
if ($abort) {
	if (defined("PAUL_SYNC_ADMIN_LOGIN")) {
		$admin = steam_factory::username_to_object($GLOBALS["STEAM"]->get_id(), PAUL_SYNC_ADMIN_LOGIN);
		if (is_object($admin))$admin->mail( "Error synchronizing with PAUL", "It seems that the synchronizing is still running. Please check." );
	}
	$GLOBALS["STEAM"]->disconnect();
	exit;
}

/*
 * get current semester and courses
 */
echo "load current semester and couses";
$current_semester = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), PAUL_SYNC_KOALA_SEMESTER, 0 );
if ($current_semester != null && $current_semester instanceof  steam_group) {
	$courses_koala  = lms_steam::semester_get_courses( $current_semester->get_id() );
	echo "\t\t\t\t\t\t\t\t[OK]" . $newline;
} else {
	echo "\t\t\t\t\t\t\t\t[FAIL]" . $newline;
	echo "--> ERROR: PAUL_SYNC_KOALA_SEMESTER not set correctly" . $newline;
	//reset sync
	$paulsync_folder->set_attribute("PAUL_SYNC_RUNNING", "FALSE");
	exit;
}
echo "current koaLA semester for sync is " . $current_semester->get_name() . $newline;
echo "overall there are " . count($courses_koala) . " courses" . $newline;


$paul_course_ids = array();
foreach( $courses_koala as $course )
{
	// Falls Kurs aus PAUL importiert wurde und der Teilnahmedatenabgleich erfolgen soll
	if ( koala_group_course::is_paul_course($course["COURSE_NUMBER"]) && $course[KOALA_GROUP_ACCESS] == PERMISSION_COURSE_PAUL_SYNC ) {
		$paul_course_ids[ $course[ OBJ_NAME ] ] = PAUL_SYNC_KOALA_SEMESTER . "." . $course[ OBJ_NAME ];
	}
}
echo "there are " . count($paul_course_ids) . " courses to be synced with paul" . $newline;

/*
 * init database connection
 */
echo "setting up sync database";
if (!defined("PAUL_SYNC_DB_SERVER") || PAUL_SYNC_DB_SERVER == "") {
	echo "\t\t\t\t\t\t\t\t\t[FAIL]" . $newline;
	echo "--> ERROR: PAUL_SYNC_DB_SERVER not set" . $newline;
	//reset sync
	$paulsync_folder->set_attribute("PAUL_SYNC_RUNNING", "FALSE");
	exit;
}
if (!defined("PAUL_SYNC_DB_USER") || PAUL_SYNC_DB_USER == "") {
	echo "\t\t\t\t\t\t\t\t\t[FAIL]" . $newline;
	echo "--> ERROR: PAUL_SYNC_DB_USER not set" . $newline;
	//reset sync
	$paulsync_folder->set_attribute("PAUL_SYNC_RUNNING", "FALSE");
	exit;
}
if (!defined("PAUL_SYNC_DB_PW")) {
	echo "\t\t\t\t\t\t\t\t\t[FAIL]" . $newline;
	echo "--> ERROR: PAUL_SYNC_DB_PW not set" . $newline;
	//reset sync
	$paulsync_folder->set_attribute("PAUL_SYNC_RUNNING", "FALSE");
	exit;
}
if (!defined("PAUL_SYNC_DB_NAME") || PAUL_SYNC_DB_NAME == "") {
	echo "\t\t\t\t\t\t\t\t\t[FAIL]" . $newline;
	echo "--> ERROR: PAUL_SYNC_DB_NAME not set" . $newline;
	//reset sync
	$paulsync_folder->set_attribute("PAUL_SYNC_RUNNING", "FALSE");
	exit;
}
if (!defined("PAUL_SYNC_TABLE_NAME") || PAUL_SYNC_TABLE_NAME == "") {
	echo "\t\t\t\t\t\t\t\t\t[FAIL]" . $newline;
	echo "--> ERROR: PAUL_SYNC_TABLE_NAME not set" . $newline;
	//reset sync
	$paulsync_folder->set_attribute("PAUL_SYNC_RUNNING", "FALSE");
	exit;
}
echo "\t\t\t\t\t\t\t\t\t[OK]" . $newline;
echo "PAUL_SYNC_DB_SERVER:\t\t" . PAUL_SYNC_DB_SERVER . $newline;
echo "PAUL_SYNC_DB_USER:\t\t" . PAUL_SYNC_DB_USER . $newline;
echo "PAUL_SYNC_DB_PW:\t\t" . "****" . $newline;
echo "PAUL_SYNC_DB_NAME:\t\t" . PAUL_SYNC_DB_NAME . $newline;
echo "PAUL_SYNC_TABLE_NAME:\t\t" . PAUL_SYNC_TABLE_NAME . $newline;

echo "try to connect to DB and select table";
try {
	$db = mysql_connect( PAUL_SYNC_DB_SERVER, PAUL_SYNC_DB_USER, PAUL_SYNC_DB_PW );
} catch (Exception $ex) {
	paul_sync_log( "PAUL_SYNC\tKeine Verbindung zur Datenbank", PAUL_SYNC_LOGLEVEL_ERROR );
	echo "\t\t\t\t\t\t\t\t[FAIL]" . $newline;
	echo "--> ERROR: error while connecting" . $newline;
	//reset sync
	$paulsync_folder->set_attribute("PAUL_SYNC_RUNNING", "FALSE");
	exit;
}
if (!$db) {
	paul_sync_log( "PAUL_SYNC\tKeine Verbindung zur Datenbank", PAUL_SYNC_LOGLEVEL_ERROR );
	echo "\t\t\t\t\t\t\t\t[FAIL]" . $newline;
	echo "--> ERROR: error while connecting (DB is NULL)" . $newline;
	//reset sync
	$paulsync_folder->set_attribute("PAUL_SYNC_RUNNING", "FALSE");
	exit;
}
if ( ! mysql_select_db( PAUL_SYNC_DB_NAME , $db ) ) {
	paul_sync_log( "PAUL_SYNC\tAuswahl der Datenbank schlug fehl", PAUL_SYNC_LOGLEVEL_ERROR );
	echo "\t\t\t\t\t\t\t\t[FAIL]" . $newline;
	echo "--> ERROR: mysql select failed for PAUL_SYNC_DB_NAME" . $newline;
	//reset sync
	$paulsync_folder->set_attribute("PAUL_SYNC_RUNNING", "FALSE");
	exit;
}
echo "\t\t\t\t\t\t\t\t[OK]" . $newline;

/*
 * start real sync process
 */

// BELEGUNGEN IM PAUL SYSTEM ABFRAGEN, MIT DEN BEREITS DURCHGEFÜHRTEN TRANSAKTIONEN VERGLEICHEN,
// UND DIE NEUEN BELEGUNGEN ZUR DURCHFÜHRUNG MARKIEREN.

$paul_soap = new paul_soap();
// memory caches for ldap (mnr=>login), steam users (login=>steam_user), steam groups (groupname=>steam_group), koala_courses (groupname => koala_group_course)
$cache_steam_users = array();
$cache_steam_groups = array();
$cache_ldap_logins = array();
$cache_koala_courses = array();
// to clean caches
$cleancache_users = array();
$cleancache_groups = array();
// for statistics
$info_courses = 0;
$new_bookings = array();
$paul_meta_data_all = array();
$bookings_for_deletion = array();
$info_sum_part_paul = 0;
$info_sum_part_koala = 0;
$info_sum_part_db = 0;
$info_sum_bookings = 0;
$info_sum_part_todelete = 0;
$info_sum_part_floating = 0;
$info_sum_soap_error = 0;
$info_ldap_requests = 0;
$info_paul_requests = 0;
$info_mysql_requests = 0;
// To build notification E-Mails separately
// array( student_login => array( "added" =>array( "link"=>link, "name" => course name ), "deleted" =>array( "link"=>link, "name" => course name )  )  )
$notifications = array();
timelog("runtime_schedule_sum", TRUE);

/*
 * get participants for each koala course from paul
 */

echo "init paul and pia connection";
if (!defined("PAUL_URL") || PAUL_URL == "") {
	echo "\t\t\t\t\t\t\t\t\t[FAIL]" . $newline;
	echo "--> ERROR: PAUL_URL not set" . $newline;
	//reset sync
	$paulsync_folder->set_attribute("PAUL_SYNC_RUNNING", "FALSE");
	exit;
}
if (!defined("PIA_URL") || PIA_URL == "") {
	echo "\t\t\t\t\t\t\t\t\t[FAIL]" . $newline;
	echo "--> ERROR: PIA_URL not set" . $newline;
	//reset sync
	$paulsync_folder->set_attribute("PAUL_SYNC_RUNNING", "FALSE");
	exit;
}
if (!defined("PAUL_SOAP_PASSWORD") || PAUL_SOAP_PASSWORD == "") {
	echo "\t\t\t\t\t\t\t\t\t[FAIL]" . $newline;
	echo "--> ERROR: PAUL_SOAP_PASSWORD not set" . $newline;
	//reset sync
	$paulsync_folder->set_attribute("PAUL_SYNC_RUNNING", "FALSE");
	exit;
}
if (!defined("SEMESTER_ID") || SEMESTER_ID == "") {
	echo "\t\t\t\t\t\t\t\t\t[FAIL]" . $newline;
	echo "--> ERROR: SEMESTER_ID not set" . $newline;
	//reset sync
	$paulsync_folder->set_attribute("PAUL_SYNC_RUNNING", "FALSE");
	exit;
}
if (!defined("PIA_SOAP_USER") || PIA_SOAP_USER == "") {
	echo "\t\t\t\t\t\t\t\t\t[FAIL]" . $newline;
	echo "--> ERROR: PIA_SOAP_USER not set" . $newline;
	//reset sync
	$paulsync_folder->set_attribute("PAUL_SYNC_RUNNING", "FALSE");
	exit;
}
if (!defined("PIA_SOAP_PASSWORD") || PIA_SOAP_PASSWORD == "") {
	echo "\t\t\t\t\t\t\t\t\t[FAIL]" . $newline;
	echo "--> ERROR: PIA_SOAP_PASSWORD not set" . $newline;
	//reset sync
	$paulsync_folder->set_attribute("PAUL_SYNC_RUNNING", "FALSE");
	exit;
}
echo "\t\t\t\t\t\t\t\t\t[OK]" . $newline;

echo "get participants from paul".$newline;
/*
 * This loops over all courses to sync.
 * Collect course paul_meta_data here.
 */
while ( list( $paul_id, $steam_group_name ) = each( $paul_course_ids ) )
{
	timelog("runtime_schedule", TRUE);
	$info_part_paul = 0;
	$info_part_koala = 0;
	$info_part_db = 0;
	$info_part_todelete = 0;
	$info_bookings = 0;
	$info_part_floating = 0;
	$status_mapping = array();
	$soaperror = FALSE;
	timelog("runtime_get_participants", TRUE);
	$info_paul_requests++;
	try {
		paul_sync_log("PAUL_SYNC\tget_participants\t".$paul_id, PAUL_SYNC_LOGLEVEL_INFO);
		$participants_paul = $paul_soap->get_participants( (string) $paul_id );
		paul_sync_log(" runtime=". timelog("runtime_get_participants"), PAUL_SYNC_LOGLEVEL_INFO, TRUE); // Append runtime
	} catch (Exception $ex) {
		paul_sync_log("PAUL_SYNC\tERROR\tget_participants\t".$paul_id ." error: " . $ex, PAUL_SYNC_LOGLEVEL_ERROR);
		$soaperror = TRUE;
		$info_sum_soap_error++;
		//echo "\t\t\t\t\t\t\t\t\t[FAIL]" . $newline;
		//echo "--> ERROR: a soap error occured " . $ex . $newline;
		//reset sync
		//$paulsync_folder->set_attribute("PAUL_SYNC_RUNNING", "FALSE");
		//exit;
	}

	if ( !$soaperror ) {
		timelog("runtime_schedule_participants", TRUE);
		$info_courses++;
		$info_part_paul = count($participants_paul);
		// Durchgeführte Kursbuchungen ermitteln
		//$query = "SELECT * FROM " . PAUL_SYNC_TABLE_NAME . " WHERE course_id_paul = '" . $paul_id . "' AND status_paul = 1;";
		//now we take all participants
		$query = "SELECT * FROM " . PAUL_SYNC_TABLE_NAME . " WHERE course_id_paul = '" . $paul_id . "';";
		$info_mysql_requests++;
		paul_sync_log("PAUL_SYNC\tmysql query\t" . $query, PAUL_SYNC_LOGLEVEL_DEBUG);
		$result = mysql_query( $query );
		$old_bookings = array();
		while ( $transaction = mysql_fetch_object( $result ) ) {
			paul_sync_log("PAUL_SYNC\tfound old booking\t" . $transaction->student_id . "\tfor paul course\t" . $paul_id, PAUL_SYNC_LOGLEVEL_DEBUG);
			$old_bookings[ $transaction->student_id ] = $transaction;
		}
		$info_part_db = count($old_bookings);

		foreach( $participants_paul as $booking ) {
			paul_sync_log("PAUL_SYNC\tfound participant\t" . $booking["mnr"] . "\tfor paul course\t" . $paul_id, PAUL_SYNC_LOGLEVEL_DEBUG);
			if ( trim( $booking[ "status" ] ) != 1 ) {
				// Uns interessieren nur die mit dem Status 1
				// = zugelassene Teilnehmer
				//continue;
				// new since SS10: add floating participants also
				$info_part_floating++;
				paul_sync_log("PAUL_SYNC\tparticipant\t" . $booking["mnr"] . "\tis floading\t" . $paul_id, PAUL_SYNC_LOGLEVEL_DEBUG);
				if (trim( $booking[ "status" ] ) != 0) {
					paul_sync_log("PAUL_SYNC\tFound unknown status for\t" . $booking["mnr"] . "\tstatus:\t". $booking["status"]. "\tin course\t" . $paul_id, PAUL_SYNC_LOGLEVEL_WARNING);
				}
			}
			//save status mapping here
			if (!array_key_exists(trim($booking["status"]), $status_mapping)) {
				$status_mapping[trim($booking["status"])] = array($booking[ "mnr" ]);
			} else {
				$array = $status_mapping[trim($booking["status"])];
				$array[] = $booking[ "mnr" ];
				$status_mapping[trim($booking["status"])] = $array;
			}
			
			// BUCHUNG SCHONMAL GETAETIGT?
			if ( ! array_key_exists( $booking[ "mnr" ], $old_bookings ) ) {
				// NEUE BUCHUNGEN MERKEN
				$new_bookings[] = array(
                        "matrnr"     => $booking["mnr"],
                        "course_paul" => $paul_id,
                        "course_koala" => $steam_group_name,
                        "status_paul"     => $booking["status"]
				);
				$info_bookings++;
			} else {
				// Im Array $old_bookings sollen nur noch die
				// uebrig bleiben, denen kein Datensatz in
				// $new_bookings gegenueber steht und die
				// somit aus der Datenbank geloescht werden
				// muessen.
				unset( $old_bookings[ $booking[ "mnr" ] ] );
			}
		}
		// Alle uebrig gebliebenen sind nicht mehr im PAUL Kurs
		// angemeldet und wurden also zwischenzeitlich
		// aus dem PAUL Kurs geloescht.
		foreach( $old_bookings as $old_booking ) {
			paul_sync_log("PAUL_SYNC\tscheduled for deletion\t" . $old_booking->mnr . "\tfor paul course\t" . $paul_id, PAUL_SYNC_LOGLEVEL_DEBUG);
			$bookings_for_deletion[] = $old_booking;
			$info_part_todelete++;
		}
		
		//save paul meta data for this couse
		$paul_meta_data_all[$steam_group_name] = array ("count_paul" => $info_part_paul,
									  			        "count_status" => $status_mapping,
									  					"paul_sync_time" => $paul_sync_time);
	}
	
	
	$info_sum_part_paul += $info_part_paul;
	$info_sum_part_koala += $info_part_koala;
	$info_sum_part_db += $info_part_db;
	$info_sum_part_todelete += $info_part_todelete;
	$info_sum_bookings += $info_bookings;
	$info_sum_part_floating += $info_part_floating;
	paul_sync_log( "PAUL_SYNC\tscheduled actions for course" . $paul_id . ": paulcount=" . $info_part_paul . " dbcount=" . $info_part_db . " deletecount=" . $info_part_todelete . " new bookings=" . $info_bookings, PAUL_SYNC_LOGLEVEL_INFO);
	paul_sync_log(" runtime=". timelog("runtime_schedule_participants"), PAUL_SYNC_LOGLEVEL_INFO, TRUE); // Append runtime
	if ($info_paul_requests % 50 == 0) {
		echo $info_paul_requests . $newline;
	} else if ($soaperror) {
		echo "f";
	} else {
		echo ".";
	}
}
paul_sync_log( "PAUL_SYNC\tSUMMARY\tscheduled actions: for paulcount=" . $info_sum_part_paul . " dbcount=" . $info_sum_part_db . " deletecount=" . $info_sum_part_todelete . " new bookings=" . $info_sum_bookings, PAUL_SYNC_LOGLEVEL_INFO);
echo "\t\t\t\t\t\t\t\t\t[OK]" . $newline;
echo "statistics participiats: " . $newline;
echo "\t# courses to sync:\t\t" . $info_courses . $newline;
echo "\t# new bookings:\t\t\t" . $info_sum_bookings . $newline;
echo "\t# deleted bookings:\t\t" . $info_sum_part_todelete . $newline;
echo "\t# floating bookings:\t\t" . $info_sum_part_floating . $newline;
echo "\t# participiats in paul:\t\t" . $info_sum_part_paul . $newline;
echo "\t# participiats in koala:\t" . $info_sum_part_koala . $newline;
echo "\t# participiats in DB:\t\t" . $info_sum_part_db . $newline;
echo "statistics technical: " . $newline;
echo "\t# ldap requests:\t\t" . $info_ldap_requests . $newline;
echo "\t# paul requests:\t\t" . $info_paul_requests . $newline;
echo "\t# soap errors:\t\t\t" . $info_sum_soap_error . $newline;
echo "\t# mysql requests:\t\t" . $info_mysql_requests . $newline;

if (!$force) {
	if ($soaperror) {
		echo "soap error(s) happend. stopping here!" . $newline;
		//reset sync
		$paulsync_folder->set_attribute("PAUL_SYNC_RUNNING", "FALSE");
		exit;
	}
}

var_dump($paul_meta_data_all);

echo "bind ldap";
if (!defined("LDAP_SERVER") || LDAP_SERVER == "") {
	echo "\t\t\t\t\t\t\t\t\t\t\t[FAIL]" . $newline;
	echo "--> ERROR: LDAP_SERVER not set" . $newline;
	//reset sync
	$paulsync_folder->set_attribute("PAUL_SYNC_RUNNING", "FALSE");
	exit;
}
if (!defined("LDAP_PORT") || LDAP_PORT == "") {
	echo "\t\t\t\t\t\t\t\t\t\t\t[FAIL]" . $newline;
	echo "--> ERROR: LDAP_PORT not set" . $newline;
	//reset sync
	$paulsync_folder->set_attribute("PAUL_SYNC_RUNNING", "FALSE");
	exit;
}
if (!defined("LDAP_LOGIN") || LDAP_LOGIN == "") {
	echo "\t\t\t\t\t\t\t\t\t\t\t[FAIL]" . $newline;
	echo "--> ERROR: LDAP_LOGIN not set" . $newline;
	//reset sync
	$paulsync_folder->set_attribute("PAUL_SYNC_RUNNING", "FALSE");
	exit;
}
if (!defined("LDAP_PASSWORD") || LDAP_PASSWORD == "") {
	echo "\t\t\t\t\t\t\t\t\t\t\t[FAIL]" . $newline;
	echo "--> ERROR: LDAP_PASSWORD not set" . $newline;
	//reset sync
	$paulsync_folder->set_attribute("PAUL_SYNC_RUNNING", "FALSE");
	exit;
}
try {
	$lms_ldap = new lms_ldap();
	$lms_ldap->bind( LDAP_LOGIN, LDAP_PASSWORD );
}
catch ( Exception $e ) {
	paul_sync_log("PAUL_SYNC\t" . $e->getMessage(), PAUL_SYNC_LOGLEVEL_ERROR );
	echo "\t\t\t\t\t\t\t\t\t\t\t[FAIL]" . $newline;
	echo "--> ERROR: ldap connection failed" . $newline;
	//reset sync
	$paulsync_folder->set_attribute("PAUL_SYNC_RUNNING", "FALSE");
	exit;
}
echo "\t\t\t\t\t\t\t\t\t\t\t[OK]" . $newline;

/*
echo "stop here !" . $newline;
//reset sync
$paulsync_folder->set_attribute("PAUL_SYNC_RUNNING", "FALSE");
exit;*/
	
// Zaehler fuer erfolgreich durchgefuehrte Buchungen
$counter_new_bookings = 0;
$loop_run = 0;
$info_sum_part_neverloggedin = 0;
$info_sum_part_notinldap = 0;
$info_sum_part_wakeupmailssent = 0;
echo "do booking";
foreach( $new_bookings as $booking ) {
		
	paul_sync_log( "PAUL_SYNC\tgetting uid for mnr=" . $booking["matrnr"], PAUL_SYNC_LOGLEVEL_DEBUG);
	$info_ldap_requests++;

	if (isset($cache_ldap_logins[$booking[ "matrnr" ]])) {
		$booking[ "uid" ] = $cache_ldap_logins[$booking[ "matrnr" ]];
	} else {
		timelog("ldap_call", TRUE);
		paul_sync_log("PAUL_SYNC\tDEBUG getting mnr=" . $booking[ "matrnr" ] . " from ldap", PAUL_SYNC_LOGLEVEL_DEBUG );
		$booking[ "uid" ] = $lms_ldap->studentid2uid($booking[ "matrnr" ] );
		paul_sync_log(" runtime=" . timelog("ldap_call"), PAUL_SYNC_LOGLEVEL_DEBUG, TRUE);
		$cache_ldap_logins[$booking[ "matrnr" ]] = $booking[ "uid" ];
	}

	if ( empty( $booking[ "uid" ] ) ) {
		paul_sync_log("PAUL_SYNC\tWARNING " . $booking[ "matrnr" ] . " NOT FOUND IN LDAP", PAUL_SYNC_LOGLEVEL_WARNING );
		$loop_run++;
		$info_sum_part_notinldap++;
		if ($loop_run % 100 == 0) {
			echo $loop_run . $newline;
		} else {
			echo "l";
		}
		continue;
	}
	paul_sync_log( "PAUL_SYNC\tgetting user object for uid=" . $booking["uid"], PAUL_SYNC_LOGLEVEL_DEBUG);

	if (isset($cache_steam_users[$booking[ "uid" ]])) {
		$student = $cache_steam_users[$booking[ "uid" ]];
	}
	if (!is_object($student)) {
		timelog("lookup_login", TRUE);
		paul_sync_log( "PAUL_SYNC\tlookup_login uid=" . $booking["uid"], PAUL_SYNC_LOGLEVEL_DEBUG);
		$result = $GLOBALS[ "STEAM" ]->predefined_command($user_module,
      													  "lookup_login",
														  array( $booking[ "uid" ], TRUE ),
														  0
														  );
		paul_sync_log( " runtime=" . timelog("lookup_login"), PAUL_SYNC_LOGLEVEL_DEBUG, TRUE);
		if ( is_object( $result[ 0 ] ) ) {
			$student = $result[ 0 ];
			if ( is_object($student) ) $cache_steam_users[$booking[ "uid" ]] = $student;
		}
		else {
			paul_sync_log( "PAUL_SYNC\tWARNING\tcannot add member uid=" . $booking["uid"] . " to " . $booking["course_koala"] . " user not found in koaLA", PAUL_SYNC_LOGLEVEL_WARNING);
			// Benutzer hat sich noch nie in koaLA/sTeam eingeloggt.
		    $info_sum_part_neverloggedin++;
			/*
			 * check if wakeup-mail was already sent for this user+course (mysql)
			 */
		    $query = "SELECT * FROM " . PAUL_SYNC_TABLE_MAIL_NAME . " WHERE course_id_paul = '" . $booking[ "course_paul" ] . "' AND WHERE uid = '" . $booking[ "uid" ] . "';";
			$info_mysql_requests++;
			$result = mysql_query( $query );
		    if (!$result && defined("PAUL_SYNC_EMAIL_WAKEUP") && PAUL_SYNC_EMAIL_WAKEUP) {
		    	/*
		    	 * Send wakeup-mail
		    	 */
		    	//$user = steam_factory::username_to_object($GLOBALS["STEAM"]->get_id(), $booking["uid"]);
		    	//for testing
		    	$user = steam_factory::username_to_object($GLOBALS["STEAM"]->get_id(), PAUL_SYNC_ADMIN_LOGIN);
		    	$steam_group = $cache_steam_groups[$booking[ "course_koala" ]];
		    	$course = new koala_group_course( $steam_group );
				if (is_object($user))$user->mail( "[koaLA] Benachrichtigung über Kurse" , "Hallo " . $user->get_full_name() .",\n\n du belegst an der Universität Paderborn den Kurs " . $course->get_name() . ". Damit du auf Materialien zugreifen kannst und du in die Rundmail-List aufgenommen werden kannst, muss du dich einmal an der koaLA Plattform anmelden (koala.upb.de). Deine Mitgliedschaft für diesen Kurs wird dann in den nächsten 24 Stunden eingetragen. Für weitere Kurse, die die koaLA Plattform nutzen erfolgt ab dann die Eintragung automatisch.\n\n Mit freundlichen Grüßen\ndas koaLA-Team" );
		    	$info_sum_part_wakeupmailssent++;
		    	/*
		    	 * Save sent status to mysql
		    	 */
		    	$query = "insert into " . PAUL_SYNC_TABLE_MAIL_NAME . " values(" .
	         	"''," . 
	          	"'" . $booking[ "course_paul" ] . "', " .
	          	"'" . $booking[ "course_koala" ] . "', " .
	          	"'" . $booking[ "matrnr" ] . "', " .
	          	"'" . $booking[ "uid" ] . "', " .
	          	"'" . $booking[ "status_paul" ] . "', " .
	            "''" .
	             ");";
				$info_mysql_requests++;
				if ( mysql_query( $query ) ) {
					//inset was successful
				} else {
					//ERROR
				}
		    } else {
		    	/*
		    	 * User never logged in and wakeupmail already sent. Nothing to do here. Just wait. 
		    	 */		    	
		    }
		    
			$loop_run++;
			if ($loop_run % 100 == 0) {
				echo $loop_run . $newline;
			} else {
				echo "k";
			}
			continue;
		}
	}

	if (isset($cache_steam_groups[$booking[ "course_koala" ]])) {
		$steam_group = $cache_steam_groups[$booking[ "course_koala" ]];
	}
	if (!is_object($steam_group)) {
		$steam_group = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), $booking[ "course_koala" ], 0 );
		if (is_object($steam_group) && $steam_group instanceof steam_group) $cache_steam_groups[$booking[ "course_koala" ]] = $steam_group;
	}

	if (is_object($steam_group) && $steam_group instanceof steam_group) {
		if (isset($cache_koala_courses[$booking[ "course_koala" ]])) {
			$course = $cache_koala_courses[$booking[ "course_koala" ]];
		}
		if (!is_object($course)) {
			timelog("new_koala_course", TRUE);
			paul_sync_log( "PAUL_SYNC\tnew_koala_course course=" . $booking["course_koala"], PAUL_SYNC_LOGLEVEL_DEBUG);
			$course = new koala_group_course( $steam_group );
			if (is_object($course)) $cache_koala_courses[$booking[ "course_koala" ]] = $course;
			paul_sync_log( " runtime=" . timelog("new_koala_course"), PAUL_SYNC_LOGLEVEL_DEBUG, TRUE);
		} else {
			paul_sync_log( "PAUL_SYNC\tCache hit for course=" . $booking["course_koala"], PAUL_SYNC_LOGLEVEL_DEBUG);
		}
		timelog("add_member", TRUE);
		paul_sync_log( "PAUL_SYNC\tadd_member " . $booking["uid"]  . " into " . $booking["course_koala"], PAUL_SYNC_LOGLEVEL_DEBUG);
		
		// ZULASSUNG IN KOALA MIT UEBERNEHMEN
		if (!$readonly) {
			if ( $course->add_member( $student ) ) {
				paul_sync_log( " runtime=" . timelog("add_member"), PAUL_SYNC_LOGLEVEL_DEBUG, TRUE);
				// schedule usercache and groupcache for cleanup
				$cleancache_users[$student->get_id()] = $student;
				$cleancache_groups[$steam_group->get_id()] = 1;
				if (defined("PAUL_SYNC_EMAIL_NOTIFICATION") && PAUL_SYNC_EMAIL_NOTIFICATION === TRUE ) {
					paul_sync_log( "PAUL_SYNC\tsending notification to uid=" . $booking["uid"], PAUL_SYNC_LOGLEVEL_DEBUG);
					$message = str_replace( "%NAME", $student->get_attribute( "USER_FIRSTNAME" ) . " " . $student->get_attribute( "USER_FULLNAME" ), gettext( "Hallo %NAME," ) ). "\n\n";
					$message .= str_replace( "%GROUP", $course->get_name(), gettext( "You have been automatically added to the course '%GROUP' because of your membership in that course in the PAUL system." ) ) . "\n\n";
					$message .= gettext( "This is an automatically generated email." );
					lms_steam::mail($student, lms_steam::get_current_user(), PLATFORM_NAME . ": " . str_replace( "%GROUP", $course->get_name(), gettext( "You have been added to the course '%GROUP'." ) ) , $message);
				} else {
					paul_sync_log( "PAUL_SYNC\tsupressed add notification e-mail to uid=" . $booking["uid"], PAUL_SYNC_LOGLEVEL_DEBUG);
				}
				timelog("mysql_query", TRUE);
				paul_sync_log( "PAUL_SYNC\tadded uid=" . $booking["uid"] . " to " . $booking[ "course_koala" ], PAUL_SYNC_LOGLEVEL_DEBUG);
				paul_sync_log( "PAUL_SYNC\tmysql query uid=" . $booking["uid"] . " to " . $booking[ "course_koala" ], PAUL_SYNC_LOGLEVEL_DEBUG);
				$query = "insert into " . PAUL_SYNC_TABLE_NAME . " values(" .
	          "''," . 
	          "'" . $booking[ "course_paul" ] . "', " .
	          "'" . $booking[ "course_koala" ] . "', " .
	          "'" . $booking[ "matrnr" ] . "', " .
	          "'" . $booking[ "uid" ] . "', " .
	          "'" . $booking[ "status_paul" ] . "', " .
	          "''" .
	          ");";
				$info_mysql_requests++;
				if ( mysql_query( $query ) ) {
					$counter_new_bookings++;
				}
				paul_sync_log( " runtime=" . timelog("mysql_query"), PAUL_SYNC_LOGLEVEL_DEBUG, TRUE);
				paul_sync_log( "PAUL_SYNC\tadd_member added runtime=" . timelog("add_member"), PAUL_SYNC_LOGLEVEL_DEBUG);
			} else {
				paul_sync_log( "PAUL_SYNC\tcant add_member runtime=" . timelog("add_member"), PAUL_SYNC_LOGLEVEL_DEBUG);
			}
		}
	} else {
		paul_sync_log("PAUL_SYNC\tWARNING\tcannot add\t" . $booking[ "uid" ] . "\t from course with id\t" . $booking[ "course_koala" ] . "\tcourse doesnt exist in koaLA", PAUL_SYNC_LOGLEVEL_WARNING );
	}
	
	$loop_run++;
	if ($loop_run % 100 == 0) {
		echo $loop_run . $newline;
	} else {
		echo ".";
	}
}

echo "\t\t\t\t\t[OK]".$newline;
/*echo "stop here !" . $newline;
//reset sync
$paulsync_folder->set_attribute("PAUL_SYNC_RUNNING", "FALSE");
exit;*/

echo "remove deleted users";
$counter_deleted_bookings = 0;
$loop_run = 0;
foreach( $bookings_for_deletion as $booking )
{
	$student = $cache_steam_users[$booking->student_login];
	if (!is_object($student)) {
		$result = $GLOBALS[ "STEAM" ]->predefined_command(
		$user_module,
              "lookup_login",
		array( $booking->student_login, FALSE ),
		0
		);
		if ( is_object( $result[ 0 ] ) ) {
			$student = $result[ 0 ];
			if (is_object($student)) $cache_steam_users[$booking->student_login] = $student;
		}
		else {
			// Benutzer hat sich noch nie in sTeam eingeloggt.
			paul_sync_log( "PAUL_SYNC\tWARNING\tcannot remove member uid=" . $booking["uid"] . " from course=" . $booking["course_koala"] . " user not found in koaLA", PAUL_SYNC_LOGLEVEL_WARNING);
			// Benutzer hat sich noch nie in koaLA/sTeam eingeloggt.
			$loop_run++;
			if ($loop_run % 100 == 0) {
				echo $loop_run . $newline;
			} else {
				echo "k";
			}
			continue;
		}
	}

	$steam_group = $cache_steam_groups[$booking->course_id_koala];
	if (!is_object($steam_group)) {
		$steam_group = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), $booking->course_id_koala, 0 );
		if (is_object($steam_group) && $steam_group instanceof steam_group) $cache_steam_groups[$booking->course_id_koala] = $steam_group;
	}
	//  $steam_group = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), $booking->course_id_koala, 0 );

	if (is_object($steam_group) && $steam_group instanceof steam_group) {
		$course = new koala_group_course( $steam_group );
		if (!$readonly) {
			// KURSAUSTRITT AUCH IN KOALA MIT UEBERNEHMEN
			if ( $course->remove_member( $student ) )
			{
				$cleancache_users[$student->get_id()] = $student;
				$cleancache_groups[$steam_group->get_id()] = 1;
				if (defined("PAUL_SYNC_EMAIL_NOTIFICATION") && PAUL_SYNC_EMAIL_NOTIFICATION === TRUE ) {
					paul_sync_log( "PAUL_SYNC\tsending remove notification to uid=" . $booking["uid"], PAUL_SYNC_LOGLEVEL_DEBUG);
					$message = str_replace( "%NAME", $student->get_attribute( "USER_FIRSTNAME" ) . " " . $student->get_attribute( "USER_FULLNAME" ), gettext( "Hallo %NAME," ) ). "\n\n";
					$message .= str_replace( "%GROUP", $course->get_name(), gettext( "You have been removed from the course '%GROUP' because of your membership data in the PAUL system." ) ) . "\n\n";
					$message .= gettext( "This is an automatically generated email." );
					lms_steam::mail($student, lms_steam::get_current_user(), PLATFORM_NAME . ": " . str_replace( "%GROUP", $course->get_name(), gettext( "You have been removed from the course '%GROUP'." ) ) , $message);
				} else {
					paul_sync_log( "PAUL_SYNC\tsupressed remove notification e-mail to uid=" . $booking->student_login, PAUL_SYNC_LOGLEVEL_DEBUG);
				}
				$query = "DELETE FROM " . PAUL_SYNC_TABLE_NAME . " WHERE id='" . $booking->id . "';";
				$info_mysql_requests++;
				if ( mysql_query( $query ) )  {
					$counter_deleted_bookings++;
				}
				paul_sync_log( "PAUL_SYNC\tremoved uid=" . $booking->student_login . " from course=" . $booking->course_id_koala, PAUL_SYNC_LOGLEVEL_DEBUG);
			} else {
				if (is_object($student) && is_object($course)) {
					paul_sync_log("PAUL_SYNC\tWARNING\tcannot remove\t" . $booking->student_login . "\tfrom course \t" . $booking->course_id_koala, PAUL_SYNC_LOGLEVEL_WARNING );
				} else {
					paul_sync_log("PAUL_SYNC\tWARNING\tcannot remove ", PAUL_SYNC_LOGLEVEL_WARNING);
				}
			}
		}
	} else {
		paul_sync_log("PAUL_SYNC\tWARNING\tcannot remove\t" . $booking->student_login . "\t from course \t" . $booking->course_id_koala . "\tcourse doesnt exist in koaLA", PAUL_SYNC_LOGLEVEL_WARNING );
		$loop_run++;
		if ($loop_run % 100 == 0) {
			echo $loop_run . $newline;
		} else {
			echo "f";
		}
		continue;
	}
	$loop_run++;
	if ($loop_run % 100 == 0) {
		echo $loop_run . $newline;
	} else {
		echo ".";
	}
}

echo "\t\t\t\t\t[OK]".$newline;

/*
 * Setting paul_sync_meta to server
 */
while ( list( $paul_id, $steam_group_name ) = each( $paul_course_ids ) )
{
	//get meta for this course
	$meta = $paul_meta_data_all[$steam_group_name];
	
	//query bookings
	$query = "SELECT * FROM " . PAUL_SYNC_TABLE_NAME . " WHERE course_id_paul = '" . $paul_id . "';";
	$info_mysql_requests++;
	$result = mysql_query( $query );
	$bookings_mtr = array();
	while ( $transaction = mysql_fetch_object( $result ) ) {
			$bookings_mtr[] = $transaction->matrnr;
	}
	$meta["imported users"] = $bookings_mtr;
	
	//set meta data to server
	$steam_group = $cache_steam_groups[$steam_group_name];
	$course = new koala_group_course( $steam_group );
	$course->set_attribute("PAUL_SYNC_META_DATA",$meta);
	
	echo "setting metedate to course: " . $course->get_name() . " " . var_dump($meta) . $newline;
	
}

echo "cleaning up caches";
timelog("empty_cache", TRUE);
paul_sync_log("PAUL_SYNC\tcleaning cache data\t", PAUL_SYNC_LOGLEVEL_DEBUG );
// Empty koaLA Caches
foreach ($cleancache_users as $id => $user) {
	$cache = get_cache_function( $user->get_name());
	$cache->drop( "lms_steam::user_get_groups", $user->get_name(), TRUE );
	$cache->drop( "lms_steam::user_get_groups", $user->get_name(), FALSE );
	$cache->drop( "lms_steam::user_get_groups", $user->get_name() );
	$cache->drop( "lms_steam::user_get_profile", $user->get_name() );
	$cache->drop( "lms_portal::get_menu_html", $user->get_name(), TRUE );
}
foreach($cleancache_groups as $id => $group) {
	$cache = get_cache_function( $id );
	$cache->drop( "lms_steam::group_get_members", $id );
}
paul_sync_log("runtime=" . timelog("empty_cache"), PAUL_SYNC_LOGLEVEL_DEBUG, TRUE );
echo "\t\t\t\t\t\t[OK]".$newline;

// LOGGING
$memory_usage_peak = memory_get_peak_usage(TRUE);
try {
	paul_sync_log("PAUL_SYNC\tRequest counts: steam=" . $GLOBALS["STEAM"]->get_request_count() . " paul=" . $info_paul_requests . " ldap=" . $info_ldap_requests . " mysql=" . $info_mysql_requests, PAUL_SYNC_LOGLEVEL_INFO);
	paul_sync_log("PAUL_SYNC\tPeak memory usage: " .  $memory_usage_peak, PAUL_SYNC_LOGLEVEL_INFO);
	paul_sync_log("PAUL_SYNC\tNew Bookings: " . $counter_new_bookings . " (scheduled:" . $info_sum_bookings . ")" );
	paul_sync_log("PAUL_SYNC\tDeleted bookings: " . $counter_deleted_bookings . " (scheduled: " . $info_sum_part_todelete. ")" );
	paul_sync_log("PAUL_SYNC\tSynchronized participants of " . $info_courses. " courses in: " . timelog("runtime_script"));
	paul_sync_log("PAUL_SYNC\t=== END ===" );
} catch( Exception $e ) {
	paul_sync_log($e->getTraceAsString(), PAUL_SYNC_LOGLEVEL_ERROR);
	print( "Cannot write Log-File! " );
	print( "Please check if " . LOG_PAULSYNC . " is writable." );
}

echo "final statistic:" . $newline;
echo "\t# new bookings:\t\t\t" . $counter_new_bookings . $newline;
echo "\t# deleted bookings:\t\t\t" . $counter_deleted_bookings . $newline;
echo "\t# wake-up-mails sent:\t\t" . $info_sum_part_wakeupmailssent . $newline;
echo "\t# user never logged in:\t" . $info_sum_part_neverloggedin . $newline;
echo "\t# user not found in LDAP:\t" . $info_sum_part_notinldap . $newline;
echo "time: " . (time() - $paul_sync_time) . "ms";

if (defined("PAUL_SYNC_ADMIN_LOGIN")) {
	$logdata = "";
	if ( file_exists( LOG_PAULSYNC_LAST) ) {
		$fp = fopen( LOG_PAULSYNC_LAST, "r");
		$logdata = fread($fp, filesize (LOG_PAULSYNC_LAST));
		fclose($fp);
	}
	$admin = steam_factory::username_to_object($GLOBALS["STEAM"]->get_id(), PAUL_SYNC_ADMIN_LOGIN);
	if (is_object($admin))$admin->mail( "PAUL Sync summary for " . PATH_SERVER, ($logdata!=""?str_replace("\n", "<br />", $logdata):"No log data") );
}
try {
	if (is_object($paulsync_folder)) {
		$paulsync_folder->set_attribute("PAUL_SYNC_ENDTIME", time());
		$paulsync_folder->set_attribute("PAUL_SYNC_RUNNING", "FALSE");
	}
	$GLOBALS["STEAM"]->disconnect();
} catch(Exception $e) {
	paul_sync_log("PAUL_SYNC\tWARNING\tError disconnecting from steam error=" . $e->getMessage(), PAUL_SYNC_LOGLEVEL_WARNING );
}
?>