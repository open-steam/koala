<?php
namespace School\Model;
class SchoolBookmark extends \AbstractObjectModel {
	
	private static $bookmarkIds = null;
	
	public static function isObject(\steam_object $steamObject) {
		
	}
	
	private static function getBookmarkIds() {
		if (!self::$bookmarkIds) {
			self::$bookmarkIds = array();
			$bookmarks = $GLOBALS["STEAM"]->get_current_steam_user()->get_attribute(USER_BOOKMARKROOM);
			$bookmarkItems = $bookmarks->get_inventory();
			self::$bookmarkIds = self::workBookmarkIds($bookmarkItems);
			
		}
		return self::$bookmarkIds;
	}
	
	public static function isBookmark($itemId) {
		if (in_array($itemId, self::getBookmarkIds())) {
			return true;
		} else {
			return false;
		}
	}
	
	public static function getBookmark($itemId) {
		$ids = self::getBookmarkIds();
		return array_search($itemId, $ids);
	}
	
	public static function getMarkerHtml($itemId) {
		return "<img id=\"{$itemId}_BookmarkMarker\" onclick=\"event.stopPropagation(); params = new Object(); params.id = '".self::getBookmark($itemId)."'; sendRequest('RemoveBookmark', params, '', 'updater', function(event) { document.getElementById('{$itemId}_BookmarkMarker').src='".PATH_URL."bookmarks/asset/icons/star_inactive_16.png'}, null , 'bookmarks');\" style=\"cursor: pointer\" title=\"Lesezeichen\" src=\"".PATH_URL."bookmarks/asset/icons/star_16.png\"></img>";
	}
	
	private static function workBookmarkIds($bookmarks) {
		$result = array();
		foreach ($bookmarks as $bookmarkItem) {
			if ($bookmarkItem instanceof \steam_link) {
				$result[$bookmarkItem->get_id()] = $bookmarkItem->get_link_object()->get_id();
			} else if ($bookmarkItem instanceof \steam_container) {
				$result = array_merge($result, self::workBookmarkIds($bookmarkItem->get_inventory()));
			}
		}
		return $result;
	}
	
}
?>