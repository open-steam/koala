<?php

class steam_weblog extends steam_calendar
{

	public function get_categories()
	{
		if ( ! $categories = $this->get_object_by_name( "categories" ) )
		{
			return array();
		}
		return $categories->get_inventory( CLASS_CONTAINER );
	}

	public function get_entries_by_category( $category )
	{
		$result = array();
		$links = $category->get_inventory( CLASS_LINK );
		foreach( $links as $link )
		{
			$result[] = $link->get_source_object();
		}
		return $result;
	}

	public function get_blogroll_list( )
	{
		$blogroll = $this->get_blogroll();
		return $blogroll->get_inventory( CLASS_DOCEXTERN, array(), SORT_NAME );
	}

	public function get_archives( $dates )
	{
		if ( sizeof( $dates ) == 0 || ! is_array( $dates ) )
		{
			// throw new Exception( "No dates are given.", E_PARAMETER );
			return array();
		}
		$months = array();
		foreach( $dates as $date_objects )
		{
			$month = date( "Y-m", $date_objects->get_attribute( "DATE_START_DATE" ) );
			@$months[ $month ]++;  //Count for the month and hide error notice at first time
		}
		return $months;
	}


	public function create_steam_structure( $steam_connector, $name, $description, $env )
	{
		// CREATE CONTAINER FOR THIS WEBLOG
		$new_calendar = steam_factory::create_calendar(
				$steam_connector->get_id(),
				$name,
				$env,
				$description
				);
		$new_calendar->set_attribute( "OBJ_TYPE", "calendar_weblog_koala" );
		$all_user = steam_factory::groupname_to_object( $steam_connector->get_id(), STEAM_ALL_USER );
		$new_calendar->set_read_access( $all_user );

		// CREATE CONTAINER FOR CATEGORIES
		$categories = steam_factory::create_container( $steam_connector->get_id(), "categories", $new_calendar, "all categories for this weblog" );

		// CREATE CONTAINER FOR BLOGROLL
		$blogroll   = steam_factory::create_container( $steam_connector->get_id(), "blogroll", $new_calendar, "blogroll for this weblog" );
		
		// CREATE CONTAINER FOR PODCASTING
		$blogroll   = steam_factory::create_container( $steam_connector->get_id(), "podspace", $new_calendar, "multimedia files for podcasting" );
    
    // convert calendar to weblog to match result
    $new_weblog = new steam_weblog( $GLOBALS["STEAM"], $new_calendar->get_id() );
		return $new_weblog;
	}

	public function create_category( $name, $description )
	{
		if ( ! $categories = $this->get_object_by_name( "categories" ) )
		{
			$categories = steam_factory::create_container( $GLOBALS["STEAM"]->get_id(), "categories", $this, "all categories for this weblog" );
		}
		
		$new_category = steam_factory::create_container(
				
				//$this->steam_connector->get_id(),
				$GLOBALS["STEAM"]->get_id(),
				$name,
				$categories,
				$description
				);
		return $new_category;
	}

	public function get_blogroll()
	{
		return $this->get_object_by_name( "blogroll" );
	}

	public function get_podspace()
	{
		return $this->get_object_by_name( "podspace" );
	}

	public function blogroll_add_blog( $name, $url, $dsc = "" )
	{
		$blogroll = $this->get_blogroll();
		$blog = steam_factory::create_docextern( $this->steam_connector->get_id(), $name, $url, $blogroll, $dsc );
		return $blog;
	}

	public function categorize_entry( $entry, $category )
	{
		if ( ! $entry instanceof steam_date )
		{
			throw new Exception( "not a date object", E_PARAMETER );
		}
		$old_cat = $entry->get_attribute( "DATE_CATEGORY" );
		if ( is_object( $old_cat ) )
		{
			if( is_object( $category ) )
			{
				if ( $category->get_id() != $old_cat->get_id() )
				{
					$old_link = $old_cat->get_object_by_name( $entry->get_name() );
					$old_link->move( $category );
				}
			}
			else
			{
				$old_link = $old_cat->get_object_by_name( $entry->get_name() );
				$old_link->delete();
			}
		}
		else
		{
			if( is_object( $category ) )
			{
				$link = steam_factory::create_link(
					$this->steam_connector->get_id(),
					$entry	
					);
				$link->move( $category );
			}
		}
		$entry->set_attribute( "DATE_CATEGORY", $category );
	}

	public function create_entry( $subject, $body, $category = "", $keywords = array(), $timestamp = "" )
	{
		if ( empty( $timestamp ) )
		{
			$timestamp = time();
		}
		$data = array(
				"DATE_TITLE" 		=> $subject,
				"DATE_DESCRIPTION" 	=> $body,
				"DATE_START_DATE"	=> $timestamp,
				"DATE_END_DATE"		=> $timestamp,
				"DATE_CATEGORY"		=> $category,
				"OBJ_KEYWORDS"		=> $keywords
			     );
		// CREATE NEW ENTRY IN CALENDAR
		$date_object = $this->add_entry( $data );
		$all_user = steam_factory::groupname_to_object( $GLOBALS["STEAM"]->get_id(), STEAM_ALL_USER );
		// CREATE NEW LINK IN CATEGORY
		if ( ! empty( $category ) )
		{
			$link = steam_factory::create_link(
					$GLOBALS["STEAM"]->get_id(),
					$date_object	
					);
			$link->move( $category );
		}
		// RETURN NEW DATE OBJECT
		return $date_object;
	}

}

?>
