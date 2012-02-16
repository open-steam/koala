<?php
$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "weblog_category_edit.template.html" );
if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" )
{
	$values = $_POST[ "values" ];
	if ( ! empty( $values[ "preview_comment" ] ) )
	{
		$content->setCurrentBlock( "BLOCK_PREVIEW_COMMENT" );
		$content->setVariable( "TEXT_DSC", get_formatted_output( $values[ "desc" ] )  );
		$content->setVariable( "LABEL_PREVIEW_YOUR_COMMENT", gettext( "Preview your description" ) );
		$template->parse( "BLOCK_PREVIEW_COMMENT" );
		$headline = gettext( "Change it?" );
	}
	if ( ! empty( $values[ "save" ] ) && ! empty( $values[ "desc" ] ) && ! empty( $values[ "title" ] ) )
	{
		// PRUEFEN, OB ALLES OK, DANN WEBLOG ANLEGEN
		$category->set_name( $values[ "title" ] );
		$category->set_attribute( "OBJ_DESC", $values[ "desc" ] );
	
		header( "Location: " . PATH_URL . "weblog/" . $category->get_id() . "/" );
		exit;
	}
	else
	{
		// PREVIEW
		$content->setCurrentBlock( "BLOCK_PREVIEW" );
		$content->setVariable( "LABEL_PREVIEW_EDIT", gettext( "Preview the description" ) );
		$content->setVariable( "PREVIEW_EDIT", get_formatted_output( $values[ "desc" ] ) );
		$content->parse( "BLOCK_PREVIEW" );
		$headline =  gettext( "Change it?" );
		$content->setVariable( "TEXT_DSC", h($values[ "desc" ]) );
		$content->setVariable( "TITLE_COMMENT", h($values[ "title" ]) );
	}
}

if ( empty( $values ) )
{
	$content->setVariable( "TEXT_DSC", h($category->get_attribute( "OBJ_DESC" )) );
	$content->setVariable( "TITLE_COMMENT", h($category->get_name()) );
}

$content->setVariable( "LABEL_HERE_IT_IS", "" );
$content->setVariable( "LABEL_TITLE", gettext( "Title" ) );
$content->setVariable( "LABEL_DESC", gettext( "Description") );

$content->setVariable( "LABEL_BB_BOLD", gettext( "B" ) );
$content->setVariable( "HINT_BB_BOLD", gettext( "boldface" ) );
$content->setVariable( "LABEL_BB_ITALIC", gettext( "I" ) );
$content->setVariable( "HINT_BB_ITALIC", gettext( "italic" ) );
$content->setVariable( "LABEL_BB_UNDERLINE", gettext( "U" ) );
$content->setVariable( "HINT_BB_UNDERLINE", gettext( "underline" ) );
$content->setVariable( "LABEL_BB_STRIKETHROUGH", gettext( "S" ) );
$content->setVariable( "HINT_BB_STRIKETHROUGH", gettext( "strikethrough" ) );
$content->setVariable( "LABEL_BB_IMAGE", gettext( "IMG" ) );
$content->setVariable( "HINT_BB_IMAGE", gettext( "image" ) );
$content->setVariable( "LABEL_BB_URL", gettext( "URL" ) );
$content->setVariable( "HINT_BB_URL", gettext( "web link" ) );
$content->setVariable( "LABEL_BB_MAIL", gettext( "MAIL" ) );
$content->setVariable( "HINT_BB_MAIL", gettext( "email link" ) );

$content->setVariable( "LABEL_PREVIEW", gettext( "Preview" ) );
$content->setVariable( "LABEL_SAVE_CHANGES", gettext( "Save changes" ) );
$content->setVariable( "LABEL_RETURN", gettext( "back" ) );

$rootlink = lms_steam::get_link_to_root( $weblog );
$headline = array(
				$rootlink[0],
				$rootlink[1],
				array( "link" => $rootlink[1]["link"] . "communication/", "name" => gettext("Communication")),
				array( "name" =>  h($weblog->get_name()), "link" => PATH_URL . "weblog/" . $weblog->get_id() . "/"),
				array( "link" => PATH_URL . "weblog/" . $category->get_id() . "/", "name" => h( $category->get_name() ) ),
				array( "link" => "", "name" => str_replace( "%NAME", h($category->get_name()), gettext( "Edit '%NAME'" )) )
			);

$portal->set_page_main(
		$headline,
		$content->get()
		);
$portal->show_html();
?>
