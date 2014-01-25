<?php

namespace Widgets;

class Videoplayer extends Widget {

    private $playerWidth = 450;
    //private $playerHeight = 300;
    private $playerHeight = 338;
    private $target = "";
    private $swfPath = "";

    //PATH_URL . "extensions/base/widgets/ui/swf/flowplayer-3.2.10.swf";

    public function setTarget($targetUrl) {
        $this->target = $targetUrl;
    }

    public function setSwfPath($swfP) {
        $this->swfPath = $swfP;
    }

    public function setWidth($value) {
        $this->playerWidth = $value;
    }

    public function setHeight($value) {
        $this->playerHeight = $value;
    }

    public function getHtml() {
        if (isset($_SERVER['HTTP_USER_AGENT']) && (strstr($_SERVER['HTTP_USER_AGENT'], "iPad") || strstr($_SERVER['HTTP_USER_AGENT'], "iPhone") || strstr($_SERVER['HTTP_USER_AGENT'], "Android"))) {
            $this->getContent()->setCurrentBlock("BLOCK_VIDEO_HTML5");
        } else {
            $this->getContent()->setCurrentBlock("BLOCK_VIDEO");
        }

        \lms_portal::get_instance()->add_javascript_src("flowplayer", PATH_URL . "styles/standard/javascript/Flowplayer/flowplayer-3.2.9.min.js");
        //if($this->target == ""){
        //    throw new \Exception("videoplayer: target-url isn't set.");
        //}else{
        $this->getContent()->setVariable("TARGET_URL", $this->target);
        //}
        if ($this->swfPath == "") {
            $this->getContent()->setVariable("PATH_SWF", PATH_URL . "styles/standard/javascript/Flowplayer/flowplayer-3.2.10.swf");
        } else {
            $this->getContent()->setVariable("PATH_SWF", $this->swfPath);
        }

        $this->getContent()->setVariable("PLAYER_WIDTH", $this->playerWidth);
        $this->getContent()->setVariable("PLAYER_HEIGHT", $this->playerHeight);

        $this->getContent()->setVariable("PLAYERTAG_ID", uniqid());

        if (isset($_SERVER['HTTP_USER_AGENT']) && (strstr($_SERVER['HTTP_USER_AGENT'], "iPad") || strstr($_SERVER['HTTP_USER_AGENT'], "iPhone") || strstr($_SERVER['HTTP_USER_AGENT'], "Android"))) {
            $this->getContent()->parse("BLOCK_VIDEO_HTML5");
        } else {
            $this->getContent()->parse("BLOCK_VIDEO");
        }

        return $this->getContent()->get();
    }

}

?>