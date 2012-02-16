<?php
class portfolio_html_welcome extends portfolio_html {
	public function __construct($path = "") {
		parent::__construct(PORTFOLIO_PATH_TEMPLATES . "welcome.template.html");
		$this->set_context($this->generate_context("start"));
		$this->set_content();
	}

	public function set_content(){
		$this->get_template()->setVariable("WELCOME_HEADLINE", gettext("Willkommen beim E-Portfolio"));
		$this->get_template()->setVariable("WELCOME_TEXT", "Nulla facilisi. In vel sem. Morbi id urna in diam dignissim feugiat. Proin molestie tortor eu velit. Aliquam erat volutpat. Nullam ultrices, diam tempus vulputate egestas, eros pede varius leo, sed imperdiet lectus est ornare odio. Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Proin consectetuer velit in dui. Phasellus wisi purus, interdum vitae, rutrum accumsan, viverra in, velit. Sed enim risus, congue non, tristique in, commodo eu, metus. Aenean tortor mi, imperdiet id, gravida eu, posuere eu, felis. Mauris sollicitudin, turpis in hendrerit sodales, lectus ipsum pellentesque ligula, sit amet scelerisque urna nibh ut arcu. Aliquam in lacus. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Nulla placerat aliquam wisi. Mauris viverra odio. Quisque fermentum pulvinar odio. Proin posuere est vitae ligula. Etiam euismod. Cras a eros.");
		$this->get_template()->setVariable("PATH_UPLOAD_ARTEFACTS", PATH_SERVER . "/portfolio/artefacts/upload");
		$this->get_template()->setVariable("PATH_CREATE_PORTFOLIO", PATH_SERVER . "/portfolio/artefacts/upload");
	}
	
}