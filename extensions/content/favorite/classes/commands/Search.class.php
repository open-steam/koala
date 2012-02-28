<?php
namespace Favorite\Commands;
class Search extends \AbstractCommand implements \IFrameCommand {

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
		//DEFINITION OF IGNORED USERS AND GROUPS
		$ignoredUser = array(0=>"postman", 1=>"root",2=>"guest");
		$ignoredGroups = array(0=>"sTeam", 1=>"admin");

		$steam = $GLOBALS["STEAM"];
		$action = (isset($_POST["action"]))?$_POST["action"]:"";
		$searchString = (isset($_POST["searchString"]))?$_POST["searchString"]:"";
		$searchType = (isset($_POST["searchType"]))?$_POST["searchType"]:"searchUser";
		$steamUser = \lms_steam::get_current_user();


		$searchResult = array();
		$min_search_string_count = 4;
		if ($action != ""){
			$searchString = trim($searchString);

			if (strlen($searchString) < $min_search_string_count){
				//$frameResponseObject->setProblemDescription(gettext("Search string too short"));
				$frameResponseObject->setProblemDescription("Länge der Suchanfrage zu klein! Eine Suchanfrage muss aus mindestens 4 Zeichen bestehen.");
			}
			else{
				/* prepare search string */

				$modSearchString = $searchString;
				if ($modSearchString[0] != "%")
				$modSearchString = "%" . $modSearchString;
				if ($modSearchString[strlen($modSearchString)-1] != "%")
				$modSearchString = $modSearchString . "%";

				$searchModule = $steam->get_module("searching");
				$searchobject = new \searching($searchModule);
				$search = new \search_define();

					
				if ($searchType == "searchUser"){
					$search->extendAttr("OBJ_NAME", \search_define::like($modSearchString));
					$resultItems = $searchobject->search($search, CLASS_USER);
					foreach($resultItems as $resultItem){
						$id = $resultItem->get_id();
						$resultItemName[$id] = $resultItem->get_name(1);
					}
				}
				elseif($searchType == "searchGroup"){
					$search->extendAttr("GROUP_NAME", \search_define::like($modSearchString));
					$resultItems = $searchobject->search($search, CLASS_GROUP);
					foreach($resultItems as $resultItem) {
						$id = $resultItem->get_id();
						$resultItemName[$id] = $resultItem->get_groupname(1);
					}
				}
				elseif($searchType == "searchUserFullname"){
					$cache = get_cache_function( $steamUser->get_name(), 60 );
					$resultUser = $cache->call( "lms_steam::search_user", $searchString, "name" );
					$resultItems=array();
					for($i=0;$i<count($resultUser);$i++){
						$resultItems[$i]=\steam_factory::get_object($steam->get_id(), $resultUser[$i]["OBJ_ID"]);
					}

					foreach($resultItems as $resultItem){
						$id = $resultItem->get_id();
						$resultItemName[$id] = $resultItem->get_name();
					}
				}
				if($searchType!="searchUserFullname"){
					$result = $steam->buffer_flush();
				}
				else{
					$result=array();
					$counter=0;
					foreach($resultItems as $resultItem){
						$result[$resultItem->get_name()] = $resultItem->get_id();
						$counter++;

					}
				}
				$helper=array();
				
				foreach($resultItems as $resultItem){
					$id = $resultItem->get_id();
					
					if($resultItem instanceof \steam_object){
						$helper[$resultItem->get_name()] = $id;
					}
					
					if($resultItem instanceof \steam_group){
						$helper[$resultItem->get_groupname()] = $id;
					}
					
					$resultItemName[$id] = $result[$resultItemName[$id]];
					$searchResult[] = $resultItemName[$id];
				}
			}
		}
		
		
		
		// sort favourites
		natcasesort($searchResult);
		$content=\Favorite::getInstance()->loadTemplate("fav_search.html");
		$content->setVariable("TITLE", "Favoritensuche");

		$content->setVariable("SEARCH","Suche");
		$content->setVariable("BUTTON_LABEL", "Suchen");

		
		$content->setVariable("GROUPS","Gruppen");
		$content->setVariable("USER_LOGIN","Benutzer (Login)");
		$content->setVariable("USER_FULLNAME", "Benutzer (Namen)");

		if($action != ""){
			$loopCount = 0;
			if($searchType=="searchUser" || $searchType=="searchUserFullname"){
				$category="user";
			}
			else{
				$category="group";
			}
			foreach($searchResult as $resultEntry){
				$content->setVariable("SEARCH_RESULTS", "Suchergebnisse");
				$b = false;
				if($searchType!="searchUserFullname"){
					$urlId=$helper[$resultEntry];
				}
				else{
					$urlId=$resultEntry;
				}
				if ($category == "user"){
					foreach ($ignoredUser as $ignore){
						if($ignore == $resultEntry){
							$b = true;
						}
					}
				}
				if ($category == "group"){
					foreach ($ignoredGroups as $ignore){
						if($ignore == $resultEntry){
							$b = true;
						}
					}
				}
				if(!$b){
					if($category == "user"){
						$content->setCurrentBlock("BLOCK_SEARCH_RESULTS");
						$content->setVariable("BUDDY_NAME", PATH_URL."profile/index/" . $resultEntry ."/");

						$resultUser = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $urlId);
						
						$fullname = $resultUser->get_full_name();
						$content->setVariable("BUDDY_NAME1",$fullname);
						$picId = $resultUser->get_attribute("OBJ_ICON")->get_id();
						$content->setVariable("BUDDY_PIC_LINK", PATH_URL."download/image/".$picId."/60/40/" );
						if($steamUser->get_id() == $resultUser->get_id()){
							$content->setVariable("ALREADY_BUDDY","Das bist Du!");

						}
						elseif(!($steamUser->is_buddy($resultUser))){
							$content->setVariable("ADD_FAVORITE_BUDDY", "Favorit hinzufügen");

							$content->setVariable("FAVORITE_BUDDY_LINK", PATH_URL."favorite/add/". $urlId . "/" . $category . "/");
						}
						else{
							$content->setVariable("ALREADY_BUDDY", "Bereits Teil der Favoritenliste");
							

						}
						$content->parse("BLOCK_SEARCH_RESULTS");
						$loopCount++;
					}
					else if($category == "group"){
						$content->setCurrentBlock("BLOCK_GROUP_LIST");
						$content->setVariable("GROUP_NAME",$resultEntry);
						$resultGroup = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $urlId);

						$groupDesc=$resultGroup->get_attribute("OBJ_DESC");
						$content->setVariable("GROUP_DESC",$groupDesc);
						if(!($steamUser->is_buddy($resultGroup))){

							$content->setVariable("ADD_FAVORITE_GROUP", "Favorit hinzufügen");

							$content->setVariable("FAVORITE_GROUP_LINK", PATH_URL."favorite/add/". $urlId . "/" . $category . "/");
						}
						else{
							$content->setVariable("ALREADY_GROUP",  "Bereits Teil der Favoritenliste");
							
						}
						$content->parse("BLOCK_GROUP_LIST");
						$loopCount++;
					}
				}
			}


			if($loopCount == 0 || (count($searchResult) == 0)){
				$content->setVariable("NO_RESULT", "Suchanfrage ergab keinen Treffer");
			}

		}


		$headline = new \Widgets\Breadcrumb();
		$headline->setData(array(array("name"=>"Profil", "link"=>PATH_URL."profile/index/"),array("name"=>"Favoriten", "link"=>PATH_URL."favorite/index/"),array("name"=>"Favoritensuche")));

		$rawHtml=new \Widgets\RawHtml();
		$rawHtml->setHtml($content->get());
		$frameResponseObject->addWidget($headline);
		$frameResponseObject->addWidget($rawHtml);
		return $frameResponseObject;
	}
}
?>