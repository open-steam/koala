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
        $currentSteamUserName = $GLOBALS["STEAM"]->get_current_steam_user()->get_name();

        $container = $obj->get_attribute("bid:postbox:container");
        $checkAccessWrite = $obj->check_access_write();
        $checkAccesRead = ($currentSteamUserName == "guest") ? false : true;
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
        $currentUserFullName = $GLOBALS["STEAM"]->get_current_steam_user()->get_full_name();
        $currentUserId = $GLOBALS["STEAM"]->get_current_steam_user()->get_id();

        /*   $inventory = $container->get_inventory();
          $index = -1;
          foreach ($inventory as $i => $ele) {
          $eleName = $ele->get_name();
          if ($eleName == $currentUserFullName) {
          $index = $i;
          break;
          }
          } */

        /*   if ($index != -1) {
          $lastChangeTimeStamp = $inventory[$index]->get_attribute("OBJ_LAST_CHANGED");
          $date = date("d.m.Y", $lastChangeTimeStamp);
          $time = date("H:i", $lastChangeTimeStamp);
          $dateTime = $date . " " . $time . "Uhr";
          } else {
          $dateTime = "-";
          } */
        
        $lastRelease = $obj->get_attribute("bid:postbox:lastobj");
        $lastReleaseCurrentUser = $lastRelease->get_attribute($currentUserId);
        if ($lastReleaseCurrentUser != 0) {
            $date = date("d.m.Y", $lastReleaseCurrentUser);
            $time = date("H:i", $lastReleaseCurrentUser);
            $dateTime = $date . " " . $time . "Uhr";
        } else {
            $dateTime = "-";
        }

       


        $this->getExtension()->addJS();
        $headlineHtml = new \Widgets\Breadcrumb();
        $headlineHtml->setData(array("", array("name" => "<img src=\"" . PATH_URL . "explorer/asset/icons/mimetype/reference_folder.png\"></img> " . $obj->get_name() . " ")));


        $cssStyles = new \Widgets\RawHtml();
        $cssStyles->setCss('.attribute{width:150px;float:left;padding-left:50px;padding-top:5px;} .value{padding-top:5px;} .value-red{color:red;padding-top:5px;} .value-green{color:green;padding-top:5px;}
            #button{padding-left:50px;padding-right: 50px;
                    }
            .breadcrumb {
                padding-left: 50px;
                padding-right: 50px;
            }
            #postboxWrapper {
                padding-left: 50px;
                padding-right: 50px;
            }');

//TODO:Überprüfe, ob Actionbar zwischen Schreib- und Berechtigungsrechten unterscheidet.
        if ($checkAccessWrite) {
            $actionBar = new \Widgets\ActionBar();
            $actionBar->setActions(array(
                array("name" => "Zu Ordner umwandeln", "ajax" => array("onclick" => array("command" => "Release", "params" => array("id" => $this->id), "requestType" => "data"))),
                array("name" => "Eigenschaften", "ajax" => array("onclick" => array("command" => "edit", "params" => array("id" => $this->id), "requestType" => "popup"))),
                array("name" => "Rechte", "ajax" => array("onclick" => array("command" => "Sanctions", "params" => array("id" => $this->id), "requestType" => "popup", "namespace" => "Explorer"))),
            ));
            $frameResponseObject->addWidget($actionBar);
            $frameResponseObject->addWidget($cssStyles);
            $frameResponseObject->addWidget($headlineHtml);

            $PATH_URL = PATH_URL;
            $jsWrapper = new \Widgets\JSWrapper();
            $jsWrapper->setPostJsCode(<<<END
                    
                    function releaseFolder(){
                        if (confirm('Das aktuelle Abgabefach in einen Ordner umgewandelt. Dieser Vorgang kann nicht rückgängig gemacht werden.')) { 
                            sendRequest('Release', {'id':'{$this->id}'}, '', 'data', function(){location.href="{$PATH_URL}explorer/index/{$this->id}";}, null);                           
                        }                     
                        return false;
                    
                    }
                    $(".left").attr("onclick", "releaseFolder();");
END
            );
            $frameResponseObject->addWidget($jsWrapper);
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
            $advice = new \Widgets\RawHtml();
            $advice->setHtml('<div class="attribute">Hinweis zu Rechten:</div><div class="value">Wenn man Schreibrechte hat, kann man Abgaben einsehen. Alle anderen angemeldeten Benutzer können Abgaben einreichen.</div>');
            $frameResponseObject->addWidget($advice);
            $clearer = new \Widgets\Clearer();
            $frameResponseObject->addWidget($clearer);
            $frameResponseObject->addWidget($clearer);

            $loader = new \Widgets\Loader();
            $loader->setWrapperId("postboxWrapper");
            $loader->setMessage("Lade Abgaben ...");
            $loader->setCommand("loadPostbox");
            $loader->setParams(array("id" => $container->get_id()));
            $loader->setElementId("postboxWrapper");
            $loader->setType("updater");

            $environmentData = new \Widgets\RawHtml();
            $environmentData->setHtml("<input type=\"hidden\" id=\"environment\" value=\"$this->id\">");

            $frameResponseObject->addWidget($environmentData);
            $frameResponseObject->addWidget($loader);
        } else if ($checkAccesRead) {
       
            $currentUserFullName = $GLOBALS["STEAM"]->get_current_steam_user()->get_full_name();

          /*  $inventory = $container->get_inventory();
            $index = -1;
            foreach ($inventory as $i => $ele) {
                $eleName = $ele->get_name();
                if ($eleName == $currentUserFullName) {
                    $index = $i;
                    break;
                }
            }
            if ($index != -1) {
                $lastChangeTimeStamp = $inventory[$index]->get_attribute("OBJ_LAST_CHANGED");
                $date = date("d.m.Y", $lastChangeTimeStamp);
                $time = date("H:i", $lastChangeTimeStamp);
                $dateTime = $date . " " . $time . " Uhr";
            } else {
                $dateTime = "-";
            } */
            $buttonHtml = new \Widgets\RawHtml();
            $buttonHtml->setHtml(<<<END
                        <br>
<div id="button" onclick="sendRequest('NewDocumentForm', {'id':{$container->get_id()}}, '', 'popup', null, null);return false;">
<button>Abgabe einreichen</button>
</div>
END
            );
            $buttonHtml->setJs('$(document).ready(function() {
    $("button").button();
  });');

            $frameResponseObject->addWidget($cssStyles);
            $frameResponseObject->addWidget($headlineHtml);
            $lastReleaseHtml = new \Widgets\RawHtml();
            $lastReleaseHtml->setHtml('<div class="attribute">Letzte Abgabe:</div><div class="value">' . $dateTime . '</div>
                ');
            if (isset($isDeadlineEnd) && $isDeadlineEnd) {
                $deadlineEndHtml = new \Widgets\RawHtml();
                $deadlineEndHtml->setHtml('<div class="attribute">Status:</div><div class="value-red">Abgabefrist überschritten!</div>
                <div class="attribute">Abgabefrist:</div><div class="value">' . $deadlineDateTime . ' Uhr</div>');

                $frameResponseObject->addWidget($deadlineEndHtml);
                $frameResponseObject->addWidget($lastReleaseHtml);
            } else if (!$isDeadlineSet) {
                $noDeadlineHtml = new \Widgets\RawHtml();
                $noDeadlineHtml->setHtml('<div class="attribute">Status:</div><div class="value-green">Abgabe möglich!</div>
                <div class="attribute">Abgabefrist:</div><div class="value">-</div>');
                $frameResponseObject->addWidget($noDeadlineHtml);
                $frameResponseObject->addWidget($lastReleaseHtml);
                $frameResponseObject->addWidget($buttonHtml);
            } else {
                $deadlineRunHtml = new \Widgets\RawHtml();
                $deadlineRunHtml->setHtml('<div class="attribute">Status:</div><div class="value-green">Abgabe möglich!</div>
                <div class="attribute">Abgabefrist:</div><div class="value">' . $deadlineDateTime . ' Uhr</div>');
                $frameResponseObject->addWidget($deadlineRunHtml);
                $frameResponseObject->addWidget($lastReleaseHtml);
                $frameResponseObject->addWidget($buttonHtml);
            }
        } else {
            
        }
        return $frameResponseObject;
    }

}

?>