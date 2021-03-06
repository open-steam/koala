<?php

namespace Portal\Commands;

class ColorOptions extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $id;

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
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        if (!isset($this->id)) {
            throw new \Exception("Id isn't set!");
        }
        $obj = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
        if (!($obj instanceof \steam_object)) {
            throw new \Exception("current steam object isn't valid!");
        }
        //h1
        $component_fontcolor = $obj->get_attribute("bid:portal:component_fontcolor");
        $component_bgcolor = $obj->get_attribute("bid:portal:component_bgcolor");
        //h2
        $headline_bgcolor = $obj->get_attribute("bid:portal:headline_bgcolor");
        $headline_fontcolor = $obj->get_attribute("bid:portal:headline_fontcolor");
        //entry
        $content_fontcolor = $obj->get_attribute("bid:portal:content_fontcolor");
        $description_fontcolor = $obj->get_attribute("bid:portal:description_fontcolor");
        $content_bgcolor = $obj->get_attribute("bid:portal:content_bgcolor");
        //a
        $link_fontcolor = $obj->get_attribute("bid:portal:link_fontcolor");
        //portlet
        $bgcolor = $obj->get_attribute("bid:portal:bgcolor");

        $ajaxResponseObject->setStatus("ok");
        $dialog = new \Widgets\Dialog();
        $dialog->setTitle("Farbgestaltung");
        $dialog->setAutoSaveDialog(true);
        $dialog->setWidth(400);

        $onchange = "sendRequest( 'UpdateColor', { 'id': ".$this->id.", 'colortype': id, 'value' : value } , '', 'data', function(response){ }, function(response){ }, 'portal');return false;";

        $cpfont = new \Widgets\ColorPicker();
        $cpfont->setId("cpfont");
        $cpfont->setLabel("Schriftfarbe der Komponentenüberschrift");
        if ($component_fontcolor !== 0) {
            $cpfont->setValue($component_fontcolor);
        } else {
            $cpfont->setValue("#FFFFFF");
        }
        $cpfont->setOnChange($onchange);

        $cpbg = new \Widgets\ColorPicker();
        $cpbg->setId("componentbg");
        $cpbg->setLabel("Hintergrundfarbe der Komponentenüberschrift");

        if ($component_bgcolor !== 0) {
            $cpbg->setValue($component_bgcolor);
        } else {
            $cpbg->setValue("#396d9c");
        }
        $cpbg->setOnChange($onchange);

        $headfont = new \Widgets\ColorPicker();
        $headfont->setId("headfont");
        $headfont->setLabel("Schriftfarbe der Eintragsüberschrift");
        if ($headline_fontcolor !== 0) {
            $headfont->setValue($headline_fontcolor);
        } else {
            $headfont->setValue("#333333");
        }
        $headfont->setOnChange($onchange);

        $headbg = new \Widgets\ColorPicker();
        $headbg->setId("headbg");
        $headbg->setLabel("Hintergrundfarbe der Eintragsüberschrift");
        if ($headline_bgcolor !== 0) {
            $headbg->setValue($headline_bgcolor);
        } else {
            $headbg->setValue("#cccccc");
        }
        $headbg->setOnChange($onchange);

        $contentfont = new \Widgets\ColorPicker();
        $contentfont->setId("contentfont");
        $contentfont->setLabel("Schriftfarbe des Inhalts");
        if ($content_fontcolor !== 0) {
            $contentfont->setValue($content_fontcolor);
        } else {
            $contentfont->setValue("#333333");
        }
        $contentfont->setOnChange($onchange);

        $descriptionfont = new \Widgets\ColorPicker();
        $descriptionfont->setId("descriptionfont");
        $descriptionfont->setLabel("Schriftfarbe der Beschreibung");
        if ($description_fontcolor !== 0) {
            $descriptionfont->setValue($description_fontcolor);
        } else {
            $descriptionfont->setValue("#AAAAAA");
        }
        $descriptionfont->setOnChange($onchange);

        $contentbg = new \Widgets\ColorPicker();
        $contentbg->setId("contentbg");
        $contentbg->setLabel("Hintergrundfarbe des Inhalts");
        if ($content_bgcolor !== 0) {
            $contentbg->setValue($content_bgcolor);
        } else {
            $contentbg->setValue("#fbfbfb");
        }
        $contentbg->setOnChange($onchange);

        $portalLinkColor = new \Widgets\ColorPicker();
        $portalLinkColor->setId("portalLinkColor");
        $portalLinkColor->setLabel("Schriftfarbe der Links im Portal");
        if ($link_fontcolor !== 0) {
            $portalLinkColor->setValue($link_fontcolor);
        } else {
            $portalLinkColor->setValue("#396D9C");
        }
        $portalLinkColor->setOnChange($onchange);

        $portalbg = new \Widgets\ColorPicker();
        $portalbg->setId("portalbg");
        $portalbg->setLabel("Hintergrundfarbe des Portals");
        if($bgcolor == "0"){
            $portalbg->setValue("#FFFFFF");
        }
        else {
            $portalbg->setValue($bgcolor);
        }
        $portalbg->setOnChange($onchange);

        $jsWrapper = new \Widgets\JSWrapper();
        $jsWrapper->setPostJsCode(<<<END
                function resetColorSettings(){
                    $("#cpfont").spectrum({
    color: "#FFFFFF", showInput: true
});
                    $("#componentbg").spectrum({
    color: "#396d9c",showInput: true
});
                    $("#headfont").spectrum({
    color: "#333333",showInput: true
});
                    $("#headbg").spectrum({
    color: "#CCCCCC",showInput: true
});
                    $("#contentfont").spectrum({
    color: "#333333",showInput: true
});
                    $("#descriptionfont").spectrum({
    color: "#AAAAAA",showInput: true
});
                    $("#contentbg").spectrum({
    color: "#FBFBFB",
                showInput: true
});
                    $("#portalLinkColor").spectrum({
    color: "#396D9C",
                showInput: true
});
                    $("#portalbg").spectrum({
    color: "#FFFFFF",
                showInput: true
});

                }

END

                );
        $ajaxResponseObject->addWidget($jsWrapper);
        $dialog->addWidget($cpfont);
        $dialog->addWidget($cpbg);
        $dialog->addWidget($headfont);
        $dialog->addWidget($headbg);
        $dialog->addWidget($contentfont);
        $dialog->addWidget($descriptionfont);
        $dialog->addWidget($contentbg);
        $dialog->addWidget($portalLinkColor);
        $dialog->addWidget($portalbg);

        $button = array();
        $button["label"] = "Standardeinstellungen";
        $button["js"] = "sendRequest( 'UpdateColor', { 'id': ".$this->id.", 'colortype': 'standard', 'value' : '' } , '', 'data', function(response){ resetColorSettings();}, function(){resetColorSettings();}, 'portal');return false;";
        $buttons[0] = $button;
        $dialog->setButtons($buttons);

        $ajaxResponseObject->addWidget($dialog);
        return $ajaxResponseObject;
    }

}

?>
