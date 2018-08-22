<?php

namespace PortletSlideShow\Commands;

class Create extends \AbstractCommand implements \IAjaxCommand, \IIdCommand, \IFrameCommand {

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $params = $requestObject->getParams();
        if (isset($params["parent"])) {
            $column = $params["parent"];
        } else {
            $column = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $params["id"]);
        }

        $name = " ";
        if (isset($params["title"]) && $params["title"] != "") {
            $name = $params["title"];
        }

        //create object
        $folderListPortlet = \steam_factory::create_container($GLOBALS["STEAM"]->get_id(), $name, $column);

        $folderListPortlet->set_attributes(array(
            OBJ_DESC => "Diashowportlet",
            OBJ_TYPE => "container_portlet_bid",
            "bid:portlet" => "slideshow",
            "bid:portlet:version" => "3.0",
            "PORTLET_SLIDESHOW_GALERY_ID" => $params["galleryid"],
            "PORTLET_SLIDESHOW_SHOW_DESCRIPTION" => $params["showDescriptionHidden"]
        ));
    }

    public function idResponse(\IdResponseObject $idResponseObject) {
        
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject) {
        
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        $ajaxResponseObject->setStatus("ok");
        $jswrapper = new \Widgets\JSWrapper();
        $jswrapper->setJs(<<<END
		window.location.reload();
END
        );
        $ajaxResponseObject->addWidget($jswrapper);
        return $ajaxResponseObject;
    }

}

?>
