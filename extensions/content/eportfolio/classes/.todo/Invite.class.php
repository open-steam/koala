<?php
class Invite extends PortfolioExtensionLinkModel {
	
	public static function create($portfolio = null, $user = null) {
		$newInvite = steam_factory::create_link(
			$GLOBALS[ "STEAM" ]->get_id(),
			$name,
			""
		);
		//TODO
		//$newInvite->move($portfolio);
		$newInvite->set_attribute(PORTFOLIO_PREFIX . "TYPE", "INVITE");
		$newInvite->set_attribute("OBJ_TYPE", PORTFOLIO_PREFIX . "INVITE");
		$newInvite = new Invite($newInvite);
		return $newInvite;
	}
	
	
	
	
}
?>