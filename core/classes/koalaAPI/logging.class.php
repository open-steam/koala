<?php

class logging
{
  // For Debug issues
  function log_requests($nr) {
    if (isset($GLOBALS["ENABLE_LOGGING"]) && $GLOBALS["ENABLE_LOGGING"]) error_log($nr . ": " . $GLOBALS["STEAM"]->get_request_count());
  }
  
	static function write_log( $pFile, $pMessage )
	{
		$time 	= date( "d.m.y G:i:s", time() );
		$entry 	= "\n" . $time . "\t" . $pMessage;
    	logging::append_log( $pFile, $entry);
	}
  
	static function append_log( $pFile, $pMessage )
	{
		$entry 	= $pMessage;
		if ( $file_handler = @fopen( $pFile, "a" ) ) {
			fwrite( $file_handler, $entry );
		}
		else {
			throw new Exception( "Cannot write logfile.", E_CONFIGURATION );
		}
	}
  
  function start_timer( $timerid = "1") {
     return logging::timelog( $timerid, TRUE);
  }

  function print_timer( $timerid = "1") {
     return logging::timelog( $timerid);
  }
  
  function timelog( $timerid = "1", $start = FALSE ) {
    if ($start) {
      $GLOBALS["koala_timer_" . $timerid] = microtime(TRUE);
      return $GLOBALS["koala_timer_" . $timerid];
    }
    else {
      return (round((microtime(TRUE) -  $GLOBALS["koala_timer_" . $timerid]) * 1000 ) . " ms");
    }
  }
}

?>
