<?php
namespace Startpage\Commands;
class Index extends \AbstractCommand implements \IFrameCommand {
	
	public function isGuestAllowed(\IRequestObject $iRequestObject) {
		return true;
	}
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {
		
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
                if (STARTPAGE_REDIRECT) {
                    header("location: " . STARTPAGE_REDIRECT_URL);
                    die;
                }
		$portal = \lms_portal::get_instance();
		$lms_user = $portal->get_user();
		
		$content = \Startpage::getInstance()->loadTemplate("startpage.template.html");

		if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" && isset($_POST["portal_values"])) {
			$portal_user = \lms_steam::get_current_user();
			$portal_user->set_attribute( "USER_LANGUAGE", $_POST["portal_values"]["USER_LANGUAGE"] );
			$lang_index = language_support::get_language_index();
			language_support::choose_language( $lang_index[$_POST["portal_values"]["USER_LANGUAGE"]] );
	
			$cache = get_cache_function( $portal_user->get_name() );
			$cache->drop( "lms_steam::user_get_profile", $portal_user->get_name() );
			$cache->drop( "lms_portal::get_menu_html", $portal_user->get_name(), TRUE );
	
			header( "Location: " . $_POST["portal_values"]["redirect"] );
		}


		if ( $lms_user->is_logged_in() ) {
			header( "Location: " . PATH_URL . "desktop/"  );
			die;
		} else {
			//$content->setCurrentBlock( "BLOCK_SIGN_IN" );
			//$content->setVariable( "LOGIN_FORM_ACTION", URL_SIGNIN );
			//$content->setVariable( "LABEL_LOGIN", gettext( "Login" ) );
			//$content->setVariable( "LABEL_PASSWORD", gettext( "Password" ) );
			//$content->setVariable( "SIGN_IN_BUTTON_TEXT", gettext( "Sign in" ) );
			//$content->parse( "BLOCK_SIGN_IN" );
			
			if (STARTPAGE_AS_PORTAL) {
				$rawHtml = new \Widgets\RawHtml();
				$rawHtml->setHtml($content->get());
				
				$urlRequestObject = new \UrlRequestObject();
				$urlRequestObject->setNamespace("Portal");
				$urlRequestObject->setCommand("Index");
				$urlRequestObject->setParams(array(STARTPAGE_AS_PORTAL_ID));
				
				$command = new \Portal\Commands\Index();
				if ($command->validateData($urlRequestObject)) {
					$command->processData($urlRequestObject);
					$portalFrameResponeObject = $command->frameResponse(new \FrameResponseObject());
				}
				$frameResponseObject->addWidget($rawHtml);
				$frameResponseObject->addWidget($portalFrameResponeObject->getWidgets());
			} else {
			   $startpage_text_path = "./styles/".STYLE."/etc/startpage_text.xml"; 	
		
			   	if (file_exists($startpage_text_path)) {
					$startpage_text = simplexml_load_file($startpage_text_path, null, LIBXML_NOCDATA);
					$content->setVariable( "STARTPAGE_TEXT_LEFT", $startpage_text->left);
					$content->setVariable( "STARTPAGE_TEXT_CENTER", $startpage_text->center);
					$content->setVariable( "STARTPAGE_TEXT_RIGHT", $startpage_text->right);
				} else {
					$content->setVariable( "STARTPAGE_TEXT_LEFT", "Konnte startpage_text.xml nicht finden.");
					$content->setVariable( "STARTPAGE_TEXT_CENTER", "");
					$content->setVariable( "STARTPAGE_TEXT_RIGHT", "");
				}	
				
				$rawHtml = new \Widgets\RawHtml();
				$code = \Startpage::getInstance()->readJS();
				$code = str_replace("{STARTPAGE_IMAGE_TEXT_LONG}", STARTPAGE_IMAGE_TEXT_LONG, $code);
				$code = str_replace("{STARTPAGE_IMAGE_TEXT_MEDIUM}", STARTPAGE_IMAGE_TEXT_MEDIUM, $code);
				$code = str_replace("{STARTPAGE_IMAGE_TEXT_SHORT}", STARTPAGE_IMAGE_TEXT_SHORT, $code);
				$rawHtml->setJs($code);
				$rawHtml->setHtml($content->get());
				$frameResponseObject->addWidget($rawHtml);
			}
			$frameResponseObject->setTitle("");
			return $frameResponseObject;
		}
	}
}
?>