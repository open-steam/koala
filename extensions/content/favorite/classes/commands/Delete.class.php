<?php
namespace Favorite\Commands;
class Delete extends \AbstractCommand implements \IFrameCommand {

	private $params;
	private $id;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		isset($this->params[0]) ? $this->id = $this->params[0]: "";
		$currentUser=\lms_steam::get_current_user();
		$buddies = $currentUser->get_buddies();
		$changed = false;
		foreach($buddies as $i=>$buddy){
			if($buddy->get_id() == $this->id){
				unset($buddies[$i]);
				$changed=true;
			}
		}
                $buddies = array_values($buddies);
		if(!$changed){
			throw new \Exception("User isn't part of your buddylist");
		}else{
			$currentUser->set_buddies($buddies);
		}
	}
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$jsWrapper = new \Widgets\JSWrapper();
		$jsWrapper->setJs('self.location.href="'.PATH_URL."favorite/index/".'"');
		$frameResponseObject->addWidget($jsWrapper);
		return $frameResponseObject;
			
	}
}
?>
