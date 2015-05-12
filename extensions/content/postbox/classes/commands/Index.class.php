<?php
// #3 des Tickets: welcher Hinweistext?
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
        $currentUser = $GLOBALS["STEAM"]->get_current_steam_user();
        $currentSteamUserName = $currentUser->get_name();

        /* So komme ich an eine Abgabe, falls eine vorhanden ist!
          $objPath = $obj->get_attribute("OBJ_PATH");
          $currentUserFullName = $currentUser->get_full_name();
          $filePath = $objPath . "/postbox_container/" . $currentUserFullName;
          $file = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $filePath);
         */

        $container = $obj->get_attribute("bid:postbox:container");
        $deadlineDateTime = $obj->get_attribute("bid:postbox:deadline");
        $checkAccessWrite = $obj->check_access_write();
        $checkAccesRead = ($currentSteamUserName == "guest") ? false : true;
        $isDeadlineSet = true;

        if (!preg_match("/^\d{1,2}\.\d{1,2}\.\d{4} \d{2}:\d{2}/isU", $deadlineDateTime)) {
            
            $isDeadlineSet = false;
            if($checkAccessWrite){
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

        //Falls bereits eine Abgabe abgegeben wurde.

        $objPath = $obj->get_attribute("OBJ_PATH");
        $currentUserFullName = $currentUser->get_full_name();
        $filePath = $objPath . "/postbox_container/" . $currentUserFullName;
        $file = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $filePath);

        if ($file instanceof \steam_container) {
            $lastReleaseCurrentUser = $file->get_attribute("OBJ_LAST_CHANGED");
        } else {
            $lastReleaseCurrentUser = 0;
        }


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
        $cssStyles->setCss('.attribute{width:150px;float:left;padding-left:50px;padding-top:5px;} .value{margin-left:200px;padding-top:5px;} .value-red{color:red;padding-top:5px;margin-left:200px;} .value-green{color:green;padding-top:5px;margin-left:200px;}
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
                array("name" => "Den aktuellen Briefkasten in einen Ordner umwandeln", "ajax" => array("onclick" => array("command" => "Release", "params" => array("id" => $this->id), "requestType" => "data"))),
                array("name" => "Eigenschaften", "ajax" => array("onclick" => array("command" => "edit", "params" => array("id" => $this->id), "requestType" => "popup"))),
                array("name" => "Rechte", "ajax" => array("onclick" => array("command" => "Sanctions", "params" => array("id" => $this->id), "requestType" => "popup"))),

                
            ));
            $frameResponseObject->addWidget($actionBar);
            $frameResponseObject->addWidget($cssStyles);
            $frameResponseObject->addWidget($headlineHtml);

            $PATH_URL = PATH_URL;
            $jsWrapper = new \Widgets\JSWrapper();
            $jsWrapper->setPostJsCode(<<<END
                    
                    function releaseFolder(){
                        if (confirm('Der aktuelle Briefkasten wird in einen Ordner umgewandelt. Dieser Vorgang kann nicht rückgängig gemacht werden.')) { 
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
            //$advice->setHtml('<div class="attribute">Hinweis zu Rechten:</div><div class="value">Wenn man Schreibrechte hat, kann man Abgaben einsehen. Alle anderen angemeldeten Benutzer können Abgaben einreichen.</div>');
            //$frameResponseObject->addWidget($advice);
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
            $isButtonSet = false;
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
                $isButtonSet = true;
              //  $frameResponseObject->addWidget($buttonHtml);
            } else {
                $deadlineRunHtml = new \Widgets\RawHtml();
                $deadlineRunHtml->setHtml('<div class="attribute">Status:</div><div class="value-green">Abgabe möglich!</div>
                <div class="attribute">Abgabefrist:</div><div class="value">' . $deadlineDateTime . ' Uhr</div>');
                $frameResponseObject->addWidget($deadlineRunHtml);
                $frameResponseObject->addWidget($lastReleaseHtml);
                $isButtonSet = true;
            }
            $advice = $obj->get_attribute("postbox:advice");

            if (!($advice === "" || $advice === 0)) {
                $adviceWidget = new \Widgets\RawHtml();
                $adviceWidget->setHtml(<<<END
                      <div class="attribute">Hinweis:</div><div class="value">{$advice}</div>
END
                );
                $frameResponseObject->addWidget($adviceWidget);
            }
            if ($isButtonSet) {
                $frameResponseObject->addWidget($buttonHtml);
            }
        } 
        return $frameResponseObject;
    }

}

?>