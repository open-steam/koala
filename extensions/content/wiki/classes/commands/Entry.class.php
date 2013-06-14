<?php
namespace Wiki\Commands;
class Entry extends \AbstractCommand implements \IFrameCommand {

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
		require_once( PATH_LIB . "wiki_handling.inc.php" );
		
		$portal = \lms_portal::get_instance();
		$portal->initialize( GUEST_NOT_ALLOWED );
		
		// Disable caching
		// TODO: Work on cache handling. An enabled cache leads to bugs
		// if used with the wiki.
		\CacheSettings::disable_caching();
		
		$user = \lms_steam::get_current_user();
		
		// check for .wiki
		if (isset($this->params[1]) && (stripos( $this->params[ 1 ], ".wiki" ) !== FALSE )) {
			$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
			$objectType = $object->get_attribute("OBJ_TYPE");
			if ($objectType != "0" && $objectType == "container_wiki_koala") {
				$wiki_container = $object;
			} else {
				$wiki_container = $object->get_environment();
			}
			if ( !($wiki_doc = $wiki_container->get_object_by_name( $this->params[1] ) )) {
				if ($wiki_container->check_access_write($user)){
					header("Location: " . PATH_URL . "wiki/edit/" . $wiki_container->get_id() . "?title=" . str_replace( ".wiki", "", $this->params[1]));
					exit;
				} else {
					$_SESSION[ "error" ] = "Der gewählte Wiki Eintrag existiert nicht.";
					header("Location: " . PATH_URL . "wiki/glossary/" . $wiki_container->get_id());
					exit;
				}
			}
		// end check for .wiki
		} else {
			$wiki_doc = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
                }
                
                //chronic
                $entryName = $wiki_doc->get_name();
                $chronicPath = "/wiki/entry/" . $wiki_doc->get_id() . "/";
                $chronicTitle = "Wiki-Eintrag (" . substr($entryName, 0, strpos($entryName, ".wiki")) . ")";
                \ExtensionMaster::getInstance()->getExtensionById("Chronic")->setCurrentPath($chronicPath,$chronicTitle);
		
                $wiki_container = $wiki_doc->get_environment();
		if($wiki_container->get_attribute("UNIT_TYPE")){
		    $place = "units";
		}
		else{
		    $place = "communication";
		}
		
		if (isset($this->params[1])) {
			$version_doc = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->params[1]);
		}

		//is it a previous version of an entry?
		$is_prev_version = (isset($version_doc) && is_object($version_doc) && $version_doc instanceof \steam_document) ? TRUE : FALSE;
		
		$wiki_html_handler = new \koala_wiki( $wiki_container );
		
		if(!empty($_GET['search_query'])){
			\Zend_Search_Lucene_Analysis_Analyzer::setDefault(
					new \Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8Num_CaseInsensitive()
			);
			$highlight = urldecode($_GET['search_query']);
			$query = \Zend_Search_Lucene_Search_QueryParser::parse($highlight);
		}
		
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
                        $wiki_html_handler->set_version($version_doc->get_id());
			$wiki_html_handler->set_admin_menu( "version" , $wiki_doc );
		 	$attributes = $version_doc->get_attributes( array( "DOC_VERSION", "DOC_AUTHORS", "OBJ_LAST_CHANGED", "DOC_USER_MODIFIED", "DOC_TIMES_READ", "DOC_LAST_MODIFIED", "OBJ_WIKILINKS" ));
		}
		
		$last_author  = $attributes[ "DOC_USER_MODIFIED" ]->get_attributes( array( "USER_FIRSTNAME", "USER_FULLNAME" ) );
		
		$WikiExtension = \Wiki::getInstance();
		$WikiExtension->addCSS();
		$content = $WikiExtension->loadTemplate( "wiki_entry.template.html" );
		$content->setVariable( "LABEL_CLOSE", gettext( "close" ) );
		
		if(ENABLED_SEARCH & WIKI_SEARCH_ENABLED){
			$search_widget = new \search_widget();
			$search_widget->set_container_id($wiki_container->get_id());
			//$search_widget->set_action_type('GET');
			//$search_widget->set_action_url(PATH_SERVER."/wiki/{$wiki_doc->get_id()}/");
			$content->setVariable("SEARCH_WIDGET", $search_widget->render());
		}
		
		if(!$is_prev_version) 
			if(!empty($highlight)){
				$highlightedText = $query->highlightMatches (wiki_to_html_plain( $wiki_doc ) ,'UTF-8', new myHighlighter());
				$content->setVariable( "VALUE_ENTRY_TEXT", $highlightedText );
			}
			else $content->setVariable( "VALUE_ENTRY_TEXT", wiki_to_html_plain( $wiki_doc ) );
		else 
			$content->setVariable( "VALUE_ENTRY_TEXT", wiki_to_html_plain( $wiki_doc, $version_doc ) );
			
		if ($wiki_doc->check_access_write($user) || WIKI_SHOW_AUTHOR_TO_READER) {
			$pic_link = ( $attributes[ "DOC_USER_MODIFIED" ]->get_attribute("OBJ_ICON")->get_id() == 0 ) ? PATH_URL . "styles/standard/images/anonymous.jpg" : PATH_URL . "download/image/" . $attributes[ "DOC_USER_MODIFIED" ]->get_attribute("OBJ_ICON")->get_id() . "/60/70";
			$content->setVariable( "IMAGE_SRC", $pic_link);
			$content->setVariable( "AUTHOR_LINK", PATH_URL . "profile/index/" . $attributes[ "DOC_USER_MODIFIED" ]->get_name() . "/" );
			$content->setVariable( "VALUE_POSTED_BY", h($last_author[ "USER_FIRSTNAME" ]) . " " . h($last_author[ "USER_FULLNAME" ]) );
			$content->setVariable( "LABEL_BY", gettext("created by"));
			$content->setVariable( "VALUE_VERSION", h($attributes["DOC_VERSION"]));
			$content->setVariable( "VALUE_DATE_TIME", strftime( "%x %X", $attributes[ "DOC_LAST_MODIFIED" ] ) );
		}
		
		if ( $wiki_doc->check_access_write($user) )
		{
			$content->setCurrentBlock( "BLOCK_ACCESS" );
			$content->setVariable( "POST_LABEL_DELETE", gettext( "delete" ) );
			$content->setVariable( "POST_LABEL_EDIT", gettext( "edit" ) );
			$content->parse( "BLOCK_ACCESS" );
		
			$versions = $wiki_doc->get_previous_versions();
			$no_versions = ( is_array( $versions ) ) ? count( $versions ) : 0;
			$content->setVariable("VERSION_MANAGEMENT", "Versionsverwaltung" );
			
			if ( $no_versions > 0 )
			{
				$content->setVariable("NUMBER_VERSIONS", "<li>" . $no_versions . " " . gettext( "Vorgängerversion(en) verfügbar" ) . "</li>" );
				$content->setVariable("LINK_VERSION_MANAGEMENT", "<li><a href=\"" . PATH_URL . "wiki/versions/" . $wiki_doc->get_id() . "\">&raquo; " . gettext("enter version management") . "</a></li>");
			}
			else
			{
				$content->setVariable("NUMBER_VERSIONS", "<li>" . "Keine Vorgängerversionen verfügbar" . "</li>" );
			}
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
			$content->setVariable( "LINK", "keine Links vorhanden");
			$content->parse( "BLOCK_LINKS" );
		}
		else
		{
			foreach( $links as $doc )
			{
				if ( $doc instanceof \steam_document )
				{
					$name = str_replace( ".wiki", "", h( $doc->get_name() ) );
					$link = PATH_URL . "wiki/entry/" . $doc->get_id() . "/";			
					$content->setVariable( "LINK", '<li>&raquo; <a href="' . $link . '">' . $name . '</a></li>' );
					$content->parse( "BLOCK_LINKS" );
				}
			}
		}
		
		$wiki_html_handler->set_main_html( $content->get() );
		
		//$rootlink = \lms_steam::get_link_to_root( $wiki_container );
		(WIKI_FULL_HEADLINE) ? 
		$headline = array(
						$rootlink[0],
						$rootlink[1],
						array( "link" => $rootlink[1]["link"] . "{$place}/", "name" => gettext("{$place}")),
						array( "name" =>  h($wiki_container->get_name()), "link" => PATH_URL . "wiki/Index/" . $wiki_container->get_id() . "/"),
					) :
		$headline = array(array( "name" =>  h($wiki_container->get_name()), "link" => PATH_URL . "wiki/Index/" . $wiki_container->get_id() . "/"));
		
		
		if(!$is_prev_version)
		{
			$headline[] = array( "link" => "", "name" => str_replace( ".wiki", "", h($wiki_doc->get_name()) ) );
		} else {
			$headline[] = array( "link" => PATH_URL . "wiki/entry/" . $wiki_doc->get_id() . "/", "name" => str_replace( ".wiki", "", h($wiki_doc->get_name()) ) );
			$headline[] = array( "link" => PATH_URL . "wiki/versions/" . $wiki_doc->get_id(), "name" => gettext("Version management"));
			$headline[] = array( "link" => "", "name" => "Version" . " " . $version_doc->get_version() . " (" . gettext("Preview") . ")");
		}

		
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($wiki_html_handler->get_html());
		$frameResponseObject->addWidget($rawHtml);
		$frameResponseObject->setHeadline($headline);
		return $frameResponseObject;
	}
}
?>