<?php
namespace FileTree\Commands;

class UpdateDialog extends \AbstractCommand implements \IAjaxCommand {

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        if ($requestObject instanceof \AjaxRequestObject) {
            $this->params = $requestObject->getParams();
            isset($this->params["id"]) ? $this->id = $this->params["id"] : "";
        }
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        $user = \lms_steam::get_current_user();
        $filetree = $user->get_attribute("FILETREE");
        if (!is_array($filetree)) {
            // default options
            $filetree = array("visible" => 0,
                              "position" => "'center'",
                              "width" => 250,
                              "height" => 500);
        }
        
        if ($this->params["action"] == "open") {
            $filetree["visible"] = 1;
        } else if ($this->params["action"] == "close") {
            $filetree["visible"] = 0;
        } else if ($this->params["action"] == "drag") {
            $filetree["position"] = "[" . $this->params["left"] . ", " . $this->params["top"] . "]";
        } else if ($this->params["action"] == "resize") {
            $filetree["width"] = $this->params["width"];
            $filetree["height"] = $this->params["height"];
        }
        $user->set_attribute("FILETREE", $filetree);
        
        $ajaxResponseObject->setStatus("ok");
        return $ajaxResponseObject;
    }
}
?>