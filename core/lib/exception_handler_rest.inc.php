<?php
include_once( PATH_LIB . 'encryption_handling.inc.php' );

function rest_json_error($errmsg) {
	echo json_encode(Array(
		"error" => $errmsg
	));
}

function send_http_error_rest($pException, $pBacktrace = "", $silent = false) {
	if ($pException->getCode() == E_USER_ACCESS_DENIED ) {
		logging::write_log( LOG_403, date("d.m.Y H:i", time()) . " USER: " . $_ENV["USER"] . " " . "HTTP-" . $_SERVER[ 'REQUEST_METHOD' ]. ': ' . $_SERVER[ 'REQUEST_URI' ]);
                
                if (lms_portal::get_instance()->get_user()->is_logged_in()) {
					rest_json_error("E_USER_ACCESS_DENIED");
                    exit;
                } else {
                    rest_json_error("E_USER_ACCESS_DENIED (NOT LOGGED IN)");
                    exit;
                }
	}
	if ( $pException->getCode() == E_USER_AUTHORIZATION )
	{
		try {
			while (ob_get_level() > 0) {
				ob_end_clean();
			}
		} catch (Exception $e) {
				
		}
		
		rest_json_error("authorization required");
		
		exit;
	}
	if ( $pException->getCode() == E_USER_DISCLAIMER ) {
		try {
			while (ob_get_level() > 0) {
				ob_end_clean();
			}
		} catch (Exception $e) {
				
		}
		rest_json_error("E_USER_DISCLAIMER");
		exit;
	}
	if ( $pException->getCode() == E_USER_CHANGE_PASSWORD ) {
		try {
			while (ob_get_level() > 0) {
				ob_end_clean();
			}
		} catch (Exception $e) {
				
		}
		rest_json_error("E_USER_CHANGE_PASSWORD");
		exit;
	}
	try {
		while (ob_get_level() > 0) {
			ob_end_clean();
		}
	} catch (Exception $e) {

	}
	rest_json_error($pException->getMessage());
	exit;
}

?>