<?php
if (!defined("PATH_TEMPLATES_UNITS_HOMEWORK")) define( "PATH_TEMPLATES_UNITS_HOMEWORK", PATH_EXTENSIONS. "units_homework/templates/" );
require_once( "../etc/koala.conf.php" );
require_once( PATH_LIB . "format_handling.inc.php" );
require_once( PATH_LIB . "url_handling.inc.php");

$path = url_parse_rewrite_path( $_GET[ "path" ] );
$id = $path[4];
$docu = steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);

$group_staff=$owner->get_group_staff();
$group_admins=$owner->get_group_admins();
$group_learners=$owner->get_group_learners();

//IF-Abfrage um auszuschliessen, dass jemand andere Bewertungen lesen kann
if ( !$docu->check_access_read( lms_steam::get_current_user() ) ) {
	$portal->set_problem_description( gettext( "You are not permitted to view this feedback" ) );
	$portal->set_page_main( $html_handler->get_headline(), $html_handler->get_html(), "");
	$portal->show_html();
	exit;
}

$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );
$content = new HTML_TEMPLATE_IT( PATH_TEMPLATES_UNITS_HOMEWORK );
$content->loadTemplateFile( "units_homework_feedback.template.html" );

$feedback=$docu->get_attribute("HOMEWORK_FEEDBACK");
$points=$docu->get_attribute("HOMEWORK_POINTS");
if($group_learners->is_member($user)){
	//Fedback f�r Studenten
	if(isset($feedback) && !$feedback==0){
		$content->setVariable( "LABEL_REAL_FEEDBACK", $feedback);
	}
	else{
		$content->setVariable( "LABEL_REAL_FEEDBACK", gettext("There is no feedback for this homework."));
	}	
	if(isset($points)){
		$content->setVariable( "LABEL_HOMEWORK_POINTS", $points);
	}
	else{
		$content->set_attribute( "LABEL_HOMEWORK_POINTS", gettext("There are no points set for this homework."));
	}
}
else{
	//Feedback f�r Tutoren	
	if(isset($feedback) && !$feedback==0){
		$content->setVariable( "LABEL_REAL_FEEDBACK", "<textarea name=\"values[feedback]\" rows=\"12\" cols=\"50\">".$feedback."</textarea>" );
	}
	else{
		$content->setVariable( "LABEL_REAL_FEEDBACK", "<textarea name=\"values[feedback]\" rows=\"12\" cols=\"50\"></textarea>" );
	}
	if(isset($points)){
		$content->setVariable( "LABEL_HOMEWORK_POINTS", "<input type=\"text\" name=\"values[points]\" value=\" ".$points."\" size=\"8\">");
	}
	else{
		$content->setVariable( "LABEL_HOMEWORK_POINTS", "<input type=\"text\" name=\"values[points]\" size=\"8\">");
	}
	$content->setVariable( "BUTTON_GIVE_FEEDBACK", "<input type=\"submit\" value=\"". gettext( "give feedback" ) ."\">".gettext(" or "));
}

$content->setVariable( "LINK_DOWNLOAD", PATH_URL . "get_document.php?id=" . $docu->get_id() );
$content->setVariable( "LABEL_DOWNLOAD", gettext( "download" ) );
$content->setVariable( "ICONPATH_DOWNLOAD", PATH_STYLE . "images/download.png" );

$content->setVariable( "LABEL_FEEDBACK", gettext( "Feedback" ) );
$content->setVariable( "LABEL_POINTS", gettext( "Points" ) );
$content->setVariable( "LABEL_FILE", gettext( "File" ) );
$content->setVariable( "LABEL_FILE_INFO", PATH_URL . "doc/" . $docu->get_id() . "/" );
$content->setVariable( "LABEL_NAME", $docu->get_name());

if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" )
{
	$values = isset( $_POST[ "values" ] ) ? $_POST[ "values" ] : array();
	$problems = "";
	$hints    = "";
			
	if( empty($values[feedback])){
		$problems = gettext( "No feedback." ) . " ";
		$hints = gettext( "Please give feedback for this homework." ) . " ";
	}
				
	if ( empty( $problems ) ){
		$docu->set_attribute("HOMEWORK_POINTS", $values[points], true);
		$docu->set_attribute("HOMEWORK_FEEDBACK", $values[feedback], true);
		$GLOBALS["STEAM"]->buffer_flush();
							
		$_SESSION[ "confirmation" ] = str_replace(
					"%DOCUMENT",
					h($filename),
					gettext( "Feedback has been saved" )
		);
				
		header( "Location: " . $backlink );
		exit;
		}
	else{
		$portal->set_problem_description( $problems, $hints );
	}
}



$content->setVariable( "BACKLINK",  " <a href=\"$backlink\">" . gettext( "back" ) . "</a>" );

$html_handler->set_html_left( $content->get() );
$portal->set_page_main( $html_handler->get_headline(), $html_handler->get_html(), "");
$portal->show_html();
?>
