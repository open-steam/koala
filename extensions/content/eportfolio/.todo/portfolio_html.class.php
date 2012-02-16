<?php
class portfolio_html extends koala_html {

	public $path;
	public $portfolio;

	public function __construct($template, $path = "", $context= "") {
		parent::__construct($template);
		$this->path = $path;
		$this->set_context($this->generate_context($context));
		$this->set_content();
	}
	
	protected function generate_context($path) {
		//		echo $path;
		if ($path == "") {
			$context = "start";
		} else if ($path == "config") {
			$context = "config";
		} else if ($path == "manage") {
			$context = "manage";
		} else if ($path == "artefacts") {
			$context = "artefacts";
		} else {
			$context = "start";
		}
		return $context;
	}

	public function get_context_menu( $context, $params = array() ) {
		if ($context == "start") {
			return array(array("link" => PATH_SERVER . "/portfolio/start/config/", "name" => "Konfiguration"));
		} else if ($context == "artefacts") {
			return array(array( "name" => gettext( "Artefakte" ), "link" => PATH_URL . "desktop/documents/" ));
		} else if ($context == "config") {
			return array(array("link" => PATH_SERVER . "/portfolio/", "name" => "zurück"));
		}
	}

	public function get_menu( $params = array() )
	{
		$menu = array();
		$menu["start"] = array("name" => gettext( "Start" ), "link" => PATH_URL . "portfolio/");
		$menu["manage"] = array("name" => gettext( "Verwaltung" ), "link" => PATH_URL . "portfolio/manage/");
		$menu["artefacts"] = array("name" => gettext( "Artefakte" ), "link" => PATH_URL . "portfolio/artefacts/");
		return $menu;
	}



	public function get_breadcrumb() {
		$context = $this->get_context();
		if ($context == "index") {
			return array(array( "link" => PATH_SERVER . "/portfolio/","name" => gettext( "portfolio_titel" )));
		} else if ($context == "config") {
			return array(array( "link" => PATH_SERVER . "/portfolio/","name" => gettext( "portfolio_titel" )), array( "link" => PATH_SERVER . "/portfolio/config","name" => gettext( "configuration" )));
		}
	}

	public function get_title() {
		$context = $this->get_context();
		if ($context == "start") {
			return gettext("portfolio_titel");
		} else if ($context == "manage") {
			return gettext("portfolio_manage");
		} else if ($context == "artefacts") {
			return gettext("portfolio_artefacts");
		} else if ($context == "config") {
			return gettext("portfolio_titel") . " - Konfiguration";
		}
	}

}
?>