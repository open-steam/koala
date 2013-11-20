<?php

namespace SignIn\Commands;

class SignIn extends \AbstractCommand implements \IFrameCommand {

    private $request;

    public function setRequest($request) {
        $this->request = $request;
    }

    public function isGuestAllowed(\IRequestObject $iRequestObject) {
        return true;
    }

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject) {
        $portal = \lms_portal::get_instance();
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $problem = "";
            $hint = "";
            $values = $_POST["values"];
            if (empty($values["login"])) {
                $problem = gettext("The login name is missing.") . "&nbsp;";
                $hint = gettext("Please enter your login name.") . "&nbsp;";
            }
            if (empty($values["password"])) {
                $problem .= gettext("Your password is missing.");
                $hint .= gettext("Please enter your password.");
            }

            if ($values["login"] == "guest") {
                $problem = gettext("Invalid username or wrong password.");
                $hint = gettext("Please verify your credentials and try again.");
            }
            // Check if user is locked by usermanagement
            if (defined("USERMANAGEMENT") && USERMANAGEMENT) {
                $dataAccess = new sTeamServerDataAccess();
                $userID = $dataAccess->login2ID($values["login"]);
                if ($userID != "-1" && ($dataAccess->isLocked($userID))) {
                    $problem .= "Dieser Benutzer wurde gesperrt und kann sich vorl&auml;ufig nicht am System anmelden";
                    $hint .= "";
                } else if ($userID != "-1" && $dataAccess->isTrashed($userID)) {
                    $problem .= "Dieser Benutzer wurde gelöscht und kann sich nicht mehr am System anmelden";
                    $hint .= "";
                }
            }
            if (empty($problem)) {
                if (CHECK_LDAP_ACCESS) {
                    $client = new \SoapClient(NULL, array(
                        "location" => LDAP_SERVICE_LOCATION,
                        "uri" => LDAP_SERVICE_URL,
                        "local_cert" => LDAP_SERVICE_CERT
                    ));
                    $parameter = array(new \SoapParam(trim($values["login"]), "uid"));
                    try {
                        $result = $client->__soapCall("getState", $parameter);
                    } catch (\Exception $e) {
                        // $problem = "SOAP-SERVICE NICHT ERREICHBAR!";
                        $hint = "";
                    }

                    if (empty($problem) /** && SERVICE_NICHT_GESETZT* */) {
                        $ldap_client = new \lms_ldap();
                        $dn = "uid=" . utf8_encode(trim($values["login"])) . ", " . LDAP_OU . ", " . LDAP_O . ", " . LDAP_C;
                        if (!$ldap_client->bind($dn, utf8_encode($values["password"]))) {
                            $problem = gettext("Invalid username or wrong password.");
                            $hint = gettext("Please verify your credentials and try again.");
                        } else {
                            $_SESSION["LDAP_PASSWORD_ENCR"] = encrypt($values["password"], ENCRYPTION_KEY);
                            $_SESSION["LDAP_LOGIN"] = utf8_encode(trim($values["login"]));
                            ob_end_clean();

                            header("Location: " . PATH_URL . "disclaimer.php");
                            exit;
                        }
                    }
                }
            }

            if (empty($problem) && !$portal->login(trim($values["login"]), $values["password"], isset($values["req"]) ? $values["req"] : NULL )) {
                $problem = gettext("Invalid username or wrong password.");
                $hint = gettext("Please verify your credentials and try again.");
            }
        }

        if (!empty($problem)) {
            $portal->set_problem_description(
                    $problem, $hint
            );
        }

        $content = \SignIn::getInstance()->loadTemplate("signin.template.html");
        $content->setVariable("REQUEST_URI", (isset($_GET["req"]) ? $_GET["req"] : ""));

        $content->setVariable("LOGIN_INFO", "Sie haben keine Berechtigung, auf das ausgewählte Objekt zuzugreifen. Möglicherweise haben Sie einen falschen Benutzernamen oder ein falsches Passwort eingegeben. " .
                "(" .
                "Hinweis: Bitte beachten Sie, dass Sie in Ihren Browsereinstellungen dieser Seite das Speichern von Cookies erlauben. Das ist für die Anmeldung notwendig." .
                ")");

        //old text
        //$content->setVariable( "LOGIN_INFO", gettext("Use your IMT credentials to log in into koaLA knowledge network. Here, you can get and discuss course materials online, extend your personal and academic network, and cooperate with fellow students and colleagues.") . "<br>(" . gettext("Hinweis: Bitte beachten Sie, dass Sie in Ihren Browsereinstellungen dieser Seite das Speichern von Cookies erlauben. Das ist für die Anmeldung notwendig.") . ")");


        $content->setVariable("LOGIN_NAME_TEXT", gettext("Login"));
        $content->setVariable("PASSWORD_NAME_TEXT", gettext("Password"));
        $content->setVariable("SIGNIN_BUTTON_TEXT", gettext("Sign in"));
        $content->setVariable("RETURN_TEXT", gettext("return to the home page"));
        $content->setVariable("REQUEST_URI", (isset($this->request) ? $this->request : ""));
        if (isset($values) && is_array($values)) {
            $content->setVariable("VALUE_LOGIN", trim($values["login"]));
        }

        if (isset($values["login"]) && !empty($values["login"])) {
            $portal->add_javascript_onload("SignIn", "if($('#second_field').length > 0){document.getElementById('second_field').focus();}");
        }
        else
            $portal->add_javascript_onload("SignIn", "if($('#first_field').length > 0){document.getElementById('first_field').focus();}");

        $frameResponseObject->setTitle(gettext("Sign in"));
        $frameResponseObject->setHeadline("Anmeldung");
        $rawHtml = new \Widgets\RawHtml();
        $rawHtml->setHtml($content->get());
        $frameResponseObject->addWidget($rawHtml);

        return $frameResponseObject;
    }

}
?>



