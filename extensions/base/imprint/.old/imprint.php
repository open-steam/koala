<?php
include_once( "../etc/koala.conf.php" );


$portal = lms_portal::get_instance();
$portal->initialize( GUEST_ALLOWED );
$portal->set_page_title( gettext( "Imprint" ) );
$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "imprint.template.html" );

$imprint_text_path = './styles/'.$STYLE.'/etc/imprint.de_DE.xml'; 	
  	
if (file_exists($imprint_text_path)) {
	$imprint_text = simplexml_load_file($imprint_text_path, null, LIBXML_NOCDATA);
	$content->setVariable( "IMPRINT_TEXT", $imprint_text->content);
	$content->setVariable( "IMPRINT_DATE", $imprint_text->date);
} else {
	$content->setVariable( "IMPRINT_TEXT", "Konnte imprint.de_DE.xml nicht finden.");
	$content->setVariable( "IMPRINT_DATE", "n.a.");
}


$portal->set_page_main(
	array(
		array( "link" => "",
			"name" => gettext( "Imprint" )
		)
	),
	$content->get(),
	""
);

$portal->show_html();
?>