<?php

function http_auth() {
  // Abfragen ob man bereits über das koaLA Interface eingeloggt ist
  // Wenn ja, kein HTTP_AUTH notwendig
  if (isset($_SESSION[ "LMS_USER" ]) && $_SESSION[ "LMS_USER" ] instanceof lms_user && $_SESSION[ "LMS_USER" ]->is_logged_in()){
    $lms_user = $_SESSION[ "LMS_USER" ];
    lms_steam::connect( STEAM_SERVER, STEAM_PORT, $lms_user->get_login(), $lms_user->get_password() );
    return true;
  } else {
    // Wenn nicht, untenstehende checks durchführen
    if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']) || $_SERVER['PHP_AUTH_USER'] === "" || $_SERVER['PHP_AUTH_PW'] === "") {
      // User abort
        header('WWW-Authenticate: Basic realm="koaLA"');
        header('HTTP/1.0 401 Unauthorized');
        return false;
    } else {
        // Correct Login
        $lms_user_new = new lms_user($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
        if (!$lms_user_new->login()) {
        	header('WWW-Authenticate: Basic realm="koaLA"');
        	header('HTTP/1.0 401 Unauthorized');
        	return false;
        }
        $_SESSION[ "LMS_USER" ] = $lms_user_new;
        return true;
    }
  }
}
?>