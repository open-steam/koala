<?php
namespace FileTree\Commands;

class Index extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $id;

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
        $room = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->params["dir"]);
        $html = "<ul class=\"jqueryFileTree\" style=\"display: none;\">";
        foreach ($room->get_inventory() as $object) {
            if ($object->get_name() !== "trashbin" && !($object instanceof \steam_user) && !($object instanceof \steam_group)) {
                if ($object instanceof \steam_container && $object->get_attribute("OBJ_TYPE") == "0") {
                    $html .= "<li class=\"directory collapsed\"><a href=\"#\" rel=\"" . $object->get_id() . "/\"><img src=\"".PATH_URL."explorer/asset/icons/mimetype/".deriveIcon($object)."\"></img> " . htmlentities($object->get_name()) . "</a></li>";
                } else {
                    $html .= "<li class=\"file\"><a href=\"#\" rel=\"" . $object->get_id() . "\"><img src=\"".PATH_URL."explorer/asset/icons/mimetype/".deriveIcon($object)."\"></img> " . htmlentities($object->get_name()) . "</a></li>";
                }
            }
        }
        $html .= "</ul>";
        $rawHtml = new \Widgets\RawHTML();
        $rawHtml->setHTML($html);
        $ajaxResponseObject->addWidget($rawHtml);
        return $ajaxResponseObject;
    }
}
?>