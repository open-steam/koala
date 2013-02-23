<?php

namespace PortletSubscriptionOld\Commands;

class Create extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $id;
    private $content;
    private $rawHtmlWidget;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        //NAME PARENTID UND URL
        $params = $requestObject->getParams();        
        $name = $params["name"];
        $parent = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $params["parentId"]);
        $object = \steam_factory::create_container($GLOBALS["STEAM"]->get_id(), $name, $parent);
        $object->set_attribute("OBJ_URL", "https://ghnfs3:ghnfs@webmail.lspb.de:8443/caldav/public/" . $params["calendar"]);
        $object->set_attribute("bid:portlet", "subscription");
        $object->set_attribute("OBJ_TYPE", "container_portlet_bid");
       
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