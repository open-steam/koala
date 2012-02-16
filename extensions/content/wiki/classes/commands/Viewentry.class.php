<?php
namespace Wiki\Commands;
include_once( PATH_LIB . "url_handling.inc.php" );
include_once( PATH_LIB . "format_handling.inc.php" );
require_once( PATH_LIB . "comments_handling.inc.php" );
require_once( PATH_LIB . "wiki_handling.inc.php" );

class Viewentry extends \AbstractCommand implements \IFrameCommand {
	private $params;
	private $id;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		isset($this->params[0]) ? $this->id = $this->params[0]: "";
	}
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$frameResponseObject = $this->execute($frameResponseObject);
		return $frameResponseObject;
	}
	public function execute (\FrameResponseObject $frameResponseObject) {
		//CODE FOR ALL COMMANDS OF THIS PAKAGE END
		$user = \lms_steam::get_current_user();

		// Disable caching
		// TODO: Work on cache handling. An enabled cache leads to bugs
		// if used with the wiki.
		\CacheSettings::disable_caching();

		if ( ! $wiki_container = \steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $this->id ) )
		{
			include( "bad_link.php" );
			exit;
		}
		if ( ! $wiki_container instanceof \steam_container )
		{
			$wiki_doc = $wiki_container;
			$wiki_container = $wiki_doc->get_environment();
			if ( $wiki_doc->get_attribute( DOC_MIME_TYPE ) != "text/wiki" )
			{
				include( "bad_link.php" );
				exit;
			}
		}
		//CODE FOR ALL COMMANDS OF THIS PAKAGE END
		defined( "OBJ_ID") OR define( "OBJ_ID", $wiki_doc->get_id() );

		$is_prev_version = FALSE;
		$wiki_html_handler = new \lms_wiki( $wiki_container );

		if(!$is_prev_version)
		{
			$wiki_html_handler->set_admin_menu( "entry", $wiki_doc );
			$attributes = $wiki_doc->get_attributes( array( "DOC_VERSION", "DOC_AUTHORS", "OBJ_LAST_CHANGED", "DOC_USER_MODIFIED", "DOC_TIMES_READ", "DOC_LAST_MODIFIED", "OBJ_WIKILINKS" ));
			//TODO: check if sourcecode can be deleted
			//$wiki_html_handler->set_widget_links( $wiki_doc );
			//$wiki_html_handler->set_widget_previous_versions( $wiki_doc );
		}
		else
		{
			$wiki_html_handler->set_admin_menu( "version" , $version_doc );
			$attributes = $version_doc->get_attributes( array( "DOC_VERSION", "DOC_AUTHORS", "OBJ_LAST_CHANGED", "DOC_USER_MODIFIED", "DOC_TIMES_READ", "DOC_LAST_MODIFIED", "OBJ_WIKILINKS" ));
		}

		$last_author  = $attributes[ "DOC_USER_MODIFIED" ]->get_attributes( array( "USER_FIRSTNAME", "USER_FULLNAME" ) );

		$content = \Wiki::getInstance()->loadTemplate("wiki_entry.template.html");
		//$content = new HTML_TEMPLATE_IT();
		//$content->loadTemplateFile( PATH_TEMPLATES . "wiki_entry.template.html" );
		$content->setVariable( "LABEL_CLOSE", gettext( "close" ) );

		if(!$is_prev_version)
		$content->setVariable( "VALUE_ENTRY_TEXT", wiki_to_html_plain( $wiki_doc ) );
		else
		$content->setVariable( "VALUE_ENTRY_TEXT", wiki_to_html_plain( $wiki_doc, $version_doc ) );

		$content->setVariable( "IMAGE_SRC", PATH_URL . "download/image/" . $attributes[ "DOC_USER_MODIFIED" ]->get_attribute( "OBJ_ICON" )->get_id() . "/60/70/" );
		$content->setVariable( "AUTHOR_LINK", PATH_URL . "user/index/" . $attributes[ "DOC_USER_MODIFIED" ]->get_name() . "/" );
		$content->setVariable( "VALUE_POSTED_BY", h($last_author[ "USER_FIRSTNAME" ]) . " " . h($last_author[ "USER_FULLNAME" ]) );
		$content->setVariable( "LABEL_BY", gettext("created by"));
		$content->setVariable( "VALUE_VERSION", h($attributes["DOC_VERSION"]));
		$content->setVariable( "VALUE_DATE_TIME", strftime( "%x %X", $attributes[ "DOC_LAST_MODIFIED" ] ) );

		/*
		 if(!$is_prev_version)
		 {
		 $content->setVariable( "POST_PERMALINK", PATH_URL . "wiki/" . $wiki_doc->get_id() . "/" );
		 $content->setVariable( "POST_PERMALINK_LABEL", "(" . gettext( "permalink" ) . ")");
		 }
		 */

		if ( $wiki_doc->check_access_write( $user ) )
		{
			$content->setCurrentBlock( "BLOCK_ACCESS" );
			$content->setVariable( "POST_LABEL_DELETE", gettext( "delete" ) );
			$content->setVariable( "POST_LABEL_EDIT", gettext( "edit" ) );
			$content->parse( "BLOCK_ACCESS" );
		}

		$versions = $wiki_doc->get_previous_versions();
		$no_versions = ( is_array( $versions ) ) ? count( $versions ) : 0;
		$content->setVariable("VERSION_MANAGEMENT", gettext( "Version Management" ) );

		if ( $no_versions > 0 )
		{
			$content->setVariable("NUMBER_VERSIONS", "<li>" . $no_versions . " " . gettext( "previous version(s) available" ) . "</li>" );
			$content->setVariable("LINK_VERSION_MANAGEMENT", "<li><a href=\"" . PATH_URL . "wiki/versionoverview/" . $wiki_doc->get_id() . "/\">&raquo; " . gettext("enter version management") . "</a></li>");
		}
		else
		{
			$content->setVariable("NUMBER_VERSIONS", "<li>" . gettext( "no previous versions available" ) . "</li>" );
		}

		$content->setVariable("LINKS", gettext( "Wiki Links" ) );
		$links = $wiki_doc->get_attribute( "OBJ_WIKILINKS_CURRENT" );
		$found_doc = false;
		if (is_array($links)) {
			foreach($links as $doc) {
				if ($doc instanceof \steam_document) {
					$found_doc = true;
					break;
				}
			}
		}

		if (!$found_doc)
		{
			$content->setCurrentBlock( "BLOCK_LINKS" );
			$content->setVariable( "LINK", gettext("no links available"));
			$content->parse( "BLOCK_LINKS" );
		}
		else
		{
			foreach( $links as $doc )
			{
				if ( $doc instanceof \steam_document )
				{
					$name = str_replace( ".wiki", "", h( $doc->get_name() ) );
					$link = PATH_URL . "wiki/viewentry/" . $wiki_doc->get_id() . "/" . $doc->get_identifier();
					$content->setVariable( "LINK", '<li>&raquo; <a href="' . $link . '">' . $name . '</a></li>' );
					$content->parse( "BLOCK_LINKS" );
				}
			}
		}

		$wiki_html_handler->set_main_html( $content->get() );

		$rootlink = \lms_steam::get_link_to_root( $wiki_container );
		(WIKI_FULL_HEADLINE) ?
		$headline = array(
		$rootlink[0],
		$rootlink[1],
		array( "link" => $rootlink[1]["link"] . "communication/", "name" => gettext("Communication")),
		array( "name" =>  h($wiki_container->get_name()), "link" => PATH_URL . "wiki/index/" . $wiki_container->get_id() . "/"),
		):
		$headline = array(array( "name" =>  h($wiki_container->get_name()), "link" => PATH_URL . "wiki/index/" . $wiki_container->get_id() . "/"));


		if(!$is_prev_version)
		{
			$headline[] = array( "link" => "", "name" => str_replace( ".wiki", "", h($wiki_doc->get_name()) ) );
		} else {
			$headline[] = array( "link" => PATH_URL . "wiki/index/" . $wiki_doc->get_id() . "/", "name" => str_replace( ".wiki", "", h($wiki_doc->get_name()) ) );
			$headline[] = array( "link" => PATH_URL . "wiki/versionoverview/" . $wiki_doc->get_id() . "", "name" => gettext("Version management"));
			$headline[] = array( "link" => "", "name" => "Version" . " " . $version_doc->get_version() . " (" . gettext("Preview") . ")");
		}

		$frameResponseObject->setHeadline($headline);
		$widget=new \Widgets\RawHtml();
		$widget->setHtml($wiki_html_handler->get_html());
		$frameResponseObject->addWidget($widget);
		return $frameResponseObject;

		/*	$portal->set_page_main(
		 $headline,
		 $wiki_html_handler->get_html()
		 );
		 $portal->show_html();*/

	}
}

?>