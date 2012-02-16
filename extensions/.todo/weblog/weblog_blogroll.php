<?php
require_once( PATH_LIB . "format_handling.inc.php" );


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
		} else {
      if ( strpos($values[ "name" ], "/" )) {
        if (!isset($problem)) $problem = "";
        $problem .= gettext("Please don't use the \"/\"-char in the name of the blogroll entry.");
      }
    }

    if (empty($problem) ) {
      $environment =  $weblog->get_blogroll();
      if ( !is_object($environment)) {
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
      }	else {
          $portal->set_problem_description( $problem, $hint );
      }
		}
		else
		{
			$portal->set_problem_description( $problem, $hint );
		}
	}
}

$content = new HTML_TEMPLATE_IT();
$content->loadTemplatefile( PATH_TEMPLATES . "weblog_blogroll.template.html" );

if (!empty($problem)) {
  $content->setVariable("VALUE_NAME", $values["name"]);
  $content->setVariable("VALUE_URL", $values["url"]);
  $content->setVariable("VALUE_DESC", $values["desc"]);
}


$content->setVariable( "GREETING", str_replace( "%n", $portal->get_user()->get_forename(), gettext( "Hi %n!" ) ) );

$help_text = "<b>". gettext( "What is a blogroll?" ) . "</b> "
	  	. gettext( "A blogroll is a collection of links to other weblogs." )
		. " " 
		. gettext( "When present, blogrolls are on the front page sidebar of most weblogs." )
		. " " 
		. gettext( "Some blogrolls also simply consist of the list of weblogs an author reads himself." );
$content->setVariable( "HELP_TEXT", $help_text );
$content->setVariable( "YOUR_BLOGROLL_TEXT", gettext( "Your Blogroll" ) );
$content->setVariable( "CREATE_NEW_LINK_TEXT", gettext( "Create new Link" ) );
$content->setVariable( "FORM_ACTION", "" ); //PATH_URL . "docextern_create.php" );
$content->setVariable( "ENVIRONMENT", $weblog->get_blogroll()->get_id() );
$content->setVariable( "LABEL_NAME", gettext( "Name" ) );
$content->setVariable( "LABEL_URL", gettext( "URL" ) );
$content->setVariable( "LABEL_DESCRIPTION", gettext( "Description" ) );
$content->setVariable( "LABEL_SAVE_CHANGES", gettext( "Save changes") );
$content->setVariable( "LABEL_BACK", gettext( "back" ) );
$content->setVariable( "LINK_BACK", PATH_URL . "weblog/" . $weblog->get_id() . "/" );

$blogroll = $weblog->get_blogroll_list();

foreach( $blogroll as $link )
{
	if ( ! $link instanceof steam_docextern )
	{
		continue;
	}
	$content->setCurrentBlock( "BLOCK_LINK" );
	$content->setVariable( "LINK_URL", $link->get_url() );
	$content->setVariable( "LINK_NAME", h($link->get_name()) );
	$content->setVariable( "LINK_DESC", h($link->get_attribute( "OBJ_DESC" )) );
	$content->setVariable( "LABEL_EDIT", gettext( "edit" ) );
	$content->setVariable( "LINK_EDIT", PATH_URL . "docextern/" . $link->get_id() . "/edit/");
	$content->setVariable( "LINK_DELETE", PATH_URL . "docextern/" . $link->get_id() . "/delete/");
	$content->setVariable( "LABEL_DELETE", gettext( "delete" ) );
	$content->parse( "BLOCK_LINK" );
}

$rootlink = lms_steam::get_link_to_root( $weblog );
$headline = array(
				$rootlink[0],
				$rootlink[1],
				array( "link" => $rootlink[1]["link"] . "communication/", "name" => gettext("Communication")),
				array( "name" =>  h($weblog->get_name()), "link" => PATH_URL . "weblog/" . $weblog->get_id() . "/"),
				array( "link" => "", "name" => gettext( "Edit Blogroll" ) )
			);

$portal->set_page_main(
	$headline,
	$content->get()
);

$portal->show_html();


?>
