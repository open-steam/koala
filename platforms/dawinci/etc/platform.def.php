<?php
defined("PATH_CURRENT_PLATFORM") or define("PATH_CURRENT_PLATFORM", dirname(dirname(__FILE__)) . "/");
defined("STYLE") or define( "STYLE", "dawinci" );

// short name and language variation
define("PLATFORM_ID", "dawinci");
// plattform name
define("PLATFORM_NAME", "DAWINCI");
// plattform folder
defined("PLATFORM_FOLDER") or define("PLATFORM_FOLDER", PLATFORM_ID);
//displayed in browser title
define("PLATFORM_TITLE", "DAWINCI Projektplattform");	
//copyright information
define("COPYRIGHT_NAME", "DAWINCI Projekt");
	
// The message to display in the error dialog during the Maintainance.
// If MAINTAINANCE_MESSAGE is empty, a generic error message will be displayed
define( "MAINTAINANCE_MESSAGE", "Das koaLA System wird zur Zeit gewartet, dies wird voraussichtlich 20 Minuten in Anspruch nehmen.<br /> Bitte haben Sie etwas Geduld und versuchen es später noch einmal.");
		
define("STARTPAGE_IMAGE_TEXT_LONG", "\"zusammenwirken\", \"kommunizieren\", \"communicate\", \"communiquer\"");
define("STARTPAGE_IMAGE_TEXT_MEDIUM", "\"verbinden\",\"cooperate\",\"comunicar\",\"compartir\",\"colaborar\",\"apprendre\",\"partager\",\"aprender\",\"coopérer\",\"joindre\"");
define("STARTPAGE_IMAGE_TEXT_SHORT", "\"teilen\",\"lernen\",\"juntar\",\"share\",\"learn\",\"join\"");

defined("DISCLAIMER") or define("DISCLAIMER", FALSE);
defined("CHANGE_PASSWORD") or define("CHANGE_PASSWORD", FALSE);

define("BLACKLISTED_EXTENSIONS", "BookmarksHome, ChangeLogHome, LastVisitedHome, SchoolHome");
define("YOUR_COURSES", false);
define("YOUR_GROUPS", false);
define("YOUR_PORTFOLIO", true);
define("SERVERMENU", false);
?>
