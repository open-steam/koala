<?php

require_once( "HTML/Template/IT.php" );
require_once( PATH_LIB . "format_handling.inc.php" );
// require_once( "steam_weblog.class.php" );

class lms_weblog extends koala_object {
	private $steam_weblog;
	private $template;
	private $entrycount = FALSE;
	private $date_objects = FALSE;

	public function __construct( $steam_weblog ) {
		if ( ! $steam_weblog instanceof steam_calendar ) {
			throw new Exception( "not a weblog", E_PARAMETER );
		}
		$this->steam_weblog = $steam_weblog;
		$this->steam_object = $steam_weblog;
		$this->template = Weblog::getInstance()->loadTemplate("weblog_index.template.html");
		//define("OBJ_ID", $steam_weblog->get_id());
		if ( defined( "OBJ_ID" ) ) {
			//$this->template = Weblog::getInstance()->loadTemplate("weblog_index.template.html");
			//$this->template = new HTML_TEMPLATE_IT();
			//$this->template->loadTemplateFile( PATH_EXTENSIONS . "weblog/ui/html/weblog_index.template.html" );
			$obj = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), OBJ_ID, CLASS_CALENDAR );
			$dsc = $obj->get_attribute( "OBJ_DESC" );
			if ( ! empty( $dsc ) )
			{
				$this->template->setCurrentBlock( "BLOCK_DESCRIPTION" );
				$this->template->setVariable( "WEBLOG_DESCRIPTION", get_formatted_output( $dsc ) );
				$this->template->parse( "BLOCK_DESCRIPTION" );
			}
		}
	}

	public function get_date_objects() {
		if ($this->date_objects === FALSE) {
			$this->date_objects = $this->steam_weblog->get_date_objects();
		}
		return $this->date_objects;
	}

	public function count_entries() {
		if ($this->entrycount === FALSE) {
			$this->entrycount = count( $this->get_date_objects() );
		}
		return $this->entrycount;
	}

	public function set_menu( $context = "entries" )
	{
		$user = lms_steam::get_current_user();
		$menue = array();
		defined("OBJ_ID") OR define("OBJ_ID", $this->steam_weblog->get_id());
		if ( $this->steam_weblog->check_access_insert( $user ) )
		{
			if ( !isset( $menue["entries"] ) || !is_array( $menue["entries"] ) ) $menue["entries"] = array();
			$menue["entries"][] = array(
              "link" => PATH_URL . "weblog/post/" . OBJ_ID. "/" ,
              "name" => gettext( "Create new entry" )
			);
			$menue["entries"][] = array(
              "link" => PATH_URL . "weblog/categorycreate/" . OBJ_ID . "/",
              "name" => gettext( "Create new category" )
			);
			if ( !isset( $menue["category"] ) || !is_array( $menue["category"] ) ) $menue["category"] = array();
			$menue["category"][] = array(
              "link" => PATH_URL . "weblog/createentry/" . OBJ_ID . "/?category=" . OBJ_ID,
              "name" => gettext( "Create new entry" )
			);
		}

		if ( $this->steam_weblog->check_access_write( $user ) )
		{
			if ( !is_array( $menue["entries"] ) ) $menue["entries"] = array();
			$menue["entries"][] = array(
              "link" => PATH_URL . "weblog/blogroll/" . OBJ_ID . "/",
              "name" => gettext( "Edit Blogroll" )
			);
			$menue["entries"][] = array(
              "link" => PATH_URL . "weblog/edit/" . OBJ_ID . "/",
              "name" => gettext( "Preferences" )
			);
			$menue["entries"][] = array(
              "link" => PATH_URL . "weblog/delete/" . OBJ_ID . "/",
              "name" => gettext( "Delete Weblog" )
			);
			if ( !isset( $menue["entry"] ) || !is_array( $menue["entry"] ) ) $menue["entry"] = array();
			$menue["entry"][] = array(
              "link" => PATH_URL . "weblog/edit/" . OBJ_ID . "/",
              "name" => gettext( "Edit Entry" )
			);
			$menue["entry"][] = array(
              "link" => PATH_URL . "weblog/delete/" . OBJ_ID . "/",
              "name" => gettext( "Delete Entry")
			);
			if ( !is_array( $menue["category"] ) ) $menue["category"] = array();
			$menue["category"][] = array(
              "link" => PATH_URL . "weblog/edit/" . OBJ_ID . "/",
              "name" => gettext( "Edit Category" )
			);
			$menue["category"][] = array(
              "link" => PATH_URL . "weblog/delete/" . OBJ_ID . "/",
              "name" => gettext( "Delete Category" )
			);
		}
		$rss_feeds = $user->get_attribute("USER_RSS_FEEDS");
		$is_watching = FALSE;
		if (is_array($rss_feeds)) {
			foreach(array_keys($rss_feeds) as $item) {
				if ($item == $this->steam_weblog->get_id()) {
					$is_watching=TRUE;
				}
			}
		}
		$fctns = $menue[ $context ];
		if ($is_watching) {
			$fctns[] = array( "name" => gettext( "End watching" ), "link" => PATH_URL . "weblog/" . OBJ_ID . "/?action=delete_bookmark&unsubscribe=" . $this->steam_weblog->get_id() );
		} else {
			$fctns[] = array( "name" => gettext( "Watch this blog" ), "link" => PATH_URL . "weblog/" . OBJ_ID . "/?action=bookmark_rss" );
		}
		/*if(!isset($this->template)) {
			$this->template = Weblog::getInstance()->loadTemplate("weblog_index.template.html");
		}*/
		$this->template->setCurrentBlock( "BLOCK_ADMIN" );
		foreach( $fctns as $fctn )
		{
			$this->template->setCurrentBlock( "BLOCK_FUNCTION" );
			$this->template->setVariable( "LINK_FCTN", $fctn[ "link" ] );
			$this->template->setVariable( "LABEL_FCTN", $fctn[ "name" ] );
			$this->template->parse( "BLOCK_FUNCTION" );
		}
		$this->template->parse( "BLOCK_ADMIN" );
	}

	public function get_item_data( $item ) {
		$tnr = array();
		$tnr["ATTRIBUTES"] = $item->get_attributes(
		array(
                    "DATE_START_DATE",
                    "DATE_TITLE",
                    "DATE_PODCAST",
                    "OBJ_DESC",
                    "OBJ_KEYWORDS",
                    "DOC_MIME_TYPE",
                    "DATE_DESCRIPTION"
                    ), TRUE
                    );
                    $tnr["CREATOR"] = $item->get_creator(TRUE);
                    $buffer_result = $GLOBALS["STEAM"]->buffer_flush();
                    $author_dates = $buffer_result[$tnr["CREATOR"]]->get_attributes( array( "USER_FIRSTNAME", "USER_FULLNAME" ) );
                    $result = $buffer_result[$tnr["ATTRIBUTES"]];
                    $result[ "OBJ_ID" ] = $item->get_id();
                    $result[ "CONTENT" ] = $buffer_result[$tnr["ATTRIBUTES"]][ "DATE_DESCRIPTION" ];
                    $result[ "OBJ_NAME" ] = $buffer_result[$tnr["ATTRIBUTES"]][ "DATE_TITLE" ];
                    $result[ "AUTHOR" ] = $author_dates[ "USER_FIRSTNAME" ] . " " . $author_dates[ "USER_FULLNAME" ];
                    return $result;
	}

	public function get_items( $id )
	{
		$weblog = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $id, CLASS_CALENDAR );
		$items = $weblog->get_date_objects();
		$res = array();
		$i = 0;
		$data_tnr = array();
		foreach( $items as $item )
		{
			$tnr = array();
			$tnr["ATTRIBUTES"] = $item->get_attributes(
			array(
                            "DATE_START_DATE",
                            "DATE_TITLE",
                            "DATE_PODCAST",
                            "OBJ_DESC",
                            "OBJ_KEYWORDS",
                            "DOC_MIME_TYPE",
                            "DATE_DESCRIPTION"
                            ), TRUE
                            );
                            $tnr["CREATOR"] = $item->get_creator(TRUE);
                            $data_tnr[$i] = $tnr;
                            $i++;
		}
		$data_result = $GLOBALS["STEAM"]->buffer_flush();
		$i = 0;
		$author_tnr = array();
		foreach( $items as $item )
		{
			$author_tnr[$i] = $data_result[$data_tnr[$i]["CREATOR"]]->get_attributes( array( "USER_FIRSTNAME", "USER_FULLNAME" ), TRUE );
			$i++;
		}
		$author_result = $GLOBALS["STEAM"]->buffer_flush();
		$result = array();
		$i = 0;
		foreach( $items as $item )
		{
			$result = $data_result[$data_tnr[$i]["ATTRIBUTES"]];
			$result[ "OBJ_ID" ] = $item->get_id();
			$result[ "CONTENT" ] = $data_result[$data_tnr[$i]["ATTRIBUTES"]][ "DATE_DESCRIPTION" ];
			$result[ "OBJ_NAME" ] = $data_result[$data_tnr[$i]["ATTRIBUTES"]][ "DATE_TITLE" ];
			$result[ "AUTHOR" ] = $author_result[$author_tnr[$i]][ "USER_FIRSTNAME" ] . " " . $author_result[$author_tnr[$i]][ "USER_FULLNAME" ];
			$res[] = $result;
			$i++;
		}
		return $res;
	}

	public function set_main_html( $html_code )
	{
		$this->template->setVariable( "WEBLOG_MAIN_HTML", $html_code );
	}

	public function set_widget_html( $widget_title, $widget_html )
	{
		$this->template->setCurrentBlock( "BLOCK_WIDGET" );
		$this->template->setVariable( "WIDGET_TITLE", $widget_title );
		$this->template->setVariable( "WIDGET_HTML_CODE", $widget_html );
		$this->template->parse( "BLOCK_WIDGET" );
	}

	public function get_html()
	{
		return $this->template->get();
	}

	public function get_name()
	{
		return $this->steam_weblog->get_name();
	}

	public function set_widget_categories()
	{
		$t = Weblog::getInstance()->loadTemplate("widget_weblog_categories.template.html");
		//$t = new HTML_TEMPLATE_IT();
		//$t->loadTemplateFile( PATH_EXTENSIONS . "weblog/ui/html/widget_weblog_categories.template.html" );
		$t->setVariable( "STYLEPATH", PATH_STYLE );
		$t->setVariable( "LINK_ALL_ENTRIES", PATH_URL . "weblog/category/" . $this->steam_weblog->get_id() . "/?entries=all" );
		$t->setVariable( "LABEL_ALL_ENTRIES", gettext( "All entries" ) . " (" . $this->count_entries() . ")" );
		$categories = $this->steam_weblog->get_categories();

		$tnr = array();
		foreach( $categories as $category ) {
			$tnr[$category->get_id()]["count"] = $category->count_inventory( TRUE );
			$tnr[$category->get_id()]["name"] = $category->get_name( TRUE );
		}
		$result = $GLOBALS["STEAM"]->buffer_flush();

		foreach( $categories as $category ) {
			$t->setCurrentBlock( "BLOCK_CATEGORY" );
			$t->setVariable( "BLOCK_STYLE_PATH", PATH_STYLE );
			$t->setVariable( "LINK_CATEGORY", PATH_URL . "weblog/category/" . $category->get_id() . "/" );
			$t->setVariable( "LABEL_CATEGORY", h( $result[$tnr[$category->get_id()]["name"]] ) . "(" . $result[$tnr[$category->get_id()]["count"]] . ")" );
			$t->parse( "BLOCK_CATEGORY" );
		}
		$this->set_widget_html( gettext( "Categories" ), $t->get() );
	}

	public function get_blogroll() {
		return $this->steam_weblog->get_blogroll();
	}

	public function set_widget_blogroll( )
	{
		$t = Weblog::getInstance()->loadTemplate("widget_weblog_blogroll.template.html");
		//$t = new HTML_TEMPLATE_IT();
		//$t->loadTemplateFile( PATH_EXTENSIONS . "weblog/ui/html/widget_weblog_blogroll.template.html" );
		$blogroll = $this->steam_weblog->get_blogroll_list();
		if ( count( $blogroll ) == 0  )
		{
			return NULL;
		}
		foreach( $blogroll as $weblog )
		{
			$t->setCurrentBlock( "BLOCK_WEBLOG" );
			$t->setVariable( "LINK_WEBLOG", $weblog->get_url() );
			$t->setVariable( "WEBLOG_DESC", h($weblog->get_attribute( "OBJ_DESC" )) );
			$t->setVariable( "LABEL_WEBLOG", h($weblog->get_name()) );
			$t->parse( "BLOCK_WEBLOG" );
		}
		$this->set_widget_html( gettext( "Blogroll" ), $t->get() );
	}

	public function set_widget_archive( )
	{
		$months = $this->steam_weblog->get_archives(  $this->get_date_objects( ) );
		$t = Weblog::getInstance()->loadTemplate("widget_weblog_archive.template.html");
		
		//$t = new HTML_TEMPLATE_IT();
		//$t->loadTemplateFile( PATH_EXTENSIONS . "weblog/ui/html/widget_weblog_archive.template.html" );
		while ( list( $year_month, $no_entries ) = each( $months) )
		{
			$year_month_str = explode( "-", $year_month );
			$timestamp = mktime( 0, 0, 0, $year_month_str[ 1 ], 1, $year_month_str[ 0 ] );
			$t->setCurrentBlock( "BLOCK_ARCHIVE" );
			$t->setVariable( "LINK_TO_ARCHIVE", PATH_URL . "weblog/archive/" . $this->steam_weblog->get_id() . "/" . $timestamp . "/");
			$t->setVariable( "LABEL_ARCHIVE", strftime( "%B %g", $timestamp ) );
			$t->parse( "BLOCK_ARCHIVE" );
		}
		$t->setVariable( "LABEL_CURRENT_POSTS", gettext( "Current posts") );
		$this->set_widget_html( gettext( "Archive" ), $t->get() );
	}

	public function set_widget_access( $grp )
	{
		$access_descriptions = lms_weblog::get_access_descriptions( $grp );
		$act_access = $this->steam_weblog->get_attribute(KOALA_ACCESS);
		$access_descriptions = $access_descriptions[$act_access];
		$access = $access_descriptions["summary_short"] . ": " . $access_descriptions["label"];
		if ($act_access == PERMISSION_PRIVATE_READONLY) {
			$creator = $this->steam_weblog->get_creator();
			if ($creator->get_id() != lms_steam::get_current_user()->get_id() ) {
				$access = str_replace( "%NAME", $creator->get_name(), $access);
			} else {
				$access = gettext("Only members can read and comment. Only you can post.");
			}
		}
		$t = Weblog::getInstance()->loadTemplate("widget_weblog_access.template.html");
		//$t = new HTML_TEMPLATE_IT();
		//$t->loadTemplateFile( PATH_EXTENSIONS . "weblog/ui/html/widget_weblog_access.template.html" );
		$t->setCurrentBlock("BLOCK_ACCESS");
		$t->setVariable("LABEL_ACCESS", $access);
		$t->parse("BLOCK_ACCESS");
		$this->set_widget_html( gettext( "Access" ), $t->get() );
	}

	public function set_podcast_link()
	{
		$this->template->setCurrentBlock( "BLOCK_PODCAST" );
		$this->template->setVariable( "PODCAST_LINK", "pcast://131.234.154.23/locomotion/public/services/feeds/podcast.php?id=" . $this->steam_weblog->get_id() );
		$this->template->setVariable( "PODCAST_LOGO", PATH_STYLE . "images/podcaster_full_small.jpg" );
		$this->template->parse( "BLOCK_PODCAST" );
	}


	public function print_entries( $date_objects = array(), $show_dates = TRUE )
	{
		$t = Weblog::getInstance()->loadTemplate("weblog_entries.template.html");
		//$t = new HTML_TEMPLATE_IT();
		//$t->loadTemplateFile( PATH_EXTENSIONS . "weblog/ui/html/weblog_entries.template.html" );
		$user = lms_steam::get_current_user();

		if ( count( $date_objects ) == 0 )
		{
			$date_objects = $this->get_date_objects( );
			usort( $date_objects, "sort_dates_ascending" );
		}

		if ( count( $date_objects ) == 0 )
		{
			$t->setVariable( "LABEL_NO_ENTRY_FOUND", "<h3>" . gettext( "No posts yet." ) . "</h3>" );
			$this->set_main_html( $t->get() );
			return NULL;
		}

		$ld = 0;
		// TODO FIX IT !!!
		$data_tnr = array();
		foreach( $date_objects as $date_object )	{
			$data_tnr[$date_object->get_id()] = array();
			$data_tnr[$date_object->get_id()]["comments"] = $date_object->get_annotations( FALSE, TRUE );
			$data_tnr[$date_object->get_id()]["creator"] = $date_object->get_creator(TRUE);
			$data_tnr[$date_object->get_id()]["is_writer"] = $date_object->check_access_write( $user, TRUE );
			$data_tnr[$date_object->get_id()]["attributes"] = $date_object->get_attributes(
			array(
                "DATE_TITLE",
                "DATE_DESCRIPTION",
                "DATE_START_DATE",
                "DATE_CATEGORY",
                "DATE_PODCAST",
                "OBJ_KEYWORDS"
                ), TRUE
                );
		}
		$data_result = $GLOBALS["STEAM"]->buffer_flush();

		$creator = FALSE;
		$category = FALSE;
		$creators = array();
		$categories = array();
		foreach( $date_objects as $date_object )	{
			$creator = $data_result[$data_tnr[$date_object->get_id()]["creator"]];
			$creators[$creator->get_id()] = $creator;
			$category = $data_result[$data_tnr[$date_object->get_id()]["attributes"]]["DATE_CATEGORY"];
			if (is_object($category)) {
				$categories[$category->get_id()] = $category;
			}
		}

		$creator_data = steam_factory::get_attributes($GLOBALS["STEAM"]->get_id(), array_values($creators), array(OBJ_NAME, USER_FIRSTNAME, USER_FULLNAME));

		$category_data = steam_factory::get_attributes($GLOBALS["STEAM"]->get_id(), array_values($categories), array(OBJ_NAME));

		foreach( $date_objects as $date_object )
		{
			$t->setCurrentBlock( "BLOCK_ARTICLE" );
			$entry = $data_result[$data_tnr[$date_object->get_id()]["attributes"]];
			$comments = $data_result[$data_tnr[$date_object->get_id()]["comments"]];
			$cd = strftime( "%G%m%d", $entry[  "DATE_START_DATE" ] ) ;
			if ( ( $cd != $ld || $ld == 0 ) && $show_dates )
			{
				$t->setCurrentBlock( "BLOCK_DATE" );
				$t->setVariable( "VALUE_DATE", strftime( "%d. %B %G", $entry[ "DATE_START_DATE" ] ) );
				$t->parse( "BLOCK_DATE" );
			}

			$t->setVariable( "VALUE_ARTICLE_SUBJECT", h($entry[ "DATE_TITLE" ]) );
			$t->setVariable( "VALUE_ARTICLE_TEXT", get_formatted_output( $entry[ "DATE_DESCRIPTION" ] ) );
			$creator = $data_result[$data_tnr[$date_object->get_id()]["creator"]];
			$t->setVariable( "VALUE_POSTED_BY", str_replace( "%NAME", "<a href=\"" . PATH_URL . "user/" . $creator_data[$creator->get_id()][OBJ_NAME] . "/\">" . h($creator_data[$creator->get_id()][USER_FIRSTNAME] ) . " " . h($creator_data[$creator->get_id()][USER_FULLNAME]) . "</a>", gettext( "Posted by %NAME" ) ) );

			if ( $show_dates )
			{
				$date_or_time = strftime( "%R", $entry[ "DATE_START_DATE" ] );
			}
			else
			{
				$date_or_time = strftime( "%x %R", $entry[ "DATE_START_DATE" ] );
			}
			$t->setVariable( "VALUE_DATE_TIME", $date_or_time );

			$t->setVariable( 'POST_ID', $date_object->get_id() );
			$t->setVariable( 'POST_PERMALINK', PATH_URL . 'weblog/' . $this->steam_weblog->get_id() . '/#comment' . $date_object->get_id() );
			$t->setVariable( "POST_PERMALINK_LABEL", gettext( "permalink" ) );

			if ( $data_result[$data_tnr[$date_object->get_id()]["is_writer"]] )
			{
				$t->setCurrentBlock( "BLOCK_OWN_POST" );
				$t->setVariable( "POST_LINK_DELETE", PATH_URL . "weblog/entrydelete/" . $date_object->get_id() . "/" );
				$t->setVariable( "POST_LABEL_DELETE", gettext( "delete" ) );
				$t->setVariable( "POST_LINK_EDIT", PATH_URL . "weblog/entryedit/" . $date_object->get_id() . "/" );
				$t->setVariable( "POST_LABEL_EDIT", gettext( "edit" ) );
				$t->parse( "BLOCK_OWN_POST" );
			}

			$category = $entry[ "DATE_CATEGORY" ];
			if ( is_object( $category ) )
			{
				$t->setVariable( "LABEL_IN" , gettext( "in" ) );
				$t->setVariable( "VALUE_CATEGORY", "<a href=\"" . PATH_URL . "weblog/" . $category->get_id() . "/\">" . h($category_data[$category->get_id()][OBJ_NAME] ) . "</a>" );
			}
			else
			{
				$t->setVariable( "VALUE_CATEGORY", gettext( "no category" ) );
			}
			$t->setVariable( "LINK_COMMENTS", PATH_URL . "weblog/comments/" . $date_object->get_id() . "/"  );
			$t->setVariable( "LABEL_COMMENTS", count( $comments) . " " . ( (count($comments) == 1) ? gettext( "comment") : gettext("comments") ) );
			$t->parse( "BLOCK_ARTICLE" );
			$ld = $cd;
		}
		$this->set_main_html( $t->get() );
	}

	static public function get_access_descriptions( $grp ) {
		$private = gettext("Private");
		$public = gettext("Public");
		$staff_only = gettext("Staff only");
		$ret = array(
		PERMISSION_UNDEFINED => array(
      "label" =>  gettext( "Not defined." ),
      "summary_short" => gettext("-"))
		);
		if ( (string) $grp->get_attribute( "OBJ_TYPE" ) == "course" )
		{
			$ret += array(
			PERMISSION_PUBLIC => array(
          "label" => gettext( "All users can read and make posts." ),
          "summary_short" => $public,
          "members" => 0,
          "steam" => SANCTION_READ | SANCTION_ANNOTATE | SANCTION_INSERT,
			),
			PERMISSION_PUBLIC_READONLY => array(
          "label" => gettext( "All users can read and make comments. Only members can post." ),
          "summary_short" => $public,
          "members" => SANCTION_ANNOTATE | SANCTION_INSERT,
          "steam" => SANCTION_READ,
			),
			PERMISSION_PUBLIC_READONLY_STAFF => array(
          "label" => gettext( "All users can read and make comments. Only staff members can post." ),
          "summary_short" => $public,
          "members" => 0,
          "steam" => SANCTION_READ | SANCTION_ANNOTATE,
			),
			PERMISSION_PRIVATE => array(
          "label" => gettext( "Only members can read and make posts." ),
          "summary_short" => $private,
          "members" =>  SANCTION_READ | SANCTION_ANNOTATE | SANCTION_INSERT,
          "steam" => 0,
			),
			PERMISSION_PRIVATE_READONLY => array(
          "label" => gettext( "Only members can read and comment. Only staff members of can post." ),
          "summary_short" => $private,
          "members" =>  SANCTION_READ | SANCTION_ANNOTATE,
          "steam" => 0,
			),
			PERMISSION_PRIVATE_STAFF => array(
          "label" => gettext( "Only staff members can read and make posts." ),
          "summary_short" => $staff_only,
          "members" => 0,
          "steam" => 0,
			)
			);
		} else {
			$ret += array(
			PERMISSION_PUBLIC => array(
          "label" => gettext( "All users can read and post new entries." ),
          "summary_short" => $public,
          "members" => 0,
          "steam" => SANCTION_READ | SANCTION_ANNOTATE | SANCTION_INSERT,
			),
			PERMISSION_PUBLIC_READONLY => array(
          "label" => gettext( "All users can read and comment. Only members can make posts." ),
          "summary_short" => $public,
          "members" => SANCTION_INSERT,
          "steam" => SANCTION_READ | SANCTION_ANNOTATE,
			),
			PERMISSION_PRIVATE => array(
          "label" => gettext( "Only members can read and make posts." ),
          "summary_short" => $private,
          "members" => SANCTION_READ | SANCTION_ANNOTATE | SANCTION_INSERT,
          "steam" => 0,
			),
			PERMISSION_PRIVATE_READONLY => array(
          "label" => gettext( "Only members can read and comment. Only %NAME can post." ),
          "summary_short" => $private,
          "members" =>  SANCTION_READ | SANCTION_ANNOTATE,
          "steam" => 0,
			)
			);
		}
		return $ret;
	}




	public function get_changes( $start_time ) {
		// TODO:
	}
}

?>
