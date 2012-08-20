<?php

namespace Widgets;

class Helper extends Widget {

    private $elementId;
    private $html;

    public function setElementId($elementId) {
        $this->elementId = $elementId;
    }

    public function setHtml($html) {
        $this->html = $html;
    }

    public function getHtml() {
        //RESET ICON URL
        $pathURL = "http://3.bp.blogspot.com/_U1-L9RzFqFs/SKHg8kxx1FI/AAAAAAAAACE/0M3snWhAIU4/s400/fragezeichen.jpg";
        $this->getContent()->setVariable("ELEMENT_ID", $this->elementId);
        $this->getContent()->setVariable("TIPSY_TEXT", $this->html);
        $this->getContent()->setVariable("IMG_PATH", "http://3.bp.blogspot.com/_U1-L9RzFqFs/SKHg8kxx1FI/AAAAAAAAACE/0M3snWhAIU4/s400/fragezeichen.jpg");
        return $this->getContent()->get();
    }

}

?>
