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

        $widthHtml = '<h3>Breite bearbeiten</h3>';
        $widthSum = 0;
        $html = '<h3>Sortieren (Drag & Drop)</h3>';
        $html .= '<div class="sort">';
        $widthArray = array();
        $counter = 0;

        foreach ($columnsMapping as $id => $column) {
            $inventory = $column->get_inventory();
            $width = $column->get_attribute("bid:portal:column:width");
            if(strpos($width,"px")!==false) $width = substr($width, 0, -2);
            $widthArray[]=$width;
            $widthSum += $width;
            $ele = "";
            $counter++;
            $widthHtml .= '<div class="columnWidth">Spalte ' . $counter . ': <input id="column_' . $counter . '_' . $id . '" value="'.$width.'" maxlength="3" type="number" size="3" min="100" max="900" onchange="columnWidth(id, value);"></input> '.$ele.'</div>';
            $html .= '<ul id="' . $id . '" class="columnSort">';
            foreach ($inventory as $e) {
                $eId = $e->get_id();
                $portletType = $e->get_attribute("bid:portlet");
                $eName = $e->get_attribute("OBJ_DESC");
                if($portletType === "headline" || $portletType === "media"){
                    $elementContent = $e->get_attribute("bid:portlet:content");
                    $eName = $elementContent["headline"];
                }

                $html .= '<li id="' . $eId . '" class="elementSort">' . $eName . '</li>';
            }
            $html .= '</ul>';
        }
        $html .= "</div>";
        $html .= '<div id="hiddenBox" class="" style="display:none"></div>';
        $info ='<div class="currentValue">Summe: ';
        $info .= "<span id=\"sum\"> ". $widthSum. "</span> (Empfehlung: 900) </div>";
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
        .sort{
          clear:both;
        }
        .columnWidth{
          margin-right: 10px;
          padding: 5px;
        }
        .currentValue{
          padding: 5px 0px 0px 5px;
        }
        .columnSort {
          list-style-type: none;
          margin: 0;
          padding: 0;
          float: left;
          margin-right: 10px;
          background: #eee;
          padding: 5px;
          width: 143px;
        }
        .elementSort{
          margin: 5px;
          padding: 5px;
          width: 120px;
          background: #396d9c;
          color: #FFFFFF;
	      }
        ');
        $js = <<<END
        <script>
        if(parseInt($('#sum').text()) > 900){
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

                        }

		});
                $( "{$string}" ).disableSelection();
	});
        function columnWidth(id, value){
            var value1 = parseInt(value);
            if(value1 != value){
                alert("Es wurde eine ungültige Spaltenbreite eingetragen. Eine gültige Spaltenbreite besteht ausschließlich aus Zahlen, z.B. 300.");
            }else if(value > 900){
                alert("Eine einzige Spalte darf eine Breite von 900 nicht überschreiten!");
            }else{
                var array = id.split("_");
                var objId = array[2];
                sendRequest("UpdateWidth", {"id": objId, "value": value}, "", "data", function(response){ }, function(response){ }, "portal");

                var sum = 0;
                var i = 1;

                while($("[id^=column_"+i+"]").length != 0){
                  var val = $("[id^=column_"+i+"]").val();
                  val = parseInt(val);
                  sum = sum + val;
                  i++;
                }

                $('#sum').text(sum);
                if(sum > 900){
                    $('#sum').css('color', '#FF0066');
                }else{
                    $('#sum').css('color', 'black');
                }
            }

        }

	</script>

END

        ;

        $rawHtml->setHtml($widthHtml.$info.$html.$js);
        $jsWrapper = new \Widgets\JSWrapper();
        $jsWrapper->setJs($js);
        $dialog = new \Widgets\Dialog();
        $dialog->setWidth(500);
        $dialog->setTitle(" Breite bearbeiten und Sortieren des Portals »" . getCleanName($portalObj) . "«");
        $dialog->setPositionX($this->params["mouseX"]);
        $dialog->setPositionY($this->params["mouseY"]);
        //this button does not have anny effect on the autosavingprocess. It is only there to close the dialog and make the user happy
        $dialog->setAutoSaveDialog(true);
        $dialog->setSaveAndCloseButtonLabel("Speichern & Schließen");
        $dialog->addWidget($rawHtml);
        $ajaxResponseObject->setStatus("ok");
        $ajaxResponseObject->addWidget($dialog);
        $ajaxResponseObject->setStatus("ok");
        return $ajaxResponseObject;
    }

}

?>
