<?php

namespace Map\Commands;

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
        $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
        if (!($object instanceof \steam_document)) {
            throw new \Exception("object isn't an instance of steam_document");
        }
        $objName = $object->get_name();
        if ((strpos($objName, ".kml") === false) && (strpos($objName, ".kmz") === false)) {
            throw new \Exception("object isn't a map");
        }
        $everyone = \steam_factory::get_group($GLOBALS["STEAM"]->get_id(), "everyone");

        if (!($object->check_access(SANCTION_READ, $everyone))) {
            if ($object->check_access(SANCTION_SANCTION)) {
                $label = new \Widgets\RawHtml();
                $label->setHtml('<div id="worldopen-label" class="worldopen">Damit eine Karte angezeigt werden kann, müssen die Rechte der Karte auf "Welt-Öffentlich" gesetzt werden.
Die Rechte können erweitert werden, indem sie die folgende Schaltfläche betätigen.</div>');


                $everyoneId = $everyone->get_id();
                $buttonHtml = new \Widgets\RawHtml();
                $buttonHtml->setHtml(<<<END
<div id="worldopen-button" onclick="setSanction()" class="worldopen">
<button>Rechte auf Welt-Öffentlich setzen</button>
</div>
END
                );
                $buttonHtml->setJs(<<<END
       function setSanction(){
        sendRequest("UpdateSanctions", { "id":"{$this->id}" , "type": "acq", "value": "" }, "", "data", "", null, "explorer");
        sendRequest("UpdateSanctions", { "id":"{$this->id}" , "type": "crude", "value": "server_public" }, "", "data", function(response){location.reload()}, null, "explorer");
}
    $(document).ready(function() {
    $("button").button();
  });

END
                );
                $buttonHtml->setCss(<<<END
   .worldopen{
       margin-top: 20px;
       margin-left: 50px;
       margin-right: 50px;
}
#worldopen-warning{
    color:red;
}
END
                );

                $warningHtml = new \Widgets\RawHtml();
                $warningHtml->setHtml('<div id="worldopen-warning" class="worldopen">
Hinweis: "Welt-Öffentlich" bedeutet, dass jeder Benutzer berechtigt ist die Kartendatei zu lesen
und zwar unabhängig davon, ob der Benutzer angemeldet ist.    
</div>');

                $frameResponseObject->addWidget($label);
                $frameResponseObject->addWidget($buttonHtml);
                $frameResponseObject->addWidget($warningHtml);
                return $frameResponseObject;
            } else if (!($object->check_access(SANCTION_READ))) {
                $accessDeniedHtml = new \Widgets\RawHtml();
                $accessDeniedHtml->setHtml('<div id="map-access-denied" class="worldopen">Sie verfügen nicht über die notwendige Berechtigung, um sich den gewünschten Inhalt anzeigen zu lassen.</div>');
                $accessDeniedHtml->setCss(<<<END
   .worldopen{
       margin-top: 20px;
       margin-left: 50px;
       margin-right: 50px;
}
END
                );
                $frameResponseObject->addWidget($accessDeniedHtml);
                return $frameResponseObject;
            } else {
                $mapLocked = new \Widgets\RawHtml();
                $mapLocked->setHtml('<div id="map-locked" class="worldopen">Diese Karte wurde nicht zur Anzeige freigeschaltet. Bitte kontaktieren Sie eine Person, die die Rechte dieser Karte verwaltet, um diese zur Anzeige freizuschalten.</div>');
                $mapLocked->setCss(<<<END
   .worldopen{
       margin-top: 20px;
       margin-left: 50px;
       margin-right: 50px;
}
END
                );
                $frameResponseObject->addWidget($mapLocked);
                return $frameResponseObject;
            }
        } else {
            $actionBar = new \Widgets\ActionBar();
            $downloadUrl = getDownloadUrlForObjectId($this->id);
            $actionBar->setActions(array(
                array("name" => "URL in neuem Fenster öffnen", "onclick" => "javascript:window.open('http://maps.google.de/maps?f=q&hl=de&q=" . $downloadUrl . "');return false;")
            ));

            $rawHtml = new \Widgets\RawHtml();
            $rawHtml->setHtml("<iframe height=\"800px\" width=\"100%\" src=\"http://maps.google.de/maps?f=q&hl=de&q=" . $downloadUrl . "&output=embed\" scrolling=\"yes\"></iframe>");
            $frameResponseObject->setTitle($objName);
            $frameResponseObject->addWidget($actionBar);
            $frameResponseObject->addWidget($rawHtml);
            return $frameResponseObject;
        }
    }

}

?>