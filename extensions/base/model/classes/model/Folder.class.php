<?php
namespace Explorer\Model;
class Folder extends \AbstractObjectModel {
	
	public static function isObject(\steam_object $steamObject) {
		if ((($steamObject instanceof \steam_container) || ($steamObject instanceof \steam_room)) && !($steamObject instanceof \steam_user)) {
			return true;
		}
		return false;
	}
	
}
?>