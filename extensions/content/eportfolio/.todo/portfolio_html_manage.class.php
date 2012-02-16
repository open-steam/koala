<?php
class portfolio_html_manage extends portfolio_html {

	public function __construct($path = "") {
		parent::__construct(PORTFOLIO_PATH_TEMPLATES . "index.template.html", $path, "manage");
	}

	public function set_content(){
		$template = new HTML_TEMPLATE_IT();
		$template->loadTemplateFile(PORTFOLIO_PATH_TEMPLATES . "manage.template.html");

		// Set content on right side
		$template->setCurrentBlock("BLOCK_DESKTOP_LEFT");

		$template->setCurrentBlock("BLOCK_NEXTSTEPS");
		$template->setVariable("LABEL_NEXTSTEPS", "Nächste Schritte");
		$template->setCurrentBlock("BLOCK_NEXTSTEP");
		$template->setVariable("CONTENT_NEXTSTEPS", "Nummer 1");
		$template->parseCurrentBlock();
		$template->setVariable("CONTENT_NEXTSTEPS", "Nummer 2");
		$template->parseCurrentBlock();
		$template->setVariable("CONTENT_NEXTSTEPS", "Nummer 3");
		$template->parseCurrentBlock();
		$template->parseCurrentBlock();

		$template->setCurrentBlock("BLOCK_VISIBLE_PORTFOLIOS");
		$template->setVariable("LABEL_VISIBLE", "Sichtbare Portfolios");
		$template->setCurrentBlock("BLOCK_VISIBLE_BOX");
		$template->setVariable("CONTENT_VISIBLE", "Nummer 1");
		$template->parseCurrentBlock();
		$template->setVariable("CONTENT_VISIBLE", "Nummer 2");
		$template->parseCurrentBlock();
		$template->setVariable("CONTENT_VISIBLE", "Nummer 3");
		$template->parseCurrentBlock();
		$template->parseCurrentBlock();


		// Set content on left side
		$template->setCurrentBlock("BLOCK_DESKTOP_RIGHT");

		$template->setCurrentBlock("BLOCK_COMMENTS");
		$template->setVariable("LABEL_COMMENTS", "Kommentare");
		$template->setCurrentBlock("BLOCK_COMMENTS_BOX");
		$template->setVariable("CONTENT_COMMENTS", "Nummer 1");
		$template->parseCurrentBlock();
		$template->setVariable("CONTENT_COMMENTS", "Nummer 2");
		$template->parseCurrentBlock();
		$template->setVariable("CONTENT_COMMENTS", "Nummer 3");
		$template->parseCurrentBlock();
		$template->parseCurrentBlock();
		
		$this->template->setVariable("HTML_CODE_LEFT", $template->get());
	}


}
?>