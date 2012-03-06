<?php
include_once( PATH_LIB . 'encryption_handling.inc.php' );

function send_http_error($pException, $pBacktrace = "", $silent = false) {
	if ($pException->getCode() == E_USER_ACCESS_DENIED ) {
		logging::write_log( LOG_403, date("d.m.Y H:i", time()) . " USER: " . $_ENV["USER"] . " " . "HTTP-" . $_SERVER[ 'REQUEST_METHOD' ]. ': ' . $_SERVER[ 'REQUEST_URI' ]);
                header( 'Location: ' . PATH_URL . "403/");
		exit;
	}
	if ( $pException->getCode() == E_USER_AUTHORIZATION )
	{
		try {
			while (ob_get_level() > 0) {
				ob_end_clean();
			}
		} catch (Exception $e) {
				
		}
		$protocoll = isset($_SERVER["HTTPS"]) ? "https://" : "http://";
		$url = $protocoll.$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
		$request_url = str_ireplace(PATH_URL, "/", $url);
		($silent) or header( 'Location: ' . URL_SIGNIN_REQUEST . substr($request_url, 1));
		exit;
	}
	if ( $pException->getCode() == E_USER_DISCLAIMER ) {
		try {
			while (ob_get_level() > 0) {
				ob_end_clean();
			}
		} catch (Exception $e) {
				
		}
		$protocoll = isset($_SERVER["HTTPS"]) ? "https://" : "http://";
		$url = $protocoll.$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
		$request_url = str_ireplace(PATH_URL, "/", $url);
		($silent) or header( 'Location: ' . PATH_URL . 'disclaimer_local.php?req=' . $request_url );
		exit;
	}
	if ( $pException->getCode() == E_USER_CHANGE_PASSWORD ) {
		try {
			while (ob_get_level() > 0) {
				ob_end_clean();
			}
		} catch (Exception $e) {
				
		}
		($silent) or header( 'Location: ' . PATH_URL . 'usermanagement/user-password');//?req=' . $_SERVER[ 'REQUEST_URI' ] );
		exit;
	}
	if ( $pException->getCode() != E_USER_RIGHTS )
	{
		$error_id = 'E' . strtoupper(uniqid( '', FALSE ));
		try
		{
			$user = 0;
			try {
				$user = lms_steam::get_current_user();
			} catch ( Exception $x) {
			}

			$ustring = 'user is not a valid object (' . $_ENV["USER"] . ')';
			try {
				if (is_object($user) && $user instanceof steam_user) $ustring = $user->get_name();
			}
			catch (Exception $e){
				$ustring = '(id=' . $user->get_id() . ')';
			}
			
			if (lms_portal::is_instance() && is_object(lms_portal::get_instance()->get_user())) {
				$password = lms_portal::get_instance()->get_user()->get_password();
			} else {
				$password = "1234567890NICHTGESETZT1234567890";
			}
			
			$_SESSION["ERROR_ID"] = $error_id;
			if ($pBacktrace != "") {
				$backtrace = $pBacktrace;
			} else {
				$backtrace = $pException->getTraceAsString();
			}
			$backtrace = str_replace($password, "*****", $backtrace . "\n==============================");
			$_SESSION["ERROR_TEXT"] = 'ID: ' . $error_id .
			                          "\tCode: " . $pException->getCode() .
									  "\nDate: " . date("d.m.Y H:i", time()) .
			                          "\nUser: " . $ustring .
			                          "\nHTTP-" . $_SERVER[ 'REQUEST_METHOD' ]. ': ' . $_SERVER[ 'REQUEST_URI' ] .
			                          "\nBrowser: " . $_SERVER[ 'HTTP_USER_AGENT' ] .
			                          "\nMessage: " . $pException->getMessage() .
									  "\nServer: " . PATH_SERVER .
									  "\nPlatform: " . PLATFORM_ID . 
			                          "\nBacktrace:\n" . $backtrace;
			$_SESSION["ERROR_REFERER"] = $_SERVER["REQUEST_URI"];
			if (defined("SEND_ERROR_MAIL") && SEND_ERROR_MAIL) {
				$subject = PLATFORM_NAME . " Error " . $_SESSION["ERROR_ID"];
				$header = "MIME-Version: 1.0\r\n".
						  "Content-type: text/plain; charset=utf-8\r\n".
				   		  "From: " . ERROR_MAIL_SENDER . "\r\n" .
				   		  "X-Mailer: PHP/" . phpversion();
				mail(ERROR_MAIL_RECEIVER, '=?UTF-8?B?'.base64_encode($subject).'?=', $_SESSION["ERROR_TEXT"], $header);
				logging::write_log(LOG_ERROR, "Error mail sent.");
			}
			logging::write_log( LOG_ERROR, $_SESSION["ERROR_TEXT"]);
		}
		catch( Exception $e )
		{
			echo "<pre>" . $e->getTraceAsString() . "</pre>";
			error_log($e->getTraceAsString());
			print( 'Cannot write Log-File! ' );
			print( 'Please check if ' . LOG_ERROR . ' is writable. <br>' );
			print( 'ErrorMessage:<br><pre>');
			try {
				$password = lms_portal::get_instance()->get_user()->get_password();
			} catch (Exception $e){
				$password = "1234567890NICHTGESETZT1234567890";
			}
			print( 'ID: ' . $error_id .
                          "\tCode: " . $pException->getCode() .
						  "\nDate: " . date("d.m.Y H:i", time()) .
                          "\nUser: " . $ustring .
                          "\nHTTP-" . $_SERVER[ 'REQUEST_METHOD' ]. ': ' . $_SERVER[ 'REQUEST_URI' ] .
                          "\nBrowser: " . $_SERVER[ 'HTTP_USER_AGENT' ] .
                          "\nMessage: " . $pException->getMessage() .
						  "\nServer: " . PATH_SERVER .
						  "\nPlatform: " . PLATFORM_ID .
                          "\nBacktrace:\n" . str_replace($password, "*****", $pException->getTraceAsString() . "\n=============================="));
			exit;
		}
	}
	try {
		while (ob_get_level() > 0) {
			ob_end_clean();
		}
	} catch (Exception $e) {

	}
	if (!isErrorPage()) {
		if (!headers_sent()) {
			if (!isset($error_id)) {
				$error_id = "missing";
			}
			if (!defined("URL_ERROR_REPORT")) {
				echo "Extension for error handling not loaded.<br><pre>" . $pException->getMessage() . "</pre>";
				die;
			} else {
	    		($silent) or header( 'Location: ' . URL_ERROR_REPORT . $pException->getCode() . "/" . $error_id);
	    		die;
			}
		} else {
			//fallback if header already sent
			echo "<br /><br /><div style=\"color:red;font-size:small\">koala error handling: error occured but cannot redirect</div>";
			echo "Try to redirect with Javascript.<br>";
			echo "or go to error page by link: <a href=\"". URL_ERROR_REPORT . $pException->getCode() . "/" . $error_id . "\">plattform error page</a>";
			echo "<script type=\"text/javascript\">window.location.href = '" . URL_ERROR_REPORT . $pException->getCode() . "/" . $error_id . "';</script>";
		}
	} else {
		echo "<h3>Deep Framework-Error - cannot display error page</h3>The last error, which could be restored. May be more.<pre>" . $_SESSION["ERROR_TEXT"] . "</pre>System terminated :-(";
	}
	exit;
}

function send_http_error_silent($pException, $pBacktrace = "") {
	send_http_error($pException, $pBacktrace, true);
}
?>
