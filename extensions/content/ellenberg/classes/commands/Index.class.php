<?php

namespace Ellenberg\Commands;

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
        
        
        //this is the header
        $headlineHtml = new \Widgets\Breadcrumb();
        $headlineHtml->setData(array("", array("name" => "<img src=\"" . PATH_URL . "explorer/asset/icons/mimetype/old/annotation.gif\" /> " . $object->get_name() . " - ein Ellenbergtool ")));


        //headline
        $frameResponseObject->addWidget($headlineHtml);

        $cssStyles = new \Widgets\RawHtml();
        $cssStyles->setCss('
            .attribute{width:150px;float:left;padding-left:50px;padding-top:5px;} 
            .value{margin-left:200px;padding-top:5px;} 

            .breadcrumb {
                padding-left: 50px;
                padding-right: 50px;
            }
        ');
        $frameResponseObject->addWidget($cssStyles);

        
        
        
        $hint = new \Widgets\RawHtml();
        $hint->setHtml('<p class ="breadcrumb">Mit den Links werden Sie zu einer externen Plattform weitergeleitet.</p>');
        $frameResponseObject->addWidget($hint);
        
        
        $ellenbergUrl = 'http://'.$object->get_Attribute("ELLENBERG_URL").'/#';
        
        $generatorPlatform = new \Widgets\RawHtml();
        $generatorPlatform->setHtml('<div class="attribute">Generator Plattform:</div><div class="value"><a target="_blank" href="'.$ellenbergUrl.'scenario/'.$object->get_Attribute("ELLENBERG_ID").'" > '.$ellenbergUrl.'scenario/'.$object->get_Attribute("ELLENBERG_ID").'</a></div>');
        $frameResponseObject->addWidget($generatorPlatform);
        
        $auswertungsPlatform = new \Widgets\RawHtml();
        $auswertungsPlatform->setHtml('<div class="attribute">Auswertungs Plattform:</div><div class="value"><a target="_blank" href="'.$ellenbergUrl.'summary/'.$object->get_Attribute("ELLENBERG_ID").'" > '.$ellenbergUrl.'summary/'.$object->get_Attribute("ELLENBERG_ID").'</a></div>');
        $frameResponseObject->addWidget($auswertungsPlatform);
        
        
        $environmentData = new \Widgets\RawHtml();
        $environmentData->setHtml("<input type=\"hidden\" id=\"environment\" value=\"$this->id\">");
        $frameResponseObject->addWidget($environmentData);
        
        
        return $frameResponseObject;
    }


}

?>