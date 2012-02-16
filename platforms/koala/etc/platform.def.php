<?php
defined("PATH_CURRENT_PLATFORM") or define("PATH_CURRENT_PLATFORM", dirname(dirname(__FILE__)) . "/");
defined("STYLE") or define( "STYLE", "koala" );

// short name and language variation
defined("PLATFORM_ID") or define("PLATFORM_ID", "koala");
// plattform name
defined("PLATFORM_NAME") or define("PLATFORM_NAME", "koaLA");
// plattform folder
defined("PLATFORM_FOLDER") or define("PLATFORM_FOLDER", PLATFORM_ID);
//displayed in browser title
defined("PLATFORM_TITLE") or define("PLATFORM_TITLE", "koaLA");	
//copyright information
defined("COPYRIGHT_NAME") or define("COPYRIGHT_NAME", "University of Paderborn");

define("STARTPAGE_IMAGE_TEXT_LONG", "\"zusammenwirken\", \"kommunizieren\", \"communicate\", \"communiquer\"");
define("STARTPAGE_IMAGE_TEXT_MEDIUM", "\"verbinden\",\"cooperate\",\"comunicar\",\"compartir\",\"colaborar\",\"apprendre\",\"partager\",\"aprender\",\"coopérer\",\"joindre\"");
define("STARTPAGE_IMAGE_TEXT_SHORT", "\"teilen\",\"lernen\",\"juntar\",\"share\",\"learn\",\"join\"");

defined("DISCLAIMER") or define("DISCLAIMER", FALSE);
defined("CHANGE_PASSWORD") or define("CHANGE_PASSWORD", FALSE);

define("BLACKLISTED_EXTENSIONS", "Artefact, Competences, Connections, Portfolio, PortfolioHome, Portfolios");

?>
