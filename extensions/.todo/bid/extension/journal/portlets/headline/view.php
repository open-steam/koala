<?php

if(sizeof($portlet_content) > 0) {
	$template = new HTML_TEMPLATE_IT();
	$template->loadTemplateFile(PATH_CURRENT_EXTENSION . 
		"portlets/headline/templates/view.html");

	$template->setVariable("ALIGNMENT", trim($portlet_content["alignment"]));
	$template->setVariable("SIZE", trim($portlet_content["size"]));
	$template->setVariable("HEADLINE", /*$UBB->encode*/($portlet_content["headline"]));

	if ($portlet->check_access_write($GLOBALS["STEAM"]->get_current_steam_user())) {
		$template->setVariable("PORTLET_ID", $portlet->get_id());
		$template->setVariable("PORTLET_ROOT", PATH_CURRENT_EXTENSION . "./portlets/headline");
	}

	echo $template->get();
}
else
	echo("");

?>
