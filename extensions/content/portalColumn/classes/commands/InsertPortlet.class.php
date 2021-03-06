<?php

namespace PortalColumn\Commands;

class InsertPortlet extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $id;
    private $user;
    private $order;
    private $orderDependency; //object 

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        $this->id = $this->params["portletId"];
        $this->user = \lms_steam::get_current_user_no_guest()->get_name();


        //insert order
        $this->order = 0;
        $this->orderDependency = 0;

        if (isset($this->params["order"])) {
            $this->order = $this->params["order"]; //first, last, above, below
        }
        if (isset($this->params["orderDependency"])) {
            $this->orderDependency = $this->params["orderDependency"]; //object id relativ orderd to
        }
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        $idRequestObject = new \IdRequestObject();
        $idRequestObject->setId($this->id);
        $columnObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);

        foreach (\lms_steam::get_current_user_no_guest()->get_inventory() as $steamObject) {
            $portletType = $steamObject->get_attribute("bid:portlet");


            if ($portletType !== 0 && in_array($portletType, array("msg", "appointment", "termplan", "topic", "headline", "poll", "media", "rss", "chronic", "userpicture", "folderlist", "subscription", "slideshow"))) {

                $steamObject->move($columnObject);
            }
        }
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