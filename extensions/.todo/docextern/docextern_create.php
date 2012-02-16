<?php
require_once( "../etc/koala.conf.php" );
require_once( PATH_LIB . "format_handling.inc.php" );

$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );

if ( empty( $_GET[ "env" ] ) )
throw new Exception( "Environment not set." ); 

if ( ! $env = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $_GET[ "env" ] ) )
throw new Exception( "Environment unknown." );

$koala_env = koala_object::get_koala_object( $env );

$backlink = ( empty( $_POST["values"]["backlink"] ) ) ? $_SERVER[ "HTTP_REFERER" ] : $_POST[ "values" ][ "backlink" ];

include("docextern_edit.php");
/*

include_once( "../etc/koala.conf.php" );

$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );

$user = lms_steam::get_current_user();

if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" )
{

	$values = $_POST[ "values" ];
	$problem = "";
	$hint = "";

	if ( $values[ "save" ] )
	{
		if ( empty( $values[ "url" ] ) )
		{
			$problem  = gettext( "The URL is missing." ) . " ";
			$hint     = gettext( "Please insert the URL, starting with 'http://'" ) . " ";
		}
		if ( empty( $values[ "name" ] ) )
		{
			$problem .= gettext( "The name is missing." );
			$hint    .= gettext( "How is the title of the webpage?" );
		}
		if ( ! $environment = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $values[ "environment" ] ) )
		{
			throw new Exception( "Environment is not correct."  );
		}
		if ( ! $environment instanceof steam_container )
		{
			throw new Exception( "Environment is no container." );
		}
		if( ! $environment->check_access_write( $user ) )
		{
			throw new Exception( "No write access on this container.", E_USER_RIGHTS );
		}

		if ( empty( $problem ) )
		{
			$docextern = steam_factory::create_docextern(
					$GLOBALS[ "STEAM" ]->get_id(),
					$values[ "name" ],
					$values[ "url" ],
					$environment,
					$values[ "desc" ]
				);

			header( "Location: " . $values[ "return_to" ] );
			exit;
		}
		else
		{
			$portal->set_problem_description( $problem, $hint );
		}
	}
}


$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "docextern_create.template.html" );

$portal->set_page_main( 
		gettext( "Create an URL" ),
		$content->get(),
		""
		);
$portal->show_html();
*/
?>
