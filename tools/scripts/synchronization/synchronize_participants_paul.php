#!/usr/bin/php5
<?php
require_once( "/var/www/koala/etc/koala.conf.php" );
//require_once( "/Library/WebServer/Documents/koala-1_5/etc/koala.conf.php" );
require_once( PATH_CLASSES . "lms_ldap.class.php" );
require_once( PATH_LIB . "cache_handling.inc.php" );
require_once( "Cache/Lite/Function.php" );
ini_set('memory_limit', '1024M');
ini_set('max_execution_time', '300');

$GLOBALS["PAUL_SYNC_LOG_KOALA"] = " ";

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

if ( file_exists( LOG_PAULSYNC_LAST) ) {
  $fp = fopen( LOG_PAULSYNC_LAST, "w");
  if ($fp) {
    ftruncate($fp, 0);
    fclose($fp);
  }
}
if (!defined("LOG_PAULSYNC")) define( "LOG_PAULSYNC", LOG_MESSAGES);
paul_sync_log( "PAUL_SYNC\t=== START ===" );
timelog("runtime_script", TRUE);
// SEMESTER UND KURSE ERMITTELN AUS KOALA ERMITTELN
try {
  $steam_user = new lms_user( STEAM_ROOT_LOGIN, STEAM_ROOT_PW );
  $steam_user->login();
  $user_module = $GLOBALS[ "STEAM" ]->get_module( "users" );
} catch (Exception $ex) {
  error_log("error connecting to steam:" . $ex->getMessage());
  paul_sync_log("PAUL_SYNC\tERROR\Error connecting to steam:" . $ex->getMessage(), PAUL_SYNC_LOGLEVEL_ERROR );
  exit;
}
$paulsync_folder = steam_factory::path_to_object($GLOBALS["STEAM"]->get_id(), "/home/root/documents/paulsync");
if (is_object($paulsync_folder)) {
  $lock = $paulsync_folder->get_attribute("PAUL_SYNC_RUNNING");
  if ($lock !== "TRUE") {
    $paulsync_folder->set_attribute("PAUL_SYNC_STARTTIME", time());
    $paulsync_folder->set_attribute("PAUL_SYNC_RUNNING", "TRUE");
  } else {
    paul_sync_log("PAUL_SYNC\tERROR\tScript already running, aborting...", PAUL_SYNC_LOGLEVEL_ERROR );
    $abort = TRUE;
  }
} else {
  paul_sync_log("PAUL_SYNC\tERROR\t/home/root/documents/paulsync/ not found cannot determine script status, exiting...", PAUL_SYNC_LOGLEVEL_ERROR );
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
$current_semester = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), PAUL_SYNC_KOALA_SEMESTER, 0 );

$courses_koala  = lms_steam::semester_get_courses( $current_semester->get_id() );

$paul_course_ids = array();
foreach( $courses_koala as $course )
{
  // Falls Kurs aus PAUL importiert wurde und der Teilnahmedatenabgleich erfolgen soll
  if ( koala_group_course::is_paul_course($course["COURSE_NUMBER"]) && $course[KOALA_GROUP_ACCESS] == PERMISSION_COURSE_PAUL_SYNC ) {
    $paul_course_ids[ $course[ OBJ_NAME ] ] = PAUL_SYNC_KOALA_SEMESTER . "." . $course[ OBJ_NAME ];
    paul_sync_log("Course " . $course[ OBJ_NAME ] . " will be synced."); 
  }
}

// DATENBANK INITIALISIEREN
$db = mysql_connect( PAUL_SYNC_DB_SERVER, PAUL_SYNC_DB_USER, PAUL_SYNC_DB_PW );
if ( ! $db ) {
  paul_sync_log( "PAUL_SYNC\tKeine Verbindung zur Datenbank", PAUL_SYNC_LOGLEVEL_ERROR );
  exit;
}
if ( ! mysql_select_db( PAUL_SYNC_DB_NAME , $db ) ) {
  paul_sync_log( "PAUL_SYNC\tAuswahl der Datenbank schlug fehl", PAUL_SYNC_LOGLEVEL_ERROR );
  exit;
}
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
$bookings_for_deletion = array();
$info_sum_part_paul = 0;
$info_sum_part_koala = 0;
$info_sum_part_db = 0;
$info_sum_bookings = 0;
$info_sum_part_todelete = 0;
$info_ldap_requests = 0;
$info_paul_requests = 0;
$info_mysql_requests = 0;
$paul_sync_errors = array();
// To build notification E-Mails separately
// array( student_login => array( "added" =>array( "link"=>link, "name" => course name ), "deleted" =>array( "link"=>link, "name" => course name )  )  )
$notifications = array();
timelog("runtime_schedule_sum", TRUE);
while ( list( $paul_id, $steam_group_name ) = each( $paul_course_ids ) )
{
  timelog("runtime_schedule", TRUE);
  $info_part_paul = 0;
  $info_part_koala = 0;
  $info_part_db = 0;
  $info_part_todelete = 0;
  $info_bookings = 0;
  $soaperror = FALSE;
  timelog("runtime_get_participants", TRUE);
  $info_paul_requests++;
  try {
    paul_sync_log("PAUL_SYNC\tget_participants\t".$paul_id, PAUL_SYNC_LOGLEVEL_INFO);
    $participants_paul = $paul_soap->get_participants( (string) $paul_id );
    if (count($participants_paul) == 0) {
    	paul_sync_log("EMPTY Course ".$paul_id);
    }
    paul_sync_log(" runtime=". timelog("runtime_get_participants"), PAUL_SYNC_LOGLEVEL_INFO, TRUE); // Append runtime
  } catch (Exception $ex) {
    paul_sync_log("PAUL_SYNC\tERROR\tget_participants\t".$paul_id." ".$ex->getMessage(), PAUL_SYNC_LOGLEVEL_ERROR);
    $soaperror = TRUE;
    $paul_sync_errors[] = "SOAP ERROR get_participants ".$paul_id;
  }

  if ( !$soaperror ) {
    timelog("runtime_schedule_participants", TRUE);
    $info_courses++;
    $info_part_paul = count($participants_paul);
    // Durchgef?hrte Kursbuchungen ermitteln
    $query = "SELECT * FROM " . PAUL_SYNC_TABLE_NAME . " WHERE course_id_paul = '" . $paul_id . "' AND status_paul = 1;";
    $info_mysql_requests++;
    $result = mysql_query( $query );
    $old_bookings = array();
    while ( $transaction = mysql_fetch_object( $result ) ) {
      paul_sync_log("PAUL_SYNC\tfound old booking\t" . $transaction->student_id . "\tfor paul course\t" . $paul_id, PAUL_SYNC_LOGLEVEL_DEBUG);
      $old_bookings[ $transaction->student_id ] = $transaction;
    }
    $info_part_db = count($old_bookings);
    
    foreach( $participants_paul as $booking ) {
      paul_sync_log("PAUL_SYNC\tfound participant\t" . $booking["mnr"] . "\tfor paul course\t" . $paul_id, PAUL_SYNC_LOGLEVEL_DEBUG);
      if ( !(trim( $booking[ "status" ] ) == 1 || trim( $booking[ "status" ] ) == 0) ) {
      	paul_sync_log("PAUL_SYNC\tparticipant\t" . $booking["mnr"] . "\t not allowed for paul course\t" . $paul_id, PAUL_SYNC_LOGLEVEL_DEBUG);
        // Uns interessieren nur die mit dem Status 1
        // = zugelassene Teilnehmer
        continue;
      }
      if ( $booking["mnr"] == "" ) {
      	// Matrikelnummer fehlt in paul!!
      	paul_sync_log("PAUL_SYNC\tmatnr is MISSING cann't add user", PAUL_SYNC_LOGLEVEL_DEBUG);
      	continue;
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
  }
  $info_sum_part_paul += $info_part_paul;
  $info_sum_part_koala += $info_part_koala;
  $info_sum_part_db += $info_part_db;
  $info_sum_part_todelete += $info_part_todelete;
  $info_sum_bookings += $info_bookings;
  paul_sync_log( "PAUL_SYNC\tscheduled actions for course" . $paul_id . ": paulcount=" . $info_part_paul . " dbcount=" . $info_part_db . " deletecount=" . $info_part_todelete . " new bookings=" . $info_bookings, PAUL_SYNC_LOGLEVEL_INFO);
  paul_sync_log(" runtime=". timelog("runtime_schedule_participants"), PAUL_SYNC_LOGLEVEL_INFO, TRUE); // Append runtime
}
paul_sync_log( "PAUL_SYNC\tSUMMARY\tscheduled actions: for paulcount=" . $info_sum_part_paul . " dbcount=" . $info_sum_part_db . " deletecount=" . $info_sum_part_todelete . " new bookings=" . $info_sum_bookings, PAUL_SYNC_LOGLEVEL_INFO);
if (count($paul_sync_errors) >= 1) {
	paul_sync_log("**************Paul SOAP Errors**************");
	foreach ($paul_sync_errors as $error_string) {
		paul_sync_log($error_string);
	}
	paul_sync_log("********************************************");
}

// Zaehler fuer erfolgreich durchgefuehrte Buchungen
$counter_new_bookings = 0;
$counter_never_seen_users = 0;
$counter_LDAP_errors = 0;
$counter_koala_not_remove = 0;

foreach( $new_bookings as $booking ) {
  paul_sync_log( "PAUL_SYNC\tgetting uid for mnr=" . $booking["matrnr"], PAUL_SYNC_LOGLEVEL_DEBUG);
  $info_ldap_requests++;

  if (isset($cache_ldap_logins[$booking[ "matrnr" ]])) {
    $booking[ "uid" ] = $cache_ldap_logins[$booking[ "matrnr" ]];
  } else {
    timelog("ldap_call", TRUE);
    paul_sync_log("PAUL_SYNC\tDEBUG getting mnr=" . $booking[ "matrnr" ] . " from ldap", PAUL_SYNC_LOGLEVEL_DEBUG );
    
    try {
      $lms_ldap = new lms_ldap();
      $lms_ldap->bind( LDAP_LOGIN, LDAP_PASSWORD );
	}
	catch ( Exception $e ) {
	  paul_sync_log("PAUL_SYNC\t" . $e->getMessage(), PAUL_SYNC_LOGLEVEL_ERROR );
	  exit;
	}
    $booking[ "uid" ] = $lms_ldap->studentid2uid($booking[ "matrnr" ] );
    
    paul_sync_log(" runtime=" . timelog("ldap_call"), PAUL_SYNC_LOGLEVEL_DEBUG, TRUE);
    $cache_ldap_logins[$booking[ "matrnr" ]] = $booking[ "uid" ];
  }

  if ( empty( $booking[ "uid" ] ) ) {
    paul_sync_log("PAUL_SYNC\tWARNING " . $booking[ "matrnr" ] . " NOT FOUND IN LDAP", PAUL_SYNC_LOGLEVEL_WARNING );
    $counter_LDAP_errors++;
    continue;
  }
  paul_sync_log( "PAUL_SYNC\tgetting user object for uid=" . $booking["uid"], PAUL_SYNC_LOGLEVEL_DEBUG);
  
  $student = $cache_steam_users[$booking[ "uid" ]];
  if (!is_object($student)) {
    timelog("lookup_login", TRUE);
    paul_sync_log( "PAUL_SYNC\tlookup_login uid=" . $booking["uid"], PAUL_SYNC_LOGLEVEL_DEBUG);
    $result = $GLOBALS[ "STEAM" ]->predefined_command(
      $user_module,
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
      $counter_never_seen_users++;
      continue;
    }
  }

  $steam_group = $cache_steam_groups[$booking[ "course_koala" ]];
  if (!is_object($steam_group)) {
    $steam_group = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), $booking[ "course_koala" ], 0 );
    if (is_object($steam_group) && $steam_group instanceof steam_group) $cache_steam_groups[$booking[ "course_koala" ]] = $steam_group;
  }

  if (is_object($steam_group) && $steam_group instanceof steam_group) {
    $course = $cache_koala_courses[$booking[ "course_koala" ]];
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
      //Wait here for a while
      for ($i=0; $i<5; $i++) {
	  	paul_sync_log(".");
  		sleep(1);
	  }
    } else {
      paul_sync_log( "PAUL_SYNC\tcant add_member runtime=" . timelog("add_member"), PAUL_SYNC_LOGLEVEL_DEBUG);
    }
  } else {
      paul_sync_log("PAUL_SYNC\tWARNING\tcannot add\t" . $booking[ "uid" ] . "\t from course with id\t" . $booking[ "course_koala" ] . "\tcourse doesnt exist in koaLA", PAUL_SYNC_LOGLEVEL_WARNING );    
  }
}

$counter_deleted_bookings = 0;
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
      //Wait here for a while
      for ($i=0; $i<5; $i++) {
      	paul_sync_log(".");
  		sleep(1);
	  }
    } else {
      if (is_object($student) && is_object($course)) {
          paul_sync_log("PAUL_SYNC\tWARNING\tcannot remove\t" . $booking->student_login . "\tfrom course \t" . $booking->course_id_koala, PAUL_SYNC_LOGLEVEL_WARNING );
      	  $counter_koala_not_remove++;
      } else {
          paul_sync_log("PAUL_SYNC\tWARNING\tcannot remove ", PAUL_SYNC_LOGLEVEL_WARNING);
      }
    }
  } else {
      paul_sync_log("PAUL_SYNC\tWARNING\tcannot remove\t" . $booking->student_login . "\t from course \t" . $booking->course_id_koala . "\tcourse doesnt exist in koaLA", PAUL_SYNC_LOGLEVEL_WARNING );    
  }
}
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

// LOGGING
$memory_usage_peak = memory_get_peak_usage(TRUE);
try {
    paul_sync_log("PAUL_SYNC\tRequest counts: steam=" . $GLOBALS["STEAM"]->get_request_count() . " paul=" . $info_paul_requests . " ldap=" . $info_ldap_requests . " mysql=" . $info_mysql_requests, PAUL_SYNC_LOGLEVEL_INFO);
    paul_sync_log("PAUL_SYNC\tPeak memory usage: " .  $memory_usage_peak, PAUL_SYNC_LOGLEVEL_INFO);
    paul_sync_log("PAUL_SYNC\tNew Bookings: " . $counter_new_bookings . " (scheduled:" . $info_sum_bookings . ")" );
    paul_sync_log("PAUL_SYNC\tDeleted bookings: " . $counter_deleted_bookings . " (scheduled: " . $info_sum_part_todelete. ")" );
    paul_sync_log("PAUL_SYNC\tSynchronized participants of " . $info_courses. " courses in: " . timelog("runtime_script"));
    paul_sync_log("PAUL_SYNC\tUsers never seen: " . $counter_never_seen_users);
    paul_sync_log("PAUL_SYNC\tLDAP errors: " . $counter_LDAP_errors);
    paul_sync_log("PAUL_SYNC\tkoaLA not remove: " . $counter_koala_not_remove);
    paul_sync_log("PAUL_SYNC\t=== END ===" );
} catch( Exception $e ) {
  paul_sync_log($e->getTraceAsString(), PAUL_SYNC_LOGLEVEL_ERROR);
  print( "Cannot write Log-File! " );
  print( "Please check if " . LOG_PAULSYNC . " is writable." );
}

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
