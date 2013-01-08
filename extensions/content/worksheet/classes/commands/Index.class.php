<?php
namespace Worksheet\Commands;
class Index extends \AbstractCommand implements \IFrameCommand {
	
	private $params;
	private $id;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		isset($this->params[0]) ? $this->id = $this->params[0]: "";
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$myExtension = \Worksheet::getInstance();

                // chronic
                $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
                \ExtensionMaster::getInstance()->getExtensionById("Chronic")->setCurrentObject($object);
		
		$worksheet = new \Worksheet\Worksheet($this->id);

		$role = $worksheet->getRole();

		if ($role == "build") {
			
			header("Location: ".PATH_URL."worksheet/Build/".$this->id);
			
		} elseif ($role == "view") {
			
			header("Location: ".PATH_URL."worksheet/View/".$this->id);
			
		} elseif ($role == "edit") {
			
			header("Location: ".PATH_URL."worksheet/Edit/".$this->id);
			
		}


		return $frameResponseObject;
	}
}
?>