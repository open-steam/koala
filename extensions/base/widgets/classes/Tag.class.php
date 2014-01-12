<?php

namespace Widgets;

class Tag extends Widget {
    private $keyword;
    
    public function setKeyword($kw){
        $this->keyword = $kw;
        
    }
    
        public function getHtml() {
        $html = '<script>function copyToTextInput(){
            var name = "'.$this->keyword.'";
            var valOld = $("input[type=text]")[1].value.trim();
            $("input[type=text]")[1].value = valOld + " " + name;
            $(".tag[name="+name+"]")[0].onclick="";
}</script><div class="tag" name="'.$this->keyword.'" onclick="copyToTextInput();">'.$this->keyword.'</div>';
        $css = '.tag{float:right;max-width:150;cursor:pointer;}';
        
        $rawHtml = new \Widgets\RawHtml();
        $rawHtml->setCss($css);
        $rawHtml->setHtml($html);
        
        return $rawHtml->getHtml();
    }    
}
?>
