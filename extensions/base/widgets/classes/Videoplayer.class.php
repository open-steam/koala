<?php
namespace Widgets;

class Videoplayer extends Widget {
	private $target = "";
        private $swfPath = "";
                //PATH_URL . "extensions/base/widgets/ui/swf/flowplayer-3.2.10.swf";
        
        public function setTarget($targetUrl){
            $this->target = $targetUrl;
        }
        public function setSwfPath($swfP){
            $this->swfPath = $swfP;
        }
	public function getHtml() {
		if($this->target == ""){
                    throw new \Exception("videoplayer: target-url isn't set.");
                }else{
                    $this->getContent()->setVariable("TARGET_URL", $this->target);
                }
                if($this->swfPath == ""){
                    $this->getContent()->setVariable("PATH_SWF", PATH_URL . "styles/standard/javascript/Flowplayer/flowplayer-3.2.10.swf");
                }else{
                    $this->getContent()->setVariable("PATH_SWF", $this->swfPath);
                }
		return $this->getContent()->get();
	}
}
?>