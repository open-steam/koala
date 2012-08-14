<?php

namespace Portal\Commands;

class Sort extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $id;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        if ($requestObject instanceof \UrlRequestObject) {
            $this->params = $requestObject->getParams();
            isset($this->params[0]) ? $this->id = $this->params[0] : "";
        } else if ($requestObject instanceof \AjaxRequestObject) {
            $this->params = $requestObject->getParams();
            isset($this->params["id"]) ? $this->id = $this->params["id"] : "";
        }
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        $portalObj = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
        $columnsObjArray = $portalObj->get_inventory();
        $columnsMapping = array();
        foreach ($columnsObjArray as $c) {
            $columnsMapping[$c->get_id()] = $c;
        }
      //  $portletsMapping = array();
      //  $portletsMapping[] = array();
      //  $portletsMappingName = array();
      //  $portletsMappingName[] = array();

        $html = '<div class="sort">';
        foreach ($columnsMapping as $id => $column) {
            $inventory = $column->get_inventory();
            $html .= '<ul id="' . $id . '" class="columnSort">';
            foreach ($inventory as $e) {
                $eId = $e->get_id();
                $eName = $e->get_name();
        //        $portletsMapping[$id][$eId] = $e;
        //        $portletsMappingName[$id][$eId] = $eName;
                $html .= '<li id="' . $eId . '" class="elementSort">' . $eName . '</li>';
            }
            $html .= '</ul>';
        }
        $html .= "</div>";
        
        $string = "";
        $i=1;
        foreach($columnsMapping as $id => $c){
            if(!($i == 1)){
                $string .= ", ";
            }
            $string .= "#".$id;
            $i++; 
        }
        $string = trim($string);
        $rawHtml = new \Widgets\RawHtml();
        $rawHtml->setCss('
        .columnSort {list-style-type: none; margin: 0; padding: 0; float: left; margin-right: 10px; background: #eee;; padding: 5px; width: 143px;}
        .elementSort{ margin: 5px; padding: 5px; width: 120px;background: #396d9c;
	background: -webkit-gradient(linear, left top, left bottom, from(#7599bb),to(#356fa1));
	background: -moz-linear-gradient(top,#7599bb,#356fa1); color: #ffffff; } 
        
                

               
	

');
        $js = <<<END
        <script>
	$(function() {
		$( "{$string}" ).sortable({
			connectWith: "ul",
                        update: function(event, ui){
                            var column = this.id;                
                            var itemList = $("#"+this.id).children();
                            var elements = "";
                            $(itemList).each(function(index,value){
                                var vID = $.trim(value.id);
                                if(vID != ""){
                                    elements += vID + ",";
                                }                                 
                            });
                            console.log(elements);                                            
                           // sendRequest("Update", { "id": column, "elements" : elements }, "", "data", null, null, "portal");
                
                           sendRequest("Update", { "id": column, "elements" : elements }, "", "data", function(response){jQuery('#dynamic_wrapper').remove(); jQuery("body").prepend('<div id="overlay" style="position: absolute; width: 2545px; height: 1469px; top: 491px; left: 0px; opacity: 0.8; background-color: white; z-index: 200;"></div>');sendRequest('Sort', {'id':'{$this->id}'}, '', 'popup', null, null);}, null, "portal");
                            console.log("Warum schließt sich jetzt das Fenster?"); 
                            
                            
                        }
                        
		});
                $( "{$string}" ).disableSelection();		
	});
      
       
	</script>
   
END
        
        ;
        
        $rawHtml->setHtml($html.$js);
        $jsWrapper = new \Widgets\JSWrapper();
        $jsWrapper->setJs($js);
        $dialog = new \Widgets\Dialog();
        $dialog->setWidth(600);
        $dialog->setTitle(" Sortieren des Portals »" . getCleanName($portalObj) . "«");       
        $dialog->setPositionX($this->params["mouseX"]);
        $dialog->setPositionY($this->params["mouseY"]);
        $dialog->addWidget($rawHtml);
        $ajaxResponseObject->setStatus("ok");
        $ajaxResponseObject->addWidget($dialog);
        $ajaxResponseObject->setStatus("ok");
	return $ajaxResponseObject;
    }

}

?>