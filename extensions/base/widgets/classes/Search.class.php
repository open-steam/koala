<?php

namespace Widgets;

class Search extends Widget {

    private $id;
    private $autocomplete = array();
    private $popupMenu = false;
    private $value = "";

    public function setPopupMenu($popupMenu){
        if($popupMenu instanceof \Widgets\PopupMenu){
            $this->popupMenu = $popupMenu;
        }
    }
    public function setId($id) {
        $this->id = $id;
    }
    public function setValue($value) {
        $this->value = $value;
    }

    public function setAutocomplete($ac) {
        if (is_array($ac)) {
            $this->autocomplete = $ac;
        } else {
            $array[] = $ac;
            $this->autocomplete = $array;
        }
    }

    public function addAutocompleteValue($value) {
        if (!is_array($value)) {
            $this->autocomplete[] = $value;
        }
    }

    public function getHtml() {
        if (!isset($this->id)) {
            $this->id = rand();
        }
        $content = $this->getContent();
        $content->setVariable("ID", $this->id);
        $acValuesString = "";

        $this->autocomplete = array_unique($this->autocomplete);
        foreach ($this->autocomplete as $acValue) {
            $acValuesString .= "'" . $acValue . "'" . ",";
        }

        $acValuesString = substr($acValuesString, 0, (strlen($acValuesString) - 1));
        $content->setVariable("AUTOCOMPLETE_VALUES", $acValuesString);

        if($this->popupMenu instanceof \Widgets\PopupMenu){
            $content->setVariable("POPUPMENU_HTML", $this->popupMenu->getHtml());
        }else{
            $content->setVariable("POPUPMENU_HTML", "");
        }
        $content->setVariable("VALUE", $this->value); 

        return $content->get();
    }

}

?>
