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
        $numberOfColumns = count($columnsObjArray);
        $columnsMapping = array();
        foreach ($columnsObjArray as $c) {
            $columnsMapping[$c->get_id()] = $c;
        }
      //  $portletsMapping = array();
      //  $portletsMapping[] = array();
      //  $portletsMappingName = array();
      //  $portletsMappingName[] = array();
        
        $widthHtml = "";
        $widthSum = 0;
        $html = '<div class="sort">';
        $widthArray = array();
        $counter = 0;
        foreach ($columnsMapping as $id => $column) {   
            $inventory = $column->get_inventory();
            $width = $column->get_attribute("bid:portal:column:width");
            if(strpos($width,"px")!==false) $width = substr($width, 0, -2);
            //echo $width;die;
            $widthArray[]=$width;
            $widthSum += $width;
            $ele = "";
            $widthHtml .= '<div class="columnWidth">Spaltenbreite: <input id="column_'.$id.'_'.$counter.'" value="'.$width.'" maxlength="3" type="text" size="3" onchange="columnWidth(id, value);"></input> '.$ele.'</div>';
            $html .= '<ul id="' . $id . '" class="columnSort">';
            $counter++;
            foreach ($inventory as $e) {
                $eId = $e->get_id();
                $eName = $e->get_attribute(OBJ_DESC);
        //        $portletsMapping[$id][$eId] = $e;
        //        $portletsMappingName[$id][$eId] = $eName;
                $html .= '<li id="' . $eId . '" class="elementSort">' . $eName . '</li>';
            }
            $html .= '</ul>';
        }
        $html .= "</div>";
        $html .= '<div id="hiddenBox" class="" style="display:none"></div>';
        $info = '<div class="info">Die korrekte Darstellung des Portals kann nur gewährleistet werden, wenn die Breite aller Spalten 
            addiert maximal 900 beträgt.</div>';
        $info.='<div class="currentValue">Die aktuelle Summe aller Spaltenbreiten beträgt: ';
        foreach($widthArray as $id => $value){
            $ele = $numberOfColumns-1 > $id ? " + " : " ";
            $info.= '<span id="sum_'.$id.'">'.$value."</span>".$ele; 
        }
        $info .= " = <span id=\"sum\">". $widthSum. "</span> (Empfehlung: 900) </div>";
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
            .sort{clear:both;}
        .columnWidth{float: left; margin-right: 10px;padding: 5px; width: 143px;}    
        .columnSort {list-style-type: none; margin: 0; padding: 0; float: left; margin-right: 10px; background: #eee;; padding: 5px; width: 143px;}
        .elementSort{ margin: 5px; padding: 5px; width: 120px;background: #396d9c;
	background: -webkit-gradient(linear, left top, left bottom, from(#7599bb),to(#356fa1));
	background: -moz-linear-gradient(top,#7599bb,#356fa1); color: #ffffff; } 
        
                

               
	

');
        $js = <<<END
        <script>
        if(parseInt($('#sum').text())>900){
            $('#sum').css('color', 'red');
        }
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
                           var changedElement = $(ui.item).attr("id");
                           sendRequest("Update", {"changedElement": changedElement, "id": column, "elements" : elements }, "", "data", function(response){ }, function(response){ }, "portal");
                           $('#hiddenBox').addClass("changed"); 
                            
                        }
                        
		});
                $( "{$string}" ).disableSelection();		
	});
        function columnWidth(id, value){
            var value1 = parseInt(value);
            if(value1 != value){
                alert("Es wurde eine ungültige Spaltenbreite eingetragen. Eine gültige Spaltenbreite besteht ausschließlich aus Zahlen, z.B. 300.");
            }else if(value >900){
                alert("Eine einzige Spalte darf eine Breite von 900 nicht überschreiten!");
            }else{
                var array = id.split("_");
                var objId = array[1];
                var col = array[2];
                sendRequest("UpdateWidth", {"id": objId, "value": value}, "", "data", function(response){ }, function(response){ }, "portal");
                $('#hiddenBox').addClass("changed"); 
                var old = parseInt($('#sum_'+col).text());
                $('#sum_'+col).text(value);
                var sum = $('#sum').text();
                sum = parseInt(sum);
                sum = sum - old + parseInt(value);
                $('#sum').text(sum);
                if(parseInt($('#sum').text())>900){
                    $('#sum').css('color', '#FF0066');
                }else{
                    $('#sum').css('color', 'black');
                }
            }
        
        
        }
      
       
	</script>
   
END
        
        ;
        
        $rawHtml->setHtml($info.$widthHtml.$html.$js);
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