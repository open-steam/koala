<?php
namespace Error\Commands;
class Report extends \AbstractCommand implements \IFrameCommand {
	
	private $params;
	
	public function isGuestAllowed(\IRequestObject $iRequestObject) {
		return true;
	}
	
	public function workOffline(\IRequestObject $iRequestObject) {
		return true;
	}
	
	public function validateData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {
		
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$rawHtml = new \Widgets\RawHtml();
		$errorCode = isset($this->params[0]) ? $this->params[0] : 0;
		$errorId = isset($this->params[1]) ? $this->params[1] : 0;
		try {
			switch($errorCode) {
				case E_AJAX_ERROR:
				case E_JS_ERROR:
					$frameResponseObject->setTitle("Error");
					$frameResponseObject->setHeadline("Es ist ein Javascript-Fehler aufgetreten.");
					$frameResponseObject->setProblemDescription("Es ist ein Javascript-Fehler aufgetreten. Das Problem ist protokolliert.");
					
					$errorTitle = (isset($_COOKIE["title"])) ? $_COOKIE["title"] : "";
					$errorDescription = (isset($_COOKIE["description"])) ? $_COOKIE["description"] : "";
					$errorLocation = (isset($_COOKIE["location"])) ? $_COOKIE["location"] : "";
					$errorParams = (isset($_COOKIE["params"])) ? $_COOKIE["params"] : "";
					$subject = PLATFORM_NAME . " Error AJAX/JS Handling";
					mail(ERROR_MAIL_RECEIVER, '=?UTF-8?B?'.base64_encode($subject).'?=', $errorTitle . "\n" . $errorDescription, null,'-f' . ERROR_MAIL_SENDER);
					setcookie("title", "", -1);
					setcookie("description", "", -1);
					setcookie("location", "", -1);
					setcookie("params", "", -1);
				break;
				case E_USER_RIGHTS:
					$frameResponseObject->setTitle(gettext("No Access"));
					$rawHtml->setHtml(gettext("No sufficient rights for this object" ));
					$frameResponseObject->setProblemDescription(gettext("You have not the required rights to execute this action. Please consult the owner of the object for further information.") . "<br /><a href=\"" . $_SERVER[ "HTTP_REFERER" ] . "\">" . gettext( "back" ) . "</a>");
				break;
				case E_USER_NO_NETWORKINGPROFILE:
					$frameResponseObject->setTitle(gettext("User Profile not initialized"));
					$rawHtml->setHtml(gettext("User Profile not initialized"));
					$frameResponseObject->setProblemDescription(gettext( "This user never logged in using the koaLA frontend. Therefore the profile is not initilized yet.<br />The profile will be displayed if the user has used the koaLA frontend at least once.") . "<br /><a href=\"" . $_SERVER[ "HTTP_REFERER" ] . "\">" . gettext( "back" ) . "</a>");
				break;
				case E_CONNECTION:
			    switch (TRUE) {
			      case defined("MAINTAINANCE") && MAINTAINANCE && defined("MAINTAINANCE_MESSAGE") && MAINTAINANCE_MESSAGE && MAINTAINANCE_MESSAGE != "" :
			        $frameResponseObject->setTitle(gettext( "Maintainance"));
			        $rawHtml->setHtml(gettext("Maintainance"));
			        $frameResponseObject->setProblemDescription(MAINTAINANCE_MESSAGE . "<br /><a href=\"" . PATH_URL . "\">" . gettext("To the start page.") . "</a>");
			        break;
			      case defined("BACKUP_TIME_START") && defined("BACKUP_TIME_END") && BACKUP_TIME_START && BACKUP_TIME_END && BACKUP_TIME_START <= date("G") && date("G") <= BACKUP_TIME_END :
			        $frameResponseObject->setTitle(gettext("Backup"));
			        $rawHtml->setHtml(gettext("Backup"));
			        $frameResponseObject->setProblemDescription(str_replace("%START", BACKUP_TIME_START, str_replace("%END", BACKUP_TIME_END, gettext("The koaLA System is being backuped. The backup process approximately runs from %START to %END each day.") . "<br />" . gettext("Please try again later."))) . "<br /><a href=\"" . PATH_URL . "\">" . gettext("To the start page.") . "</a>");
			        break;
			      default:
			        $frameResponseObject->setTitle(gettext("Unable to process request"));
			        $rawHtml->setHtml(gettext("Unable to process request"));
			        $frameResponseObject->setProblemDescription(gettext("system is not available at the moment. We are working on the problem. Please try again later.") ."</a> "."<br /><a href=\"" . PATH_URL . "\">" . gettext("To the start page."));
			    }
				break;
				case E_CONFIGURATION:
			    $frameResponseObject->setTitle(gettext("Error in koala configuration"));
			    $rawHtml->setHtml(gettext("Error in koala configuration"));
			    $frameResponseObject->setProblemDescription(str_replace("%MAIL", "<a href=\"mailto:".SUPPORT_EMAIL."?subject=".PLATFORM_NAME."%20Configuration%20Error%20" . $errorId . "%20&body=Configuration error: Refer to the ".PLATFORM_NAME." error log to get more details." . "\">" . gettext("Mail an error description") . "</a>", gettext("There is an error in the koala configuration. %MAIL.")) . "<br /><a href=\"" . PATH_URL . "\">" . gettext("To the start page.") . "</a>");
			  break;
			  case E_SOAP_SERVICE_ERROR:
			  case E_SOAP_INVALID_PASSWORD:
			  case E_SOAP_INVALID_INPUT:
			  case E_SOAP_INVALID_RESPONSE_DATA:
			    $frameResponseObject->setTitle(gettext("SOAP Error"));
			    $rawHtml->setHtml(gettext("Communication error"));
			    $frameResponseObject->setProblemDescription(str_replace("%MAIL", "<a href=\"mailto:".SUPPORT_EMAIL."?subject=".PLATFORM_NAME."%20Configuration%20Error%20" . $errorId . "%20&body=Communication error: Refer to the ".PLATFORM_NAME." error log to get more details.\r\n" . rawurlencode("\r\nReferer: " . (!empty($_SERVER[ "HTTP_REFERER" ])?$_SERVER[ "HTTP_REFERER" ]:"Direct call") . "\n") . "\"/>" . gettext("Mail an error description") . "</a>", gettext(" An error occured while communicating with the PAUL system. Please help the koaLA support fixing this issue by providing some information e.g. how to produce this error. Please keep in mind that it may be a temporary problem, so try again later.") . "<br />" . "%MAIL")  . "<br /><br /><a href=\"" . PATH_URL . "\">" . gettext("To the start page.") . "</a>");
			  break;
				default:
					if (isset($_SESSION["ERROR_ID"]) && $errorId == $_SESSION["ERROR_ID"]) {
					    if (isset($_SESSION["ERROR_REFERER"]) && !empty($_SESSION["ERROR_REFERER"])) {
					    	$referer = $_SESSION["ERROR_REFERER"];
					    } else if (isset($_SERVER["HTTP_REFERER"]) && !empty($_SERVER["HTTP_REFERER"])) {
					    	$referer = $_SERVER["HTTP_REFERER"];
					    } else {
					    	$referer = "Direct call";
					    }
					    $frameResponseObject->setTitle("Error");
					    $frameResponseObject->setHeadline(gettext("An error occured.")); 
					    $frameResponseObject->setProblemDescription(str_replace("%MAIL", "<a href=\"mailto:".SUPPORT_EMAIL."?subject=".PLATFORM_NAME."%20Error%20" . $errorId . "&body=" . rawurlencode("\n\nReferer: " . $referer . "\n") . "\">" . gettext("Mail an error description") . "</a>", gettext("An unspecified error occured. Please help fixing this issue by sending a detailed error description: %MAIL.")) . "<br /><a href=\"" . PATH_URL . "\">" . gettext("To the start page.") . "</a>");
					    $error_text = "";
					    if (defined("DEVELOPMENT_MODE") && DEVELOPMENT_MODE) {
					    	if (isset($_SESSION["ERROR_REFERER"])){
					    		$error_referer = $_SESSION["ERROR_REFERER"];
					    	} else {
					    		$error_referer = "";
					    	}
					    	if (isset($_SESSION["ERROR_TEXT"])){
					    		$error_text = $_SESSION["ERROR_TEXT"];
					    	} else {
					    		$error_text = "";
					    	}
					    	$error_text = htmlentities($error_text);
					    	$error_text = "<h1 style=\"display:inline\">Entwickler Modus</h1> <a href=\"".$error_referer."\">Seite erneut laden</a><br><div style=\"background-color:#ccc;border:1px solid #ddd;overflow-x:auto\"><pre>" . $error_text . "</pre></div>";
					    }
					    $rawHtml->setHtml($error_text);
					} else {
						$frameResponseObject->setTitle("Error");
						$frameResponseObject->setProblemDescription("Probleme bei der Fehlerbehandlung. <br> Mögliche Ursache: Es sind mehrer Fehler aufgetreten. Diese Seite bezieht sich auf ein veraltetes Problem.<br>Daher ist leider keine nähere Fehlerbeschreibung möglich.");
					}
				break;
			}
			 $frameResponseObject->addWidget($rawHtml);
			return $frameResponseObject;
		} catch (Exception $e) {
			echo "error in server configuration";
			error_log($e->getMessage() . "\n" . $e->getTraceAsString());
			die;
		}
	}
}
?>