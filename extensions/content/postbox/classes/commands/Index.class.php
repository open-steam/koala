<?php
namespace Postbox\Commands;

class Index extends \AbstractCommand implements \IFrameCommand {

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

        $obj = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);

        $container = $obj->get_attribute("bid:postbox:container");

        if(!$container instanceof \steam_container){
            $rawHtml = new \Widgets\RawHtml();
            $rawHtml->setHtml("Dieses Objekt ist kein Briefkastenobjekt.");
            $frameResponseObject->addWidget($rawHtml);
            return $frameResponseObject;
        }

        $deadlineDateTime = $obj->get_attribute("bid:postbox:deadline");

        //depending on the serverconfiguration (API_DOUBLE_FILENAME_NOT_ALLOWED) you need read rights and insert rights or only insert rights
        //required sanctions for inner container
        $requiredSanctionsForInnerContainer = SANCTION_READ | SANCTION_INSERT;
        if (defined("API_DOUBLE_FILENAME_NOT_ALLOWED") && API_DOUBLE_FILENAME_NOT_ALLOWED){
            //if API_DOUBLE_FILENAME_NOT_ALLOWED is false, we only need INSERT rights (and don't need to check whether there already exists a file with the same name)
            $requiredSanctionsForInnerContainer = SANCTION_INSERT;
        }

        $checkAccessInsert = $container->check_access($requiredSanctionsForInnerContainer);
        $checkAccessAdmin = $obj->check_access(SANCTION_ALL);

        $isDeadlineSet = true;

        if (!preg_match("/^\d{1,2}\.\d{1,2}\.\d{4} \d{2}:\d{2}/isU", $deadlineDateTime)) {
            $isDeadlineSet = false;
            if($checkAccessAdmin){
                $obj->set_attribute("bid:postbox:deadline", "");
            }
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

        $this->getExtension()->addJS();
        $this->getExtension()->addCSS();

        $headlineHtml = new \Widgets\Breadcrumb();
        $headlineHtml->setData(array("", array("name" => "<svg style='width:16px; height:16px; color:#3a6e9f; top:3px; position:relative;'><use xlink:href='" . PATH_URL . "explorer/asset/icons/mimetype/svg/postbox.svg#postbox'/></svg> " . $obj->get_name() . " ")));

        $cssStyles = new \Widgets\RawHtml();
        $cssStyles->setCss('
          .attribute{
            width:100px;
            float:left;
            padding-top:5px;
          }

          .value{
            margin-left:100px;
            padding-top:5px;
          }

          .value-red{
            color:red;
            padding-top:5px;
            margin-left:100px;
          }

          .value-green{
            color:green;
            padding-top:5px;
            margin-left:100px;
          }

          #button{
            width: 151px;
          }'
        );

        //TODO:Überprüfe, ob Actionbar zwischen Schreib- und Berechtigungsrechten unterscheidet.
        if ($checkAccessAdmin) {
            $actionBar = new \Widgets\ActionBar();
            $actionBar->setActions(array(
                //array("name" => "Den aktuellen Briefkasten in einen Ordner umwandeln", "ajax" => array("onclick" => array("command" => "Release", "params" => array("id" => $this->id), "requestType" => "data"))),
                //array("name" => "Eigenschaften", "ajax" => array("onclick" => array("command" => "edit", "params" => array("id" => $this->id), "requestType" => "popup"))),
                //array("name" => "Abgabe freischalten und Rechte verwalten", "ajax" => array("onclick" => array("command" => "Sanctions", "params" => array("id" => $this->id), "requestType" => "popup"))),
            ));
            $frameResponseObject->addWidget($actionBar);
            $frameResponseObject->addWidget($cssStyles);
            $frameResponseObject->addWidget($headlineHtml);

            /*
            $PATH_URL = PATH_URL;
            $jsWrapper = new \Widgets\JSWrapper();
            $jsWrapper->setPostJsCode(<<<END

                    function releaseFolder(){
                        if (confirm('Das aktuelle Abgabefach wird in einen Ordner umgewandelt. Dieser Vorgang kann nicht rückgängig gemacht werden.')) {
                            sendRequest('Release', {'id':{$this->id}}, '', 'data', function(){location.href="{$PATH_URL}explorer/index/{$this->id}";}, null);
                        }
                        return false;

                    }
                    $(".left").attr("onclick", "releaseFolder();");
                    END
            );
            $frameResponseObject->addWidget($jsWrapper);
            */

            if (isset($isDeadlineEnd) && $isDeadlineEnd) {
                $deadlineEndHtml = new \Widgets\RawHtml();
                $deadlineEndHtml->setHtml('<div class="attribute">Abgabefrist:</div><div class="value-red">' . $deadlineDateTime . ' Uhr</div>');
                $frameResponseObject->addWidget($deadlineEndHtml);
            } else if (!$isDeadlineSet) {
                $noDeadlineHtml = new \Widgets\RawHtml();
                $noDeadlineHtml->setHtml('<div class="attribute">Abgabefrist:</div><div class="value">keine</div>');
                $frameResponseObject->addWidget($noDeadlineHtml);
            } else {
                $deadlineRunHtml = new \Widgets\RawHtml();
                $deadlineRunHtml->setHtml('<div class="attribute">Abgabefrist:</div><div class="value-green">' . $deadlineDateTime . ' Uhr</div>');
                $frameResponseObject->addWidget($deadlineRunHtml);
            }
            $advice = $obj->get_attribute("postbox:advice");
            $adviceWidget = new \Widgets\RawHtml();
            $adviceWidget->setHtml('<div class="attribute">Hinweis:</div><div class="value">'.$advice.'</div>');
            $frameResponseObject->addWidget($adviceWidget);

            $clearer = new \Widgets\Clearer();
            $frameResponseObject->addWidget($clearer);
            $frameResponseObject->addWidget($clearer);

            $loader = new \Widgets\Loader();
            $loader->setWrapperId("postboxWrapper");
            $loader->setMessage("Lade Abgaben...");
            $loader->setCommand("LoadPostbox");
            $loader->setNamespace("Postbox");
            $loader->setParams(array("id" => $container->get_id()));
            $loader->setElementId("postboxWrapper");
            $loader->setType("updater");

            $environmentData = new \Widgets\RawHtml();
            $environmentData->setHtml("<input type=\"hidden\" id=\"environment\" value=\"$this->id\">");

            $frameResponseObject->addWidget($environmentData);
            $frameResponseObject->addWidget($loader);

        } else if ($checkAccessInsert) {
            //here we are allowed to insert new documents to the postbox

            //check if there is already a submitted object
            $currentUser = $GLOBALS["STEAM"]->get_current_steam_user();
            $inventory = $container->get_inventory();
            $statusText = 'Es liegt keine Abgabe von dir vor.';
            $class = 'value';
            foreach ($inventory as $value){
              if($value->get_creator() == $currentUser){
                $statusText = 'Deine Abgabe war erfolgreich!';
                $class = 'value-green';
              }
            }

            $frameResponseObject->addWidget($cssStyles);
            $frameResponseObject->addWidget($headlineHtml);

            $lastReleaseHtml = new \Widgets\RawHtml();
            $lastReleaseHtml->setHtml('<div class="attribute">Status:</div><div class=' . $class . '>' . $statusText . '</div>');
            $isButtonSet = false;

            if (isset($isDeadlineEnd) && $isDeadlineEnd) { //deadline is over
                $deadlineEndHtml = new \Widgets\RawHtml();
                $deadlineEndHtml->setHtml('<div class="attribute">Abgabefrist:</div><div class="value-red">' . $deadlineDateTime . ' Uhr</div>');
                $frameResponseObject->addWidget($deadlineEndHtml);

            } else if (!$isDeadlineSet) { // no deadline set
                $noDeadlineHtml = new \Widgets\RawHtml();
                $noDeadlineHtml->setHtml('<div class="attribute">Abgabefrist:</div><div class="value">keine</div>');
                $frameResponseObject->addWidget($noDeadlineHtml);
                $isButtonSet = true;

            } else {
                $deadlineRunHtml = new \Widgets\RawHtml();
                $deadlineRunHtml->setHtml('<div class="attribute">Abgabefrist:</div><div class="value-green">' . $deadlineDateTime . ' Uhr</div>');
                $frameResponseObject->addWidget($deadlineRunHtml);
                $isButtonSet = true;
            }

            $advice = $obj->get_attribute("postbox:advice");

            if (!($advice === "" || $advice === 0)) {
                $adviceWidget = new \Widgets\RawHtml();
                $adviceWidget->setHtml('<div class="attribute">Hinweis:</div><div class="value">'.$advice.'</div>');
                $frameResponseObject->addWidget($adviceWidget);
            }

            $frameResponseObject->addWidget($lastReleaseHtml);

            if ($isButtonSet) {
                $buttonHtml = new \Widgets\RawHtml();
                $buttonHtml->setHtml(<<<END
                        <br>
<div id="button" onclick="sendRequest('NewDocumentForm', {'id':{$container->get_id()}}, '', 'popup', null, null);return false;">
<button class="bidButton">Abgabe einreichen</button>
</div>
END
            );
                $buttonHtml->setJs('$(document).ready(function() { $("button").button(); });');
                $frameResponseObject->addWidget($buttonHtml);
            }
        } else {
            $buttonHtml = new \Widgets\RawHtml();
            $buttonHtml->setHtml("Sie besitzen nicht die nötigen Rechte, um dieses Objekt zu sehen.");
            $frameResponseObject->addWidget($buttonHtml);
        }
        return $frameResponseObject;
    }
}

?>
