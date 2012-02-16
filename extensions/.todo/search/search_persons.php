<?php

// no direct call
if (!defined('_VALID_KOALA') || !USER_SEARCH) {
	header("location:/");
	exit;
}

$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "search_persons.template.html" );
$content->setVariable( "HEAD_SEARCH", gettext( "Search" ) );
$content->setVariable( "LABEL_SEARCH", gettext( "Search" ) );
$content->setVariable( "INFO_TEXT", gettext( "Using the search function, you can find contacts who are relevant to you." ) . " " . gettext( "Either you can search by the first or last name, or by a part of the email-address or login." ) . " <br/><br/>" . gettext( "Hint: Use '%' as a wildcard in your search pattern." ) );
$content->setVariable( "LABEL_CHECK_NAME", gettext( "Name" ) );
$content->setVariable( "LABEL_CHECK_LOGIN", gettext( "Email address or login" ) );

function is_valid_pattern($pattern) 
{
    $pattern = trim( $pattern );
    
    if ( empty( $pattern) ) 
    {
        return false; 
    }
    
    $stripped_pattern = str_replace('%', '', $pattern);
    $stripped_pattern = str_replace('_', '', $stripped_pattern);
    if ( strlen( $stripped_pattern ) < 3 )
    {
        return false;
    }
    
    return true;
}

// SEARCH RESULTS
if ( !empty($_REQUEST[ "pattern" ]) && is_valid_pattern( $_REQUEST[ "pattern" ] ) )
{
    $cache = get_cache_function( $user->get_name(), 60 );
    $result = $cache->call( "lms_steam::search_user", $_REQUEST[ "pattern" ], $_REQUEST[ "lookin" ] );
    $content->setVariable( "VALUE_PATTERN", $_REQUEST[ "pattern" ] );
    if ( $_REQUEST[ "lookin" ] == "login" )
    {
        $content->setVariable( "CHECKED_LOGIN", 'checked="checked"' );
    }
    else
    {
        $content->setVariable( "CHECKED_NAME", 'checked="checked"' );
    }
    // PROCEED RESULT SET
    $html_people = new HTML_TEMPLATE_IT();
    $html_people->loadTemplateFile( PATH_TEMPLATES . "list_users.template.html" );
    $no_people = count( $result );
    $start = $portal->set_paginator( $html_people, 10, $no_people, "(" . gettext( "%TOTAL people in result set" ). ")", "?pattern=" . $_REQUEST[ "pattern" ] . "&lookin=" . $_REQUEST[ "lookin" ] );
    $end = ( $start + 10 > $no_people ) ? $no_people : $start + 10;
    $html_people->setVariable( "LABEL_CONTACTS", gettext( "Results" ) . " (" . str_replace( array( "%a", "%z", "%s" ), array( $start + 1, $end, $no_people), gettext( "%a-%z out of %s" ) ) . ")" );
    if ( $no_people > 0 )
    {
        $html_people->setCurrentBlock( "BLOCK_CONTACT_LIST" );
        $html_people->setVariable( "LABEL_NAME_POSITION", gettext( "Name, position" ) );
        $html_people->setVariable( "LABEL_SUBJECT_AREA", gettext( "Origin/Focus" ) );
        $html_people->setVariable( "LABEL_COMMUNICATION", gettext( "Communication" ) );
        
        for ( $i = $start; $i < $end; $i++ )
        {
            $person = $result[ $i ];
            $html_people->setCurrentBlock( "BLOCK_CONTACT" );
            $html_people->setVariable( "CONTACT_LINK", PATH_URL . "user/" . h($person[ "OBJ_NAME" ]). "/" );
            $icon_link = ( $person[ "OBJ_ICON" ] == 0 ) ? PATH_STYLE . "images/anonymous.jpg" : PATH_URL . "cached/get_document.php?id=" . h($person[ "OBJ_ICON" ]) . "&type=usericon&width=30&height=40";
            $html_people->setVariable( "CONTACT_IMAGE", $icon_link );
            $html_people->setVariable( "CONTACT_NAME", h($person[ "USER_FIRSTNAME" ]) . " " . h($person[ "USER_FULLNAME" ]) );
            $html_people->setVariable( "LINK_SEND_MESSAGE", PATH_URL . "messages_write.php?to=" . h($person[ "OBJ_NAME" ]) );
            $html_people->setVariable( "LABEL_MESSAGE", gettext( "Message" ) );
            $html_people->setVariable( "LABEL_SEND", gettext( "Send" ) );
            
            $userDescription = h($person[ "OBJ_DESC" ]);
			switch ($userDescription){
				case "student":$userDescription = gettext("student");break;
				case "staff member":$userDescription = gettext("staff member");break;
				case "alumni":$userDescription = gettext("alumni");break;
				case "guest":$userDescription = gettext("guest");break;
				default:$userDescription = "&nbsp;";break;
			}
	
            $html_people->setVariable( "OBJ_DESC", $userDescription );
            $fof = $person[ "USER_PROFILE_FACULTY" ];
            $fof .= ( empty( $person[ "USER_PROFILE_FOCUS" ] ) ) ? "" : ", " . $person[ "USER_PROFILE_FOCUS" ];
            $html_people->setVariable( "FACULTY_AND_FOCUS", h($fof) );
            $html_people->parse( "BLOCK_CONTACT" );
        }
        
        $html_people->parse( "BLOCK_CONTACT_LIST" );
    }
    else
    {
        $html_people->setVariable( "LABEL_CONTACTS", gettext( "No results." ) );
    }
    
    $content->setVariable( "HTML_USER_LIST", $html_people->get() );
}
else
{
    if ( isset($_REQUEST["process_formular"]) && !is_valid_pattern($_REQUEST[ "pattern" ]) )
    {
        $portal->set_problem_description( gettext( "Your search string is invalid. Make sure to enter at least 3 non wildcard characters." ) );
    }
    
    if ( !empty($_REQUEST[ "lookin" ]) && $_REQUEST[ "lookin" ] == "login" )
    {
        $content->setVariable( "CHECKED_LOGIN", 'checked="checked"' );
    }
    else
    {
        $content->setVariable( "CHECKED_NAME", 'checked="checked"' );
    }
}

$portal->add_javascript_onload("search_persons", "document.getElementById('pattern').focus();");

$portal->set_page_main( array(  array( "link" => "", "name" => gettext( "People Search" )) ), $content->get() , "" );
$portal->show_html();
?>
