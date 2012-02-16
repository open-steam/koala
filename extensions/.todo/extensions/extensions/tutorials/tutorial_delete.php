<?php
require_once( "../etc/koala.conf.php" );
if(!isset($portal) || !is_object($portal))
		{
			$portal = lms_portal::get_instance();
			$portal->initialize( GUEST_NOT_ALLOWED );
		}
$user = lms_steam::get_current_user();

if ( (! lms_steam::is_steam_admin( $user )) && ( ! lms_steam::is_semester_admin( $current_semester, $user )) && ( ! $course->is_admin( $user ) ) )
{
	include( "bad_link.php" );
	exit;
}

if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" )
{
		if ( $_POST[ "id" ] == $tutorial->get_id() )
		{
			try
			{
				$tutorial->delete();
			}
			catch( Exception $exception )
			{
				$problems = $exception->get_message();
			}
		}
		
		if ( empty( $problems ) )
		{
			$_SESSION[ "confirmation" ] = str_replace( "%NAME", $tutorial_name, gettext( "Tutorial %NAME deleted." ) );
			header( "Location: " . $backlink );
	   		exit;
		}
		
	else
	{
		$portal->set_problem_description( $problems, $hints );
	}
}

$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_EXTENSIONS . "tutorials/templates/tutorial_delete.template.html" );
$content->setVariable( "TUTORIAL_ID", $tutorial->get_id() );
$content->setVariable( "BACK_LINK", $backlink );
$content->setVariable( "INFO_TEXT", gettext( "Do you really want to delete this tutorial?" ) );
$content->setVariable( "LABEL_OK", gettext( "Delete tutorial" ) );
$content->setVariable( "BACKLINK", "<a href=\"$backlink\">" . gettext( "back" ) . "</a>" );

$portal->set_page_main( 
	array( array( "link" => $backlink, "name" => h($tutorial->get_attribute("OBJ_DESC")) ), array( "linK" => "", "name" => gettext( "Delete this tutorial" ) ) ),
	$content->get()
);
$portal->show_html();

?>
