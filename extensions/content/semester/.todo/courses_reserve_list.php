<?php

$html_handler_course = new koala_html_course( $course );
$html_handler_course->set_context( "reserve_list" );

$content = new HTML_TEMPLATE_IT( PATH_TEMPLATES );
$content->loadTemplateFile( "list_reserve_list.template.html" );

$rl = $course->get_attributes( array( "SEM_APP_ID", "SEM_APP_TOKEN" ) );

$content->setVariable( "SEM_APP_BASE_URL", SEM_APP_BASE_URL); // Global config
$content->setVariable( "SEM_APP_ID", $rl["SEM_APP_ID"] ); // sTeam attribute
$content->setVariable( "SEM_APP_TOKEN", $rl["SEM_APP_TOKEN"] ); // sTeam attribute

$html_handler_course->set_html_left( $content->get() );
$portal->set_page_main( $html_handler_course->get_headline(), $html_handler_course->get_html(), "" );

$portal->show_html();
?>
