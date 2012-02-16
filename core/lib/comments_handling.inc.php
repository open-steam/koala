<?php

function get_comment_html( $document, $url )
{

	$cache = get_cache_function( $document->get_id(), 600 );
	
	$user = lms_steam::get_current_user();
	$write_access = $document->check_access( SANCTION_ANNOTATE, $user );
	
	$template = new HTML_TEMPLATE_IT();
	$template->loadTemplateFile( PATH_TEMPLATES . "comments.template.html" );
	$headline = gettext( "Add your comment" );

	if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" && $write_access )
	{

		$values = $_POST[ "values" ];
		if ( ! empty( $values[ "preview_comment" ] ) )
		{
			$template->setCurrentBlock( "BLOCK_PREVIEW_COMMENT" );
			$template->setVariable( "TEXT_COMMENT", $values[ "comment" ] );

			$template->setVariable("PREVIEW", gettext("Preview"));
			$template->setVariable("POST_COMMENT", gettext("Post comment"));
			
			$template->setVariable( "LABEL_PREVIEW_YOUR_COMMENT", gettext( "Preview your comment") );
			$template->setVariable( "VALUE_PREVIEW_COMMENT", get_formatted_output( $values[ "comment" ] ) );
			$template->parse( "BLOCK_PREVIEW_COMMENT" );
			$headline = gettext( "Change it?" );
		}

		if ( ! empty( $values[ "submit_comment" ] ) && ! empty( $values[ "comment" ]) )
		{
			$new_comment = steam_factory::create_textdoc(
					$GLOBALS[ "STEAM" ]->get_id(),
					$user->get_name() . "-" . time(),
					$values[ "comment" ]
					);
			$all_user = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), STEAM_ALL_USER );
			$new_comment->set_acquire( $document );
			$new_comment->set_read_access( $all_user );
			$document->add_annotation( $new_comment );

			$cache->drop( "lms_steam::get_annotations", $document->get_id() );
		}
	}
	$comments = $cache->call( "lms_steam::get_annotations", $document->get_id() );

	if ( count( $comments ) > 0  )
	{
		$template->setVariable( "LABEL_COMMENTS", gettext( "comments" ));
	}
	
	$comments=array_reverse($comments);  //reverse comment order (oldest first)

	foreach( $comments as $comment )
	{
		$obj_comment = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $comment[ "OBJ_ID" ] );
		$template->setCurrentBlock( "BLOCK_ANNOTATION" );
		$template->setVariable( "COMMENT_ID", $comment[ "OBJ_ID" ] );
		$template->setVariable( "AUTHOR_LINK", PATH_URL . "user/" . $comment[ "OBJ_CREATOR_LOGIN" ] . "/" );
		$template->setVariable( "AUTHOR_NAME", $comment[ "OBJ_CREATOR" ] );
		$template->setVariable( "IMAGE_LINK", PATH_URL . "get_document.php?id=" . $comment[ "OBJ_ICON" ] );
		$template->setVariable( "LABEL_SAYS", gettext( "says" ) );
		$template->setVariable( "ANNOTATION_COMMENT", get_formatted_output( $comment[ "CONTENT" ], 80, "\n" ) );
		$template->setVariable( "HOW_LONG_AGO", how_long_ago( $comment[ "OBJ_CREATION_TIME" ] ) );
		$template->setVariable( 'LINK_PERMALINK', $url . '/#comment' . $comment['OBJ_ID'] );
		$template->setVariable( "LABEL_PERMALINK", gettext( "permalink" ) );
		if ( $obj_comment->check_access_write( $user ) )
		{
			$template->setCurrentBlock( "BLOCK_OWN_COMMENT" );
			$template->setVariable( "LINK_DELETE", $url . "/deletecomment" . $comment[ "OBJ_ID" ] . "/" );
			$template->setVariable( "LABEL_DELETE", gettext( "delete" ) );
			$template->setVariable( "LINK_EDIT", $url . "/editcomment" . $comment[ "OBJ_ID" ] . "/" );
			$template->setVariable( "LABEL_EDIT", gettext( "edit" ) );
			$template->parse( "BLOCK_OWN_COMMENT" );
		}
		$template->parse( "BLOCK_ANNOTATION" );
	}
	if ( $write_access )
	{
		$template->setCurrentBlock( "BLOCK_ADD_COMMENT" );
		$template->setVariable( "LABEL_ADD_YOUR_COMMENT", $headline );
		$template->setVariable( "LABEL_PREVIEW", gettext( "Preview" ) );
		$template->setVariable( "LABEL_OR", gettext( "or") );
		$template->setVariable( "LABEL_COMMENT", gettext( "Add comment") );
		
		$template->setVariable( "LABEL_BB_BOLD", gettext( "B" ) );
		$template->setVariable( "HINT_BB_BOLD", gettext( "boldface" ) );
		$template->setVariable( "LABEL_BB_ITALIC", gettext( "I" ) );
		$template->setVariable( "HINT_BB_ITALIC", gettext( "italic" ) );
		$template->setVariable( "LABEL_BB_UNDERLINE", gettext( "U" ) );
		$template->setVariable( "HINT_BB_UNDERLINE", gettext( "underline" ) );
		$template->setVariable( "LABEL_BB_STRIKETHROUGH", gettext( "S" ) );
		$template->setVariable( "HINT_BB_STRIKETHROUGH", gettext( "strikethrough" ) );
		$template->setVariable( "LABEL_BB_IMAGE", gettext( "IMG" ) );
		$template->setVariable( "HINT_BB_IMAGE", gettext( "image" ) );
		$template->setVariable( "LABEL_BB_URL", gettext( "URL" ) );
		$template->setVariable( "HINT_BB_URL", gettext( "web link" ) );
		$template->setVariable( "LABEL_BB_MAIL", gettext( "MAIL" ) );
		$template->setVariable( "HINT_BB_MAIL", gettext( "email link" ) );
		
		$template->parse( "BLOCK_ADD_COMMENT" );
	}
	return $template->get();
}

?>
