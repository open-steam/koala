<?php
include_once( "../../etc/koala.conf.php" );
define("PORTFOLIO_PATH", PATH_PUBLIC . "portfolio/");
define("PORTFOLIO_PATH_TEMPLATES", PORTFOLIO_PATH . "templates/");
define("PORTFOLIO_PATH_CLASSES", PORTFOLIO_PATH . "classes/");

/*
 * /portfolio/  -> welcome oder dashboard
 * 
 * /portfolio/ POST ?action=init&oid=2654
 * 
 * /portfolio/welcome/
 * /portfolio/dashboard/
 * 
 * /portfolio/discuss/23123
 * /portfolio/present/3423
 * 
 * /portfolio/artefacts/ Artefact Verwaltung POST
 * 
 * /portfolio/command/ Ajax -> POST
 * 
 * 
 * 
 * 
 * 
 */


$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );

$user = lms_steam::get_current_user();

$portal->set_page_title(gettext( "My Portfolio" ));

//$content = new HTML_TEMPLATE_IT();
//$content->loadTemplateFile( PORTFOLIO_PATH_TEMPLATES . "index.template.html" );


// start working

$portfolios = portfolio::get_my_portfolios();
if (empty($portfolios) && empty($_GET)) {
	if (isset($_GET["context"]) && $_GET["context"] == "init") {
		portfolio::init();
	}
	$portfolio_html = new portfolio_html_welcome();
} else {
	if (!isset($_GET["context"])) {
		$portfolio_html = new portfolio_html_start(isset($_GET["path"])? $_GET["path"] : "");
	} else if ($_GET["context"] == "artefacts") {
		$portfolio_html = new portfolio_html_artefacts(isset($_GET["path"])? $_GET["path"] : "");
	} else if ($_GET["context"] == "manage") {
		$portfolio_html = new portfolio_html_manage(isset($_GET["path"])? $_GET["path"] : "");
	} else {
		$portfolio_html = new portfolio_html_start(isset($_GET["path"])? $_GET["path"] : "");
	}
}


//$content = $portfolio_html->get_template();
//print $content;
$portal->set_page_title($portfolio_html->get_title());
//print $portfolio_html->get_html();
//print $portfolio_html->get_breadcrumb();
//print $portfolio_html->get_title();
//$content->setVariable( "HTML_CODE_LEFT", $portfolio_html->get_html());

//$content = $portfolio_html->get_template();
//$content->setCurrentBlock( "BLOCK_GROUP" );
//$content->setVariable( "LABEL_TO", gettext( "To" ) );
//$content->parse( "BLOCK_GROUP" );


//$portfolio_html->get_template()->setCurrentBlock("");
//$portfolio_html->get_template()->setCurrentBlock("");
//$portfolio_html->get_template()->setCurrentBlock("");
$portal->set_page_main(
$portfolio_html->get_breadcrumb(),
$portfolio_html->get_html(),
		""
);

$portal->show_html();
//$portal->set_page_main(
//"",
//$content->get(),
//""
//);
//$portal->show_html();

?>