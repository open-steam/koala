<?php
//TODO: check for trashbin and then check for move permission on the container in which the document is
if ( ! $document->check_access_write( $user ) )
{
	throw new Exception( $user->get_login() . ": no right to delete " . $document->get_id(), E_USER_RIGHTS );
}

if ( $_SERVER[ "REQUEST_METHOD" ] == "POST"  )
{
				$values     = $_POST[ "values" ];
				$doc_name   = $document->get_name();
				$env = $document->get_environment();
				if ( is_object( $env ) && is_object( $koala_container = koala_object::get_koala_object( $env ) ) )
					$backlink = $koala_container->get_url();
				if ( !isset($backlink) || empty($backlink) ) {
					$upper_link = lms_steam::get_link_to_root( $document );
					$backlink = $upper_link[ "link" ];
				}
			

				if ( lms_steam::delete( $document ) )
				{
					$_SESSION[ "confirmation" ] = str_replace( "%DOC_NAME", $doc_name, gettext( "%DOC_NAME successfully deleted." ) );

					// DASS DAS DOKUMENT IRGENDWO IM CACHE LIEGT, IST MIR ZZT NICHT BEKANNT.
					// FALLS CACHE-BEREINIGUNG NOTWENDIG IST, DANN HIER.

					header( "Location: " . $backlink );
					exit;
				}
				else
				{
					throw new Exception( "Cannot delete document" );
				}


}

$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "document_delete.template.html" );
$content->setVariable( "FORM_ACTION", "" );
$content->setVariable( "LABEL_ARE_YOU_SURE", gettext( "Are you sure?" ) );
$content->setVariable( "INFO_DELETE_DOCUMENT", str_replace( "%DOCNAME", h($document->get_name()), gettext( "You are going to delete %DOCNAME." ) ) );

$content->setVariable( "LABEL_DELETE_IT", gettext( "YES, DELETE THIS DOCUMENT" ) );
$content->setVariable( "DELETE_BACK_LINK", $_SERVER[ "HTTP_REFERER" ] );
$content->setVariable( "BACK_LINK", $_SERVER[ "HTTP_REFERER" ] );
$content->setVariable( "LABEL_RETURN", gettext( "back" ) );
// TEST
//				$env = $document->get_environment();
//				if ( is_object( $env ) && is_object( $koala_container = koala_object::get_koala_object( $env ) ) )
//					$backlink = $koala_container->get_url();
//error_log("backlink=" . $backlink);

$portal->set_page_main(
	array( lms_steam::get_link_to_root( $env ), array( "link" => "", "name" => h($document->get_name()) ) ),
	$content->get(),
	""
);
$portal->show_html();

?>
