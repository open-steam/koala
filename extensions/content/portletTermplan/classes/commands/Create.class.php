<?php

namespace PortletTermplan\Commands;

class Create extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $id;
    private $content;
    private $rawHtmlWidget;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $params = $requestObject->getParams();
        $name = $params["title"];
        $column = $params["id"];
        $version = "1.0";
        $desc = $params["desc"];

        $startD = $params["startDate"];
        $endD = $params["endDate"];
        $startDateArray = array();
        $startDateArray = explode(".", $startD);
        $endDateArray = array();
        $endDateArray = explode(".", $endD);

        $term0 = $params["term0"];
        $term1 = $params["term1"];
        $term2 = $params["term2"];
        $term3 = $params["term3"];
        $term4 = $params["term4"];
        $term5 = $params["term5"];
        //check diffrent types of parameter
        if (is_string($column)) {
            $columnObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $column);
        } else {
            $columnObject = $column;
        }

        if($name == ""){
    			$name = " ";
    		}

        //create
        $termPlanObject = \steam_factory::create_container($GLOBALS["STEAM"]->get_id(), $name, $columnObject);

        $pollTopic = $desc;
        $startDate = array("day" => $startDateArray[0], "month" => $startDateArray[1], "year" => $startDateArray[2],);
        $endDate = array("day" => $endDateArray[0], "month" => $endDateArray[1], "year" => $endDateArray[2],);
        $options = array($term0, $term1, $term2, $term3, $term4, $term5);
        $optionsVotecount = array(0, 0, 0, 0, 0, 0);

        $termPlanContent = array("end_date" => $endDate,
            "options" => $options,
            "options_votecount" => $optionsVotecount,
            "poll_topic" => $pollTopic,
            "start_date" => $startDate,
        );

        $termPlanObject->set_attributes(array(
            OBJ_DESC => $name,
            OBJ_TYPE => "container_portlet_bid",
            "bid:portlet" => "termplan",
            "bid:portlet:version" => $version,
            "bid:portlet:content" => $termPlanContent,
        ));

        $termChoices = "0"; //initial value
        $termPlanObject->set_attribute("termChoices", $termChoices);

        //sanctions
        $everybody = \steam_factory::get_group($GLOBALS["STEAM"]->get_id(), "steam");
        $termPlanObject->set_sanction($everybody, SANCTION_READ | SANCTION_WRITE);
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
