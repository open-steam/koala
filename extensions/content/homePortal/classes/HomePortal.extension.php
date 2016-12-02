<?php
class HomePortal extends AbstractExtension {

	public function getName() {
		return "HomePortal";
	}

	public function getDesciption() {
		return "Extension for home portal.";
	}

	public function getVersion() {
		return "v1.0.0";
	}

	public function getAuthors() {
		$result = array();
		$result[] = new Person("Jan", "Petertonkoker", "janp@mail.uni-paderborn.de");
		return $result;
	}

	public function getUrlNamespaces() {
		return array(strtolower($this->getName()), "desktop");
	}

	public function getPriority() {
		return 71;
	}

	public function updateSubscriptions($id) {
		$user = \lms_steam::get_current_user();
		$subscriptions = array();

		$portal = $user->get_attribute("HOME_PORTAL");
		if ($portal instanceof \steam_object && $id == $portal->get_id()) {
			$columns = $portal->get_inventory();
			foreach ($columns as $column) {
				if ($column instanceof \steam_container) {
					$portlets = $column->get_inventory();
					foreach ($portlets as $portlet) {
						if ($portlet->get_attribute("bid:portlet") === "subscription") {
							$subscriptions[] = $portlet->get_attribute("PORTLET_SUBSCRIPTION_OBJECTID");
						}
					}
				}
			}
			$user->set_attribute("USER_HOMEPORTAL_SUBSCRIPTIONS", $subscriptions);
		}
	}
}
?>
