<?php
namespace Favorite\Commands;
class Add extends \AbstractCommand implements \IFrameCommand {

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
		$frameResponseObject = $this->execute($frameResponseObject);
		return $frameResponseObject;
	}
	public function execute (\FrameResponseObject $frameResponseObject) {
		$steam=$GLOBALS["STEAM"];
		$id = $this->id;
		if($this->params[1] == "group" || $this->params[1] == "user" ){
			$category = $this->params[1];
		}
		else{
			throw new \Exception("category isn't set");
		}
		if ($category == "user" && $id!=0) {
			$user = \steam_factory::get_object($steam->get_id(), $id);
			$user_data = $user->get_attributes(array(OBJ_NAME, OBJ_DESC, USER_FIRSTNAME, USER_FULLNAME, USER_EMAIL, USER_ADRESS, OBJ_ICON, "bid:user_callto", "bid:user_im_adress", "bid:user_im_protocol"));
			//    $user_email_forwarding = $user->get_email_forwarding();
		}
		elseif($category == "group" && $id!=0) {
			$group = \steam_factory::get_object($steam->get_id(), $id);
			$group_data = $group->get_attributes(array(OBJ_NAME, OBJ_DESC));
		}
		$user_favourites = \lms_steam::get_current_user()->get_buddies();
		if (count($user_favourites) == 0)
		$user_favourites = array();
		if ($category=="user")
		array_push($user_favourites, $user);
		else if ($category=="group")
		array_push($user_favourites, $group);

		\lms_steam::get_current_user()->set_attribute("USER_FAVOURITES", $user_favourites);
		//$frameResponseObject->setConfirmText(gettext("Favorite added successfully"));
		$frameResponseObject->setConfirmText("Favorit erfolgreich hinzugefügt!");

		$widget = new \Widgets\JSWrapper();
		$url = 'self.location.href="'.PATH_URL.'favorite/index'.'"';
		$widget->setJs($url);
		$frameResponseObject->addWidget($widget);
		return $frameResponseObject;






	}
}
?>