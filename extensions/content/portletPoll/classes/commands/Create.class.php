<?php

namespace PortletPoll\Commands;

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
        $column = $params["parent"];
        $version = "1.0";
        $input0 = $params["input0"];
        $input1 = $params["input1"];
        $input2 = $params["input2"];
        $input3 = $params["input3"];
        $input4 = $params["input4"];
        $input5 = $params["input5"];

        $desc = $params["desc"];


        $startDateArray = array();
        $startDateArray = explode(".", $params["startDate"]);

        $endDateArray = array();
        $endDateArray = explode(".", $params["endDate"]);

        //if one of the date fields is empty, fill it with the current date
        $fallbackDate = array("d", "m", "Y");
        for ($i = 0; $i <= 2; $i++) {
            if (!isset($startDateArray[$i]) || $startDateArray[$i] == "") {
                $startDateArray[$i] = date($fallbackDate[$i]);
            }

            if (!isset($endDateArray[$i]) || $endDateArray[$i] == "") {
                if ($i == 1) { //set the end deadline to the next month
                    $endDateArray[$i] = date($fallbackDate[$i]) + 1;
                } else {
                    $endDateArray[$i] = date($fallbackDate[$i]);
                }
            }
        }

        //check diffrent types of parameter
        if (is_string($column)) {
            $columnObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $column);
        } else {
            $columnObject = $column;
        }

        if ($name == "") {
            $name = " ";
        }

        //create
        $pollObject = \steam_factory::create_container($GLOBALS["STEAM"]->get_id(), $name, $columnObject);

        $startDate = array("day" => $startDateArray[0], "month" => $startDateArray[1], "year" => $startDateArray[2],);
        $endDate = array("day" => $endDateArray[0], "month" => $endDateArray[1], "year" => $endDateArray[2],);
        $options = array($input0, $input1, $input2, $input3, $input4, $input5);
        $optionsVotecount = array(0, 0, 0, 0, 0, 0);


        $pollContent = array("end_date" => $endDate,
            "options" => $options,
            "options_votecount" => $optionsVotecount,
            "poll_topic" => $desc,
            "start_date" => $startDate,
        );


        $pollObject->set_attributes(array(
            OBJ_DESC => $name,
            OBJ_TYPE => "container_portlet_bid",
            "bid:portlet" => "poll",
            "bid:portlet:version" => $version,
            "bid:portlet:content" => $pollContent,
        ));

        //sanctions
        $everybody = \steam_factory::get_group($GLOBALS["STEAM"]->get_id(), "steam");
        $pollObject->set_sanction($everybody, SANCTION_READ | SANCTION_WRITE);
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
