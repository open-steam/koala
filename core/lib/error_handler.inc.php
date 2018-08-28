<?php
	// error handler function
	function myErrorHandler($errno, $errstr, $errfile, $errline, $silent = false)
	{
	    /*
		//if (stripos($errfile, "/lib/php")!==false || stripos($errfile, "/classes/PEAR")!==false) {
		if (stripos($errfile, "/classes/PEAR")!==false) {
			if (defined("DISPLAY_PEAR_ERRORS") && DISPLAY_PEAR_ERRORS) {
				return false;
			} else {
				return true;
			}
		}*/
		if ($errno & RED_BANNER_ERROR_LEVEL) {
			error_log("custom error handler:\n" . $errno . " " . $errstr . " " .$errfile . " " .$errline);
			send_http_error(new Exception($errno . " " . $errstr . " " .$errfile . " " .$errline, E_RUNTIME_ERROR), "", $silent);
		}
		return false;
	}
	
	function myErrorHandler_silent($errno, $errstr, $errfile, $errline) {
		myErrorHandler($errno, $errstr, $errfile, $errline, true);
	}
	
	function shutdown($silent = false) {
	    $isError = false;
        if ($error = error_get_last()){
            switch($error['type']){
                case E_ERROR:
                case E_CORE_ERROR:
                case E_COMPILE_ERROR:
                case E_USER_ERROR:
                case E_PARSE:
                	$log_file = ini_get("error_log");
                	if (file_exists($log_file)) {
	                	$number_of_characters_to_get = 5000; 
						$bf = new BigFile($log_file);
						$text = $bf->getFromEnd( $number_of_characters_to_get );
						$lines = array_reverse(explode("\n", $text));
						$backtrace = "";
						foreach ($lines as $i => $line) {
							if (strstr($line, "Fatal")) {
								for ($j = $i; $j > 0; $j--) {
									$backtrace .= $lines[$j] . "\n";
								}
								break;
							}
						}
						if ($backtrace == "") {
							$backtrace = "No Fatal error found in the last $number_of_characters_to_get lines of $log_file";
						}
                	} else {
                		$backtrace = "No error log found at " . $log_file;
                	}
                    $isError = true;
                    break;
            }
        }

        if ($isError){
        	send_http_error(new Exception("E_ERROR " . $error['message'] . "\n file: " . $error['file'] . " (". $error['line'] .")", E_FATAL_ERROR), "FATAL ERROR. shutdown function tried to restore backtrace from php error log ($log_file):\n" . $backtrace, $silent);
        }
	}
	
	function shutdown_silent() {
		shutdown(true);
	}
?>