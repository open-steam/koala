<?php
namespace Download\Commands;
abstract class AbstractDownloadCommand extends \AbstractCommand implements \IResourcesCommand
{
    protected $params;
    protected $id;
    protected $filename;
    private $login;
    private $password;

    public function isGuestAllowed(\IRequestObject $iRequestObject)
    {
        return true;
    }

    public function validateData(\IRequestObject $requestObject)
    {
        if (isset($_SESSION[ "LMS_USER" ]) && $_SESSION[ "LMS_USER" ] instanceof \lms_user && $_SESSION[ "LMS_USER" ]->is_logged_in()) {
            $this->login = $_SESSION[ "LMS_USER" ]->get_login();
            $this->password = $_SESSION[ "LMS_USER" ]->get_password();
        } else {
            $this->login = 'guest';
            $this->password = 'guest';
        }
        $this->params = $requestObject->getParams();
        if (isset($this->params[0])) {
            $this->id = $this->params[0];
            if (isset($this->params[1])) {
                $this->filename = $this->params[1];
            }

            return true;
        }

        return false;
    }

    public function processData(\IRequestObject $requestObject)
    {
        $this->download_document($this->login, $this->password, $this->id, "id", isset($this->width) ? $this->width : null, isset($this->height) ? $this->height : null);
        exit;
    }

    public function resourcesResponse()
    {

    }


    private function download_document($login, $password, $identifier, $identifier_type, $width = false, $height = false)
    {
        $STEAM = \steam_connector::connect(STEAM_SERVER, STEAM_PORT, $login, $password);
        if ($identifier_type === "name") {
            $document = $STEAM->predefined_command($STEAM->get_module("icons"), "get_icon_by_name", array((string) $identifier ), 0);
        } elseif ($identifier_type === "id") {
            $document = \steam_factory::get_object($STEAM->get_id(), (int) $identifier);
            if (!isset($this->filename)) {
                header("location: " . PATH_URL . "Download/Document/{$identifier}/{$document->get_name()}");
                exit;
            }
        }

        if (!$document instanceof \steam_document) {
            \ExtensionMaster::getInstance()->send404Error();
        }

        // If user is not logged in, open login dialog. If user is logged in
        // and not guest, then display "Access denied" message.
        if (!$document->check_access_read($STEAM->get_current_steam_user())) {
            if ($login == 'guest') throw new \Exception("Access denied. Please login.", E_USER_AUTHORIZATION);
            else {
                throw new \Exception("Access denied.", E_USER_RIGHTS);
            }
        }


        if (!$width && !$height) {
            $document->download();
        } else {
            $document->download(DOWNLOAD_IMAGE, array($width, $height));
        }
    }
}
