<?php
namespace Wiki\Commands;
class Glossary extends \AbstractCommand implements \IFrameCommand {

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
		$portal = \lms_portal::get_instance();
		$portal->initialize( GUEST_NOT_ALLOWED );

		// Disable caching
		// TODO: Work on cache handling. An enabled cache leads to bugs
		// if used with the wiki.
		\CacheSettings::disable_caching();

		$WikiExtension = \Wiki::getInstance();
		$WikiExtension->addCSS();
		$wiki_container = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		$wiki_html_handler = new \koala_wiki($wiki_container);
		$wiki_html_handler->set_admin_menu("index", $wiki_container);

		// chronic
		\ExtensionMaster::getInstance()->getExtensionById("Chronic")->setCurrentObject($wiki_container);

		$content = $WikiExtension->loadTemplate("wiki_entries.template.html");

		if($wiki_container->get_attribute("UNIT_TYPE")){
		    $place = "units";
		}
		else{
		    $place = "communication";
		}

		if(ENABLED_SEARCH & WIKI_SEARCH_ENABLED){
			$search_widget = new \search_widget();
			$search_widget->set_container_id($wiki_container->get_id());
			$content->setVariable("SEARCH_WIDGET", $search_widget->render());
		}

		if (!($wiki_container->check_access_read())) {
				$errorHtml = new \Widgets\RawHtml();
				$errorHtml->setHtml("Das Wiki kann nicht angezeigt werden, da Sie nicht über die erforderlichen Leserechte verfügen.");
				$frameResponseObject->addWidget($errorHtml);
				return $frameResponseObject;
		}

		$cache = get_cache_function( $wiki_container->get_id(), 600 );
		$wiki_entries = $cache->call( "koala_wiki::get_items", $wiki_container->get_id() );

		$recently_changed = new \LinkedList( 5 );
		$most_discussed   = new \LinkedList( 5 );
		$latest_comments  = new \LinkedList( 5 );

		$no_wiki_entries = count( $wiki_entries );

		if ( $no_wiki_entries > 0 ){
			$first_char = "";
			for( $i = 0; $i < $no_wiki_entries; $i++ ){
				$this_char = substr( strtoupper( $wiki_entries[ $i ][ "OBJ_NAME" ] ), 0, 1  );
				if ( $this_char > $first_char ){
					$first_char = $this_char;
					if ( $i > 1 ){
						$content->parse( "BLOCK_CHARACTER" );
					}
					$content->setCurrentBlock( "BLOCK_CHARACTER" );
					$content->setVariable( "FIRST_CHAR", h($this_char) );
				}
				$char_articles = array();
				while ( $i < $no_wiki_entries && $this_char == substr( strtoupper( $wiki_entries[ $i ][ "OBJ_NAME" ] ), 0, 1 ) ){
					$char_articles[] = $wiki_entries[ $i ];
					if ( $recently_changed->can_be_added( $wiki_entries[ $i ][ "DOC_LAST_MODIFIED" ] ) ){
						$recently_changed->add_element(
						$wiki_entries[ $i ][ "DOC_LAST_MODIFIED" ],
						$wiki_entries[ $i ]
						);
					}
					if ( isset($wiki_entries[ $i ][ "COMMENTS_NO" ]) && $most_discussed->can_be_added( $wiki_entries[ $i ][ "COMMENTS_NO" ] ) && $wiki_entries[ $i ][ "COMMENTS_NO" ] > 1 ){
						$most_discussed->add_element(
						$wiki_entries[ $i ][ "COMMENTS_NO" ],
						$wiki_entries[ $i ]
						);
					}
					if ( isset($wiki_entries[ $i ][ "COMMENTS_LAST" ] ) && $latest_comments->can_be_added( $wiki_entries[ $i ][ "COMMENTS_LAST" ] ) && $wiki_entries[ $i ][ "COMMENTS_LAST" ] > 0 ){
						$latest_comments->add_element(
						$wiki_entries[ $i ][ "COMMENTS_LAST" ],
						$wiki_entries[ $i ]
						);
					}
					$i++;
				}
				$i--;
				$no_articles_in_first_row = ceil( count( $char_articles ) / 2 );

				$content->setCurrentBlock( "BLOCK_COLUMN" );
				for ( $c = 0; $c < $no_articles_in_first_row; $c++ ){
					$content->setCurrentBlock( "BLOCK_ARTICLE" );
					$content->setVariable( "ARTICLE_LINK", PATH_URL . "wiki/entry/" . $char_articles[ $c ][ "OBJ_ID" ] . "/" );
					$content->setVariable( "ARTICLE_NAME", str_replace( ".wiki", "", h($char_articles[ $c ][ "OBJ_NAME" ] )) );
					$content->parse( "BLOCK_ARTICLE" );
				}
				$content->parse( "BLOCK_COLUMN" );

				$content->setCurrentBlock( "BLOCK_COLUMN" );
				for ( $c = $no_articles_in_first_row; $c < count( $char_articles ); $c++ ){
					$content->setCurrentBlock( "BLOCK_ARTICLE" );
					$content->setVariable( "ARTICLE_LINK", PATH_URL . "wiki/entry/" . $char_articles[ $c ][ "OBJ_ID" ] . "/" );
					$content->setVariable( "ARTICLE_NAME", str_replace( ".wiki", "", h($char_articles[ $c ][ "OBJ_NAME" ] )) );
					$content->parse( "BLOCK_ARTICLE" );
				}
				$content->parse( "BLOCK_COLUMN" );
				$content->parse( "BLOCK_CHARACTER" );
			}

			foreach( $wiki_entries as $entry ){
				$content->setCurrentBlock( "BLOCK_ARTICLE" );
				$content->setVariable( "VALUE_WIKI_ENTRY", h($entry[ "OBJ_NAME" ]) );
				$content->setVariable( "LINK_WIKI_ENTRY", PATH_URL . "wiki/entry/" . $wiki_container->get_id() . "/" . h($entry[ "OBJ_NAME" ]) );
				$content->setVariable( "LABEL_LAST_MODIFICATION", gettext( "last edited" ) );
				$content->setVariable( "VALUE_POSTED_BY", $entry[ "DOC_USER_MODIFIED" ] );
				$content->setVariable( "POST_PERMALINK", PATH_URL . "wiki/entry/" . $entry[ "OBJ_ID" ] . "/" );
				$content->setVariable( "VALUE_DATE_TIME", strftime( "%x %X", $entry[ "OBJ_CREATION_TIME" ] ) );
				$content->setVariable( "POST_PERMALINK_LABEL", gettext( "permalink" ) );
				$content->parse( "BLOCK_ARTICLE" );
			}
		}
		else{
			$content->setVariable('NO_ENTRIES', "Es existieren keine Wiki Einträge.");
		}

		/*TODO: check if these functions can be deleted
		$wiki_html_handler->set_widget_latest_comments( $latest_comments );
		$wiki_html_handler->set_widget_last_changed( $recently_changed );
		$wiki_html_handler->set_widget_most_discussed( $most_discussed );
		$wiki_html_handler->set_widget_access( $grp );*/

		(WIKI_RSS) ? $portal->set_rss_feed(PATH_URL . "wiki/RSS/" . $wiki_container->get_id() , gettext("Feed"), gettext("Subscribe to this forum's Newsfeed")) : "";
		$wiki_html_handler->set_main_html( $content->get());

		(WIKI_FULL_HEADLINE) ? $headline = array( $rootlink[0], $rootlink[1], array("link" => $rootlink[1]["link"] . "{$place}/", "name" => gettext("{$place}")), array( "link" => "", "name" => '<svg style="width:16px; height:16px; color:#3a6e9f; top:3px; position:relative;"><use xlink:href="' . \Explorer::getInstance()->getAssetUrl() . 'icons/mimetype/svg/wiki.svg#wiki"></use></svg> ' . h($wiki_container->get_name() )) ) :
		$headline = array(array( "link" => "", "name" => '<svg style="width:16px; height:16px; color:#3a6e9f; top:3px; position:relative;"><use xlink:href="' . \Explorer::getInstance()->getAssetUrl() . 'icons/mimetype/svg/wiki.svg#wiki"></use></svg> ' . h($wiki_container->get_name())));

		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($wiki_html_handler->get_html());
		$frameResponseObject->addWidget($rawHtml);
		$frameResponseObject->setHeadline($headline);
		return $frameResponseObject;
	}
}
?>
