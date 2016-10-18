<?php

namespace Portal\Commands;

class UpdateColor extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $id;
    private $cE;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        if ($requestObject instanceof \UrlRequestObject) {
            $this->params = $requestObject->getParams();
            isset($this->params[0]) ? $this->id = $this->params[0] : "";
        } else if ($requestObject instanceof \AjaxRequestObject) {
            $this->params = $requestObject->getParams();
            isset($this->params["id"]) ? $this->id = $this->params["id"] : "";
        }
        $colortype = $this->params["colortype"];
        if (isset($this->params["value"])) {
            $value = $this->params["value"];
             if(strlen($value) == 4){
                 $value = $value[0] . $value[1] . $value[1] . $value[2] . $value[2] . $value[3] . $value[3];
             }
        }else{
            $value = 0;
        }
        $obj = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);

        if ($colortype === "standard") {
            $obj->set_attribute("bid:portal:component_fontcolor", 0);
            $obj->set_attribute("bid:portal:component_bgcolor", 0);
            $obj->set_attribute("bid:portal:headline_fontcolor", 0);
            $obj->set_attribute("bid:portal:headline_bgcolor", 0);
            $obj->set_attribute("bid:portal:content_fontcolor", 0);
            $obj->set_attribute("bid:portal:description_fontcolor", 0);
            $obj->set_attribute("bid:portal:content_bgcolor", 0);
            $obj->set_attribute("bid:portal:link_fontcolor", 0);
            $obj->set_attribute("bid:portal:bgcolor", 0);

            /*$obj->set_attribute("bid:portal:component_fontcolor", 0);
            $obj->set_attribute("bid:portal:component_bgcolor", 0);
            $obj->set_attribute("bid:portal:headline_fontcolor", 0);
            $obj->set_attribute("bid:portal:headline_bgcolor", 0);
            $obj->set_attribute("bid:portal:content_fontcolor", 0);
            $obj->set_attribute("bid:portal:content_bgcolor", 0);
            $obj->set_attribute("bid:portal:link_fontcolor", 0);
            $obj->set_attribute("bid:portal:bgcolor", 0); */
        } else if ($colortype === "cpfont") {
            $obj->set_attribute("bid:portal:component_fontcolor", $value);
        } else if ($colortype === "componentbg") {
            $obj->set_attribute("bid:portal:component_bgcolor", $value);
        } else if ($colortype === "headfont") {
            $obj->set_attribute("bid:portal:headline_fontcolor", $value);
        } else if ($colortype === "headbg") {
            $obj->set_attribute("bid:portal:headline_bgcolor", $value);
        } else if ($colortype === "contentfont") {
            $obj->set_attribute("bid:portal:content_fontcolor", $value);
        } else if ($colortype === "descriptionfont") {
            $obj->set_attribute("bid:portal:description_fontcolor", $value);
        } else if ($colortype === "contentbg") {
            $obj->set_attribute("bid:portal:content_bgcolor", $value);
        } else if ($colortype === "portalLinkColor") {
            $obj->set_attribute("bid:portal:link_fontcolor", $value);
        } else if ($colortype === "portalbg") {
            $obj->set_attribute("bid:portal:bgcolor", $value);
        } else if ($colortype === "standard") {
            $obj->set_attribute("bid:portal:component_fontcolor", 0);
            $obj->set_attribute("bid:portal:component_bgcolor", 0);
            $obj->set_attribute("bid:portal:headline_fontcolor", 0);
            $obj->set_attribute("bid:portal:headline_bgcolor", 0);
            $obj->set_attribute("bid:portal:content_fontcolor", 0);
            $obj->set_attribute("bid:portal:description_fontcolor", 0);
            $obj->set_attribute("bid:portal:content_bgcolor", 0);
            $obj->set_attribute("bid:portal:link_fontcolor", 0);
            $obj->set_attribute("bid:portal:bgcolor", 0);
        }
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        $ajaxResponseObject->setStatus("ok");
        return $ajaxResponseObject;
    }

}
?>
