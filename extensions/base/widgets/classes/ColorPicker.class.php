<?php
namespace Widgets;

class ColorPicker extends Widget {
	private $id="uniqueId";
        
        public function setId($i){
            $this->id = $i;
        } 

        public function getHtml() {
		$html = '<input class="ColorPicker" id="'.$this->id.'" value="#FFFFFE">';
                $id = "#".$this->id;
                $html.= "<script>";
                $html.= '$("'.$id.'").simpleColor();';
                $html.= "</script>";
                return $html;
                
            
	}
}
?>