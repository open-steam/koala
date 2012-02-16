<?php
namespace Favorite\Commands;
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
		$frameResponseObject = $this->execute($frameResponseObject);
		return $frameResponseObject;
	}
	public function execute (\FrameResponseObject $frameResponseObject) {
		$steam = $GLOBALS["STEAM"];
		$steamUser =  \lms_steam::get_current_user();

		$buddies = $steamUser->get_buddies();
		$buddies_user = array();
		$buddies_group = array();
		$buddies_user_name = array();
		$buddies_group_name = array();

		foreach ($buddies as $buddy) {
			$id = $buddy->get_id();
			if($buddy instanceof \steam_user){
				$buddies_user[$id] = $buddy;
				$buddies_user_name[$id] = $buddy->get_name();
			}else if($buddy instanceof \steam_group) {
				$buddies_group[$id] = $buddy;
				$buddies_group_name[$id] = $buddy->get_groupname();
			}
		}
		foreach ($buddies_user_name as $id=>$val) {
			$buddies_user_name[$id] = $buddies_user_name[$id];
		}
		foreach ($buddies_group_name as $id=>$val) {
			$buddies_group_name[$id] = $buddies_group_name[$id];
		}

		// sort favourites
		natcasesort($buddies_user_name);
		natcasesort($buddies_group_name);
	
		//Setze Template...

		$content = \Favorite::getInstance()->loadTemplate("display_buddy.html");
		//$content->setVariable("BUDDYS", gettext("User"));
		$content->setVariable("BUDDYS", "Benutzer");

		$loopCount=0;
		foreach ($buddies_user_name as $id=>$buddy) {
			$content->setCurrentBlock("BLOCK_BUDDY_LIST");

			$user = \steam_factory::get_object($steam->get_id(), $id);
			$picId = $user->get_attribute("OBJ_ICON")->get_id();
			$content->setVariable("BUDDY_PIC_LINK", PATH_URL . "download/image/". $picId. "/60/60");
			$content->setVariable("BUDDY_NAME1", $user->get_attribute("USER_FIRSTNAME"). " ". $user->get_attribute("USER_FULLNAME") );
			$content->setVariable("BUDDY_NAME",PATH_URL."profile/index/".$buddy."/");
			//$content->setVariable("DELETE_BUDDY", gettext("Delete Favorite"));
			$content->setVariable("DELETE_BUDDY", "Favorit löschen");

			$content->setVariable("DELETE_BUDDY_LINK", PATH_URL."favorite/delete/" . $id . "/");
			$content->parse("BLOCK_BUDDY_LIST");
			$loopCount += 1;
		}

		if ($loopCount == 0) {
			//$content->setVariable("NO_BUDDYS", gettext("You don't have buddys"));

			$content->setVariable("NO_BUDDYS", "Es wurde kein Benutzer der Favoritenliste hinzugefügt");
		}
		//$content->setVariable("GROUPS", gettext("Groups"));
		$content->setVariable("GROUPS", "Gruppen");

		$loopCount=0;
		foreach ($buddies_group_name as $id=>$buddy) {
			$group=\steam_factory::get_object($steam->get_id(), $id);
			$groupDesc=$group->get_attribute("OBJ_DESC");
			$content->setCurrentBlock("BLOCK_GROUP_LIST");
			$content->setVariable("GROUP_NAME",$buddy);
			$content->setVariable("GROUP_DESC",$groupDesc);
			//$content->setVariable("DELETE_GROUP", gettext("Delete Favorite"));
			$content->setVariable("DELETE_GROUP", "Favorit löschen");

			$content->setVariable("DELETE_GROUP_LINK", PATH_URL."favorite/delete/" . $id . "/");

			$content->parse("BLOCK_GROUP_LIST");
			$loopCount += 1;

		}
		if ($loopCount == 0) {
			//$content->setVariable("NO_GROUPS", gettext("You are not a member of a group"));
			$content->setVariable("NO_GROUPS", "Es wurde keine Gruppe der Favoritenliste hinzugefügt");

		}
		$headline = new \Widgets\Breadcrumb();
		//$headline->setData(array(array("name"=>gettext("Profile"), "link"=>PATH_URL."profile/index/"),array("name"=>" / ".gettext("Favorites"))));
		$headline->setData(array(array("name"=>"Profil", "link"=>PATH_URL."profile/index/"),array("name"=>"Favoriten")));

		$actionBar = new \Widgets\ActionBar();
		//$actionBar->setActions(array(array("name"=>gettext("Search and add favorites"),"link"=> $this->getExtension()->getExtensionUrl()."search/")));
		$actionBar->setActions(array(array("name"=>"Favoriten suchen und hinzufügen","link"=> $this->getExtension()->getExtensionUrl()."search/")));

		$rawHtml=new \Widgets\RawHtml();
		$rawHtml->setHtml($content->get());
		$frameResponseObject->addWidget($headline);
		$frameResponseObject->addWidget($actionBar);
		$frameResponseObject->addWidget($rawHtml);
		return $frameResponseObject;

	}
}

?>