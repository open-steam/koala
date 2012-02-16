<?php
namespace School\Model;
class FolderSchoolBookmark extends \AbstractObjectModel {
	
	public static function getSchoolBookmarkFolderObject() {
		$myuser = $GLOBALS["STEAM"]->get_current_steam_user();
		$object = $myuser->get_attribute("USER_SCHOOLBOOKMARKROOM");
		if (isset($object) && $object instanceof \steam_room && $object->get_attribute(OBJ_TYPE) === "container_schoolbookmarkroom") {
			return $object;
		} else {
			$object = \steam_factory::create_room($GLOBALS["STEAM"]->get_id(), "Schoolbookmarkroom", null);
			$object->set_attribute(OBJ_TYPE, "container_schoolbookmarkroom");
			$myuser->set_attribute("USER_SCHOOLBOOKMARKROOM", $object);
			return $object;
		}
	}
		
	public static function isObject(\steam_object $steamObject) {
		$myuser = $GLOBALS["STEAM"]->get_current_steam_user();
		$schoolbookmarksroom = $myuser->get_attribute("USER_SCHOOLBOOKMARKROOM");
		if (($schoolbookmarksroom instanceof \steam_object) && $schoolbookmarksroom->get_id() == $steamObject->get_id()) {
			return true;
		} else {
			return false;
		}
	}
	
}
?>