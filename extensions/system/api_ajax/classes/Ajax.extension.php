<?php
include_once(PATH_LIB . "error_handler.inc.php");
include_once(PATH_LIB . "exception_handler.inc.php");

function ajaxErrorHandler($errno, $errstr, $errfile, $errline)
{
	//if (stripos($errfile, "/lib/php")!==false || stripos($errfile, "/classes/PEAR")!==false) {
	if (stripos($errfile, "/classes/PEAR")!==false) {
		if (defined("DISPLAY_PEAR_ERRORS") && DISPLAY_PEAR_ERRORS) {
			return false;
		} else {
			return true;
		}
	}
	if ($errno & RED_BANNER_ERROR_LEVEL) {
		error_log("custom error handler:\n" . $errno . " " . $errstr . " " .$errfile . " " .$errline);
		send_http_error(new Exception($errno . " " . $errstr . " " .$errfile . " " .$errline, E_RUNTIME_ERROR), "", true);
		$response = new HttpResponse();
		$response->setStatus("400 Bad Request");
		$response->write("Error: " . $errno . " " . $errstr . " " .$errfile . " " .$errline);
		$response->flush();
		//send_http_error(new Exception($errno . " " . $errstr . " " .$errfile . " " .$errline, E_RUNTIME_ERROR));
	}
	return false;
}

function ajaxExceptionHandler($exception) {
	send_http_error($exception, "", true);
	$response = new HttpResponse();
	$response->setStatus("400 Bad Request");
	$response->write("Exception\n" . var_export($exception, true	));
	$response->flush();
	return false;
}

function ajaxShutdown() {
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
        	send_http_error(new Exception("E_ERROR " . $error['message'] . "\n file: " . $error['file'] . " (". $error['line'] .")", E_FATAL_ERROR), "FATAL ERROR. shutdown function tried to restore backtrace from php error log ($log_file):\n" . $backtrace, true);
        	$response = new HttpResponse();
			$response->setStatus("400 Bad Request");
			$response->write("E_ERROR " . $error['message'] . "\n file: " . $error['file'] . " (". $error['line'] .")");
			$response->flush();
        }
}


class Ajax extends AbstractExtension {
	
	public function init() {
		if (!$this->initOnce) {
			$this->addJS();
			lms_portal::get_instance()->add_javascript_onload("ajax", <<< END
jQuery(document).mousemove(function(e){
		currentX = e.pageX;
		currentY = e.pageY;
});
END
);
			$this->initOnce = true;
		}
	}
	
	public function getName() {
		return "Ajax";
	}
	
	public function getDesciption() {
		return "Extension for the Ajax API.";
	}
	
	public function getVersion() {
		return "v1.0.0";
	}
	
	public function getAuthors() {
		$result = array();
		$result[] = new Person("Dominik", "Niehus", "nicke@uni-paderborn.de");
		return $result;
	}
	
	public static function isAjaxRequest() {
		return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));
	}
	
	public function enableAjaxErrorHandling() {
		set_error_handler("ajaxErrorHandler");
		register_shutdown_function("ajaxShutdown");
		set_exception_handler("ajaxExceptionHandler");
	}
		
	public function handleRequest($path) {
		//***********AJAX Handling*************
		// Is current request is an AJAX-Request, don't generate template code
		if (self::isAjaxRequest()) {
			$app = lms_portal::get_instance();
			$app->initialize(GUEST_NOT_ALLOWED);
			$frontController = new FrontController();
			//$request = new HttpRequest();
			//$response = new HttpResponse();
			$ajaxRequestObject = new AjaxRequestObject();
			if (isset($_REQUEST["namespace"]) && $_REQUEST["namespace"] != "") {
				$ajaxRequestObject->setNamespace($_REQUEST["namespace"]);
			} else {
				$ajaxRequestObject->setNamespace($path[0]);
			}
			$ajaxRequestObject->setCommand(isset($_REQUEST["command"])?$_REQUEST["command"]:"");
			$ajaxRequestObject->setElementId(isset($_REQUEST["elementId"])?$_REQUEST["elementId"]:"");
			$ajaxRequestObject->setRequestType(isset($_REQUEST["requestType"])?$_REQUEST["requestType"]:"");
			$params = $_REQUEST;
			unset($params["command"]);
			unset($params["elementId"]);
			unset($params["requestType"]);
			$ajaxRequestObject->setParams($params);
			$ajaxResponseObject = new AjaxResponseObject();
			// Handle request by calling command with frontcontroller
			$result = $frontController->handleRequest($ajaxRequestObject, $ajaxResponseObject);
			exit;
		}
		//**************************************
	}
}
?>