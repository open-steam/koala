<?php
namespace Bookmarks\Model;
class FolderBookmark extends \AbstractObjectModel {
		
	public static function isObject(\steam_object $steamObject) {
		$myuser = $GLOBALS["STEAM"]->get_current_steam_user();
		if ($myuser->get_attribute(USER_BOOKMARKROOM)->get_id() == $steamObject->get_id()) {
			return true;
		} else {
			return false;
		}
	}
	
}
?>