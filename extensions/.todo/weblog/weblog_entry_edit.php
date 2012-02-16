<?php

$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "weblog_entry_edit.template.html" );

if ( $_SERVER[ "REQUEST_METHOD" ] == "GET" )
{
	$content->setVariable( "BACK_LINK", $_SERVER[ "HTTP_REFERER" ] );
}
if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" && $date->check_access_write( $user ) )
{
	$values = $_POST[ "values" ];
	if ( ! empty( $values[ "save" ] ) )
	{
		$problem = "";
		$hint    = "";
		if ( empty( $values[ "title" ] ) )
		{
			$problem .= gettext( "The title is missing." ) . "&nbsp;";
			$hint    .= gettext( "Please add the missing values." );
		}
		if ( empty( $values[ "body" ] ) )
		{
			$problem .= gettext( "There is no message for your readers." ) . "&nbsp;" ;
			$hint    .= gettext( "Please write your post into the text area." );
		}
		if ( ! empty( $values[ "category" ] ) && $values[ "category" ] != 0 )
		{
			$category = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $values[ "category" ] );
			if ( ! $category instanceof steam_container )
			{
				throw new Exception( "Not a valid category: " . $values[ "category" ] );
			}
		}
		else
		{
			$category = "";
		}
		if ( ! $timestamp = strtotime( $values[ "date" ] . ":00" ) )
		{
			$problem .= gettext( "I cannot parse the date and time." );
			$hint .= gettext( "Please verify your date and time format" ) . ": YYYY-MM-DD HH:MM";
		}


		if ( empty( $problem )  )
		{
			require_once( "Cache/Lite.php" );
			$cache = new Cache_Lite( array( "cacheDir" => PATH_CACHE ) );
			$cache->clean( $weblog->get_id() );
			$cache->clean( $date->get_id() );
			
			$attributes = array(
				"DATE_START_DATE" => $timestamp,
				"DATE_TITLE" => $values[ "title" ],
				"DATE_DESCRIPTION" => $values[ "body" ]
			);
			$date->set_attributes( $attributes );
			$weblog->categorize_entry( $date, $category );

			header( "Location: " . $values[ "return_to" ] );
			exit;
		}
		else
		{
			$portal->set_problem_description( $problem, $hint );
		}
	}
	if ( $values[ "preview" ] )
	{
		$content->setCurrentBlock( "BLOCK_PREVIEW" );
		$content->setVariable( "LABEL_PREVIEW_EDIT", gettext( "Preview the edit" ) );
		$content->setVariable( "PREVIEW_EDIT", get_formatted_output( $values[ "body" ] ) );
		$content->parse( "BLOCK_PREVIEW" );
	}
}

$content->setVariable( "LABEL_HERE_IT_IS", "" );
$content->setVariable( "LABEL_DATE", gettext( "Date" ) );
$content->setVariable( "LABEL_SUBJECT", gettext( "Subject" ) );
$content->setVariable( "LABEL_CATEGORY", gettext( "Category" ) );
$content->setVariable( "CAT_NO_SELECTION", gettext( "nothing selected" ) );
$content->setVariable( "LABEL_YOUR_POST", gettext( "Your post" ) );
$content->setVariable( "LABEL_PREVIEW", gettext( "Preview" ) );
$content->setVariable( "LABEL_SAVE_CHANGES", gettext( "Save changes" ) );
$content->setVariable( "LABEL_RETURN", gettext( "back" ) );

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

if ( isset($values) && count( $values ) )
{
	// FORMULAR WURDE SCHON EINMAL ABGESCHICKT
	$date_value = ( empty( $values[ "date" ] ) ) ? strftime( "%Y-%m-%d %H:%M" ) : $values[ "date" ];
	$content->setVariable( "DATE_COMMENT", h($date_value) );
	$content->setVariable( "TEXT_COMMENT", h($values[ "body" ]) );
	$content->setVariable( "TITLE_COMMENT", h($values[ "title" ]) );
	$cat = $values[ "category" ];
	$content->setVariable( "BACK_LINK", $values[ "return_to" ] );
}
else
{
	$attribs = $date->get_attributes(
			array( "DATE_START_DATE", "DATE_TITLE", "DATE_DESCRIPTION", "DATE_CATEGORY" )
			);
	$content->setVariable( "DATE_COMMENT", strftime( "%Y-%m-%d %H:%M", $attribs[ "DATE_START_DATE" ] ) );
	$content->setVariable( "TITLE_COMMENT", h($attribs[ "DATE_TITLE" ]) );
	$content->setVariable( "TEXT_COMMENT", h($attribs[ "DATE_DESCRIPTION" ]) );
	$content->setVariable( "TITLE_COMMENT", h($attribs[ "DATE_TITLE" ]) );
	$cat = ( is_object( $attribs[ "DATE_CATEGORY" ] ) ) ? $attribs[ "DATE_CATEGORY" ]->get_id() : 0;
}

$categories = $weblog->get_categories();
foreach( $categories as $category )
{
	$content->setCurrentBlock( "BLOCK_SELECT_CAT" );
	$content->setVariable( "VALUE_CAT", $category->get_id() );
	$content->setVariable( "LABEL_CAT", h($category->get_name()) );
	if ( $category->get_id() == $cat )
	{
		$content->setVariable( "CAT_SELECTED", 'selected="selected"' );
	}
	$content->parse( "BLOCK_SELECT_CAT" );
}

$rootlink = lms_steam::get_link_to_root( $weblog );
$headline = array(
				$rootlink[0],
				$rootlink[1],
				array( "link" => $rootlink[1]["link"] . "communication/", "name" => gettext("Communication")),
				array( "link" => "", "name" =>  h($weblog->get_name()), "link" => PATH_URL . "weblog/" . $weblog->get_id() . "/"),
				array( "name" => str_replace( "%NAME", h($date->get_attribute( "DATE_TITLE" )), gettext( "Edit '%NAME'?" )))
			);

$portal->set_page_main(
		$headline,
		$content->get(),
		""
		);
$portal->show_html();

?>