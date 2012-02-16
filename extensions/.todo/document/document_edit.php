<?php
require_once( "../etc/koala.conf.php" );
require_once( PATH_LIB . "format_handling.inc.php" );

$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );

$max_file_size = parse_filesize( ini_get( 'upload_max_filesize' ) );
$max_post_size = parse_filesize( ini_get( 'post_max_size' ) );
if ( $max_post_size > 0 && $max_post_size < $max_file_size )
	$max_file_size = $max_post_size;

if ( empty( $values ) )
	$backlink = $_SERVER[ "HTTP_REFERER" ];
else
	$backlink = $values[ "return_to" ];

if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" && ! empty( $_POST[ "values" ] ) )
{
	$values = $_POST[ "values" ];
	$problems = "";
	$hints    = "";
	
	if ( count( $_FILES ) > 0 )
	{
    if (defined("LOG_DEBUGLOG")) {
      $time1 = microtime(TRUE);
      logging::write_log( LOG_DEBUGLOG, "document_edit" . " \t" . $GLOBALS["STEAM"]->get_login_user_name() . " \t" . $document->get_name()  . " \t" . $_FILES[ "material" ][ "name" ] . " \t" . filesize( $_FILES["material"]["tmp_name"] ) . " Bytes \t... " );
    }
		ob_start();
		readfile( $_FILES["material"]["tmp_name"] );
		$content = ob_get_contents();
		ob_end_clean();
		if ( ! empty( $content ) )
		{
			$document->set_content( $content );
		}
    if (defined("LOG_DEBUGLOG")) {
      logging::append_log( LOG_DEBUGLOG, " \t" . round((microtime(TRUE) - $time1) * 1000 ) . " ms");
    }
	}
	if ( ! empty( $values[ "name" ] ) )
	{
		$document->set_name( $values[ "name" ] );
	}
	
	$document->set_attribute( "OBJ_DESC", $values[ "desc" ] );
	
	if ( empty( $problems ) )
	{
		$_SESSION[ "confirmation" ] = str_replace(
			"%DOCUMENT",
			$document->get_name(),
			gettext( "The changes to '%DOCUMENT' have been saved." )
		);
		header( "Location: " . $values[ "return_to" ]  );
	}
	else
	{
		$portal->set_problem_description( $problems );
	}
	
}

$content = new HTML_TEMPLATE_IT( PATH_TEMPLATES );
$content->loadTemplateFile( "document_edit.template.html" );

$content->setVariable( "LABEL_HERE_IT_IS", "" );
$content->setVariable( "LABEL_NAME", gettext( "Name" ) );
$content->setVariable( "LABEL_REPLACE", gettext( "Replace with file" ) );
$content->setVariable( "LABEL_DESC", gettext( "Description" ) );
$content->setVariable( "LABEL_SAVE_CHANGES", gettext( "Save changes" ) );
$content->setVariable( "BACKLINK", "<a href=\"$backlink\">" . gettext( "back" ) . "</a>" );
$content->setVariable( "BACK_LINK", $backlink );

if ( empty( $values ) )
{
	$content->setVariable( "VALUE_NAME", h($document->get_name()) );
	$content->setVariable( "VALUE_DESC", h($document->get_attribute( "OBJ_DESC" )) );
}
else
{
	$content->setVariable( "VALUE_NAME", h($values[ "name" ]) );
	$content->setVariable( "VALUE_DESC", h($values[ "desc" ]) );
}

if ( $max_file_size > 0 ) {
	$content->setVariable( "MAX_FILE_SIZE_INPUT", "<input type='hidden' name='MAX_FILE_SIZE' value='" . (string)$max_file_size . "'/>" );
	$content->setVariable( "MAX_FILE_SIZE_INFO", "<br />" . str_replace( "%SIZE", readable_filesize( $max_file_size ), gettext( "The maximum allowed file size is %SIZE." ) ) );
}
$koala_doc = koala_object::get_koala_object( $document );

$link_path = $koala_doc->get_link_path();
if ( !is_array( $link_path ) ) $link_path = array();
$link_path[] = array( "name" => gettext("Preferences") );

$portal->set_page_main(
	$link_path,
	$content->get(),
	""
);
$portal->set_page_title( gettext( "Edit Document" ) );
$portal->show_html()

?>
