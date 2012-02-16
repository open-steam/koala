<?php
	$content = new HTML_TEMPLATE_IT();
	$content->loadTemplateFile( PATH_TEMPLATES . "home.template.html" );
	
	$content->setVariable("LABEL_GREETING", gettext( "Welcome" ) . " " . 
			$portal->get_user()->get_forename() . " " . 
			$portal->get_user()->get_surname() );
	
	$content->setVariable("INFO_TODO_MAILS",
			"<a href=\"".PATH_SERVER."/bid/journal/1599/\">zum Test Journal</a>");
?>
