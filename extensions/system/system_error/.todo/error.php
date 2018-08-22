<?php
try {
include_once( "../etc/koala.conf.php" );
require_once( PATH_LIB . "url_handling.inc.php" );
	$portal = lms_portal::get_instance();

switch( $_GET[ "error" ] )
{
	case E_USER_RIGHTS:
    $portal->initialize( GUEST_ALLOWED );
		$portal->set_page_title( gettext( "No Access") );
		$portal->set_page_main( gettext( "No sufficient rights for this object" ) );
		$portal->set_problem_description( gettext( "You have not the required rights to execute this action. Please consult the owner of the object for further information." ) . "<br /><a href=\"" . $_SERVER[ "HTTP_REFERER" ] . "\">" . gettext( "back" ) . "</a>" );
	break;
	case E_USER_NO_NETWORKINGPROFILE:
    $portal->initialize( GUEST_ALLOWED );
		$portal->set_page_title( gettext( "User Profile not initialized") );
		$portal->set_page_main( gettext( "User Profile not initialized" ) );
		$portal->set_problem_description( gettext( "This user never logged in using the koaLA frontend. Therefore the profile is not initilized yet.<br />The profile will be displayed if the user has used the koaLA frontend at least once." ) . "<br /><a href=\"" . $_SERVER[ "HTTP_REFERER" ] . "\">" . gettext( "back" ) . "</a>" );
	break;
	case E_CONNECTION:
    $portal->initialize( GUEST_ALLOWED, TRUE );
    switch (TRUE) {
      case defined("MAINTAINANCE") && MAINTAINANCE && defined("MAINTAINANCE_MESSAGE") && MAINTAINANCE_MESSAGE && MAINTAINANCE_MESSAGE != "" :
        $portal->set_page_title( gettext( "Maintainance") );
        $portal->set_page_main( gettext( "Maintainance" ) );
        $portal->set_problem_description( MAINTAINANCE_MESSAGE . "<br /><a href=\"" . PATH_URL . "\">" . gettext( "To the start page." ) . "</a>" );
        break;
      case defined("BACKUP_TIME_START") && defined("BACKUP_TIME_END") && BACKUP_TIME_START && BACKUP_TIME_END && BACKUP_TIME_START <= date("G") && date("G") <= BACKUP_TIME_END :
        $portal->set_page_title( gettext( "Backup") );
        $portal->set_page_main( gettext( "Backup" ) );
        $portal->set_problem_description( str_replace("%START", BACKUP_TIME_START,  str_replace( "%END", BACKUP_TIME_END, gettext("The koaLA System is being backuped. The backup process approximately runs from %START to %END each day.") . "<br />" . gettext("Please try again later."))) . "<br /><a href=\"" . PATH_URL . "\">" . gettext( "To the start page." ) . "</a>" );
        break;
      default:
        $portal->set_page_title( gettext( "Unable to process request") );
        $portal->set_page_main( gettext( "Unable to process request" ) );
        //$portal->set_problem_description( gettext("koaLA is not available at the moment. Please try again later.<br />Please note that the regular schedule to maintain koaLA is tuesdays from 4:30 p.m.") . "<br /><a href=\"" . PATH_URL . "\">" . gettext( "To the start page." ) . "</a>" );
        $portal->set_problem_description( gettext("koaLA is not available at the moment. We are working on the problem. Please try again later.") ."</a> "."<br /><a href=\"" . PATH_URL . "\">" . gettext( "To the start page." ) );
    }
	break;
	case E_CONFIGURATION:
    $portal->initialize( GUEST_ALLOWED, TRUE );
    $portal->set_page_title( gettext( "Error in koala configuration") );
    $portal->set_page_main( gettext( "Error in koala configuration" ) );
    $portal->set_problem_description( str_replace( "%MAIL", "<a href=\"mailto:".SUPPORT_EMAIL."?subject=".PLATFORM_NAME."%20Configuration%20Error%20" . $_GET[ "id" ]. "%20&body=Configuration error: Refer to the ".PLATFORM_NAME." error log to get more details." . "\">" . gettext( "Mail an error description" ) . "</a>", gettext("There is an error in the koala configuration. %MAIL." ) ) . "<br /><a href=\"" . PATH_URL . "\">" . gettext( "To the start page." ) . "</a>" );
  break;
  case E_SOAP_SERVICE_ERROR:
  case E_SOAP_INVALID_PASSWORD:
  case E_SOAP_INVALID_INPUT:
  case E_SOAP_INVALID_RESPONSE_DATA:
    $portal->initialize( GUEST_ALLOWED, TRUE );
    $portal->set_page_title( gettext( "SOAP Error") );
    $portal->set_page_main( gettext( "Communication error" ) );
    $portal->set_problem_description( str_replace( "%MAIL", "<a href=\"mailto:".SUPPORT_EMAIL."?subject=".PLATFORM_NAME."%20Configuration%20Error%20" . $_GET[ "id" ]. "%20&body=Communication error: Refer to the ".PLATFORM_NAME." error log to get more details.\r\n" . rawurlencode( "\r\nReferer: " . (!empty($_SERVER[ "HTTP_REFERER" ])?$_SERVER[ "HTTP_REFERER" ]:"Direct call") . "\n" ) . "\"/>" . gettext( "Mail an error description" ) . "</a>", gettext(" An error occured while communicating with the PAUL system. Please help the koaLA support fixing this issue by providing some information e.g. how to produce this error. Please keep in mind that it may be a temporary problem, so try again later.") . "<br />" . "%MAIL" )  . "<br /><br /><a href=\"" . PATH_URL . "\">" . gettext( "To the start page." ) . "</a>" );
  break;
	default:
		if ($_GET[ "id" ] == $_SESSION["ERROR_ID"]) {
		    $portal->initialize( GUEST_ALLOWED );
		    $portal->set_problem_description( str_replace( "%MAIL", "<a href=\"mailto:".SUPPORT_EMAIL."?subject=".PLATFORM_NAME."%20Error%20" . $_GET[ "id" ] . "&body=" . rawurlencode( "\n\nReferer: " . (!empty($_SESSION["ERROR_REFERER"])?$_SESSION["ERROR_REFERER"]:!empty($_SERVER[ "HTTP_REFERER" ])?$_SERVER[ "HTTP_REFERER" ]:"Direct call") . "\n" ) . "\">" . gettext( "Mail an error description" ) . "</a>", gettext( "An unspecified error occured. Please help fixing this issue by sending a detailed error description: %MAIL." ) ) . "<br /><a href=\"" . PATH_URL . "\">" . gettext( "To the start page." ) . "</a>" );
		
		    $portal->set_page_title( "Error" );
		    $error_text = "";
		    if (defined("DEVELOPMENT_MODE") && DEVELOPMENT_MODE) {
		    	$error_text = "<h1 style=\"display:inline\">Entwickler Modus</h1> <a href=\"".$_SESSION["ERROR_REFERER"]."\">Seite erneut laden</a><br><div style=\"background-color:#ccc;border:1px solid #ddd;overflow-x:auto\"><pre>" . $_SESSION["ERROR_TEXT"] . "</pre><div>";
		    }
		    $portal->set_page_main( gettext( "An error occured." ), $error_text );
		} else {
			$portal->initialize( GUEST_ALLOWED );
			$portal->set_page_title( "Error" );
			$portal->set_problem_description("Ungültiger Fehler");
		}
	break;
}

$portal->show_html();
exit;
// print( nl2br( shell_exec( "tail " . LOG_ERROR ) ) );
} catch (Exception $e) {
	echo "error in server configuration";
	error_log($e->getMessage() . "\n" . $e->getTraceAsString());
}

?>
