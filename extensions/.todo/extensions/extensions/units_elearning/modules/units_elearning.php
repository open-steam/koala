<?php
//diese Class sorgt für die Darstellung der Extension!!
if (!defined("PATH_TEMPLATES_UNITS_ELEARNING")) define( "PATH_TEMPLATES_UNITS_ELEARNING", PATH_EXTENSIONS . "units_elearning/templates/" );

if ( !isset( $portal ) ) {
	$portal = lms_portal::get_instance();
	$portal->initialize( GUEST_NOT_ALLOWED );
}

if ( !isset( $html_handler_course ) ) {
	$html_handler_course = new koala_html_course( $course );
	$html_handler_course->set_context("units", array( "subcontext" => "unit"));
}

$content = new HTML_TEMPLATE_IT();

$content->loadTemplateFile(PATH_TEMPLATES_UNITS_ELEARNING . "units_elearning.template.html");

$mediathek = elearning_mediathek::get_instance();
$mediathek->set_unit($steam_unit);
$mediathek->set_course($course);
$elearning_course = $mediathek->get_elearning_course_for_unit($steam_unit);


$navi = "<div id=\"elearning_navigation\"></div>";


if (!isset($elearning_course) || !($elearning_course instanceof elearning_course)) {
	$content->setVariable("ELEARNING_COURSE_CONTENT", "ERROR" );
} else {
	if ($action == "index") {
		header("Location: " . $_SERVER["REQUEST_URI"] . "elearning/einleitung/");
		exit;
	} else if ($action == "chapter") {
		$html = "";
		//error_log("chapter" . $html);
		if (isset($chapter) && $chapter!="") {
			$html .= $elearning_course->get_chapter_by_id($chapter)->get_content_html();
			$content->setVariable("ELEARNING_COURSE_CONTENT", $html);
		} else {
			$html .= "Zugriff auf ungültiges Kapitel";
			$content->setVariable("ELEARNING_COURSE_CONTENT", $html);
		}	
	} else if ($action == "media") {
		$c = $elearning_course->get_chapter_by_id($chapter);
		$m = $c->get_media_by_id($media);
		$m->download();
		exit;
	} else if ($action == "scripts") {
		//error_log("c");
		//error_log("Called Script : " . $elearning_course->get_internal_path() . $scripts);
		$sd = steam_factory::get_object_by_name($GLOBALS[ "STEAM" ]->get_id(), $elearning_course->get_internal_path() . $scripts);
		if ($sd instanceof steam_document) {
			//echo $sd->download();
			$download_url = "/download/" . $sd->get_id() . "/" . $sd->get_name();
			header("Location: " . $download_url);
			exit;
		}
	} else {
		//ERROR
	
	}
}
$html_handler->set_html_left( $content->get() );
$headline = $html_handler->get_headline();
$headline = array(array("name"=>"zurück zum Kurs", "link"=>"../../../../"), array("name"=>"Lektionen zum Kurs »" . $elearning_course->get_name() . "«"));
$portal->set_page_main($headline, $html_handler->get_html(), "");
$portal->show_html();
?>