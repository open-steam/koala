<?php
class Comment extends PortfolioExtensionDocumentModel{

	public static function create($name, $portfolio) {
		$newComment = steam_factory::create_document(
			$GLOBALS[ "STEAM" ]->get_id(),
			$name,
			"",
			$pMimeType = ""
		);
		$newComment->move($portfolio->getRoom());
		$newComment->set_attribute(PORTFOLIO_PREFIX . "TYPE", "COMMENT");
		$newComment->set_attribute("OBJ_TYPE", PORTFOLIO_PREFIX . "COMMENT");
		$newComment = new Comment($newComment);
		return $newPortfolio;
	}
	
	
}
?>