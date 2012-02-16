<?php
	require_once( "../etc/koala.conf.php" );
	if (!defined("DISCLAIMER") || !DISCLAIMER) {
		header("location:/");
		exit;
	}
	
	$portal = lms_portal::get_instance();
	$portal->initialize( GUEST_NOT_ALLOWED );
	
		
		$css = <<< END
	#content {
		font-size: 14px;
		padding-left: 25px;
		padding-right: 25px;
	}
END;
	
		$portal->add_css_style($css);
	
		$content = new HTML_TEMPLATE_IT( PATH_TEMPLATES );
		$content->loadTemplateFile( "disclaimer_static.template.html" );
	
		$disclaimer_text_path = './styles/'.$STYLE.'/etc/disclaimer.de_DE.xml'; 	
	  	
		if (file_exists($disclaimer_text_path)) {
			$disclaimer_text = simplexml_load_file($disclaimer_text_path, null, LIBXML_NOCDATA);
			$content->setVariable( "DISCLAIMER_TEXT", $disclaimer_text->content);
		} else {
			$content->setVariable( "DISCLAIMER_TEXT", "Konnte disclaimer.de_DE.xml nicht finden.");
		}
		
		$portal->set_page_title( "Nutzungsordnung" );
		$portal->set_page_main( "Nutzungsordnung", $content->get() );
		$portal->show_html();
?>
