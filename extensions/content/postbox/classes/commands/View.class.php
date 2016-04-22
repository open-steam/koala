<?php

namespace Postbox\Commands;

class View extends \AbstractCommand implements \IFrameCommand {

    private $params;
    private $id;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        isset($this->params[0]) ? $this->id = $this->params[0] : "";
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject) {
        $child = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
        $obj = $child->get_environment();
        $checkAccessWrite = $obj->check_access_write();
        $deadlineDateTime = $obj->get_attribute("bid:postbox:deadline");
        $isDeadlineSet = true;

        if ($deadlineDateTime == "" || $deadlineDateTime == 0) {
            $isDeadlineSet = false;
        }
        if ($isDeadlineSet) {
            //determine current date
            $now = mktime(date("H"), date("i"), 0, date("n"), date("j"), date("Y"));
            //compute Deadline
            $deadlineArray = explode(" ", $deadlineDateTime);
            //0 -> day, 1 -> month, 2 -> year
            $deadlineDate = explode(".", $deadlineArray[0]);
            // 0 -> hour, 1 -> minute
            $deadlineTime = explode(":", $deadlineArray[1]);
            $deadline = mktime($deadlineTime[0], $deadlineTime[1], 0, $deadlineDate[1], $deadlineDate[0], $deadlineDate[2]);

            $isDeadlineEnd = false;
            if ($now > $deadline) {
                $isDeadlineEnd = true;
            }
        }



        //Falls bereits eine Abgabe abgegeben wurde.
        /* $inventory = $obj->get_inventory();
          $elementNames = array();
          foreach($inventory as $index => $ele){
          $elementNames[$index] = $ele->get_name();
          }
          //compare the names

         */
        //  $headlineHtml = new \Widgets\RawHtml();
        //  $headlineHtml->setHtml('<h1 class="headline">' . $obj->get_name() . '</h1>');
        $this->getExtension()->addJS();
        $headlineHtml = new \Widgets\Breadcrumb();
        $headlineHtml->setData(array("", array("name" => "<img src=\"" . PATH_URL . "explorer/asset/icons/mimetype/reference_folder.png\"></img> " . $obj->get_environment()->get_name() . " ")));


        $cssStyles = new \Widgets\RawHtml();
        $cssStyles->setCss('.attribute{width:150px;float:left;padding-left:50px;padding-top:5px;} .value{padding-top:5px;} .value-red{color:red;padding-top:5px;} .value-green{color:green;padding-top:5px;}
            #button{padding-left:20px;} .breadcrumb {
    padding-left: 50px;
    padding-right: 50px;0
} #postboxWrapper {
    padding-left: 50px;
    padding-right: 50px;
}');


        if ($checkAccessWrite) {
            $frameResponseObject->addWidget($cssStyles);
            $frameResponseObject->addWidget($headlineHtml);

            if (isset($isDeadlineEnd) && $isDeadlineEnd) {
                $deadlineEndHtml = new \Widgets\RawHtml();
                $deadlineEndHtml->setHtml('<div class="attribute">Status:</div><div class="value-red">Abgabefrist überschritten!</div>
                <div class="attribute">Abgabefrist:</div><div class="value">' . $deadlineDateTime . ' Uhr</div>');
                $frameResponseObject->addWidget($deadlineEndHtml);
            } else if (!$isDeadlineSet) {
                $noDeadlineHtml = new \Widgets\RawHtml();
                $noDeadlineHtml->setHtml('<div class="attribute">Status:</div><div class="value-green">Abgabe möglich!</div>
                <div class="attribute">Abgabefrist:</div><div class="value">-</div>');
                $frameResponseObject->addWidget($noDeadlineHtml);
            } else {
                $deadlineRunHtml = new \Widgets\RawHtml();
                $deadlineRunHtml->setHtml('<div class="attribute">Status:</div><div class="value-green">Abgabe möglich!</div>
                <div class="attribute">Abgabefrist:</div><div class="value">' . $deadlineDateTime . ' Uhr</div>');
                $frameResponseObject->addWidget($deadlineRunHtml);
            }
            $clearer = new \Widgets\Clearer();
            $frameResponseObject->addWidget($clearer);
            $frameResponseObject->addWidget($clearer);

            $loader = new \Widgets\Loader();
            $loader->setWrapperId("postboxWrapper");
            $loader->setMessage("Lade Abgaben...");
            $loader->setCommand("LoadPostbox2");
            $loader->setNamespace("Postbox");
            $loader->setParams(array("id" => $this->id));
            $loader->setElementId("postboxWrapper");
            $loader->setType("updater");

            $environmentData = new \Widgets\RawHtml();
            $environmentData->setHtml("<input type=\"hidden\" id=\"environment\" value=\"$this->id\">");

            $frameResponseObject->addWidget($environmentData);
            $frameResponseObject->addWidget($loader);
        } else {

        }
        return $frameResponseObject;
    }

}

?>
