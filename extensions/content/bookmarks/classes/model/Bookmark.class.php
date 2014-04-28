<?php
namespace Bookmarks\Model;
class Bookmark extends \AbstractObjectModel {

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
		$url = PATH_URL . "bookmarks/";
		return "<img id=\"{$itemId}_BookmarkMarker\" onclick=\"window.location = '{$url}'\" style=\"cursor: pointer\" title=\"Lesezeichen\" src=\"".PATH_URL."bookmarks/asset/icons/bookmark.png\"></img>";
	}

	private static function workBookmarkIds($bookmarks) {
		$result = array();
		foreach ($bookmarks as $bookmarkItem) {
			if ($bookmarkItem instanceof \steam_link) {
				$linkObject = $bookmarkItem->get_link_object();
				if ($linkObject instanceof steam_object) {
					$result[$bookmarkItem->get_id()] = $linkObject->get_id();
				}
			} else if ($bookmarkItem instanceof \steam_container) {
               try {
                       $result = array_merge($result, self::workBookmarkIds($bookmarkItem->get_inventory()));
               } catch (\Exception $e) {
                       \logging::write_log(LOG_ERROR, "bookmark access problem: #" . $bookmarkItem->get_id());
               }
           }
		}
		return $result;
	}

}
?>