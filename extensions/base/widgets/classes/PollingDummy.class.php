<?php
namespace Widgets;

class PollingDummy extends Widget {
	private $command = "Dummy";
        private $namespace = "Explorer";
	
	public function setCommand($commandName) {
		$this->command = $commandName;
	}
        public function setNamespace($nameSpace){
            $this->namespace = $nameSpace;
        }
	
	public function getHtml() {
		$script = 'setInterval(function(){
 sendRequest("'.$this->command.'", "", "", "data", function(response){ }, function(response){ }, "'.$this->namespace.'");
 }, 150000);';
                return "<script>".$script."</script>";
	}
}
?>