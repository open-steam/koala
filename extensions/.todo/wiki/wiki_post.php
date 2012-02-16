<?php
$create = TRUE;
include("wiki_edit.php");
/*
$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "wiki_post.template.html" );
$headline = gettext( "Write a new article" );
$backlink = ( empty( $_POST["values"]["backlink"] ) ) ? $_SERVER[ "HTTP_REFERER" ] : $_POST[ "values" ][ "backlink" ];

if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" )
{
	$values = $_POST[ "values" ];
	if ( get_magic_quotes_gpc() ) {
		if ( !empty( $values['title'] ) ) $values['title'] = stripslashes( $values['title'] );
		if ( !empty( $values['body'] ) ) $values['body'] = stripslashes( $values['body'] );
	}

  if ( ! empty( $values[ "save" ] ) ) {
  
    if ( empty( $values[ "title" ] ) )
    {
      $problems = gettext( "The subject of your entry is missing." );
      $hints    = gettext( "Please type in a subject." );
    }
  
    if ( strpos($values[ "title" ], "/" )) {
      if (!isset($problems)) $problems = "";
      $problems .= gettext("Please don't use the \"/\"-char in the subject of your post.");
    }
    
    if ( empty($problems) )
    {
      $new_article = steam_factory::create_document(
        $GLOBALS[ "STEAM" ]->get_id(),
        $values[ "title" ] . ".wiki",
        $values[ "body" ],
        "text/wiki",
        $wiki_container,
        ""
      );


        header( "Location: " . PATH_URL . "wiki/" . $new_article->get_id() . "/" );
        exit;
    } else {
        $portal->set_problem_description( $problems, isset($hints)?$hints:"" );
    }
  }
}

$content->setVariable( "POST_NEW_TOPIC_TEXT", h($headline) );
$content->setVariable( "LABEL_TOPIC", gettext( "Topic" ) );
$content->setVariable( "LABEL_YOUR_POST", gettext( "Your Article") );

if ( ! empty( $values ) )
{
	$content->setVariable( "TITLE_COMMENT", h($values[ "title" ]) );
	$content->setVariable( "TEXT_COMMENT", h($values[ "body" ]) );
}

$content->setVariable( "LABEL_WIKI_H2", gettext( "H2" ) );
$content->setVariable( "HINT_WIKI_H2", gettext( "heading (level 2)" ) );
$content->setVariable( "LABEL_WIKI_H3", gettext( "H3" ) );
$content->setVariable( "HINT_WIKI_H3", gettext( "heading (level 3)" ) );
$content->setVariable( "LABEL_WIKI_BOLD", gettext( "'''B'''" ) );
$content->setVariable( "HINT_WIKI_BOLD", gettext( "boldface" ) );
$content->setVariable( "LABEL_WIKI_ITALIC", gettext( "''I''" ) );
$content->setVariable( "HINT_WIKI_ITALIC", gettext( "italic" ) );
$content->setVariable( "LABEL_WIKI_BULLET_LIST", gettext( "* list" ) );
$content->setVariable( "HINT_WIKI_BULLET_LIST", gettext( "bullet list" ) );
$content->setVariable( "LABEL_WIKI_NUMBERED_LIST", gettext( "# list" ) );
$content->setVariable( "HINT_WIKI_NUMBERED_LIST", gettext( "numbered list" ) );
$content->setVariable( "LABEL_WIKI_LINE", gettext( "-----" ) );
$content->setVariable( "HINT_WIKI_LINE", gettext( "horizontal line" ) );
$content->setVariable( "LABEL_WIKI_LINK", gettext( "[[LINK]]" ) );
$content->setVariable( "HINT_WIKI_LINK", gettext( "wiki link" ) );
$content->setVariable( "LABEL_WIKI_URL", gettext( "[URL]" ) );
$content->setVariable( "HINT_WIKI_URL", gettext( "web link" ) );
$content->setVariable( "LABEL_WIKI_IMAGE", gettext( "IMG" ) );
$content->setVariable( "HINT_WIKI_IMAGE", gettext( "image" ) );

$content->setVariable( "LABEL_SAVE_CHANGES", gettext( "Save changes" ) );
$content->setVariable( "BACKLINK", "<a href=\"$backlink\">" . gettext( "back" ) . "</a>" );


$headline = array( array( "link" => PATH_URL . "wiki/" . $wiki_container->get_id() . "/", "name" => h($wiki_container->get_name()) ), array( "link" => "", "name" => gettext( "New Article")) );
$portal->set_page_main(
$headline,
$content->get()
);
$portal->show_html();
*/
?>
