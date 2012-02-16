#!/usr/bin/php5
<?php
require_once( "/var/www/koala/etc/koala.conf.php" );
require_once( PATH_CLASSES . "lms_ldap.class.php" );
require_once( PATH_LIB . "cache_handling.inc.php" );
require_once( "Cache/Lite/Function.php" );


if (!defined("LOG_HISLSFSYNC")) define( "LOG_HISLSFSYNC", LOG_MESSAGES);
logging::write_log( LOG_HISLSFSYNC, "HISLSF_SYNC\t=== START ===" );

// SEMESTER UND KURSE ERMITTELN AUS KOALA ERMITTELN

$steam_user = new lms_user( STEAM_ROOT_LOGIN, STEAM_ROOT_PW );
$steam_user->login();
$user_module = $GLOBALS[ "STEAM" ]->get_module( "users" );

$current_semester = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), SYNC_KOALA_SEMESTER, 0 );

$courses_koala  = lms_steam::semester_get_courses( $current_semester->get_id() );

$hislsf_course_ids = array();
foreach( $courses_koala as $course )
{
        if ( $course[ "COURSE_HISLSF_ID" ] > 0 )
                $hislsf_course_ids[ $course[ "COURSE_HISLSF_ID" ] ] = SYNC_KOALA_SEMESTER . "." . $course[ "OBJ_NAME" ];
}

// DATENBANK INITIALISIEREN
$db = mysql_connect( SYNC_DB_SERVER, SYNC_DB_USER, SYNC_DB_PW );
if ( ! $db )
{
        error_log( "HISLSF_SYNC\tKeine Verbindung zur Datenbank" );
        exit;
}
if ( ! mysql_select_db( SYNC_DB_NAME , $db ) )
{
        error_log( "HISLSF_SYNC\tAuswahl der Datenbank schlug fehl" );
        exit;
}

// BELEGUNGEN IM HIS-LSF ABFRAGEN, MIT DEN BEREITS DURCHGEFÜHRTEN TRANSAKTIONEN VERGLEICHEN,
// UND DIE NEUEN BELEGUNGEN ZUR DURCHFÜHRUNG MARKIEREN. 

$his_lsf = new hislsf_soap();

$new_bookings = array();
$bookings_for_deletion = array();

while ( list( $hislsf_id, $steam_group_name ) = each( $hislsf_course_ids ) )
{
        $r_beleg = $his_lsf->get_participant_list( SYNC_HISLSF_SEMESTER, (string) $hislsf_id );
        if ( $r_beleg->statistics->rows > 0 )
        {
                // Durchgef?hrte Kursbuchungen ermitteln
                $query = "SELECT * FROM " . SYNC_TABLE_NAME . " WHERE semester = '" . SYNC_HISLSF_SEMESTER . "' AND course_id_lsf = '" . $hislsf_id . "' AND status_lsf = 'ZU';";
                $result = mysql_query( $query );
                $old_bookings = array();
                while ( $transaction = mysql_fetch_object( $result ) )
                {
                        $old_bookings[ $transaction->student_id ] = $transaction;
                }

                foreach( $r_beleg->bel as $booking )
                {
                        // SimpleXmlElement => Array
                        $booking = (array) $booking;

                        if ( trim( $booking[ "r_beleg.status" ] ) != "ZU" )
                        {
                                // Uns interessieren nur die mit dem Status ZU
                                // = zugelassenen Teilnehmern
                                continue;
                        }

                        // BUCHUNG SCHONMAL GETAETIGT?

                        if ( ! array_key_exists( $booking[ "r_beleg.tabpk" ], $old_bookings ) )
                        {
                                // NEUE BUCHUNGEN MERKEN
                                $new_bookings[] = array(
                                                "matrnr"     => $booking["r_beleg.tabpk"],
                                                "semester"   => SYNC_HISLSF_SEMESTER,
                                                "course_lsf" => $booking["veranstaltung.veranstid"],
                                                "course_koala" => $hislsf_course_ids[ $booking[ "veranstaltung.veranstid" ] ],
                                                "status_lsf"     => $booking["r_beleg.status"]
                                                );
                        }
                        else
                        {
                                // Im Array $old_bookings sollen nur noch die 
                                // uebrig bleiben, denen kein Datensatz in
                                // $new_bookings gegenueber steht und die
                                // somit aus der Datenbank geloescht werden
                                // muessen.
                                unset( $old_bookings[ $booking[ "r_beleg.tabpk" ] ] );
                        }

                }
                // Alle uebrig gebliebenen sind nicht mehr in der LSF
                // zulassungeliste und wurden also zwischenzeitlich 
                // aus dem LSF geloescht.
                foreach( $old_bookings as $old_booking )
                {
                        $bookings_for_deletion[] = $old_booking;
                }
        }
}

try
{
        $lms_ldap = new lms_ldap();
        $lms_ldap->bind( LDAP_LOGIN, LDAP_PASSWORD );
}
catch ( Exception $e )
{
        error_log( "HISLSF_SYNC\t" . $e->getMessage() );
        exit;
}

// Zaehler fuer erfolgreich durchgefuehrte Buchungen
$counter_new_bookings = 0;

foreach( $new_bookings as $booking )
{
        $booking[ "uid" ] = $lms_ldap->studentid2uid($booking[ "matrnr" ] );
                if ( empty( $booking[ "uid" ] ) )
                                {
                                        logging::write_log( LOG_HISLSFSYNC, "HISLSF_SYNC\tWARNING " . $booking[ "matrnr" ] . " NOT FOUND IN LDAP" );
                                        continue;
                                }

                $result = $GLOBALS[ "STEAM" ]->predefined_command(
                        $user_module,
                        "lookup_login",
                        array( $booking[ "uid" ], TRUE ),
                        0
                );
                if ( is_object( $result[ 0 ] ) )
                {
                        $student = $result[ 0 ];
                }
                else
                {
                        // Benutzer hat sich noch nie in sTeam eingeloggt.
                        continue;
                }

        $steam_group = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), $booking[ "course_koala" ], 0 );
                $course      = new koala_group_course( $steam_group );

        // ZULASSUNG IN KOALA MIT UEBERNEHMEN
        if ( $course->add_member( $student ) )
        {
                $message = str_replace( "%NAME", $student->get_attribute( "USER_FIRSTNAME" ) . " " . $student->get_attribute( "USER_FULLNAME" ), gettext( "Hallo %NAME," ) ). "\n\n";
                $message .= str_replace( "%GROUP", $course->get_name(), gettext( "You have been automatically added to the course '%GROUP' because of your membership in that course in the HIS LSF system." ) ) . "\n\n";
                $message .= gettext( "This is an automatically generated email." );
                lms_steam::mail($student, lms_steam::get_current_user(), PLATFORM_NAME . ": " . str_replace( "%GROUP", $course->get_name(), gettext( "You have been added to the course '%GROUP'." ) ) , $message);
        }
        $query = "insert into " . SYNC_TABLE_NAME . " values(" .
                "'', '" . $booking[ "semester" ]. "', ".
                "'" . $booking[ "course_lsf" ] . "', " .
                "'" . $booking[ "course_koala" ] . "', " .
                "'" . $booking[ "matrnr" ] . "', " .
                "'" . $booking[ "uid" ] . "', " .
                "'" . $booking[ "status_lsf" ] . "', " .
                "''" .
                ");";
        if ( mysql_query( $query ) )
        {
                $counter_new_bookings++; 
        }
}
// LOGGING

try
{
        logging::write_log( LOG_HISLSFSYNC, "HISLSF_SYNC\tNew Bookings: " . $counter_new_bookings );
}
catch( Exception $e )
{
        error_log($e->getTraceAsString());
        print( "Cannot write Log-File! " );
        print( "Please check if " . LOG_HISLSFSYNC . " is writable." );
        exit;
}

$counter_deleted_bookings = 0;
foreach( $bookings_for_deletion as $booking )
{
                $result = $GLOBALS[ "STEAM" ]->predefined_command(
                        $user_module,
                        "lookup_login",
                        array( $booking->student_login, FALSE ),
                        0
                );
                if ( is_object( $result[ 0 ] ) )
                {
                        $student = $result[ 0 ];
                }
                else
                {
                        // Benutzer hat sich noch nie in sTeam eingeloggt.
                        continue;
                }

        $steam_group = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), $booking->course_id_koala, 0 );
        $course = new koala_group_course( $steam_group );

        // KURSAUSTRITT AUCH IN KOALA MIT UEBERNEHMEN
        if ( $course->remove_member( $student ) ) 
        {
                $message = str_replace( "%NAME", $student->get_attribute( "USER_FIRSTNAME" ) . " " . $student->get_attribute( "USER_FULLNAME" ), gettext( "Hallo %NAME," ) ). "\n\n";
                $message .= str_replace( "%GROUP", $course->get_name(), gettext( "You have been removed from the course '%GROUP' because of your membership data in the HIS LSF system." ) ) . "\n\n";
                $message .= gettext( "This is an automatically generated email." );
                lms_steam::mail($student, lms_steam::get_current_user(), PLATFORM_NAME . ": " . str_replace( "%GROUP", $course->get_name(), gettext( "You have been removed from the course '%GROUP'." ) ) , $message);

                $query = "DELETE FROM " . SYNC_TABLE_NAME . " WHERE id='" . $booking->id . "';";
                if ( mysql_query( $query ) )
                {
                        $counter_deleted_bookings++;
                }

        }
        else
        {
                print( "konnte nicht geloescht werden." );
        }

}
// LOGGING

try
{
        logging::write_log( LOG_HISLSFSYNC, "HISLSF_SYNC\tDeleted bookings: " . $counter_deleted_bookings );
        logging::write_log( LOG_HISLSFSYNC, "HISLSF_SYNC\t=== END ===" );
}
catch( Exception $e )
{
        error_log($e->getTraceAsString());
        print( "Cannot write Log-File! " );
        print( "Please check if " . LOG_HISLSFSYNC . " is writable." );
        exit;
}





?>
