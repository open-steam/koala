<?php

namespace PortletBookmarks\Commands;

class Create extends \AbstractCommand implements \IFrameCommand, \IIdCommand, \IAjaxCommand {

    private $isError = false;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        //create headline
        $params = $requestObject->getParams();

        if(intval($params["number"]) == 0){
          $number = 10;
        }
        else{
          $number = intval($params["number"]);
        }

        if (isset($params["id"])) {
            $id = $params["id"];
        } else {
            $this->isError = true;
            return false;
            ;
        }

        $version = "3.0";

        //check diffrent types of parameter
        if (isset($params["parent"])) {
            $column = $params["parent"];
        } else {
            $column = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
        }

        //create object
        $bookmarkPortlet = \steam_factory::create_container($GLOBALS["STEAM"]->get_id(), "Lesezeichen", $column);

        $bookmarkPortlet->set_attributes(array(
            OBJ_DESC => "Meine Lesezeichen",
            OBJ_TYPE => "container_portlet_bid",
            "bid:portlet" => "bookmarks",
            "bid:portlet:version" => $version,
            "PORTLET_BOOKMARK_COUNT" => $number
        ));
    }

    public function idResponse(\IdResponseObject $idResponseObject) {

    }

    public function frameResponse(\FrameResponseObject $frameResponseObject) {

    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        $ajaxResponseObject->setStatus("ok");

        $jswrapper = new \Widgets\JSWrapper();
        if ($this->isError) {
            $jswrapper->setJs('alert("Leider ist ein Fehler aufgetreten. Bitte wiederholen Sie den Vorgang!");');
        } else {
            $jswrapper->setJs(<<<END
		window.location.reload();
END
            );
        }

        $ajaxResponseObject->addWidget($jswrapper);
        return $ajaxResponseObject;
    }

}

?>
