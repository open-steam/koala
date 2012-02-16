<?php 
class PortfolioHome extends AbstractExtension implements IHomeExtension {
	
	public function getName() {
		return "PortfolioHome";
	}
	
	public function getDesciption() {
		return "Portfolio extension for changelog.";
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
		$box->setId(\PortfolioHome::getInstance()->getId());
		$box->setTitle(\Portfolio::getInstance()->getText("Your Portfolio"));
		$box->setTitleLink(PATH_URL . "portfolio/");
		$box->setContent(<<<END
Hund
END
);
		$box->setContentMoreLink(PATH_URL . "portfolio/");
		return $box;
	}
}
?>