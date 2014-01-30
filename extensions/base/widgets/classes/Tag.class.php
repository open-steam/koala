<?php

namespace Widgets;

class Tag extends Widget {
    private $keyword;
    
    public function setKeyword($kw){
        $this->keyword = $kw;
        
    }
    
        public function getHtml() {
        $html = '
<div class="tag" name="'.$this->keyword.'" onclick="copyToTextInput(&quot;'.$this->keyword.'&quot;);">'.$this->keyword.'</div>';
        $css = '.tag{float:right;max-width:150;cursor:pointer;}';
        
        $rawHtml = new \Widgets\RawHtml();
        $rawHtml->setHtml($html);
        $rawHtml->setCss($css);
        return $rawHtml->getHtml();
    }    
}
?>
