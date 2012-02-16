<?php
require_once( PATH_EXTENSIONS . 'tutorials/classes/koala_group_tutorial.class.php');
$html_handler_course = new koala_html_course( $course );
$html_handler_course->set_context( "tutorials" );

$csg = $course->get_steam_group();

if(!isset($portal) || !is_object($portal))
{
	$portal = lms_portal::get_instance();
	$portal->initialize( GUEST_NOT_ALLOWED );
}

if ( ! $course->is_admin( $user ) )
{
	throw new Exception( 'No admin!', E_ACCESS );
}

$excl_memb_used = $csg->get_attribute("EXCLUSIVE_TUTORIAL_MEMBERSHIP") == "FALSE" || $csg->get_attribute("EXCLUSIVE_TUTORIAL_MEMBERSHIP") == "0" ? FALSE : TRUE;


if ( $_SERVER[ 'REQUEST_METHOD' ] == 'POST' )
{
	$values = $_POST[ 'values' ];
	
	// ABFRAGEN

	$problems = '';
	$hints    = '';
	
	if ( empty( $problems ) )
	{
		
		if(isset($values['exclusive']) && $values['exclusive'] == 1)
			$course->steam_object->set_attribute("EXCLUSIVE_TUTORIAL_MEMBERSHIP", "TRUE");
		else
			$course->steam_object->set_attribute("EXCLUSIVE_TUTORIAL_MEMBERSHIP", "FALSE");
		
		$_SESSION["confirmation"] = gettext( "The changes have been saved." );
    	header( "Location: " . $_SERVER["REQUEST_URI"]);
    	exit;
		
		
	}
	else
	{
		$portal->set_problem_description( $problems, $hints );
	}
}

$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_EXTENSIONS . 'tutorials/templates/tutorials_prefs.template.html' );

$content->setVariable( 'INFO_TEXT', gettext( 'Here you can edit preferences that are effective for all tutorials in this course.' ) );
$content->setVariable( 'LABEL_SAVE_PREFS', gettext( 'Save changes' ) );


$content->setVariable( 'LABEL_EXCLUSIVE', gettext( 'Exclusive membership' ));
$content->setVariable( 'EXCLUSIVE_INFO', gettext( 'If this option is checked, membership to tutorials will be exclusive. This means, that a student can only be member in exactly one of the existing tutorials.' ));
$content->setVariable( 'EXCLUSIVE_SHORT', gettext( 'Exclusive membership active' ));

if($excl_memb_used) 
	$content->setVariable( 'CHECK', "checked=\"checked\"");
	

$content->setVariable( 'BACKLINK', gettext( 'Or,' ) . " <a href=\"$backlink\">" . gettext( 'back' ) . '</a>' );

$link_path = $html_handler_course->get_headline();
$link_path[] = array( 'name' => gettext( 'Edit tutorial preferences' ) );

$portal->set_page_main( $link_path, $content->get(), '' );

$portal->show_html();
?>
