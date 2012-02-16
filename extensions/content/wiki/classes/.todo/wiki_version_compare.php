<?php
require_once( PATH_LIB . "wiki_handling.inc.php" );


$wiki_html_handler = new lms_wiki( $wiki_container );
//$wiki_html_handler->set_admin_menu( "versions", $wiki_doc );

$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "wiki_version_compare.template.html" );


$difftext = wiki_diff_html( $to, $compare );

$content->setVariable( "DIFF_TEXT", $difftext);

$wiki_html_handler->set_main_html( $content->get() );

$rootlink = lms_steam::get_link_to_root( $wiki_container );
(WIKI_FULL_HEADLINE) ?
$headline = array(
				$rootlink[0],
				$rootlink[1],
				array( "link" => $rootlink[1]["link"] . "communication/", "name" => gettext("Communication")),
				array( "name" =>  h($wiki_container->get_name()), "link" => PATH_URL . "wiki/" . $wiki_container->get_id() . "/"),
				array( "link" => PATH_URL . "wiki/" . $wiki_doc->get_id() . "/", "name" => str_replace( ".wiki", "", h($wiki_doc->get_name()) ) ),
				array( "link" => PATH_URL . "wiki/" . $wiki_doc->get_id() . "/versions/", "name" => gettext("Version management")),
				array( "link" => "", "name" => gettext("Version compare") . " (" . $compare->get_version() . " " . gettext("to") . " " .$to->get_version() . ")")
				):
$headline = array(
				array( "name" =>  h($wiki_container->get_name()), "link" => PATH_URL . "wiki/" . $wiki_container->get_id() . "/"),
				array( "link" => PATH_URL . "wiki/" . $wiki_doc->get_id() . "/", "name" => str_replace( ".wiki", "", h($wiki_doc->get_name()) ) ),
				array( "link" => PATH_URL . "wiki/" . $wiki_doc->get_id() . "/versions/", "name" => gettext("Version management")),
				array( "link" => "", "name" => gettext("Version compare") . " (" . $compare->get_version() . " " . gettext("to") . " " .$to->get_version() . ")")
				);

$portal->set_page_main(
    $headline,
    $wiki_html_handler->get_html()
);
$portal->show_html();

?>