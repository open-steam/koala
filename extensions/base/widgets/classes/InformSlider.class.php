<?php

namespace Widgets;

class InformSlider extends Widget{

    private $title;
    private $content;

    public function setTitle($title){
        $this->title = $title;
    }

    public function setContent($content){
        $this->content = $content;
    }

    public function getHtml() {
      $this->getContent()->setVariable("TITLE", $this->title);
      $this->getContent()->setVariable("CONTENT", $this->content);
      return $this->getContent()->get();
    }
}
