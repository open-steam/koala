<?php
namespace Spreadsheets\Commands;

/**
 * This Command can be used with a HTTP request to remove the RT_EDIT attribute of the document  
 * with the given ID.
 * Must be used with authentication data in the URL.
 */
class GetUserPermissions extends \AbstractCommand implements \IFrameCommand {

    private $params;
    private $id;
    private $data;
    private $document;
    private $user_name;
    
    public function httpAuth(\IRequestObject $requestObject) {
        return true;
    }

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        if (isset($this->params[0]) && isset($this->params[1])) {
            $this->id = $this->params[0];
            $this->user_name = $this->params[1];
            $this->document = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
        }
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject) {
        $user = \steam_factory::get_user($GLOBALS["STEAM"]->get_id(), $this->user_name);
        if ($this->document->get_attribute("OBJ_TYPE") == "document_spreadsheet") {
            if ($this->document->check_access_write($user)) {
                echo "w";
            }
            elseif ($this->document->check_access_read($user)) {
                echo "r";
            }
            else {
                echo "0";
            }
        }
        else {
            echo "document $this->id is not a spreadsheet!";
        }
        
        die;
    }

}
?>