<?php
defined("PATH_CURRENT_PLATFORM") or define("PATH_CURRENT_PLATFORM", dirname(dirname(__FILE__)) . "/");
defined("STYLE") or define( "STYLE", "elab" );

// short name and language variation
defined("PLATFORM_ID") or define("PLATFORM_ID", "koala");
// plattform name
defined("PLATFORM_NAME") or define("PLATFORM_NAME", "e-lab");
// plattform folder
defined("PLATFORM_FOLDER") or define("PLATFORM_FOLDER", PLATFORM_ID);
//displayed in browser title
defined("PLATFORM_TITLE") or define("PLATFORM_TITLE", "e-lab");	
//copyright information
defined("COPYRIGHT_NAME") or define("COPYRIGHT_NAME", "University of Paderborn");

defined("STARTPAGE_IMAGE_TEXT_LONG") or define("STARTPAGE_IMAGE_TEXT_LONG", "\"Pyramidendiskussion\", \"Rapidfeedback\"");
defined("STARTPAGE_IMAGE_TEXT_MEDIUM") or define("STARTPAGE_IMAGE_TEXT_MEDIUM", "\"Lernszenarien\",\"eLearning\",\"Medi@Thing\"");
defined("STARTPAGE_IMAGE_TEXT_SHORT") or define("STARTPAGE_IMAGE_TEXT_SHORT", "\"ViLM\",\"Web2.0\",\"e-lab\"");

defined("DISCLAIMER") or define("DISCLAIMER", FALSE);
defined("CHANGE_PASSWORD") or define("CHANGE_PASSWORD", FALSE);

define("BLACKLISTED_EXTENSIONS", "Artefact, Competences, Connections, Portfolio, PortfolioHome, Portfolios");

?>
