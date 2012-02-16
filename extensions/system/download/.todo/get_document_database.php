<?php
if (!USE_DATABASE_DOWNLOAD === "TRUE" || !$valid_database_call) {
  throw new Exception("Direct call of get_documen_database.php is forbidden", E_CONFIGURATION);
} 
  // getting login data from session or use guest accounts else
  if ( isset($_SESSION[ "LMS_USER" ]) && $_SESSION[ "LMS_USER" ] instanceof lms_user && $_SESSION[ "LMS_USER" ]->is_logged_in() ) {
    $login = $_SESSION[ "LMS_USER" ]->get_login();
    $password = $_SESSION[ "LMS_USER" ]->get_password();
  }
  else {
    $login = 'guest';
    $password = 'guest';
  }
	try {
		while (ob_get_level() > 0) {
			ob_end_clean();
		}
	} catch (Exception $e) {
		
	}
  $downloader = new downloader();
  $downloader->connect_to_mysql();
  // If user is not logged in, open login dialog. If user is logged in
  // and not guest, then display "Access denied" message.
  if ( !$downloader->check_permissions($login, $identifier, $password)) {
     if ($login == 'guest') throw new Exception( "Access denied. Please login.", E_USER_AUTHORIZATION );
     else {
       throw new Exception( "No rights to download object " . $identifier . ".", E_USER_RIGHTS  );
     }
  }
  $data = $downloader->get_document_attributes( $identifier );
//        print_r($data);
//	print("name=" .  $data["name"] 	);
//        exit();
  header( "Pragma: private" );
  header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
  header( "Content-Type: " . $data[ "mimetype" ] );
  header( "Content-Length:" .  $data["contentsize"] );
// The line below forces the Browser's "Save as..." Dialog to pop up
  if (isset($_GET["filename"])) {
//download was started using "/download/<id>/<filename>/" use <filename> as name
    header( "Content-Disposition: filename=\"" . $_GET["filename"] . "\"");
  } else {
    header( "Content-Disposition: attachment; filename=\"" . $data["name"] . "\"");
  }
  ob_flush();
  if (defined("LOG_DEBUGLOG")) {
    $time1 = microtime(TRUE);
    logging::write_log( LOG_DEBUGLOG, "get_document_database:mysql\t" . $login . " \t". $identifier . " \t" . $data["name"] . " \t" .  $data["contentsize"] . " Bytes \t... " );
  }
  $downloader->download_and_print($identifier, $login);
  if (defined("LOG_DEBUGLOG")) {
    logging::append_log( LOG_DEBUGLOG, " \t" . round((microtime(TRUE) - $time1) * 1000 ) . " ms");
  }
?>
