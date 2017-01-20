<?php
namespace Explorer\Model;
class Sanction {
	
	public static function getMarkerHtml(\steam_object $steam_object, $aquireUsed = true) {
		$myUser = \lms_steam::get_current_user();
		if (!$steam_object->check_access_read($myUser)) {
			return "<img title=\"Kein Zugriff.\" src=\"".PATH_URL."explorer/asset/icons/no_access.png\"></img>";
		}
		if ($aquireUsed && $steam_object->get_acquire() instanceof \steam_room) {
			return "";
		}
		$onclick = "sendRequest('Sanctions', {'id':'{$steam_object->get_id()}'}, '', 'popup', null,null,'explorer'); return false;";
		if ($steam_object->check_access_read(\steam_factory::get_user($GLOBALS["STEAM"]->get_id(), STEAM_GUEST_LOGIN))) {
			return "<img style=\"cursor:pointer\" onclick=\"{$onclick}\" title=\"Öffentlich lesbar.\" src=\"".PATH_URL."explorer/asset/icons/world_public.png\"></img>";
		} else if ($steam_object->check_access_read(\steam_factory::get_group($GLOBALS["STEAM"]->get_id(),"steam"))) {
			return "<img style=\"cursor:pointer\" onclick=\"{$onclick}\" title=\"Für alle Benutzer lesbar.\" src=\"".PATH_URL."explorer/asset/icons/server_public.png\"></img>";
		} else {
			$sanctions = $steam_object->get_sanction();
			$keys = array_keys($sanctions);
			if (empty($keys) && ($steam_object->get_creator()->get_id() == $myUser->get_id())) {
				return "<img style=\"cursor:pointer\" onclick=\"{$onclick}\" title=\"Nur für mich privat.\" src=\"".PATH_URL."explorer/asset/icons/private.png\"></img>";
			}
			if (isset($keys[0])) {
				$firstKey = $keys[0];
				if (count($keys) == 1 && $firstKey == $myUser->get_id() && $steam_object->check_access_read($myUser)) {
					return "<img style=\"cursor:pointer\" onclick=\"{$onclick}\" title=\"Nur für mich privat.\" src=\"".PATH_URL."explorer/asset/icons/private.png\"></img>";
				}
			}
			return "<img style=\"cursor:pointer\" onclick=\"{$onclick}\" title=\"Benutzerdefinierte Rechte.\" src=\"".PATH_URL."explorer/asset/icons/user_defined.png\"></img>";
		}
	}
	
}
?>