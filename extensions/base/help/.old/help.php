<?php
include_once( "../etc/koala.conf.php" );


$portal = lms_portal::get_instance();
$portal->initialize( GUEST_ALLOWED );
$portal->set_page_title( "Hilfe" );
$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "help.template.html" );

$help_text_path = './styles/'.$STYLE.'/etc/help.de_DE.xml'; 	
  	
if (file_exists($help_text_path)) {
	$help_text = simplexml_load_file($help_text_path, null, LIBXML_NOCDATA);
	$content->setVariable( "HELP_TEXT", $help_text->content);
} else {
	$content->setVariable( "HELP_TEXT", "Konnte help.de_DE.xml nicht finden.");
}


$portal->set_page_main(
	array(
		array( "link" => "/help/",
			"name" => gettext( "Help" )
		)
	),
	$content->get(),
	""
);

$portal->show_html();