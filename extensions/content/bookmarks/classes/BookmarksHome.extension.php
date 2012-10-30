<?php 
class BookmarksHome extends AbstractExtension implements IHomeExtension {
	
	public function getName() {
		return "BookmarksHome";
	}
	
	public function getDesciption() {
		return "Home Extension for Bookmarks.";
	}
	
	public function getVersion() {
		return "v1.0.0";
	}
	
	public function getAuthors() {
		$result = array();
		$result[] = new Person("Dominik", "Niehus", "nicke@uni-paderborn.de");
		return $result;
	}
	
	public function getWidget() {
                $box = new \Widgets\Box();
		$box->setId(\BookmarksHome::getInstance()->getId());
		$box->setTitle("Meine Lesezeichen");
		$box->setTitleLink(PATH_URL . "bookmarks/");
		$loader = new \Widgets\DivLoader();
		$loader->setWrapperId("bookmarksWrapper");
		$loader->setMessage("Lade Lesezeichen ...");
		$loader->setCommand("loadRecentBookmarks");
		$loader->setNamespace("Bookmarks");
		
                try{
                    $object = $GLOBALS["STEAM"]->get_current_steam_user()->get_attribute("USER_BOOKMARKROOM");
                    
                    if (!is_object($object)){
                        throw new steam_exception;
                    }
                    $id = $object->get_id();
                }  catch (Exception $e){
                    //fallback
                    $user = $GLOBALS["STEAM"]->get_current_steam_user();
                    $id = $user->get_workroom()->get_id();
                }
        
                $loader->setParams(array("id"=>$id));
		
                $loader->setElementId("bookmarksWrapper");
		$loader->setType("updater");
		$box->addWidget($loader);
		$box->setContent($loader->getHtml());
		$box->setContentMoreLink(PATH_URL . "bookmarks/");
		return $box;
	}
}
?>