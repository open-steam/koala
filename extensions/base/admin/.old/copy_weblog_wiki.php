<?php
include_once( "../etc/koala.conf.php" );
include_once( PATH_LIB . "url_handling.inc.php" );
include_once( PATH_LIB . "format_handling.inc.php" );

$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );
$user = lms_steam::get_current_user();
$course = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $_GET[ "course" ] );
$semester_name = $course->get_attribute("COURSE_SEMESTER");
$semester_object = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), "Courses." . $semester_name );
$admin_group = steam_factory::get_group( $GLOBALS["STEAM"]->get_id(), "Admin" );
$is_semester_admin = lms_steam::is_semester_admin($semester_object, $user);

//TODO: semester_admin check
//if ( !$admin_group->is_member( $user ) && !$is_semester_admin )
if ( !is_object($admin_group) || !$admin_group->is_member( $user ) )
{
	header("location:/");
	exit;
}

if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" )
{
	if ( $_POST[ "copy" ] )
	{
		$weblogs_ids = $_POST[ "weblogs" ];
		$wiki_ids = $_POST[ "wikis" ];
		
		//TODO: Update Cache
		
	    $_SESSION[ "confirmation" ] = gettext( "Copy complete." );
		header( "Location: " . PATH_URL . "semester/" . $semester_name . "/?mode=edit" );
	    exit;
	}
}

$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "copy_weblog_wiki.template.html" );

$group = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), $course->get_groupname() . ".learners" );
$workroom = $group->get_workroom();
$read_access = $workroom->check_access_read( $user );

if (!$read_access)
{
  throw new Exception( "No read access on container: id=" . $workroom->get_id(), E_USER_RIGHTS );
}

$cache = get_cache_function( lms_steam::get_current_user()->get_name(), 600 );
$communication_objects = $cache->call( "lms_steam::get_group_communication_objects", $workroom->get_id(), CLASS_MESSAGEBOARD | CLASS_CALENDAR | CLASS_CONTAINER | CLASS_ROOM );

$weblogs = array();
$wikis = array();

foreach ( $communication_objects as $object )
{
	if ($object["OBJ_CLASS"] === "steam_calendar")
	{
		$weblogs[] = $object;
	}
	else if ( ($object["OBJ_CLASS"] === "steam_container" || $object["OBJ_CLASS"] === "steam_room") && ($object["OBJ_TYPE"] != null && ($object["OBJ_TYPE"] == "KOALA_WIKI" || $object["OBJ_TYPE"] == "container_wiki_koala" ) ) )
	{
		$wikis[] = $object;
	}
}

/// WEBLOGS ///
$content->setVariable( "LABEL_WEBLOGS", gettext( "Weblogs" ) );
if( count( $weblogs ) > 0 )
{
	$content->setCurrentBlock( "BLOCK_WEBLOGS" );
		$content->setVariable( "LABEL_WEBLOG_DESCRIPTION", gettext( "Weblog / description" ) );
		$content->setVariable( "LABEL_WEBLOG_ENTRIES", gettext( "Entries" ) );
		$content->setVariable( "LABEL_WEBLOG_ACCESS", gettext( "Access" ) );
	  	$access_descriptions = lms_weblog::get_access_descriptions( $group );
	
	  	foreach( $weblogs as $weblog )
		{
			$cache = get_cache_function( $weblog[ "OBJ_ID" ], 600 );
			$entries  = $cache->call( "lms_weblog::get_items", $weblog[ "OBJ_ID" ] );
			
			$content->setCurrentBlock( "BLOCK_WEBLOG" );
				$content->setVariable( "WEBLOG_ID", $weblog[ "OBJ_ID" ] );
				$content->setVariable( "NAME_WEBLOG", h($weblog[ "OBJ_NAME" ]) );
				$content->setVariable( "LINK_WEBLOG", PATH_URL . "weblog/" . $weblog[ "OBJ_ID" ] . "/" );
				$content->setVariable( "WEBLOG_OBJ_DESC", get_formatted_output($weblog[ "OBJ_DESC" ]) );
	    		$title = $access_descriptions[$weblog["KOALA_ACCESS"]]["label"];

	    		if ( $weblog["KOALA_ACCESS"] == PERMISSION_PRIVATE_READONLY && !($group instanceof koala_html_course))
	    		{
	      			$obj = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $weblog[ "OBJ_ID"], CLASS_CALENDAR );
	      			$creator = $obj->get_creator();
	      			if ($creator->get_id() != lms_steam::get_current_user()->get_id() )
	      			{
	        			$title = str_replace( "%NAME", $creator->get_name(), $title );
	      			} else {
	        			$title = str_replace( "%NAME", "you", $title );
	      			}
	    		}
	    		
				$access = "<span title=\"". $title . "\">" . $access_descriptions[$weblog["KOALA_ACCESS"]]["summary_short"] . "</span>";
				$content->setVariable( "VALUE_WEBLOG_ACCESS", $access);
				$content->setVariable( "VALUE_WEBLOG_ARTICLES", count( $entries ) );
	    	$content->parse( "BLOCK_WEBLOG" );
		}
	$content->parse( "BLOCK_WEBLOGS" );
}
else
{
	$content->setVariable( "LABEL_NO_WEBLOGS_FOUND", "<b>" . gettext( "No weblogs available. Either no weblogs are created in this context, or you have no rights to read them." ) . "</b>" );
}

/// WIKIS ///
$content->setVariable( "LABEL_WIKIS", gettext( "Wikis" ) );

if( count( $wikis ) > 0 )
{
	$content->setCurrentBlock( "BLOCK_WIKIS" );
		$content->setVariable( "LABEL_WIKI_DESCRIPTION", gettext( "Wiki / description" ) );
		$content->setVariable( "LABEL_WIKI_ENTRIES", gettext( "Entries" ) );
		$content->setVariable( "LABEL_WIKI_ACCESS", gettext( "Access" ) );
	  	$access_descriptions = lms_wiki::get_access_descriptions( $group );
		
	  	foreach( $wikis as $wiki )
		{
			$cache = get_cache_function( $wiki[ "OBJ_ID" ], 600 );
			$entries  = $cache->call( "lms_wiki::get_items", $wiki[ "OBJ_ID" ] );
			$content->setCurrentBlock( "BLOCK_WIKI" );
				$content->setVariable( "WIKI_ID", $wiki[ "OBJ_ID" ] );
				$content->setVariable( "NAME_WIKI", h($wiki[ "OBJ_NAME" ]) );
				$content->setVariable( "LINK_WIKI", PATH_URL . "wiki/" . $wiki[ "OBJ_ID" ] . "/" );
				$content->setVariable( "WIKI_OBJ_DESC", get_formatted_output($wiki[ "OBJ_DESC" ]) );
		    	$title = $access_descriptions[$wiki["KOALA_ACCESS"]]["label"];
		    
		    	if ( $wiki["KOALA_ACCESS"] == PERMISSION_PRIVATE_READONLY && !($group instanceof koala_html_course))
		    	{
		      		$obj = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $wiki[ "OBJ_ID" ], CLASS_CONTAINER );
		      		$creator = $obj->get_creator();
		      
		      		if ($creator->get_id() != lms_steam::get_current_user()->get_id() )
		      		{
		        		$title = str_replace( "%NAME", $creator->get_name(), $title );
		      		} else {
		        		$title = str_replace( "%NAME", "you", $title );
		      		}
		    	}
		    	
		    	$access = "<span title=\"". $title . "\">" . $access_descriptions[$wiki["KOALA_ACCESS"]]["summary_short"] . "</span>";
				$content->setVariable( "VALUE_WIKI_ACCESS", $access);
				$content->setVariable( "VALUE_WIKI_ARTICLES", count( $entries ) );
			$content->parse( "BLOCK_WIKI" );
		}
	$content->parse( "BLOCK_WIKIS" );
}
else
{
	$content->setVariable( "LABEL_NO_WIKIS_FOUND", "<b>" . gettext( "No wikis available. Either no wikis are created in this context, or you have no rights to read them." ) . "</b>" );
}

$content->setVariable( "BACK_LINK", $_SERVER[ "HTTP_REFERER" ] );
$content->setVariable( "LABEL_RETURN", gettext( "back" ) );
$content->setVariable( "LABEL_COPY", gettext( "copy" ) );
$content->setVariable( "LABEL_COPY_TO", gettext( "Copy the selected weblogs and wikis to the following course" ) );

//GET BOOKED COURSES
$booked_courses = $cache->call( "lms_steam::user_get_booked_courses", $user->get_id() );
foreach( $booked_courses as $bc )
{
	$name = str_replace( $bc[ "OBJ_NAME" ], $bc[ "SEMESTER_NAME" ], $bc[ "COURSE_NAME" ] );
	$content->setCurrentBlock( "BLOCK_COPY_TO" );
	$content->setVariable( "ENTRY_COPY_TO", $name );
	$content->parse( "BLOCK_COPY_TO" );
}

$rootlink = lms_steam::get_link_to_root( $course );
$headline = array( $rootlink[0], $rootlink[1], array( "name" => gettext("Copy Weblogs and Wikis") ) );

$portal->set_page_main(
	$headline,
	$content->get(),
	""
);
$portal->show_html();

?>