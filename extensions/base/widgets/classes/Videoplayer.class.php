<?php
namespace Widgets;

class Videoplayer extends Widget {
    
    
        private $playerWidth = 450;
        private $playerHeight = 300;
    
	private $target = "";
        private $swfPath = "";
                //PATH_URL . "extensions/base/widgets/ui/swf/flowplayer-3.2.10.swf";
        
        public function setTarget($targetUrl){
            $this->target = $targetUrl;
        }
        
        public function setSwfPath($swfP){
            $this->swfPath = $swfP;
        }
        
        public function setWidth($value){
            $this->playerWidth = $value; 
        }
        
        public function setHeight($value){
            $this->playerHeight = $value; 
        }
        
        
	public function getHtml() {
		//if($this->target == ""){
                //    throw new \Exception("videoplayer: target-url isn't set.");
                //}else{
                    $this->getContent()->setVariable("TARGET_URL", $this->target);
                //}                
                if($this->swfPath == ""){
                    $this->getContent()->setVariable("PATH_SWF", PATH_URL . "styles/standard/javascript/Flowplayer/flowplayer-3.2.10.swf");
                }else{
                    $this->getContent()->setVariable("PATH_SWF", $this->swfPath);
                }
                
                $this->getContent()->setVariable("PLAYER_WIDTH", $this->playerWidth);
		$this->getContent()->setVariable("PLAYER_HEIGHT", $this->playerHeight);
		
                $this->getContent()->setVariable("PLAYERTAG_ID", uniqid());
		return $this->getContent()->get();
	}
}
?>